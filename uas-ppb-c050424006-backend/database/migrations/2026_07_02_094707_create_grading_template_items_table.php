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
        Schema::create('grading_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_template_id')->constrained('grading_templates');
            $table->decimal('batas_bawah', 8, 2);
            $table->decimal('batas_atas', 8, 2);
            $table->string('huruf_mutu');
            $table->decimal('indeks', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grading_template_items');
    }
};
