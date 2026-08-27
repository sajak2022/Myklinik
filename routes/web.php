<?php

use App\Filament\Resources\Pendaftarans\PendaftaranResource;
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
        $queryActive = Pendaftaran::whereIn('status_pelayanan', ['Sedang Diperiksa', 'Menunggu'])->latest('tanggal_pendaftaran');
        if (! $user->hasRole('super_admin') && $user->pegawai?->poli_id) {
            $queryActive->where('poli_id', $user->pegawai->poli_id);
        }
        $pendaftaran = $queryActive->first();
    } else {
        $pendaftaran = Pendaftaran::find((int) $pendaftaranId);
    }

    if (! $pendaftaran) {
        return redirect()->to(PendaftaranResource::getUrl('index'));
    }

    $pendaftaran->update(['status_pelayanan' => 'Selesai']);
    session()->forget('active_pendaftaran_id');

    Notification::make()
        ->title('Pelayanan Selesai')
        ->body("Pelayanan untuk pasien {$pendaftaran->pasien?->nama} telah berhasil diselesaikan.")
        ->success()
        ->send();

    return redirect()->to(PendaftaranResource::getUrl('index'));
})->middleware(['web', 'auth'])->name('pelayanan.selesaikan');
