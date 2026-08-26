# 🏥 Myklinik - Sistem Informasi Manajemen Klinik & Rekam Medis Elektronik (RME)

<p align="center">
  <img src="public/storage/logo/logo.png" alt="Myklinik Logo" width="220" onerror="this.style.display='none'">
</p>

<p align="center">
  <strong>Myklinik</strong> adalah Sistem Informasi Manajemen Klinik dan Rekam Medis Elektronik (RME) modern berbasis web yang dirancang untuk mengelola seluruh alur operasional klinik secara komprehensif, mulai dari loket pendaftaran, pemanggilan antrian, pemeriksaan klinis dokter & perawat, catatan perkembangan pasien terintegrasi (CPPT), data kepegawaian & perizinan (STR/SIP), inventaris obat & barang farmasi, hingga integrasi database wilayah Indonesia.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x%20%7C%2012.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Filament-v3%20%2F%20v4-FDAE4B?style=for-the-badge&logo=filament&logoColor=black" alt="Filament">
  <img src="https://img.shields.io/badge/Livewire-3.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge" alt="License">
</p>

---

## 🌟 Fitur Unggulan Sistem

### 1. 🏥 Loket Pendaftaran & Admisi (*Front Office*)
- **Registrasi Kunjungan Pasien**: Pendaftaran kunjungan baru maupun pasien lama dengan pencarian cepat (*No. RM / NIK / Nama*).
- **Penomoran Otomatis (*Auto-Numbering*)**: Nomor registrasi unik berformat `REG-YYYYMMDD-XXXX` dan nomor antrian per poli secara *real-time*.
- **Multi-Penjamin & Tarif**: Mendukung pasien Umum, Pegawai/Kementerian, BPJS Kesehatan, dan Asuransi Swasta.
- **Master Data Pasien Terstruktur**: Data identitas lengkap, NIK, alamat bertingkat wilayah Indonesia, data kontak darurat, penanggung jawab/keluarga, serta instansi kepegawaian (**Eselon 1** & **Eselon 2**).

### 2. 📋 Monitoring Antrian & Kunjungan Pasien (*Live Queue*)
- **Papan Antrian Interaktif**: Tampilan kartu antrian pasien yang rapi dengan nomor antrian tebal, identitas pasien, poli tujuan, DPJP, dan status pelayanan (*Menunggu, Sedang Dilayani, Selesai, Batal*).
- **Otorisasi Sesuai Penugasan Poli**: Perawat dan Dokter hanya melihat antrian sesuai poli penugasan mereka (misal: Perawat Poli Gigi hanya melihat antrian Poli Gigi).
- **Alur Kerja Terstandarisasi**:
  - **Perawat**: Menerima pasien (*Terima*) dan membatalkan antrian (*Batal*).
  - **Dokter**: Melakukan pemeriksaan (*Pemeriksaan*) dan menyelesaikan kunjungan (*Selesai*).

### 3. 🩺 Cluster Detail Kunjungan (Pemeriksaan & CPPT)
- **Header Identitas Pasien Terpusat (*Single Modular Header*)**:
  - Menampilkan No. RM, Nama Pasien (dapat diklik untuk melihat rekam medis lengkap), Jenis Kelamin, Usia presisi (*Tahun, Bulan, Hari*), Status Pasien.
  - Instansi Kepegawaian: **Eselon 1**, **Eselon 2**, NIP, dan Status ASN.
  - DPJP Pemeriksa, Poli, Waktu Masuk/Keluar, Status Pelayanan, dan Kelas Rawat.
- **Modul Pemeriksaan Pasien**:
  - Status Kesadaran & Keadaan Umum.
  - **Glasgow Coma Scale (GCS)**: Skor Eye, Motorik, Verbal dengan kalkulasi otomatis total GCS.
  - **Tanda-Tanda Vital Lengkap**: Tekanan Darah Sistolik/Diastolik, Frekuensi Nadi, Frekuensi Nafas, Suhu Tubuh, Saturasi O2, Alat Bantu Nafas, Tinggi/Berat Badan, Lingkar Kepala, Lingkar Perut, dan IMT (*Indeks Massa Tubuh*).
  - **Early Warning Scoring System (EWSS)**: Penilaian skor risiko klinis otomatis dengan indikator warna kategori risiko (*Hijau / Kuning / Oranye / Merah*).
  - **Default 0 & Auto-Reset**: Form terisi default `0` dan otomatis ter-reset saat mengganti pasien.
  - **Riwayat Catatan Medis Sebelumnya**: Menampilkan riwayat vital signs kunjungan sebelumnya di sisi kanan.
