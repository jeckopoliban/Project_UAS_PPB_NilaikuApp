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
        Schema::table('aktivitas_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('aktivitas_logs', 'aksi')) {
                $table->string('aksi')->after('mahasiswa_id');
            }
            if (! Schema::hasColumn('aktivitas_logs', 'deskripsi')) {
                $table->string('deskripsi')->after('aksi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aktivitas_logs', function (Blueprint $table) {
            if (Schema::hasColumn('aktivitas_logs', 'deskripsi')) {
                $table->dropColumn('deskripsi');
            }
            if (Schema::hasColumn('aktivitas_logs', 'aksi')) {
                $table->dropColumn('aksi');
            }
        });
    }
};
