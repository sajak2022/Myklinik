<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'gelar_depan', 'nama_lengkap', 'gelar_belakang',
        'tempat_lahir_regency_id', 'tanggal_lahir',
        'jenis_kelamin', 'agama',
        'profesi', 'jenis_spesialis', 'poli_id',
        'no_str', 'str_berlaku_sampai', 'no_sip', 'sip_berlaku_sampai',
        'jenis_kartu', 'nomor_kartu',
        'alamat_kartu', 'rt_kartu', 'rw_kartu', 'kode_pos_kartu',
        'province_id_kartu', 'regency_id_kartu', 'district_id_kartu', 'village_id_kartu',
        'alamat', 'rt', 'rw', 'kode_pos',
        'province_id', 'regency_id', 'district_id', 'village_id',
        'tempat_tanggal_lahir',
        'status',
    ];

    protected $casts = [
        'tanggal_lahir'      => 'datetime:Y-m-d',
        'str_berlaku_sampai' => 'datetime:Y-m-d',
        'sip_berlaku_sampai' => 'datetime:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kontaks(): HasMany
    {
        return $this->hasMany(KontakPegawai::class);
    }

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class);
    }
    public function tempatLahir(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'tempat_lahir_regency_id', 'code');
    }
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id', 'code');
    }
    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class, 'regency_id', 'code');
    }
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id', 'code');
    }
    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class, 'village_id', 'code');
    }
}
