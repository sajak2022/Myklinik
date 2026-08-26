<?php

namespace App\Filament\Resources\Pendaftarans\Pages;

use App\Filament\Resources\Pendaftarans\PendaftaranResource;
use Filament\Resources\Pages\CreateRecord;

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
                ->whereIn('status_pelayanan', ['Menunggu', 'Sedang Diperiksa'])
                ->first();

            if ($active) {
                \Filament\Notifications\Notification::make()
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
}
