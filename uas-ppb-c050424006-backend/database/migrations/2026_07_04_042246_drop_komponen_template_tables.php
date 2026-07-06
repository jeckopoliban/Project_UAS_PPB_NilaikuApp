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
        Schema::dropIfExists('komponen_template_items');
        Schema::dropIfExists('komponen_templates');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('komponen_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama_template');
            $table->foreignId('mahasiswa_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('komponen_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komponen_template_id')->constrained('komponen_templates');
            $table->integer('bobot_persen');
            $table->string('nama_komponen');
            $table->integer('nilai_angka')->nullable();
            $table->timestamps();
        });
    }
};
