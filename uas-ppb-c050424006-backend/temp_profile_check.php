<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::where('email', 'c050424006@sinilai.test')->first();
if (! $user) {
    echo "NO_USER\n";
    exit(0);
}
$profile = $user->profil;
echo "USER_ID=" . $user->id . "\n";
echo "PROFIL=" . ($profile ? 'FOUND' : 'NONE') . "\n";
if ($profile) {
    echo "NIM=" . ($profile->nim_nis ?? 'NULL') . "\n";
    echo "PRODI=" . ($profile->program_studi ?? 'NULL') . "\n";
    echo "IPK=" . ($profile->target_ipk ?? 'NULL') . "\n";
    echo "SKS=" . ($profile->target_sks ?? 'NULL') . "\n";
}
