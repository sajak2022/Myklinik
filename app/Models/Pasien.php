<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pasien extends Model
{
    protected $fillable = [
        'no_rm', 'pasien_tidak_dikenal', 'norm_manual', 'gelar_depan', 'nama', 'gelar_belakang', 'nama_panggilan',
        'tempat_lahir_regency_id', 'tanggal_lahir', 'jenis_kelamin',
        'agama', 'status_perkawinan', 'pendidikan', 'pekerjaan',
        'golongan_darah', 'suku_bangsa',
        'country_id', 'status_pasien',
        'unit_eksternal_id', 'sub_unit_eksternal_id',
        'alamat', 'rt', 'rw', 'kode_pos', 'province_id', 'regency_id', 'district_id', 'village_id',
        'sama_dengan_alamat_sekarang', 'jenis_kartu', 'nomor_kartu',
        'alamat_kartu', 'rt_kartu', 'rw_kartu', 'kode_pos_kartu',
        'province_id_kartu', 'regency_id_kartu', 'district_id_kartu', 'village_id_kartu',
    ];

    protected $casts = [
        'pasien_tidak_dikenal' => 'boolean',
        'sama_dengan_alamat_sekarang' => 'boolean',
        'tanggal_lahir' => 'datetime:Y-m-d',
    ];

    public function kontaks(): HasMany
    {
        return $this->hasMany(PasienKontak::class);
    }

    public function pendaftarans(): HasMany
    {
        return $this->hasMany(Pendaftaran::class);
    }

    public function anamnesisRecords(): HasMany
    {
        return $this->hasMany(AnamnesisPasien::class);
    }

    // Relasi ini WAJIB ada untuk Select::make('country_id')->relationship('country', 'name')
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function tempatLahir(): BelongsTo { return $this->belongsTo(Regency::class, 'tempat_lahir_regency_id', 'code'); }
    public function province(): BelongsTo { return $this->belongsTo(Province::class, 'province_id', 'code'); }
    public function regency(): BelongsTo { return $this->belongsTo(Regency::class, 'regency_id', 'code'); }
    public function district(): BelongsTo { return $this->belongsTo(District::class, 'district_id', 'code'); }
    public function village(): BelongsTo { return $this->belongsTo(Village::class, 'village_id', 'code'); }

    public function unitEksternal(): BelongsTo { return $this->belongsTo(UnitEksternal::class, 'unit_eksternal_id'); }
    public function subUnitEksternal(): BelongsTo { return $this->belongsTo(UnitEksternal::class, 'sub_unit_eksternal_id'); }
}
