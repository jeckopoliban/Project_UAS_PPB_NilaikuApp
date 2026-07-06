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
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            if (Schema::hasColumn('mata_kuliahs', 'grading_template_id')) {
                $table->dropForeign(['grading_template_id']);
                $table->dropColumn('grading_template_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            if (! Schema::hasColumn('mata_kuliahs', 'grading_template_id')) {
                $table->foreignId('grading_template_id')
                    ->nullable()
                    ->after('sks')
                    ->constrained('grading_templates')
                    ->nullOnDelete();
            }
        });
    }
};
