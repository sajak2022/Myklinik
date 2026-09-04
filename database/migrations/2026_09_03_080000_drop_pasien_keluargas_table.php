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
        Schema::dropIfExists('pasien_keluargas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('pasien_keluargas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('pasiens')->cascadeOnDelete();
            $table->string('status_keluarga')->nullable();
            $table->string('nama');
            $table->string('jenis_kelamin')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->text('alamat')->nullable();
            $table->string('jenis_kartu')->nullable();
            $table->string('nomor_kartu')->nullable();
            $table->text('alamat_kartu')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('province_id', 10)->nullable();
            $table->string('regency_id', 20)->nullable();
            $table->string('district_id', 20)->nullable();
            $table->string('village_id', 20)->nullable();
            $table->string('telepon_seluler')->nullable();
            $table->timestamps();
        });
    }
};
