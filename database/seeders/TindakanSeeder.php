<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TindakanSeeder extends Seeder
{
    public function run(): void
    {
        $tindakans = [
            [
                'id' => 1,
                'nama_tindakan' => 'Pendaftaran Kunjungan Baru',
                'kategori_tindakan' => 'Layanan',
                'status' => 'Aktif',
                'created_at' => '2026-08-22 12:28:37',
                'updated_at' => '2026-08-22 12:28:37',
            ],
            [
                'id' => 2,
                'nama_tindakan' => 'Pendaftaran Kunjungan Lama',
                'kategori_tindakan' => 'Layanan',
                'status' => 'Aktif',
                'created_at' => '2026-08-22 12:29:06',
                'updated_at' => '2026-08-22 12:29:06',
            ],
            [
                'id' => 3,
                'nama_tindakan' => 'Pemeriksaan Dokter Umum / Gigi',
                'kategori_tindakan' => 'Layanan',
                'status' => 'Aktif',
                'created_at' => '2026-08-22 12:29:33',
                'updated_at' => '2026-08-22 12:29:33',
            ],
        ];

        foreach ($tindakans as $tindakan) {
            DB::table('tindakans')->updateOrInsert(['id' => $tindakan['id']], $tindakan);
        }
    }
}
