<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenyediaSeeder extends Seeder
{
    public function run(): void
    {
        $penyedias = [
            [
                'id' => 1,
                'nama' => 'Klinik Pratama Kementrian Pariwisata',
                'alamat' => 'Jl. Kimia No.12-20 7, RT.7/RW.1, Pegangsaan, Kec. Menteng, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10320',
                'no_telepon' => '085792175573',
                'fax' => '-',
                'tanggal' => '2026-08-23',
                'status' => 'Aktif',
                'created_at' => '2026-08-23 04:38:01',
                'updated_at' => '2026-08-23 04:38:01',
            ],
        ];

        foreach ($penyedias as $penyedia) {
            DB::table('penyedias')->updateOrInsert(['id' => $penyedia['id']], $penyedia);
        }
    }
}
