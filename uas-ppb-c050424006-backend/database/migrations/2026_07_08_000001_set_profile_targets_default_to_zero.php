<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE profils MODIFY target_ipk DECIMAL(3,2) NULL DEFAULT 0');
        DB::statement('ALTER TABLE profils MODIFY target_sks INT NULL DEFAULT 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE profils MODIFY target_ipk DECIMAL(3,2) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE profils MODIFY target_sks INT NULL DEFAULT 144');
    }
};
