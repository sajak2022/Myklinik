<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnamnesisPasien extends Model
{
    use HasFactory;

    protected $table = 'anamnesis_pasiens';

    protected $fillable = [
        'pendaftaran_id',
        'pasien_id',
        'pegawai_id',
        'user_id',
        'waktu_anamnesis',

        // 1. Keluhan Utama
        'keluhan_utama',

        // 2. Anamnesis Diperoleh
        'sumber_anamnesis',
        'nama_sumber_informasi',
        'hubungan_sumber_informasi',

        // 3. Riwayat Penyakit Sekarang (Umum)
        'riwayat_penyakit_sekarang',

        // 4. Riwayat
        'riwayat_penyakit_dahulu',
        'riwayat_penyakit_keluarga',
        'riwayat_alergi',
        'riwayat_pengobatan',

        // 5. Status Fungsional
        'status_fungsional',
        'alat_bantu',
        'alat_bantu_array',
        'alat_bantu_lainnya',
        'cacat_tubuh',
        'cacat_tubuh_pilihan',
        'cacat_tubuh_keterangan',

        // 6. Hubungan Status Psikososial
        'status_psikologis',
        'status_mental',
        'hubungan_keluarga',
        'tinggal_bersama',
        'nilai_kepercayaan_agama',

        // 7. Edukasi
        'kesediaan_menerima_edukasi',
        'ada_hambatan_edukasi',
        'butuh_penerjemah',
        'kebutuhan_edukasi',
        'hambatan_belajar',
        'penerima_edukasi',

        // 8. Skrining Gizi Awal (MST)
        'penurunan_bb',
        'skor_penurunan_bb',
        'asupan_makan_berkurang',
        'skor_asupan_makan',
        'kondisi_khusus_gizi',
        'total_skor_gizi',
        'kategori_gizi',

        // 9. Batuk / Skrining TB
        'skrining_batuk',
        'skrining_batuk_keterangan',

        // 10. Masuk
        'cara_masuk',
        'asal_rujukan',
        'alasan_kunjungan',
    ];

    protected $casts = [
        'waktu_anamnesis'    => 'datetime',
        'alat_bantu_array'   => 'array',
        'kebutuhan_edukasi'  => 'array',
        'hambatan_belajar'   => 'array',
        'skrining_batuk'     => 'array',
        'skor_penurunan_bb'  => 'integer',
        'skor_asupan_makan'  => 'integer',
        'total_skor_gizi'    => 'integer',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}