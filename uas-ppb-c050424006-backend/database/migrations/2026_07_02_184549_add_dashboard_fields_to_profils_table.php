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
        Schema::table('profils', function (Blueprint $table) {
            $table->string('program_studi')->nullable()->after('jenis_institusi');
            $table->decimal('target_ipk', 3, 2)->nullable()->after('program_studi');
            $table->string('foto_profil')->nullable()->after('target_ipk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profils', function (Blueprint $table) {
            $table->dropColumn(['program_studi', 'target_ipk', 'foto_profil']);
        });
    }
};
