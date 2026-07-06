<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class TahunAkademikController extends Controller
{
    public function index(): View
    {
        $items = TahunAkademik::where('mahasiswa_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('portal.tahun-akademik.index', ['items' => $items]);
    }

    public function create(): View
    {
        return view('portal.tahun-akademik.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'status_aktif' => ['sometimes', 'nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $item = TahunAkademik::create([
            'mahasiswa_id' => auth()->id(),
            'nama' => $request->input('nama'),
            'status_aktif' => $request->input('status_aktif') ?? false,
        ]);

        AktivitasLog::create([
            'mahasiswa_id' => auth()->id(),
            'aksi' => 'Menambahkan Tahun Akademik',
            'deskripsi' => $item->nama,
        ]);

        return redirect()->route('portal.tahun-akademik.index')->with('success', 'Tahun akademik berhasil dibuat');
    }

    public function edit($id): View
    {
        $item = TahunAkademik::where('mahasiswa_id', auth()->id())->findOrFail($id);

        return view('portal.tahun-akademik.edit', ['item' => $item]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $item = TahunAkademik::where('mahasiswa_id', auth()->id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'status_aktif' => ['sometimes', 'nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $item->fill($validator->validated());
        $item->save();

        AktivitasLog::create([
            'mahasiswa_id' => auth()->id(),
            'aksi' => 'Mengubah Tahun Akademik',
            'deskripsi' => $item->nama,
        ]);

        return redirect()->route('portal.tahun-akademik.index')->with('success', 'Tahun akademik berhasil diperbarui');
    }

    public function destroy($id): RedirectResponse
    {
        $item = TahunAkademik::where('mahasiswa_id', auth()->id())->findOrFail($id);
        $name = $item->nama;
        $item->delete();

        AktivitasLog::create([
            'mahasiswa_id' => auth()->id(),
            'aksi' => 'Menghapus Tahun Akademik',
            'deskripsi' => $name,
        ]);

        return redirect()->route('portal.tahun-akademik.index')->with('success', 'Tahun akademik berhasil dihapus');
    }
}
