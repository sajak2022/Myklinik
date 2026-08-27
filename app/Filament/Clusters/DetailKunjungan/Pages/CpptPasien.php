<?php

namespace App\Filament\Clusters\DetailKunjungan\Pages;

use App\Filament\Clusters\DetailKunjungan;
use App\Models\CpptRecord;
use App\Models\Pendaftaran;
use App\Models\User;
use BackedEnum;
use Daljo25\FilamentTablerIcons\Enums\TablerIcon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class CpptPasien extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $cluster = DetailKunjungan::class;

    protected static string|BackedEnum|null $navigationIcon = TablerIcon::Notes;

    protected static ?string $navigationLabel = 'CPPT';

    protected static ?string $title = 'CPPT';

    protected static ?string $slug = 'cppt/{record?}';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.cppt-pasien';

    public ?int $record = null;
    public ?Pendaftaran $pendaftaran = null;
    public ?array $data = [];

    #[On('trigger-selesaikan-pelayanan')]
    public function triggerSelesaikanPelayanan(): void
    {
        $this->mountAction('selesaikanPelayanan');
    }

    public function selesaikanPelayananAction(): Action
    {
        return Action::make('selesaikanPelayanan')
            ->label('Selesaikan Pelayanan')
            ->icon('heroicon-m-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Selesaikan Pelayanan Pasien?')
            ->modalDescription(fn () => "Apakah Anda yakin ingin menyelesaikan pelayanan untuk pasien {$this->pendaftaran?->pasien?->nama} ({$this->pendaftaran?->no_pendaftaran})?")
            ->modalSubmitActionLabel('Ya, Selesaikan')
            ->action(function () {
                if (! $this->pendaftaran) {
                    return;
                }

                $this->pendaftaran->update(['status_pelayanan' => 'Selesai']);
                session()->forget('active_pendaftaran_id');

                Notification::make()
                    ->title('Pelayanan Selesai')
                    ->body("Pelayanan untuk pasien {$this->pendaftaran->pasien?->nama} telah berhasil diselesaikan.")
                    ->success()
                    ->send();

                $this->redirect(\App\Filament\Resources\Pendaftarans\PendaftaranResource::getUrl('index'));
            });
    }

    public function mount($record = null): void
    {
        if ($record) {
            $this->record = (int) $record;
            $this->pendaftaran = Pendaftaran::with(['pasien.tempatLahir', 'pasien.pekerjaan', 'pasien.unitEksternal', 'pasien.subUnitEksternal', 'poli', 'dokter'])->find($this->record);
            if ($this->pendaftaran) {
                session(['active_pendaftaran_id' => $this->pendaftaran->id]);
            }
        }

        // Cek session jika tidak ada record di URL
        if (! $this->pendaftaran && session()->has('active_pendaftaran_id')) {
            $sessId = (int) session('active_pendaftaran_id');
            $this->pendaftaran = Pendaftaran::with(['pasien.tempatLahir', 'pasien.pekerjaan', 'pasien.unitEksternal', 'pasien.subUnitEksternal', 'poli', 'dokter'])->find($sessId);
            if ($this->pendaftaran) {
                $this->record = $this->pendaftaran->id;
            }
        }

        // Jika masih belum ada record, cari kunjungan aktif atau kunjungan terbaru
        if (! $this->pendaftaran) {
            /** @var User|null $user */
            $user = Auth::user();

            $queryActive = Pendaftaran::query()
                ->whereIn('status_pelayanan', ['Sedang Diperiksa', 'Menunggu'])
                ->latest('tanggal_pendaftaran');

            if ($user && ! $user->hasRole('super_admin')) {
                $pegawai = $user->pegawai;
                if ($pegawai && $pegawai->poli_id) {
                    $queryActive->where('poli_id', $pegawai->poli_id);
                }
            }

            $this->pendaftaran = $queryActive->first();

            if (! $this->pendaftaran) {
                $queryLatest = Pendaftaran::query()->latest('tanggal_pendaftaran');
                if ($user && ! $user->hasRole('super_admin')) {
                    $pegawai = $user->pegawai;
                    if ($pegawai && $pegawai->poli_id) {
                        $queryLatest->where('poli_id', $pegawai->poli_id);
                    }
                }
                $this->pendaftaran = $queryLatest->first();
            }

            if ($this->pendaftaran) {
                $this->record = $this->pendaftaran->id;
                session(['active_pendaftaran_id' => $this->pendaftaran->id]);
            }
        }

        $this->isiDefaultForm();
    }

    protected function isiDefaultForm(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        $pegawai = $user?->pegawai;

        $namaPpa = $pegawai ? trim(($pegawai->nip ? $pegawai->nip . ' - ' : '') . $pegawai->nama_lengkap) : ($user?->name ?? 'Tenaga Medis');
        $profesi = $pegawai?->profesi ?? ($user?->hasRole('Perawat') ? 'Perawat' : 'Dokter');

        $this->form->fill([
            'pendaftaran_id' => $this->record,
            'nama_ppa'       => $namaPpa,
            'profesi'        => $profesi,
            'tanggal_waktu'  => now(),
            'is_sbar'        => false,
            'is_tbak'        => false,
            'subjektif'      => null,
            'objektif'       => null,
            'assessment'     => null,
            'planning'       => null,
            'instruksi'      => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Form (Id Generate By Sistem)')
                    ->icon('heroicon-o-document-text')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(12)
                            ->schema([
                                TextInput::make('nama_ppa')
                                    ->label('Nama Profesional Pemberi Asuhan (PPA):')
                                    ->readOnly()
                                    ->required()
                                    ->columnSpan(12),

                                Select::make('profesi')
                                    ->label('Profesi:')
                                    ->options([
                                        'Dokter'        => 'Dokter',
                                        'Perawat'       => 'Perawat',
                                        'Bidan'         => 'Bidan',
                                        'Farmasi'       => 'Farmasi',
                                        'Gizi'          => 'Gizi',
                                        'Fisioterapi'   => 'Fisioterapi',
                                        'Perekam Medis' => 'Perekam Medis',
                                    ])
                                    ->required()
                                    ->columnSpan(6),
                            ]),

                        Grid::make(12)
                            ->schema([
                                DateTimePicker::make('tanggal_waktu')
                                    ->label('Tanggal & Waktu:')
                                    ->default(now())
                                    ->seconds(false)
                                    ->required()
                                    ->columnSpan(7),

                                Grid::make(2)
                                    ->columnSpan(5)
                                    ->schema([
                                        Checkbox::make('is_sbar')
                                            ->label('SBAR')
                                            ->inline(false),

                                        Checkbox::make('is_tbak')
                                            ->label('TBAK')
                                            ->inline(false),
                                    ]),
                            ]),

                        RichEditor::make('subjektif')
                            ->label('Subjektif (S) & Objektif (O)')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'link',
                            ])
                            ->placeholder('Hasil asesmen pasien dan pemberian pelayanan (Subjektif & Objektif)...'),

                        RichEditor::make('assessment')
                            ->label('Assesment (A)')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'link',
                            ])
                            ->placeholder('Analisis klinis, diagnosis kerja / diagnosis banding / masalah asuhan...'),

                        RichEditor::make('planning')
                            ->label('Planning (P) & Instruksi')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'link',
                            ])
                            ->placeholder('Instruksi tenaga kesehatan termasuk pasca bedah / prosedur / terapi...'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                if (! $this->pendaftaran) {
                    return CpptRecord::query()->whereNull('id');
                }

                return CpptRecord::query()
                    ->with(['pegawai', 'verifiedBy'])
                    ->where('pendaftaran_id', $this->pendaftaran->id)
                    ->latest('tanggal_waktu');
            })
            ->heading('Riwayat Catatan Perkembangan Pasien (CPPT) Kunjungan Ini')
            ->columns([
                TextColumn::make('tanggal_waktu')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama_ppa')
                    ->label('PPA')
                    ->description(fn (CpptRecord $record) => $record->profesi)
                    ->searchable(),

                TextColumn::make('metode_badge')
                    ->label('Metode')
                    ->state(function (CpptRecord $record): string {
                        $labels = [];
                        if ($record->is_sbar) $labels[] = 'SBAR';
                        if ($record->is_tbak) $labels[] = 'TBAK';
                        return empty($labels) ? 'SOAP' : implode(' + ', $labels);
                    })
                    ->badge()
                    ->color(fn ($state) => str_contains($state, 'SBAR') ? 'warning' : 'info')
                    ->alignCenter(),

                TextColumn::make('subjektif')
                    ->label('S / O')
                    ->html()
                    ->limit(50),

                TextColumn::make('assessment')
                    ->label('A')
                    ->html()
                    ->limit(50),

                TextColumn::make('planning')
                    ->label('P / Instruksi')
                    ->html()
                    ->limit(50),

                IconColumn::make('is_verified')
                    ->label('Verifikasi DPJP')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),
            ])
            ->actions([
                DeleteAction::make(),
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

        CpptRecord::create([
            'pendaftaran_id' => $this->pendaftaran->id,
            'pasien_id'      => $this->pendaftaran->pasien_id,
            'pegawai_id'     => $pegawaiId,
            'nama_ppa'       => $state['nama_ppa'] ?? ($user?->name ?? 'Tenaga Medis'),
            'profesi'        => $state['profesi'] ?? 'Perawat',
            'tanggal_waktu'  => $state['tanggal_waktu'] ?? now(),
            'metode'         => ($state['is_sbar'] ?? false) ? 'SBAR' : 'SOAP',
            'subjektif'      => $state['subjektif'] ?? null,
            'objektif'       => null,
            'assessment'     => $state['assessment'] ?? null,
            'planning'       => $state['planning'] ?? null,
            'instruksi'      => null,
            'is_sbar'        => (bool) ($state['is_sbar'] ?? false),
            'is_tbak'        => (bool) ($state['is_tbak'] ?? false),
            'is_verified'    => false,
        ]);

        $this->isiDefaultForm();

        Notification::make()
            ->title('CPPT Berhasil Disimpan')
            ->body('Catatan Perkembangan Pasien Terintegrasi telah berhasil dicatat.')
            ->success()
            ->send();
    }

    public function hapusCppt(int $id): void
    {
        $cppt = CpptRecord::find($id);
        if ($cppt) {
            $cppt->delete();
            Notification::make()->title('Catatan CPPT Berhasil Dihapus')->success()->send();
        }
    }

    public function verifikasiCppt(int $id): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        $pegawaiId = $user?->pegawai?->id;

        $cppt = CpptRecord::find($id);
        if ($cppt) {
            $cppt->update([
                'is_verified' => true,
                'verified_by_pegawai_id' => $pegawaiId,
                'verified_at' => now(),
            ]);
            Notification::make()->title('CPPT Berhasil Diverifikasi')->success()->send();
        }
    }

    public function getRiwayatCpptProperty()
    {
        if (! $this->pendaftaran || ! $this->pendaftaran->pasien_id) {
            return collect();
        }

        return CpptRecord::query()
            ->with(['pegawai', 'verifiedBy', 'pendaftaran.poli'])
            ->where('pasien_id', $this->pendaftaran->pasien_id)
            ->latest('tanggal_waktu')
            ->paginate(5, ['*'], 'cpptPage');
    }

    public function pilihKunjungan(int $pendaftaranId): void
    {
        $this->record = $pendaftaranId;
        $this->pendaftaran = Pendaftaran::with(['pasien.tempatLahir', 'poli', 'dokter'])->find($pendaftaranId);
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
}
