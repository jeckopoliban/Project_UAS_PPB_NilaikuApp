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
        Schema::create('komponen_nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('users');
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs');
            $table->string('nama_komponen');
            $table->decimal('nilai_angka', 5, 2)->nullable();
            $table->decimal('bobot_persen', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komponen_nilais');
    }
};
