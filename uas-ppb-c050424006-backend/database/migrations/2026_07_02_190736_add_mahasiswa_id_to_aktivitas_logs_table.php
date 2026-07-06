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
            if (! Schema::hasColumn('aktivitas_logs', 'mahasiswa_id')) {
                $table->foreignId('mahasiswa_id')->after('id')->constrained('users')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aktivitas_logs', function (Blueprint $table) {
            if (Schema::hasColumn('aktivitas_logs', 'mahasiswa_id')) {
                $table->dropForeign(['mahasiswa_id']);
                $table->dropColumn('mahasiswa_id');
            }
        });
    }
};
