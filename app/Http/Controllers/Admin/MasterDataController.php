<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JabatanGuru;
use App\Models\KelasSiswa;
use App\Models\LevelGuru;
use App\Models\MasterEkstra;
use App\Models\MasterGuru;
use App\Models\MasterJurusan;
use App\Models\MasterKelas;
use App\Models\MasterKelompokMapel;
use App\Models\MasterMapel;
use App\Models\MasterSiswa;
use App\Models\MasterSmt;
use App\Models\MasterTp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use ZipArchive;

class MasterDataController extends Controller
{
    // =========================================================================
    // 1. TAHUN PELAJARAN & SEMESTER
    // =========================================================================
    public function indexTp(Request $request): View
    {
        $tpList = MasterTp::orderBy('id_tp', 'desc')->get();
        $smtList = MasterSmt::orderBy('id_smt', 'asc')->get();

        return view('admin.master.tp', compact('tpList', 'smtList'));
    }

    public function storeTp(Request $request): RedirectResponse
    {
        $request->validate(['tahun' => 'required|string|max:20']);

        $tp = MasterTp::create([
            'tahun'  => $request->input('tahun'),
            'active' => 0,
        ]);

        try {
            foreach (['1', '2'] as $sem) {
                $semText = $sem === '1' ? 'Ganjil' : 'Genap';
                \App\Models\RefTahunAjaran::updateOrCreate(
                    ['tahun' => $tp->tahun, 'semester' => $sem],
                    [
                        'nama_lengkap' => "T.P. {$tp->tahun} ({$semText})",
                        'is_active' => false,
                    ]
                );
            }
            app(\App\Services\AcademicYear\AcademicYearService::class)->clearCache();
        } catch (\Throwable $e) {}

        return back()->with('success', 'Tahun Pelajaran berhasil ditambahkan.');
    }

    public function setAktifTp(int $id): RedirectResponse
    {
        MasterTp::query()->update(['active' => 0]);
        MasterTp::where('id_tp', $id)->update(['active' => 1]);

        $tp = MasterTp::find($id);
        if ($tp) {
            $smt = MasterSmt::where('active', 1)->first()?->id_smt ?? 1;
            $ref = \App\Models\RefTahunAjaran::where('tahun', $tp->tahun)->where('semester', (string)$smt)->first();
            if ($ref) {
                app(\App\Services\AcademicYear\AcademicYearService::class)->setActiveYear($ref->id);
            }
        }

        return back()->with('success', 'Tahun Pelajaran aktif berhasil diubah.');
    }

    public function setAktifSmt(int $id): RedirectResponse
    {
        MasterSmt::query()->update(['active' => 0]);
        MasterSmt::where('id_smt', $id)->update(['active' => 1]);

        $tp = MasterTp::where('active', 1)->first();
        if ($tp) {
            $ref = \App\Models\RefTahunAjaran::where('tahun', $tp->tahun)->where('semester', (string)$id)->first();
            if ($ref) {
                app(\App\Services\AcademicYear\AcademicYearService::class)->setActiveYear($ref->id);
            }
        }

        return back()->with('success', 'Semester aktif berhasil diubah.');
    }

