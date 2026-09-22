<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelasSiswa;
use App\Models\MasterGuru;
use App\Models\MasterJurusan;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\MasterSiswa;
use App\Models\MasterSmt;
use App\Models\MasterTp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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
    public function indexJurusan(): View
    {
        $jurusanList = MasterJurusan::orderBy('id_jurusan', 'asc')->get();
        return view('admin.master.jurusan', compact('jurusanList'));
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

    // =========================================================================
    // 3. KELAS & ROMBEL
    // =========================================================================
    public function indexKelas(): View
    {
        $kelasList = MasterKelas::with(['jurusan'])->orderBy('level_id', 'asc')->orderBy('nama_kelas', 'asc')->get();
        $jurusanList = MasterJurusan::all();

        return view('admin.master.kelas', compact('kelasList', 'jurusanList'));
    }

    public function storeKelas(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:50',
            'kode_kelas' => 'required|string|max:20',
            'level_id'   => 'required|integer',
        ]);

        MasterKelas::create($request->only('nama_kelas', 'kode_kelas', 'level_id', 'jurusan_id'));
        return back()->with('success', 'Kelas / Rombel berhasil ditambahkan.');
    }

    // =========================================================================
    // 4. MATA PELAJARAN
    // =========================================================================
    public function indexMapel(): View
    {
        $mapelList = MasterMapel::orderBy('nama_mapel', 'asc')->get();
        return view('admin.master.mapel', compact('mapelList'));
    }

    public function storeMapel(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_mapel' => 'required|string|max:100',
            'kode'       => 'required|string|max:20',
        ]);

        MasterMapel::create($request->only('nama_mapel', 'kode', 'urutan_tampil'));
        return back()->with('success', 'Mata Pelajaran berhasil ditambahkan.');
    }

    // =========================================================================
    // 5. DATA GURU & STAF
    // =========================================================================
    public function indexGuru(): View
    {
        $guruList = MasterGuru::with('user')->orderBy('nama_guru', 'asc')->paginate(20);
        return view('admin.master.guru', compact('guruList'));
    }

    public function storeGuru(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_guru' => 'required|string|max:100',
            'username'  => 'required|string|max:50|unique:users,username',
            'password'  => 'required|string|min:4',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'username'   => $request->input('username'),
                'password'   => password_hash($request->input('password'), PASSWORD_BCRYPT),
                'first_name' => $request->input('nama_guru'),
                'active'     => 1,
                'created_on' => time(),
            ]);
            $user->groups()->attach(User::ROLE_GURU);

            MasterGuru::create([
                'id_user'   => $user->id,
                'nama_guru' => $request->input('nama_guru'),
                'nip'       => $request->input('nip') ?? '-',
                'username'  => $request->input('username'),
                'password'  => $request->input('password'),
            ]);
        });

        return back()->with('success', 'Data Guru dan Akun Pengawas berhasil dibuat.');
    }

    // =========================================================================
    // 6. DATA SISWA (HIGH-CONCURRENCY PAGINATED & FILTERED)
    // =========================================================================
    public function indexSiswa(Request $request): View
    {
        $search         = $request->input('q');
        $kelasId        = $request->input('kelas_id');
        $academicService = app(\App\Services\AcademicYear\AcademicYearService::class);
        $allYears       = $academicService->getAllYears();
        $selectedYearId = $request->input('tahun_ajaran_id') ? (int) $request->input('tahun_ajaran_id') : ($academicService->getSelectedYear()?->id);

        $query = MasterSiswa::with(['kelasSiswa.kelas', 'rombelTahun']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($selectedYearId) {
            $rombelQuery = \App\Models\SiswaRombelTahun::where('tahun_ajaran_id', $selectedYearId);
            if ($kelasId) {
                $rombelQuery->where('kelas_id', $kelasId);
            }
            $siswaIdsInYear = $rombelQuery->pluck('siswa_id');
            if ($siswaIdsInYear->isNotEmpty()) {
                $query->whereIn('id_siswa', $siswaIdsInYear);
            } elseif ($kelasId) {
                $siswaIds = KelasSiswa::where('id_kelas', $kelasId)->pluck('id_siswa');
                $query->whereIn('id_siswa', $siswaIds);
            }
        } elseif ($kelasId) {
            $siswaIds = KelasSiswa::where('id_kelas', $kelasId)->pluck('id_siswa');
            $query->whereIn('id_siswa', $siswaIds);
        }

        $siswas = $query->orderBy('nama', 'asc')->paginate(25)->withQueryString();
        $kelasList = MasterKelas::orderBy('nama_kelas', 'asc')->get();

        return view('admin.master.siswa', compact('siswas', 'kelasList', 'search', 'kelasId', 'allYears', 'selectedYearId'));
    }

    public function storeSiswa(Request $request): RedirectResponse
    {
        $request->validate([
            'nama'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:master_siswa,username',
            'password' => 'required|string|min:4',
            'nisn'     => 'nullable|string|max:20',
            'id_kelas' => 'required|integer',
        ]);

        DB::transaction(function () use ($request) {
            $siswa = MasterSiswa::create([
                'nama'     => $request->input('nama'),
                'username' => $request->input('username'),
                'password' => $request->input('password'),
                'nisn'     => $request->input('nisn') ?? '-',
                'nis'      => $request->input('nis') ?? '-',
            ]);

            KelasSiswa::create([
                'id_siswa' => $siswa->id_siswa,
                'id_kelas' => $request->input('id_kelas'),
                'id_tp'    => MasterTp::activeTp()?->id_tp ?? 1,
                'id_smt'   => MasterSmt::activeSmt()?->id_smt ?? 1,
            ]);

            // Sinkronkan ke tabel siswa_rombel_tahun
            $activeTa = app(\App\Services\AcademicYear\AcademicYearService::class)->getActiveYear();
            if ($activeTa) {
                \App\Models\SiswaRombelTahun::updateOrCreate(
                    ['siswa_id' => $siswa->id_siswa, 'tahun_ajaran_id' => $activeTa->id],
                    ['kelas_id' => $request->input('id_kelas'), 'status' => 'aktif', 'keterangan' => 'Siswa Baru']
                );
            }

            // Sinkronkan ke tabel users
            $user = User::create([
                'username'   => $siswa->username,
                'password'   => password_hash($siswa->password, PASSWORD_BCRYPT),
                'first_name' => $siswa->nama,
                'active'     => 1,
                'created_on' => time(),
            ]);
            $user->groups()->attach(User::ROLE_SISWA);
        });

        return back()->with('success', 'Siswa baru berhasil ditambahkan dan siap mengikuti ujian.');
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

        $alumni = $query->orderBy('nama', 'asc')->paginate(25)->withQueryString();
        return view('admin.master.alumni', compact('alumni', 'search'));
    }

    // =========================================================================
    // 8. IMPORT DATA MAPEL & SISWA (CSV / EXCEL)
    // =========================================================================
    public function importMapel(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $path = $request->file('file')->getRealPath();
        $file = fopen($path, 'r');
        $count = 0;
        
        // Skip header
        fgetcsv($file);
        while (($row = fgetcsv($file, 1000, ',')) !== false) {
            if (!empty($row[0]) && !empty($row[1])) {
                MasterMapel::updateOrCreate(
                    ['kode' => trim($row[0])],
                    ['nama_mapel' => trim($row[1]), 'urutan_tampil' => $count + 1]
                );
                $count++;
            }
        }
        fclose($file);

        return back()->with('success', "Sebanyak {$count} data Mata Pelajaran berhasil diimport.");
    }

    public function importSiswa(Request $request): RedirectResponse
    {
        $request->validate([
            'file'     => 'required|file|mimes:csv,txt',
            'id_kelas' => 'required|integer',
        ]);

        $path = $request->file('file')->getRealPath();
        $file = fopen($path, 'r');
        $idKelas = (int) $request->input('id_kelas');
        $count = 0;

        $activeTp = MasterTp::activeTp()?->id_tp ?? 1;
        $activeSmt = MasterSmt::activeSmt()?->id_smt ?? 1;

        // Skip header
        fgetcsv($file);
        while (($row = fgetcsv($file, 1000, ',')) !== false) {
            if (!empty($row[0]) && !empty($row[1])) {
                $nama = trim($row[0]);
                $nisn = trim($row[1]);
                $username = !empty($row[2]) ? trim($row[2]) : $nisn;
                $password = !empty($row[3]) ? trim($row[3]) : '123456';

                DB::transaction(function () use ($nama, $nisn, $username, $password, $idKelas, $activeTp, $activeSmt) {
                    $siswa = MasterSiswa::updateOrCreate(
                        ['username' => $username],
                        ['nama' => $nama, 'nisn' => $nisn, 'password' => $password]
                    );

                    KelasSiswa::updateOrCreate(
                        ['id_siswa' => $siswa->id_siswa, 'id_tp' => $activeTp, 'id_smt' => $activeSmt],
                        ['id_kelas' => $idKelas]
                    );

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
        }
        fclose($file);

        return back()->with('success', "Sebanyak {$count} data Siswa berhasil diimport ke rombel kelas.");
    }
}
