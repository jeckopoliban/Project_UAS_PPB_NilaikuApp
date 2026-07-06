<?php

namespace Database\Seeders;

use App\Models\GradingTemplate;
use Illuminate\Database\Seeder;

class GradingTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $template = GradingTemplate::firstOrCreate(
            ['nama_template' => 'Default Poliban'],
            ['is_default' => true]
        );

        $template->items()->delete();

        $template->items()->createMany([
            ['batas_bawah' => 80.00, 'batas_atas' => 100.00, 'huruf_mutu' => 'A', 'indeks' => 4.00],
            ['batas_bawah' => 75.00, 'batas_atas' => 79.99, 'huruf_mutu' => 'B+', 'indeks' => 3.50],
            ['batas_bawah' => 70.00, 'batas_atas' => 74.99, 'huruf_mutu' => 'B', 'indeks' => 3.00],
            ['batas_bawah' => 65.00, 'batas_atas' => 69.99, 'huruf_mutu' => 'C+', 'indeks' => 2.50],
            ['batas_bawah' => 60.00, 'batas_atas' => 64.99, 'huruf_mutu' => 'C', 'indeks' => 2.00],
            ['batas_bawah' => 50.00, 'batas_atas' => 59.99, 'huruf_mutu' => 'D', 'indeks' => 1.00],
            ['batas_bawah' => 0.00, 'batas_atas' => 49.99, 'huruf_mutu' => 'E', 'indeks' => 0.00],
        ]);
    }
}
