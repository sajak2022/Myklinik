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
        Schema::table('pendaftarans', function (Blueprint $table) {
            $columnsToDrop = [
                'general_consent',
                'consent_satusehat',
                'resiko_jatuh',
                'cara_masuk',
                'penjamin',
                'no_asuransi',
                'no_sep',
                'kelas_rawat',
                'is_rujukan',
                'faskes_perujuk',
                'no_rujukan',
                'tgl_rujukan',
                'dokter_perujuk',
                'diagnosis_rujukan',
                'pj_tgl_lahir',
                'pengantar_nama',
                'pengantar_hubungan',
                'pengantar_no_telepon',
                'pengantar_alamat',
                'biaya_pendaftaran',
                'status_pembayaran',
            ];

            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('pendaftarans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftarans', function (Blueprint $table) {
            $table->boolean('general_consent')->default(true);
            $table->boolean('consent_satusehat')->default(true);
            $table->boolean('resiko_jatuh')->default(false);
            $table->string('cara_masuk', 50)->default('Datang Sendiri');
            $table->string('penjamin', 50)->default('Umum / Mandiri');
            $table->string('no_asuransi', 100)->nullable();
            $table->string('no_sep', 100)->nullable();
            $table->string('kelas_rawat', 50)->nullable();
            $table->boolean('is_rujukan')->default(false);
            $table->string('faskes_perujuk')->nullable();
            $table->string('no_rujukan')->nullable();
            $table->date('tgl_rujukan')->nullable();
            $table->string('dokter_perujuk')->nullable();
            $table->string('diagnosis_rujukan')->nullable();
            $table->date('pj_tgl_lahir')->nullable();
            $table->string('pengantar_nama')->nullable();
            $table->string('pengantar_hubungan', 50)->nullable();
            $table->string('pengantar_no_telepon', 30)->nullable();
            $table->text('pengantar_alamat')->nullable();
            $table->decimal('biaya_pendaftaran', 12, 2)->default(0);
            $table->enum('status_pembayaran', ['Belum Lunas', 'Lunas', 'Gratis'])->default('Gratis');
        });
    }
};
