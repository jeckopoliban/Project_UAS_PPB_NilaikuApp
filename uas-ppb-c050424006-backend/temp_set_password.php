<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'c050424006@sinilai.test';
$user = User::where('email', $email)->first();
if (! $user) {
    echo "User not found: {$email}\n";
    exit(1);
}
$user->password = Hash::make('Mhs26');
$user->save();
echo "Password for {$email} set to Mhs26\n";
