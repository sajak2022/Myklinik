<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah tipe kolom status_pelayanan menjadi VARCHAR(50) agar mendukung semua tahapan workflow
        Schema::table('pendaftarans', function (Blueprint $table) {
            $table->string('status_pelayanan', 50)->default('Menunggu')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftarans', function (Blueprint $table) {
            $table->string('status_pelayanan', 50)->default('Menunggu')->change();
        });
    }
};
