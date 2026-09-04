<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('anamnesis_pasiens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftarans')->cascadeOnDelete();
            $table->foreignId('pasien_id')->constrained('pasiens')->cascadeOnDelete();
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawais')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('waktu_anamnesis');

            // 1. Keluhan Utama
            $table->text('keluhan_utama')->nullable();

            // 2. Anamnesis Diperoleh
            $table->string('sumber_anamnesis', 50)->default('Autoanamnesis');
            $table->string('nama_sumber_informasi')->nullable();
            $table->string('hubungan_sumber_informasi', 100)->nullable();

            // 3. Riwayat Penyakit Sekarang (Umum)
            $table->text('riwayat_penyakit_sekarang')->nullable();

            // 4. Riwayat (Penyakit Dahulu, Keluarga, Alergi, Pengobatan)
            $table->text('riwayat_penyakit_dahulu')->nullable();
            $table->text('riwayat_penyakit_keluarga')->nullable();
            $table->text('riwayat_alergi')->nullable();
            $table->text('riwayat_pengobatan')->nullable();

            // 5. Status Fungsional
            $table->string('status_fungsional', 50)->default('Mandiri');
            $table->string('alat_bantu')->nullable();
            $table->string('cacat_tubuh')->nullable();

            // 6. Hubungan Status Psikososial
            $table->string('status_psikologis', 50)->default('Tenang');
            $table->string('status_mental', 100)->nullable();
            $table->string('hubungan_keluarga', 50)->default('Baik');
            $table->string('tinggal_bersama', 100)->nullable();
            $table->string('nilai_kepercayaan_agama')->nullable();

            // 7. Edukasi
            $table->json('kebutuhan_edukasi')->nullable();
            $table->json('hambatan_belajar')->nullable();
            $table->string('penerima_edukasi', 50)->default('Pasien');

            // 8. Skrining Gizi Awal (MST)
            $table->string('penurunan_bb', 100)->nullable();
            $table->integer('skor_penurunan_bb')->default(0);
            $table->string('asupan_makan_berkurang', 50)->nullable();
            $table->integer('skor_asupan_makan')->default(0);
            $table->string('kondisi_khusus_gizi')->nullable();
            $table->integer('total_skor_gizi')->default(0);
            $table->string('kategori_gizi', 50)->default('Risiko Rendah');

            // 9. Masuk
            $table->string('cara_masuk', 50)->default('Datang Sendiri');
            $table->string('asal_rujukan')->nullable();
            $table->text('alasan_kunjungan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anamnesis_pasiens');
    }
};
