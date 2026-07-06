<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\MataKuliah;
use App\Models\GradingTemplate;
use App\Models\GradingTemplateItem;
use App\Models\KomponenNilai;
use Illuminate\Support\Facades\DB;

$email = 'c050424006@sinilai.test';
$user = User::where('email', $email)->first();
if (! $user) {
    echo "User not found: {$email}\n";
    exit(1);
}
$id = $user->id;

DB::beginTransaction();
try {
    // Delete komponen_nilai for mata kuliah owned by user
    $mataKuliahIds = MataKuliah::where('mahasiswa_id', $id)->pluck('id')->toArray();
    if (! empty($mataKuliahIds)) {
        KomponenNilai::whereIn('mata_kuliah_id', $mataKuliahIds)->delete();
        MataKuliah::whereIn('id', $mataKuliahIds)->delete();
        echo "Deleted " . count($mataKuliahIds) . " MataKuliah and their KomponenNilai\n";
    } else {
        echo "No MataKuliah found for user\n";
    }

    // Delete grading template items and templates owned by user
    $templateIds = GradingTemplate::where('mahasiswa_id', $id)->pluck('id')->toArray();
    if (! empty($templateIds)) {
        GradingTemplateItem::whereIn('grading_template_id', $templateIds)->delete();
        GradingTemplate::whereIn('id', $templateIds)->delete();
        echo "Deleted " . count($templateIds) . " GradingTemplate and their items\n";
    } else {
        echo "No GradingTemplate found for user\n";
    }

    DB::commit();
    echo "Cleanup completed successfully.\n";
} catch (Exception $e) {
    DB::rollBack();
    echo "Cleanup failed: " . $e->getMessage() . "\n";
    exit(1);
}
