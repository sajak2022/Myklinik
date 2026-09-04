<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KontakPegawai extends Model
{
    protected $table = 'kontak_pegawais';

    protected $fillable = ['pegawai_id', 'jenis_kontak', 'nomor_kontak', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
