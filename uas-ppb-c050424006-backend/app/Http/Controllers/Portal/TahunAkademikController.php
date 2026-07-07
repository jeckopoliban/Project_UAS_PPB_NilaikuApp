<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use App\Models\TahunAkademik;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class TahunAkademikController extends Controller
{
    public function index(): View
    {
        $userId = (int) (request()->user()?->id ?? 0);

        $items = TahunAkademik::where('mahasiswa_id', $userId)
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
            'mahasiswa_id' => (int) (request()->user()?->id ?? 0),
            'nama' => $request->input('nama'),
            'status_aktif' => $request->input('status_aktif') ?? false,
        ]);

        AktivitasLog::create([
            'mahasiswa_id' => (int) (request()->user()?->id ?? 0),
            'aksi' => 'Menambahkan Tahun Akademik',
            'deskripsi' => $item->nama,
        ]);

        app(AuditLogService::class)->record(
            $request,
            'create_tahun_akademik',
            'Menambahkan tahun akademik: ' . $item->nama,
        );

        return redirect()->route('portal.tahun-akademik.index')->with('success', 'Tahun akademik berhasil dibuat');
    }

    public function edit($id): View
    {
        $userId = (int) (request()->user()?->id ?? 0);

        $item = TahunAkademik::where('mahasiswa_id', $userId)->findOrFail($id);

        return view('portal.tahun-akademik.edit', ['item' => $item]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $userId = (int) (request()->user()?->id ?? 0);

        $item = TahunAkademik::where('mahasiswa_id', $userId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'status_aktif' => ['sometimes', 'nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $item->nama = $validated['nama'];
        // Checkbox unchecked does not send value; treat as false explicitly.
        $item->status_aktif = $request->boolean('status_aktif');
        $item->save();

        AktivitasLog::create([
            'mahasiswa_id' => (int) (request()->user()?->id ?? 0),
            'aksi' => 'Mengubah Tahun Akademik',
            'deskripsi' => $item->nama,
        ]);

        app(AuditLogService::class)->record(
            $request,
            'update_tahun_akademik',
            'Memperbarui tahun akademik: ' . $item->nama,
        );

        return redirect()->route('portal.tahun-akademik.index')->with('success', 'Tahun akademik berhasil diperbarui');
    }

    public function destroy($id): RedirectResponse
    {
        $userId = (int) (request()->user()?->id ?? 0);

        $item = TahunAkademik::where('mahasiswa_id', $userId)->findOrFail($id);
        $name = $item->nama;
        $item->delete();

        AktivitasLog::create([
            'mahasiswa_id' => (int) (request()->user()?->id ?? 0),
            'aksi' => 'Menghapus Tahun Akademik',
            'deskripsi' => $name,
        ]);

        app(AuditLogService::class)->record(
            request(),
            'delete_tahun_akademik',
            'Menghapus tahun akademik: ' . $name,
        );

        return redirect()->route('portal.tahun-akademik.index')->with('success', 'Tahun akademik berhasil dihapus');
    }
}
