<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelasSiswa;
use App\Models\MasterKelas;
use App\Models\MasterSiswa;
use App\Models\RefKelas;
use App\Models\RefTahunAjaran;
use App\Models\SiswaRombelTahun;
use App\Services\AcademicYear\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ClassPromotionController extends Controller
{
    public function __construct(
        protected AcademicYearService $academicService
    ) {}

    /**
     * Halaman Utama Manajemen Kenaikan Kelas, Mutasi, dan Kelulusan.
     */
    public function index(Request $request): View
    {
        $this->academicService->ensureSchema();

        $allYears = $this->academicService->getAllYears();
        $activeYear = $this->academicService->getActiveYear();
        $selectedYearId = (int) ($request->input('tahun_ajaran_id') ?? $activeYear?->id ?? 1);

        $kelasList = MasterKelas::orderBy('nama_kelas', 'asc')->get();
        if ($kelasList->isEmpty()) {
            $kelasList = RefKelas::orderBy('nama_kelas', 'asc')->get();
        }

        $selectedKelasId = $request->input('kelas_id') ? (int) $request->input('kelas_id') : ($kelasList->first()?->id_kelas ?? $kelasList->first()?->id);

        // Ambil daftar siswa pada tahun ajaran dan kelas yang dipilih
        $siswaQuery = SiswaRombelTahun::with(['masterSiswa', 'masterKelas', 'tahunAjaran'])
            ->where('tahun_ajaran_id', $selectedYearId);

        if ($selectedKelasId) {
            $siswaQuery->where('kelas_id', $selectedKelasId);
        }

        $enrollments = $siswaQuery->get();

        // Jika tabel siswa_rombel_tahun masih kosong untuk filter ini, fallback ambil dari kelas_siswa legacy
        if ($enrollments->isEmpty() && $selectedKelasId) {
            $legacySiswa = DB::table('kelas_siswa')
                ->join('master_siswa', 'kelas_siswa.id_siswa', '=', 'master_siswa.id_siswa')
                ->where('kelas_siswa.id_kelas', $selectedKelasId)
                ->select('master_siswa.id_siswa', 'master_siswa.nama', 'master_siswa.nisn', 'master_siswa.username')
                ->orderBy('master_siswa.nama', 'asc')
                ->get();
        } else {
            $legacySiswa = collect();
        }

        $allSiswaList = MasterSiswa::orderBy('nama', 'asc')->select('id_siswa', 'nama', 'nisn', 'nis')->get();

        return view('admin.master.kenaikan_kelas', compact(
            'allYears',
            'activeYear',
            'selectedYearId',
            'kelasList',
            'selectedKelasId',
            'enrollments',
            'legacySiswa',
            'allSiswaList'
        ));
    }

    /**
     * Eksekusi Kenaikan Kelas Massal ke Tahun Ajaran Baru.
     */
    public function promoteClass(Request $request): RedirectResponse
    {
        $request->validate([
            'tahun_asal_id'   => 'required|integer',
            'kelas_asal_id'   => 'required|integer',
            'tahun_tujuan_id' => 'required|integer',
            'kelas_tujuan_id' => 'required|integer',
            'siswa_ids'       => 'required|array|min:1',
            'action_type'     => 'required|in:naik,tinggal',
        ]);

        $tahunAsalId   = (int) $request->input('tahun_asal_id');
        $kelasAsalId   = (int) $request->input('kelas_asal_id');
        $tahunTujuanId = (int) $request->input('tahun_tujuan_id');
        $kelasTujuanId = (int) $request->input('kelas_tujuan_id');
        $siswaIds      = $request->input('siswa_ids');
        $actionType    = $request->input('action_type');

        $tahunTujuan = RefTahunAjaran::findOrFail($tahunTujuanId);
        $kelasTujuan = MasterKelas::find($kelasTujuanId);

        DB::transaction(function () use ($tahunAsalId, $kelasAsalId, $tahunTujuanId, $kelasTujuanId, $siswaIds, $actionType, $tahunTujuan) {
            foreach ($siswaIds as $siswaId) {
                // 1. Update status di tahun ajaran asal
                SiswaRombelTahun::updateOrCreate(
                    ['siswa_id' => $siswaId, 'tahun_ajaran_id' => $tahunAsalId],
                    ['kelas_id' => $kelasAsalId, 'status' => $actionType, 'keterangan' => "Diproses ke T.P. {$tahunTujuan->nama_lengkap}"]
                );

                // 2. Buat entri rombel baru di tahun ajaran tujuan
                SiswaRombelTahun::updateOrCreate(
                    ['siswa_id' => $siswaId, 'tahun_ajaran_id' => $tahunTujuanId],
                    ['kelas_id' => $kelasTujuanId, 'status' => 'aktif', 'keterangan' => "Kenaikan dari kelas sebelumnya"]
                );

                // 3. Sinkronkan tabel kelas_siswa legacy jika tahun tujuan adalah tahun aktif
                if ($tahunTujuan->is_active) {
                    KelasSiswa::updateOrCreate(
                        ['id_siswa' => $siswaId],
                        ['id_kelas' => $kelasTujuanId, 'id_tp' => 1, 'id_smt' => (int) $tahunTujuan->semester]
                    );
                }
            }
        });

        $count = count($siswaIds);
        $statusText = $actionType === 'naik' ? 'dinaikkan' : 'ditetapkan tinggal kelas';
        return back()->with('success', "Sebanyak {$count} siswa berhasil {$statusText} ke kelas [{$kelasTujuan?->nama_kelas}] untuk T.P. [{$tahunTujuan->nama_lengkap}].");
    }

    /**
     * Mutasi / Pindah Kelas dalam Tahun Ajaran yang Sama.
     */
    public function switchClass(Request $request): RedirectResponse
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|integer',
            'kelas_tujuan_id' => 'required|integer',
            'alasan'          => 'nullable|string|max:255',
        ]);

        $siswaIds = [];
        if ($request->filled('siswa_id')) {
            $siswaIds = [(int) $request->input('siswa_id')];
        } elseif ($request->has('siswa_ids')) {
            $siswaIds = array_map('intval', (array) $request->input('siswa_ids'));
        }

        if (empty($siswaIds)) {
            return back()->with('error', 'Silakan pilih atau cari siswa yang ingin dimutasi / dipindahkan.');
        }

        $tahunAjaranId = (int) $request->input('tahun_ajaran_id');
        $kelasTujuanId = (int) $request->input('kelas_tujuan_id');
        $alasan        = $request->input('alasan', 'Pindah rombel kelas');

        $kelasTujuan = MasterKelas::find($kelasTujuanId);
        $ta = RefTahunAjaran::find($tahunAjaranId);

        DB::transaction(function () use ($tahunAjaranId, $kelasTujuanId, $siswaIds, $alasan, $ta) {
            foreach ($siswaIds as $siswaId) {
                SiswaRombelTahun::updateOrCreate(
                    ['siswa_id' => $siswaId, 'tahun_ajaran_id' => $tahunAjaranId],
                    ['kelas_id' => $kelasTujuanId, 'status' => 'aktif', 'keterangan' => $alasan]
                );

                if ($ta && $ta->is_active) {
                    KelasSiswa::updateOrCreate(
                        ['id_siswa' => $siswaId],
                        ['id_kelas' => $kelasTujuanId]
                    );
                }
            }
        });

        $count = count($siswaIds);
        return back()->with('success', "Sebanyak {$count} siswa berhasil dipindahkan ke rombel [{$kelasTujuan?->nama_kelas}].");
    }

    /**
     * Menetapkan Siswa Tingkat Akhir Menjadi Alumni (Lulus).
     */
    public function graduateStudents(Request $request): RedirectResponse
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|integer',
            'siswa_ids'       => 'required|array|min:1',
        ]);

        $tahunAjaranId = (int) $request->input('tahun_ajaran_id');
        $siswaIds      = $request->input('siswa_ids');

        DB::transaction(function () use ($tahunAjaranId, $siswaIds) {
            foreach ($siswaIds as $siswaId) {
                SiswaRombelTahun::where('siswa_id', $siswaId)
                    ->where('tahun_ajaran_id', $tahunAjaranId)
                    ->update(['status' => 'lulus', 'keterangan' => 'Lulus / Alumni']);

                // Lepaskan dari rombel kelas aktif di kelas_siswa agar masuk ke arsip alumni
                KelasSiswa::where('id_siswa', $siswaId)->delete();
            }
        });

        $count = count($siswaIds);
        return back()->with('success', "Sebanyak {$count} siswa tingkat akhir berhasil dinyatakan LULUS dan diarsipkan sebagai Alumni.");
    }

    /**
     * Switch Filter Tahun Ajaran pada Sesi Admin / Global.
     */
    public function switchAcademicYear(Request $request): RedirectResponse
    {
        $tahunId = $request->input('tahun_ajaran_id');

        if ($request->input('set_active_system') == '1' && $tahunId) {
            // Ubah tahun ajaran aktif sistem permanen
            $this->academicService->setActiveYear((int) $tahunId);
            $this->academicService->resetSelectedYear();
            return back()->with('success', 'Tahun Ajaran aktif sistem berhasil diperbarui.');
        }

        if ($tahunId) {
            $this->academicService->setSelectedYear((int) $tahunId);
            return back()->with('success', 'Filter tampilan tahun ajaran berhasil dialihkan ke mode arsip.');
        }

        $this->academicService->resetSelectedYear();
        return back()->with('success', 'Filter tampilan tahun ajaran dikembalikan ke tahun aktif sistem.');
    }
}
