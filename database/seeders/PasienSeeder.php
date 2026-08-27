<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Pasien;
use App\Models\PasienKeluarga;
use App\Models\PasienKontak;
use App\Models\ReferensiDetail;
use App\Models\UnitEksternal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PasienSeeder extends Seeder
{
    public function run(): void
    {
        $reference = fn (string $category, string $description): int => $this->referenceId($category, $description);
        $region = $this->regionPath();
        $countryId = Country::where('code', 'ID')->value('id') ?? Country::query()->value('id');

        if (! $countryId) {
            throw new RuntimeException('Tidak ada data negara untuk seeder pasien.');
        }

        $unit = UnitEksternal::whereNull('parent_id')->first();
        $subUnit = $unit ? UnitEksternal::where('parent_id', $unit->id)->first() : null;
        $patients = [
            ['Budi Santoso', 'Budi', 'Laki-Laki', '1988-02-14', '081234560001', 'Andi Santoso', 'Kepala Keluarga'],
            ['Siti Aminah', 'Siti', 'Perempuan', '1992-07-21', '081234560002', 'Rina Aminah', 'Istri'],
            ['Rizky Pratama', 'Rizky', 'Laki-Laki', '2001-11-05', '081234560003', 'Dewi Pratama', 'Anak'],
            ['Maya Lestari', 'Maya', 'Perempuan', '1997-04-30', '081234560004', 'Hendra Lestari', 'Anak'],
            ['Agus Wijaya', 'Agus', 'Laki-Laki', '1975-09-18', '081234560005', 'Nur Wijaya', 'Istri'],
        ];

        DB::transaction(function () use ($patients, $reference, $region, $countryId, $unit, $subUnit): void {
            foreach ($patients as $index => [$name, $nickname, $gender, $birthDate, $phone, $familyName, $familyRelation]) {
                $number = $index + 1;
                $postalCode = sprintf('401%d%d', $number, $number);
                $patient = Pasien::updateOrCreate(
                    ['no_rm' => sprintf('DUMY%04d', $number)],
                    [
                        'pasien_tidak_dikenal' => false,
                        'norm_manual' => sprintf('900%04d', $number),
                        'gelar_depan' => $number === 5 ? 'Dr.' : null,
                        'nama' => $name,
                        'gelar_belakang' => $number === 5 ? 'S.Kom.' : null,
                        'nama_panggilan' => $nickname,
                        'tempat_lahir_regency_id' => $region['regency'],
                        'tanggal_lahir' => $birthDate,
                        'jenis_kelamin' => $gender,
                        'agama_detail_id' => $reference('Agama', $number === 2 ? 'Kristen (Protestan)' : 'Islam'),
                        'status_perkawinan_detail_id' => $reference('Status Perkawinan', $number === 3 || $number === 4 ? 'Belum Kawin' : 'Kawin'),
                        'pendidikan_detail_id' => $reference('Pendidikan', $number === 5 ? 'Strata II' : 'Diploma IV/Strata I'),
                        'pekerjaan_detail_id' => $reference('Pekerjaan', $number === 1 ? 'Pegawai Negeri Sipil' : 'Karyawan Swasta'),
                        'golongan_darah_detail_id' => $reference('Golongan Darah', ['A', 'B', 'AB', 'O', 'A'][$index]),
                        'suku_bangsa_detail_id' => $reference('Daftar Suku', 'Jawa'),
                        'country_id' => $countryId,
                        'status_pasien' => 'Hidup',
                        'unit_eksternal_id' => $unit?->id,
                        'sub_unit_eksternal_id' => $subUnit?->id,
                        'alamat' => sprintf('Jl. Contoh Sehat No. %d', $number),
                        'rt' => sprintf('%03d', $number),
                        'rw' => '002',
                        'kode_pos' => $postalCode,
                        'province_id' => $region['province'],
                        'regency_id' => $region['regency'],
                        'district_id' => $region['district'],
                        'village_id' => $region['village'],
                        'sama_dengan_alamat_sekarang' => false,
                        'jenis_kartu_detail_id' => $reference('Jenis Kartu Identitas', 'Kartu Tanda Penduduk (KTP)'),
                        'nomor_kartu' => sprintf('3273%08d%03d', 88000000 + $number, $number),
                        'alamat_kartu' => sprintf('Jl. Contoh Sehat No. %d', $number),
                        'rt_kartu' => sprintf('%03d', $number),
                        'rw_kartu' => '002',
                        'kode_pos_kartu' => $postalCode,
                        'province_id_kartu' => $region['province'],
                        'regency_id_kartu' => $region['regency'],
                        'district_id_kartu' => $region['district'],
                        'village_id_kartu' => $region['village'],
                    ]
                );

                PasienKontak::updateOrCreate(
                    ['pasien_id' => $patient->id, 'jenis_kontak_detail_id' => $reference('Jenis Kontak', 'Telepon Seluler')],
                    ['nomor_kontak' => $phone]
                );

                PasienKeluarga::updateOrCreate(
                    ['pasien_id' => $patient->id],
                    [
                        'status_keluarga_detail_id' => $reference('Status Hubungan Dalam Keluarga', $familyRelation),
                        'nama' => $familyName,
                        'jenis_kelamin' => $familyRelation === 'Istri' ? 'Perempuan' : 'Laki-Laki',
                        'tanggal_lahir' => '1980-06-12',
                        'pendidikan_detail_id' => $reference('Pendidikan', 'SLTA/Sederajat'),
                        'pekerjaan_detail_id' => $reference('Pekerjaan', 'Karyawan Swasta'),
                        'alamat' => sprintf('Jl. Contoh Sehat No. %d', $number),
                        'jenis_kartu_detail_id' => $reference('Jenis Kartu Identitas', 'Kartu Keluarga'),
                        'nomor_kartu' => sprintf('3273%08d%03d', 77000000 + $number, $number),
                        'alamat_kartu' => sprintf('Jl. Contoh Sehat No. %d', $number),
                        'rt' => sprintf('%03d', $number),
                        'rw' => '002',
                        'kode_pos' => $postalCode,
                        'province_id' => $region['province'],
                        'regency_id' => $region['regency'],
                        'district_id' => $region['district'],
                        'village_id' => $region['village'],
                        'telepon_seluler' => $phone,
                    ]
                );
            }
        });
    }

    private function referenceId(string $category, string $description): int
    {
        $id = ReferensiDetail::whereHas('referensi', fn ($query) => $query->where('nama', $category))
            ->where('deskripsi', $description)
            ->value('id');

        if (! $id) {
            throw new RuntimeException("Referensi {$category} / {$description} tidak ditemukan.");
        }

        return $id;
    }

    private function regionPath(): array
    {
        $province = DB::table('indonesia_regions')->where('code', 'not like', '%.%')->orderBy('code')->first();
        $regency = $province ? DB::table('indonesia_regions')->where('code', 'like', $province->code . '.%')->orderBy('code')->first() : null;
        $district = $regency ? DB::table('indonesia_regions')->where('code', 'like', $regency->code . '.%')->orderBy('code')->first() : null;
        $village = $district ? DB::table('indonesia_regions')->where('code', 'like', $district->code . '.%')->orderBy('code')->first() : null;

        if (! $province || ! $regency || ! $district || ! $village) {
            throw new RuntimeException('Data wilayah Indonesia belum lengkap untuk seeder pasien.');
        }

        return ['province' => $province->code, 'regency' => $regency->code, 'district' => $district->code, 'village' => $village->code];
    }
}
