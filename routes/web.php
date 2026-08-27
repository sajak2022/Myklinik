<?php

use App\Filament\Pages\KunjunganPasien;
use App\Models\Pendaftaran;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::post('/admin/pelayanan/selesaikan/{id?}', function ($id = null) {
    /** @var User|null $user */
    $user = auth()->user();
    if (! $user || ! ($user->hasRole(['super_admin', 'Dokter']) || ($user->pegawai && $user->pegawai->profesi === 'Dokter'))) {
        abort(403);
    }

    $pendaftaranId = $id ?: session('active_pendaftaran_id');
    if (! $pendaftaranId) {
        $queryActive = Pendaftaran::where('status_pelayanan', Pendaftaran::STATUS_SEDANG_DIPERIKSA)->latest('tanggal_pendaftaran');
        if (! $user->hasRole('super_admin') && $user->pegawai?->poli_id) {
            $queryActive->where('poli_id', $user->pegawai->poli_id);
        }
        $pendaftaran = $queryActive->first();
    } else {
        $pendaftaran = Pendaftaran::find((int) $pendaftaranId);
    }

    if (! $pendaftaran) {
        return redirect()->to(KunjunganPasien::getUrl());
    }

    $pendaftaran->update(['status_pelayanan' => Pendaftaran::STATUS_SELESAI]);
    session()->forget('active_pendaftaran_id');

    Notification::make()
        ->title('Pelayanan Selesai')
        ->body("Pelayanan untuk pasien {$pendaftaran->pasien?->nama} telah berhasil diselesaikan.")
        ->success()
        ->send();

    return redirect()->to(KunjunganPasien::getUrl());
})->middleware(['web', 'auth'])->name('pelayanan.selesaikan');
