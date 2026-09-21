<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\SawDecisionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConflictController extends Controller
{
    public function index(SawDecisionService $decisions): View
    {
        return view('admin.conflicts.index', ['groups' => $decisions->conflictAnalyses()]);
    }

    public function apply(Booking $booking, SawDecisionService $decisions): RedirectResponse
    {
        $winner = $decisions->applyRecommendation($booking);

        return back()->with('success', "Rekomendasi SAW diterapkan. Booking #{$winner->id} dipilih dan jadwal bentrok lainnya dibatalkan.");
    }
}
