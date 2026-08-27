<?php

namespace App\Filament\Resources\Pendaftarans\Pages;

use App\Filament\Resources\Pendaftarans\PendaftaranResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePendaftaran extends CreateRecord
{
    protected static string $resource = PendaftaranResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function mount(): void
    {
        parent::mount();

        if ($pasienId = request()->query('pasien_id')) {
            $active = \App\Models\Pendaftaran::where('pasien_id', $pasienId)
                ->whereIn('status_pelayanan', \App\Models\Pendaftaran::ACTIVE_STATUSES)
                ->first();

            if ($active) {
                Notification::make()
                    ->warning()
                    ->persistent()
                    ->title('Pasien Masih Dalam Pelayanan')
                    ->body("Pasien ini masih memiliki antrian/pelayanan aktif ({$active->no_pendaftaran} di {$active->poli?->nama} - Status: {$active->status_pelayanan}). Pelayanan sebelumnya harus diselesaikan terlebih dahulu.")
                    ->send();
            }

            $pasien = \App\Models\Pasien::find($pasienId);
            $hasHistory = \App\Models\Pendaftaran::where('pasien_id', $pasienId)->exists();
            $kel = $pasien?->keluargas()->first();

            $this->form->fill([
                'pasien_id'           => (int) $pasienId,
                'tanggal_pendaftaran' => now(),
                'jenis_pelayanan'     => 'Pelayanan Rawat Jalan',
                'jenis_kunjungan'     => $hasHistory ? 'Lama' : 'Baru',
                'ada_pj'              => (bool) $kel,
                'pj_nama'             => $kel?->nama,
                'pj_hubungan'         => $kel?->statusKeluarga?->deskripsi ?? 'Keluarga',
                'pj_no_telepon'       => $kel?->telepon,
                'pj_alamat'           => $kel?->alamat,
            ]);
        }
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $pasienNama = $record->pasien?->nama ?? 'Pasien';
        $noAntrian = $record->no_antrian ?? '-';
        $poliNama = $record->poli?->nama ?? 'Poli Tujuan';

        // Set flash session agar suara berbunyi saat redirect halaman index
        session()->flash('play_sound_on_load', true);
        $this->dispatch('play-notification-sound');

        // Kirim ke database notification pengguna saat ini
        if ($user = Auth::user()) {
            Notification::make()
                ->title('Pendaftaran Pasien Selesai')
                ->body("Pasien {$pasienNama} (No. Antrian: {$noAntrian}) berhasil didaftarkan ke {$poliNama}.")
                ->icon('heroicon-o-check-circle')
                ->iconColor('success')
                ->success()
                ->sendToDatabase($user);
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        $record = $this->record;
        $pasienNama = $record->pasien?->nama ?? 'Pasien';
        $noAntrian = $record->no_antrian ?? '-';
        $poliNama = $record->poli?->nama ?? 'Poli Tujuan';

        return Notification::make()
            ->title('Pendaftaran Pasien Berhasil')
            ->body("Pasien {$pasienNama} (No. Antrian: {$noAntrian}) berhasil didaftarkan ke {$poliNama}.")
            ->success();
    }
}