- **Modul CPPT (*Catatan Perkembangan Pasien Terintegrasi*)**:
  - Format SOAP (*Subjektif, Objektif, Assessment, Planning*), **SBAR**, dan **TBAK**.
  - Deteksi otomatis PPA (NIP + Nama) dan Profesi dari akun petugas yang sedang login.
  - 3 Rich Text Editor terintegrasi untuk input catatan medis yang detail dan fleksibel.
  - **Kartu Riwayat CPPT Berwarna Primary**: Tampilan kartu riwayat catatan CPPT dengan status verifikasi DPJP, tombol verifikasi, dan tombol hapus.
- **Navigasi Sticky Bebas Benturan**:
  - Menu navigasi samping terkunci (*sticky*) pada layar desktop, dan 100% responsif alami pada perangkat mobile/tablet.

### 4. 👥 Manajemen Pengguna & Kepegawaian (*Cluster*)
- **Data Pegawai Medis & Non-Medis**: Pengelolaan dokter umum, dokter gigi, perawat, bidan, apoteker, staf administrasi.
- **Legalitas Profesi**: Pengelolaan Nomor SIP, masa berlaku SIP, Nomor STR, dan masa berlaku STR dengan notifikasi masa aktif.
- **Integrasi Akun User**: Penautan langsung data pegawai ke akun login pengguna sistem.
- **Role-Based Access Control (Filament Shield)**: Pengaturan izin akses granular per menu dan aksi untuk Super Admin, Dokter, Perawat, Pendaftaran, dan Apoteker.

### 5. 📦 Manajemen Barang & Farmasi (*Cluster*)
- Master data obat dan barang medis/non-medis.
- Kategori barang, satuan barang (*Pcs, Box, Strip, Botol, dll.*), dan direktori penyedia/vendor distributor.

### 6. 🗺️ Basis Data Wilayah Indonesia (*Cluster*)
- Database resmi **91.162+ data wilayah Indonesia** (`aliziodev/laravel-indonesia-regions`):
  - 38 Provinsi
  - 514 Kabupaten / Kota
  - 7.277 Kecamatan
  - 83.333+ Desa / Kelurahan beserta Kode Pos.

### 7. ⚙️ Master Data & Pengaturan Instansi
- Pengaturan identitas klinik, logo *Light/Dark Mode*, favicon, nama instansi, tinggi logo (CSS), dan durasi kunci sesi otomatis (*session timeout*).
- Master Poli, Tindakan Medis & Tarif, Unit Eksternal / Instansi Eselon, dan Referensi Dinamis (Agama, Golongan Darah, Pendidikan, dll.).

---

## 💻 Kebutuhan Sistem (*System Requirements*)

Pastikan server atau komputer lokal Anda telah memenuhi spesifikasi berikut:

| Komponen | Versi Minimum | Keterangan |
| :--- | :--- | :--- |
| **PHP** | `^8.2` atau `^8.3` | Ekstensi aktif: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `intl`, `fileinfo`, `gd`, `exif`, `xml` |
| **Database** | MySQL `^8.0` / MariaDB `^10.4` | Direkomendasikan MySQL 8.0+ |
| **Web Server** | Apache / Nginx / Laragon | Direkomendasikan Laragon untuk Windows |
| **Composer** | `^2.5` | Manajemen dependensi PHP |
| **Node.js & NPM** | Node `^18.x` / `^20.x` & NPM `^10.x` | Kompilasi aset Vite & Tailwind CSS |

---

## 🚀 Panduan Instalasi Langkah-demi-Langkah (*Error-Free Installation*)

Ikuti langkah-langkah berikut secara berurutan untuk memasang dan menjalankan aplikasi tanpa error:

### 1. Clone Repository
Buka terminal / Git Bash / PowerShell, lalu clone repository ini:
```bash
git clone https://github.com/TiyasTasya/Myklinik.git
cd Myklinik
```

