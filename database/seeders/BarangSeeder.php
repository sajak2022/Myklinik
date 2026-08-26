<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            [
                'id' => 1,
                'nama' => 'Tablet',
                'created_at' => '2026-08-23 05:05:42',
                'updated_at' => '2026-08-23 05:05:42',
            ],
            [
                'id' => 2,
                'nama' => 'Syrup',
                'created_at' => '2026-08-23 05:05:47',
                'updated_at' => '2026-08-23 05:05:47',
            ],
            [
                'id' => 3,
                'nama' => 'Semua Kategori',
                'created_at' => '2026-08-23 05:06:01',
                'updated_at' => '2026-08-23 05:06:01',
            ],
            [
                'id' => 4,
                'nama' => 'Salep',
                'created_at' => '2026-08-23 05:13:15',
                'updated_at' => '2026-08-23 05:13:15',
            ],
        ];

        foreach ($kategoris as $kategori) {
            DB::table('kategoris')->updateOrInsert(['id' => $kategori['id']], $kategori);
        }

        $satuans = [
            [
                'id' => 1,
                'nama' => 'Tablet',
                'created_at' => '2026-08-23 05:06:28',
                'updated_at' => '2026-08-23 05:06:28',
            ],
            [
                'id' => 2,
                'nama' => 'Tube',
                'created_at' => '2026-08-23 05:06:37',
                'updated_at' => '2026-08-23 05:06:37',
            ],
            [
                'id' => 3,
                'nama' => 'Kapsul',
                'created_at' => '2026-08-23 05:06:46',
                'updated_at' => '2026-08-23 05:06:46',
            ],
            [
                'id' => 4,
                'nama' => 'Botol',
                'created_at' => '2026-08-23 05:06:51',
                'updated_at' => '2026-08-23 05:06:51',
            ],
            [
                'id' => 5,
                'nama' => 'Suppositoria ',
                'created_at' => '2026-08-23 05:07:22',
                'updated_at' => '2026-08-23 05:07:22',
            ],
        ];

        foreach ($satuans as $satuan) {
            DB::table('satuans')->updateOrInsert(['id' => $satuan['id']], $satuan);
        }

        $barangs = [
            [
                'id' => 1,
                'nama_barang' => 'Asam Folat 1 mg',
                'kategori_id' => 1,
                'satuan_id' => 1,
                'merk' => 'Promed',
                'penyedia_id' => 1,
                'generik' => 'Folic acid',
                'jenis_penggunaan' => 'Obat Dalam',
                'stok_minimum' => 30,
                'status' => 1,
                'created_at' => '2026-08-23 05:11:19',
                'updated_at' => '2026-08-23 05:11:19',
            ],
            [
                'id' => 2,
                'nama_barang' => 'Chloramphenicol Salep Mata',
                'kategori_id' => 4,
                'satuan_id' => 2,
                'merk' => 'Bernofarm',
                'penyedia_id' => 1,
                'generik' => 'Chloramphenicol',
                'jenis_penggunaan' => 'Obat Luar',
                'stok_minimum' => 2,
                'status' => 1,
                'created_at' => '2026-08-23 05:14:40',
                'updated_at' => '2026-08-23 05:14:40',
            ],
            [
                'id' => 3,
                'nama_barang' => 'Cetirizine 10 mg',
                'kategori_id' => 1,
                'satuan_id' => 1,
                'merk' => 'Bernofarm',
                'penyedia_id' => 1,
                'generik' => 'Cetirizine',
                'jenis_penggunaan' => 'Obat Dalam',
                'stok_minimum' => 20,
                'status' => 1,
                'created_at' => '2026-08-23 05:16:35',
                'updated_at' => '2026-08-23 05:16:35',
            ],
        ];

        foreach ($barangs as $barang) {
            DB::table('barangs')->updateOrInsert(['id' => $barang['id']], $barang);
        }
    }
}
