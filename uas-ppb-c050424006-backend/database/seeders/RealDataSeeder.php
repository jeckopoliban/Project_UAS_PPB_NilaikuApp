<?php

namespace Database\Seeders;

use App\Models\GradingTemplate;
use App\Models\GradingTemplateItem;
use App\Models\InstitusiReferensi;
use App\Models\KomponenNilai;
use App\Models\MataKuliah;
use App\Models\Profil;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RealDataSeeder extends Seeder
{
    private function clamp(float $value, float $min = 0, float $max = 100): float
    {
        return max($min, min($max, $value));
    }

    private function decomposeIntoComponents(float $nilaiAkhir): array
    {
        $tugas = round($this->clamp($nilaiAkhir + 4, 0, 100), 2);
        $uts = round($this->clamp($nilaiAkhir - 3, 0, 100), 2);
        $uas = round((($nilaiAkhir - 0.3 * $tugas - 0.3 * $uts) / 0.4), 2);

        if ($uas < 0 || $uas > 100) {
            $tugas = $uts = $uas = round($nilaiAkhir, 2);
        }

        $total = round(0.3 * $tugas + 0.3 * $uts + 0.4 * $uas, 2);

        if (abs($total - $nilaiAkhir) > 0.01) {
            $difference = $nilaiAkhir - $total;
            $uas = round($uas + ($difference / 0.4), 2);
            if ($uas < 0 || $uas > 100) {
                $tugas = $uts = $uas = round($nilaiAkhir, 2);
            }
        }

        $total = round(0.3 * $tugas + 0.3 * $uts + 0.4 * $uas, 2);

        if (abs($total - $nilaiAkhir) > 0.01) {
            $uas = round((($nilaiAkhir - 0.3 * $tugas - 0.3 * $uts) / 0.4), 2);
        }

        return [
            'Tugas' => $tugas,
            'UTS' => $uts,
            'UAS' => $uas,
        ];
    }

    private function createStudent(array $profile, array $semesters, int $decomposeLastNSemesters = 2): void
    {
        $user = User::create([
            'name' => $profile['name'],
            'email' => $profile['email'],
            'password' => Hash::make($profile['password']),
            'role' => 'user',
            'status_aktif' => true,
            'created_at' => $profile['created_at'],
            'updated_at' => $profile['created_at'],
        ]);

        $user->profil()->create([
            'user_id' => $user->id,
            'nim_nis' => $profile['nim'],
            'nama_institusi' => $profile['kampus'],
            'program_studi' => $profile['prodi'],
            'target_ipk' => $profile['target_ipk'],
            'target_sks' => $profile['target_sks'],
        ]);

        $semesterNames = array_keys($semesters);
        $decomposeNames = array_slice($semesterNames, max(0, count($semesterNames) - $decomposeLastNSemesters));

        foreach ($semesters as $semesterName => $courses) {
            $tahunAkademik = TahunAkademik::create([
                'mahasiswa_id' => $user->id,
                'nama' => $semesterName,
                'status_aktif' => false,
            ]);

            foreach ($courses as $courseData) {
                [$namaMk, $sks, $nilai] = $courseData;

                $mataKuliah = MataKuliah::create([
                    'mahasiswa_id' => $user->id,
                    'tahun_akademik_id' => $tahunAkademik->id,
                    'nama_mk' => $namaMk,
                    'sks' => $sks,
                    'nama_komponen_penilaian' => in_array($semesterName, $decomposeNames, true) && is_numeric($nilai) ? 'Tugas + UTS + UAS' : 'Nilai Akhir',
                ]);

                if ($nilai === null || $nilai === 'lulus') {
                    continue;
                }

                if (in_array($semesterName, $decomposeNames, true)) {
                    $components = $this->decomposeIntoComponents((float) $nilai);
                    foreach ($components as $komponen => $nilaiAngka) {
                        KomponenNilai::create([
                            'mahasiswa_id' => $user->id,
                            'mata_kuliah_id' => $mataKuliah->id,
                            'nama_komponen' => $komponen,
                            'bobot_persen' => $komponen === 'UAS' ? 40 : 30,
                            'nilai_angka' => $nilaiAngka,
                        ]);
                    }
                } else {
                    KomponenNilai::create([
                        'mahasiswa_id' => $user->id,
                        'mata_kuliah_id' => $mataKuliah->id,
                        'nama_komponen' => 'Nilai Akhir',
                        'bobot_persen' => 100,
                        'nilai_angka' => round((float) $nilai, 2),
                    ]);
                }
            }
        }
    }

    public function run(): void
    {
        DB::transaction(function () {
            $gradingTemplate = GradingTemplate::create([
                'nama_template' => 'Default Nilaiku',
                'is_default' => true,
                'mahasiswa_id' => null,
            ]);

            $gradingTemplateItems = [
                ['batas_bawah' => 80.00, 'batas_atas' => 100.00, 'huruf_mutu' => 'A', 'indeks' => 4.00],
                ['batas_bawah' => 75.00, 'batas_atas' => 79.99, 'huruf_mutu' => 'B+', 'indeks' => 3.50],
                ['batas_bawah' => 70.00, 'batas_atas' => 74.99, 'huruf_mutu' => 'B', 'indeks' => 3.00],
                ['batas_bawah' => 65.00, 'batas_atas' => 69.99, 'huruf_mutu' => 'C+', 'indeks' => 2.50],
                ['batas_bawah' => 60.00, 'batas_atas' => 64.99, 'huruf_mutu' => 'C', 'indeks' => 2.00],
                ['batas_bawah' => 50.00, 'batas_atas' => 59.99, 'huruf_mutu' => 'D', 'indeks' => 1.00],
                ['batas_bawah' => 0.00, 'batas_atas' => 49.99, 'huruf_mutu' => 'E', 'indeks' => 0.00],
            ];

            foreach ($gradingTemplateItems as $item) {
                GradingTemplateItem::create(array_merge($item, [
                    'grading_template_id' => $gradingTemplate->id,
                ]));
            }

            InstitusiReferensi::updateOrCreate(
                ['nama_institusi' => 'Universitas Lambung Mangkurat'],
                ['jenis' => 'perguruan_tinggi', 'status_verifikasi' => true]
            );

            User::create([
                'name' => 'Super Administrator',
                'email' => 'super@admin.com',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
                'status_aktif' => true,
            ]);

            $this->createStudent([
                'name' => 'M. Zackly Assidhqi',
                'email' => 'zackly@mail.com',
                'password' => 'zackly123',
                'nim' => '183100701',
                'kampus' => 'Uniska MAB',
                'prodi' => 'Manajemen',
                'target_ipk' => 3.70,
                'target_sks' => 144,
                'created_at' => '2023-11-15 00:00:00',
            ], [
                '2023/2024 Ganjil' => [
                    ['Pendidikan Agama Islam', 2, 80.01], ['Pendidikan Pancasila', 2, 78.00],
                    ['Tauhid', 2, 87.27], ['Bahasa Inggris', 2, 80.00],
                    ['Pengantar Akuntansi 1', 3, 75.56], ['Pengantar Bisnis', 3, 76.71],
                    ['Teori Ekonomi Mikro', 3, 81.02], ['Matematika Ekonomi dan Bisnis', 3, 72.89],
                ],
                '2023/2024 Genap' => [
                    ['Bahasa Indonesia', 2, 84.36], ['Bahasa Arab', 2, 89.15],
                    ['Sejarah Islam', 1, 91.28], ['Pengantar Akuntansi 2', 3, 77.84],
                    ['Pengantar Manajemen', 3, 82.67], ['Pengantar Ilmu Ekonomi', 3, 79.91],
                    ['Pengantar Ekonomi Makro', 2, 75.43], ['Sosiologi dan Politik', 2, 86.54],
                ],
                '2024/2025 Ganjil' => [
                    ['Pendidikan Al-Qur\'an', 2, 93.20], ['Statistik', 3, 73.65],
                    ['Hukum Bisnis', 3, 81.74], ['M. Perbankan & Lemb. Keu. Lainnya', 3, 78.53],
                    ['Manajemen Sumber Daya Manusia 1', 3, 87.91], ['Manajemen Operasional 1', 3, 84.25],
                    ['Manajemen Pemasaran 1', 3, 80.68],
                ],
                '2024/2025 Genap' => [
                    ['Pendidikan Kewarganegaraan', 2, 88.42], ['Pengantar Sistem Informasi', 2, 90.13],
                    ['Akuntansi Biaya', 3, 76.94], ['Perekonomian Indonesia', 2, 79.36],
                    ['Sistem Lembaga Keuangan Syariah', 3, 85.81], ['Manajemen Sumber Daya Manusia 2', 3, 86.73],
                    ['Manajemen Operasional 2', 3, 82.17], ['Manajemen Pemasaran 2', 3, 83.56],
                ],
                '2025/2026 Ganjil' => [
                    ['Fiqih', 2, 94.58], ['Metode Penelitian dan Bisnis', 3, 88.27],
                    ['Ekonomi Manajerial', 3, 69.84], ['Ekonomi Pembangunan', 2, 74.91],
                    ['Ekonomi Koperasi', 2, 81.46], ['Ekonomi Internasional', 2, 77.32],
                    ['Manajemen Keuangan 1', 3, 83.74], ['Manajemen Biaya', 3, 85.63],
                ],
                '2025/2026 Genap' => [
                    ['Akhlak', 2, 96.18], ['Perpajakan', 2, 76.51], ['Kewirausahaan', 2, null],
                    ['Perilaku Keorganisasian', 3, 84.09], ['Penganggaran Perusahaan', 3, null],
                    ['Operation Research', 3, 72.83], ['Manajemen Koperasi', 2, 78.64],
                    ['Manajemen Keuangan 2', 3, null], ['Manajemen Strategi', 3, null],
                ],
            ], 2);

            $this->createStudent([
                'name' => 'Adinda Yasmine',
                'email' => 'adinda@mail.com',
                'password' => 'adinda123',
                'nim' => '243081232010',
                'kampus' => 'Universitas Lambung Mangkurat',
                'prodi' => 'Pendidikan Pancasila & Kewarganegaraan',
                'target_ipk' => 3.85,
                'target_sks' => 151,
                'created_at' => '2024-10-15 00:00:00',
            ], [
                '2024/2025 Ganjil' => [
                    ['Pendidikan Agama', 3, 88.34], ['Pancasila', 2, 84.72],
                    ['Bahasa Indonesia', 3, 91.18], ['Ilmu Alamiah Dasar', 3, 79.65],
                    ['Pengantar Pendidikan', 2, 86.40], ['Pengantar Ilmu Politik', 3, 77.93],
                    ['Pengantar Ilmu Hukum', 2, 82.57], ['Pengantar Sosiologi', 2, 75.81],
                    ['Pengantar Antropologi', 2, 80.46],
                ],
                '2024/2025 Genap' => [
                    ['Pendidikan Kewarganegaraan', 3, 89.27], ['Bahasa Inggris', 2, 83.74],
                    ['Ilmu Sosial Budaya Dasar', 3, 87.19], ['Perkembangan Peserta Didik', 2, 81.35],
                    ['Pengantar Hukum Indonesia', 2, 76.84], ['Dasar dan Konsep Pendidikan Kewarganegaraan', 3, 85.91],
                    ['Dasar-Dasar Pendidikan Moral', 3, 90.28], ['Hukum Perdata', 2, 74.63],
                    ['Pendidikan Multikultural', 2, 86.17], ['Ilmu Kewarganegaraan', 3, 79.48],
                ],
                '2025/2026 Ganjil' => [
                    ['Belajar dan Pembelajaran', 2, 92.13], ['Hukum Dagang', 2, 77.56],
                    ['Hukum Tata Negara', 2, 81.74], ['Negara Hukum dan Demokrasi', 2, 88.62],
                    ['Teknologi dan Informasi Komunikasi', 2, 85.09], ['Pendidikan Ilmu Pengetahuan Sosial', 2, 83.28],
                    ['Hukum Adat', 2, 78.95], ['Kajian Kurikulum Pendidikan Kewarganegaraan', 2, 91.46],
                ],
                '2025/2026 Genap' => [
                    ['Profesi Pendidikan', 3, 94.21], ['Filsafat Pendidikan', 2, 87.56],
                    ['Hukum Pajak', 3, 80.83], ['Hukum Tata Usaha Negara', 2, 78.34],
                    ['Hukum Acara Mahkamah Konstitusi', 2, 82.65], ['Hukum Internasional', 2, 85.17],
                    ['Filsafat Ilmu', 2, 89.44], ['Kriminologi', 2, 76.91],
                    ['Strategi Belajar Mengajar Pendidikan Kewarganegaraan', 4, 90.76],
                ],
            ], 2);

            $this->createStudent([
                'name' => 'Raudatul Jannah',
                'email' => 'raudatul@mail.com',
                'password' => 'raudatul123',
                'nim' => '183100706',
                'kampus' => 'Uniska MAB',
                'prodi' => 'Manajemen',
                'target_ipk' => 3.90,
                'target_sks' => 144,
                'created_at' => '2024-12-15 00:00:00',
            ], [
                '2024/2025 Ganjil' => [
                    ['Pendidikan Agama Islam', 2, 80.01], ['Pendidikan Pancasila', 2, 78.00],
                    ['Tauhid', 2, 87.27], ['Bahasa Inggris', 2, 80.00],
                    ['Pengantar Akuntansi 1', 3, 85.56], ['Pengantar Bisnis', 3, 76.71],
                    ['Teori Ekonomi Mikro', 3, 81.02], ['Matematika Ekonomi dan Bisnis', 3, 92.89],
                ],
                '2024/2025 Genap' => [
                    ['Bahasa Indonesia', 2, 84.36], ['Bahasa Arab', 2, 89.15],
                    ['Sejarah Islam', 1, 91.28], ['Pengantar Akuntansi 2', 3, 77.84],
                    ['Pengantar Manajemen', 3, 82.67], ['Pengantar Ilmu Ekonomi', 3, 79.91],
                    ['Pengantar Ekonomi Makro', 2, 85.43], ['Sosiologi dan Politik', 2, 86.54],
                ],
                '2025/2026 Ganjil' => [
                    ['Pendidikan Al-Qur\'an', 2, 93.20], ['Statistik', 3, 73.65],
                    ['Hukum Bisnis', 3, 81.74], ['M. Perbankan & Lemb. Keu. Lainnya', 3, 78.53],
                    ['Manajemen Sumber Daya Manusia 1', 3, 87.91], ['Manajemen Operasional 1', 3, 84.25],
                    ['Manajemen Pemasaran 1', 3, 80.68],
                ],
                '2025/2026 Genap' => [
                    ['Pendidikan Kewarganegaraan', 2, 88.42], ['Pengantar Sistem Informasi', 2, 90.13],
                    ['Akuntansi Biaya', 3, 96.94], ['Perekonomian Indonesia', 2, 79.36],
                    ['Sistem Lembaga Keuangan Syariah', 3, 85.81], ['Manajemen Sumber Daya Manusia 2', 3, 86.73],
                    ['Manajemen Operasional 2', 3, 82.17], ['Manajemen Pemasaran 2', 3, 93.56],
                ],
            ], 2);

            $this->createStudent([
                'name' => 'Firda Aulia',
                'email' => 'firda@mail.com',
                'password' => 'firda123',
                'nim' => null,
                'kampus' => 'Universitas Muhammadiyah Banjarmasin',
                'prodi' => 'Psikologi',
                'target_ipk' => 3.95,
                'target_sks' => 144,
                'created_at' => '2025-02-15 00:00:00',
            ], [
                '2024/2025 Ganjil' => [
                    ['Psikologi Anak dan Remaja', 3, 86.72], ['Pendidikan Kewarganegaraan', 2, 90.15],
                    ['Kemuhammadiyahan', 2, 88.34], ['Psikologi Dasar', 2, 81.49],
                    ['Psikologi Kesehatan', 3, 84.76], ['Bahasa Inggris', 2, 79.88],
                    ['Kepribadian Kontemporer', 3, 87.53], ['Biopsikologi', 3, 82.64],
                ],
                '2024/2025 Genap' => [
                    ['Asesmen Dewasa dan Lansia', 3, 89.27], ['Psikologi Industri dan Organisasi', 3, 83.91],
                    ['Kesehatan Mental', 4, 91.44], ['Observasi dan Wawancara', 4, 86.25],
                    ['Psikologi Abnormal dan Psikopatologi', 4, 79.83], ['Dasar-Dasar Psikologi ABK', 2, 88.56],
                ],
                '2025/2026 Ganjil' => [
                    ['Konstruksi Alat Ukur Psikologi', 4, 85.31], ['Pengembangan Diri', 3, 92.08],
                    ['Intervensi Individu Non-Klinis', 4, 81.77], ['Asesmen Komunitas', 4, 84.52],
                    ['Psikologi Konseling', 4, 87.93], ['Psikologi Kearifan Lokal', 2, 90.64],
                ],
                '2025/2026 Genap' => [
                    ['Psikologi Content Creator', 2, 94.12], ['Analisis Jabatan', 2, 83.48],
                    ['Identifikasi Hambatan Perkembangan', 2, 89.76],
                ],
            ], 2);

            $this->createStudent([
                'name' => 'Mohammad Hassan Baadila',
                'email' => 'hassan@mail.com',
                'password' => 'hassan123',
                'nim' => '423000986',
                'kampus' => 'UIN Antasari Banjarmasin',
                'prodi' => 'Ekonomi Syariah',
                'target_ipk' => 3.83,
                'target_sks' => 146,
                'created_at' => '2023-11-15 00:00:00',
            ], [
                '2023/2024 Ganjil' => [
                    ['Pancasila', 2, 89.24], ['Tauhid', 2, 91.53],
                    ['Fikih', 2, 86.41], ['Studi Quran dan Hadits', 2, 88.76],
                    ['Pengantar Bisnis', 2, 80.39], ['Pengantar Akuntansi', 2, 84.57],
                    ['Pengantar Ekonomi Islam', 2, 82.94], ['Pengantar Ekonomi Mikro', 2, 71.68],
                    ['Pengantar Filsafat', 2, 90.15],
                ],
                '2023/2024 Genap' => [
                    ['Kewarganegaraan', 2, 87.32], ['Akhlak Tasawuf', 2, 92.45],
                    ['Etika Bisnis Syariah', 2, 85.78], ['Bahasa Indonesia', 2, 88.26],
                    ['Islam dan Budaya Banjar', 2, 90.64], ['Matematika Ekonomi dan Bisnis 1', 2, 78.83],
                    ['Pengantar Integrasi Ilmu', 2, 86.17], ['Pengantar Manajemen', 2, 81.25],
                    ['Pengantar Ekonomi Makro', 2, 79.91],
                ],
                '2024/2025 Ganjil' => [
                    ['Fikih Muamalat', 2, 89.48], ['Kebangsentralan dan Otoritas Jasa Keuangan', 2, 84.62],
                    ['Ushul Fikih Ekonomi dan Keuangan Syariah', 2, 77.95], ['Teori Ekonomi Mikro', 2, 82.73],
                    ['Teori Ekonomi Makro', 2, 80.54], ['Matematika Ekonomi dan Bisnis 2', 2, 78.42],
                    ['Sejarah Pemikiran Ekonomi Islam', 2, 81.36], ['Aspek Hukum Dalam Bisnis', 2, 86.85],
                    ['Tafsir Ayat Ekonomi', 2, 80.11],
                ],
                '2024/2025 Genap' => [
                    ['Fikih Muamalat Kontemporer', 2, 88.74], ['Statistika Ekonomi dan Bisnis 1', 2, 80.93],
                    ['Ekonomi Mikro Islam', 2, 74.58], ['Ekonomi Makro Islam', 2, 82.19],
                    ['Akuntansi Syariah', 2, 91.24], ['Ekonometrika 1', 2, 77.86],
                    ['Kaidah-Kaidah Fikih Ekonomi dan Keuangan Syariah', 2, 89.43],
                    ['Ekonomi Moneter Islam', 2, 85.17], ['Syarah Hadits Ekonomi', 2, 90.67],
                ],
                '2025/2026 Ganjil' => [
                    ['Kewirausahaan Syariah', 2, 92.18], ['Statistika Ekonomi dan Bisnis 2', 2, 84.26],
                    ['Perencanaan Regional', 2, 81.75], ['Ekonomi Manajerial', 2, 83.68],
                    ['Pemasaran Digital', 2, 89.52], ['Ekonometrika 2', 2, 78.64],
                    ['Metodologi Penelitian Ekonomi dan Bisnis', 2, 87.39], ['Praktikum Lembaga Keuangan Syariah', 2, 90.85],
                ],
                '2025/2026 Genap' => [
                    ['Ekonomi Pembangunan Islam', 2, 88.41], ['Keterampilan Karir', 2, 93.17],
                    ['Lembaga dan Instrumen Keuangan Syariah', 2, 86.94], ['Keuangan Publik Islam', 2, 84.29],
                    ['Seminar Penelitian', 2, 82.63], ['Praktik Kerja Lapangan', 2, 91.76],
                    ['Perpajakan', 2, 79.58], ['Studi Kelayakan Bisnis', 2, 87.24],
                    ['Sosiologi Ekonomi Islam', 2, 85.91], ['Manajemen Pemasaran Syariah', 2, 83.37],
                    ['Manajemen Sumber Daya Manusia Syariah', 2, 78.05], ['English for Economics and Business', 2, 90.42],
                ],
            ], 2);

            $this->createStudent([
                'name' => 'Suryo Aji Notonegoro',
                'email' => 'suryo@mail.com',
                'password' => 'suryo123',
                'nim' => '243081132010',
                'kampus' => 'Universitas Lambung Mangkurat',
                'prodi' => 'Ilmu Hukum',
                'target_ipk' => 3.88,
                'target_sks' => 146,
                'created_at' => '2024-10-15 00:00:00',
            ], [
                '2024/2025 Ganjil' => [
                    ['Agama', 3, 90.42], ['Pancasila', 2, 87.15],
                    ['Bahasa Inggris 1', 2, 84.73], ['Bahasa Indonesia', 3, 89.56],
                    ['Pengantar Ilmu Hukum', 3, 81.37], ['Pengantar Hukum Indonesia', 3, 85.00],
                    ['Ilmu Negara', 3, 88.64],
                ],
                '2024/2025 Genap' => [
                    ['Bahasa Inggris 2', 2, 86.22], ['Kewarganegaraan', 2, 91.18],
                    ['Kewirausahaan', 2, 80.07], ['Hukum Perdata', 3, 82.32],
                    ['Hukum Pidana', 3, 79.81], ['Hukum Tata Negara', 3, 87.94],
                    ['Hukum Administrasi Negara', 3, 70.46], ['Hukum Adat', 3, 88.35],
                ],
                '2025/2026 Ganjil' => [
                    ['Hukum Internasional Publik', 3, 85.13], ['Hukum Perlindungan Anak', 2, 90.07],
                    ['Hukum dan Masyarakat', 3, 83.84], ['Hukum Dagang', 3, 81.72],
                    ['Hukum Perikatan', 3, 87.26], ['Hukum Kehendaan', 2, 74.49],
                    ['Hukum Pidana Lanjut', 2, 79.35], ['Hukum Pemerintahan Daerah', 2, 88.53],
                    ['Hukum Acara Pidana', 3, 90.68],
                ],
                '2025/2026 Genap' => [
                    ['Hukum Lingkungan', 2, 76.75], ['Hukum Kesehatan', 2, 89.14],
                    ['Hukum Islam', 3, 91.52], ['Hukum Kekayaan Intelektual', 2, 84.26],
                    ['Hukum Agraria', 3, 82.31], ['Hukum Antar Tata Hukum', 3, 80.87],
                    ['Tindak Pidana Tertentu dalam KUHP', 3, 78.95], ['Hukum Acara Administrasi', 3, 87.83],
                    ['Arbitrase dan Alternatif Penyelesaian Sengketa', 2, 93.11],
                ],
            ], 2);

            $this->createStudent([
                'name' => 'Noor Aisyah',
                'email' => 'noor@mail.com',
                'password' => 'noor123',
                'nim' => '183100704',
                'kampus' => 'Uniska MAB',
                'prodi' => 'Manajemen',
                'target_ipk' => 3.95,
                'target_sks' => 144,
                'created_at' => '2025-02-15 00:00:00',
            ], [
                '2024/2025 Ganjil' => [
                    ['Pendidikan Agama Islam', 2, 80.01], ['Pendidikan Pancasila', 2, 88.00],
                    ['Tauhid', 2, 87.27], ['Bahasa Inggris', 2, 80.00],
                    ['Pengantar Akuntansi 1', 3, 85.56], ['Pengantar Bisnis', 3, 79.71],
                    ['Teori Ekonomi Mikro', 3, 81.02], ['Matematika Ekonomi dan Bisnis', 3, 92.89],
                ],
                '2024/2025 Genap' => [
                    ['Bahasa Indonesia', 2, 84.36], ['Bahasa Arab', 2, 89.15],
                    ['Sejarah Islam', 1, 91.28], ['Pengantar Akuntansi 2', 3, 87.84],
                    ['Pengantar Manajemen', 3, 82.67], ['Pengantar Ilmu Ekonomi', 3, 79.91],
                    ['Pengantar Ekonomi Makro', 2, 95.43], ['Sosiologi dan Politik', 2, 86.54],
                ],
                '2025/2026 Ganjil' => [
                    ['Pendidikan Al-Qur\'an', 2, 91.20], ['Statistik', 3, 93.65],
                    ['Hukum Bisnis', 3, 81.74], ['M. Perbankan & Lemb. Keu. Lainnya', 3, 78.53],
                    ['Manajemen Sumber Daya Manusia 1', 3, 87.91], ['Manajemen Operasional 1', 3, 84.25],
                    ['Manajemen Pemasaran 1', 3, 80.68],
                ],
                '2025/2026 Genap' => [
                    ['Pendidikan Kewarganegaraan', 2, 88.42], ['Pengantar Sistem Informasi', 2, 90.13],
                    ['Akuntansi Biaya', 3, 96.94], ['Perekonomian Indonesia', 2, 89.36],
                    ['Sistem Lembaga Keuangan Syariah', 3, 85.81], ['Manajemen Sumber Daya Manusia 2', 3, 86.73],
                    ['Manajemen Operasional 2', 3, 92.17], ['Manajemen Pemasaran 2', 3, 83.56],
                ],
            ], 2);

            $this->createStudent([
                'name' => 'Akbar Fadliansyah',
                'email' => 'akbar@mail.com',
                'password' => 'akbar123',
                'nim' => 'C02048366',
                'kampus' => 'Politeknik Negeri Banjarmasin',
                'prodi' => 'Teknologi Rekayasa Geomatika dan Survei',
                'target_ipk' => 3.70,
                'target_sks' => 146,
                'created_at' => '2024-02-15 00:00:00',
            ], [
                '2023/2024 Ganjil' => [
                    ['Bahasa Inggris 1', 2, 81.36], ['Matematika Terapan', 2, 74.28],
                    ['Pengantar Geomatika dan Survei', 1, 84.52], ['Praktik Pengantar Geomatika dan Survei', 2, 79.64],
                    ['K3', 2, 86.13], ['Statistika', 2, 72.95], ['Survei Terestris', 2, 77.48],
                    ['Praktik Survei Terestris', 3, 83.79], ['Manajemen Basis Data', 1, 78.32], ['Praktik Manajemen Basis Data', 3, 85.47],
                ],
                '2023/2024 Genap' => [
                    ['Bahasa Inggris 2', 2, 80.41], ['Ilmu Hitung Perataan', 3, 73.66],
                    ['Survei Topografi', 1, 76.82], ['Praktik Survei Topografi', 3, 82.15],
                    ['Sistem Transformasi Koordinat', 3, 71.73], ['Basis Data Spasial', 1, 84.09],
                    ['Praktik Basis Data Spasial', 3, 79.88], ['Kartografi Dasar', 2, 77.34], ['Praktik Kartografi Dasar', 2, 86.58],
                ],
                '2024/2025 Ganjil' => [
                    ['Bahasa Indonesia', 2, 85.91], ['Proyeksi Peta', 3, 74.87], ['Kartografi Digital', 1, 80.26],
                    ['Praktik Kartografi Digital', 3, 84.18], ['Survei GNSS', 2, 76.74], ['Praktik Survei GNSS', 2, 82.66],
                    ['Fotogrametri', 2, 75.38], ['Praktik Fotogrametri', 3, 87.21], ['Praktik Pemrograman Dasar', 2, 79.44],
                ],
                '2024/2025 Genap' => [
                    ['Pancasila', 2, 84.77], ['Oseanografi', 2, 73.85], ['Sistem Informasi Geografis', 2, 80.62],
                    ['Praktik Sistem Informasi Geografis', 3, 86.91], ['Penginderaan Jauh', 2, 77.53], ['Praktik Penginderaan Jauh', 3, 83.74],
                    ['Pemodelan Digital', 2, 71.95], ['Praktik Pemodelan Digital', 3, 82.47], ['Praktik Pemrograman Spasial', 2, 79.68],
                ],
                '2025/2026 Ganjil' => [
                    ['Agama', 2, 87.34], ['Kemah Kerja', 3, 81.76], ['Survei Rekayasa', 1, 74.23],
                    ['Praktik Survei Rekayasa', 2, 83.45], ['Survei Hidrografi', 2, 75.84], ['Praktik Survei Hidrografi', 3, 84.92],
                    ['Praktik Pengolahan Data LIDAR', 3, 78.31], ['Praktik Sistem Informasi Geografis Terapan', 3, 86.27],
                ],
                '2025/2026 Genap' => [
                    ['Kewarganegaraan', 2, 88.14], ['Kewirausahaan', 2, 82.39], ['Metodologi Penelitian', 2, 79.52],
                    ['Manajemen Proyek', 2, 76.44], ['Survei Kadastral', 2, 74.89], ['Praktik Survei Kadastral', 3, 85.17],
                    ['Survei Tambang', 1, 73.28], ['Praktik Survei Tambang', 2, 81.63], ['Praktik WebGIS', 2, 84.36],
                ],
            ], 2);

            $this->createStudent([
                'name' => 'Gusti Irani Fauzati',
                'email' => 'irani@mail.com',
                'password' => 'irani123',
                'nim' => '192666828',
                'kampus' => 'Universitas Indonesia',
                'prodi' => 'Ilmu Kedokteran',
                'target_ipk' => 3.85,
                'target_sks' => 144,
                'created_at' => '2026-06-15 00:00:00',
            ], [
                '2025/2026 Ganjil' => [
                    ['Program Dasar Pendidikan Tinggi (PDPT)', 4, 86.74], ['Pengantar Empati dan Bioetik untuk Pengembangan Pribadi dan Profesi Kedokteran (EBP3KH)', 4, 88.21],
                    ['Pertolongan Pertama pada Kegawatan dan Kedaruratan (P2K2)', 3, 81.36], ['Pengantar Riset', 3, 84.17],
                    ['Keterampilan Belajar Sepanjang Hayat', 2, 89.42], ['Komunikasi Medis Dasar', 2, 87.53],
                ],
                '2025/2026 Genap' => [
                    ['Modul Ilmu Kedokteran Terintegrasi I', 6, 79.84], ['Modul Ilmu Kedokteran Terintegrasi II', 6, 82.37],
                    ['Keterampilan Klinik Dasar I', 2, 88.14], ['Tumbuh Kembang', 3, 84.73], ['Kulit dan Jaringan Penunjang', 3, 80.25], ['Muskuloskeletal', 4, 98.56],
                ],
            ], 2);

            $this->createStudent([
                'name' => 'Alya Rohali',
                'email' => 'alya@mail.com',
                'password' => 'alya123',
                'nim' => 'C04052500',
                'kampus' => 'Politeknik Negeri Banjarmasin',
                'prodi' => 'Sistem Informasi Kota Cerdas',
                'target_ipk' => 3.86,
                'target_sks' => 160,
                'created_at' => '2026-06-15 00:00:00',
            ], [
                '2025/2026 Ganjil' => [
                    ['Pancasila', 2, 86.14], ['Matematika', 2, 80.02],
                    ['Etika Profesi', 2, 84.63], ['Bahasa Inggris 1', 2, 81.27],
                    ['Algoritma Pemrograman', 3, 78.95], ['Pengantar Sistem Informasi', 2, 88.31],
                    ['Dasar-Dasar Jaringan', 2, 75.84], ['Elektronika', 3, 79.46],
                ],
            ], 2);

            $this->createStudent([
                'name' => 'Maher Ahmad Wibowo',
                'email' => 'maher@mail.com',
                'password' => 'maher123',
                'nim' => 'C04052300',
                'kampus' => 'Politeknik Negeri Banjarmasin',
                'prodi' => 'Sistem Informasi Kota Cerdas',
                'target_ipk' => 3.80,
                'target_sks' => 160,
                'created_at' => '2023-12-15 00:00:00',
            ], [
                '2023/2024 Ganjil' => [
                    ['Pancasila', 2, 84.76], ['Matematika', 2, 76.58],
                    ['Etika Profesi', 2, 87.42], ['Bahasa Inggris 1', 2, 82.13],
                    ['Algoritma Pemrograman', 3, 79.64], ['Pengantar Sistem Informasi', 2, 86.37],
                    ['Dasar-Dasar Jaringan', 2, 77.95], ['Elektronika', 3, 75.84],
                ],
                '2023/2024 Genap' => [
                    ['Bahasa Inggris 2', 2, 85.28], ['Kewirausahaan', 2, 88.31],
                    ['Agama', 2, 90.14], ['Matematika Diskrit', 2, 74.86],
                    ['Basis Data', 3, 80.52], ['Dasar Internet of Things', 3, 81.97],
                    ['Statistika dan Probabilitas', 3, 78.43], ['Sistem Operasi', 2, 83.69],
                ],
                '2024/2025 Ganjil' => [
                    ['Rekayasa Perangkat Lunak', 2, 82.46], ['Struktur Data', 2, 76.83],
                    ['Pemrograman Berbasis Web', 3, 85.94], ['Pemrograman Berorientasi Objek', 3, 81.76],
                    ['Administrasi Basis Data', 3, 79.35], ['Administrasi Jaringan', 3, 78.68],
                    ['Pemodelan Proses Bisnis', 3, 87.52], ['Dasar Pengembangan Sistem Informasi Kota Cerdas', 3, 84.29],
                ],
                '2024/2025 Genap' => [
                    ['Pemrograman Aplikasi Perangkat Bergerak', 3, 80.84], ['Desain Interaksi dan Antarmuka Pengguna', 2, 88.42],
                    ['Data Mining', 3, 77.51], ['Kartografi dan Informasi Geospasial', 2, 81.37],
                    ['Keamanan Jaringan', 3, 79.92], ['Sistem Enterprise', 3, 84.56],
                    ['Mobile Cloud Computing', 2, 76.48], ['Analisis dan Desain Sistem Informasi Kota Cerdas', 3, 89.14],
                ],
                '2025/2026 Ganjil' => [
                    ['Sistem Pendukung Keputusan', 2, 83.15], ['Data Warehouse', 3, 80.43],
                    ['Pengolahan Citra Digital', 2, 76.92], ['Pengujian Penetrasi Jaringan', 3, 78.65],
                    ['Sistem Informasi Geografis', 3, 84.97], ['Tata Kelola Sistem/Teknologi Informasi', 2, 86.53],
                    ['Keamanan Aset dan Sistem Informasi Kota Cerdas', 3, 81.24], ['Implementasi dan Pengujian Sistem Informasi Kota Cerdas', 3, 88.16],
                    ['Manajemen Proyek dan Keberlanjutan Sistem Informasi Kota Cerdas', 3, 79.73],
                ],
                '2025/2026 Genap' => [
                    ['Metodologi Penelitian Sistem Informasi', 2, 85.62], ['Big Data', 3, 78.47],
                    ['Internet of Things dalam Kota Cerdas', 3, 83.51], ['Pemrograman Penetrasi Jaringan', 3, 76.84],
                    ['Kewarganegaraan', 2, 89.28], ['Bahasa Indonesia', 2, 87.73],
                    ['Perencanaan Strategis Sistem Informasi Kota Cerdas', 3, 81.36], ['Evaluasi dan Audit Sistem Informasi Kota Cerdas', 3, 84.91],
                ],
            ], 2);

            $this->createStudent([
                'name' => 'Rusdiansyah Azhar',
                'email' => 'rusdi@mail.com',
                'password' => 'rusdi123',
                'nim' => 'C02048361',
                'kampus' => 'Politeknik Negeri Banjarmasin',
                'prodi' => 'Teknologi Rekayasa Geomatika dan Survei',
                'target_ipk' => 3.75,
                'target_sks' => 146,
                'created_at' => '2024-06-15 00:00:00',
            ], [
                '2023/2024 Ganjil' => [
                    ['Bahasa Inggris 1', 2, 81.36], ['Matematika Terapan', 2, 74.28],
                    ['Pengantar Geomatika dan Survei', 1, 84.52], ['Praktik Pengantar Geomatika dan Survei', 2, 79.64],
                    ['K3', 2, 86.13], ['Statistika', 2, 72.95], ['Survei Terestris', 2, 77.48],
                    ['Praktik Survei Terestris', 3, 83.79], ['Manajemen Basis Data', 1, 78.32], ['Praktik Manajemen Basis Data', 3, 85.47],
                ],
                '2023/2024 Genap' => [
                    ['Bahasa Inggris 2', 2, 80.41], ['Ilmu Hitung Perataan', 3, 73.66],
                    ['Survei Topografi', 1, 76.82], ['Praktik Survei Topografi', 3, 82.15],
                    ['Sistem Transformasi Koordinat', 3, 78.73], ['Basis Data Spasial', 1, 84.09],
                    ['Praktik Basis Data Spasial', 3, 79.88], ['Kartografi Dasar', 2, 77.34], ['Praktik Kartografi Dasar', 2, 86.58],
                ],
                '2024/2025 Ganjil' => [
                    ['Bahasa Indonesia', 2, 85.91], ['Proyeksi Peta', 3, 74.87], ['Kartografi Digital', 1, 80.26],
                    ['Praktik Kartografi Digital', 3, 84.18], ['Survei GNSS', 2, 76.74], ['Praktik Survei GNSS', 2, 82.66],
                    ['Fotogrametri', 2, 75.38], ['Praktik Fotogrametri', 3, 87.21], ['Praktik Pemrograman Dasar', 2, 79.44],
                ],
                '2024/2025 Genap' => [
                    ['Pancasila', 2, 84.77], ['Oseanografi', 2, 73.85], ['Sistem Informasi Geografis', 2, 80.62],
                    ['Praktik Sistem Informasi Geografis', 3, 86.91], ['Penginderaan Jauh', 2, 77.53], ['Praktik Penginderaan Jauh', 3, 83.74],
                    ['Pemodelan Digital', 2, 71.95], ['Praktik Pemodelan Digital', 3, 82.47], ['Praktik Pemrograman Spasial', 2, 79.68],
                ],
                '2025/2026 Ganjil' => [
                    ['Agama', 2, 87.34], ['Kemah Kerja', 3, 81.76], ['Survei Rekayasa', 1, 79.23],
                    ['Praktik Survei Rekayasa', 2, 83.45], ['Survei Hidrografi', 2, 85.84], ['Praktik Survei Hidrografi', 3, 84.92],
                    ['Praktik Pengolahan Data LIDAR', 3, 78.31], ['Praktik Sistem Informasi Geografis Terapan', 3, 86.27],
                ],
                '2025/2026 Genap' => [
                    ['Kewarganegaraan', 2, 88.14], ['Kewirausahaan', 2, 82.39], ['Metodologi Penelitian', 2, 79.52],
                    ['Manajemen Proyek', 2, 76.44], ['Survei Kadastral', 2, null], ['Praktik Survei Kadastral', 3, 85.17],
                    ['Survei Tambang', 1, 73.28], ['Praktik Survei Tambang', 2, 81.63], ['Praktik WebGIS', 2, null],
                ],
            ], 2);
        });
    }
}