### 2. Install Dependensi PHP (Composer)
```bash
composer install
```

### 3. Install Dependensi Frontend (NPM)
```bash
npm install
```

### 4. Konfigurasi File Environment (`.env`)
Salin file template `.env.example` menjadi `.env`:

*Untuk Windows (PowerShell / Command Prompt):*
```powershell
copy .env.example .env
```
*Untuk Linux / macOS / Git Bash:*
```bash
cp .env.example .env
```

Lalu generate Application Encryption Key:
```bash
php artisan key:generate
```

### 5. Buat Basis Data MySQL & Sesuaikan `.env`
Buka phpMyAdmin / MySQL CLI / HeidiSQL / DBeaver, lalu buat database baru bernama **`database`** (atau nama database sesuai keinginan Anda):
```sql
CREATE DATABASE `database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Buka file **`.env`** di text editor Anda dan sesuaikan konfigurasi koneksi database:
```env
APP_NAME=Myklinik
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Hubungkan Folder Storage (*Symlink*)
Buat tautan simbolis folder storage publik untuk foto profil dan logo klinik:
```bash
php artisan storage:link
```

### 7. Jalankan Migrasi Basis Data & Seeder Data Awal
Eksekusi migrasi tabel dan data awal referensi:
```bash
php artisan migrate --seed
```

### 8. Sinkronisasi Data Wilayah Indonesia (Provinsi, Kota, Kecamatan, Kelurahan)
Jalankan perintah sinkronisasi wilayah resmi Indonesia (memerlukan waktu ~1-2 menit):
```bash
php artisan indonesia-regions:sync
```

### 9. Generate Permissions & Kebijakan Akses (Filament Shield)
Generate seluruh hak akses dan policy otorisasi:
```bash
php artisan shield:generate --all --panel=admin --option=policies_and_permissions --no-interaction
```

### 10. Buat Akun Super Admin
Jalankan perintah berikut untuk membuat akun Super Admin pertama Anda:
```bash
php artisan shield:super-admin
```
> Masukkan nama, email (contoh: `admin@myklinik.test`), dan password sesuai yang Anda inginkan.

### 11. Kompilasi Aset Frontend (Vite)
Kompilasi seluruh stylesheet Tailwind CSS dan tema Filament:
```bash
npm run build
```

### 12. Bersihkan Cache Aplikasi
Pastikan seluruh cache konfigurasi, view, dan routing bersih:
```bash
php artisan optimize:clear
```

### 13. Jalankan Server Aplikasi
Jalankan server pengembangan Laravel:
```bash
php artisan serve
```

Buka browser Anda dan akses aplikasi pada URL:
👉 **[http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)**

---

## 🔑 Akun Pengguna Demo / Default (*Ready to Test*)

Jika Anda menjalankan seeder default, berikut adalah kredensial akun bawaan untuk pengujian alur peran klinis:

| Peran (*Role*) | Email / Username | Password | Penugasan Unit |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@myklinik.test` *(atau yang dibuat via shield)* | *sesuai input* | Semua Modul Sistem |
| **Dokter Umum** | `dokter.umum@myklinik.test` | `password` | Poli Umum |
| **Dokter Gigi** | `dokter.gigi@myklinik.test` | `password` | Poli Gigi |
| **Perawat Poli Umum** | `perawat@myklinik.test` | `password` | Poli Umum |
| **Perawat Poli Gigi** | `perawat.gigi@myklinik.test` | `password` | Poli Gigi |
| **Pendaftaran / Kasir** | `pendaftaran@myklinik.test` | `password` | Loket Pendaftaran |

---

## 🛠️ Perintah Berguna (*Useful Commands*)

| Kebutuhan | Perintah |
| :--- | :--- |
| **Mode Pengembangan Frontend (Hot Reload)** | `npm run dev` |
| **Kompilasi Aset Produksi** | `npm run build` |
| **Reset Database & Muat Ulang Seluruh Data** | `php artisan migrate:fresh --seed` |
| **Clear Cache View, Config, & Route** | `php artisan optimize:clear` |
| **Sinkron Ulang Izin Akses Filament Shield** | `php artisan shield:generate --all --panel=admin --no-interaction` |

