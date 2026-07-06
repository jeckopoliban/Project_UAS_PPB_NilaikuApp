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
use App\Models\TahunAkademik;
use App\Services\GradingService;
use Illuminate\Support\Str;

$email = 'c050424006@sinilai.test';
$user = User::where('email', $email)->first();
if (! $user) {
    $user = User::create([
        'name' => 'Sim User',
        'email' => $email,
        'password' => password_hash('password', PASSWORD_BCRYPT),
        'role' => 'user',
        'status_aktif' => 1,
    ]);
    echo "CREATED_USER={$user->id} {$user->email}\n";
} else {
    echo "USER_ID={$user->id}\n";
}

// Pick a Mata Kuliah to operate on
// Ensure a Tahun Akademik exists
$ta = TahunAkademik::first();
if (! $ta) {
    $ta = TahunAkademik::create([
        'mahasiswa_id' => $user->id,
        'nama' => '2026/1',
        'status_aktif' => true,
    ]);
    echo "CREATED_TA={$ta->id}\n";
}

$mk = MataKuliah::first();
if (! $mk) {
    $mk = MataKuliah::create([
        'mahasiswa_id' => $user->id,
        'tahun_akademik_id' => $ta->id,
        'nama_mk' => 'Sample MK',
        'sks' => 3,
    ]);
    echo "CREATED_MK={$mk->id} - {$mk->nama_mk}\n";
} else {
    echo "SELECTED_MK={$mk->id} - {$mk->nama_mk}\n";
}

// 1) Simulate Template Cepat: create a student-owned grading template and apply its items as komponen_nilai for selected mata kuliah
// Template Cepat simulation: create komponen nilai directly for the MK
$components = [
    ['nama_komponen' => 'Tugas', 'bobot_persen' => 30],
    ['nama_komponen' => 'UTS', 'bobot_persen' => 30],
    ['nama_komponen' => 'UAS', 'bobot_persen' => 40],
];
foreach ($components as $c) {
    KomponenNilai::create([
        'mahasiswa_id' => $user->id,
        'mata_kuliah_id' => $mk->id,
        'nama_komponen' => $c['nama_komponen'],
        'bobot_persen' => $c['bobot_persen'],
    ]);
}

$totalBobot = KomponenNilai::where('mata_kuliah_id', $mk->id)->sum('bobot_persen');
echo "Applied components to MK {$mk->id}. Total bobot now: {$totalBobot}%\n";

$list = KomponenNilai::where('mata_kuliah_id', $mk->id)->get();
foreach ($list as $k) {
    echo " - Komponen: {$k->nama_komponen} ({$k->bobot_persen}%)\n";
}

// 2) Wizard flow A: use an existing system template (mahasiswa_id NULL) if available
$systemTemplate = GradingTemplate::whereNull('mahasiswa_id')->first();
if ($systemTemplate) {
    $mkA = MataKuliah::create([
        'mahasiswa_id' => $user->id,
        'tahun_akademik_id' => $ta->id,
        'nama_mk' => 'Wizard MK - System Template ' . Str::random(4),
        'sks' => 3,
        'grading_template_id' => $systemTemplate->id,
    ]);
    // create example komponen nilai for mkA
    KomponenNilai::create(['mahasiswa_id' => $user->id, 'mata_kuliah_id' => $mkA->id, 'nama_komponen' => 'Tugas', 'bobot_persen' => 40]);
    KomponenNilai::create(['mahasiswa_id' => $user->id, 'mata_kuliah_id' => $mkA->id, 'nama_komponen' => 'UAS', 'bobot_persen' => 60]);
    echo "Created MK {$mkA->id} with system template {$systemTemplate->id}\n";
} else {
    echo "No system template found to simulate wizard A\n";
}

// 3) Wizard flow B: create custom template and mata_kuliah in one transaction
$customTemplate = GradingTemplate::create([
    'nama_template' => 'Custom Wizard Template by '.$user->id,
    'mahasiswa_id' => $user->id,
    'is_default' => false,
]);
// GradingTemplateItem is a grade scale; we'll just add placeholder items
GradingTemplateItem::create(['grading_template_id' => $customTemplate->id, 'batas_bawah' => 0, 'batas_atas' => 59, 'huruf_mutu' => 'E', 'indeks' => 0]);
GradingTemplateItem::create(['grading_template_id' => $customTemplate->id, 'batas_bawah' => 60, 'batas_atas' => 69, 'huruf_mutu' => 'C', 'indeks' => 2]);
GradingTemplateItem::create(['grading_template_id' => $customTemplate->id, 'batas_bawah' => 70, 'batas_atas' => 79, 'huruf_mutu' => 'B', 'indeks' => 3]);
GradingTemplateItem::create(['grading_template_id' => $customTemplate->id, 'batas_bawah' => 80, 'batas_atas' => 100, 'huruf_mutu' => 'A', 'indeks' => 4]);

$mkB = MataKuliah::create([
    'mahasiswa_id' => $user->id,
    'tahun_akademik_id' => $ta->id,
    'nama_mk' => 'Wizard MK - Custom Template ' . Str::random(4),
    'sks' => 2,
    'grading_template_id' => $customTemplate->id,
]);
// create komponen nilai matching typical distribution
KomponenNilai::create(['mahasiswa_id' => $user->id, 'mata_kuliah_id' => $mkB->id, 'nama_komponen' => 'Kuis', 'bobot_persen' => 20]);
KomponenNilai::create(['mahasiswa_id' => $user->id, 'mata_kuliah_id' => $mkB->id, 'nama_komponen' => 'Proyek', 'bobot_persen' => 30]);
KomponenNilai::create(['mahasiswa_id' => $user->id, 'mata_kuliah_id' => $mkB->id, 'nama_komponen' => 'UAS', 'bobot_persen' => 50]);

echo "Created MK {$mkB->id} with custom template {$customTemplate->id}\n";

// 4) Validate GradingService conversion uses per-MK grading template
$gradingService = app(GradingService::class);
$nilaiAkhir = 85; // sample
// Use mkB's grading_template_id
// Convert nilai using mkB's grading template
$grade = $gradingService->convert((float)$nilaiAkhir, $mkB->grading_template_id ?? null);
echo "Converted nilai {$nilaiAkhir} using template {$mkB->grading_template_id}: " . json_encode($grade) . "\n";

echo "SIMULATION_DONE\n";
