<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Pendaftaran extends Model
{
    use HasFactory;

    protected $table = 'pendaftarans';

    protected $fillable = [
        'no_pendaftaran',
        'no_antrian',
        'tanggal_pendaftaran',
        'pasien_id',
        'poli_id',
        'dokter_id',
        'petugas_id',
        'jenis_pelayanan',
        'jenis_kunjungan',
        'is_kecelakaan',
        'jenis_kecelakaan',
        'no_laporan_polisi',
        'tgl_kejadian_kecelakaan',
        'penjamin_kecelakaan',
        'lokasi_kecelakaan',
        'pj_nama',
        'pj_hubungan',
        'pj_pekerjaan',
        'pj_jenis_kartu',
        'pj_nomor_kartu',
        'pj_no_telepon',
        'pj_alamat',
        'status_pelayanan',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pendaftaran'     => 'datetime',
        'tgl_kejadian_kecelakaan' => 'date',
        'is_kecelakaan'           => 'boolean',
    ];

    public const STATUS_MENUNGGU = 'Menunggu';
    public const STATUS_PEMERIKSAAN_PERAWAT = 'Pemeriksaan Perawat';
    public const STATUS_MENUNGGU_DOKTER = 'Menunggu Dokter';
    public const STATUS_SEDANG_DIPERIKSA = 'Sedang Diperiksa';
    public const STATUS_FINAL = 'Final';
    public const STATUS_SELESAI = 'Final';
    public const STATUS_BATAL = 'Batal';

    public const ACTIVE_STATUSES = [
        self::STATUS_MENUNGGU,
        self::STATUS_PEMERIKSAAN_PERAWAT,
        self::STATUS_MENUNGGU_DOKTER,
        self::STATUS_SEDANG_DIPERIKSA,
    ];

    protected static function booted(): void
    {
        static::creating(function (Pendaftaran $pendaftaran) {
            // 0. Validasi: Cegah 1 pasien memiliki lebih dari 1 pendaftaran aktif sekaligus
            if ($pendaftaran->pasien_id) {
                $hasActive = static::where('pasien_id', $pendaftaran->pasien_id)
                    ->whereIn('status_pelayanan', self::ACTIVE_STATUSES)
                    ->exists();

                if ($hasActive) {
                    throw new \Exception('Pasien ini masih memiliki pendaftaran aktif yang belum selesai. Selesaikan pelayanan sebelumnya terlebih dahulu.');
                }
            }

            $regDate = $pendaftaran->tanggal_pendaftaran
                ? Carbon::parse($pendaftaran->tanggal_pendaftaran)->toDateString()
                : Carbon::today()->toDateString();
            $datePrefix = Carbon::parse($regDate)->format('Ymd');

            // 1. Auto No. Pendaftaran (REG-YYYYMMDD-0001)
            if (empty($pendaftaran->no_pendaftaran)) {
                $latestToday = static::whereDate('tanggal_pendaftaran', $regDate)
                    ->orderBy('id', 'desc')
                    ->first();

                $lastNumber = 0;
                if ($latestToday && preg_match('/REG-\d{8}-(\d+)/', $latestToday->no_pendaftaran, $matches)) {
                    $lastNumber = (int) $matches[1];
                }

                $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
                $pendaftaran->no_pendaftaran = "REG-{$datePrefix}-{$nextNumber}";
            }

            // 2. Auto No. Antrian (1, 2, 3, dst per Poli per hari)
            if (empty($pendaftaran->no_antrian)) {
                $query = static::whereDate('tanggal_pendaftaran', $regDate);
                if ($pendaftaran->poli_id) {
                    $query->where('poli_id', $pendaftaran->poli_id);
                }
                $maxAntrian = (int) $query->max('no_antrian');
                $pendaftaran->no_antrian = (string) ($maxAntrian + 1);
            }

            // 3. Auto Petugas
            if (empty($pendaftaran->petugas_id) && Auth::check()) {
                $pendaftaran->petugas_id = Auth::id();
            }

            // 4. Auto Deteksi Kunjungan Baru vs Lama
            if (empty($pendaftaran->jenis_kunjungan) && $pendaftaran->pasien_id) {
                $hasHistory = static::where('pasien_id', $pendaftaran->pasien_id)->exists();
                $pendaftaran->jenis_kunjungan = $hasHistory ? 'Lama' : 'Baru';
            }

            // 5. Default Status Pelayanan
            if (empty($pendaftaran->status_pelayanan)) {
                $pendaftaran->status_pelayanan = self::STATUS_MENUNGGU;
            }
        });
    }

    public function hasPemeriksaanFisik(): bool
    {
        if ($this->relationLoaded('pemeriksaanFisiks')) {
            return $this->pemeriksaanFisiks->isNotEmpty();
        }

        return $this->pemeriksaanFisiks()->exists();
    }

    public function hasPemeriksaanFisikByProfesi(string|array $profesi): bool
    {
        $profesi = (array) $profesi;

        if ($this->relationLoaded('pemeriksaanFisiks')) {
            return $this->pemeriksaanFisiks->contains(function ($pf) use ($profesi) {
                return in_array($pf->pegawai?->profesi, $profesi, true);
            });
        }

        return $this->pemeriksaanFisiks()
            ->whereHas('pegawai', fn ($query) => $query->whereIn('profesi', $profesi))
            ->exists();
    }

    public function hasCppt(): bool
    {
        if ($this->relationLoaded('cpptRecords')) {
            return $this->cpptRecords->isNotEmpty();
        }

        return $this->cpptRecords()->exists();
    }

    public function hasCpptByProfesi(string|array $profesi): bool
    {
        $profesi = (array) $profesi;

        if ($this->relationLoaded('cpptRecords')) {
            return $this->cpptRecords->contains(function ($cppt) use ($profesi) {
                return in_array($cppt->pegawai?->profesi ?? $cppt->profesi, $profesi, true);
            });
        }

        return $this->cpptRecords()
            ->where(function ($query) use ($profesi) {
                $query->whereHas('pegawai', fn ($pegawai) => $pegawai->whereIn('profesi', $profesi))
                    ->orWhere(function ($legacy) use ($profesi) {
                        $legacy->whereDoesntHave('pegawai')->whereIn('profesi', $profesi);
                    });
            })
            ->exists();
    }

    public function isSiapUntukDokter(): bool
    {
        return $this->hasPemeriksaanFisik() && $this->hasCppt();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_pelayanan) {
            self::STATUS_MENUNGGU            => 'Menunggu Perawat',
            self::STATUS_PEMERIKSAAN_PERAWAT => 'Pemeriksaan Perawat',
            self::STATUS_MENUNGGU_DOKTER     => 'Menunggu Dokter',
            self::STATUS_SEDANG_DIPERIKSA    => 'Sedang Diperiksa Dokter',
            self::STATUS_FINAL, 'Selesai'    => 'Final',
            self::STATUS_BATAL               => 'Dibatalkan',
            default                          => (string) $this->status_pelayanan,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_pelayanan) {
            self::STATUS_MENUNGGU            => '#f59e0b', // amber
            self::STATUS_PEMERIKSAAN_PERAWAT => '#3b82f6', // blue
            self::STATUS_MENUNGGU_DOKTER     => '#8b5cf6', // purple
            self::STATUS_SEDANG_DIPERIKSA    => '#06b6d4', // cyan
            self::STATUS_FINAL, 'Selesai'    => '#10b981', // emerald
            self::STATUS_BATAL               => '#ef4444', // red
            default                          => '#94a3b8',
        };
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status_pelayanan', self::ACTIVE_STATUSES);
    }

    public function scopeUntukDokter(Builder $query, ?int $poliId = null, ?int $dokterId = null): Builder
    {
        // Dokter melihat pasien yang sudah diterima/sedang diperiksa hingga Final/Batal
        $query->whereIn('status_pelayanan', [
            self::STATUS_SEDANG_DIPERIKSA,
            self::STATUS_FINAL,
            'Selesai',
            self::STATUS_BATAL,
            'Batal',
        ]);

        if ($poliId) {
            $query->where('poli_id', $poliId);
        }

        if ($dokterId) {
            $query->where(function ($q) use ($dokterId) {
                $q->where('dokter_id', $dokterId)
                  ->orWhereNull('dokter_id');
            });
        }

        return $query;
    }

    public function scopeUntukPerawat(Builder $query, ?int $poliId = null): Builder
    {
        // Perawat melihat pasien yang sudah diterima (Pemeriksaan Perawat hingga Final/Batal) untuk poli terkait
        $query->whereIn('status_pelayanan', [
            self::STATUS_PEMERIKSAAN_PERAWAT,
            self::STATUS_MENUNGGU_DOKTER,
            self::STATUS_SEDANG_DIPERIKSA,
            self::STATUS_FINAL,
            'Selesai',
            self::STATUS_BATAL,
            'Batal',
        ]);

        if ($poliId) {
            $query->where('poli_id', $poliId);
        }

        return $query;
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class);
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'dokter_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function cpptRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CpptRecord::class, 'pendaftaran_id')->latest('tanggal_waktu');
    }

    public function pemeriksaanFisiks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PemeriksaanFisik::class, 'pendaftaran_id')->latest('waktu_pemeriksaan');
    }



    public function anamnesisRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AnamnesisPasien::class, 'pendaftaran_id')->latest('waktu_anamnesis');
    }

    public function anamnesisTerakhir(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AnamnesisPasien::class, 'pendaftaran_id')->latestOfMany('waktu_anamnesis');
    }

    public function asuhanKeperawatans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AsuhanKeperawatan::class, 'pendaftaran_id')->latest('waktu_input');
    }

    public function finalkanPelayanan(?int $verifiedByPegawaiId = null): void
    {
        $this->update(['status_pelayanan' => self::STATUS_FINAL]);

        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $pegawaiId = $verifiedByPegawaiId ?? $this->dokter_id ?? $currentUser?->pegawai_id;

        $this->cpptRecords()->where('is_verified', false)->update([
            'is_verified' => true,
            'verified_by_pegawai_id' => $pegawaiId,
            'verified_at' => now(),
        ]);
    }

    public function selesaikanPelayanan(?int $verifiedByPegawaiId = null): void
    {
        $this->finalkanPelayanan($verifiedByPegawaiId);
    }

    public function batalkanFinalPelayanan(): void
    {
        $newStatus = $this->dokter_id ? self::STATUS_SEDANG_DIPERIKSA : self::STATUS_MENUNGGU_DOKTER;
        $this->update(['status_pelayanan' => $newStatus]);
    }

    public function kirimNotifikasiKeDokter(): void
    {
        $this->loadMissing(['pasien', 'poli', 'dokter.user']);
        $pasienNama = $this->pasien?->nama ?? 'Pasien';
        $noAntrian = $this->no_antrian ?? '-';
        $poliNama = $this->poli?->nama ?? 'Poli';
        $dokterId = $this->dokter_id;
        $poliId = $this->poli_id;

        $dokterUsers = collect();
        if ($dokterId && $this->dokter?->user_id) {
            $dokterUser = User::find($this->dokter->user_id);
            if ($dokterUser) {
                $dokterUsers->push($dokterUser);
            }
        }

        if ($dokterUsers->isEmpty()) {
            $dokterUsers = User::where(function ($query) {
                $query->whereHas('roles', fn ($q) => $q->where('name', 'Dokter'))
                    ->orWhereHas('pegawai', fn ($q) => $q->where('profesi', 'Dokter'));
            })
            ->when($poliId, function ($query) use ($poliId) {
                $query->whereHas('pegawai', function ($q) use ($poliId) {
                    $q->where('poli_id', $poliId);
                });
            })
            ->get();
        }

        if ($dokterUsers->isNotEmpty()) {
            \Filament\Notifications\Notification::make()
                ->title('Pasien Siap Diperiksa')
                ->body("Asesmen perawat untuk pasien {$pasienNama} (No. Antrian: {$noAntrian}) di {$poliNama} telah lengkap. Pasien siap untuk diperiksa dokter.")
                ->icon('heroicon-o-clipboard-document-check')
                ->iconColor('info')
                ->info()
                ->actions([
                    \Filament\Actions\Action::make('terima')
                        ->button()
                        ->label('Buka Pengunjung')
                        ->url(\App\Filament\Resources\Pendaftarans\PendaftaranResource::getUrl('index')),
                ])
                ->sendToDatabase($dokterUsers);
        }
    }
}
