<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasienKontak extends Model
{
    protected $fillable = ['pasien_id', 'jenis_kontak', 'nomor_kontak'];

    public function pasien(): BelongsTo { return $this->belongsTo(Pasien::class); }
}
