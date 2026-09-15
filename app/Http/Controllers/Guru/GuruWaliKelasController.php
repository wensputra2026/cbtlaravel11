<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\MasterGuru;
use App\Models\MasterKelas;
use App\Services\Teacher\TeacherScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GuruWaliKelasController extends Controller
{
    public function __construct(
        protected TeacherScopeService $scopeService
    ) {}

    /**
     * Dapatkan assignment guru dan pastikan berstatus Wali Kelas.
     */
    protected function authorizeWali(): array
    {
        $guru = $this->scopeService->getTeacherProfile();
        if (!$guru) {
            abort(403, 'Akses Ditolak: Profil guru tidak ditemukan.');
        }

        $assignment = $this->scopeService->getTeacherAssignment($guru);
        if (!$assignment['is_wali_kelas'] || empty($assignment['wali_kelas'])) {
            abort(403, 'Akses Ditolak: Anda tidak ditugaskan sebagai Wali Kelas pada semester aktif saat ini.');
        }

        return $assignment;
    }

    /**
     * Tampilkan daftar siswa dalam kelas bimbingan wali kelas.
     */
    public function indexSiswa(Request $request): View
    {
        $assignment = $this->authorizeWali();
        $waliKelas = $assignment['wali_kelas'];
        $tpId = $assignment['active_tp']?->id_tp ?? 1;
        $smtId = $assignment['active_smt']?->id_smt ?? 1;

        $query = DB::table('kelas_siswa as ks')
            ->join('master_siswa as s', 's.id_siswa', '=', 'ks.id_siswa')
            ->where('ks.id_kelas', $waliKelas->id_kelas)
            ->where('ks.id_tp', $tpId)
            ->where('ks.id_smt', $smtId)
            ->select([
                's.id_siswa',
                's.nama',
                's.nis',
                's.nisn',
                's.jenis_kelamin',
                's.agama',
                's.username',
                's.foto',
                's.tempat_lahir',
                's.tanggal_lahir',
                's.hp',
                's.nama_ayah',
                's.nama_ibu',
            ]);

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($sq) use ($search) {
                $sq->where('s.nama', 'like', "%{$search}%")
                   ->orWhere('s.nis', 'like', "%{$search}%")
                   ->orWhere('s.nisn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('gender')) {
            $query->where('s.jenis_kelamin', $request->input('gender'));
        }

        if ($request->filled('agama')) {
            $query->where('s.agama', $request->input('agama'));
        }

        $siswas = $query->orderBy('s.nama', 'asc')->get();

        // Hitung statistik kelas
        $allSiswas = DB::table('kelas_siswa as ks')
            ->join('master_siswa as s', 's.id_siswa', '=', 'ks.id_siswa')
            ->where('ks.id_kelas', $waliKelas->id_kelas)
            ->where('ks.id_tp', $tpId)
            ->where('ks.id_smt', $smtId)
            ->select('s.jenis_kelamin', 's.agama')
            ->get();

        $stats = [
            'total'     => $allSiswas->count(),
            'laki'      => $allSiswas->where('jenis_kelamin', 'L')->count(),
            'perempuan' => $allSiswas->where('jenis_kelamin', 'P')->count(),
        ];

        return view('guru.wali.siswa', compact('assignment', 'waliKelas', 'siswas', 'stats'));
    }

    /**
     * Tampilkan struktur organisasi kelas bimbingan wali.
     */
    public function indexStruktur(): View
    {
        $assignment = $this->authorizeWali();
        $waliKelas = $assignment['wali_kelas'];
        $tpId = $assignment['active_tp']?->id_tp ?? 1;
        $smtId = $assignment['active_smt']?->id_smt ?? 1;

        // Ambil struktur kelas
        $struktur = DB::table('kelas_struktur')->where('id_kelas', $waliKelas->id_kelas)->first();

        // Ambil daftar siswa kelas untuk dropdown
        $siswas = DB::table('kelas_siswa as ks')
            ->join('master_siswa as s', 's.id_siswa', '=', 'ks.id_siswa')
            ->where('ks.id_kelas', $waliKelas->id_kelas)
            ->where('ks.id_tp', $tpId)
            ->where('ks.id_smt', $smtId)
            ->select('s.id_siswa', 's.nama', 's.nis')
            ->orderBy('s.nama', 'asc')
            ->get();

        return view('guru.wali.struktur', compact('assignment', 'waliKelas', 'struktur', 'siswas'));
    }

    /**
     * Simpan struktur organisasi kelas bimbingan wali.
     */
    public function saveStruktur(Request $request): RedirectResponse
    {
        $assignment = $this->authorizeWali();
        $waliKelas = $assignment['wali_kelas'];

        DB::table('kelas_struktur')->updateOrInsert(
            ['id_kelas' => $waliKelas->id_kelas],
            [
                'ketua'               => $request->filled('ketua') ? (int)$request->input('ketua') : null,
                'wakil_ketua'         => $request->filled('wakil_ketua') ? (int)$request->input('wakil_ketua') : null,
                'sekretaris_1'        => $request->filled('sekretaris_1') ? (int)$request->input('sekretaris_1') : null,
                'sekretaris_2'        => $request->filled('sekretaris_2') ? (int)$request->input('sekretaris_2') : null,
                'bendahara_1'         => $request->filled('bendahara_1') ? (int)$request->input('bendahara_1') : null,
                'bendahara_2'         => $request->filled('bendahara_2') ? (int)$request->input('bendahara_2') : null,
                'sie_ekstrakurikuler' => $request->filled('sie_ekstrakurikuler') ? (int)$request->input('sie_ekstrakurikuler') : null,
                'sie_upacara'         => $request->filled('sie_upacara') ? (int)$request->input('sie_upacara') : null,
                'sie_olahraga'        => $request->filled('sie_olahraga') ? (int)$request->input('sie_olahraga') : null,
                'sie_keagamaan'       => $request->filled('sie_keagamaan') ? (int)$request->input('sie_keagamaan') : null,
                'sie_keamanan'        => $request->filled('sie_keamanan') ? (int)$request->input('sie_keamanan') : null,
                'sie_ketertiban'      => $request->filled('sie_ketertiban') ? (int)$request->input('sie_ketertiban') : null,
                'sie_kebersihan'      => $request->filled('sie_kebersihan') ? (int)$request->input('sie_kebersihan') : null,
                'sie_keindahan'       => $request->filled('sie_keindahan') ? (int)$request->input('sie_keindahan') : null,
                'sie_kesehatan'       => $request->filled('sie_kesehatan') ? (int)$request->input('sie_kesehatan') : null,
                'sie_kekeluargaan'    => $request->filled('sie_kekeluargaan') ? (int)$request->input('sie_kekeluargaan') : null,
                'sie_humas'           => $request->filled('sie_humas') ? (int)$request->input('sie_humas') : null,
            ]
        );

        return back()->with('success', "Struktur organisasi kelas '{$waliKelas->nama_kelas}' berhasil disimpan.");
    }

    /**
     * Tampilkan catatan wali kelas untuk pembinaan siswa.
     */
    public function indexCatatan(Request $request): View
    {
        $assignment = $this->authorizeWali();
        $waliKelas = $assignment['wali_kelas'];
        $tpId = $assignment['active_tp']?->id_tp ?? 1;
        $smtId = $assignment['active_smt']?->id_smt ?? 1;

        $catatanQuery = DB::table('kelas_catatan_wali as c')
            ->leftJoin('master_siswa as s', 's.id_siswa', '=', 'c.id_siswa')
            ->where('c.id_kelas', $waliKelas->id_kelas)
            ->where('c.id_tp', $tpId)
            ->where('c.id_smt', $smtId)
            ->select([
                'c.id_catatan',
                'c.type',
                'c.level',
                'c.tgl',
                'c.text',
                's.id_siswa',
                's.nama as nama_siswa',
                's.nis',
            ]);

        if ($request->filled('level')) {
            $catatanQuery->where('c.level', $request->input('level'));
        }

        $catatans = $catatanQuery->orderBy('c.tgl', 'desc')->paginate(15)->withQueryString();

        // Siswa list untuk catatan per siswa
        $siswas = DB::table('kelas_siswa as ks')
            ->join('master_siswa as s', 's.id_siswa', '=', 'ks.id_siswa')
            ->where('ks.id_kelas', $waliKelas->id_kelas)
            ->where('ks.id_tp', $tpId)
            ->where('ks.id_smt', $smtId)
            ->select('s.id_siswa', 's.nama', 's.nis')
            ->orderBy('s.nama', 'asc')
            ->get();

        return view('guru.wali.catatan', compact('assignment', 'waliKelas', 'catatans', 'siswas'));
    }

    /**
     * Simpan catatan pembinaan baru.
     */
    public function storeCatatan(Request $request): RedirectResponse
    {
        $request->validate([
            'type'  => 'required|in:1,2',
            'level' => 'required|in:1,2,3,4',
            'text'  => 'required|string|max:1000',
        ]);

        $assignment = $this->authorizeWali();
        $waliKelas = $assignment['wali_kelas'];
        $tpId = $assignment['active_tp']?->id_tp ?? 1;
        $smtId = $assignment['active_smt']?->id_smt ?? 1;

        $type = (int)$request->input('type');
        $idSiswa = $type === 2 && $request->filled('id_siswa') ? (int)$request->input('id_siswa') : null;

        DB::table('kelas_catatan_wali')->insert([
            'id_tp'    => $tpId,
            'id_smt'   => $smtId,
            'type'     => $type,
            'level'    => $request->input('level'),
            'id_kelas' => $waliKelas->id_kelas,
            'id_siswa' => $idSiswa,
            'text'     => $request->input('text'),
            'tgl'      => now(),
        ]);

        return back()->with('success', 'Catatan pembinaan wali kelas berhasil ditambahkan.');
    }
}
