<?php

namespace App\Support;

class ReferensiOpsi
{
    public static function agama(): array
    {
        return [
            'Islam'                                      => 'Islam',
            'Kristen (Protestan)'                        => 'Kristen (Protestan)',
            'Katolik'                                    => 'Katolik',
            'Hindu'                                      => 'Hindu',
            'Buddha'                                     => 'Buddha',
            'Konghucu'                                   => 'Konghucu',
            'Kepercayaan Terhadap Tuhan YME / Penghayat' => 'Kepercayaan Terhadap Tuhan YME / Penghayat',
            'Lainnya'                                    => 'Lainnya',
        ];
    }

    public static function statusPerkawinan(): array
    {
        return [
            'Belum Kawin'   => 'Belum Kawin',
            'Kawin'         => 'Kawin',
            'Cerai Hidup'   => 'Cerai Hidup',
            'Cerai Mati'    => 'Cerai Mati',
        ];
    }

    public static function pendidikan(): array
    {
        return [
            'Tidak/Belum Sekolah'         => 'Tidak/Belum Sekolah',
            'Belum Tamat SD/Sederajat'    => 'Belum Tamat SD/Sederajat',
            'Tamat SD/Sederajat'          => 'Tamat SD/Sederajat',
            'SLTP/Sederajat'              => 'SLTP/Sederajat',
            'SLTA/Sederajat'              => 'SLTA/Sederajat',
            'Diploma I/II'                => 'Diploma I/II',
            'Akademi/Diploma III/S. Muda' => 'Akademi/Diploma III/S. Muda',
            'Diploma IV/Strata I'         => 'Diploma IV/Strata I',
            'Strata II'                   => 'Strata II',
            'Strata III'                  => 'Strata III',
            'Lainnya'                     => 'Lainnya',
        ];
    }

    public static function pekerjaan(): array
    {
        return [
            'Pegawai Negeri Sipil'     => 'Pegawai Negeri Sipil (PNS/ASN)',
            'TNI / POLRI'              => 'TNI / POLRI',
            'Pegawai Swasta'           => 'Pegawai Swasta',
            'BUMN / BUMD'              => 'BUMN / BUMD',
            'Wiraswasta / Pengusaha'   => 'Wiraswasta / Pengusaha',
            'Petani / Peternak'        => 'Petani / Peternak',
            'Nelayan'                  => 'Nelayan',
            'Buruh / Pekerja Lepas'    => 'Buruh / Pekerja Lepas',
            'Pensiunan'                => 'Pensiunan',
            'Pelajar / Mahasiswa'      => 'Pelajar / Mahasiswa',
            'Ibu Rumah Tangga'         => 'Ibu Rumah Tangga',
            'Belum / Tidak Bekerja'    => 'Belum / Tidak Bekerja',
            'Tenaga Medis / Kesehatan' => 'Tenaga Medis / Kesehatan',
            'Lainnya'                  => 'Lainnya',
        ];
    }

    public static function golonganDarah(): array
    {
        return [
            'A'          => 'A',
            'B'          => 'B',
            'AB'         => 'AB',
            'O'          => 'O',
            'A+'         => 'A+',
            'A-'         => 'A-',
            'B+'         => 'B+',
            'B-'         => 'B-',
            'AB+'        => 'AB+',
            'AB-'        => 'AB-',
            'O+'         => 'O+',
            'O-'         => 'O-',
            'Tidak Tahu' => 'Tidak Tahu',
        ];
    }

    public static function sukuBangsa(): array
    {
        return [
            'Jawa'        => 'Jawa',
            'Sunda'       => 'Sunda',
            'Batak'       => 'Batak',
            'Minangkabau' => 'Minangkabau',
            'Betawi'      => 'Betawi',
            'Bugis'       => 'Bugis',
            'Melayu'      => 'Melayu',
            'Banjar'      => 'Banjar',
            'Bali'        => 'Bali',
            'Sasak'       => 'Sasak',
            'Dayak'       => 'Dayak',
            'Makassar'    => 'Makassar',
            'Cirebon'     => 'Cirebon',
            'Madura'      => 'Madura',
            'Papua'       => 'Papua',
            'Lainnya'     => 'Lainnya',
        ];
    }

