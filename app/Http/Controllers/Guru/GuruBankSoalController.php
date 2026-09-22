<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CbtBankSoal;
use App\Models\CbtSoal;
use App\Models\MasterGuru;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\MasterTp;
use App\Models\MasterSmt;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GuruBankSoalController extends Controller
{
    /**
     * Dapatkan data model MasterGuru untuk pengguna yang sedang aktif.
     */
    protected function getGuru(): ?MasterGuru
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        return MasterGuru::where('id_user', $user->id)
            ->orWhere('username', $user->username)
            ->first();
    }

    /**
     * Halaman Utama Bank Soal (Garuda CBT Authentic Parity: type=0&mode=1).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $guru = $this->getGuru();
        $isAdmin = $user && ($user->isAdmin() || $user->role === 'admin');

        // Parameter mode tampilan: 1 = Table / List (default), 2 = Grid / Cards
        $mode = (string) $request->input('mode', '1');
        if (!in_array($mode, ['1', '2'])) {
            $mode = '1';
        }

        // Parameter filter: 0 = Semua, 1 = Guru (admin only), 2 = Mapel, 3 = Level
        $type = (string) $request->input('type', '0');
        $idFilter = $request->input('id');

        // Tahun Pelajaran & Semester Aktif
        $tp_active = MasterTp::activeTp() ?? MasterTp::orderBy('id_tp', 'desc')->first();
        $smt_active = MasterSmt::activeSmt() ?? MasterSmt::first();
        $tpList = MasterTp::orderBy('id_tp', 'desc')->get();
        $smtList = MasterSmt::all();

        // Pengaturan Sekolah & Jenjang
        $setting = Setting::first() ?? new Setting();
        $jenjang = (int) ($setting->jenjang ?? 3);
        if ($jenjang === 1) {
            $levels = ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'];
        } elseif ($jenjang === 2) {
            $levels = ['7' => '7', '8' => '8', '9' => '9'];
        } else {
            $levels = ['10' => '10', '11' => '11', '12' => '12'];
        }

        // Pilihan Filter
        if ($isAdmin) {
            $filters = ['0' => 'Semua', '1' => 'Guru', '2' => 'Mapel', '3' => 'Level'];
        } else {
            $filters = ['0' => 'Semua', '2' => 'Mapel', '3' => 'Level'];
        }

        // Daftar Guru & Mapel untuk Dropdown
        $guruList = MasterGuru::orderBy('nama_guru', 'asc')->get();
        $gurus = $guruList->pluck('nama_guru', 'id_guru')->toArray();

        $mapelList = MasterMapel::orderBy('nama_mapel', 'asc')->get();
        $mapels = $mapelList->pluck('nama_mapel', 'id_mapel')->toArray();

        // Daftar Kelas untuk TP/SMT Aktif
        $kelasList = MasterKelas::where('id_tp', $tp_active?->id_tp ?? 1)
            ->where('id_smt', $smt_active?->id_smt ?? 1)
            ->orderBy('level_id', 'asc')
            ->orderBy('nama_kelas', 'asc')
            ->get();

        if ($kelasList->isEmpty()) {
            $kelasList = MasterKelas::orderBy('level_id', 'asc')->orderBy('nama_kelas', 'asc')->get();
        }

        $kelasById = $kelasList->keyBy('id_kelas');

        // Query Bank Soal
        $query = CbtBankSoal::with(['mapel', 'guru']);

        // Jika Guru (bukan Admin), batasi ke bank soal miliknya
        if (!$isAdmin && $guru) {
            $query->where('bank_guru_id', $guru->id_guru);
        }

        // Terapkan Filter
        if ($type === '1' && !empty($idFilter)) {
            $query->where('bank_guru_id', $idFilter);
        } elseif ($type === '2' && !empty($idFilter)) {
            $query->where('bank_mapel_id', $idFilter);
        } elseif ($type === '3' && !empty($idFilter)) {
            $query->where('bank_level', $idFilter);
        }

        // Ambil data bank soal terurut
        $allBanksFound = $query->orderBy('bank_level', 'asc')
            ->orderBy('bank_kode', 'asc')
            ->orderBy('id_bank', 'desc')
            ->get();

        // Kelompokkan per TP dan SMT
        $banksGrouped = [];
        foreach ($allBanksFound as $b) {
            $tpId = (int) ($b->id_tp ?? 0);
            $smtId = (int) ($b->id_smt ?? 0);
            $banksGrouped[$tpId][$smtId][] = $b;
        }

        // Bank untuk TP & SMT Aktif
        $activeTpId = $tp_active?->id_tp ?? 0;
        $activeSmtId = $smt_active?->id_smt ?? 0;
        $banksToday = $banksGrouped[$activeTpId][$activeSmtId] ?? [];

        // Jika kosong pada semester aktif tapi ada data di TP/SMT 0/lainnya, fallback agar tidak kosong tak terduga
        if (empty($banksToday) && !empty($allBanksFound)) {
            $banksToday = $allBanksFound->values()->all();
        }

        // Status penggunaan dan total butir soal
        $allBankIds = $allBanksFound->pluck('id_bank')->toArray();
        $scheduledBankIds = [];
        $studentUsedBankIds = [];
        $soalCounts = [];

        if (!empty($allBankIds)) {
            $scheduledBankIds = DB::table('cbt_jadwal')
                ->where('status', '1')
                ->whereIn('id_bank', $allBankIds)
                ->distinct()
                ->pluck('id_bank')
                ->flip()
                ->toArray();

            $studentUsedBankIds = DB::table('cbt_siswa')
                ->whereIn('id_bank', $allBankIds)
                ->distinct()
                ->pluck('id_bank')
                ->flip()
                ->toArray();

            $soalCounts = DB::table('cbt_soal')
                ->whereIn('bank_id', $allBankIds)
                ->selectRaw('bank_id, count(*) as count')
                ->groupBy('bank_id')
                ->pluck('count', 'bank_id')
                ->toArray();
        }

        // Tambahkan metadata status ke setiap bank hari ini
        foreach ($banksToday as $b) {
            $b->total_soal = $soalCounts[$b->id_bank] ?? 0;
            $b->digunakan = isset($scheduledBankIds[$b->id_bank]) ? 1 : 0;
            $b->terpakai = isset($studentUsedBankIds[$b->id_bank]);

            // Hitung status warna (Kode Warna Garuda CBT)
            if ($b->digunakan > 0) {
                if ($b->terpakai) {
                    $b->status_color = 'maroon'; // Digunakan siswa
                    $b->status_badge = 'bg-rose-600 text-white';
                    $b->icon_color = 'text-rose-600';
                    $b->can_edit = false;
                    $b->can_delete = false;
                } else {
                    $b->status_color = 'yellow'; // Digunakan jadwal
                    $b->status_badge = 'bg-amber-500 text-white';
                    $b->icon_color = 'text-amber-500';
                    $b->can_edit = true;
                    $b->can_delete = false;
                }
            } else {
                $b->status_color = 'gray'; // Tidak digunakan (bisa dihapus)
                $b->status_badge = 'bg-slate-400 text-white';
                $b->icon_color = 'text-slate-400';
                $b->can_edit = true;
                $b->can_delete = true;
            }

            // Ekstrak nama-nama kelas
            $kelasNames = [];
            foreach ($b->kelas_ids as $kId) {
                if (isset($kelasById[$kId])) {
                    $kelasNames[] = $kelasById[$kId]->nama_kelas;
                }
            }
            $b->kelas_names = $kelasNames;
        }

        // Data semua bank soal untuk modal "Copy Bank Soal"
        $allBanksForCopy = CbtBankSoal::with(['mapel', 'guru'])
            ->orderBy('id_bank', 'desc')
            ->get();

        return view('guru.bank_soal.index', compact(
            'user',
            'guru',
            'isAdmin',
            'mode',
            'type',
            'idFilter',
            'filters',
            'gurus',
            'guruList',
            'mapels',
            'mapelList',
            'levels',
            'kelasList',
            'kelasById',
            'tp_active',
            'smt_active',
            'tpList',
            'smtList',
            'setting',
            'banksToday',
            'allBanksForCopy'
        ));
    }

    /**
     * Halaman Buat Bank Soal Baru (Garuda CBT Parity: /cbtbanksoal/addBank).
     */
    public function addBank(Request $request): View
    {
        $user = Auth::user();
        $guru = $this->getGuru();
        $isAdmin = $user && ($user->isAdmin() || $user->role === 'admin');

        $tp_active = MasterTp::activeTp() ?? MasterTp::orderBy('id_tp', 'desc')->first();
        $smt_active = MasterSmt::activeSmt() ?? MasterSmt::first();

        $setting = Setting::first() ?? new Setting();
        $jenjang = (int) ($setting->jenjang ?? 3);
        if ($jenjang === 1) {
            $levels = ['1', '2', '3', '4', '5', '6'];
        } elseif ($jenjang === 2) {
            $levels = ['7', '8', '9'];
        } else {
            $levels = ['10', '11', '12'];
        }

        $judul = 'Bank Soal';
        $subjudul = 'Buat Bank Soal';

        $mapels = MasterMapel::orderBy('nama_mapel', 'asc')->get();
        $gurus = MasterGuru::orderBy('nama_guru', 'asc')->get();

        $bank = new CbtBankSoal([
            'id_bank'         => null,
            'bank_kode'       => '',
            'bank_nama'       => '',
            'bank_mapel_id'   => $mapels->first()?->id_mapel ?? null,
            'bank_level'      => $levels[0] ?? '10',
            'bank_guru_id'    => $guru?->id_guru ?? ($gurus->first()?->id_guru ?? 1),
            'bank_kelas'      => [],
            'tampil_pg'       => 0,
            'bobot_pg'        => 0,
            'opsi'            => ($jenjang === 1 ? 3 : ($jenjang === 2 ? 4 : 5)),
            'tampil_kompleks' => 0,
            'bobot_kompleks'  => 0,
            'tampil_jodohkan' => 0,
            'bobot_jodohkan'  => 0,
            'tampil_isian'    => 0,
            'bobot_isian'     => 0,
            'tampil_esai'     => 0,
            'bobot_esai'      => 0,
            'soal_agama'      => '-',
            'status'          => 1,
        ]);

        $mapel_agama = [
            '-'        => 'Umum (Semua Agama)',
            'Islam'    => 'Islam',
            'Kristen'  => 'Kristen',
            'Katolik'  => 'Katolik',
            'Hindu'    => 'Hindu',
            'Budha'    => 'Budha',
            'Konghucu' => 'Konghucu',
        ];

        return view('guru.bank_soal.add', compact(
            'judul',
            'subjudul',
            'bank',
            'mapels',
            'gurus',
            'levels',
            'mapel_agama',
            'setting',
            'tp_active',
            'smt_active',
            'isAdmin',
            'guru'
        ));
    }

    /**
     * Halaman Edit Bank Soal (Garuda CBT Parity: /cbtbanksoal/editBank?id_bank=X).
     */
    public function editBank(Request $request, ?int $id = null): View
    {
        $id = $id ?? (int) $request->input('id_bank');
        $bank = CbtBankSoal::with(['mapel', 'guru'])->findOrFail($id);

        $user = Auth::user();
        $guru = $this->getGuru();
        $isAdmin = $user && ($user->isAdmin() || $user->role === 'admin');

        $tp_active = MasterTp::activeTp() ?? MasterTp::orderBy('id_tp', 'desc')->first();
        $smt_active = MasterSmt::activeSmt() ?? MasterSmt::first();

        $setting = Setting::first() ?? new Setting();
        $jenjang = (int) ($setting->jenjang ?? 3);
        if ($jenjang === 1) {
            $levels = ['1', '2', '3', '4', '5', '6'];
        } elseif ($jenjang === 2) {
            $levels = ['7', '8', '9'];
        } else {
            $levels = ['10', '11', '12'];
        }

        $judul = 'Bank Soal';
        $subjudul = 'Edit Bank Soal';

        $mapels = MasterMapel::orderBy('nama_mapel', 'asc')->get();
        $gurus = MasterGuru::orderBy('nama_guru', 'asc')->get();

        $mapel_agama = [
            '-'        => 'Umum (Semua Agama)',
            'Islam'    => 'Islam',
            'Kristen'  => 'Kristen',
            'Katolik'  => 'Katolik',
            'Hindu'    => 'Hindu',
            'Budha'    => 'Budha',
            'Konghucu' => 'Konghucu',
        ];

        return view('guru.bank_soal.add', compact(
            'judul',
            'subjudul',
            'bank',
            'mapels',
            'gurus',
            'levels',
            'mapel_agama',
            'setting',
            'tp_active',
            'smt_active',
            'isAdmin',
            'guru'
        ));
    }

    /**
     * Simpan Bank Soal (Garuda CBT Parity: /cbtbanksoal/saveBank).
     */
    public function saveBank(Request $request): JsonResponse|RedirectResponse
    {
        $id = $request->input('id_bank');
        $old_kode = $request->input('old_kode');
        $bank_kode = strtoupper(trim($request->input('kode') ?? $request->input('bank_kode')));

        if (empty($bank_kode)) {
            return response()->json(['status' => false, 'errors' => 'Kode Bank Soal harus diisi.'], 422);
        }

        // Cek keunikan kode jika berubah atau baru
        if (empty($id) || ($old_kode && $old_kode !== $bank_kode)) {
            $exists = CbtBankSoal::where('bank_kode', $bank_kode)->exists();
            if ($exists) {
                return response()->json(['status' => false, 'errors' => 'Kode Bank Soal sudah digunakan, gunakan kode lain.'], 422);
            }
        }

        $user = Auth::user();
        $guru = $this->getGuru();
        $isAdmin = $user && ($user->isAdmin() || $user->role === 'admin');

        $activeTp = MasterTp::activeTp()?->id_tp ?? 1;
        $activeSmt = MasterSmt::activeSmt()?->id_smt ?? 1;

        $mapelId = (int) ($request->input('mapel') ?? $request->input('id_mapel') ?? $request->input('bank_mapel_id') ?? 0);
        $guruId = $isAdmin && $request->filled('guru')
            ? (int) $request->input('guru')
            : ($guru?->id_guru ?? ($request->input('guru') ?? 1));

        // Format Kelas
        $rawKelas = $request->input('kelas', []);
        $formattedKelas = [];
        foreach ((array) $rawKelas as $kId) {
            if (!empty($kId)) {
                $formattedKelas[] = ['kelas_id' => (string) $kId];
            }
        }

        $data = [
            'id_tp'           => $activeTp,
            'id_smt'          => $activeSmt,
            'bank_kode'       => $bank_kode,
            'bank_mapel_id'   => $mapelId,
            'bank_kelas'      => json_encode($formattedKelas),
            'bank_level'      => (string) $request->input('level', $request->input('bank_level', '10')),
            'bank_guru_id'    => $guruId,
            'jml_soal'        => (int) $request->input('tampil_pg', 0),
            'tampil_pg'       => (int) $request->input('tampil_pg', 0),
            'bobot_pg'        => (int) $request->input('bobot_pg', 0),
            'opsi'            => (int) $request->input('opsi', 5),
            'jml_kompleks'    => (int) $request->input('tampil_kompleks', 0),
            'tampil_kompleks' => (int) $request->input('tampil_kompleks', 0),
            'bobot_kompleks'  => (int) $request->input('bobot_kompleks', 0),
            'jml_jodohkan'    => (int) $request->input('tampil_jodohkan', 0),
            'tampil_jodohkan' => (int) $request->input('tampil_jodohkan', 0),
            'bobot_jodohkan'  => (int) $request->input('bobot_jodohkan', 0),
            'jml_isian'       => (int) $request->input('tampil_isian', 0),
            'tampil_isian'    => (int) $request->input('tampil_isian', 0),
            'bobot_isian'     => (int) $request->input('bobot_isian', 0),
            'jml_esai'        => (int) $request->input('tampil_esai', 0),
            'tampil_esai'     => (int) $request->input('tampil_esai', 0),
            'bobot_esai'      => (int) $request->input('bobot_esai', 0),
            'status'          => (int) $request->input('status', 1),
            'soal_agama'      => $request->input('soal_agama', '-'),
            'date'            => date('Y-m-d H:i:s'),
        ];

        if (!empty($id)) {
            $bank = CbtBankSoal::findOrFail($id);
            $bank->update($data);
        } else {
            CbtBankSoal::create($data);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'Bank soal berhasil disimpan.',
            ]);
        }

        return redirect()->route('guru.bank_soal.index', ['type' => '0', 'mode' => '1'])
            ->with('success', 'Bank soal berhasil disimpan.');
    }

    /**
     * API Get Kelas Berdasarkan Level (Garuda CBT Parity: /cbtbanksoal/getkelaslevel).
     */
    public function getKelasLevel(Request $request): JsonResponse
    {
        $level = $request->input('level', '10');
        $classes = MasterKelas::where('level_id', $level)
            ->orderBy('nama_kelas', 'asc')
            ->get(['id_kelas', 'nama_kelas', 'kode_kelas']);

        return response()->json([
            'kelas' => $classes,
        ]);
    }

    /**
     * API Get Guru Berdasarkan Mapel (Garuda CBT Parity: /cbtbanksoal/getgurumapel).
     */
    public function getGuruMapel(Request $request): JsonResponse
    {
        $gurus = MasterGuru::orderBy('nama_guru', 'asc')
            ->pluck('nama_guru', 'id_guru')
            ->toArray();

        return response()->json($gurus);
    }

    /**
     * Simpan Bank Soal Baru (Full Garuda CBT Specification).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'bank_kode' => 'required|string|max:50',
            'bank_nama' => 'nullable|string|max:150',
            'id_mapel'  => 'nullable|integer',
            'mapel'     => 'nullable|integer',
        ]);

        $guru = $this->getGuru();
        $user = Auth::user();
        $isAdmin = $user && ($user->isAdmin() || $user->role === 'admin');

        $activeTp = MasterTp::activeTp()?->id_tp ?? 1;
        $activeSmt = MasterSmt::activeSmt()?->id_smt ?? 1;

        $mapelId = (int) ($request->input('id_mapel') ?? $request->input('mapel') ?? 0);
        $guruId = $isAdmin && $request->filled('bank_guru_id') 
            ? (int) $request->input('bank_guru_id') 
            : ($guru?->id_guru ?? 1);

        // Format Kelas Array
        $selectedKelas = $request->input('kelas', []);
        $formattedKelas = [];
        foreach ((array) $selectedKelas as $kId) {
            if (!empty($kId)) {
                $formattedKelas[] = ['kelas_id' => (string) $kId];
            }
        }

        $bankNama = $request->input('bank_nama');
        if (empty($bankNama)) {
            $mapelObj = MasterMapel::find($mapelId);
            $bankNama = 'Bank Soal ' . ($mapelObj?->nama_mapel ?? 'Ujian') . ' Kelas ' . $request->input('bank_level', '10');
        }

        $newBank = CbtBankSoal::create([
            'bank_kode'        => strtoupper(trim($request->input('bank_kode'))),
            'bank_nama'        => $bankNama,
            'bank_mapel_id'    => $mapelId,
            'bank_guru_id'     => $guruId,
            'bank_level'       => (string) $request->input('bank_level', '10'),
            'bank_kelas'       => json_encode($formattedKelas),
            'soal_agama'       => $request->input('soal_agama', '-'),
            'opsi'             => (int) $request->input('opsi', 5),
            'kkm'              => (int) $request->input('kkm', 75),
            'tampil_pg'        => (int) $request->input('tampil_pg', 30),
            'bobot_pg'         => (int) $request->input('bobot_pg', 70),
            'tampil_kompleks'  => (int) $request->input('tampil_kompleks', 0),
            'bobot_kompleks'   => (int) $request->input('bobot_kompleks', 0),
            'tampil_jodohkan'  => (int) $request->input('tampil_jodohkan', 0),
            'bobot_jodohkan'   => (int) $request->input('bobot_jodohkan', 0),
            'tampil_isian'     => (int) $request->input('tampil_isian', 0),
            'bobot_isian'      => (int) $request->input('bobot_isian', 0),
            'tampil_esai'      => (int) $request->input('tampil_esai', 5),
            'bobot_esai'       => (int) $request->input('bobot_esai', 30),
            'status'           => (int) $request->input('status', 1),
            'id_tp'            => $activeTp,
            'id_smt'           => $activeSmt,
            'date'             => date('Y-m-d H:i:s'),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'Bank Soal berhasil ditambahkan.',
                'id_bank' => $newBank->id_bank,
            ]);
        }

        return redirect()->route('guru.bank_soal.index', ['type' => '0', 'mode' => '1'])
            ->with('success', 'Paket Bank Soal baru berhasil dibuat.');
    }

    /**
     * Dapatkan detail Bank Soal dalam format JSON untuk pengeditan modal.
     */
    public function getBankJson(int $id): JsonResponse
    {
        $bank = CbtBankSoal::with(['mapel', 'guru'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'bank'   => $bank,
            'kelas'  => $bank->kelas_ids,
        ]);
    }

    /**
     * Perbarui Paket Bank Soal.
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $bank = CbtBankSoal::findOrFail($id);

        // Validasi kepemilikan guru jika bukan admin
        $guru = $this->getGuru();
        $user = Auth::user();
        $isAdmin = $user && ($user->isAdmin() || $user->role === 'admin');

        if (!$isAdmin && $bank->bank_guru_id != ($guru?->id_guru ?? 0)) {
            return response()->json(['status' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // Cek apakah bank sedang digunakan ujian siswa
        $isUsedByStudent = DB::table('cbt_siswa')->where('id_bank', $id)->exists();
        if ($isUsedByStudent) {
            $msg = 'Bank Soal ini sudah digunakan oleh siswa dan tidak dapat diubah!';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // Update atribut
        if ($request->filled('bank_kode')) {
            $bank->bank_kode = strtoupper(trim($request->input('bank_kode')));
        }
        if ($request->filled('bank_nama')) {
            $bank->bank_nama = $request->input('bank_nama');
        }
        if ($request->filled('id_mapel') || $request->filled('mapel')) {
            $bank->bank_mapel_id = (int) ($request->input('id_mapel') ?? $request->input('mapel'));
        }
        if ($isAdmin && $request->filled('bank_guru_id')) {
            $bank->bank_guru_id = (int) $request->input('bank_guru_id');
        }
        if ($request->filled('bank_level')) {
            $bank->bank_level = (string) $request->input('bank_level');
        }
        if ($request->has('kelas')) {
            $selectedKelas = $request->input('kelas', []);
            $formattedKelas = [];
            foreach ((array) $selectedKelas as $kId) {
                if (!empty($kId)) {
                    $formattedKelas[] = ['kelas_id' => (string) $kId];
                }
            }
            $bank->bank_kelas = json_encode($formattedKelas);
        }
        if ($request->filled('soal_agama')) {
            $bank->soal_agama = $request->input('soal_agama');
        }
        if ($request->filled('opsi')) {
            $bank->opsi = (int) $request->input('opsi');
        }
        if ($request->filled('kkm')) {
            $bank->kkm = (int) $request->input('kkm');
        }

        // Komposisi Butir Soal
        $bank->tampil_pg = (int) $request->input('tampil_pg', $bank->tampil_pg);
        $bank->bobot_pg = (int) $request->input('bobot_pg', $bank->bobot_pg);
        $bank->tampil_kompleks = (int) $request->input('tampil_kompleks', $bank->tampil_kompleks);
        $bank->bobot_kompleks = (int) $request->input('bobot_kompleks', $bank->bobot_kompleks);
        $bank->tampil_jodohkan = (int) $request->input('tampil_jodohkan', $bank->tampil_jodohkan);
        $bank->bobot_jodohkan = (int) $request->input('bobot_jodohkan', $bank->bobot_jodohkan);
        $bank->tampil_isian = (int) $request->input('tampil_isian', $bank->tampil_isian);
        $bank->bobot_isian = (int) $request->input('bobot_isian', $bank->bobot_isian);
        $bank->tampil_esai = (int) $request->input('tampil_esai', $bank->tampil_esai);
        $bank->bobot_esai = (int) $request->input('bobot_esai', $bank->bobot_esai);

        if ($request->has('status')) {
            $bank->status = (int) $request->input('status');
        }

        $bank->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'Bank Soal berhasil diperbarui.',
            ]);
        }

        return redirect()->route('guru.bank_soal.index', ['type' => '0', 'mode' => '1'])
            ->with('success', 'Bank Soal berhasil diperbarui.');
    }

    /**
     * Copy / Salin Bank Soal (Garuda CBT Parity: /cbtbanksoal/copybanksoal/{id}).
     */
    public function duplicate(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $source = CbtBankSoal::with('soals')->findOrFail($id);
        $activeTp = MasterTp::activeTp()?->id_tp ?? 1;
        $activeSmt = MasterSmt::activeSmt()?->id_smt ?? 1;

        $user = Auth::user();
        $guru = $this->getGuru();
        $targetGuruId = $guru?->id_guru ?? $source->bank_guru_id;

        $unik = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4);

        $newBank = null;
        DB::transaction(function () use ($source, $activeTp, $activeSmt, $targetGuruId, $unik, &$newBank) {
            $clone = $source->replicate();
            $clone->id_tp = $activeTp;
            $clone->id_smt = $activeSmt;
            $clone->bank_guru_id = $targetGuruId;
            $clone->bank_kode = $source->bank_kode . '_' . $unik;
            $clone->bank_nama = $source->bank_nama . ' (Salinan)';
            $clone->date = date('Y-m-d H:i:s');
            $clone->save();

            // Salin butir-butir soal
            foreach ($source->soals as $soal) {
                $cloneSoal = $soal->replicate();
                $cloneSoal->bank_id = $clone->id_bank;
                $cloneSoal->created_on = time();
                $cloneSoal->updated_on = time();
                $cloneSoal->save();
            }

            $newBank = $clone;
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'Bank Soal berhasil dicopy ke Tahun Pelajaran aktif.',
                'id_bank' => $newBank?->id_bank,
            ]);
        }

        return redirect()->route('guru.bank_soal.index', ['type' => '0', 'mode' => '1'])
            ->with('success', 'Bank Soal berhasil dicopy.');
    }

    /**
     * Hapus Tunggal Bank Soal.
     */
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $bank = CbtBankSoal::findOrFail($id);

        // Cek apakah digunakan jadwal aktif
        $isScheduled = DB::table('cbt_jadwal')->where('id_bank', $id)->where('status', '1')->exists();
        if ($isScheduled) {
            $msg = 'Ada jadwal ujian aktif yang menggunakan bank soal ini!';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // Cek apakah sudah digunakan siswa
        $isUsedByStudent = DB::table('cbt_siswa')->where('id_bank', $id)->exists();
        if ($isUsedByStudent) {
            $msg = 'Bank Soal tidak bisa dihapus karena sudah ada hasil ujian siswa!';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        DB::transaction(function () use ($id) {
            CbtSoal::where('bank_id', $id)->delete();
            CbtBankSoal::where('id_bank', $id)->delete();
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'  => true,
                'message' => 'Bank Soal berhasil dihapus.',
            ]);
        }

        return redirect()->route('guru.bank_soal.index', ['type' => '0', 'mode' => '1'])
            ->with('success', 'Bank Soal berhasil dihapus.');
    }

    /**
     * Kompatibilitas Legacy Garuda CBT: /cbtbanksoal/deleteBank?id_bank={id}.
     */
    public function destroyLegacy(Request $request): JsonResponse
    {
        $id = (int) ($request->input('id_bank') ?? $request->input('id'));
        return $this->destroy($request, $id);
    }

    /**
     * Hapus Terpilih (Bulk Delete - Garuda CBT Parity: /cbtbanksoal/deleteallbank).
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $rawIds = $request->input('ids');
        $ids = [];

        if (is_string($rawIds)) {
            $ids = json_decode($rawIds, true) ?? [];
        } elseif (is_array($rawIds)) {
            $ids = $rawIds;
        }

        if (empty($ids)) {
            return response()->json(['status' => false, 'message' => 'Pilih bank soal yang akan dihapus.'], 422);
        }

        // Cek apakah ada jadwal ujian yang menggunakan bank soal ini
        $scheduledCount = DB::table('cbt_jadwal')
            ->whereIn('id_bank', $ids)
            ->where('status', '1')
            ->count();

        if ($scheduledCount > 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Ada jadwal ujian aktif yang sedang menggunakan salah satu bank soal terpilih.',
            ], 422);
        }

        // Cek apakah sudah ada rekaman ujian siswa
        $studentUsageCount = DB::table('cbt_siswa')
            ->whereIn('id_bank', $ids)
            ->count();

        if ($studentUsageCount > 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Tidak dapat menghapus karena sebagian bank soal telah dikerjakan oleh siswa.',
            ], 422);
        }

        DB::transaction(function () use ($ids) {
            CbtSoal::whereIn('bank_id', $ids)->delete();
            CbtBankSoal::whereIn('id_bank', $ids)->delete();
        });

        return response()->json([
            'status'  => true,
            'message' => 'Semua bank soal terpilih berhasil dihapus.',
        ]);
    }

    /**
     * API Get Soal Siswa (Garuda CBT Parity: /cbtbanksoal/getsoalsiswa/{id}).
     */
    public function getSoalSiswa(int $id): JsonResponse
    {
        $bank = CbtBankSoal::with('mapel')->findOrFail($id);
        $soals = CbtSoal::where('bank_id', $id)->orderBy('nomor_soal', 'asc')->get();

        $formattedSoals = [];
        foreach ($soals as $s) {
            $jawaban = $s->jawaban;
            if (is_string($jawaban) && (str_starts_with($jawaban, '{') || str_starts_with($jawaban, '['))) {
                $decoded = json_decode($jawaban, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $jawaban = $decoded;
                }
            }

            $formattedSoals[] = [
                'id_soal'    => $s->id_soal,
                'nomor_soal' => $s->nomor_soal,
                'jenis'      => (string) ($s->jenis ?? 1),
                'soal'       => $s->soal,
                'opsi_a'     => $s->opsi_a,
                'opsi_b'     => $s->opsi_b,
                'opsi_c'     => $s->opsi_c,
                'opsi_d'     => $s->opsi_d,
                'opsi_e'     => $s->opsi_e,
                'jawaban'    => $jawaban,
            ];
        }

        return response()->json([
            'status' => true,
            'bank'   => $bank,
            'soal'   => $formattedSoals,
        ]);
    }

    /**
     * Download Butir Soal dalam format Naskah Soal Word (.doc) untuk Ujian Kertas.
     */
    public function downloadDocx(int $id): Response
    {
        $bank = CbtBankSoal::with(['mapel', 'guru'])->findOrFail($id);
        $soals = CbtSoal::where('bank_id', $id)->orderBy('nomor_soal', 'asc')->get();
        $setting = Setting::first() ?? new Setting();
        $tp_active = MasterTp::activeTp() ?? MasterTp::first();
        $smt_active = MasterSmt::activeSmt() ?? MasterSmt::first();

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta charset="utf-8"><title>Naskah Soal ' . e($bank->bank_kode) . '</title>';
        $html .= '<style>
            body { font-family: "Times New Roman", Times, serif; font-size: 11pt; line-height: 1.3; color: #000; }
            table.header-kop { width: 100%; border-bottom: 3px double #000; margin-bottom: 15px; }
            table.header-kop td { text-align: center; vertical-align: middle; }
            h2, h3, h4, p { margin: 2px 0; }
            ol { margin-left: 20px; padding-left: 0; }
            li { margin-bottom: 12px; }
            .opsi-list { list-style-type: upper-alpha; margin-top: 5px; margin-left: 25px; }
            .opsi-list li { margin-bottom: 3px; }
            .section-title { font-weight: bold; margin-top: 15px; margin-bottom: 5px; font-size: 12pt; }
        </style></head><body>';

        // Header Kop
        $html .= '<table class="header-kop"><tr><td>';
        $html .= '<h3>' . strtoupper(e($setting->sekolah ?? 'SEKOLAH')) . '</h3>';
        $html .= '<p>' . strtoupper(e($setting->alamat ?? '')) . ' - KABUPATEN ' . strtoupper(e($setting->kota ?? '')) . '</p>';
        $html .= '<p><b>NASKAH SOAL UJIAN - TAHUN PELAJARAN ' . e($tp_active?->tahun ?? '') . ' SEMESTER ' . strtoupper(e($smt_active?->smt ?? '')) . '</b></p>';
        $html .= '<p>Mata Pelajaran: <b>' . e($bank->mapel->nama_mapel ?? $bank->bank_kode) . '</b> | Kelas: <b>' . e($bank->bank_level) . '</b> | Kode Bank: <b>' . e($bank->bank_kode) . '</b></p>';
        $html .= '</td></tr></table>';

        // Butir Soal PG
        $soalsPg = $soals->where('jenis', 1);
        if ($soalsPg->isNotEmpty()) {
            $html .= '<div class="section-title">I. Pilihan Ganda</div><ol>';
            foreach ($soalsPg as $s) {
                $html .= '<li>' . $s->soal;
                $html .= '<ol class="opsi-list">';
                $html .= '<li>' . e($s->opsi_a) . '</li>';
                $html .= '<li>' . e($s->opsi_b) . '</li>';
                $html .= '<li>' . e($s->opsi_c) . '</li>';
                $html .= '<li>' . e($s->opsi_d) . '</li>';
                if (!empty($s->opsi_e)) {
                    $html .= '<li>' . e($s->opsi_e) . '</li>';
                }
                $html .= '</ol></li>';
            }
            $html .= '</ol>';
        }

        // Butir Soal PG Kompleks
        $soalsKompleks = $soals->where('jenis', 2);
        if ($soalsKompleks->isNotEmpty()) {
            $html .= '<div class="section-title">II. Pilihan Ganda Kompleks</div><ol>';
            foreach ($soalsKompleks as $s) {
                $html .= '<li>' . $s->soal . '</li>';
            }
            $html .= '</ol>';
        }

        // Butir Soal Menjodohkan
        $soalsJodoh = $soals->where('jenis', 3);
        if ($soalsJodoh->isNotEmpty()) {
            $html .= '<div class="section-title">III. Menjodohkan</div><ol>';
            foreach ($soalsJodoh as $s) {
                $html .= '<li>' . $s->soal . '</li>';
            }
            $html .= '</ol>';
        }

        // Butir Soal Isian
        $soalsIsian = $soals->where('jenis', 4);
        if ($soalsIsian->isNotEmpty()) {
            $html .= '<div class="section-title">IV. Isian Singkat</div><ol>';
            foreach ($soalsIsian as $s) {
                $html .= '<li>' . $s->soal . '</li>';
            }
            $html .= '</ol>';
        }

        // Butir Soal Esai
        $soalsEsai = $soals->where('jenis', 5);
        if ($soalsEsai->isNotEmpty()) {
            $html .= '<div class="section-title">V. Uraian / Essai</div><ol>';
            foreach ($soalsEsai as $s) {
                $html .= '<li>' . $s->soal . '</li>';
            }
            $html .= '</ol>';
        }

        $html .= '</body></html>';

        $filename = 'Naskah_Soal_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $bank->bank_kode) . '.doc';

        return response($html, 200, [
            'Content-Type'        => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Detail butir-butir soal di dalam Bank Soal.
     */
    public function show(int $id): View
    {
        $guru = $this->getGuru();
        $bank = CbtBankSoal::with(['mapel'])->findOrFail($id);

        if ($bank->bank_guru_id != ($guru?->id_guru ?? 0) && !Auth::user()->isAdmin()) {
            abort(403, 'Anda hanya berhak mengelola bank soal milik Anda sendiri.');
        }

        $soals = CbtSoal::where('bank_id', $id)->orderBy('nomor_soal', 'asc')->get();

        return view('guru.bank_soal.detail', compact('bank', 'soals', 'guru'));
    }

    /**
     * Tambah butir soal baru ke dalam Bank Soal.
     */
    public function storeSoal(Request $request, int $bankId): RedirectResponse
    {
        $request->validate([
            'soal'       => 'required|string',
            'jenis_soal' => 'required|integer',
        ]);

        $maxNomor = CbtSoal::where('bank_id', $bankId)->max('nomor_soal') ?? 0;
        $jenis = (int) $request->input('jenis_soal');

        $jawaban = $request->input('jawaban');
        if (is_array($jawaban)) {
            $jawaban = json_encode($jawaban);
        }

        CbtSoal::create([
            'bank_id'    => $bankId,
            'nomor_soal' => $maxNomor + 1,
            'jenis'      => $jenis,
            'jenis_soal' => $jenis,
            'soal'       => $request->input('soal'),
            'opsi_a'     => $request->input('opsi_a'),
            'opsi_b'     => $request->input('opsi_b'),
            'opsi_c'     => $request->input('opsi_c'),
            'opsi_d'     => $request->input('opsi_d'),
            'opsi_e'     => $request->input('opsi_e'),
            'jawaban'    => $jawaban,
            'bobot'      => $request->input('bobot', 1.00),
            'tampilkan'  => 1,
            'created_on' => time(),
            'updated_on' => time(),
        ]);

        return back()->with('success', 'Butir soal berhasil ditambahkan.');
    }

    /**
     * Hapus butir soal.
     */
    public function deleteSoal(int $soalId): RedirectResponse
    {
        $soal = CbtSoal::findOrFail($soalId);
        $bank = CbtBankSoal::findOrFail($soal->bank_id);
        $guru = $this->getGuru();

        if ($bank->bank_guru_id != ($guru?->id_guru ?? 0) && !Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $soal->delete();
        return back()->with('success', 'Butir soal berhasil dihapus.');
    }

    /**
     * Tampilan panduan / form import soal.
     */
    public function importView(int $bankId): View
    {
        $guru = $this->getGuru();
        $bank = CbtBankSoal::with(['mapel'])->findOrFail($bankId);

        if ($bank->bank_guru_id != ($guru?->id_guru ?? 0) && !Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        return view('guru.bank_soal.import', compact('bank', 'guru'));
    }

    /**
     * Unduh template file import soal (Excel .xlsx atau Word .docx resmi Garuda CBT).
     */
    public function downloadTemplate(string $format = 'word'): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if ($format === 'excel' || $format === 'xlsx') {
            $path = public_path('uploads/import/format/format_soal.xlsx');
            if (file_exists($path)) {
                return response()->download($path, 'Template_Soal_CBT.xlsx');
            }
        }

        // Format resmi Garuda CBT: format_soal_akm.docx
        $path = public_path('uploads/import/format/format_soal_akm.docx');
        return response()->download($path, 'Template_Soal_Garuda_CBT.docx');
    }

    /**
     * Proses import file Excel / Word butir soal ke bank soal guru.
     */
    public function importSoal(Request $request, int $bankId): RedirectResponse
    {
        $request->validate([
            'file_soal' => 'required|file|max:10240',
        ]);

        $bank = CbtBankSoal::findOrFail($bankId);
        $file = $request->file('file_soal');
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        // 1. Penanganan format Microsoft Word (.docx) resmi format AKM
        if ($ext === 'docx') {
            $parsedSoals = $this->parseDocxSoal($path);
            if (empty($parsedSoals)) {
                return back()->with('error', 'Tidak ditemukan butir soal yang valid dalam berkas Word. Pastikan menggunakan template resmi.');
            }

            $maxNomor = CbtSoal::where('bank_id', $bankId)->max('nomor_soal') ?? 0;
            $count = 0;

            DB::transaction(function () use ($parsedSoals, $bankId, &$maxNomor, &$count) {
                foreach ($parsedSoals as $s) {
                    $maxNomor++;
                    $s['bank_id'] = $bankId;
                    $s['nomor_soal'] = $maxNomor;
                    $s['created_on'] = time();
                    $s['updated_on'] = time();
                    CbtSoal::create($s);
                    $count++;
                }
            });

            return redirect()->route('guru.bank_soal.show', $bankId)
                ->with('success', "Sebanyak {$count} butir soal dari dokumen Word berhasil diimport ke dalam Bank Soal.");
        }

        // 2. Penanganan format Spreadsheet Excel (.xlsx, .xls) dan CSV
        $rows = [];

        if (in_array($ext, ['xlsx', 'xls'])) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
            } catch (\Throwable $e) {
                return back()->with('error', 'Gagal membaca berkas Excel: ' . $e->getMessage());
            }
        } elseif ($ext === 'csv' || $ext === 'txt') {
            $rows = array_map('str_getcsv', file($path));
        } else {
            return back()->with('error', 'Format file harus berupa Excel (.xlsx, .xls) atau Word (.docx).');
        }

        if (count($rows) < 2) {
            return back()->with('error', 'File template kosong atau format tidak sesuai.');
        }

        array_shift($rows); // Hapus header baris pertama
        $maxNomor = CbtSoal::where('bank_id', $bankId)->max('nomor_soal') ?? 0;
        $count = 0;

        DB::transaction(function () use ($rows, $bankId, &$maxNomor, &$count) {
            $currentSoal = null;

            foreach ($rows as $row) {
                if (empty($row) || !array_filter($row)) continue;

                $no = trim((string)($row[0] ?? ''));
                $jenis = isset($row[1]) && is_numeric($row[1]) ? (int)$row[1] : 1;
                $soalText = trim((string)($row[2] ?? ''));
                $opsi = strtoupper(trim((string)($row[3] ?? '')));
                $jawaban = trim((string)($row[4] ?? ''));
                $kunci = trim((string)($row[5] ?? ''));
                $bobot = isset($row[6]) && is_numeric($row[6]) ? (float)$row[6] : 1.00;

                // Jika baris memiliki nomor atau soal baru
                if (!empty($no) || !empty($soalText)) {
                    if ($currentSoal !== null && !empty($currentSoal['soal'])) {
                        CbtSoal::create($currentSoal);
                        $count++;
                    }

                    $maxNomor++;
                    $currentSoal = [
                        'bank_id'    => $bankId,
                        'nomor_soal' => $maxNomor,
                        'jenis'      => $jenis,
                        'jenis_soal' => $jenis,
                        'soal'       => $soalText,
                        'opsi_a'     => null,
                        'opsi_b'     => null,
                        'opsi_c'     => null,
                        'opsi_d'     => null,
                        'opsi_e'     => null,
                        'jawaban'    => '',
                        'bobot'      => $bobot,
                        'tampilkan'  => 1,
                        'created_on' => time(),
                        'updated_on' => time(),
                    ];
                }

                if ($currentSoal !== null) {
                    if ($opsi === 'A') $currentSoal['opsi_a'] = $jawaban;
                    elseif ($opsi === 'B') $currentSoal['opsi_b'] = $jawaban;
                    elseif ($opsi === 'C') $currentSoal['opsi_c'] = $jawaban;
                    elseif ($opsi === 'D') $currentSoal['opsi_d'] = $jawaban;
                    elseif ($opsi === 'E') $currentSoal['opsi_e'] = $jawaban;

                    if (in_array(strtolower($kunci), ['v', 'true', '1', 'benar']) && !empty($opsi)) {
                        if ($currentSoal['jenis'] == 2) {
                            $existing = array_filter(explode(',', $currentSoal['jawaban']));
                            $existing[] = $opsi;
                            $currentSoal['jawaban'] = implode(',', array_unique($existing));
                        } else {
                            $currentSoal['jawaban'] = $opsi;
                        }
                    } elseif (!empty($kunci) && empty($currentSoal['jawaban'])) {
                        $currentSoal['jawaban'] = $kunci;
                    }
                }
            }

            if ($currentSoal !== null && !empty($currentSoal['soal'])) {
                CbtSoal::create($currentSoal);
                $count++;
            }
        });

        return redirect()->route('guru.bank_soal.show', $bankId)
            ->with('success', "Sebanyak {$count} butir soal berhasil diimport ke dalam Bank Soal.");
    }

    /**
     * Parsing butir soal dari dokumen Word (.docx) resmi format AKM Garuda CBT.
     */
    protected function parseDocxSoal(string $filePath): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if (!$xml) return [];

        $doc = new \DOMDocument();
        @$doc->loadXML($xml);
        $tables = $doc->getElementsByTagName('tbl');

        $soalList = [];

        foreach ($tables as $ti => $table) {
            $rows = $table->getElementsByTagName('tr');
            if ($rows->length < 2) continue;

            $headerCells = [];
            foreach ($rows->item(0)->getElementsByTagName('tc') as $c) {
                $headerCells[] = strtoupper(trim($c->textContent));
            }

            // Tabel PG (Jenis 1) & PG Kompleks (Jenis 2)
            if (in_array('OPSI', $headerCells) && in_array('JAWABAN', $headerCells) && in_array('KUNCI', $headerCells)) {
                $currentSoal = null;
                for ($r = 1; $r < $rows->length; $r++) {
                    $cells = [];
                    foreach ($rows->item($r)->getElementsByTagName('tc') as $c) {
                        $cells[] = trim($c->textContent);
                    }

                    $no = $cells[0] ?? '';
                    $soal = $cells[1] ?? '';
                    $jenis = isset($cells[2]) && is_numeric($cells[2]) ? (int)$cells[2] : ($ti === 1 ? 2 : 1);
                    $opsi = strtoupper($cells[3] ?? '');
                    $jawaban = $cells[4] ?? '';
                    $kunci = $cells[5] ?? '';

                    if (!empty($no) || !empty($soal)) {
                        if ($currentSoal !== null && !empty($currentSoal['soal'])) {
                            $soalList[] = $currentSoal;
                        }
                        $currentSoal = [
                            'jenis'      => $jenis,
                            'jenis_soal' => $jenis,
                            'soal'       => $soal,
                            'opsi_a'     => null,
                            'opsi_b'     => null,
                            'opsi_c'     => null,
                            'opsi_d'     => null,
                            'opsi_e'     => null,
                            'jawaban'    => '',
                            'bobot'      => 1.0,
                            'tampilkan'  => 1,
                        ];
                    }

                    if ($currentSoal !== null) {
                        if ($opsi === 'A') $currentSoal['opsi_a'] = $jawaban;
                        elseif ($opsi === 'B') $currentSoal['opsi_b'] = $jawaban;
                        elseif ($opsi === 'C') $currentSoal['opsi_c'] = $jawaban;
                        elseif ($opsi === 'D') $currentSoal['opsi_d'] = $jawaban;
                        elseif ($opsi === 'E') $currentSoal['opsi_e'] = $jawaban;

                        if (in_array(strtolower($kunci), ['v', 'true', '1', 'benar']) && !empty($opsi)) {
                            if ($currentSoal['jenis'] == 2) {
                                $existing = array_filter(explode(',', $currentSoal['jawaban']));
                                $existing[] = $opsi;
                                $currentSoal['jawaban'] = implode(',', array_unique($existing));
                            } else {
                                $currentSoal['jawaban'] = $opsi;
                            }
                        } elseif (!empty($kunci) && empty($currentSoal['jawaban'])) {
                            $currentSoal['jawaban'] = $kunci;
                        }
                    }
                }
                if ($currentSoal !== null && !empty($currentSoal['soal'])) {
                    $soalList[] = $currentSoal;
                }
            }
            // Tabel Isian Singkat (Jenis 4) & Uraian / Essai (Jenis 5)
            elseif (in_array('JAWABAN', $headerCells) && !in_array('OPSI', $headerCells)) {
                for ($r = 1; $r < $rows->length; $r++) {
                    $cells = [];
                    foreach ($rows->item($r)->getElementsByTagName('tc') as $c) {
                        $cells[] = trim($c->textContent);
                    }
                    $soal = $cells[1] ?? '';
                    $jenis = isset($cells[2]) && is_numeric($cells[2]) ? (int)$cells[2] : ($ti === 3 ? 4 : 5);
                    $jawaban = $cells[3] ?? '';

                    if (!empty($soal)) {
                        $soalList[] = [
                            'jenis'      => $jenis,
                            'jenis_soal' => $jenis,
                            'soal'       => $soal,
                            'opsi_a'     => null,
                            'opsi_b'     => null,
                            'opsi_c'     => null,
                            'opsi_d'     => null,
                            'opsi_e'     => null,
                            'jawaban'    => $jawaban,
                            'bobot'      => 1.0,
                            'tampilkan'  => 1,
                        ];
                    }
                }
            }
        }

        return $soalList;
    }
}
