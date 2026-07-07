<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradingTemplate;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class GradingTemplateController extends Controller
{
    public function index(): View
    {
        $items = GradingTemplate::with('items')
            ->whereNull('mahasiswa_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.grading-template.index', ['items' => $items]);
    }

    public function create(): View
    {
        return view('admin.grading-template.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_template' => ['required', 'string', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.batas_bawah' => ['required', 'numeric'],
            'items.*.batas_atas' => ['required', 'numeric'],
            'items.*.huruf_mutu' => ['required', 'string', 'max:5'],
            'items.*.indeks' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['is_default'] = $request->boolean('is_default');

        foreach ($data['items'] as $index => $item) {
            if ((float) $item['batas_bawah'] >= (float) $item['batas_atas']) {
                return redirect()->back()->with('error', "Rentang nilai pada baris #".($index + 1)." tidak valid. Batas bawah harus lebih kecil dari batas atas.")->withInput();
            }
        }

        DB::transaction(function () use ($data) {
            if ($data['is_default']) {
                GradingTemplate::where('is_default', true)->update(['is_default' => false]);
            }

            $template = GradingTemplate::create([
                'nama_template' => $data['nama_template'],
                'is_default' => $data['is_default'],
                'mahasiswa_id' => null,
            ]);

            foreach ($data['items'] as $item) {
                $template->items()->create($item);
            }

            app(AuditLogService::class)->record(
                request(),
                'create_grading_template',
                'Membuat template grading: ' . $data['nama_template'],
            );
        });

        return redirect()->route('admin.grading-template.index')->with('success', 'Template grading berhasil dibuat');
    }

    public function show($id): View
    {
        $template = GradingTemplate::with('items')->whereNull('mahasiswa_id')->findOrFail($id);

        return view('admin.grading-template.show', ['template' => $template]);
    }

    public function edit($id): View
    {
        $template = GradingTemplate::with('items')->whereNull('mahasiswa_id')->findOrFail($id);

        return view('admin.grading-template.edit', ['template' => $template]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $template = GradingTemplate::whereNull('mahasiswa_id')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_template' => ['required', 'string', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.batas_bawah' => ['required', 'numeric'],
            'items.*.batas_atas' => ['required', 'numeric'],
            'items.*.huruf_mutu' => ['required', 'string', 'max:5'],
            'items.*.indeks' => ['required', 'numeric'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['is_default'] = $request->boolean('is_default');

        foreach ($data['items'] as $index => $item) {
            if ((float) $item['batas_bawah'] >= (float) $item['batas_atas']) {
                return redirect()->back()->with('error', "Rentang nilai pada baris #".($index + 1)." tidak valid. Batas bawah harus lebih kecil dari batas atas.")->withInput();
            }
        }

        DB::transaction(function () use ($template, $data) {
            if ($data['is_default']) {
                GradingTemplate::where('is_default', true)->where('id', '!=', $template->id)->update(['is_default' => false]);
            }

            $template->update([
                'nama_template' => $data['nama_template'],
                'is_default' => $data['is_default'],
            ]);

            $existingIds = collect($data['items'])->pluck('id')->filter()->toArray();

            $template->items()->whereNotIn('id', $existingIds)->delete();

            foreach ($data['items'] as $item) {
                if (! empty($item['id'])) {
                    $template->items()->where('id', $item['id'])->update([
                        'batas_bawah' => $item['batas_bawah'],
                        'batas_atas' => $item['batas_atas'],
                        'huruf_mutu' => $item['huruf_mutu'],
                        'indeks' => $item['indeks'],
                    ]);
                } else {
                    $template->items()->create([
                        'batas_bawah' => $item['batas_bawah'],
                        'batas_atas' => $item['batas_atas'],
                        'huruf_mutu' => $item['huruf_mutu'],
                        'indeks' => $item['indeks'],
                    ]);
                }
            }

            app(AuditLogService::class)->record(
                request(),
                'update_grading_template',
                'Memperbarui template grading: ' . $template->nama_template,
            );
        });

        return redirect()->route('admin.grading-template.index')->with('success', 'Template grading berhasil diperbarui');
    }

    public function destroy($id): RedirectResponse
    {
        $template = GradingTemplate::whereNull('mahasiswa_id')->findOrFail($id);
        $templateName = $template->nama_template;
        $template->delete();

        app(AuditLogService::class)->record(
            request(),
            'delete_grading_template',
            'Menghapus template grading: ' . $templateName,
        );

        return redirect()->route('admin.grading-template.index')->with('success', 'Template grading berhasil dihapus');
    }
}