---

## ❓ Panduan Pemecahan Masalah (*Troubleshooting*)

<details>
<summary><strong>1. Muncul error "Vite manifest not found" atau tampilan CSS berantakan</strong></summary>

Jalankan perintah build aset Vite:
```bash
npm run build
php artisan optimize:clear
```
Lalu lakukan **Hard Refresh** pada browser Anda (**Ctrl + Shift + R** atau **Ctrl + F5**).
</details>

<details>
<summary><strong>2. Muncul error 403 (Unauthorized / Forbidden) saat mengakses menu tertentu</strong></summary>

Jalankan perintah pembuatan ulang izin Shield:
```bash
php artisan shield:generate --all --panel=admin --option=policies_and_permissions --no-interaction
php artisan optimize:clear
```
Pastikan akun yang login telah memiliki peran (*Role*) yang sesuai melalui menu **Manajemen Pengguna > User**.
</details>

<details>
<summary><strong>3. Gambar logo atau foto profil pasien tidak tampil</strong></summary>

Pastikan symlink storage telah dibuat:
```bash
php artisan storage:link
```
</details>

<details>
<summary><strong>4. Proses `indonesia-regions:sync` mengalami memory limit atau timeout</strong></summary>

Tingkatkan batas memory PHP sementara saat menjalankan perintah:
```bash
php -d memory_limit=512M artisan indonesia-regions:sync
```
</details>

---

## 📂 Arsitektur Direktori Proyek

```
Myklinik/
├── app/
│   ├── Filament/
│   │   ├── Clusters/
│   │   │   ├── DetailKunjungan/
│   │   │   │   ├── Pages/PemeriksaanPasien.php  # Pemeriksaan Vital Signs, GCS, EWSS
│   │   │   │   └── Pages/CpptPasien.php         # Catatan Perkembangan Pasien (CPPT SOAP)
│   │   │   ├── DetailKunjungan.php              # Cluster Detail Kunjungan Pasien
│   │   │   ├── ManajemenBarang.php              # Cluster Obat & Barang Farmasi
│   │   │   ├── ManajemenPengguna.php            # Cluster Pegawai, User, & Peran
│   │   │   ├── Pengaturan.php                   # Cluster Pengaturan Instansi & Logo
│   │   │   └── Wilayah.php                      # Cluster Data Wilayah Indonesia
│   │   ├── Pages/
│   │   │   └── KunjunganPasien.php              # Live Queue Monitoring Kunjungan
│   │   ├── Resources/
│   │   │   ├── Pasiens/                         # Master Pasien & Rekam Medis
│   │   │   ├── Pegawais/                        # Master Pegawai (SIP, STR, Profesi)
│   │   │   ├── Pendaftarans/                    # Pendaftaran Kunjungan & Antrian
│   │   │   ├── Polis/                           # Master Poli Klinik
│   │   │   └── Users/                           # Master Akun Login
│   ├── Models/                                  # Eloquent Models
│   └── Policies/                                # Policy Keamanan Filament Shield
├── resources/
│   ├── css/filament/admin/theme.css             # Kustomisasi CSS & Tema Filament
│   └── views/filament/
│       ├── clusters/detail-kunjungan/components/
│       │   └── patient-header.blade.php         # Reusable Patient Header Banner
│       ├── pages/
│       │   ├── pemeriksaan-pasien.blade.php     # Template View Pemeriksaan Pasien
│       │   ├── cppt-pasien.blade.php            # Template View CPPT SOAP Cards
│       │   └── kunjungan-pasien.blade.php       # Template View Live Antrian
│       └── tables/columns/
│           ├── kunjungan-card.blade.php         # Kartu Informasi Kunjungan Pasien
│           └── antrian-box.blade.php            # Kotak Nomor Antrian Primary Solid
├── database/
│   ├── migrations/                              # Struktur Basis Data
│   └── seeders/                                 # Data Awal & Demo
└── routes/
    └── web.php                                  # Rute Web Laravel
```

---

## 📄 Lisensi

Proyek ini dikembangkan di bawah lisensi terbuka **[MIT License](LICENSE)**.
Dibuat dengan ❤️ untuk sistem manajemen pelayanan kesehatan yang efisien, modern, dan andal.
