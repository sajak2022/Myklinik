<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\KontakPegawai;
use App\Models\Pegawai;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $region = $this->regionPath();
        $countryId = Country::where('code', 'ID')->value('id') ?? Country::query()->value('id');
        $poliUmum = Poli::firstOrCreate(['nama' => 'Poli Umum'], ['status' => true]);
        $poliGigi = Poli::firstOrCreate(['nama' => 'Poli Gigi'], ['status' => true]);

        if (! $countryId) {
            throw new RuntimeException('Tidak ada data negara untuk seeder pegawai.');
        }

        $staff = [
            ['drumum', 'drumum@myklinik.test', 'dr. Andi Pratama', 'Andi Pratama', 'Dokter', $poliUmum, 'Laki-laki', '1985-03-12', '081234570001', 'SIP-DU-0001', 'STR-DU-0001'],
            ['drgigi', 'drgigi@myklinik.test', 'drg. Citra Maharani', 'Citra Maharani', 'Dokter', $poliGigi, 'Perempuan', '1987-08-25', '081234570002', 'SIP-DG-0001', 'STR-DG-0001'],
            ['perawatumum', 'perawat.umum@myklinik.test', 'Ns. Dimas Saputra', 'Dimas Saputra', 'Perawat', $poliUmum, 'Laki-laki', '1990-01-17', '081234570003', 'SIP-PU-0001', 'STR-PU-0001'],
            ['perawatgigi', 'perawat.gigi@myklinik.test', 'Ns. Eka Wulandari', 'Eka Wulandari', 'Perawat', $poliGigi, 'Perempuan', '1993-06-09', '081234570004', 'SIP-PG-0001', 'STR-PG-0001'],
        ];

        DB::transaction(function () use ($staff, $region, $countryId): void {
            foreach ($staff as $index => [$username, $email, $displayName, $name, $profession, $poli, $gender, $birthDate, $phone, $sip, $str]) {
                $number = $index + 1;
                $postalCode = sprintf('401%d%d', $number, $number);
                $address = sprintf('Jl. Tenaga Kesehatan No. %d', $number);
                $user = User::updateOrCreate(
                    ['email' => $email],
                    ['name' => $displayName, 'password' => Hash::make('password')]
                );

                $pegawai = Pegawai::updateOrCreate(
                    ['nip' => sprintf('197%s000%d', 500 + $number, $number)],
                    [
                        'user_id' => $user->id,
                        'gelar_depan' => str_starts_with($displayName, 'drg.') ? 'drg.' : (str_starts_with($displayName, 'dr.') ? 'dr.' : 'Ns.'),
                        'nama_lengkap' => $name,
                        'gelar_belakang' => $profession === 'Dokter' ? 'M.Kes.' : 'S.Kep., Ns.',
                        'tempat_lahir_regency_id' => $region['regency'],
                        'tanggal_lahir' => $birthDate,
                        'tempat_tanggal_lahir' => $region['regency'] . ', ' . date('d-m-Y', strtotime($birthDate)),
                        'jenis_kelamin' => $gender,
                        'agama' => 'Islam',
                        'profesi' => $profession,
                        'poli_id' => $poli->id,
                        'no_str' => $str,
                        'str_berlaku_sampai' => '2029-12-31',
                        'no_sip' => $sip,
                        'sip_berlaku_sampai' => '2028-12-31',
                        'jenis_kartu' => 'Kartu Tanda Penduduk (KTP)',
                        'nomor_kartu' => sprintf('3273%08d%03d', 66000000 + $number, $number),
                        'alamat_kartu' => $address,
                        'rt_kartu' => sprintf('%03d', $number),
                        'rw_kartu' => '002',
                        'kode_pos_kartu' => $postalCode,
                        'province_id_kartu' => $region['province'],
                        'regency_id_kartu' => $region['regency'],
                        'district_id_kartu' => $region['district'],
                        'village_id_kartu' => $region['village'],
                        'alamat' => $address,
                        'rt' => sprintf('%03d', $number),
                        'rw' => '002',
                        'kode_pos' => $postalCode,
                        'province_id' => $region['province'],
                        'regency_id' => $region['regency'],
                        'district_id' => $region['district'],
                        'village_id' => $region['village'],
                        'status' => 'Aktif',
                    ]
                );

                KontakPegawai::updateOrCreate(
                    ['pegawai_id' => $pegawai->id],
                    ['jenis_kontak' => 'Telepon Seluler', 'nomor_kontak' => $phone, 'status' => true]
                );
            }
        });
    }

    private function regionPath(): array
    {
        $province = DB::table('indonesia_regions')->where('code', 'not like', '%.%')->orderBy('code')->first();
        $regency = $province ? DB::table('indonesia_regions')->where('code', 'like', $province->code . '.%')->orderBy('code')->first() : null;
        $district = $regency ? DB::table('indonesia_regions')->where('code', 'like', $regency->code . '.%')->orderBy('code')->first() : null;
        $village = $district ? DB::table('indonesia_regions')->where('code', 'like', $district->code . '.%')->orderBy('code')->first() : null;

        if (! $province || ! $regency || ! $district || ! $village) {
            throw new RuntimeException('Data wilayah Indonesia belum lengkap untuk seeder pegawai.');
        }

        return ['province' => $province->code, 'regency' => $regency->code, 'district' => $district->code, 'village' => $village->code];
    }
}