    // =========================================================================
    // 2. JURUSAN / PEMINATAN
    // =========================================================================
    public function indexJurusan(Request $request): View
    {
        $search = $request->input('q');
        $query = MasterJurusan::orderBy('id_jurusan', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_jurusan', 'like', "%{$search}%")
                  ->orWhere('kode_jurusan', 'like', "%{$search}%");
            });
        }

        $jurusanList = $query->paginate(10)->withQueryString();
        return view('admin.master.jurusan', compact('jurusanList', 'search'));
    }

    public function storeJurusan(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:100',
            'kode_jurusan' => 'required|string|max:20',
        ]);

        MasterJurusan::create($request->only('nama_jurusan', 'kode_jurusan'));
        return back()->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function updateJurusan(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:100',
            'kode_jurusan' => 'required|string|max:20',
        ]);

        $jurusan = MasterJurusan::findOrFail($id);
        $jurusan->update($request->only('nama_jurusan', 'kode_jurusan'));
        return back()->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroyJurusan(int $id): RedirectResponse
    {
        $jurusan = MasterJurusan::findOrFail($id);
        $jurusan->delete();
        return back()->with('success', 'Jurusan berhasil dihapus.');
    }

    // =========================================================================
    // 3. KELAS & ROMBEL
    // =========================================================================
    // 3. KELAS & ROMBEL
    // =========================================================================
    public function indexKelas(Request $request): View
    {
        $activeTp = MasterTp::activeTp() ?? MasterTp::orderBy('id_tp', 'desc')->first();
        $activeSmt = MasterSmt::activeSmt() ?? MasterSmt::orderBy('id_smt', 'desc')->first();
        $tpId = $activeTp?->id_tp ?? 1;
        $smtId = $activeSmt?->id_smt ?? 1;

        $query = DB::table('master_kelas as a')
            ->leftJoin('master_jurusan as b', 'b.id_jurusan', '=', 'a.jurusan_id')
            ->leftJoin('jabatan_guru as f', function ($join) use ($tpId, $smtId) {
                $join->on('f.id_kelas', '=', 'a.id_kelas')
                    ->where('f.id_jabatan', '=', 4)
                    ->where('f.id_tp', '=', $tpId)
                    ->where('f.id_smt', '=', $smtId);
            })
            ->leftJoin('master_guru as d', 'd.id_guru', '=', 'f.id_guru')
            ->select([
                'a.id_kelas',
                'a.nama_kelas',
                'a.kode_kelas',
                'a.level_id',
                'a.jurusan_id',
                'a.guru_id',
                'b.nama_jurusan',
                'd.id_guru as wali_guru_id',
                'd.nama_guru as nama_wali',
                'd.nip as nip_wali',
                'd.foto as foto_wali',
                DB::raw("(SELECT COUNT(k.id_kelas_siswa) FROM kelas_siswa k WHERE k.id_kelas = a.id_kelas AND k.id_tp = {$tpId} AND k.id_smt = {$smtId}) as jml_siswa")
            ]);

        // Filter Tahun & Semester aktif (jika ada data untuk TP/SMT aktif)
        $hasCurrentTpData = DB::table('master_kelas')->where('id_tp', $tpId)->where('id_smt', $smtId)->exists();
        if ($hasCurrentTpData) {
            $query->where('a.id_tp', $tpId)->where('a.id_smt', $smtId);
        }

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($sq) use ($search) {
                $sq->where('a.nama_kelas', 'like', "%{$search}%")
                   ->orWhere('a.kode_kelas', 'like', "%{$search}%")
                   ->orWhere('d.nama_guru', 'like', "%{$search}%");
            });
        }

        if ($request->filled('level_id')) {
            $query->where('a.level_id', (int) $request->input('level_id'));
        }

        if ($request->filled('jurusan_id')) {
            $query->where('a.jurusan_id', (int) $request->input('jurusan_id'));
        }

        if ($request->filled('status_wali')) {
            if ($request->input('status_wali') === 'terisi') {
                $query->whereNotNull('d.id_guru');
            } elseif ($request->input('status_wali') === 'kosong') {
                $query->whereNull('d.id_guru');
            }
        }

        $kelasList = $query->orderBy('a.level_id', 'asc')
            ->orderBy('a.nama_kelas', 'asc')
            ->paginate(15)
            ->withQueryString();

        $jurusanList = MasterJurusan::orderBy('nama_jurusan', 'asc')->get();
        $guruList = MasterGuru::orderBy('nama_guru', 'asc')->get();

        // Hitung statistik lengkap
        $baseCountQuery = DB::table('master_kelas');
        if ($hasCurrentTpData) {
            $baseCountQuery->where('id_tp', $tpId)->where('id_smt', $smtId);
        }
        $totalKelas = (clone $baseCountQuery)->count();
        $l10Count = (clone $baseCountQuery)->where('level_id', 10)->count();
        $l11Count = (clone $baseCountQuery)->where('level_id', 11)->count();
        $l12Count = (clone $baseCountQuery)->where('level_id', 12)->count();

        $totalSiswa = DB::table('kelas_siswa')
            ->where('id_tp', $tpId)
            ->where('id_smt', $smtId)
            ->where('id_kelas', '>', 0)
            ->count();

        $waliCount = DB::table('jabatan_guru')
            ->where('id_tp', $tpId)
            ->where('id_smt', $smtId)
            ->where('id_jabatan', 4)
            ->where('id_kelas', '>', 0)
            ->count();

        $stats = [
            'total'       => $totalKelas,
            'l10'         => $l10Count,
            'l11'         => $l11Count,
            'l12'         => $l12Count,
            'total_siswa' => $totalSiswa,
            'wali_count'  => $waliCount,
        ];

        return view('admin.master.kelas', compact(
            'kelasList', 
            'jurusanList', 
            'guruList', 
            'stats', 
            'activeTp', 
            'activeSmt'
        ));
    }

    public function storeKelas(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:50',
            'kode_kelas' => 'required|string|max:20',
            'level_id'   => 'required|integer',
            'jurusan_id' => 'nullable|integer',
            'guru_id'    => 'nullable|integer',
        ]);

        $activeTp = MasterTp::activeTp() ?? MasterTp::orderBy('id_tp', 'desc')->first();
        $activeSmt = MasterSmt::activeSmt() ?? MasterSmt::orderBy('id_smt', 'desc')->first();
        $tpId = $activeTp?->id_tp ?? 1;
        $smtId = $activeSmt?->id_smt ?? 1;

        $guruId = (int) ($request->input('guru_id') ?? 0);

        $kelas = MasterKelas::create([
            'nama_kelas' => $request->input('nama_kelas'),
            'kode_kelas' => strtoupper($request->input('kode_kelas')),
            'level_id'   => (int) $request->input('level_id'),
            'jurusan_id' => $request->filled('jurusan_id') ? (int) $request->input('jurusan_id') : null,
            'guru_id'    => $guruId,
            'id_tp'      => $tpId,
            'id_smt'     => $smtId,
        ]);

        // Hubungkan Wali Kelas ke jabatan_guru
        if ($guruId > 0) {
            $idJabatanGuru = "{$guruId}{$tpId}{$smtId}";
            DB::table('jabatan_guru')->updateOrInsert(
                [
                    'id_guru' => $guruId,
                    'id_tp'   => $tpId,
                    'id_smt'  => $smtId,
                ],
                [
                    'id_jabatan_guru' => $idJabatanGuru,
                    'id_jabatan'      => 4, // Wali Kelas
                    'id_kelas'        => $kelas->id_kelas,
                ]
            );
        }

        return back()->with('success', "Kelas '{$kelas->nama_kelas}' berhasil ditambahkan.");
    }

    public function updateKelas(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:50',
            'kode_kelas' => 'required|string|max:20',
            'level_id'   => 'required|integer',
            'jurusan_id' => 'nullable|integer',
            'guru_id'    => 'nullable|integer',
        ]);

        $kelas = MasterKelas::findOrFail($id);
        $activeTp = MasterTp::activeTp() ?? MasterTp::orderBy('id_tp', 'desc')->first();
        $activeSmt = MasterSmt::activeSmt() ?? MasterSmt::orderBy('id_smt', 'desc')->first();
        $tpId = $activeTp?->id_tp ?? ($kelas->id_tp ?: 1);
        $smtId = $activeSmt?->id_smt ?? ($kelas->id_smt ?: 1);

        $newGuruId = (int) ($request->input('guru_id') ?? 0);

        $kelas->update([
            'nama_kelas' => $request->input('nama_kelas'),
            'kode_kelas' => strtoupper($request->input('kode_kelas')),
            'level_id'   => (int) $request->input('level_id'),
            'jurusan_id' => $request->filled('jurusan_id') ? (int) $request->input('jurusan_id') : null,
            'guru_id'    => $newGuruId,
        ]);

        // Lepas wali kelas lama untuk kelas ini
        DB::table('jabatan_guru')
            ->where('id_kelas', $id)
            ->where('id_tp', $tpId)
            ->where('id_smt', $smtId)
            ->where('id_jabatan', 4)
            ->where('id_guru', '!=', $newGuruId)
            ->update([
                'id_kelas'   => 0,
                'id_jabatan' => 5, // Kembalikan ke Guru Mapel biasa
            ]);

        // Pasang wali kelas baru
        if ($newGuruId > 0) {
            $idJabatanGuru = "{$newGuruId}{$tpId}{$smtId}";
            DB::table('jabatan_guru')->updateOrInsert(
                [
                    'id_guru' => $newGuruId,
                    'id_tp'   => $tpId,
                    'id_smt'  => $smtId,
                ],
                [
                    'id_jabatan_guru' => $idJabatanGuru,
                    'id_jabatan'      => 4, // Wali Kelas
                    'id_kelas'        => $id,
                ]
            );
        }

        return back()->with('success', "Data Kelas '{$kelas->nama_kelas}' berhasil diperbarui.");
    }

    public function destroyKelas(int $id): RedirectResponse
    {
        $kelas = MasterKelas::findOrFail($id);
        $nama = $kelas->nama_kelas;

        // Lepaskan penugasan wali kelas
        DB::table('jabatan_guru')->where('id_kelas', $id)->update(['id_kelas' => 0, 'id_jabatan' => 5]);

        // Hapus penempatan siswa di kelas ini
        DB::table('kelas_siswa')->where('id_kelas', $id)->delete();

        $kelas->delete();
        return back()->with('success', "Kelas '{$nama}' beserta penempatannya berhasil dihapus.");
    }

    /**
     * Tambah Kelas Sekaligus (Bulk Create Kelas) seperti pada US1.
     */
    public function bulkStoreKelas(Request $request): RedirectResponse
    {
        $request->validate([
            'format_angka' => 'required|in:romawi,biasa',
            'level'        => 'required|integer',
            'jumlah'       => 'required|integer|min:1|max:20',
            'char_type'    => 'required|in:angka,huruf',
            'awal'         => 'required|string',
            'nama_pola'    => 'nullable|string|max:30',
            'jurusan_id'   => 'nullable|integer',
        ]);

        $activeTp = MasterTp::activeTp() ?? MasterTp::orderBy('id_tp', 'desc')->first();
        $activeSmt = MasterSmt::activeSmt() ?? MasterSmt::orderBy('id_smt', 'desc')->first();
        $tpId = $activeTp?->id_tp ?? 1;
        $smtId = $activeSmt?->id_smt ?? 1;

        $formatAngka = $request->input('format_angka');
        $level = (int) $request->input('level');
        $jumlah = (int) $request->input('jumlah');
        $charType = $request->input('char_type');
        $awal = $request->input('awal');
        $namaPola = trim($request->input('nama_pola') ?? 'Merdeka');
        $jurusanId = $request->filled('jurusan_id') ? (int) $request->input('jurusan_id') : null;

        $romanMap = [10 => 'X', 11 => 'XI', 12 => 'XII', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI'];
        $levelText = $formatAngka === 'romawi' ? ($romanMap[$level] ?? (string)$level) : (string)$level;

        $startVal = $charType === 'angka' ? (int) $awal : ord(strtoupper($awal));
        $createdCount = 0;

        for ($i = 0; $i < $jumlah; $i++) {
            $suffix = $charType === 'angka' ? (string)($startVal + $i) : chr($startVal + $i);
            $namaKelas = $namaPola !== '' ? "{$levelText} {$namaPola} {$suffix}" : "{$levelText} {$suffix}";
            $kodeKelas = $namaPola !== '' ? "{$levelText}-" . strtoupper(substr($namaPola, 0, 1)) . "{$suffix}" : "{$levelText}-{$suffix}";

            MasterKelas::updateOrCreate(
                [
                    'nama_kelas' => $namaKelas,
                    'id_tp'      => $tpId,
                    'id_smt'     => $smtId,
                ],
                [
                    'kode_kelas' => strtoupper($kodeKelas),
                    'level_id'   => $level,
                    'jurusan_id' => $jurusanId,
                    'guru_id'    => 0,
                ]
            );
            $createdCount++;
        }

        return back()->with('success', "Berhasil menambahkan {$createdCount} kelas baru secara serentak.");
    }

    /**
     * Mengambil data detail kelas dan daftar siswa untuk modal AJAX.
     */
    public function detailKelas(int $id): JsonResponse
    {
        $activeTp = MasterTp::activeTp() ?? MasterTp::orderBy('id_tp', 'desc')->first();
        $activeSmt = MasterSmt::activeSmt() ?? MasterSmt::orderBy('id_smt', 'desc')->first();
        $tpId = $activeTp?->id_tp ?? 1;
        $smtId = $activeSmt?->id_smt ?? 1;

        $kelas = DB::table('master_kelas as a')
            ->leftJoin('master_jurusan as b', 'b.id_jurusan', '=', 'a.jurusan_id')
            ->leftJoin('jabatan_guru as f', function ($join) use ($tpId, $smtId) {
                $join->on('f.id_kelas', '=', 'a.id_kelas')
                    ->where('f.id_jabatan', '=', 4)
                    ->where('f.id_tp', '=', $tpId)
                    ->where('f.id_smt', '=', $smtId);
            })
            ->leftJoin('master_guru as d', 'd.id_guru', '=', 'f.id_guru')
            ->where('a.id_kelas', $id)
            ->select([
                'a.id_kelas',
                'a.nama_kelas',
                'a.kode_kelas',
                'a.level_id',
                'b.nama_jurusan',
                'd.nama_guru as nama_wali',
                'd.nip as nip_wali',
                'd.foto as foto_wali',
            ])
            ->first();

        if (!$kelas) {
            return response()->json(['success' => false, 'message' => 'Kelas tidak ditemukan'], 404);
        }

        $siswas = DB::table('kelas_siswa as ks')
            ->join('master_siswa as s', 's.id_siswa', '=', 'ks.id_siswa')
            ->where('ks.id_kelas', $id)
            ->where('ks.id_tp', $tpId)
            ->where('ks.id_smt', $smtId)
            ->select([
                's.id_siswa',
                's.nama',
                's.nis',
                's.nisn',
                's.jenis_kelamin',
                's.agama',
                's.foto'
            ])
            ->orderBy('s.nama', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'kelas'   => $kelas,
            'siswas'  => $siswas,
            'total'   => $siswas->count(),
            'laki'    => $siswas->where('jenis_kelamin', 'L')->count(),
            'perempuan' => $siswas->where('jenis_kelamin', 'P')->count(),
        ]);
    }

    /**
     * Salin semua data kelas dan siswa dari Semester I ke Semester II (seperti di US1).
     */
    public function syncSemesterKelas(Request $request): RedirectResponse
    {
        $activeTp = MasterTp::activeTp() ?? MasterTp::orderBy('id_tp', 'desc')->first();
        $tpId = $activeTp?->id_tp ?? 1;

        // Ambil kelas dari Semester 1
        $kelasSmt1 = MasterKelas::where('id_tp', $tpId)->where('id_smt', 1)->get();
        if ($kelasSmt1->isEmpty()) {
            return back()->with('error', 'Tidak ada data kelas pada Semester I untuk disalin.');
        }

        $copiedClasses = 0;
        $copiedStudents = 0;

        foreach ($kelasSmt1 as $k1) {
            // Cek atau buat kelas pada Semester 2
            $k2 = MasterKelas::firstOrCreate(
                [
                    'id_tp'      => $tpId,
                    'id_smt'     => 2,
                    'nama_kelas' => $k1->nama_kelas,
                ],
                [
                    'kode_kelas'   => $k1->kode_kelas,
                    'level_id'     => $k1->level_id,
                    'jurusan_id'   => $k1->jurusan_id,
                    'guru_id'      => $k1->guru_id,
                    'jumlah_siswa' => $k1->jumlah_siswa,
                    'set_siswa'    => $k1->set_siswa,
                ]
            );

            if ($k2->wasRecentlyCreated) {
                $copiedClasses++;
            }

            // Salin siswa kelas dari SMT 1 ke SMT 2
            $siswaSmt1 = DB::table('kelas_siswa')
                ->where('id_tp', $tpId)
                ->where('id_smt', 1)
                ->where('id_kelas', $k1->id_kelas)
                ->get();

            foreach ($siswaSmt1 as $s) {
                $idKelasSiswa = "{$tpId}2{$s->id_siswa}";
                DB::table('kelas_siswa')->updateOrInsert(
                    [
                        'id_tp'    => $tpId,
                        'id_smt'   => 2,
                        'id_siswa' => $s->id_siswa,
                    ],
                    [
                        'id_kelas_siswa' => $idKelasSiswa,
                        'id_kelas'       => $k2->id_kelas,
                    ]
                );
                $copiedStudents++;
            }

            // Salin penugasan wali kelas
            if ($k1->guru_id > 0) {
                $idJabatanGuru = "{$k1->guru_id}{$tpId}2";
                DB::table('jabatan_guru')->updateOrInsert(
                    [
                        'id_guru' => $k1->guru_id,
                        'id_tp'   => $tpId,
                        'id_smt'  => 2,
                    ],
                    [
                        'id_jabatan_guru' => $idJabatanGuru,
                        'id_jabatan'      => 4,
                        'id_kelas'        => $k2->id_kelas,
                    ]
                );
            }
        }

        return back()->with('success', "Berhasil menyalin {$copiedClasses} kelas dan {$copiedStudents} penempatan siswa ke Semester II.");
    }

    // =========================================================================
    // 4. MATA PELAJARAN & KELOMPOK MAPEL
    // =========================================================================
    public function indexMapel(Request $request): View
    {
        $query = MasterMapel::with('kelompokRelasi');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($sq) use ($search) {
                $sq->where('nama_mapel', 'like', "%{$search}%")
                   ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelompok') && $request->input('kelompok') !== 'all') {
            $query->where('kelompok', $request->input('kelompok'));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', (int) $request->input('status'));
        }

        // Ambil data mapel diurutkan berdasarkan kelompok, urutan_tampil, nama_mapel
        $mapelList = $query->orderBy('kelompok', 'asc')
            ->orderBy('urutan_tampil', 'asc')
            ->orderBy('nama_mapel', 'asc')
            ->get();

        // Kelompok Utama (id_parent = 0)
        $kelompokUtama = MasterKelompokMapel::where('id_parent', 0)
            ->orderBy('kode_kel_mapel', 'asc')
            ->get();

        // Sub Kelompok (id_parent != 0)
        $subKelompok = MasterKelompokMapel::where('id_parent', '<>', 0)
            ->with('parent')
            ->orderBy('kode_kel_mapel', 'asc')
            ->get();

        // Kategori Baku
        $kategoriList = [
            'WAJIB', 
            'PAI (Kemenag)', 
            'PEMINATAN AKADEMIK', 
            'AKADEMIK KEJURUAN', 
            'LINTAS MINAT', 
            'MULOK'
        ];

        // All Kelompok options for dropdown (Key: kode_kel_mapel, Val: nama_kel_mapel)
        $allKelompok = MasterKelompokMapel::orderBy('kode_kel_mapel', 'asc')->get();

        $stats = [
            'total'      => MasterMapel::count(),
            'aktif'      => MasterMapel::where('status', 1)->count(),
            'nonaktif'   => MasterMapel::where('status', 0)->count(),
            'max_urutan' => MasterMapel::max('urutan_tampil') ?? 0,
        ];

        return view('admin.master.mapel', compact(
            'mapelList', 
            'kelompokUtama', 
            'subKelompok', 
            'kategoriList', 
            'allKelompok', 
            'stats'
        ));
    }

    public function storeMapel(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_mapel'    => 'required|string|max:100',
            'kode'          => 'required|string|max:20',
            'kelompok'      => 'nullable|string|max:20',
            'mapel_agama'   => 'nullable|in:0,1',
            'status'        => 'nullable|in:0,1',
            'urutan_tampil' => 'nullable|integer',
        ]);

        MasterMapel::create([
            'nama_mapel'    => $request->input('nama_mapel'),
            'kode'          => strtoupper(trim($request->input('kode'))),
            'kelompok'      => $request->input('kelompok') ?: '-',
            'mapel_agama'   => $request->input('mapel_agama', '0'),
            'status'        => (int) $request->input('status', 1),
            'urutan_tampil' => $request->filled('urutan_tampil') ? (int) $request->input('urutan_tampil') : ((MasterMapel::max('urutan_tampil') ?? 0) + 1),
            'deletable'     => 1,
        ]);

        return back()->with('success', 'Mata Pelajaran berhasil ditambahkan.');
    }

    public function updateMapel(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'nama_mapel'    => 'required|string|max:100',
            'kode'          => 'required|string|max:20',
            'kelompok'      => 'nullable|string|max:20',
            'mapel_agama'   => 'nullable|in:0,1',
            'status'        => 'nullable|in:0,1',
            'urutan_tampil' => 'nullable|integer',
        ]);

        $mapel = MasterMapel::findOrFail($id);
        $mapel->update([
            'nama_mapel'    => $request->input('nama_mapel'),
            'kode'          => strtoupper(trim($request->input('kode'))),
            'kelompok'      => $request->input('kelompok') ?: '-',
            'mapel_agama'   => $request->input('mapel_agama', '0'),
            'status'        => (int) $request->input('status', 1),
            'urutan_tampil' => $request->filled('urutan_tampil') ? (int) $request->input('urutan_tampil') : $mapel->urutan_tampil,
        ]);

        return back()->with('success', 'Mata Pelajaran berhasil diperbarui.');
    }

    public function destroyMapel(int $id): RedirectResponse
    {
        $mapel = MasterMapel::findOrFail($id);
        $messages = [];

        // Cek tabel bank soal
        if (DB::table('cbt_bank_soal')->where('bank_mapel_id', $id)->exists()) {
            $messages[] = 'Bank Soal (cbt_bank_soal)';
        }

        // Cek tabel soal
        if (DB::table('cbt_soal')->where('mapel_id', $id)->exists()) {
            $messages[] = 'Butir Soal (cbt_soal)';
        }

        // Cek tabel rekap jadwal jika ada
        if (Schema::hasTable('cbt_rekap_jadwal') && DB::table('cbt_rekap_jadwal')->where('id_mapel', $id)->exists()) {
            $messages[] = 'Rekap Jadwal (cbt_rekap_jadwal)';
        }

        if (!empty($messages)) {
            return back()->with('error', 'Mata pelajaran tidak dapat dihapus karena masih digunakan di: ' . implode(', ', $messages) . '. Harap hapus atau ubah relasi data terlebih dahulu.');
        }

        $mapel->delete();
        return back()->with('success', 'Mata Pelajaran berhasil dihapus.');
    }

    public function bulkDestroyMapel(Request $request): RedirectResponse
    {
        $ids = $request->input('checked', []);
        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'Tidak ada mata pelajaran yang dipilih untuk dihapus.');
        }

        $inUse = [];
        $deletableIds = [];

        foreach ($ids as $id) {
            $mapel = MasterMapel::find($id);
            if (!$mapel) continue;

            $used = false;
            if (DB::table('cbt_bank_soal')->where('bank_mapel_id', $id)->exists() ||
                DB::table('cbt_soal')->where('mapel_id', $id)->exists()) {
                $used = true;
                $inUse[] = $mapel->nama_mapel . " ({$mapel->kode})";
            }

            if (!$used) {
                $deletableIds[] = $id;
            }
        }

        $deletedCount = 0;
        if (!empty($deletableIds)) {
            $deletedCount = MasterMapel::whereIn('id_mapel', $deletableIds)->delete();
        }

        if (!empty($inUse)) {
            $warn = "Sebanyak {$deletedCount} mapel berhasil dihapus. Namun, mapel berikut tidak dapat dihapus karena masih digunakan dalam Bank Soal/Soal: " . implode(', ', $inUse);
            return back()->with('warning', $warn);
        }

        return back()->with('success', "Sebanyak {$deletedCount} data Mata Pelajaran berhasil dihapus.");
    }

    public function toggleStatusMapel(int $id): RedirectResponse
    {
        $mapel = MasterMapel::findOrFail($id);
        $mapel->status = $mapel->status == 1 ? 0 : 1;
        $mapel->save();

        $statusText = $mapel->status == 1 ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Mata Pelajaran {$mapel->nama_mapel} berhasil {$statusText}.");
    }

    public function storeKelompokMapel(Request $request): RedirectResponse
    {
        $request->validate([
            'kode_kel_mapel' => 'required|string|max:20',
            'nama_kel_mapel' => 'required|string|max:100',
            'kategori'       => 'nullable|string|max:50',
            'id_parent'      => 'nullable|integer',
        ]);

        $idParent = (int) $request->input('id_parent', 0);
        $kategori = $request->input('kategori');

        // Jika sub-kelompok dan kategori kosong, ambil kategori induk
        if ($idParent > 0 && empty($kategori)) {
            $parent = MasterKelompokMapel::find($idParent);
            if ($parent) {
                $kategori = $parent->kategori;
            }
        }

        MasterKelompokMapel::create([
            'kode_kel_mapel' => strtoupper(trim($request->input('kode_kel_mapel'))),
            'nama_kel_mapel' => $request->input('nama_kel_mapel'),
            'kategori'       => $kategori ?: 'WAJIB',
            'id_parent'      => $idParent,
        ]);

        $tipe = $idParent == 0 ? 'Kelompok Utama' : 'Sub Kelompok';
        return back()->with('success', "{$tipe} berhasil ditambahkan.");
    }

    public function updateKelompokMapel(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'kode_kel_mapel' => 'required|string|max:20',
            'nama_kel_mapel' => 'required|string|max:100',
            'kategori'       => 'nullable|string|max:50',
            'id_parent'      => 'nullable|integer',
        ]);

        $kelompok = MasterKelompokMapel::findOrFail($id);
        $oldKode = $kelompok->kode_kel_mapel;
        $newKode = strtoupper(trim($request->input('kode_kel_mapel')));

        $idParent = $request->has('id_parent') ? (int) $request->input('id_parent') : $kelompok->id_parent;
        $kategori = $request->input('kategori');

        if ($idParent > 0 && empty($kategori)) {
            $parent = MasterKelompokMapel::find($idParent);
            if ($parent) {
                $kategori = $parent->kategori;
            }
        }

        $kelompok->update([
            'kode_kel_mapel' => $newKode,
            'nama_kel_mapel' => $request->input('nama_kel_mapel'),
            'kategori'       => $kategori ?: $kelompok->kategori,
            'id_parent'      => $idParent,
        ]);

        // Jika kodenya berubah, perbarui referensi di master_mapel
        if ($oldKode !== $newKode) {
            MasterMapel::where('kelompok', $oldKode)->update(['kelompok' => $newKode]);
        }

        $tipe = $kelompok->id_parent == 0 ? 'Kelompok Utama' : 'Sub Kelompok';
        return back()->with('success', "{$tipe} berhasil diperbarui.");
    }

    public function destroyKelompokMapel(int $id): RedirectResponse
    {
        $kelompok = MasterKelompokMapel::findOrFail($id);
        $messages = [];

        // Cek apakah kode kelompok digunakan di master_mapel
        $mapelCount = MasterMapel::where('kelompok', $kelompok->kode_kel_mapel)->count();
        if ($mapelCount > 0) {
            $messages[] = "Mata Pelajaran ({$mapelCount} mapel terkait)";
        }

        // Cek apakah id_kel_mapel digunakan sebagai parent oleh sub-kelompok
        $subCount = MasterKelompokMapel::where('id_parent', $kelompok->id_kel_mapel)->count();
        if ($subCount > 0) {
            $messages[] = "Sub Kelompok ({$subCount} sub-kelompok terkait)";
        }

        if (!empty($messages)) {
            return back()->with('error', 'Kelompok Mapel tidak dapat dihapus karena masih digunakan pada: ' . implode(', ', $messages) . '. Harap ubah atau hapus data terkait terlebih dahulu.');
        }

        $kelompok->delete();
        return back()->with('success', 'Kelompok Mapel berhasil dihapus.');
    }

    public function downloadTemplateMapel()
    {
        $excelPath = public_path('templates/format_mapel.xlsx');
        if (file_exists($excelPath)) {
            return response()->download($excelPath, 'format_mapel.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        // Fallback ke CSV jika file xlsx tidak ditemukan
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="format_mapel.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, ['No', 'Nama Mapel', 'Kode Mapel', 'Kelompok', 'No Urut Rapor']);
            fputcsv($handle, ['1', 'Pendidikan Agama Islam', 'PAI', 'A', '1']);
            fputcsv($handle, ['2', 'Pendidikan Pancasila dan Kewarganegaraan', 'PPKN', 'A', '2']);
            fputcsv($handle, ['3', 'Bahasa Indonesia', 'BIND', 'A', '3']);
            fputcsv($handle, ['4', 'Matematika', 'MAT', 'A', '4']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================================================================
    // 5. DATA GURU & STAF (SYNCHRONIZED WITH US1)
    // =========================================================================
    public function indexGuru(Request $request): View
    {
        $activeTp = MasterTp::where('active', 1)->first() ?? MasterTp::first();
        $activeSmt = MasterSmt::where('active', 1)->first() ?? MasterSmt::first();

        $query = MasterGuru::with([
            'user',
            'jabatan.level',
            'jabatan.kelas',
        ]);

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($sq) use ($search) {
                $sq->where('nama_guru', 'like', "%{$search}%")
                   ->orWhere('nip', 'like', "%{$search}%")
                   ->orWhere('username', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('level_id')) {
            $levelId = (int) $request->input('level_id');
            $query->whereHas('jabatan', function ($jq) use ($levelId) {
                $jq->where('id_jabatan', $levelId);
            });
        }

        if ($request->filled('status')) {
            $status = (int) $request->input('status');
            $query->whereHas('user', function ($uq) use ($status) {
                $uq->where('active', $status);
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $guruList = $query->orderBy('nama_guru', 'asc')->paginate($perPage)->withQueryString();

        $kelasList = MasterKelas::orderBy('nama_kelas', 'asc')->get();
        $mapelList = MasterMapel::where('status', 1)->orderBy('nama_mapel', 'asc')->get();
        $levels = LevelGuru::orderBy('id_level', 'asc')->get();

        // Map id_kelas => nama_kelas for quick lookup in table badges
        $kelasMap = $kelasList->pluck('nama_kelas', 'id_kelas')->toArray();

        $stats = [
            'total'     => MasterGuru::count(),
            'with_nip'  => MasterGuru::whereNotNull('nip')->where('nip', '!=', '-')->where('nip', '!=', '')->count(),
            'aktif'     => MasterGuru::whereHas('user', fn($u) => $u->where('active', 1))->count(),
            'walikelas' => JabatanGuru::where('id_tp', $activeTp?->id_tp ?? 1)
                                      ->where('id_smt', $activeSmt?->id_smt ?? 1)
                                      ->where('id_jabatan', 4)->count(),
        ];

        return view('admin.master.guru', compact('guruList', 'stats', 'kelasList', 'mapelList', 'levels', 'kelasMap', 'activeTp', 'activeSmt'));
    }

    public function storeGuru(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_guru'     => 'required|string|max:100',
            'username'      => 'required|string|max:50|unique:users,username|unique:master_guru,username',
            'password'      => 'required|string|min:4',
            'nip'           => 'nullable|string|max:30',
            'email'         => 'nullable|email|max:100',
            'no_hp'         => 'nullable|string|max:25',
            'jenis_kelamin' => 'nullable|in:L,P',
            'id_level'      => 'nullable|integer',
            'id_kelas'      => 'nullable|integer',
        ]);

        $activeTp = MasterTp::where('active', 1)->first() ?? MasterTp::first();
        $activeSmt = MasterSmt::where('active', 1)->first() ?? MasterSmt::first();

        DB::transaction(function () use ($request, $activeTp, $activeSmt) {
            $user = User::create([
                'username'   => trim($request->input('username')),
                'password'   => password_hash($request->input('password'), PASSWORD_BCRYPT),
                'first_name' => trim($request->input('nama_guru')),
                'email'      => $request->input('email'),
                'active'     => 1,
                'created_on' => time(),
            ]);
            $user->groups()->attach(User::ROLE_GURU);

            $guru = MasterGuru::create([
                'id_user'       => $user->id,
                'nama_guru'     => trim($request->input('nama_guru')),
                'nip'           => trim($request->input('nip')) ?: '-',
                'username'      => trim($request->input('username')),
                'password'      => trim($request->input('password')),
                'email'         => $request->input('email'),
                'no_hp'         => $request->input('no_hp'),
                'jenis_kelamin' => $request->input('jenis_kelamin'),
                'foto'          => 'uploads/profiles/' . (trim($request->input('nip')) ?: 'guru') . '.jpg',
            ]);

            $idLevel = (int) $request->input('id_level', 5);
            $idKelas = $idLevel == 4 ? (int) $request->input('id_kelas', 0) : 0;

            if ($activeTp && $activeSmt) {
                JabatanGuru::create([
                    'id_jabatan_guru' => $guru->id_guru . $activeTp->id_tp . $activeSmt->id_smt,
                    'id_guru'         => $guru->id_guru,
                    'id_jabatan'      => $idLevel,
                    'id_kelas'        => $idKelas,
                    'mapel_kelas'     => '[]',
                    'ekstra_kelas'    => '[]',
                    'id_tp'           => $activeTp->id_tp,
                    'id_smt'          => $activeSmt->id_smt,
                ]);

                if ($idLevel == 4 && $idKelas > 0) {
                    MasterKelas::where('id_kelas', $idKelas)->update(['guru_id' => $guru->id_guru]);
                }
            }
        });

        return back()->with('success', 'Data Guru dan Akun Pengawas berhasil ditambahkan.');
    }

    public function updateGuru(Request $request, int $id): RedirectResponse
    {
        $guru = MasterGuru::findOrFail($id);
        $request->validate([
            'nama_guru'     => 'required|string|max:100',
            'username'      => 'required|string|max:50|unique:users,username,' . $guru->id_user . '|unique:master_guru,username,' . $guru->id_guru . ',id_guru',
            'nip'           => 'nullable|string|max:30',
            'email'         => 'nullable|email|max:100',
            'no_hp'         => 'nullable|string|max:25',
            'jenis_kelamin' => 'nullable|in:L,P',
        ]);

        DB::transaction(function () use ($request, $guru) {
            $userData = [
                'first_name' => trim($request->input('nama_guru')),
                'username'   => trim($request->input('username')),
                'email'      => $request->input('email'),
            ];
            if ($request->filled('password')) {
                $userData['password'] = password_hash($request->input('password'), PASSWORD_BCRYPT);
            }
            User::where('id', $guru->id_user)->update($userData);

            $guruData = [
                'nama_guru'     => trim($request->input('nama_guru')),
                'nip'           => trim($request->input('nip')) ?: '-',
                'username'      => trim($request->input('username')),
                'email'         => $request->input('email'),
                'no_hp'         => $request->input('no_hp'),
                'jenis_kelamin' => $request->input('jenis_kelamin'),
            ];
            if ($request->filled('password')) {
                $guruData['password'] = $request->input('password');
            }
            $guru->update($guruData);
        });

        return back()->with('success', 'Profil Guru dan Akun Login berhasil diperbarui.');
    }

    public function editJabatanGuru(int $id): View
    {
        $guru = MasterGuru::with(['jabatan.level', 'jabatan.kelas'])->findOrFail($id);

        $activeTp = MasterTp::where('active', 1)->first() ?? MasterTp::first();
        $activeSmt = MasterSmt::where('active', 1)->first() ?? MasterSmt::first();

        $kelasList = MasterKelas::orderBy('nama_kelas', 'asc')->get();
        $mapelList = MasterMapel::where('status', 1)->orderBy('nama_mapel', 'asc')->get();
        $ekskulList = MasterEkstra::orderBy('nama_ekstra', 'asc')->get();
        $levels = LevelGuru::orderBy('id_level', 'asc')->get();

        $kelasMap = $kelasList->pluck('nama_kelas', 'id_kelas')->toArray();

        // Cari Jabatan semester sebelumnya untuk fitur "Salin Jabatan Sebelumnya"
        $smtBefore = $activeSmt?->id_smt == 2 ? MasterSmt::where('id_smt', 1)->first() : MasterSmt::where('id_smt', 2)->first();
        $tpBefore = $activeSmt?->id_smt == 2 ? $activeTp : MasterTp::where('id_tp', '<', $activeTp?->id_tp)->orderBy('id_tp', 'desc')->first();

        $jabatanBefore = null;
        if ($tpBefore && $smtBefore) {
            $jabatanBefore = JabatanGuru::with(['level', 'kelas'])
                ->where('id_guru', $id)
                ->where('id_tp', $tpBefore->id_tp)
                ->where('id_smt', $smtBefore->id_smt)
                ->first();
        }

        $jabatan = $guru->jabatan;
        $rawMapels = $jabatan?->parsed_mapel_kelas ?? [];

        $assignedMapels = [];
        if (is_array($rawMapels)) {
            foreach ($rawMapels as $rm) {
                if (empty($rm['id_mapel'])) continue;
                $kelasRaw = $rm['kelas_mapel'] ?? [];
                $normalizedKelas = [];
                if (is_array($kelasRaw)) {
                    foreach ($kelasRaw as $kr) {
                        if (is_string($kr)) {
                            $normalizedKelas[] = ['kelas' => $kr];
                        } elseif (is_array($kr) && isset($kr['kelas'])) {
                            $normalizedKelas[] = ['kelas' => (string)$kr['kelas']];
                        } elseif (is_array($kr) && isset($kr['id_kelas'])) {
                            $normalizedKelas[] = ['kelas' => (string)$kr['id_kelas']];
                        }
                    }
                }
                $assignedMapels[] = [
                    'id_mapel'    => (string)$rm['id_mapel'],
                    'nama_mapel'  => $rm['nama_mapel'] ?? '',
                    'kelas_mapel' => $normalizedKelas,
                ];
            }
        }

        $rawEkskul = $jabatan?->parsed_ekstra_kelas ?? [];
        $assignedEkskul = [];
        if (is_array($rawEkskul)) {
            foreach ($rawEkskul as $re) {
                if (empty($re['id_ekstra'])) continue;
                $kelasRaw = $re['kelas_ekstra'] ?? [];
                $normalizedKelas = [];
                if (is_array($kelasRaw)) {
                    foreach ($kelasRaw as $kr) {
                        if (is_string($kr)) {
                            $normalizedKelas[] = ['kelas' => $kr];
                        } elseif (is_array($kr) && isset($kr['kelas'])) {
                            $normalizedKelas[] = ['kelas' => (string)$kr['kelas']];
                        } elseif (is_array($kr) && isset($kr['id_kelas'])) {
                            $normalizedKelas[] = ['kelas' => (string)$kr['id_kelas']];
                        }
                    }
                }
                $assignedEkskul[] = [
                    'id_ekstra'    => (string)$re['id_ekstra'],
                    'nama_ekstra'  => $re['nama_ekstra'] ?? '',
                    'kelas_ekstra' => $normalizedKelas,
                ];
            }
        }

        return view('admin.master.guru_edit_jabatan', compact(
            'guru',
            'jabatan',
            'jabatanBefore',
            'tpBefore',
            'smtBefore',
            'assignedMapels',
            'assignedEkskul',
            'kelasList',
            'mapelList',
            'ekskulList',
            'levels',
            'kelasMap',
            'activeTp',
            'activeSmt'
        ));
    }

    public function updateJabatanGuru(Request $request, int $id): RedirectResponse
    {
        $guru = MasterGuru::findOrFail($id);
        $activeTp = MasterTp::where('active', 1)->first() ?? MasterTp::first();
        $activeSmt = MasterSmt::where('active', 1)->first() ?? MasterSmt::first();

        $idLevel = (int) $request->input('level', 5);
        $kelasWali = $idLevel == 4 ? (int) $request->input('kelas_wali', 0) : 0;

        // Process mapel_kelas
        $mapels = [];
        $selectedMapels = $request->input('mapel', []);
        if (is_array($selectedMapels)) {
            foreach ($selectedMapels as $mapelId) {
                if (empty($mapelId)) continue;
                $mapel = MasterMapel::find($mapelId);
                if (!$mapel) continue;

                $assignedClasses = $request->input("kelasmapel_{$mapelId}", []);
                if (!is_array($assignedClasses)) {
                    $assignedClasses = [];
                }

                $classesData = [];
                foreach ($assignedClasses as $klsId) {
                    if (!empty($klsId)) {
                        $classesData[] = ['kelas' => (string) $klsId];
                    }
                }

                $mapels[] = [
                    'id_mapel'    => (string) $mapelId,
                    'nama_mapel'  => $mapel->nama_mapel,
                    'kelas_mapel' => $classesData,
                ];
            }
        }

        // Process ekstra_kelas
        $ekstras = [];
        $selectedEkstra = $request->input('ekstra', []);
        if (is_array($selectedEkstra)) {
            foreach ($selectedEkstra as $ekstraId) {
                if (empty($ekstraId)) continue;
                $ekstra = MasterEkstra::find($ekstraId);
                if (!$ekstra) continue;

                $assignedClasses = $request->input("kelasekstra_{$ekstraId}", []);
                if (!is_array($assignedClasses)) {
                    $assignedClasses = [];
                }

                $classesData = [];
                foreach ($assignedClasses as $klsId) {
                    if (!empty($klsId)) {
                        $classesData[] = ['kelas' => (string) $klsId];
                    }
                }

                $ekstras[] = [
                    'id_ekstra'    => (string) $ekstraId,
                    'nama_ekstra'  => $ekstra->nama_ekstra,
                    'kelas_ekstra' => $classesData,
                ];
            }
        }

        $idJabatanGuru = $guru->id_guru . ($activeTp?->id_tp ?? 1) . ($activeSmt?->id_smt ?? 1);

        JabatanGuru::updateOrCreate(
            ['id_jabatan_guru' => $idJabatanGuru],
            [
                'id_guru'      => $guru->id_guru,
                'id_jabatan'   => $idLevel,
                'id_kelas'     => $kelasWali,
                'mapel_kelas'  => json_encode($mapels),
                'ekstra_kelas' => json_encode($ekstras),
                'id_tp'        => $activeTp?->id_tp ?? 1,
                'id_smt'       => $activeSmt?->id_smt ?? 1,
            ]
        );

        // Sync wali kelas in master_kelas
        if ($kelasWali > 0) {
            MasterKelas::where('guru_id', $guru->id_guru)->where('id_kelas', '!=', $kelasWali)->update(['guru_id' => null]);
            MasterKelas::where('id_kelas', $kelasWali)->update(['guru_id' => $guru->id_guru]);
        } else {
            MasterKelas::where('guru_id', $guru->id_guru)->update(['guru_id' => null]);
        }

        return back()->with('success', "Jabatan dan Penugasan Mengajar {$guru->nama_guru} berhasil diperbarui.");
    }

    public function copyJabatanGuru(Request $request, int $id): RedirectResponse
    {
        $guru = MasterGuru::findOrFail($id);
        $activeTp = MasterTp::where('active', 1)->first() ?? MasterTp::first();
        $activeSmt = MasterSmt::where('active', 1)->first() ?? MasterSmt::first();

        $smtBefore = $activeSmt?->id_smt == 2 ? MasterSmt::where('id_smt', 1)->first() : MasterSmt::where('id_smt', 2)->first();
        $tpBefore = $activeSmt?->id_smt == 2 ? $activeTp : MasterTp::where('id_tp', '<', $activeTp?->id_tp)->orderBy('id_tp', 'desc')->first();

        if (!$tpBefore || !$smtBefore) {
            return back()->with('error', 'Informasi tahun ajaran atau semester sebelumnya tidak ditemukan.');
        }

        $jabatanBefore = JabatanGuru::where('id_guru', $id)
            ->where('id_tp', $tpBefore->id_tp)
            ->where('id_smt', $smtBefore->id_smt)
            ->first();

        if (!$jabatanBefore) {
            return back()->with('error', 'Tidak ada data jabatan/mengajar pada semester sebelumnya.');
        }

        // Map classes between semesters: normalize class names (7.A -> 7a)
        $currentClasses = MasterKelas::all();
        $normalizedCurrentClasses = [];
        foreach ($currentClasses as $cls) {
            $clean = str_replace([' ', '.', '-', '_'], '', strtolower($cls->nama_kelas));
            $normalizedCurrentClasses[$clean] = $cls->id_kelas;
            $normalizedCurrentClasses['id_' . $cls->id_kelas] = $cls->id_kelas;
        }

        $previousClasses = MasterKelas::all();
        $prevClassMap = $previousClasses->pluck('nama_kelas', 'id_kelas')->toArray();

        // Copy Mapel Kelas
        $copiedMapels = [];
        $rawMapels = $jabatanBefore->parsed_mapel_kelas;
        foreach ($rawMapels as $m) {
            $newKelasList = [];
            foreach ($m['kelas_mapel'] ?? [] as $km) {
                $prevKlsId = is_array($km) ? ($km['kelas'] ?? null) : $km;
                if (!$prevKlsId) continue;

                $prevName = $prevClassMap[$prevKlsId] ?? '';
                $cleanPrev = str_replace([' ', '.', '-', '_'], '', strtolower($prevName));

                if (isset($normalizedCurrentClasses[$cleanPrev])) {
                    $newKelasList[] = ['kelas' => (string)$normalizedCurrentClasses[$cleanPrev]];
                } elseif (isset($normalizedCurrentClasses['id_' . $prevKlsId])) {
                    $newKelasList[] = ['kelas' => (string)$prevKlsId];
                }
            }

            if (!empty($newKelasList)) {
                $copiedMapels[] = [
                    'id_mapel' => (string)$m['id_mapel'],
                    'nama_mapel' => $m['nama_mapel'] ?? '',
                    'kelas_mapel' => $newKelasList,
                ];
            }
        }

        // Copy Ekstra Kelas
        $copiedEkstras = [];
        $rawEkstras = $jabatanBefore->parsed_ekstra_kelas;
        foreach ($rawEkstras as $e) {
            $newKelasList = [];
            foreach ($e['kelas_ekstra'] ?? [] as $ke) {
                $prevKlsId = is_array($ke) ? ($ke['kelas'] ?? null) : $ke;
                if (!$prevKlsId) continue;

                $prevName = $prevClassMap[$prevKlsId] ?? '';
                $cleanPrev = str_replace([' ', '.', '-', '_'], '', strtolower($prevName));

                if (isset($normalizedCurrentClasses[$cleanPrev])) {
                    $newKelasList[] = ['kelas' => (string)$normalizedCurrentClasses[$cleanPrev]];
                } elseif (isset($normalizedCurrentClasses['id_' . $prevKlsId])) {
                    $newKelasList[] = ['kelas' => (string)$prevKlsId];
                }
            }

            if (!empty($newKelasList)) {
                $copiedEkstras[] = [
                    'id_ekstra' => (string)$e['id_ekstra'],
                    'nama_ekstra' => $e['nama_ekstra'] ?? '',
                    'kelas_ekstra' => $newKelasList,
                ];
            }
        }

        // Copy Wali Kelas
        $newKelasWali = 0;
        if ($jabatanBefore->id_jabatan == 4 && $jabatanBefore->id_kelas) {
            $prevWaliName = $prevClassMap[$jabatanBefore->id_kelas] ?? '';
            $cleanPrevWali = str_replace([' ', '.', '-', '_'], '', strtolower($prevWaliName));
            if (isset($normalizedCurrentClasses[$cleanPrevWali])) {
                $newKelasWali = $normalizedCurrentClasses[$cleanPrevWali];
            } elseif (isset($normalizedCurrentClasses['id_' . $jabatanBefore->id_kelas])) {
                $newKelasWali = $jabatanBefore->id_kelas;
            }
        }

        $idJabatanGuru = $guru->id_guru . ($activeTp?->id_tp ?? 1) . ($activeSmt?->id_smt ?? 1);

        JabatanGuru::updateOrCreate(
            ['id_jabatan_guru' => $idJabatanGuru],
            [
                'id_guru'      => $guru->id_guru,
                'id_jabatan'   => $jabatanBefore->id_jabatan,
                'id_kelas'     => $newKelasWali,
                'mapel_kelas'  => json_encode($copiedMapels),
                'ekstra_kelas' => json_encode($copiedEkstras),
                'id_tp'        => $activeTp?->id_tp ?? 1,
                'id_smt'       => $activeSmt?->id_smt ?? 1,
            ]
        );

        if ($newKelasWali > 0) {
            MasterKelas::where('guru_id', $guru->id_guru)->where('id_kelas', '!=', $newKelasWali)->update(['guru_id' => null]);
            MasterKelas::where('id_kelas', $newKelasWali)->update(['guru_id' => $guru->id_guru]);
        } else {
            MasterKelas::where('guru_id', $guru->id_guru)->update(['guru_id' => null]);
        }

        return back()->with('success', 'Jabatan dan penugasan mengajar berhasil disalin dari semester sebelumnya.');
    }

    public function storeLevelGuru(Request $request): RedirectResponse
    {
        $request->validate([
            'level' => 'required|string|max:50',
        ]);

        LevelGuru::create([
            'level' => $request->input('level'),
        ]);

        return back()->with('success', 'Level Jabatan berhasil ditambahkan.');
    }

    public function toggleStatusGuru(int $id): RedirectResponse
    {
        $guru = MasterGuru::findOrFail($id);
        $user = User::find($guru->id_user);

        if ($user) {
            $user->active = $user->active == 1 ? 0 : 1;
            $user->save();
            $statusText = $user->active == 1 ? 'diaktifkan' : 'dinonaktifkan';
            return back()->with('success', "Status akun guru {$guru->nama_guru} berhasil {$statusText}.");
        }

        return back()->with('error', "Akun login pengguna untuk {$guru->nama_guru} tidak ditemukan.");
    }

    public function destroyGuru(int $id): RedirectResponse
    {
        $guru = MasterGuru::findOrFail($id);
        $messages = [];

        if (Schema::hasTable('cbt_bank_soal') && DB::table('cbt_bank_soal')->where('bank_guru_id', $id)->exists()) {
            $messages[] = 'Bank Soal (cbt_bank_soal)';
        }
        if (Schema::hasTable('cbt_pengawas') && DB::table('cbt_pengawas')->where('id_guru', 'like', "%{$id}%")->exists()) {
            $messages[] = 'Jadwal Pengawas (cbt_pengawas)';
        }
        if (Schema::hasTable('kelas_materi') && DB::table('kelas_materi')->where('id_guru', $id)->exists()) {
            $messages[] = 'Materi Pembelajaran (kelas_materi)';
        }
        if (Schema::hasTable('kelas_catatan_mapel') && DB::table('kelas_catatan_mapel')->where('id_guru', $id)->exists()) {
            $messages[] = 'Catatan Mapel (kelas_catatan_mapel)';
        }

        if (!empty($messages)) {
            return back()->with('error', "Data Guru {$guru->nama_guru} tidak dapat dihapus karena masih digunakan di: " . implode(', ', $messages) . '. Harap ubah relasi data terlebih dahulu.');
        }

        DB::transaction(function () use ($guru, $id) {
            MasterKelas::where('guru_id', $id)->update(['guru_id' => null]);
            JabatanGuru::where('id_guru', $id)->delete();
            if ($guru->id_user) {
                User::where('id', $guru->id_user)->delete();
            }
            $guru->delete();
        });

        return back()->with('success', "Data Guru {$guru->nama_guru} berhasil dihapus.");
    }

    public function bulkDestroyGuru(Request $request): RedirectResponse
    {
        $ids = $request->input('checked', []);
        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'Tidak ada data guru yang dipilih untuk dihapus.');
        }

        $inUse = [];
        $deletedCount = 0;

        foreach ($ids as $id) {
            $guru = MasterGuru::find($id);
            if (!$guru) continue;

            $used = false;
            if (Schema::hasTable('cbt_bank_soal') && DB::table('cbt_bank_soal')->where('bank_guru_id', $id)->exists()) {
                $used = true;
            }
            if (Schema::hasTable('cbt_pengawas') && DB::table('cbt_pengawas')->where('id_guru', 'like', "%{$id}%")->exists()) {
                $used = true;
            }
            if (Schema::hasTable('kelas_materi') && DB::table('kelas_materi')->where('id_guru', $id)->exists()) {
                $used = true;
            }

            if ($used) {
                $inUse[] = $guru->nama_guru;
            } else {
                DB::transaction(function () use ($guru, $id) {
                    MasterKelas::where('guru_id', $id)->update(['guru_id' => null]);
                    JabatanGuru::where('id_guru', $id)->delete();
                    if ($guru->id_user) {
                        User::where('id', $guru->id_user)->delete();
                    }
                    $guru->delete();
                });
                $deletedCount++;
            }
        }

        if (!empty($inUse)) {
            $warn = "Sebanyak {$deletedCount} data Guru berhasil dihapus. Guru berikut tidak dapat dihapus karena masih digunakan di Bank Soal / Pengawas: " . implode(', ', $inUse);
            return back()->with('warning', $warn);
        }

        return back()->with('success', "Sebanyak {$deletedCount} data Guru berhasil dihapus.");
    }

    public function downloadTemplateGuru()
    {
        $excelPath = public_path('templates/format_guru.xlsx');
        if (file_exists($excelPath)) {
            return response()->download($excelPath, 'format_guru.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="format_guru.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, ['No', 'Nama Guru', 'NIP', 'Kode Guru', 'Username', 'Password']);
            fputcsv($handle, ['1', 'Dra. Siti Aminah, M.Pd', '197508122000032001', 'SA', 'sitiaminah', 'guru123']);
            fputcsv($handle, ['2', 'Ahmad Fauzi, S.Pd', '198204152008011005', 'AF', 'ahmadfauzi', 'guru123']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importGuru(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $rows = $this->parseSpreadsheetRows($request->file('file'));

        if (empty($rows)) {
            return back()->with('error', 'Berkas tidak memuat data atau format kolom tidak dapat dibaca.');
        }

        $activeTp = MasterTp::where('active', 1)->first() ?? MasterTp::first();
        $activeSmt = MasterSmt::where('active', 1)->first() ?? MasterSmt::first();
        $count = 0;

        foreach ($rows as $r) {
            $nama = '';
            $nip = '-';
            $kode = '';
            $username = '';
            $password = '123456';

            if (isset($r[2]) && !empty($r[2]) && isset($r[5]) && !empty($r[5])) {
                // Sesuai template format_guru.xlsx (Col 2=Nama, Col 3=NIP, Col 4=Kode, Col 5=Username, Col 6=Password)
                $nama = trim($r[2]);
                $nip = trim($r[3] ?? '') ?: '-';
                $kode = trim($r[4] ?? '');
                $username = trim($r[5]);
                $password = trim($r[6] ?? '') ?: '123456';
            } elseif (isset($r[1]) && !empty($r[1]) && isset($r[4]) && !empty($r[4])) {
                // Varian 0-indexed (Col 1=Nama, Col 2=NIP, Col 3=Kode, Col 4=Username, Col 5=Password)
                $nama = trim($r[1]);
                $nip = trim($r[2] ?? '') ?: '-';
                $kode = trim($r[3] ?? '');
                $username = trim($r[4]);
                $password = trim($r[5] ?? '') ?: '123456';
            } elseif (isset($r[1]) && !empty($r[1])) {
                $nama = trim($r[1]);
                $nip = trim($r[2] ?? '') ?: '-';
                $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
                if (strlen($username) < 3) $username = 'guru' . rand(100, 999);
                $password = '123456';
            }

            if (in_array(strtolower($nama), ['nama guru', 'nama', 'name', 'nama lengkap'])) {
                continue;
            }

            if (!empty($nama) && !empty($username)) {
                DB::transaction(function () use ($nama, $nip, $kode, $username, $password, $activeTp, $activeSmt) {
                    $user = User::updateOrCreate(
                        ['username' => $username],
                        [
                            'password'   => password_hash($password, PASSWORD_BCRYPT),
                            'first_name' => $nama,
                            'active'     => 1,
                            'created_on' => time(),
                        ]
                    );
                    $user->groups()->syncWithoutDetaching([User::ROLE_GURU]);

                    $guru = MasterGuru::updateOrCreate(
                        ['username' => $username],
                        [
                            'id_user'   => $user->id,
                            'nama_guru' => $nama,
                            'nip'       => $nip,
                            'kode_guru' => $kode,
                            'password'  => $password,
                            'foto'      => 'uploads/profiles/' . ($nip !== '-' ? $nip : 'guru') . '.jpg',
                        ]
                    );

                    if ($activeTp && $activeSmt) {
                        $idJabatanGuru = $guru->id_guru . $activeTp->id_tp . $activeSmt->id_smt;
                        JabatanGuru::firstOrCreate(
                            ['id_jabatan_guru' => $idJabatanGuru],
                            [
                                'id_guru'      => $guru->id_guru,
                                'id_jabatan'   => 5, // Default Guru
                                'id_kelas'     => 0,
                                'mapel_kelas'  => '[]',
                                'ekstra_kelas' => '[]',
                                'id_tp'        => $activeTp->id_tp,
                                'id_smt'       => $activeSmt->id_smt,
                            ]
                        );
                    }
                });
                $count++;
            }
        }

        return back()->with('success', "Sebanyak {$count} data Guru berhasil diimport.");
    }

    // =========================================================================
    // 6. DATA SISWA (HIGH-PERFORMANCE, FILTERED, US1 FEATURE PARITY)
    // =========================================================================
    public function indexSiswa(Request $request)
    {
        $search         = $request->input('q') ?? $request->input('search');
        $kelasId        = $request->input('kelas_id') ?? $request->input('filter_kelas');
        $statusFilter   = $request->input('status') ?? $request->input('filter');
        $perPage        = (int) ($request->input('per_page') ?? $request->input('limit', 10));
        if ($perPage <= 0 || $perPage > 100) $perPage = 10;

        $academicService = app(\App\Services\AcademicYear\AcademicYearService::class);
        $allYears       = $academicService->getAllYears();
        $selectedYearId = $request->input('tahun_ajaran_id') ? (int) $request->input('tahun_ajaran_id') : ($academicService->getSelectedYear()?->id);

        $query = MasterSiswa::with(['kelasSiswa.kelas', 'rombelTahun', 'user']);

        // 1. Search Filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // 2. Academic Year & Kelas Filter
        if ($selectedYearId) {
            $rombelQuery = \App\Models\SiswaRombelTahun::where('tahun_ajaran_id', $selectedYearId);
            if ($kelasId) {
                $rombelQuery->where('kelas_id', $kelasId);
            }
            if ($statusFilter && in_array($statusFilter, ['aktif', 'lulus', 'pindah', 'keluar', '1', '2', '3', '4'])) {
                $stMap = ['1' => 'aktif', '2' => 'lulus', '3' => 'pindah', '4' => 'keluar'];
                $mapped = $stMap[$statusFilter] ?? $statusFilter;
                $rombelQuery->where('status', $mapped);
            }
            $siswaIdsInYear = $rombelQuery->pluck('siswa_id');
            if ($siswaIdsInYear->isNotEmpty() || $kelasId || $statusFilter) {
                $query->whereIn('id_siswa', $siswaIdsInYear);
            }
        } elseif ($kelasId) {
            $siswaIds = KelasSiswa::where('id_kelas', $kelasId)->pluck('id_siswa');
            $query->whereIn('id_siswa', $siswaIds);
        }

        // 3. User Active / Inactive Filter
        if ($statusFilter === 'nonaktif' || $statusFilter === '2') {
            $query->whereHas('user', function ($uq) {
                $uq->where('active', 0);
            });
        } elseif ($statusFilter === '1' || $statusFilter === 'aktif') {
            $query->whereHas('user', function ($uq) {
                $uq->where('active', 1);
            });
        }

        // Stats calculation
        $totalSiswa       = MasterSiswa::count();
        $totalLaki        = MasterSiswa::where('jenis_kelamin', 'L')->count();
        $totalPerempuan   = MasterSiswa::where('jenis_kelamin', 'P')->count();
        $totalBelumKelas  = MasterSiswa::whereDoesntHave('kelasSiswa')->count();
        $totalAktif       = \App\Models\SiswaRombelTahun::where('status', 'aktif')->count();

        $siswas = $query->orderBy('nama', 'asc')->paginate($perPage)->withQueryString();
        $kelasList = MasterKelas::orderBy('level_id', 'asc')->orderBy('nama_kelas', 'asc')->get();
        $mapelList = MasterMapel::where('status', 1)->orderBy('nama_mapel', 'asc')->get();

        if ($request->ajax() && $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $siswas,
                'stats'   => [
                    'total'       => $totalSiswa,
                    'laki'        => $totalLaki,
                    'perempuan'   => $totalPerempuan,
                    'belum_kelas' => $totalBelumKelas,
                ],
            ]);
        }

        return view('admin.master.siswa', compact(
            'siswas',
            'kelasList',
            'mapelList',
            'search',
            'kelasId',
            'statusFilter',
            'perPage',
            'allYears',
            'selectedYearId',
            'totalSiswa',
            'totalLaki',
            'totalPerempuan',
            'totalBelumKelas',
            'totalAktif'
        ));
    }

    public function storeSiswa(Request $request)
    {
        $request->validate([
            'nama'          => 'required|string|max:100',
            'username'      => 'required|string|max:50|unique:master_siswa,username|unique:users,username',
            'password'      => 'required|string|min:4',
            'nis'           => 'nullable|string|max:30',
            'nisn'          => 'nullable|string|max:30',
            'jenis_kelamin' => 'nullable|string|in:L,P',
            'agama'         => 'nullable|string|max:30',
            'id_kelas'      => 'required|integer',
            'kelas_awal'    => 'nullable|string|max:20',
            'tahun_masuk'   => 'nullable|string|max:20',
        ]);

        $mapelPilihan = $request->input('mapel_pilihan');
        if (is_array($mapelPilihan)) {
            $mapelPilihanJson = json_encode(array_values(array_filter($mapelPilihan)));
        } else {
            $mapelPilihanJson = null;
        }

        DB::transaction(function () use ($request, $mapelPilihanJson) {
            $kelasObj = MasterKelas::find($request->input('id_kelas'));
            $levelId = $kelasObj?->level_id ?? 10;
            $kelasAwal = $request->input('kelas_awal') ? (int) $request->input('kelas_awal') : (int) $levelId;

            $siswa = MasterSiswa::create([
                'nama'          => trim($request->input('nama')),
                'username'      => trim($request->input('username')),
                'password'      => trim($request->input('password')),
                'nis'           => trim($request->input('nis') ?? '') ?: '-',
                'nisn'          => trim($request->input('nisn') ?? '') ?: '-',
                'jenis_kelamin' => $request->input('jenis_kelamin') ?: 'L',
                'agama'         => $request->input('agama') ?: 'Islam',
                'kelas_awal'    => $kelasAwal ?: 10,
                'tahun_masuk'   => $request->input('tahun_masuk') ?: date('Y'),
                'nik'           => '-',
                'warga_negara'  => 'WNI',
                'uid'           => (string) \Illuminate\Support\Str::uuid(),
                'mapel_pilihan' => $mapelPilihanJson,
            ]);

            $activeTp = MasterTp::activeTp()?->id_tp ?? 1;
            $activeSmt = MasterSmt::activeSmt()?->id_smt ?? 1;

            KelasSiswa::updateOrCreate(
                ['id_siswa' => $siswa->id_siswa, 'id_tp' => $activeTp, 'id_smt' => $activeSmt],
                ['id_kelas' => $request->input('id_kelas')]
            );

            $activeTa = app(\App\Services\AcademicYear\AcademicYearService::class)->getActiveYear();
            if ($activeTa) {
                \App\Models\SiswaRombelTahun::updateOrCreate(
                    ['siswa_id' => $siswa->id_siswa, 'tahun_ajaran_id' => $activeTa->id],
                    ['kelas_id' => $request->input('id_kelas'), 'status' => 'aktif', 'keterangan' => 'Siswa Baru']
                );
            }

            $user = User::create([
                'username'   => $siswa->username,
                'password'   => password_hash($siswa->password, PASSWORD_BCRYPT),
                'first_name' => $siswa->nama,
                'active'     => 1,
                'created_on' => time(),
            ]);
            $user->groups()->attach(User::ROLE_SISWA);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['insert' => true, 'status' => true, 'text' => 'Siswa berhasil ditambahkan']);
        }

        return back()->with('success', 'Siswa baru berhasil ditambahkan dan siap mengikuti ujian.');
    }

    public function updateSiswa(Request $request, $id)
    {
        $siswa = MasterSiswa::findOrFail($id);

        $request->validate([
            'nama'          => 'required|string|max:100',
            'username'      => 'required|string|max:50|unique:master_siswa,username,' . $id . ',id_siswa',
            'nis'           => 'nullable|string|max:30',
            'nisn'          => 'nullable|string|max:30',
            'jenis_kelamin' => 'nullable|string|in:L,P',
            'agama'         => 'nullable|string|max:30',
            'id_kelas'      => 'required|integer',
            'password'      => 'nullable|string|min:4',
            'status'        => 'nullable|string|in:aktif,nonaktif,pindah,keluar,lulus,1,2,3,4',
        ]);

        $mapelPilihan = $request->input('mapel_pilihan');
        if (is_array($mapelPilihan)) {
            $mapelPilihanJson = json_encode(array_values(array_filter($mapelPilihan)));
        } else {
            $mapelPilihanJson = $siswa->mapel_pilihan;
        }

        DB::transaction(function () use ($request, $siswa, $mapelPilihanJson) {
            $updateData = [
                'nama'          => trim($request->input('nama')),
                'username'      => trim($request->input('username')),
                'nis'           => trim($request->input('nis') ?? '-'),
                'nisn'          => trim($request->input('nisn') ?? '-'),
                'jenis_kelamin' => $request->input('jenis_kelamin') ?: $siswa->jenis_kelamin,
                'agama'         => $request->input('agama') ?: $siswa->agama,
                'kelas_awal'    => $request->input('kelas_awal') ?: $siswa->kelas_awal,
                'tahun_masuk'   => $request->input('tahun_masuk') ?: $siswa->tahun_masuk,
                'mapel_pilihan' => $mapelPilihanJson,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = trim($request->input('password'));
            }

            $oldUsername = $siswa->username;
            $siswa->update($updateData);

            $activeTp = MasterTp::activeTp()?->id_tp ?? 1;
            $activeSmt = MasterSmt::activeSmt()?->id_smt ?? 1;

            KelasSiswa::updateOrCreate(
                ['id_siswa' => $siswa->id_siswa, 'id_tp' => $activeTp, 'id_smt' => $activeSmt],
                ['id_kelas' => $request->input('id_kelas')]
            );

            $st = $request->input('status', 'aktif');
            $stMap = ['1' => 'aktif', '2' => 'lulus', '3' => 'pindah', '4' => 'keluar'];
            $statusVal = $stMap[$st] ?? $st;

            $activeTa = app(\App\Services\AcademicYear\AcademicYearService::class)->getActiveYear();
            if ($activeTa) {
                \App\Models\SiswaRombelTahun::updateOrCreate(
                    ['siswa_id' => $siswa->id_siswa, 'tahun_ajaran_id' => $activeTa->id],
                    ['kelas_id' => $request->input('id_kelas'), 'status' => $statusVal]
                );
            }

            // Sync User login
            $user = User::where('username', $oldUsername)->first() ?? User::where('username', $siswa->username)->first();
            $isActive = !in_array($statusVal, ['nonaktif', 'pindah', 'keluar']);

            if ($user) {
                $userData = [
                    'username'   => $siswa->username,
                    'first_name' => $siswa->nama,
                    'active'     => $isActive ? 1 : 0,
                ];
                if ($request->filled('password')) {
                    $userData['password'] = password_hash($request->input('password'), PASSWORD_BCRYPT);
                }
                $user->update($userData);
            } else {
                $newUser = User::create([
                    'username'   => $siswa->username,
                    'password'   => password_hash($siswa->password ?? '123456', PASSWORD_BCRYPT),
                    'first_name' => $siswa->nama,
                    'active'     => $isActive ? 1 : 0,
                    'created_on' => time(),
                ]);
                $newUser->groups()->syncWithoutDetaching([User::ROLE_SISWA]);
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Data siswa {$siswa->nama} berhasil diperbarui"]);
        }

        return back()->with('success', "Data siswa {$siswa->nama} berhasil diperbarui.");
    }

    public function destroySiswa($id)
    {
        $siswa = MasterSiswa::findOrFail($id);

        DB::transaction(function () use ($siswa, $id) {
            KelasSiswa::where('id_siswa', $id)->delete();
            \App\Models\SiswaRombelTahun::where('siswa_id', $id)->delete();
            \App\Models\SiswaMapelPilihan::where('siswa_id', $id)->delete();

            if (Schema::hasTable('cbt_sesi_siswa')) {
                DB::table('cbt_sesi_siswa')->where('siswa_id', $id)->delete();
            }
            if (Schema::hasTable('cbt_nomor_peserta')) {
                DB::table('cbt_nomor_peserta')->where('id_siswa', $id)->delete();
            }

            User::where('username', $siswa->username)->delete();
            $siswa->delete();
        });

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Data siswa berhasil dihapus"]);
        }

        return back()->with('success', "Data siswa {$siswa->nama} berhasil dihapus.");
    }

    public function bulkActionSiswa(Request $request)
    {
        $ids = $request->input('checked', []);
        $action = $request->input('aksi') ?? $request->input('action');

        if (empty($ids) || !is_array($ids)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => false, 'message' => 'Tidak ada siswa yang dipilih'], 422);
            }
            return back()->with('error', 'Tidak ada data siswa yang dipilih.');
        }

        $activeTa = app(\App\Services\AcademicYear\AcademicYearService::class)->getActiveYear();
        $taId = $activeTa?->id;
        $count = 0;

        DB::transaction(function () use ($ids, $action, $taId, &$count) {
            foreach ($ids as $id) {
                $siswa = MasterSiswa::find($id);
                if (!$siswa) continue;

                if ($action === 'hapus') {
                    KelasSiswa::where('id_siswa', $id)->delete();
                    \App\Models\SiswaRombelTahun::where('siswa_id', $id)->delete();
                    \App\Models\SiswaMapelPilihan::where('siswa_id', $id)->delete();
                    User::where('username', $siswa->username)->delete();
                    $siswa->delete();
                } elseif (in_array($action, ['pindah', 'keluar', 'nonaktif'])) {
                    if ($taId) {
                        \App\Models\SiswaRombelTahun::where('siswa_id', $id)
                            ->where('tahun_ajaran_id', $taId)
                            ->update(['status' => $action]);
                    }
                    User::where('username', $siswa->username)->update(['active' => 0]);
                } elseif ($action === 'aktif') {
                    if ($taId) {
                        \App\Models\SiswaRombelTahun::where('siswa_id', $id)
                            ->where('tahun_ajaran_id', $taId)
                            ->update(['status' => 'aktif']);
                    }
                    User::where('username', $siswa->username)->update(['active' => 1]);
                }
                $count++;
            }
        });

        $actionMessages = [
            'hapus'    => 'Data siswa terpilih berhasil dihapus',
            'pindah'   => 'Siswa terpilih berhasil diatur status PINDAH',
            'keluar'   => 'Siswa terpilih berhasil diatur status KELUAR',
            'nonaktif' => 'Akun siswa terpilih berhasil dinonaktifkan',
            'aktif'    => 'Siswa terpilih berhasil diaktifkan kembali',
        ];

        $msg = $actionMessages[$action] ?? "Aksi {$action} berhasil diproses untuk {$count} siswa";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => true, 'total' => $count, 'message' => $msg]);
        }

        return back()->with('success', "Sebanyak {$count} {$msg}.");
    }

    public function resetPasswordSiswa(Request $request, $id)
    {
        $siswa = MasterSiswa::findOrFail($id);
        $newPassword = $request->input('password') ?: ($siswa->nisn !== '-' ? $siswa->nisn : '123456');

        DB::transaction(function () use ($siswa, $newPassword) {
            $siswa->update(['password' => $newPassword]);
            User::where('username', $siswa->username)->update([
                'password' => password_hash($newPassword, PASSWORD_BCRYPT)
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Password siswa {$siswa->nama} berhasil direset menjadi '{$newPassword}'"]);
        }

        return back()->with('success', "Password {$siswa->nama} berhasil direset menjadi '{$newPassword}'.");
    }

    public function downloadTemplateSiswa()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="format_siswa.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, ['No', 'Nama Siswa', 'NIS', 'NISN', 'Jenis Kelamin (L/P)', 'Agama', 'Kelas', 'Username', 'Password']);
            fputcsv($handle, ['1', 'Ahmad Rizky Pratama', '21001', '0051234561', 'L', 'Islam', 'X-1', '0051234561', '123456']);
            fputcsv($handle, ['2', 'Siti Nurhaliza', '21002', '0051234562', 'P', 'Islam', 'X-1', '0051234562', '123456']);
            fputcsv($handle, ['3', 'Budi Santoso', '21003', '0051234563', 'L', 'Kristen', 'X-2', '0051234563', '123456']);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportSiswa(Request $request, $id_kelas = null)
    {
        $targetKelasId = $id_kelas ?: $request->input('kelas_id');
        $query = MasterSiswa::with(['kelasSiswa.kelas', 'rombelTahun', 'user']);

        if ($targetKelasId) {
            $siswaIds = KelasSiswa::where('id_kelas', $targetKelasId)->pluck('id_siswa');
            $query->whereIn('id_siswa', $siswaIds);
            $kelas = MasterKelas::find($targetKelasId);
            $filename = 'data_siswa_' . ($kelas ? strtolower(str_replace(' ', '_', $kelas->nama_kelas)) : 'kelas') . '.csv';
        } else {
            $filename = 'data_seluruh_siswa.csv';
        }

        $siswas = $query->orderBy('nama', 'asc')->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($siswas) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, ['No', 'Nama Siswa', 'NIS', 'NISN', 'Jenis Kelamin', 'Agama', 'Kelas', 'Username', 'Password', 'Status']);

            $no = 1;
            foreach ($siswas as $s) {
                $namaKelas = $s->kelasSiswa->first()?->kelas->nama_kelas ?? '-';
                $status = $s->rombelTahun->first()?->status ?? ($s->user?->active ? 'Aktif' : 'Nonaktif');
                fputcsv($handle, [
                    $no++,
                    $s->nama,
                    $s->nis ?? '-',
                    $s->nisn ?? '-',
                    $s->jenis_kelamin ?? 'L',
                    $s->agama ?? 'Islam',
                    $namaKelas,
                    $s->username,
                    $s->password,
                    ucfirst($status)
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function ajaxListSiswa(Request $request)
    {
        $page = (int) $request->input('page', 1);
        $limit = (int) $request->input('limit', 10);
        $search = $request->input('search');
        $filter = $request->input('filter');
        $kelas = $request->input('kelas');

        $offset = ($page - 1) * $limit;

        $academicService = app(\App\Services\AcademicYear\AcademicYearService::class);
        $activeTa = $academicService->getActiveYear();
        $selectedYearId = $activeTa?->id;

        $query = MasterSiswa::with(['kelasSiswa.kelas', 'rombelTahun', 'user']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($filter == '1') { // Aktif
            $query->whereHas('user', fn($q) => $q->where('active', 1));
        } elseif ($filter == '2') { // Nonaktif
            $query->whereHas('user', fn($q) => $q->where('active', 0));
        }

        if ($kelas) {
            $siswaIds = KelasSiswa::where('id_kelas', $kelas)->pluck('id_siswa');
            $query->whereIn('id_siswa', $siswaIds);
        }

        $total = $query->count();
        $listData = $query->orderBy('nama', 'asc')->skip($offset)->take($limit)->get();

        $allMapels = MasterMapel::pluck('nama_mapel', 'id_mapel')->toArray();

        $lists = [];
        foreach ($listData as $s) {
            $klsName = $s->kelasSiswa->first()?->kelas->nama_kelas ?? '';
            $st = $s->rombelTahun->first()?->status ?? ($s->user?->active ? 'aktif' : 'nonaktif');
            $stCode = '1';
            if ($st === 'lulus') $stCode = '2';
            elseif ($st === 'pindah') $stCode = '3';
            elseif ($st === 'keluar') $stCode = '4';

            $mpLabels = [];
            if (!empty($s->mapel_pilihan)) {
                $decoded = is_array($s->mapel_pilihan) ? $s->mapel_pilihan : json_decode($s->mapel_pilihan, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $mpId) {
                        if (isset($allMapels[$mpId])) {
                            $mpLabels[] = $allMapels[$mpId];
                        }
                    }
                }
            }

            $lists[] = [
                'id_siswa'             => $s->id_siswa,
                'nama'                 => $s->nama,
                'nis'                  => $s->nis ?? '-',
                'nisn'                 => $s->nisn ?? '-',
                'jenis_kelamin'        => $s->jenis_kelamin ?? 'L',
                'agama'                => $s->agama ?? 'Islam',
                'id_kelas'             => $s->kelasSiswa->first()?->id_kelas,
                'nama_kelas'           => $klsName,
                'foto'                 => $s->foto && file_exists(public_path($s->foto)) ? asset($s->foto) : asset('assets/img/siswa_sm.png'),
                'username'             => $s->username,
                'password'             => $s->password,
                'status'               => $stCode,
                'active'               => (string) ($s->user?->active ?? 1),
                'mapel_pilihan_labels' => $mpLabels,
            ];
        }

        return response()->json([
            'lists'   => $lists,
            'total'   => $total,
            'pages'   => ceil($total / $limit),
            'search'  => $search,
            'perpage' => $limit,
            'filter'  => $filter,
        ]);
    }

    // =========================================================================
    // 7. DATA ALUMNI (SISWA LULUS / ARSIP)
    // =========================================================================
    public function indexAlumni(Request $request): View
    {
        $search = $request->input('q');
        $query = MasterSiswa::query()->whereDoesntHave('kelasSiswa');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $alumni = $query->orderBy('nama', 'asc')->paginate(10)->withQueryString();
        return view('admin.master.alumni', compact('alumni', 'search'));
    }

    // =========================================================================
    // 8. IMPORT DATA MAPEL & SISWA (CSV / EXCEL)
    // =========================================================================
    // IMPORT MAPEL
    // =========================================================================
    public function importMapel(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $rows = $this->parseSpreadsheetRows($request->file('file'));

        if (empty($rows)) {
            return back()->with('error', 'Berkas tidak memuat data atau format kolom tidak dapat dibaca.');
        }

        $count = 0;
        $maxUrutan = MasterMapel::max('urutan_tampil') ?? 0;

        foreach ($rows as $r) {
            // Sesuai template format_mapel.xlsx:
            // Kolom A (0): No
            // Kolom B (1): Nama Mapel
            // Kolom C (2): Kode Mapel
            // Kolom D (3): Kelompok (opsional)
            // Kolom E (4): Urutan Tampil (opsional)
            $nama = '';
            $kode = '';
            $kelompok = '-';
            $urutan = null;

            if (isset($r[1]) && isset($r[2]) && !empty($r[1]) && !empty($r[2])) {
                $nama = trim($r[1]);
                $kode = strtoupper(trim($r[2]));
                $kelompok = isset($r[3]) && !empty($r[3]) ? trim($r[3]) : '-';
                $urutan = isset($r[4]) && is_numeric($r[4]) ? (int) $r[4] : null;
            } elseif (isset($r[0]) && isset($r[1]) && !empty($r[0]) && !empty($r[1])) {
                if (strlen($r[0]) <= 20 && strlen($r[1]) > strlen($r[0])) {
                    $kode = strtoupper(trim($r[0]));
                    $nama = trim($r[1]);
                } else {
                    $nama = trim($r[0]);
                    $kode = strtoupper(trim($r[1]));
                }
                $kelompok = isset($r[2]) && !empty($r[2]) ? trim($r[2]) : '-';
            }

            if (!empty($nama) && !empty($kode)) {
                $maxUrutan++;
                MasterMapel::updateOrCreate(
                    ['kode' => $kode],
                    [
                        'nama_mapel'    => $nama,
                        'kelompok'      => $kelompok,
                        'urutan_tampil' => $urutan ?? $maxUrutan,
                        'status'        => 1,
                        'deletable'     => 1,
                    ]
                );
                $count++;
            }
        }

        return back()->with('success', "Sebanyak {$count} data Mata Pelajaran berhasil diimport.");
    }

    public function importSiswa(Request $request): RedirectResponse
    {
        $request->validate([
            'file'     => 'required|file|max:10240',
            'id_kelas' => 'nullable|integer',
        ]);

        $rows = $this->parseSpreadsheetRows($request->file('file'));

        if (empty($rows)) {
            return back()->with('error', 'Berkas tidak memuat data atau format kolom tidak dapat dibaca.');
        }

        $activeTp = MasterTp::activeTp()?->id_tp ?? 1;
        $activeSmt = MasterSmt::activeSmt()?->id_smt ?? 1;
        $activeTa = app(\App\Services\AcademicYear\AcademicYearService::class)->getActiveYear();
        $taId = $activeTa?->id;

        $kelasMapping = MasterKelas::pluck('id_kelas', 'nama_kelas')->mapWithKeys(function ($val, $key) {
            return [strtolower(trim($key)) => $val];
        })->toArray();

        $defaultKelasId = $request->input('id_kelas');
        $count = 0;

        foreach ($rows as $r) {
            $col0 = strtolower(trim($r[0] ?? ''));
            $col1 = strtolower(trim($r[1] ?? ''));
            if (in_array($col0, ['no', 'nama', 'nama siswa', 'name']) || in_array($col1, ['nama', 'nama siswa', 'name'])) {
                continue; // Skip header row
            }

            $nama = '';
            $nis = '-';
            $nisn = '-';
            $gender = 'L';
            $agama = 'Islam';
            $kelasNama = '';
            $username = '';
            $password = '123456';

            if (isset($r[1]) && !empty($r[1]) && (isset($r[7]) || isset($r[3]))) {
                if (isset($r[7]) && !empty($r[7])) {
                    // Standard template with No column: [0:No, 1:Nama, 2:NIS, 3:NISN, 4:Gender, 5:Agama, 6:Kelas, 7:Username, 8:Password]
                    $nama = trim($r[1]);
                    $nis = trim($r[2] ?? '') ?: '-';
                    $nisn = trim($r[3] ?? '') ?: '-';
                    $gender = strtoupper(substr(trim($r[4] ?? 'L'), 0, 1)) === 'P' ? 'P' : 'L';
                    $agama = trim($r[5] ?? '') ?: 'Islam';
                    $kelasNama = trim($r[6] ?? '');
                    $username = trim($r[7]);
                    $password = trim($r[8] ?? '') ?: '123456';
                } else {
                    // Format: 0: Nama, 1: NIS, 2: NISN, 3: Username, 4: Password, 5: Kelas
                    $nama = trim($r[0]);
                    $nis = trim($r[1] ?? '') ?: '-';
                    $nisn = trim($r[2] ?? '') ?: '-';
                    $username = trim($r[3] ?? '') ?: ($nisn !== '-' ? $nisn : strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama)));
                    $password = trim($r[4] ?? '') ?: '123456';
                    $kelasNama = trim($r[5] ?? '');
                }
            } elseif (isset($r[0]) && !empty($r[0])) {
                $nama = trim($r[0]);
                $nisn = trim($r[1] ?? '') ?: '-';
                $username = trim($r[2] ?? '') ?: ($nisn !== '-' ? $nisn : strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama)));
                $password = trim($r[3] ?? '') ?: '123456';
                $kelasNama = trim($r[4] ?? '');
            }

            if (empty($nama)) continue;
            if (empty($username)) {
                $username = $nisn !== '-' ? $nisn : strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
                if (strlen($username) < 3) $username = 'siswa' . rand(1000, 9999);
            }

            // Resolve target class
            $targetKelas = $defaultKelasId;
            if (!empty($kelasNama)) {
                $kKey = strtolower($kelasNama);
                if (isset($kelasMapping[$kKey])) {
                    $targetKelas = $kelasMapping[$kKey];
                } else {
                    $klsObj = MasterKelas::firstOrCreate(['nama_kelas' => $kelasNama], ['level_id' => 10, 'jurusan_id' => 1]);
                    $targetKelas = $klsObj->id_kelas;
                    $kelasMapping[$kKey] = $targetKelas;
                }
            }
            if (!$targetKelas) {
                $targetKelas = MasterKelas::first()?->id_kelas ?? 1;
            }

            DB::transaction(function () use ($nama, $nis, $nisn, $gender, $agama, $username, $password, $targetKelas, $activeTp, $activeSmt, $taId) {
                $targetKlsObj = MasterKelas::find($targetKelas);
                $levelId = $targetKlsObj?->level_id ?? 10;

                $siswa = MasterSiswa::updateOrCreate(
                    ['username' => $username],
                    [
                        'nama'          => $nama,
                        'nis'           => $nis,
                        'nisn'          => $nisn,
                        'jenis_kelamin' => $gender,
                        'agama'         => $agama,
                        'password'      => $password,
                        'kelas_awal'    => (int) $levelId ?: 10,
                        'nik'           => '-',
                        'warga_negara'  => 'WNI',
                        'uid'           => (string) \Illuminate\Support\Str::uuid(),
                    ]
                );

                KelasSiswa::updateOrCreate(
                    ['id_siswa' => $siswa->id_siswa, 'id_tp' => $activeTp, 'id_smt' => $activeSmt],
                    ['id_kelas' => $targetKelas]
                );

                if ($taId) {
                    \App\Models\SiswaRombelTahun::updateOrCreate(
                        ['siswa_id' => $siswa->id_siswa, 'tahun_ajaran_id' => $taId],
                        ['kelas_id' => $targetKelas, 'status' => 'aktif', 'keterangan' => 'Import Siswa']
                    );
                }

                $user = User::updateOrCreate(
                    ['username' => $username],
                    [
                        'password'   => password_hash($password, PASSWORD_BCRYPT),
                        'first_name' => $nama,
                        'active'     => 1,
                        'created_on' => time(),
                    ]
                );
                $user->groups()->syncWithoutDetaching([User::ROLE_SISWA]);
            });

            $count++;
        }

        return back()->with('success', "Sebanyak {$count} data Siswa berhasil diimport ke rombel kelas.");
    }

    /**
     * Helper privat untuk membaca file spreadsheet (.xlsx, .xls, .csv, .txt) menjadi array baris data.
     */
    private function parseSpreadsheetRows($file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();
        $rows = [];

        if (in_array($ext, ['csv', 'txt'])) {
            $handle = fopen($path, 'r');
            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = str_contains($firstLine, ';') ? ';' : ',';

            $isFirst = true;
            while (($data = fgetcsv($handle, 2000, $delimiter)) !== false) {
                if ($isFirst) {
                    $isFirst = false;
                    continue; // Lewati header
                }
                if (!empty($data)) {
                    $rows[] = array_map('trim', $data);
                }
            }
            fclose($handle);
        } elseif (in_array($ext, ['xlsx', 'xls'])) {
            $zip = new ZipArchive();
            if ($zip->open($path) === true) {
                $sharedStrings = [];
                $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
                if ($sharedStringsXml) {
                    $xml = simplexml_load_string($sharedStringsXml);
                    foreach ($xml->si as $si) {
                        if (isset($si->t)) {
                            $sharedStrings[] = (string) $si->t;
                        } elseif (isset($si->r)) {
                            $text = '';
                            foreach ($si->r as $r) {
                                $text .= (string) $r->t;
                            }
                            $sharedStrings[] = $text;
                        } else {
                            $sharedStrings[] = '';
                        }
                    }
                }

                $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
                if ($sheetXml) {
                    $xmlSheet = simplexml_load_string($sheetXml);
                    $isFirstRow = true;
                    if (isset($xmlSheet->sheetData->row)) {
                        foreach ($xmlSheet->sheetData->row as $row) {
                            if ($isFirstRow) {
                                $isFirstRow = false;
                                continue; // Lewati header
                            }
                            $rowData = [];
                            $currentIndex = 0;
                            foreach ($row->c as $c) {
                                $cellRef = (string) $c['r'];
                                preg_match('/([A-Z]+)(\d+)/', $cellRef, $matches);
                                $colLetters = $matches[1] ?? 'A';
                                $colIndex = 0;
                                for ($i = 0; $i < strlen($colLetters); $i++) {
                                    $colIndex = $colIndex * 26 + (ord($colLetters[$i]) - ord('A') + 1);
                                }
                                $colIndex -= 1;

                                while ($currentIndex < $colIndex) {
                                    $rowData[] = '';
                                    $currentIndex++;
                                }

                                $type = (string) $c['t'];
                                $val = isset($c->v) ? (string) $c->v : '';
                                if ($type === 's' && isset($sharedStrings[(int) $val])) {
                                    $val = $sharedStrings[(int) $val];
                                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                                    $val = (string) $c->is->t;
                                }

                                $rowData[] = trim($val);
                                $currentIndex++;
                            }
                            if (!empty(array_filter($rowData))) {
                                $rows[] = $rowData;
                            }
                        }
                    }
                }
                $zip->close();
            }
        }

        return $rows;
    }
}
