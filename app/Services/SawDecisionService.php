<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SawDecisionService
{
    public const WEIGHTS = ['k1' => 0.30, 'k2' => 0.20, 'k3' => 0.15, 'k4' => 0.20, 'k5' => 0.15];

    public const ATTRIBUTES = ['k1' => 'benefit', 'k2' => 'benefit', 'k3' => 'cost', 'k4' => 'benefit', 'k5' => 'cost'];

    public const CRITERIA = [
        'k1' => ['name' => 'Tingkat kepentingan', 'weight' => '30%', 'attribute' => 'Benefit'],
        'k2' => ['name' => 'Jumlah peserta', 'weight' => '20%', 'attribute' => 'Benefit'],
        'k3' => ['name' => 'Durasi penggunaan', 'weight' => '15%', 'attribute' => 'Cost'],
        'k4' => ['name' => 'Waktu pengajuan', 'weight' => '20%', 'attribute' => 'Benefit'],
        'k5' => ['name' => 'Frekuensi divisi', 'weight' => '15%', 'attribute' => 'Cost'],
    ];

    private const ACTIVITY_SCORES = [
        'direksi_eksternal' => 5,
        'antar_divisi' => 4,
        'internal_divisi' => 3,
        'pelatihan' => 2,
        'rutin' => 1,
    ];

    /** @return list<array<string, mixed>> */
    public function conflictAnalyses(): array
    {
        $bookings = Booking::query()
            ->with(['room', 'user'])
            ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->whereDate('date', '>=', today())
            ->orderBy('date')
            ->orderBy('room_id')
            ->orderBy('start_time')
            ->get();

        $groups = [];
        foreach ($bookings->groupBy(fn (Booking $booking) => $booking->room_id.'_'.$booking->date->format('Y-m-d')) as $sameRoomDate) {
            foreach ($this->connectedConflictGroups($sameRoomDate->values()) as $conflicts) {
                if ($conflicts->contains(fn (Booking $booking) => $booking->status === BookingStatus::Pending)) {
                    $analysis = $this->analyze($conflicts);
                    $first = $conflicts->first();
                    $groups[] = [
                        'key' => $first->room_id.'-'.$first->date->format('Y-m-d').'-'.$first->id,
                        'room' => $first->room,
                        'date' => $first->date,
                        'bookings' => $conflicts,
                        'analysis' => $analysis,
                    ];
                }
            }
        }

        return $groups;
    }

    /** @return array<string, mixed> */
    public function analyze(Collection $bookings): array
    {
        if ($bookings->count() < 2) {
            throw ValidationException::withMessages(['booking' => 'Analisis SAW memerlukan minimal dua booking yang bentrok.']);
        }

        $usageCounts = $this->monthlyDepartmentUsageCounts($bookings);
        $rawRows = $bookings->values()->map(function (Booking $booking, int $index) use ($usageCounts): array {
            $usage = $usageCounts[$booking->user_dept] ?? 0;

            return [
                'code' => 'A'.($index + 1),
                'booking' => $booking,
                'raw' => $this->rawScores($booking, $usage),
            ];
        });

        $bounds = [];
        foreach (array_keys(self::WEIGHTS) as $criterion) {
            $values = $rawRows->pluck("raw.{$criterion}.value");
            $bounds[$criterion] = ['min' => $values->min(), 'max' => $values->max()];
        }

        $results = $rawRows->map(function (array $row) use ($bounds): array {
            $score = 0.0;
            $normalized = [];

            foreach ($row['raw'] as $criterion => $raw) {
                $value = $raw['value'];
                $ratio = self::ATTRIBUTES[$criterion] === 'benefit'
                    ? $value / max(1, $bounds[$criterion]['max'])
                    : $bounds[$criterion]['min'] / max(1, $value);
                $normalized[$criterion] = round($ratio, 4);
                $score += self::WEIGHTS[$criterion] * $ratio;
            }

            return $row + [
                'normalized' => $normalized,
                'preference_score' => round($score, 4),
            ];
        })->sort(function (array $left, array $right): int {
            $byScore = $right['preference_score'] <=> $left['preference_score'];
            if ($byScore !== 0) {
                return $byScore;
            }

            return [$left['booking']->created_at, $left['booking']->id]
                <=> [$right['booking']->created_at, $right['booking']->id];
        })->values()->map(function (array $result, int $index): array {
            $result['rank'] = $index + 1;

            return $result;
        });

        return [
            'criteria' => self::CRITERIA,
            'bounds' => $bounds,
            'results' => $results,
            'winner' => $results->first(),
        ];
    }

    public function applyRecommendation(Booking $anchor): Booking
    {
        return DB::transaction(function () use ($anchor): Booking {
            $lockedAnchor = Booking::query()->lockForUpdate()->findOrFail($anchor->id);
            Room::query()->lockForUpdate()->findOrFail($lockedAnchor->room_id);

            if (! in_array($lockedAnchor->status, [BookingStatus::Pending, BookingStatus::Confirmed], true)) {
                throw ValidationException::withMessages(['booking' => 'Kelompok konflik ini sudah berubah. Muat ulang halaman.']);
            }

            $activeBookings = Booking::query()
                ->with(['room', 'user'])
                ->where('room_id', $lockedAnchor->room_id)
                ->whereDate('date', $lockedAnchor->date)
                ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
                ->orderBy('start_time')
                ->lockForUpdate()
                ->get();

            $group = collect($this->connectedConflictGroups($activeBookings))
                ->first(fn (Collection $candidate) => $candidate->contains('id', $lockedAnchor->id));

            if (! $group || ! $group->contains(fn (Booking $booking) => $booking->status === BookingStatus::Pending)) {
                throw ValidationException::withMessages(['booking' => 'Konflik tidak lagi memiliki pengajuan pending.']);
            }

            if ($group->contains(fn (Booking $booking) => $booking->startsAt()->isPast())) {
                throw ValidationException::withMessages(['booking' => 'Keputusan SAW tidak dapat diterapkan setelah salah satu jadwal dimulai.']);
            }

            $winner = $this->analyze($group)['winner']['booking'];

            Booking::query()->whereIn('id', $group->pluck('id')->reject(fn (int $id) => $id === $winner->id))
                ->update(['status' => BookingStatus::Cancelled->value]);

            Booking::query()->whereKey($winner->id)->update([
                'status' => BookingStatus::Confirmed->value,
            ]);

            return Booking::query()->with(['room', 'user'])->findOrFail($winner->id);
        }, attempts: 3);
    }

    /** @return list<Collection<int, Booking>> */
    private function connectedConflictGroups(Collection $bookings): array
    {
        $bookings = $bookings->values();
        $visited = [];
        $groups = [];

        foreach ($bookings as $index => $booking) {
            if (isset($visited[$index])) {
                continue;
            }

            $queue = [$index];
            $visited[$index] = true;
            $component = collect();

            while ($queue !== []) {
                $currentIndex = array_shift($queue);
                $current = $bookings[$currentIndex];
                $component->push($current);

                foreach ($bookings as $candidateIndex => $candidate) {
                    if (! isset($visited[$candidateIndex]) && $this->overlaps($current, $candidate)) {
                        $visited[$candidateIndex] = true;
                        $queue[] = $candidateIndex;
                    }
                }
            }

            if ($component->count() >= 2) {
                $groups[] = $component;
            }
        }

        return $groups;
    }

    private function overlaps(Booking $left, Booking $right): bool
    {
        return $left->start_time < $right->end_time && $left->end_time > $right->start_time;
    }

    /** @return array<string, array{value: int, label: string}> */
    private function rawScores(Booking $booking, int $monthlyUsage): array
    {
        $attendees = $booking->attendees_count;
        $durationMinutes = CarbonImmutable::parse($booking->start_time)->diffInMinutes(CarbonImmutable::parse($booking->end_time));
        $durationHours = max(1, (int) ceil($durationMinutes / 60));
        $createdDate = CarbonImmutable::parse($booking->created_at ?? now())->startOfDay();
        $leadDays = max(0, $createdDate->diffInDays($booking->date, false));

        return [
            'k1' => ['value' => self::ACTIVITY_SCORES[$booking->activity_type] ?? 3, 'label' => 'Prioritas kegiatan'],
            'k2' => ['value' => $attendees > 20 ? 4 : ($attendees >= 11 ? 3 : ($attendees >= 5 ? 2 : 1)), 'label' => $attendees.' peserta'],
            'k3' => ['value' => min(4, $durationHours), 'label' => $durationHours.' jam'],
            'k4' => ['value' => $leadDays >= 3 ? 3 : ($leadDays >= 1 ? 2 : 1), 'label' => 'Diajukan H-'.$leadDays],
            'k5' => ['value' => $monthlyUsage <= 2 ? 1 : ($monthlyUsage <= 5 ? 2 : 3), 'label' => $monthlyUsage.' penggunaan bulan ini'],
        ];
    }

    /** @return array<string, int> */
    private function monthlyDepartmentUsageCounts(Collection $bookings): array
    {
        $departments = $bookings->pluck('user_dept')->filter()->unique()->values();
        $date = $bookings->first()?->date;

        if ($departments->isEmpty() || ! $date) {
            return [];
        }

        return Booking::query()
            ->selectRaw('user_dept, COUNT(*) as aggregate')
            ->whereIn('user_dept', $departments)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Completed->value])
            ->groupBy('user_dept')
            ->pluck('aggregate', 'user_dept')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }
}
