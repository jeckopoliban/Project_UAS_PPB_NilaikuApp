<?php

namespace App\Services;

use App\Models\KomponenNilai;
use App\Models\GradingTemplate;
use App\Models\MataKuliah;

class GradingService
{
    protected function resolveGradingTemplateIdForMataKuliah(MataKuliah $mataKuliah): ?int
    {
        $gradingTemplateId = $mataKuliah->tahunAkademik?->grading_template_id;

        if ($gradingTemplateId) {
            return $gradingTemplateId;
        }

        $fallback = GradingTemplate::where('is_default', true)->first();

        return $fallback?->id;
    }

    public function convert(float $nilaiAkhir, ?int $gradingTemplateId = null): array
    {
        if ($gradingTemplateId) {
            $template = GradingTemplate::with('items')->find($gradingTemplateId);
        } else {
            $template = GradingTemplate::with('items')
                ->where('is_default', true)
                ->first();
        }

        if (! $template || $template->items->isEmpty()) {
            return [
                'huruf_mutu' => null,
                'indeks' => 0,
            ];
        }

        foreach ($template->items as $item) {
            $lower = min((float) $item->batas_bawah, (float) $item->batas_atas);
            $upper = max((float) $item->batas_bawah, (float) $item->batas_atas);

            if ($lower <= $nilaiAkhir && $nilaiAkhir <= $upper) {
                return [
                    'huruf_mutu' => $item->huruf_mutu,
                    'indeks' => (float) $item->indeks,
                ];
            }
        }

        return [
            'huruf_mutu' => null,
            'indeks' => 0,
        ];
    }

    public function hitungNilaiAkhir(int $mataKuliahId, int $mahasiswaId): ?float
    {
        $mataKuliah = MataKuliah::where('mahasiswa_id', $mahasiswaId)
            ->where('id', $mataKuliahId)
            ->first();

        if (! $mataKuliah) {
            throw new \RuntimeException('Mata kuliah tidak ditemukan atau bukan milik Anda.');
        }

        $komponen = KomponenNilai::where('mahasiswa_id', $mahasiswaId)
            ->where('mata_kuliah_id', $mataKuliahId)
            ->get();

        if ($komponen->isEmpty()) {
            return null;
        }

        $totalBobot = (float) $komponen->sum('bobot_persen');

        if (abs($totalBobot - 100) > 0.000001) {
            return null;
        }

        $nilaiAkhir = 0.0;

        foreach ($komponen as $item) {
            $nilai = (float) ($item->nilai_angka ?? 0);
            $nilaiAkhir += $nilai * ((float) $item->bobot_persen / 100);
        }

        return round($nilaiAkhir, 2);
    }
}
