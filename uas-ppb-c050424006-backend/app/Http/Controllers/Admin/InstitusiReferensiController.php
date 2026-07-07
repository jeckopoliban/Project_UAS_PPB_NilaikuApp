<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstitusiReferensi;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class InstitusiReferensiController extends Controller
{
    public function index(): View
    {
        $items = InstitusiReferensi::paginate(20);

        return view('admin.institusi-referensi.index', ['items' => $items]);
    }

    public function create(): View
    {
        return view('admin.institusi-referensi.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_institusi' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:perguruan_tinggi,sekolah,lainnya'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['status_verifikasi'] = true;

        InstitusiReferensi::create($data);

        app(AuditLogService::class)->record(
            $request,
            'create_institusi_referensi',
            'Menambahkan institusi referensi: ' . $data['nama_institusi'],
        );

        return redirect()->route('admin.institusi-referensi.index')->with('success', 'Institusi berhasil ditambahkan');
    }

    public function edit($id): View
    {
        $item = InstitusiReferensi::findOrFail($id);

        return view('admin.institusi-referensi.edit', ['item' => $item]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $item = InstitusiReferensi::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_institusi' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:sekolah,perguruan_tinggi,lainnya'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $item->fill($validator->validated());
        $item->save();

        app(AuditLogService::class)->record(
            $request,
            'update_institusi_referensi',
            'Memperbarui institusi referensi: ' . $item->nama_institusi,
        );

        return redirect()->route('admin.institusi-referensi.index')->with('success', 'Institusi berhasil diperbarui');
    }

    public function destroy($id): RedirectResponse
    {
        $item = InstitusiReferensi::findOrFail($id);
        $namaInstitusi = $item->nama_institusi;
        $item->delete();

        app(AuditLogService::class)->record(
            request(),
            'delete_institusi_referensi',
            'Menghapus institusi referensi: ' . $namaInstitusi,
        );

        return redirect()->route('admin.institusi-referensi.index')->with('success', 'Institusi berhasil dihapus');
    }
}
