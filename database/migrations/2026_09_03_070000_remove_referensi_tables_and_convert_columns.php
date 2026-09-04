<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom string baru pada tabel-tabel terkait jika belum ada
        Schema::table('pasiens', function (Blueprint $table) {
            if (!Schema::hasColumn('pasiens', 'agama')) {
                $table->string('agama')->nullable()->after('jenis_kelamin');
            }
            if (!Schema::hasColumn('pasiens', 'status_perkawinan')) {
                $table->string('status_perkawinan')->nullable()->after('agama');
            }
            if (!Schema::hasColumn('pasiens', 'pendidikan')) {
                $table->string('pendidikan')->nullable()->after('status_perkawinan');
            }
            if (!Schema::hasColumn('pasiens', 'pekerjaan')) {
                $table->string('pekerjaan')->nullable()->after('pendidikan');
            }
            if (!Schema::hasColumn('pasiens', 'golongan_darah')) {
                $table->string('golongan_darah')->nullable()->after('pekerjaan');
            }
            if (!Schema::hasColumn('pasiens', 'suku_bangsa')) {
                $table->string('suku_bangsa')->nullable()->after('golongan_darah');
            }
            if (!Schema::hasColumn('pasiens', 'jenis_kartu')) {
                $table->string('jenis_kartu')->nullable()->after('sama_dengan_alamat_sekarang');
            }
        });

        Schema::table('pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawais', 'agama')) {
                $table->string('agama')->nullable()->after('jenis_kelamin');
            }
            if (!Schema::hasColumn('pegawais', 'jenis_spesialis')) {
                $table->string('jenis_spesialis')->nullable()->after('profesi');
            }
            if (!Schema::hasColumn('pegawais', 'jenis_kartu')) {
                $table->string('jenis_kartu')->nullable()->after('sip_berlaku_sampai');
            }
        });

        Schema::table('pasien_keluargas', function (Blueprint $table) {
            if (!Schema::hasColumn('pasien_keluargas', 'status_keluarga')) {
                $table->string('status_keluarga')->nullable()->after('pasien_id');
            }
            if (!Schema::hasColumn('pasien_keluargas', 'pendidikan')) {
                $table->string('pendidikan')->nullable()->after('tanggal_lahir');
            }
            if (!Schema::hasColumn('pasien_keluargas', 'pekerjaan')) {
                $table->string('pekerjaan')->nullable()->after('pendidikan');
            }
            if (!Schema::hasColumn('pasien_keluargas', 'jenis_kartu')) {
                $table->string('jenis_kartu')->nullable()->after('alamat');
            }
        });

        Schema::table('pasien_kontaks', function (Blueprint $table) {
            if (!Schema::hasColumn('pasien_kontaks', 'jenis_kontak')) {
                $table->string('jenis_kontak')->nullable()->after('pasien_id');
            }
        });

        Schema::table('kontak_pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('kontak_pegawais', 'jenis_kontak')) {
                $table->string('jenis_kontak')->nullable()->after('pegawai_id');
            }
        });

        // 2. Salin data dari referensi_details ke kolom string jika tabel referensi_details masih ada
        if (Schema::hasTable('referensi_details')) {
            // Pasiens
            DB::statement("UPDATE pasiens p INNER JOIN referensi_details rd ON p.agama_detail_id = rd.id SET p.agama = rd.deskripsi WHERE p.agama_detail_id IS NOT NULL");
            DB::statement("UPDATE pasiens p INNER JOIN referensi_details rd ON p.status_perkawinan_detail_id = rd.id SET p.status_perkawinan = rd.deskripsi WHERE p.status_perkawinan_detail_id IS NOT NULL");
            DB::statement("UPDATE pasiens p INNER JOIN referensi_details rd ON p.pendidikan_detail_id = rd.id SET p.pendidikan = rd.deskripsi WHERE p.pendidikan_detail_id IS NOT NULL");
            DB::statement("UPDATE pasiens p INNER JOIN referensi_details rd ON p.pekerjaan_detail_id = rd.id SET p.pekerjaan = rd.deskripsi WHERE p.pekerjaan_detail_id IS NOT NULL");
            DB::statement("UPDATE pasiens p INNER JOIN referensi_details rd ON p.golongan_darah_detail_id = rd.id SET p.golongan_darah = rd.deskripsi WHERE p.golongan_darah_detail_id IS NOT NULL");
            DB::statement("UPDATE pasiens p INNER JOIN referensi_details rd ON p.suku_bangsa_detail_id = rd.id SET p.suku_bangsa = rd.deskripsi WHERE p.suku_bangsa_detail_id IS NOT NULL");
            DB::statement("UPDATE pasiens p INNER JOIN referensi_details rd ON p.jenis_kartu_detail_id = rd.id SET p.jenis_kartu = rd.deskripsi WHERE p.jenis_kartu_detail_id IS NOT NULL");

            // Pegawais
            DB::statement("UPDATE pegawais pg INNER JOIN referensi_details rd ON pg.agama_detail_id = rd.id SET pg.agama = rd.deskripsi WHERE pg.agama_detail_id IS NOT NULL");
            DB::statement("UPDATE pegawais pg INNER JOIN referensi_details rd ON pg.jenis_spesialis_detail_id = rd.id SET pg.jenis_spesialis = rd.deskripsi WHERE pg.jenis_spesialis_detail_id IS NOT NULL");
            DB::statement("UPDATE pegawais pg INNER JOIN referensi_details rd ON pg.jenis_kartu_detail_id = rd.id SET pg.jenis_kartu = rd.deskripsi WHERE pg.jenis_kartu_detail_id IS NOT NULL");

            // Pasien Keluargas
            DB::statement("UPDATE pasien_keluargas pk INNER JOIN referensi_details rd ON pk.status_keluarga_detail_id = rd.id SET pk.status_keluarga = rd.deskripsi WHERE pk.status_keluarga_detail_id IS NOT NULL");
            DB::statement("UPDATE pasien_keluargas pk INNER JOIN referensi_details rd ON pk.pendidikan_detail_id = rd.id SET pk.pendidikan = rd.deskripsi WHERE pk.pendidikan_detail_id IS NOT NULL");
            DB::statement("UPDATE pasien_keluargas pk INNER JOIN referensi_details rd ON pk.pekerjaan_detail_id = rd.id SET pk.pekerjaan = rd.deskripsi WHERE pk.pekerjaan_detail_id IS NOT NULL");
            DB::statement("UPDATE pasien_keluargas pk INNER JOIN referensi_details rd ON pk.jenis_kartu_detail_id = rd.id SET pk.jenis_kartu = rd.deskripsi WHERE pk.jenis_kartu_detail_id IS NOT NULL");

            // Pasien Kontaks
            DB::statement("UPDATE pasien_kontaks pko INNER JOIN referensi_details rd ON pko.jenis_kontak_detail_id = rd.id SET pko.jenis_kontak = rd.deskripsi WHERE pko.jenis_kontak_detail_id IS NOT NULL");

            // Kontak Pegawais
            DB::statement("UPDATE kontak_pegawais kpg INNER JOIN referensi_details rd ON kpg.jenis_kontak_detail_id = rd.id SET kpg.jenis_kontak = rd.deskripsi WHERE kpg.jenis_kontak_detail_id IS NOT NULL");
        }

        // 3. Drop Foreign Keys dan Kolom *_detail_id
        Schema::table('pasiens', function (Blueprint $table) {
            $table->dropForeign(['agama_detail_id']);
            $table->dropForeign(['status_perkawinan_detail_id']);
            $table->dropForeign(['pendidikan_detail_id']);
            $table->dropForeign(['pekerjaan_detail_id']);
            $table->dropForeign(['golongan_darah_detail_id']);
            $table->dropForeign(['suku_bangsa_detail_id']);
            $table->dropForeign(['jenis_kartu_detail_id']);

            $table->dropColumn([
                'agama_detail_id',
                'status_perkawinan_detail_id',
                'pendidikan_detail_id',
                'pekerjaan_detail_id',
                'golongan_darah_detail_id',
                'suku_bangsa_detail_id',
                'jenis_kartu_detail_id',
            ]);
        });

        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropForeign(['agama_detail_id']);
            $table->dropForeign(['jenis_spesialis_detail_id']);
            $table->dropForeign(['jenis_kartu_detail_id']);

            $table->dropColumn([
                'agama_detail_id',
                'jenis_spesialis_detail_id',
                'jenis_kartu_detail_id',
            ]);
        });

        Schema::table('pasien_keluargas', function (Blueprint $table) {
            $table->dropForeign(['status_keluarga_detail_id']);
            $table->dropForeign(['pendidikan_detail_id']);
            $table->dropForeign(['pekerjaan_detail_id']);
            $table->dropForeign(['jenis_kartu_detail_id']);

            $table->dropColumn([
                'status_keluarga_detail_id',
                'pendidikan_detail_id',
                'pekerjaan_detail_id',
                'jenis_kartu_detail_id',
            ]);
        });

        Schema::table('pasien_kontaks', function (Blueprint $table) {
            $table->dropForeign(['jenis_kontak_detail_id']);
            $table->dropColumn('jenis_kontak_detail_id');
        });

        Schema::table('kontak_pegawais', function (Blueprint $table) {
            $table->dropForeign(['jenis_kontak_detail_id']);
            $table->dropColumn('jenis_kontak_detail_id');
        });

        // 4. Drop tabel referensi_details dan referensis
        Schema::dropIfExists('referensi_details');
        Schema::dropIfExists('referensis');
    }

    public function down(): void
    {
        // Re-create referensis table
        Schema::create('referensis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->nullable();
            $table->timestamps();
        });

        Schema::create('referensi_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referensi_id')->constrained('referensis')->cascadeOnDelete();
            $table->string('deskripsi');
            $table->integer('urutan')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }
};

