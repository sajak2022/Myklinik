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
        $poliId = $record->poli_id;

        // Cari seluruh user Perawat & Bidan untuk poli terkait (atau seluruh perawat jika poli belum diset)
        $perawatUsers = \App\Models\User::where(function ($query) {
            $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['Perawat', 'Bidan']))
                ->orWhereHas('pegawai', fn ($q) => $q->whereIn('profesi', ['Perawat', 'Bidan']));
        })
        ->when($poliId, function ($query) use ($poliId) {
            $query->whereHas('pegawai', function ($q) use ($poliId) {
                $q->where('poli_id', $poliId);
            });
        })
        ->get();

        // Jika tidak ada perawat khusus pada poli terkait, kirimkan ke semua perawat
        if ($perawatUsers->isEmpty()) {
            $perawatUsers = \App\Models\User::where(function ($query) {
                $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['Perawat', 'Bidan']))
                    ->orWhereHas('pegawai', fn ($q) => $q->whereIn('profesi', ['Perawat', 'Bidan']));
            })->get();
        }

        // Kirimkan notifikasi ke database user Perawat
        if ($perawatUsers->isNotEmpty()) {
            Notification::make()
                ->title('Pasien Masuk Baru')
                ->body("Pasien {$pasienNama} (No. Antrian: {$noAntrian}) telah terdaftar di {$poliNama}. Silakan lakukan pemeriksaan awal.")
                ->icon('heroicon-o-user-plus')
                ->iconColor('info')
                ->info()
                ->actions([
                    \Filament\Actions\Action::make('terima')
                        ->button()
                        ->label('Lihat Pengunjung')
                        ->url(\App\Filament\Resources\Pendaftarans\PendaftaranResource::getUrl('index')),
                ])
                ->sendToDatabase($perawatUsers);
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
