<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class MataKuliahController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User|null $currentUser */
        $currentUser = $request->user();
        $userId = (int) ($currentUser?->id ?? 0);

        $query = MataKuliah::where('mahasiswa_id', $userId);

        if ($request->filled('tahun_akademik_id')) {
            $query->where('tahun_akademik_id', $request->query('tahun_akademik_id'));
        }

        if ($request->filled('search')) {
            $query->where('nama_mk', 'like', '%' . $request->query('search') . '%');
        }

        $items = $query->orderBy('created_at', 'desc')->get();
        $tahunAkademiks = TahunAkademik::where('mahasiswa_id', $userId)->get();

        return view('portal.mata-kuliah.index', [
            'items' => $items,
            'tahunAkademiks' => $tahunAkademiks,
            'selectedTahun' => $request->query('tahun_akademik_id'),
            'search' => $request->query('search'),
        ]);
    }

    public function create(): View
    {
        /** @var User|null $currentUser */
        $currentUser = request()->user();
        $userId = (int) ($currentUser?->id ?? 0);

        $tahunAkademiks = TahunAkademik::where('mahasiswa_id', $userId)->get();

        return view('portal.mata-kuliah.create', ['tahunAkademiks' => $tahunAkademiks]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $request->user();
        $userId = (int) ($currentUser?->id ?? 0);

        $validator = Validator::make($request->all(), [
            'tahun_akademik_id' => ['required', 'integer', 'exists:tahun_akademiks,id'],
            'nama_mk' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1', 'max:6'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tahunAkademik = TahunAkademik::where('mahasiswa_id', $userId)
            ->where('id', $request->input('tahun_akademik_id'))
            ->first();

        if (! $tahunAkademik) {
            return redirect()->back()->with('error', 'Tahun akademik tidak ditemukan atau bukan milik Anda');
        }

        $item = MataKuliah::create([
            'mahasiswa_id' => $userId,
            'tahun_akademik_id' => $request->input('tahun_akademik_id'),
            'nama_mk' => $request->input('nama_mk'),
            'sks' => $request->input('sks'),
        ]);

        AktivitasLog::create([
            'mahasiswa_id' => $userId,
            'aksi' => 'Menambahkan Mata Kuliah',
            'deskripsi' => $item->nama_mk,
        ]);

        app(AuditLogService::class)->record(
            $request,
            'create_mata_kuliah',
            'Menambahkan mata kuliah: ' . $item->nama_mk,
        );

        return redirect()->route('portal.mata-kuliah.index')->with('success', 'Mata kuliah berhasil dibuat');
    }

    public function edit($id): View
    {
        /** @var User|null $currentUser */
        $currentUser = request()->user();
        $userId = (int) ($currentUser?->id ?? 0);

        $item = MataKuliah::where('mahasiswa_id', $userId)->findOrFail($id);
        $tahunAkademiks = TahunAkademik::where('mahasiswa_id', $userId)->get();

        return view('portal.mata-kuliah.edit', ['item' => $item, 'tahunAkademiks' => $tahunAkademiks]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = $request->user();
        $userId = (int) ($currentUser?->id ?? 0);

        $item = MataKuliah::where('mahasiswa_id', $userId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tahun_akademik_id' => ['required', 'integer', 'exists:tahun_akademiks,id'],
            'nama_mk' => ['required', 'string', 'max:255'],
            'sks' => ['required', 'integer', 'min:1', 'max:6'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tahunAkademik = TahunAkademik::where('mahasiswa_id', $userId)
            ->where('id', $request->input('tahun_akademik_id'))
            ->first();

        if (! $tahunAkademik) {
            return redirect()->back()->with('error', 'Tahun akademik tidak ditemukan atau bukan milik Anda');
        }

        $item->fill($validator->validated());
        $item->save();

        AktivitasLog::create([
            'mahasiswa_id' => $userId,
            'aksi' => 'Mengubah Mata Kuliah',
            'deskripsi' => $item->nama_mk,
        ]);

        app(AuditLogService::class)->record(
            $request,
            'update_mata_kuliah',
            'Memperbarui mata kuliah: ' . $item->nama_mk,
        );

        return redirect()->route('portal.mata-kuliah.index')->with('success', 'Mata kuliah berhasil diperbarui');
    }

    public function destroy($id): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = request()->user();
        $userId = (int) ($currentUser?->id ?? 0);

        $item = MataKuliah::where('mahasiswa_id', $userId)->findOrFail($id);
        $name = $item->nama_mk;
        $item->komponenNilais()->delete();
        $item->delete();

        AktivitasLog::create([
            'mahasiswa_id' => $userId,
            'aksi' => 'Menghapus Mata Kuliah',
            'deskripsi' => $name,
        ]);

        app(AuditLogService::class)->record(
            request(),
            'delete_mata_kuliah',
            'Menghapus mata kuliah: ' . $name,
        );

        return redirect()->route('portal.mata-kuliah.index')->with('success', 'Mata kuliah berhasil dihapus');
    }

    public function show($id): RedirectResponse
    {
        // Redirect show to edit to avoid missing view errors; edit provides the
        // same information and allows managing komponen nilai for existing MK.
        return redirect()->route('portal.mata-kuliah.edit', $id);
    }
}
