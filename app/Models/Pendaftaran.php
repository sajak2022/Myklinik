<?php

namespace App\Models;

use Carbon\Carbon;
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
        'general_consent',
        'consent_satusehat',
        'resiko_jatuh',
        'jenis_kunjungan',
        'cara_masuk',
        'penjamin',
        'no_asuransi',
        'no_sep',
        'kelas_rawat',
        'is_rujukan',
        'faskes_perujuk',
        'no_rujukan',
        'tgl_rujukan',
        'dokter_perujuk',
        'diagnosis_rujukan',
        'is_kecelakaan',
        'jenis_kecelakaan',
        'no_laporan_polisi',
        'tgl_kejadian_kecelakaan',
        'penjamin_kecelakaan',
        'lokasi_kecelakaan',
        'pj_nama',
        'pj_hubungan',
        'pj_tgl_lahir',
        'pj_pekerjaan',
        'pj_jenis_kartu',
        'pj_nomor_kartu',
        'pj_no_telepon',
        'pj_alamat',
        'pengantar_nama',
        'pengantar_hubungan',
        'pengantar_no_telepon',
        'pengantar_alamat',
        'status_pelayanan',
        'biaya_pendaftaran',
        'status_pembayaran',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pendaftaran'     => 'datetime',
        'tgl_rujukan'             => 'date',
        'tgl_kejadian_kecelakaan' => 'date',
        'pj_tgl_lahir'            => 'date',
        'general_consent'         => 'boolean',
        'consent_satusehat'       => 'boolean',
        'resiko_jatuh'            => 'boolean',
        'is_rujukan'              => 'boolean',
        'is_kecelakaan'           => 'boolean',
        'biaya_pendaftaran'       => 'decimal:2',
    ];

    public const STATUS_MENUNGGU = 'Menunggu';
    public const STATUS_PEMERIKSAAN_PERAWAT = 'Pemeriksaan Perawat';
    public const STATUS_MENUNGGU_DOKTER = 'Menunggu Dokter';
    public const STATUS_SEDANG_DIPERIKSA = 'Sedang Diperiksa';
    public const STATUS_SELESAI = 'Selesai';
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

            // 5. Default Klinik Pegawai (Tanpa Biaya & Asuransi)
            if (empty($pendaftaran->penjamin)) {
                $pendaftaran->penjamin = 'Pegawai';
            }
            if (! isset($pendaftaran->biaya_pendaftaran)) {
                $pendaftaran->biaya_pendaftaran = 0;
            }
            if (empty($pendaftaran->status_pembayaran)) {
                $pendaftaran->status_pembayaran = 'Gratis';
            }
            if (empty($pendaftaran->cara_masuk)) {
                $pendaftaran->cara_masuk = 'Datang Sendiri';
            }
            if (empty($pendaftaran->status_pelayanan)) {
                $pendaftaran->status_pelayanan = self::STATUS_MENUNGGU;
            }
        });
    }

    public function hasPemeriksaanFisik(): bool
    {
        return $this->pemeriksaanFisiks()->exists();
    }

    public function hasPemeriksaanFisikByProfesi(string|array $profesi): bool
    {
        return $this->pemeriksaanFisiks()
            ->whereHas('pegawai', fn ($query) => $query->whereIn('profesi', (array) $profesi))
            ->exists();
    }

    public function hasCppt(): bool
    {
        return $this->cpptRecords()->exists();
    }

    public function hasCpptByProfesi(string|array $profesi): bool
    {
        $profesi = (array) $profesi;

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
        return $this->hasPemeriksaanFisikByProfesi(['Perawat', 'Bidan'])
            && $this->hasCpptByProfesi(['Perawat', 'Bidan']);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_pelayanan) {
            self::STATUS_MENUNGGU            => 'Menunggu Perawat',
            self::STATUS_PEMERIKSAAN_PERAWAT => 'Pemeriksaan Perawat',
            self::STATUS_MENUNGGU_DOKTER     => 'Menunggu Dokter',
            self::STATUS_SEDANG_DIPERIKSA    => 'Sedang Diperiksa Dokter',
            self::STATUS_SELESAI             => 'Pelayanan Selesai',
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
            self::STATUS_SELESAI             => '#10b981', // emerald
            self::STATUS_BATAL               => '#ef4444', // red
            default                          => '#94a3b8',
        };
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status_pelayanan', self::ACTIVE_STATUSES);
    }

    public function scopeUntukDokter($query, ?int $poliId = null, ?int $dokterId = null)
    {
        // Dokter HANYA melihat pasien setelah perawat selesai mengisi pemeriksaan & CPPT (Menunggu Dokter, Sedang Diperiksa, Selesai)
        $query->whereIn('status_pelayanan', [
            self::STATUS_MENUNGGU_DOKTER,
            self::STATUS_SEDANG_DIPERIKSA,
            self::STATUS_SELESAI,
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

    public function scopeUntukPerawat($query, ?int $poliId = null)
    {
        // Perawat melihat dari awal pendaftaran (Menunggu) hingga Selesai untuk poli terkait
        $query->whereIn('status_pelayanan', [
            self::STATUS_MENUNGGU,
            self::STATUS_PEMERIKSAAN_PERAWAT,
            self::STATUS_MENUNGGU_DOKTER,
            self::STATUS_SEDANG_DIPERIKSA,
            self::STATUS_SELESAI,
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

    public function asuhanKeperawatans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AsuhanKeperawatan::class, 'pendaftaran_id')->latest('waktu_input');
    }
}
