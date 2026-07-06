<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
$migration = DB::table('migrations')->where('migration', '2026_07_02_184549_add_dashboard_fields_to_profils_table')->first();
if (! $migration) {
    echo "MIGRATION_NOT_FOUND\n";
    exit(0);
}
echo "Migration: {$migration->migration}\n";
echo "Batch: {$migration->batch}\n";
