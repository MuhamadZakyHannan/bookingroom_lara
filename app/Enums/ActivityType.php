<?php

namespace App\Enums;

enum ActivityType: string
{
    case ExecutiveExternal = 'direksi_eksternal';
    case CrossDivision = 'antar_divisi';
    case InternalDivision = 'internal_divisi';
    case Training = 'pelatihan';
    case Routine = 'rutin';

    public function label(): string
    {
        return match ($this) {
            self::ExecutiveExternal => 'Rapat dengan Direksi / Pihak Eksternal',
            self::CrossDivision => 'Rapat Koordinasi Antar Divisi',
            self::InternalDivision => 'Rapat Internal Divisi',
            self::Training => 'Sosialisasi / Pelatihan',
            self::Routine => 'Kegiatan Rutin / Non-Prioritas',
        };
    }
}
