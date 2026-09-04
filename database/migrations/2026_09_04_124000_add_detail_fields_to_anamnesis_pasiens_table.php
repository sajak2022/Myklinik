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
        Schema::table('anamnesis_pasiens', function (Blueprint $table) {
            $table->string('kesediaan_menerima_edukasi', 20)->default('Ya')->nullable();
            $table->string('ada_hambatan_edukasi', 20)->default('Tidak')->nullable();
            $table->string('butuh_penerjemah', 100)->default('Tidak')->nullable();
            $table->json('alat_bantu_array')->nullable();
            $table->string('alat_bantu_lainnya')->nullable();
            $table->string('cacat_tubuh_pilihan', 20)->default('Tidak Ada')->nullable();
            $table->string('cacat_tubuh_keterangan')->nullable();
            $table->json('skrining_batuk')->nullable();
            $table->text('skrining_batuk_keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anamnesis_pasiens', function (Blueprint $table) {
            $table->dropColumn([
                'kesediaan_menerima_edukasi',
                'ada_hambatan_edukasi',
                'butuh_penerjemah',
                'alat_bantu_array',
                'alat_bantu_lainnya',
                'cacat_tubuh_pilihan',
                'cacat_tubuh_keterangan',
                'skrining_batuk',
                'skrining_batuk_keterangan',
            ]);
        });
    }
};
