<?php

namespace Database\Seeders;

use App\Models\GradingTemplate;
use App\Models\InstitusiReferensi;
use App\Models\KomponenNilai;
use App\Models\MataKuliah;
use App\Models\Profil;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $gradingTemplate = GradingTemplate::updateOrCreate(
                ['nama_template' => 'Default Poliban'],
                ['is_default' => true]
            );

            GradingTemplate::where('id', '!=', $gradingTemplate->id)
                ->update(['is_default' => false]);

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
                $gradingTemplate->items()->updateOrCreate(
                    [
                        'grading_template_id' => $gradingTemplate->id,
                        'huruf_mutu' => $item['huruf_mutu'],
                    ],
                    $item
                );
            }

            $institutions = [
                'Politeknik Negeri Banjarmasin',
                'Universitas Lambung Mangkurat',
                'Universitas Islam Kalimantan',
            ];

            foreach ($institutions as $institution) {
                InstitusiReferensi::updateOrCreate(
                    ['nama_institusi' => $institution],
                    ['jenis' => 'perguruan_tinggi', 'status_verifikasi' => true]
                );
            }

            $superAdmin = User::updateOrCreate(
                ['email' => 'superadmin@sinilai.test'],
                [
                    'name' => 'Super Administrator',
                    'password' => Hash::make('Admin26'),
                    'role' => 'super_admin',
                    'status_aktif' => true,
                ]
            );

            $demoUser = User::updateOrCreate(
                ['email' => 'c050424006@sinilai.test'],
                [
                    'name' => 'Mahasiswa Demo',
                    'password' => Hash::make('Mhs26'),
                    'role' => 'user',
                    'status_aktif' => true,
                ]
            );

            $demoUser->profil()->updateOrCreate(
                ['user_id' => $demoUser->id],
                [
                    'nim_nis' => 'C050424006',
                    'no_hp' => '081234567890',
                    'nama_institusi' => 'Politeknik Negeri Banjarmasin',
                    'jenis_institusi' => 'perguruan_tinggi',
                ]
            );

            $semesterGanjil = TahunAkademik::updateOrCreate(
                ['mahasiswa_id' => $demoUser->id, 'nama' => '2025/2026 Ganjil'],
                ['status_aktif' => false]
            );

            $semesterGenap = TahunAkademik::updateOrCreate(
                ['mahasiswa_id' => $demoUser->id, 'nama' => '2026/2027 Genap'],
                ['status_aktif' => true]
            );

            $matauKuliahsGanjil = [
                ['nama_mk' => 'Pemrograman Perangkat Bergerak', 'sks' => 3],
                ['nama_mk' => 'Basis Data Lanjut', 'sks' => 3],
                ['nama_mk' => 'Rekayasa Perangkat Lunak', 'sks' => 2],
            ];

            $matauKuliahsGenap = [
                ['nama_mk' => 'Kecerdasan Buatan', 'sks' => 3],
                ['nama_mk' => 'Kerja Praktik', 'sks' => 4],
            ];

            $mataKuliahMap = [];

            foreach ($matauKuliahsGanjil as $mk) {
                $mataKuliahMap[$mk['nama_mk']] = MataKuliah::updateOrCreate(
                    [
                        'mahasiswa_id' => $demoUser->id,
                        'tahun_akademik_id' => $semesterGanjil->id,
                        'nama_mk' => $mk['nama_mk'],
                    ],
                    ['sks' => $mk['sks']]
                );
            }

            foreach ($matauKuliahsGenap as $mk) {
                $mataKuliahMap[$mk['nama_mk']] = MataKuliah::updateOrCreate(
                    [
                        'mahasiswa_id' => $demoUser->id,
                        'tahun_akademik_id' => $semesterGenap->id,
                        'nama_mk' => $mk['nama_mk'],
                    ],
                    ['sks' => $mk['sks']]
                );
            }

            $komponenNilaiData = [
                'Pemrograman Perangkat Bergerak' => [
                    ['nama_komponen' => 'Tugas Akhir', 'bobot_persen' => 40, 'nilai_angka' => 85],
                    ['nama_komponen' => 'UTS', 'bobot_persen' => 30, 'nilai_angka' => 78],
                    ['nama_komponen' => 'UAS', 'bobot_persen' => 30, 'nilai_angka' => 88],
                ],
                'Basis Data Lanjut' => [
                    ['nama_komponen' => 'Tugas Praktikum', 'bobot_persen' => 35, 'nilai_angka' => 82],
                    ['nama_komponen' => 'UTS', 'bobot_persen' => 30, 'nilai_angka' => 74],
                    ['nama_komponen' => 'UAS', 'bobot_persen' => 35, 'nilai_angka' => 80],
                ],
                'Rekayasa Perangkat Lunak' => [
                    ['nama_komponen' => 'Project', 'bobot_persen' => 50, 'nilai_angka' => 90],
                    ['nama_komponen' => 'Presentasi', 'bobot_persen' => 20, 'nilai_angka' => 83],
                    ['nama_komponen' => 'Ujian', 'bobot_persen' => 30, 'nilai_angka' => 86],
                ],
                'Kecerdasan Buatan' => [
                    ['nama_komponen' => 'Tugas', 'bobot_persen' => 40, 'nilai_angka' => 88],
                ],
                'Kerja Praktik' => [
                    ['nama_komponen' => 'Laporan', 'bobot_persen' => 40, 'nilai_angka' => 92],
                ],
            ];

            foreach ($komponenNilaiData as $mkName => $components) {
                $mataKuliah = $mataKuliahMap[$mkName];

                foreach ($components as $component) {
                    KomponenNilai::updateOrCreate(
                        [
                            'mahasiswa_id' => $demoUser->id,
                            'mata_kuliah_id' => $mataKuliah->id,
                            'nama_komponen' => $component['nama_komponen'],
                        ],
                        [
                            'bobot_persen' => $component['bobot_persen'],
                            'nilai_angka' => $component['nilai_angka'],
                        ]
                    );
                }
            }

            $comparisonUser = User::updateOrCreate(
                ['email' => 'pembanding@sinilai.test'],
                [
                    'name' => 'Mahasiswa Pembanding',
                    'password' => Hash::make('Mhs26'),
                    'role' => 'user',
                    'status_aktif' => true,
                ]
            );

            $comparisonUser->profil()->updateOrCreate(
                ['user_id' => $comparisonUser->id],
                [
                    'nim_nis' => 'C050424099',
                    'no_hp' => '081234567891',
                    'nama_institusi' => 'Universitas Lambung Mangkurat',
                    'jenis_institusi' => 'perguruan_tinggi',
                ]
            );

            $semesterPembanding = TahunAkademik::updateOrCreate(
                ['mahasiswa_id' => $comparisonUser->id, 'nama' => '2026/2027 Ganjil'],
                ['status_aktif' => true]
            );

            $comparisonMataKuliahs = [
                ['nama_mk' => 'Manajemen Basis Data', 'sks' => 3],
                ['nama_mk' => 'Statistika Terapan', 'sks' => 3],
            ];

            foreach ($comparisonMataKuliahs as $mk) {
                $mataKuliah = MataKuliah::updateOrCreate(
                    [
                        'mahasiswa_id' => $comparisonUser->id,
                        'tahun_akademik_id' => $semesterPembanding->id,
                        'nama_mk' => $mk['nama_mk'],
                    ],
                    ['sks' => $mk['sks']]
                );

                KomponenNilai::updateOrCreate(
                    [
                        'mahasiswa_id' => $comparisonUser->id,
                        'mata_kuliah_id' => $mataKuliah->id,
                        'nama_komponen' => 'Tugas',
                    ],
                    [
                        'bobot_persen' => 40,
                        'nilai_angka' => 84,
                    ]
                );

                KomponenNilai::updateOrCreate(
                    [
                        'mahasiswa_id' => $comparisonUser->id,
                        'mata_kuliah_id' => $mataKuliah->id,
                        'nama_komponen' => 'UTS',
                    ],
                    [
                        'bobot_persen' => 30,
                        'nilai_angka' => 79,
                    ]
                );

                KomponenNilai::updateOrCreate(
                    [
                        'mahasiswa_id' => $comparisonUser->id,
                        'mata_kuliah_id' => $mataKuliah->id,
                        'nama_komponen' => 'UAS',
                    ],
                    [
                        'bobot_persen' => 30,
                        'nilai_angka' => 82,
                    ]
                );
            }
        });
    }
}
