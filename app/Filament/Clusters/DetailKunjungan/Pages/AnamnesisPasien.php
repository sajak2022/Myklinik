<?php

namespace App\Filament\Clusters\DetailKunjungan\Pages;

use App\Filament\Clusters\DetailKunjungan;
use App\Models\AnamnesisPasien as AnamnesisModel;
use App\Models\Pendaftaran;
use App\Models\User;
use BackedEnum;
use Daljo25\FilamentTablerIcons\Enums\TablerIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AnamnesisPasien extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $cluster = DetailKunjungan::class;

    protected static string|BackedEnum|null $navigationIcon = TablerIcon::ClipboardText;

    protected static ?string $navigationLabel = 'Anamnesis';

    protected static ?string $title = 'Anamnesis';

    protected static ?string $slug = 'anamnesis/{record?}';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.anamnesis-pasien';

    public ?int $record = null;
    public ?Pendaftaran $pendaftaran = null;
    public ?array $data = [];
    public ?int $editingAnamnesisId = null;

    // Active tab tracking for dynamic right panel
    public string $activeTab = 'keluhan_utama';

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

        $recordId = $record ?: request()->route('record') ?: request()->query('record') ?: session('active_pendaftaran_id');

        if ($recordId) {
            $this->record = (int) $recordId;
            $this->pendaftaran = Pendaftaran::with([
                'pasien.tempatLahir', 'pasien.unitEksternal', 'pasien.subUnitEksternal',
                'poli', 'dokter', 'anamnesisRecords', 'cpptRecords'
            ])->find($this->record);

            if ($this->pendaftaran) {
                session(['active_pendaftaran_id' => $this->pendaftaran->id]);
            } else {
                session()->forget('active_pendaftaran_id');
            }
        } else {
            $this->record = null;
            $this->pendaftaran = null;
            session()->forget('active_pendaftaran_id');
        }

        $this->isiDefaultForm();
    }

    public function isiDefaultForm(): void
    {
        $this->editingAnamnesisId = null;

        $existing = $this->record
            ? AnamnesisModel::where('pendaftaran_id', $this->record)->latest('waktu_anamnesis')->first()
            : null;

        if ($existing) {
            $this->editingAnamnesisId = $existing->id;
            $this->form->fill($existing->toArray());
        } else {
            $riwayatLama = $this->pendaftaran?->pasien_id
                ? AnamnesisModel::where('pasien_id', $this->pendaftaran->pasien_id)->latest('waktu_anamnesis')->first()
                : null;

            $this->form->fill([
                'pendaftaran_id'             => $this->record,
                'waktu_anamnesis'            => now(),
                'sumber_anamnesis'           => 'Autoanamnesis',
                'nama_sumber_informasi'      => null,
                'hubungan_sumber_informasi'  => null,
                'keluhan_utama'              => null,
                'riwayat_penyakit_sekarang'  => null,
                'riwayat_penyakit_dahulu'    => $riwayatLama?->riwayat_penyakit_dahulu ?? 'tidak ada',
                'riwayat_penyakit_keluarga'  => $riwayatLama?->riwayat_penyakit_keluarga ?? null,
                'riwayat_alergi'             => $riwayatLama?->riwayat_alergi ?? null,
                'riwayat_pengobatan'         => $riwayatLama?->riwayat_pengobatan ?? null,
                'status_fungsional'          => 'Mandiri',
                'alat_bantu_array'           => [],
                'alat_bantu_lainnya'         => 'tidak ada',
                'cacat_tubuh_pilihan'        => 'Tidak Ada',
                'cacat_tubuh_keterangan'     => null,
                'status_psikologis'          => null,
                'status_mental'              => null,
                'hubungan_keluarga'          => null,
                'tinggal_bersama'            => null,
                'nilai_kepercayaan_agama'    => null,
                'kesediaan_menerima_edukasi' => null,
                'ada_hambatan_edukasi'       => null,
                'butuh_penerjemah'           => null,
                'kebutuhan_edukasi'          => [],
                'hambatan_belajar'           => [],
                'penerima_edukasi'           => null,
                'penurunan_bb'               => 'Tidak ada penurunan BB (skor 0)',
                'skor_penurunan_bb'          => 0,
                'asupan_makan_berkurang'     => 'Tidak (skor 0)',
                'skor_asupan_makan'          => 0,
                'kondisi_khusus_gizi'        => null,
                'total_skor_gizi'            => 0,
                'kategori_gizi'              => 'Risiko Rendah',
                'skrining_batuk'             => [],
                'skrining_batuk_keterangan'  => null,
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('AnamnesisTabs')
                    ->columnSpanFull()
                    ->tabs([
                        // TAB 1: KELUHAN UTAMA
                        Tab::make('Keluhan Utama')
                            ->schema([
                                Textarea::make('keluhan_utama')
                                    ->label('Keluhan Utama:')
                                    ->placeholder('Tuliskan keluhan utama pasien...')
                                    ->rows(6)
                                    ->required()
                                    ->columnSpanFull(),
                            ]),

                        // TAB 2: ANAMNESIS DIPEROLEH
                        Tab::make('Anamnesis Diperoleh')
                            ->schema([
                                Radio::make('sumber_anamnesis')
                                    ->label('Anamnesis Diperoleh:')
                                    ->options([
                                        'Autoanamnesis' => 'Autoanamnesis',
                                        'Alloanamnesis' => 'Alloanamnesis',
                                    ])
                                    ->default('Autoanamnesis')
                                    ->inline()
                                    ->live()
                                    ->required(),

                                Textarea::make('nama_sumber_informasi')
                                    ->label('Dari:')
                                    ->placeholder('Nama / Keterangan sumber informasi jika Alloanamnesis...')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),

                        // TAB 3: RIWAYAT PENYAKIT SEKARANG (UMUM)
                        Tab::make('Riwayat Penyakit Sekarang (Umum)')
                            ->schema([
                                Textarea::make('riwayat_penyakit_sekarang')
                                    ->label('Riwayat Penyakit Sekarang:')
                                    ->placeholder('Uraian riwayat penyakit sekarang...')
                                    ->rows(6)
                                    ->columnSpanFull(),
                            ]),

                        // TAB 4: RIWAYAT
                        Tab::make('Riwayat')
                            ->schema([
                                Textarea::make('riwayat_penyakit_dahulu')
                                    ->label('Riwayat Penyakit / Pengobatan / Alergi:')
                                    ->placeholder('Tulis riwayat penyakit dahulu, alergi, atau pengobatan...')
                                    ->rows(6)
                                    ->columnSpanFull(),
                            ]),

                        // TAB 5: STATUS FUNGSIONAL
                        Tab::make('Status Fungsional')
                            ->schema([
                                CheckboxList::make('alat_bantu_array')
                                    ->label('Penggunaan Alat Bantu:')
                                    ->options([
                                        'Tongkat'    => 'Tongkat',
                                        'Kursi Roda' => 'Kursi Roda',
                                        'Brankard'   => 'Brankard',
                                        'Walker'     => 'Walker',
                                    ])
                                    ->columns(4),

                                TextInput::make('alat_bantu_lainnya')
                                    ->label('Lainnya, sebutkan:')
                                    ->placeholder('tidak ada')
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->schema([
                                        Radio::make('cacat_tubuh_pilihan')
                                            ->label('Cacat Tubuh:')
                                            ->options([
                                                'Tidak Ada' => 'Tidak Ada',
                                                'Ada'       => 'Ada',
                                            ])
                                            ->default('Tidak Ada')
                                            ->inline()
                                            ->live(),

                                        TextInput::make('cacat_tubuh_keterangan')
                                            ->label('Sebutkan jika ada:')
                                            ->placeholder('Keterangan cacat tubuh...')
                                            ->visible(fn (Get $get) => $get('cacat_tubuh_pilihan') === 'Ada'),
                                    ]),
                            ]),

                        // TAB 6: HUBUNGAN STATUS PSIKOSOSIAL
                        Tab::make('Hubungan Status Psikososial')
                            ->schema([]),

                        // TAB 7: EDUKASI
                        Tab::make('Edukasi')
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        Radio::make('kesediaan_menerima_edukasi')
                                            ->label('Kesediaan pasien/keluarga menerima informasi:')
                                            ->options([
                                                'Tidak' => 'Tidak',
                                                'Ya'    => 'Ya',
                                            ])
                                            ->inline(),

                                        Radio::make('ada_hambatan_edukasi')
                                            ->label('Terdapat Hambatan dalam edukasi:')
                                            ->options([
                                                'Tidak' => 'Tidak',
                                                'Ya'    => 'Ya',
                                            ])
                                            ->inline(),

                                        Radio::make('butuh_penerjemah')
                                            ->label('Dibutuhkan penerjemah (jika ya, sebutkan):')
                                            ->options([
                                                'Tidak' => 'Tidak',
                                                'Ya'    => 'Ya',
                                            ])
                                            ->inline(),
                                    ]),

                                CheckboxList::make('kebutuhan_edukasi')
                                    ->label('Kebutuhan Edukasi:')
                                    ->options([
                                        'Kondisi kesehatan dan diagnosa pasti dan penatalaksanaannya' => 'Kondisi kesehatan dan diagnosa pasti dan penatalaksanaannya',
                                        'Penggunaan obat secara efektif dan efek samping interaksinya' => 'Penggunaan obat secara efektif dan efek samping interaksinya',
                                        'Diet dan Nutrisi'                                             => 'Diet dan Nutrisi',
                                        'Teknik rehabilitasi'                                          => 'Teknik rehabilitasi',
                                        'Manajemen Nyeri'                                              => 'Manajemen Nyeri',
                                        'Penggunaan alat medis yang aman'                              => 'Penggunaan alat medis yang aman',
                                        'Hak dan Kewajiban Pasien'                                     => 'Hak dan Kewajiban Pasien',
                                        'Hak untuk berpartisipasi pada pelayanan'                      => 'Hak untuk berpartisipasi pada pelayanan',
                                        'Prosedur pemeriksaan penunjang'                               => 'Prosedur pemeriksaan penunjang',
                                        'Proses pemberian informed consent'                            => 'Proses pemberian informed consent',
                                        'Penundaan pelayanan'                                          => 'Penundaan pelayanan',
                                        'Keberatan pelayanan'                                          => 'Keberatan pelayanan',
                                        'Cuci tangan dengan benar'                                     => 'Cuci tangan dengan benar',
                                        'Bahaya merokok'                                               => 'Bahaya merokok',
                                        'Edukasi rujukan pasien'                                       => 'Edukasi rujukan pasien',
                                        'Edukasi perencanaan pulang'                                   => 'Edukasi perencanaan pulang',
                                        'Lain-lainnya'                                                 => 'Lain-lainnya',
                                    ])
                                    ->columns(3),
                            ]),

                        // TAB 8: SKRINING GIZI AWAL
                        Tab::make('Skrining Gizi Awal')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('penurunan_bb')
                                            ->label('1. Apakah ada penurunan berat badan yang tidak diinginkan dalam 6 bulan terakhir?')
                                            ->options([
                                                'Tidak ada penurunan BB (skor 0)'         => 'Tidak ada penurunan BB (Skor 0)',
                                                'Ya ada penurunan 1 - 5 kg (skor 1)'       => 'Ya, turun 1 - 5 kg (Skor 1)',
                                                'Ya ada penurunan 6 - 10 kg (skor 2)'      => 'Ya, turun 6 - 10 kg (Skor 2)',
                                                'Ya ada penurunan 11 - 15 kg (skor 3)'     => 'Ya, turun 11 - 15 kg (Skor 3)',
                                                'Ya ada penurunan > 15 kg (skor 4)'        => 'Ya, turun > 15 kg (Skor 4)',
                                                'Tidak tahu / ragu (skor 2)'               => 'Ragu-ragu / Tidak Tahu (Skor 2)',
                                            ])
                                            ->default('Tidak ada penurunan BB (skor 0)')
                                            ->native(false)
                                            ->live()
                                            ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::hitungSkorGizi($set, $get)),

                                        Select::make('asupan_makan_berkurang')
                                            ->label('2. Apakah asupan makan berkurang karena tidak nafsu makan / mual / muntah?')
                                            ->options([
                                                'Tidak (skor 0)' => 'Tidak (Skor 0)',
                                                'Ya (skor 1)'    => 'Ya, asupan makan berkurang (Skor 1)',
                                            ])
                                            ->default('Tidak (skor 0)')
                                            ->native(false)
                                            ->live()
                                            ->afterStateUpdated(fn ($state, Set $set, Get $get) => self::hitungSkorGizi($set, $get)),

                                        TextInput::make('total_skor_gizi')
                                            ->label('Total Skor MST')
                                            ->disabled()
                                            ->dehydrated()
                                            ->numeric(),

                                        TextInput::make('kategori_gizi')
                                            ->label('Kategori Risiko Malnutrisi')
                                            ->disabled()
                                            ->dehydrated(),
                                    ]),
                            ]),

                        // TAB 9: BATUK
                        Tab::make('Batuk')
                            ->schema([
                                CheckboxList::make('skrining_batuk')
                                    ->label('Skrining Gejala Batuk & Tuberkulosis (TB):')
                                    ->options([
                                        'Batuk berdahak >= 2 minggu'          => 'Batuk berdahak >= 2 minggu',
                                        'Batuk berdarah'                      => 'Batuk berdarah',
                                        'Demam meriang berkepanjangan'        => 'Demam meriang berkepanjangan',
                                        'Berkeringat malam tanpa aktivitas'   => 'Berkeringat malam hari tanpa aktivitas fisik',
                                        'Penurunan berat badan tanpa sebab'   => 'Penurunan berat badan / nafsu makan menurun drastis',
                                        'Riwayat kontak erat pasien TB'       => 'Riwayat kontak erat dengan penderita TB aktif',
                                    ])
                                    ->columns(2),

                                Textarea::make('skrining_batuk_keterangan')
                                    ->label('Keterangan / Catatan Skrining TB:')
                                    ->placeholder('Tuliskan catatan tambahan jika ada...')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function hitungSkorGizi(Set $set, Get $get): void
    {
        $bb = $get('penurunan_bb') ?? '';
        $skorBb = 0;
        if (str_contains($bb, 'skor 1')) {
            $skorBb = 1;
        } elseif (str_contains($bb, 'skor 2')) {
            $skorBb = 2;
        } elseif (str_contains($bb, 'skor 3')) {
            $skorBb = 3;
        } elseif (str_contains($bb, 'skor 4')) {
            $skorBb = 4;
        }

        $makan = $get('asupan_makan_berkurang') ?? '';
        $skorMakan = str_contains($makan, 'skor 1') ? 1 : 0;

        $total = $skorBb + $skorMakan;
        $kategori = $total >= 2 ? 'Risiko Tinggi (Perlu Konsultasi Gizi)' : 'Risiko Rendah (Skor 0-1)';

        $set('skor_penurunan_bb', $skorBb);
        $set('skor_asupan_makan', $skorMakan);
        $set('total_skor_gizi', $total);
        $set('kategori_gizi', $kategori);
    }

    public function simpan(): void
    {
        if (! $this->pendaftaran) {
            Notification::make()
                ->danger()
                ->title('Gagal Menyimpan')
                ->body('Tidak ada data kunjungan pasien yang aktif.')
                ->send();
            return;
        }

        $data = $this->form->getState();

        $bb = $data['penurunan_bb'] ?? '';
        $skorBb = 0;
        if (str_contains($bb, 'skor 1')) $skorBb = 1;
        elseif (str_contains($bb, 'skor 2')) $skorBb = 2;
        elseif (str_contains($bb, 'skor 3')) $skorBb = 3;
        elseif (str_contains($bb, 'skor 4')) $skorBb = 4;

        $makan = $data['asupan_makan_berkurang'] ?? '';
        $skorMakan = str_contains($makan, 'skor 1') ? 1 : 0;
        $totalSkorGizi = $skorBb + $skorMakan;
        $kategoriGizi = $totalSkorGizi >= 2 ? 'Risiko Tinggi' : 'Risiko Rendah';

        $data['pendaftaran_id']    = $this->pendaftaran->id;
        $data['pasien_id']         = $this->pendaftaran->pasien_id;
        $data['user_id']           = Auth::id();
        $data['pegawai_id']        = Auth::user()?->pegawai_id;
        $data['waktu_anamnesis']   = $data['waktu_anamnesis'] ?? now();
        $data['skor_penurunan_bb'] = $skorBb;
        $data['skor_asupan_makan'] = $skorMakan;
        $data['total_skor_gizi']   = $totalSkorGizi;
        $data['kategori_gizi']     = $kategoriGizi;

        if ($this->editingAnamnesisId) {
            $anamnesis = AnamnesisModel::find($this->editingAnamnesisId);
            if ($anamnesis) {
                $anamnesis->update($data);
            }
        } else {
            $anamnesis = AnamnesisModel::create($data);
            $this->editingAnamnesisId = $anamnesis->id;
        }

        if ($this->pendaftaran->status_pelayanan === Pendaftaran::STATUS_MENUNGGU) {
            $this->pendaftaran->update(['status_pelayanan' => Pendaftaran::STATUS_PEMERIKSAAN_PERAWAT]);
        }

        Notification::make()
            ->success()
            ->title('Berhasil Disimpan')
            ->body('Data anamnesis pasien berhasil disimpan.')
            ->send();

        $this->pendaftaran->refresh();
    }

    public function getRiwayatSebelumnyaProperty()
    {
        if (! $this->pendaftaran || ! $this->pendaftaran->pasien_id) {
            return collect();
        }

        return Pendaftaran::with([
                'poli',
                'dokter',
                'petugas',
                'anamnesisRecords.pegawai',
                'anamnesisRecords.user.pegawai',
                'cpptRecords.pegawai',
                'cpptRecords.user'
            ])
            ->where('pasien_id', $this->pendaftaran->pasien_id)
            ->where('id', '!=', $this->pendaftaran->id)
            ->latest('tanggal_pendaftaran')
            ->take(10)
            ->get();
    }

    protected function selesaikanPelayananAction(): Action
    {
        return Action::make('selesaikanPelayanan')
            ->label('Selesaikan Pelayanan')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Selesaikan Pelayanan Pasien')
            ->modalDescription('Apakah Anda yakin ingin menyelesaikan pelayanan pasien ini? Status kunjungan akan diubah menjadi Final/Selesai.')
            ->modalSubmitActionLabel('Ya, Selesaikan')
            ->action(function () {
                if (! $this->pendaftaran) return;
                $this->pendaftaran->selesaikanPelayanan();
                Notification::make()->success()->title('Pelayanan Selesai')->body('Pelayanan pasien berhasil diselesaikan.')->send();
                $this->redirect(\App\Filament\Pages\KunjunganPasien::getUrl());
            });
    }

    protected function batalkanFinalAction(): Action
    {
        return Action::make('batalkanFinal')
            ->label('Batalkan Status Final')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Batalkan Status Final')
            ->modalDescription('Apakah Anda yakin ingin membatalkan status final? Status akan dikembalikan menjadi Sedang Diperiksa.')
            ->modalSubmitActionLabel('Ya, Batalkan Final')
            ->action(function () {
                if (! $this->pendaftaran) return;
                $this->pendaftaran->batalkanFinalPelayanan();
                Notification::make()->info()->title('Status Final Dibatalkan')->send();
                $this->pendaftaran->refresh();
            });
    }

    protected function batalkanPendaftaranAction(): Action
    {
        return Action::make('batalkanPendaftaran')
            ->label('Batalkan Pendaftaran')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Batalkan Pendaftaran Pasien')
            ->modalDescription('Apakah Anda yakin ingin membatalkan pendaftaran pasien ini?')
            ->modalSubmitActionLabel('Ya, Batalkan')
            ->action(function () {
                if (! $this->pendaftaran) return;
                $this->pendaftaran->batalkanPendaftaran();
                Notification::make()->danger()->title('Pendaftaran Dibatalkan')->send();
                $this->redirect(\App\Filament\Pages\KunjunganPasien::getUrl());
            });
    }

    protected function teruskanKeDokterAction(): Action
    {
        return Action::make('teruskanKeDokter')
            ->label('Teruskan ke Dokter')
            ->icon('heroicon-o-paper-airplane')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Teruskan Pasien ke Dokter')
            ->modalDescription('Apakah pemeriksaan awal perawat sudah selesai dan pasien siap diteruskan ke antrian dokter?')
            ->modalSubmitActionLabel('Ya, Teruskan ke Dokter')
            ->action(function () {
                if (! $this->pendaftaran) return;
                $this->pendaftaran->update(['status_pelayanan' => Pendaftaran::STATUS_MENUNGGU_DOKTER]);
                Notification::make()->success()->title('Diteruskan ke Dokter')->body('Pasien berhasil diteruskan ke antrian dokter.')->send();
                $this->pendaftaran->refresh();
            });
    }
}
