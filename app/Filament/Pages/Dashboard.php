<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    public function getHeading(): string | Htmlable
    {
        return 'Dashboard';
    }

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if ($user && ($user->hasRole(['Dokter', 'Perawat']) || ($user->pegawai && in_array($user->pegawai->profesi, ['Dokter', 'Perawat'])))) {
            $this->redirect(KunjunganPasien::getUrl());
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // Sembunyikan Dashboard di sidebar untuk Dokter dan Perawat
        if ($user->hasRole(['Dokter', 'Perawat'])) {
            return false;
        }

        if ($user->pegawai && in_array($user->pegawai->profesi, ['Dokter', 'Perawat'])) {
            return false;
        }

        return true;
    }
}