    public static function jenisKartu(): array
    {
        return [
            'Kartu Tanda Penduduk (KTP)' => 'Kartu Tanda Penduduk (KTP)',
            'Kartu Identitas Anak (KIA)'  => 'Kartu Identitas Anak (KIA)',
            'Surat Izin Mengemudi (SIM)'  => 'Surat Izin Mengemudi (SIM)',
            'Paspor'                      => 'Paspor',
            'BPJS / KIS'                  => 'BPJS / KIS',
            'Kartu Keluarga (KK)'         => 'Kartu Keluarga (KK)',
            'Kartu Mahasiswa / Pelajar'   => 'Kartu Mahasiswa / Pelajar',
            'Lainnya'                     => 'Lainnya',
        ];
    }

    public static function jenisKontak(): array
    {
        return [
            'Telepon Seluler' => 'Telepon Seluler / WhatsApp',
            'Telepon Rumah'   => 'Telepon Rumah',
            'Telepon Kantor'  => 'Telepon Kantor',
            'Email'           => 'Email',
            'Lainnya'         => 'Lainnya',
        ];
    }

    public static function statusKeluarga(): array
    {
        return [
            'Kepala Keluarga' => 'Kepala Keluarga',
            'Suami'           => 'Suami',
            'Istri'           => 'Istri',
            'Anak'            => 'Anak',
            'Orang Tua'       => 'Orang Tua (Ayah / Ibu)',
            'Mertua'          => 'Mertua',
            'Menantu'         => 'Menantu',
            'Cucu'            => 'Cucu',
            'Saudara Kandung' => 'Saudara Kandung',
            'Kakek / Nenek'   => 'Kakek / Nenek',
            'Paman / Bibi'    => 'Paman / Bibi',
            'Wali / Kerabat'  => 'Wali / Kerabat',
            'Lainnya'         => 'Lainnya',
        ];
    }

    public static function profesiPegawai(): array
    {
        return [
            'Dokter'                         => 'Dokter',
            'Perawat'                        => 'Perawat',
            'Bidan'                          => 'Bidan',
            'Apoteker'                       => 'Apoteker',
            'Tenaga Teknis Kefarmasian'      => 'Tenaga Teknis Kefarmasian',
            'Perekam Medis'                  => 'Perekam Medis',
            'Nutrisionis / Dietisien'        => 'Nutrisionis / Dietisien',
            'Fisioterapis'                   => 'Fisioterapis',
            'Pranata Laboratorium Kesehatan' => 'Pranata Laboratorium Kesehatan',
            'Radiografer'                    => 'Radiografer',
            'Sanitarian / Kesling'           => 'Sanitarian / Kesling',
            'Staf Administrasi'              => 'Staf Administrasi',
            'Manajemen / Struktural'         => 'Manajemen / Struktural',
            'Lainnya'                        => 'Lainnya',
        ];
    }

    public static function jenisSpesialis(): array
    {
        return [
            'Dokter Umum'                                => 'Dokter Umum',
            'Dokter Gigi'                                => 'Dokter Gigi',
            'Spesialis Penyakit Dalam (Sp.PD)'           => 'Spesialis Penyakit Dalam (Sp.PD)',
            'Spesialis Anak (Sp.A)'                      => 'Spesialis Anak (Sp.A)',
            'Spesialis Bedah (Sp.B)'                     => 'Spesialis Bedah (Sp.B)',
            'Spesialis Obstetri & Ginekologi (Sp.OG)'    => 'Spesialis Obstetri & Ginekologi (Sp.OG)',
            'Spesialis Jantung & Pembuluh Darah (Sp.JP)' => 'Spesialis Jantung & Pembuluh Darah (Sp.JP)',
            'Spesialis Paru (Sp.P)'                      => 'Spesialis Paru (Sp.P)',
            'Spesialis Mata (Sp.M)'                      => 'Spesialis Mata (Sp.M)',
            'Spesialis THT-KL (Sp.THT-KL)'               => 'Spesialis THT-KL (Sp.THT-KL)',
            'Spesialis Saraf / Neurologi (Sp.N)'         => 'Spesialis Saraf / Neurologi (Sp.N)',
            'Spesialis Jiwa / Psikiatri (Sp.KJ)'         => 'Spesialis Jiwa / Psikiatri (Sp.KJ)',
            'Spesialis Kulit & Kelamin (Sp.DV)'          => 'Spesialis Kulit & Kelamin (Sp.DV)',
            'Spesialis Radiologi (Sp.Rad)'               => 'Spesialis Radiologi (Sp.Rad)',
            'Spesialis Anestesiologi (Sp.An)'            => 'Spesialis Anestesiologi (Sp.An)',
            'Spesialis Patologi Klinik (Sp.PK)'          => 'Spesialis Patologi Klinik (Sp.PK)',
            'Lainnya'                                    => 'Lainnya',
        ];
    }
}

