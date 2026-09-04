<?php

namespace App\Filament\Resources\Pasiens\Pages;

use App\Filament\Resources\Pasiens\PasienResource;
use Daljo25\FilamentTablerIcons\Enums\TablerIcon;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewPasien extends ViewRecord
{
    protected static string $resource = PasienResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $this->record->loadMissing([
            'tempatLahir',
            'country',
            'village',
            'district',
            'regency',
            'province',
            'kontaks',
            'pendaftarans.poli',
            'pendaftarans.dokter',
        ]);
    }

    protected function getHeaderActions(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $isDokterOrPerawat = $user && (
            $user->hasRole(['Dokter', 'Perawat', 'Bidan']) ||
            ($user->pegawai && in_array($user->pegawai->profesi, ['Dokter', 'Perawat', 'Bidan']))
        );

        if ($isDokterOrPerawat) {
            return [];
        }

        $hasActive = \App\Models\Pendaftaran::where('pasien_id', $this->record->id)
            ->whereIn('status_pelayanan', \App\Models\Pendaftaran::ACTIVE_STATUSES)
            ->exists();

        return [
            Action::make('daftarkan')
                ->label('Daftarkan Pasien')
                ->icon(TablerIcon::UserPlus)
                ->disabled($hasActive)
                ->tooltip($hasActive ? 'Pasien ini masih memiliki antrian/pelayanan aktif yang belum selesai.' : 'Daftarkan Pasien')
                ->iconButton()
                ->size('md')
                ->extraAttributes([
                    'style' => 'margin-right: 6px !important;',
                    'class' => $hasActive
                        ? '!bg-gray-100 dark:!bg-gray-800/90 !text-orange-500/70 dark:!text-orange-400/70 border border-gray-200/90 dark:border-gray-700/80 rounded-lg p-1.5 opacity-60 shadow-sm cursor-not-allowed'
                        : '!bg-gray-100 hover:!bg-gray-200 dark:!bg-gray-800/90 dark:hover:!bg-gray-700/90 !text-orange-500 hover:!text-orange-600 dark:!text-orange-400 dark:hover:!text-orange-300 border border-gray-200/90 dark:border-gray-700/80 rounded-lg p-1.5 transition shadow-sm',
                ])
                ->url(fn () => $hasActive ? null : \App\Filament\Resources\Pendaftarans\PendaftaranResource::getUrl('create', ['pasien_id' => $this->record->id])),

            Action::make('history')
                ->label('History Pendaftaran')
                ->icon(TablerIcon::History)
                ->tooltip('History Pendaftaran')
                ->iconButton()
                ->size('md')
                ->extraAttributes([
                    'style' => 'margin-right: 6px !important;',
                    'class' => '!bg-gray-100 hover:!bg-gray-200 dark:!bg-gray-800/90 dark:hover:!bg-gray-700/90 !text-purple-600 dark:!text-purple-400 border border-gray-200/90 dark:border-gray-700/80 rounded-lg p-1.5 transition shadow-sm',
                ])
                ->modalHeading(fn () => "History Pendaftaran Kunjungan Pasien {$this->record->nama} ({$this->record->no_rm})")
                ->modalWidth('7xl')
                ->modalContent(fn () => view('filament.infolists.components.history-pendaftaran', ['record' => $this->record]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),

            EditAction::make()
                ->label('Edit Pasien')
                ->icon(TablerIcon::Edit)
                ->tooltip('Edit Pasien')
                ->iconButton()
                ->size('md')
                ->extraAttributes([
                    'class' => '!bg-gray-100 hover:!bg-gray-200 dark:!bg-gray-800/90 dark:hover:!bg-gray-700/90 !text-blue-600 dark:!text-blue-400 border border-gray-200/90 dark:border-gray-700/80 rounded-lg p-1.5 transition shadow-sm',
                ]),
        ];
    }
}
