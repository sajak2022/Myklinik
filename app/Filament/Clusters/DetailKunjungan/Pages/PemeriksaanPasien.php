<?php

namespace App\Filament\Clusters\DetailKunjungan\Pages;

use App\Filament\Clusters\DetailKunjungan;
use App\Models\PemeriksaanFisik;
use App\Models\Pendaftaran;
use App\Models\User;
use BackedEnum;
use Daljo25\FilamentTablerIcons\Enums\TablerIcon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class PemeriksaanPasien extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $cluster = DetailKunjungan::class;

    protected static string|BackedEnum|null $navigationIcon = TablerIcon::Stethoscope;

    protected static ?string $navigationLabel = 'Pemeriksaan';

    protected static ?string $title = 'Pemeriksaan';

    protected static ?string $slug = 'pemeriksaan-pasien/{record?}';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.pemeriksaan-pasien';

    public ?int $record = null;
    public ?Pendaftaran $pendaftaran = null;
    public ?array $data = [];

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getActions(): array
    {
        return [
            $this->selesaikanPelayananAction(),
            $this->batalkanFinalAction(),
            $this->batalkanPendaftaranAction(),
            $this->teruskanKeDokterAction(),
        ];
    }

    #[On('trigger-batalkan-pendaftaran')]
    public function triggerBatalkanPendaftaran(): void
    {
        $this->mountAction('batalkanPendaftaran');
    }

    #[On('trigger-selesaikan-pelayanan')]
    public function triggerSelesaikanPelayanan(): void
    {
        if (! $this->pendaftaran && $this->record) {
            $this->pendaftaran = Pendaftaran::find($this->record);
        }

        if (! $this->pendaftaran) {
            Notification::make()
                ->title('Tidak Ada Pasien')
                ->body('Tidak ada data kunjungan pasien aktif yang dipilih.')
                ->warning()
                ->send();
            return;
        }

        if (in_array($this->pendaftaran->status_pelayanan, [Pendaftaran::STATUS_FINAL, 'Selesai'])) {
            Notification::make()
                ->title('Peringatan: Pelayanan Sudah Final')
                ->body("Pelayanan untuk pasien {$this->pendaftaran->pasien?->nama} ({$this->pendaftaran->no_pendaftaran}) sudah berstatus Final.")
                ->warning()
                ->send();
            return;
        }

        $this->mountAction('selesaikanPelayanan');
    }

    #[On('trigger-batalkan-final')]
    public function triggerBatalkanFinal(): void
    {
        if (! $this->pendaftaran && $this->record) {
            $this->pendaftaran = Pendaftaran::find($this->record);
        }

        if (! $this->pendaftaran) {
            Notification::make()
                ->title('Tidak Ada Pasien')
                ->body('Tidak ada data kunjungan pasien aktif yang dipilih.')
                ->warning()
                ->send();
            return;
        }

        if (! in_array($this->pendaftaran->status_pelayanan, [Pendaftaran::STATUS_FINAL, 'Selesai'])) {
            Notification::make()
                ->title('Pasien Belum Final')
                ->body("Pelayanan pasien {$this->pendaftaran->pasien?->nama} belum berstatus Final (Status saat ini: {$this->pendaftaran->status_pelayanan}).")
                ->warning()
                ->send();
            return;
        }

        $this->mountAction('batalkanFinal');
    }

    public function selesaikanPelayananAction(): Action
    {
        return Action::make('selesaikanPelayanan')
            ->label('Selesaikan Pelayanan')
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->modalIcon('heroicon-o-check-circle')
            ->modalHeading('Selesaikan Pelayanan Pasien (Final)?')
            ->modalDescription(fn () => "Apakah Anda yakin ingin menyelesaikan pelayanan untuk pasien {$this->pendaftaran?->pasien?->nama} ({$this->pendaftaran?->no_pendaftaran})? Status kunjungan akan berubah menjadi Final.")
            ->modalSubmitActionLabel('Ya, Selesaikan')
            ->action(function () {
                if (! $this->pendaftaran && $this->record) {
                    $this->pendaftaran = Pendaftaran::find($this->record);
                }

                if (! $this->pendaftaran) {
                    return;
                }

                if (in_array($this->pendaftaran->status_pelayanan, [Pendaftaran::STATUS_FINAL, 'Selesai'])) {
                    Notification::make()
                        ->title('Peringatan: Pelayanan Sudah Final')
                        ->body("Pelayanan untuk pasien {$this->pendaftaran->pasien?->nama} ({$this->pendaftaran->no_pendaftaran}) sudah berstatus Final.")
                        ->warning()
                        ->send();
                    return;
                }

                $this->pendaftaran->finalkanPelayanan();
                session()->forget('active_pendaftaran_id');

                Notification::make()
                    ->title('Pelayanan Final')
                    ->body("Pelayanan untuk pasien {$this->pendaftaran->pasien?->nama} telah berhasil difinalkan.")
                    ->success()
                    ->send();

                $this->redirect(\App\Filament\Pages\KunjunganPasien::getUrl());
            });
    }

    public function batalkanFinalAction(): Action
    {
        return Action::make('batalkanFinal')
            ->label('Batalkan Final')
            ->icon('heroicon-m-arrow-uturn-left')
            ->color('warning')
            ->requiresConfirmation()
            ->modalIcon('heroicon-o-exclamation-triangle')
            ->modalHeading('Batalkan Status Final Pasien?')
            ->modalDescription(fn () => "Status pelayanan pasien {$this->pendaftaran?->pasien?->nama} saat ini adalah Final. Apakah Anda yakin ingin membatalkan status Final dan mengembalikannya ke status pemeriksaan aktif (Sedang Diperiksa)?")
            ->modalSubmitActionLabel('Ya, Batalkan Final')
            ->action(function () {
                if (! $this->pendaftaran && $this->record) {
                    $this->pendaftaran = Pendaftaran::find($this->record);
                }

                if (! $this->pendaftaran) {
                    return;
                }

                $this->pendaftaran->batalkanFinalPelayanan();
                session(['active_pendaftaran_id' => $this->pendaftaran->id]);

                Notification::make()
                    ->title('Status Final Dibatalkan')
                    ->body("Pelayanan untuk pasien {$this->pendaftaran->pasien?->nama} telah dikembalikan ke status pemeriksaan aktif (Sedang Diperiksa).")
                    ->warning()
                    ->send();

                $this->pendaftaran->refresh();
                $this->redirect(\App\Filament\Clusters\DetailKunjungan\Pages\PemeriksaanPasien::getUrl(['record' => $this->pendaftaran->id]));
            });
    }

    public function batalkanPendaftaranAction(): Action
    {
        return Action::make('batalkanPendaftaran')
            ->label('Batalkan Pendaftaran')
            ->icon('heroicon-m-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Batalkan Kunjungan Pasien?')
            ->modalDescription(fn () => "Apakah Anda yakin ingin membatalkan kunjungan pelayanan pasien {$this->pendaftaran?->pasien?->nama}?")
            ->modalSubmitActionLabel('Ya, Batalkan')
            ->action(function () {
                if (! $this->pendaftaran) {
                    return;
                }

                $this->pendaftaran->update(['status_pelayanan' => Pendaftaran::STATUS_BATAL]);
                session()->forget('active_pendaftaran_id');

                Notification::make()
                    ->title('Pendaftaran Dibatalkan')
                    ->body("Kunjungan pasien {$this->pendaftaran->pasien?->nama} telah dibatalkan.")
                    ->warning()
                    ->send();

                $this->redirect(\App\Filament\Pages\KunjunganPasien::getUrl());
            });
    }

    public function teruskanKeDokterAction(): Action
    {
        return Action::make('teruskanKeDokter')
            ->label('Kirim / Teruskan ke Dokter')
            ->icon('heroicon-m-paper-airplane')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Teruskan Pasien ke Dokter?')
            ->modalDescription(function () {
                $nama = $this->pendaftaran?->pasien?->nama ?? 'Pasien';
                $dokter = $this->pendaftaran?->dokter?->nama_lengkap ?? 'Dokter Pemeriksa';
                return "Apakah Anda yakin hasil pemeriksaan fisik (TTV) dan CPPT untuk pasien {$nama} telah lengkap dan siap diperiksa oleh {$dokter}?";
            })
            ->modalSubmitActionLabel('Ya, Teruskan ke Dokter')
            ->action(function () {
                if (! $this->pendaftaran) {
                    return;
                }

                if (! $this->pendaftaran->hasPemeriksaanFisik() || ! $this->pendaftaran->hasCppt()) {
                    Notification::make()
                        ->title('Pemeriksaan Belum Lengkap')
                        ->body('Perawat wajib mengisi Pemeriksaan Fisik (Tanda Vital) dan Catatan CPPT terlebih dahulu sebelum meneruskan pasien ke dokter.')
                        ->danger()
                        ->persistent()
                        ->send();
                    return;
                }

                $this->pendaftaran->update(['status_pelayanan' => Pendaftaran::STATUS_MENUNGGU_DOKTER]);
                session()->forget('active_pendaftaran_id');

                Notification::make()
                    ->title('Pasien Berhasil Diteruskan ke Dokter')
                    ->body("Pasien {$this->pendaftaran->pasien?->nama} telah diteruskan ke antrean dokter.")
                    ->success()
                    ->send();

                $this->redirect(\App\Filament\Pages\KunjunganPasien::getUrl());
            });
    }

    private function isDokterOrAdmin(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return (bool) ($user && (
            $user->hasRole(['super_admin', 'Admin', 'Dokter'])
            || $user->pegawai?->profesi === 'Dokter'
        ));
    }





    public static function getNavigationUrl(array $parameters = []): string
    {
        $recordId = request()->route('record') ?: request()->query('record') ?: session('active_pendaftaran_id');

        if ($recordId) {
            return static::getUrl(['record' => $recordId]);
        }

        return parent::getNavigationUrl();
    }

    public function mount($record = null): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        $isDokter = $user && ($user->hasRole('Dokter') || ($user->pegawai && $user->pegawai->profesi === 'Dokter'));
        $isPerawat = $user && ($user->hasRole(['Perawat', 'Bidan']) || ($user->pegawai && in_array($user->pegawai->profesi, ['Perawat', 'Bidan'])));
        $isAdmin = $user && ($user->hasRole(['super_admin', 'Admin']));

        $recordId = $record ?: request()->route('record') ?: request()->query('record') ?: session('active_pendaftaran_id');

        if ($recordId) {
            $this->record = (int) $recordId;
            $this->pendaftaran = Pendaftaran::with([
                'pasien.tempatLahir', 'pasien.pekerjaan', 'pasien.unitEksternal', 'pasien.subUnitEksternal',
                'poli', 'dokter', 'pemeriksaanFisiks', 'cpptRecords'
            ])->find($this->record);

            if ($this->pendaftaran) {
                session(['active_pendaftaran_id' => $this->pendaftaran->id]);
            } else {
                session()->forget('active_pendaftaran_id');
            }
        }

        // Jika belum ada record di URL/session, cari pendaftaran aktif terakhir
        if (! $this->pendaftaran) {
            $queryActive = Pendaftaran::query()
                ->with([
                    'pasien.tempatLahir', 'pasien.pekerjaan', 'pasien.unitEksternal', 'pasien.subUnitEksternal',
                    'poli', 'dokter', 'pemeriksaanFisiks', 'cpptRecords'
                ])
                ->latest('tanggal_pendaftaran')
                ->latest('id');

            $pegawai = $user?->pegawai;
            if ($pegawai && $pegawai->poli_id && ! $user->hasRole('super_admin')) {
                $queryActive->where('poli_id', $pegawai->poli_id);
            }

            if ($isDokter && ! $isAdmin) {
                $queryActive->whereIn('status_pelayanan', [Pendaftaran::STATUS_SEDANG_DIPERIKSA, Pendaftaran::STATUS_MENUNGGU_DOKTER]);
                if ($pegawai?->id) {
                    $queryActive->where('dokter_id', $pegawai->id);
                }
                $this->pendaftaran = $queryActive->first();
            } elseif ($isPerawat && ! $isAdmin) {
                $queryActive->whereIn('status_pelayanan', [Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT, Pendaftaran::STATUS_MENUNGGU]);
                $this->pendaftaran = $queryActive->first();
            } else {
                $this->pendaftaran = $queryActive->first();
            }

            // Jika masih belum ada, ambil data pendaftaran terakhir apapun statusnya agar form tetap tampil
            if (! $this->pendaftaran) {
                $this->pendaftaran = Pendaftaran::with([
                    'pasien.tempatLahir', 'pasien.pekerjaan', 'pasien.unitEksternal', 'pasien.subUnitEksternal',
                    'poli', 'dokter', 'pemeriksaanFisiks', 'cpptRecords'
                ])->latest('id')->first();
            }

            if ($this->pendaftaran) {
                $this->record = $this->pendaftaran->id;
                session(['active_pendaftaran_id' => $this->pendaftaran->id]);
            }
        }

        $this->form->fill([
            'pendaftaran_id'    => $this->record,
            'keadaan_umum'      => null,
            'tingkat_kesadaran' => 'Sadar Baik / Alert',
            'gcs_eye'           => 0,
            'gcs_motorik'       => 0,
            'gcs_verbal'        => 0,
            'gcs_total'         => 0,
            'sistolik'          => 0,
            'diastolik'         => 0,
            'frekuensi_nafas'   => 0,
            'frekuensi_nadi'    => 0,
            'suhu'              => 0,
            'saturasi_o2'       => 0,
            'alat_bantu_nafas'  => 'Tidak',
            'waktu_pemeriksaan' => now(),
            'skor_ewss'         => 0,
            'kategori_ewss'     => 'Normal',
            'catatan_tambahan'  => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Kesadaran & Keadaan Umum')
                    ->icon('heroicon-o-heart')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('keadaan_umum')
                            ->label('Keadaan Umum')
                            ->placeholder('Contoh: Baik / Sedang / Lemah')
                            ->default(null),

                        Select::make('tingkat_kesadaran')
                            ->label('Tingkat Kesadaran')
                            ->options([
                                'Sadar Baik / Alert' => 'Sadar Baik / Alert',
                                'Voice'              => 'Voice (Merespon Suara)',
                                'Pain'               => 'Pain (Merespon Nyeri)',
                                'Unresponsive'       => 'Unresponsive (Tidak Merespon)',
                                'Compos Mentis'      => 'Compos Mentis',
                                'Apatis'             => 'Apatis',
                                'Somnolen'           => 'Somnolen',
                                'Sopor'              => 'Sopor',
                                'Koma'               => 'Koma',
                            ])
                            ->default('Sadar Baik / Alert')
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateEwss($set, $get)),

                        DateTimePicker::make('waktu_pemeriksaan')
                            ->label('Waktu Pemeriksaan')
                            ->default(now())
                            ->seconds(false)
                            ->required(),
                    ]),

                Section::make('Glasgow Coma Scale (GCS)')
                    ->icon('heroicon-o-cpu-chip')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextInput::make('gcs_eye')
                            ->label('E (Eye)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(4)
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $set('gcs_total', (int) $get('gcs_eye') + (int) $get('gcs_motorik') + (int) $get('gcs_verbal'));
                                self::calculateEwss($set, $get);
                            }),

                        TextInput::make('gcs_motorik')
                            ->label('M (Motorik)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(6)
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $set('gcs_total', (int) $get('gcs_eye') + (int) $get('gcs_motorik') + (int) $get('gcs_verbal'));
                                self::calculateEwss($set, $get);
                            }),

                        TextInput::make('gcs_verbal')
                            ->label('V (Verbal)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $set('gcs_total', (int) $get('gcs_eye') + (int) $get('gcs_motorik') + (int) $get('gcs_verbal'));
                                self::calculateEwss($set, $get);
                            }),

                        TextInput::make('gcs_total')
                            ->label('GCS Total')
                            ->readOnly()
                            ->default(0)
                            ->extraInputAttributes(['class' => 'font-bold text-center']),
                    ]),

                Section::make('Tanda-Tanda Vital')
                    ->icon('heroicon-o-bolt')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        TextInput::make('sistolik')
                            ->label('TD Sistolik')
                            ->suffix('mmHg')
                            ->numeric()
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateEwss($set, $get)),

                        TextInput::make('diastolik')
                            ->label('TD Diastolik')
                            ->suffix('mmHg')
                            ->numeric()
                            ->default(0),

                        TextInput::make('frekuensi_nadi')
                            ->label('Frekuensi Nadi')
                            ->suffix('x/mnt')
                            ->numeric()
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateEwss($set, $get)),

                        TextInput::make('frekuensi_nafas')
                            ->label('Frekuensi Nafas')
                            ->suffix('x/mnt')
                            ->numeric()
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateEwss($set, $get)),

                        TextInput::make('suhu')
                            ->label('Suhu Tubuh')
                            ->suffix('°C')
                            ->numeric()
                            ->step(0.1)
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateEwss($set, $get)),

                        TextInput::make('saturasi_o2')
                            ->label('Saturasi O2')
                            ->suffix('%')
                            ->numeric()
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateEwss($set, $get)),

                        Select::make('alat_bantu_nafas')
                            ->label('Alat Bantu Nafas (Oksigen)')
                            ->options([
                                'Tidak' => 'Tidak (Udara Bebas)',
                                'Ya'    => 'Ya (Oksigen Tambahan)',
                            ])
                            ->default('Tidak')
                            ->selectablePlaceholder(false)
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateEwss($set, $get))
                            ->columnSpan(2),
                    ]),

                Section::make('Hasil Penilaian EWSS')
                    ->icon('heroicon-o-chart-bar')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('skor_ewss')
                            ->label('Skor EWSS')
                            ->readOnly()
                            ->default(0)
                            ->extraInputAttributes(['class' => 'text-lg font-black text-emerald-600 dark:text-emerald-400 text-center']),

                        TextInput::make('kategori_ewss')
                            ->label('Kategori Risiko')
                            ->readOnly()
                            ->default('Normal')
                            ->extraInputAttributes(['class' => 'font-bold text-center']),
                    ]),

                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('catatan_tambahan')
                            ->hiddenLabel()
                            ->rows(4)
                            ->placeholder('Catatan khusus kondisi pasien / keluhan fisik tambahan'),
                    ]),
            ]);
    }

    public static function calculateEwss(Set $set, Get $get): void
    {
        $skor = 0;

        // 1. Nafas
        $nafas = (int) $get('frekuensi_nafas');
        if ($nafas > 0) {
            if ($nafas <= 8) $skor += 3;
            elseif ($nafas <= 11) $skor += 1;
            elseif ($nafas <= 20) $skor += 0;
            elseif ($nafas <= 24) $skor += 2;
            else $skor += 3;
        }

        // 2. SpO2
        $spo2 = (int) $get('saturasi_o2');
        if ($spo2 > 0) {
            if ($spo2 <= 91) $skor += 3;
            elseif ($spo2 <= 93) $skor += 2;
            elseif ($spo2 <= 95) $skor += 1;
            else $skor += 0;
        }

        // 3. Oksigen
        if ($get('alat_bantu_nafas') === 'Ya') {
            $skor += 2;
        }

        // 4. Sistolik
        $sis = (int) $get('sistolik');
        if ($sis > 0) {
            if ($sis <= 90) $skor += 3;
            elseif ($sis <= 100) $skor += 2;
            elseif ($sis <= 110) $skor += 1;
            elseif ($sis <= 219) $skor += 0;
            else $skor += 3;
        }

        // 5. Nadi
        $nadi = (int) $get('frekuensi_nadi');
        if ($nadi > 0) {
            if ($nadi <= 40) $skor += 3;
            elseif ($nadi <= 50) $skor += 1;
            elseif ($nadi <= 90) $skor += 0;
            elseif ($nadi <= 110) $skor += 1;
            elseif ($nadi <= 130) $skor += 2;
            else $skor += 3;
        }

        // 6. Kesadaran
        $kesadaran = strtolower(trim((string) $get('tingkat_kesadaran')));
        if (! in_array($kesadaran, ['sadar baik / alert', 'alert', 'compos mentis', 'composmentis', ''])) {
            $skor += 3;
        }

        // 7. Suhu
        $suhu = (float) $get('suhu');
        if ($suhu > 0) {
            if ($suhu <= 35.0) $skor += 3;
            elseif ($suhu <= 36.0) $skor += 1;
            elseif ($suhu <= 38.0) $skor += 0;
            elseif ($suhu <= 39.0) $skor += 1;
            else $skor += 2;
        }

        $kategori = match (true) {
            $skor === 0 => 'Normal',
            $skor <= 4  => 'Rendah',
            $skor <= 6  => 'Sedang',
            default     => 'Tinggi',
        };

        $set('skor_ewss', $skor);
        $set('kategori_ewss', $kategori);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                if (! $this->pendaftaran) {
                    return PemeriksaanFisik::query()->whereNull('id');
                }
                return PemeriksaanFisik::query()->where('pendaftaran_id', $this->pendaftaran->id)->latest('waktu_pemeriksaan');
            })
            ->heading('Riwayat Pemeriksaan Fisik Kunjungan Ini')
            ->columns([
                TextColumn::make('waktu_pemeriksaan')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->weight('medium')
                    ->description(fn (PemeriksaanFisik $record) => $record->alat_bantu_nafas ? 'O2 Bantu: Ya' : null),

                TextColumn::make('keadaan_umum')
                    ->label('Keadaan / Kesadaran')
                    ->weight('medium')
                    ->description(fn (PemeriksaanFisik $record) => "{$record->tingkat_kesadaran} (GCS: {$record->gcs_total})")
                    ->wrap(),

                TextColumn::make('tekanan_darah')
                    ->label('Tekanan Darah')
                    ->getStateUsing(fn (PemeriksaanFisik $record) => "{$record->sistolik}/{$record->diastolik} mmHg")
                    ->alignCenter(),

                TextColumn::make('frekuensi_nadi')
                    ->label('Nadi / Nafas')
                    ->getStateUsing(fn (PemeriksaanFisik $record) => "{$record->frekuensi_nadi} x/m")
                    ->description(fn (PemeriksaanFisik $record) => "Nafas: {$record->frekuensi_nafas} x/m")
                    ->alignCenter(),

                TextColumn::make('suhu')
                    ->label('Suhu / SpO2')
                    ->getStateUsing(fn (PemeriksaanFisik $record) => "{$record->suhu} °C")
                    ->description(fn (PemeriksaanFisik $record) => "SpO2: {$record->saturasi_o2}%")
                    ->alignCenter(),

                TextColumn::make('skor_ewss')
                    ->label('EWSS')
                    ->badge()
                    ->color(fn (PemeriksaanFisik $record) => match ($record->kategori_ewss) {
                        'Normal'  => 'success',
                        'Rendah'  => 'warning',
                        'Sedang'  => 'orange',
                        default   => 'danger',
                    })
                    ->formatStateUsing(fn (PemeriksaanFisik $record) => "{$record->skor_ewss} ({$record->kategori_ewss})")
                    ->alignCenter(),
            ])
            ->actions([
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Hapus Pemeriksaan'),
            ]);
    }

    public function simpan(): void
    {
        if (! $this->pendaftaran) {
            Notification::make()
                ->title('Pilih Pasien Terlebih Dahulu')
                ->warning()
                ->send();
            return;
        }

        $state = $this->form->getState();

        /** @var User|null $user */
        $user = Auth::user();
        $pegawaiId = $user?->pegawai?->id;

        PemeriksaanFisik::create([
            'pendaftaran_id'    => $this->pendaftaran->id,
            'pasien_id'         => $this->pendaftaran->pasien_id,
            'pegawai_id'        => $pegawaiId,
            'keadaan_umum'      => $state['keadaan_umum'] ?? null,
            'tingkat_kesadaran' => $state['tingkat_kesadaran'] ?? 'Sadar Baik / Alert',
            'gcs_eye'           => (int) ($state['gcs_eye'] ?? 0),
            'gcs_motorik'       => (int) ($state['gcs_motorik'] ?? 0),
            'gcs_verbal'        => (int) ($state['gcs_verbal'] ?? 0),
            'gcs_total'         => (int) ($state['gcs_total'] ?? 0),
            'sistolik'          => (int) ($state['sistolik'] ?? 0),
            'diastolik'         => (int) ($state['diastolik'] ?? 0),
            'frekuensi_nafas'   => (int) ($state['frekuensi_nafas'] ?? 0),
            'frekuensi_nadi'    => (int) ($state['frekuensi_nadi'] ?? 0),
            'suhu'              => (float) ($state['suhu'] ?? 0),
            'saturasi_o2'       => (int) ($state['saturasi_o2'] ?? 0),
            'alat_bantu_nafas'  => ($state['alat_bantu_nafas'] ?? 'Tidak') === 'Ya',
            'skor_ewss'         => (int) ($state['skor_ewss'] ?? 0),
            'kategori_ewss'     => $state['kategori_ewss'] ?? 'Normal',
            'waktu_pemeriksaan' => $state['waktu_pemeriksaan'] ?? now(),
            'catatan_tambahan'  => $state['catatan_tambahan'] ?? null,
        ]);

        $this->pendaftaran->load('pemeriksaanFisiks');
        $this->teruskanOtomatisJikaLengkap();

        $this->form->fill([
            'pendaftaran_id'    => $this->record,
            'keadaan_umum'      => null,
            'tingkat_kesadaran' => 'Sadar Baik / Alert',
            'gcs_eye'           => 0,
            'gcs_motorik'       => 0,
            'gcs_verbal'        => 0,
            'gcs_total'         => 0,
            'sistolik'          => 0,
            'diastolik'         => 0,
            'frekuensi_nafas'   => 0,
            'frekuensi_nadi'    => 0,
            'suhu'              => 0,
            'saturasi_o2'       => 0,
            'alat_bantu_nafas'  => 'Tidak',
            'waktu_pemeriksaan' => now(),
            'skor_ewss'         => 0,
            'kategori_ewss'     => 'Normal',
            'catatan_tambahan'  => null,
        ]);

        Notification::make()
            ->title('Pemeriksaan Berhasil Disimpan')
            ->body('Data tanda-tanda vital dan pemeriksaan fisik pasien telah tersimpan.')
            ->success()
            ->send();
    }

    private function teruskanOtomatisJikaLengkap(): void
    {
        $this->pendaftaran->load('cpptRecords');

        if ($this->pendaftaran->isSiapUntukDokter()
            && in_array($this->pendaftaran->status_pelayanan, [Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT, Pendaftaran::STATUS_MENUNGGU], true)) {
            $this->pendaftaran->update(['status_pelayanan' => Pendaftaran::STATUS_MENUNGGU_DOKTER]);
        }
    }

    public function pilihKunjungan(int $pendaftaranId): void
    {
        $this->record = $pendaftaranId;
        $this->pendaftaran = Pendaftaran::with(['pasien.tempatLahir', 'poli', 'dokter', 'pemeriksaanFisiks'])->find($pendaftaranId);
        if ($this->pendaftaran) {
            session(['active_pendaftaran_id' => $this->pendaftaran->id]);
        }
    }

    public function getDaftarKunjunganProperty()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $query = Pendaftaran::query()
            ->with(['pasien', 'poli', 'dokter'])
            ->latest('tanggal_pendaftaran');

        if ($user && ! $user->hasRole('super_admin')) {
            $pegawai = $user->pegawai;
            if ($pegawai && $pegawai->poli_id) {
                $query->where('poli_id', $pegawai->poli_id);
            }
        }

        return $query->take(20)->get();
    }

    public function getCatatanMedisSebelumnyaProperty()
    {
        if (! $this->pendaftaran || ! $this->pendaftaran->pasien_id) {
            return null;
        }

        return PemeriksaanFisik::query()
            ->with(['pendaftaran', 'pegawai'])
            ->where('pasien_id', $this->pendaftaran->pasien_id)
            ->latest('waktu_pemeriksaan')
            ->paginate(5, ['*'], 'catatanPage');
    }

    public function getHasPemeriksaanFisikProperty(): bool
    {
        return $this->pendaftaran ? $this->pendaftaran->hasPemeriksaanFisik() : false;
    }

    public function getHasCpptProperty(): bool
    {
        return $this->pendaftaran ? $this->pendaftaran->hasCppt() : false;
    }

    public function getIsSiapUntukDokterProperty(): bool
    {
        return $this->pendaftaran ? $this->pendaftaran->isSiapUntukDokter() : false;
    }
}

