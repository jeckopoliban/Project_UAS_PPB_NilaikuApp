<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select('SHOW COLUMNS FROM aktivitas_logs');
foreach ($columns as $column) {
    echo $column->Field . ' ' . $column->Type . ' ' . $column->Null . ' ' . ($column->Default ?? 'NULL') . "\n";
}
