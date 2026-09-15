<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtBankSoal;
use App\Models\CbtJadwal;
use App\Models\CbtJenis;
use App\Models\MasterTp;
use App\Models\MasterSmt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CbtJadwalController extends Controller
{
    /**
     * Daftar Jadwal Pelaksanaan Ujian dengan Filter Tahun Ajaran.
     */
    public function index(Request $request): View
    {
        $academicService = app(\App\Services\AcademicYear\AcademicYearService::class);
        $allYears = $academicService->getAllYears();
        $selectedYearId = $request->input('tahun_ajaran_id') ?? $academicService->getSelectedYear()?->id;
        $selectedYear = $selectedYearId ? \App\Models\RefTahunAjaran::find($selectedYearId) : $academicService->getSelectedYear();

        $query = CbtJadwal::with(['bankSoal.mapel', 'jenis'])->orderBy('id_jadwal', 'desc');

        if ($selectedYear) {
            $matchedTp = MasterTp::where('tahun', $selectedYear->tahun)->first();
            if ($matchedTp) {
                $query->where('id_tp', $matchedTp->id_tp);
            }
            if ($selectedYear->semester) {
                $query->where('id_smt', (int) $selectedYear->semester);
            }
        }

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('id_jadwal', $search)
                  ->orWhereHas('bankSoal', function ($bq) use ($search) {
                      $bq->where('bank_nama', 'like', "%{$search}%")
                         ->orWhere('bank_kode', 'like', "%{$search}%")
                         ->orWhereHas('mapel', function ($mq) use ($search) {
                             $mq->where('nama_mapel', 'like', "%{$search}%");
                         });
                  })->orWhereHas('jenis', function ($jq) use ($search) {
                      $jq->where('nama_jenis', 'like', "%{$search}%")
                         ->orWhere('kode_jenis', 'like', "%{$search}%");
                  });
            });
        }

        $jadwals = $query->paginate(10)->withQueryString();
        $bankList = CbtBankSoal::with('mapel')->where('status', 1)->get();
        $jenisList = CbtJenis::all();
        $kelasList = \App\Models\MasterKelas::orderBy('nama_kelas', 'asc')->get();
        $guruList = \App\Models\MasterGuru::orderBy('nama_guru', 'asc')->get();

        return view('admin.cbt.jadwal.index', compact('jadwals', 'bankList', 'jenisList', 'kelasList', 'guruList', 'allYears', 'selectedYear'));
    }

    /**
     * Simpan Jadwal Ujian Baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'id_bank'      => 'required|integer',
            'id_jenis'     => 'required|integer',
            'tgl_mulai'    => 'required|date',
            'tgl_selesai'  => 'required|date|after_or_equal:tgl_mulai',
            'durasi_ujian' => 'required|integer|min:10',
        ]);

        $activeTa = app(\App\Services\AcademicYear\AcademicYearService::class)->getActiveYear();
        $activeTp = MasterTp::where('tahun', $activeTa?->tahun)->first()?->id_tp ?? (MasterTp::activeTp()?->id_tp ?? 1);
        $activeSmt = (int) ($activeTa?->semester ?? (MasterSmt::activeSmt()?->id_smt ?? 1));

        CbtJadwal::create([
            'id_tp'        => $activeTp,
            'id_smt'       => $activeSmt,
            'id_bank'      => $request->input('id_bank'),
            'id_jenis'     => $request->input('id_jenis'),
            'tgl_mulai'    => $request->input('tgl_mulai'),
            'tgl_selesai'  => $request->input('tgl_selesai'),
            'durasi_ujian' => (int) $request->input('durasi_ujian'),
            'acak_soal'    => (int) $request->input('acak_soal', 1),
            'acak_opsi'    => (int) $request->input('acak_opsi', 1),
            'token'        => (int) $request->input('token', 1),
            'hasil_tampil' => (int) $request->input('hasil_tampil', 0),
            'reset_login'  => (int) $request->input('reset_login', 0),
            'pengawas'     => $request->input('pengawas', []),
            'status'       => 1,
        ]);

        return back()->with('success', 'Jadwal pelaksanaan ujian berhasil dibuat dan siap diujikan.');
    }

    /**
     * Update Data Jadwal Ujian.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'id_bank'      => 'required|integer',
            'id_jenis'     => 'required|integer',
            'tgl_mulai'    => 'required|date',
            'tgl_selesai'  => 'required|date|after_or_equal:tgl_mulai',
            'durasi_ujian' => 'required|integer|min:10',
        ]);

        $jadwal = CbtJadwal::findOrFail($id);

        $jadwal->update([
            'id_bank'      => $request->input('id_bank'),
            'id_jenis'     => $request->input('id_jenis'),
            'tgl_mulai'    => $request->input('tgl_mulai'),
            'tgl_selesai'  => $request->input('tgl_selesai'),
            'durasi_ujian' => (int) $request->input('durasi_ujian'),
            'acak_soal'    => (int) $request->input('acak_soal', 0),
            'acak_opsi'    => (int) $request->input('acak_opsi', 0),
            'token'        => (int) $request->input('token', 0),
            'hasil_tampil' => (int) $request->input('hasil_tampil', 0),
            'reset_login'  => (int) $request->input('reset_login', 0),
            'pengawas'     => $request->input('pengawas', $jadwal->pengawas ?? []),
        ]);

        return back()->with('success', 'Jadwal ujian berhasil diperbarui.');
    }

    /**
     * Toggle Aktif / Nonaktifkan Jadwal.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        $jadwal = CbtJadwal::findOrFail($id);
        $jadwal->status = $jadwal->status ? 0 : 1;
        $jadwal->save();

        $statusText = $jadwal->status ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Jadwal ujian berhasil {$statusText}.");
    }

    /**
     * Hapus Jadwal Ujian.
     */
    public function destroy(int $id): RedirectResponse
    {
        CbtJadwal::where('id_jadwal', $id)->delete();
        return back()->with('success', 'Jadwal ujian berhasil dihapus.');
    }

    // =========================================================================
    // MANAJEMEN JENIS UJIAN (PAS, PTS, PAT, USBK, DLL)
    // =========================================================================
    public function indexJenis(Request $request): View
    {
        $search = $request->input('q');
        $query = CbtJenis::orderBy('id_jenis', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_jenis', 'like', "%{$search}%")
                  ->orWhere('kode_jenis', 'like', "%{$search}%");
            });
        }

        $jenisList = $query->paginate(10)->withQueryString();
        return view('admin.cbt.jadwal.jenis', compact('jenisList', 'search'));
    }

    public function storeJenis(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:100',
            'kode_jenis' => 'required|string|max:20',
        ]);

        CbtJenis::create($request->only('nama_jenis', 'kode_jenis'));
        return back()->with('success', 'Jenis Ujian baru berhasil disimpan.');
    }

    public function updateJenis(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:100',
            'kode_jenis' => 'required|string|max:20',
        ]);

        $jenis = CbtJenis::findOrFail($id);
        $jenis->update($request->only('nama_jenis', 'kode_jenis'));

        return back()->with('success', "Jenis Ujian [{$jenis->kode_jenis}] berhasil diperbarui.");
    }

    public function destroyJenis(int $id): RedirectResponse
    {
        CbtJenis::where('id_jenis', $id)->delete();
        return back()->with('success', 'Jenis Ujian berhasil dihapus.');
    }
}
