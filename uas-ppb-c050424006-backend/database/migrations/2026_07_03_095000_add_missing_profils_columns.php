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
            if (! Schema::hasColumn('profils', 'program_studi')) {
                $table->string('program_studi')->nullable();
            }

            if (! Schema::hasColumn('profils', 'target_ipk')) {
                $table->decimal('target_ipk', 3, 2)->nullable();
            }

            if (! Schema::hasColumn('profils', 'foto_profil')) {
                $table->string('foto_profil')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profils', function (Blueprint $table) {
            if (Schema::hasColumn('profils', 'foto_profil')) {
                $table->dropColumn('foto_profil');
            }

            if (Schema::hasColumn('profils', 'target_ipk')) {
                $table->dropColumn('target_ipk');
            }

            if (Schema::hasColumn('profils', 'program_studi')) {
                $table->dropColumn('program_studi');
            }
        });
    }
};
