<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\GradingTemplate;
use App\Models\GradingTemplateItem;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GradingTemplateController extends Controller
{
    public function index(): View
    {
        /** @var User|null $currentUser */
        $currentUser = request()->user();
        $userId = (int) ($currentUser?->id ?? 0);

        $templates = GradingTemplate::where(function ($q) use ($userId) {
            $q->whereNull('mahasiswa_id')->orWhere('mahasiswa_id', $userId);
        })->with('items')->get();

        $activeTemplateId = null;
        $tahunAktif = \App\Models\TahunAkademik::where('mahasiswa_id', $userId)
            ->where('status_aktif', true)
            ->first();

        if ($tahunAktif) {
            $activeTemplateId = $tahunAktif->grading_template_id;
        }

        if (! $activeTemplateId) {
            $activeTemplateId = GradingTemplate::where('is_default', true)->value('id');
        }

        return view('portal.grading.index', [
            'templates' => $templates,
            'activeTemplateId' => $activeTemplateId,
        ]);
    }

    public function setActive(Request $request, $templateId): \Illuminate\Http\RedirectResponse
    {
        $template = GradingTemplate::findOrFail($templateId);
        /** @var User|null $currentUser */
        $currentUser = $request->user();
        $userId = (int) ($currentUser?->id ?? 0);

        if ($template->mahasiswa_id && $template->mahasiswa_id !== $userId) {
            return redirect()->back()->with('error', 'Template grading tidak dapat dipilih.');
        }

        \App\Models\TahunAkademik::where('mahasiswa_id', $userId)
            ->update(['grading_template_id' => $template->id]);

        return redirect()->route('portal.grading')->with('success', 'Template grading aktif diterapkan secara global untuk semua semester Anda.');
    }

    public function edit($id): View
    {
        $template = GradingTemplate::findOrFail($id);
        /** @var User|null $currentUser */
        $currentUser = request()->user();
        $userId = (int) ($currentUser?->id ?? 0);

        if ($template->mahasiswa_id && $template->mahasiswa_id !== $userId) {
            abort(403);
        }

        return view('portal.grading.edit', [
            'template' => $template,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $template = GradingTemplate::findOrFail($id);
        /** @var User|null $currentUser */
        $currentUser = $request->user();
        $userId = (int) ($currentUser?->id ?? 0);

        if ($template->mahasiswa_id && $template->mahasiswa_id !== $userId) {
            return redirect()->back()->with('error', 'Tidak dapat mengubah template ini');
        }

        $data = $request->validate([
            'nama_template' => 'required|string|max:255',
            'items' => 'array',
            'items.*.id' => 'nullable|integer',
            'items.*.batas_bawah' => 'required|numeric',
            'items.*.batas_atas' => 'required|numeric',
            'items.*.huruf_mutu' => 'required|string|max:5',
            'items.*.indeks' => 'required|numeric',
        ]);

        foreach ($data['items'] ?? [] as $index => $it) {
            if ((float) $it['batas_bawah'] >= (float) $it['batas_atas']) {
                return redirect()->back()->with('error', "Rentang nilai pada baris #".($index + 1)." tidak valid. Batas bawah harus lebih kecil dari batas atas.")->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $template->update(['nama_template' => $data['nama_template']]);

            $existingIds = collect($data['items'] ?? [])->pluck('id')->filter()->toArray();
            // delete removed items
            GradingTemplateItem::where('grading_template_id', $template->id)
                ->whereNotIn('id', $existingIds)
                ->delete();

            foreach ($data['items'] ?? [] as $it) {
                if (! empty($it['id'])) {
                    GradingTemplateItem::where('id', $it['id'])->update([
                        'batas_bawah' => $it['batas_bawah'],
                        'batas_atas' => $it['batas_atas'],
                        'huruf_mutu' => $it['huruf_mutu'],
                        'indeks' => $it['indeks'],
                    ]);
                } else {
                    GradingTemplateItem::create(array_merge($it, ['grading_template_id' => $template->id]));
                }
            }

            app(AuditLogService::class)->record(
                $request,
                'update_grading_template_mahasiswa',
                'Memperbarui template grading pribadi: ' . $template->nama_template,
            );

            DB::commit();
            return redirect()->route('portal.grading')->with('success', 'Template berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function create(): View
    {
        return view('portal.grading.create');
    }

    public function destroy($id): RedirectResponse
    {
        $template = GradingTemplate::findOrFail($id);
        /** @var User|null $currentUser */
        $currentUser = request()->user();
        $userId = (int) ($currentUser?->id ?? 0);

        if ($template->mahasiswa_id !== $userId) {
            return redirect()->back()->with('error', 'Anda tidak diperbolehkan menghapus template ini');
        }

        // delete items then template
        GradingTemplateItem::where('grading_template_id', $template->id)->delete();
        $template->delete();

        app(AuditLogService::class)->record(
            request(),
            'delete_grading_template_mahasiswa',
            'Menghapus template grading pribadi: ' . $template->nama_template,
        );

        return redirect()->back()->with('success', 'Template berhasil dihapus');
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $request->user();
        $userId = (int) ($currentUser?->id ?? 0);

        $validator = Validator::make($request->all(), [
            'nama_template' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $items = $request->input('items', []);

        $itemsValidator = Validator::make(['items' => $items], [
            'items' => ['required', 'array', 'min:1'],
            'items.*.batas_bawah' => ['required', 'numeric', 'gte:0', 'lte:100'],
            'items.*.batas_atas' => ['required', 'numeric', 'gte:0', 'lte:100'],
            'items.*.huruf_mutu' => ['required', 'string'],
            'items.*.indeks' => ['required', 'numeric'],
        ]);

        if ($itemsValidator->fails()) {
            return redirect()->back()->withErrors($itemsValidator)->withInput();
        }

        foreach ($items as $index => $it) {
            if ((float) $it['batas_bawah'] >= (float) $it['batas_atas']) {
                return redirect()->back()->with('error', "Rentang nilai pada item #".($index + 1)." tidak valid. Batas bawah harus lebih kecil dari batas atas.")->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $template = GradingTemplate::create([
                'nama_template' => $request->input('nama_template'),
                'mahasiswa_id' => $userId,
                'is_default' => false,
            ]);

            foreach ($items as $it) {
                GradingTemplateItem::create([
                    'grading_template_id' => $template->id,
                    'batas_bawah' => $it['batas_bawah'],
                    'batas_atas' => $it['batas_atas'],
                    'huruf_mutu' => $it['huruf_mutu'],
                    'indeks' => $it['indeks'],
                ]);
            }

            app(AuditLogService::class)->record(
                $request,
                'create_grading_template_mahasiswa',
                'Membuat template grading pribadi: ' . $request->input('nama_template'),
            );

            DB::commit();
            return redirect()->route('portal.grading')->with('success', 'Template grading berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function storeItems(Request $request, $templateId): RedirectResponse
    {
        $template = GradingTemplate::findOrFail($templateId);
        /** @var User|null $currentUser */
        $currentUser = $request->user();
        $userId = (int) ($currentUser?->id ?? 0);

        if ($template->mahasiswa_id !== $userId) {
            return redirect()->back()->with('error', 'Anda tidak diperbolehkan mengubah template ini');
        }

        $items = $request->input('items', []);

        $validator = Validator::make(['items' => $items], [
            'items' => ['required', 'array', 'min:1'],
            'items.*.batas_bawah' => ['required', 'numeric', 'gte:0', 'lte:100'],
            'items.*.batas_atas' => ['required', 'numeric', 'gte:0', 'lte:100'],
            'items.*.huruf_mutu' => ['required', 'string'],
            'items.*.indeks' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // basic non-overlap validation: ensure batas_bawah < batas_atas for each
        foreach ($items as $i => $it) {
            if ((float) $it['batas_bawah'] >= (float) $it['batas_atas']) {
                return redirect()->back()->with('error', "Rentang pada baris {$i} tidak valid (batas bawah harus < batas atas)");
            }
        }

        foreach ($items as $it) {
            GradingTemplateItem::create([
                'grading_template_id' => $template->id,
                'batas_bawah' => $it['batas_bawah'],
                'batas_atas' => $it['batas_atas'],
                'huruf_mutu' => $it['huruf_mutu'],
                'indeks' => $it['indeks'],
            ]);
        }

        app(AuditLogService::class)->record(
            $request,
            'add_grading_template_item_mahasiswa',
            'Menambah item grading ke template: ' . $template->nama_template,
        );

        return redirect()->back()->with('success', 'Item grading berhasil ditambahkan ke template');
    }
}
