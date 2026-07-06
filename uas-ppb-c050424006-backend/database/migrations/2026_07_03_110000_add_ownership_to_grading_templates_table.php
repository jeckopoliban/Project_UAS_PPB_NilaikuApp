<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grading_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('grading_templates', 'mahasiswa_id')) {
                $table->foreignId('mahasiswa_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('grading_templates', function (Blueprint $table) {
            if (Schema::hasColumn('grading_templates', 'mahasiswa_id')) {
                $table->dropForeign(['mahasiswa_id']);
                $table->dropColumn('mahasiswa_id');
            }
        });
    }
};
