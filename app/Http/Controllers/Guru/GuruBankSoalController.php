<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CbtBankSoal;
use App\Models\CbtSoal;
use App\Models\MasterGuru;
use App\Models\MasterMapel;
use App\Models\MasterTp;
use App\Models\MasterSmt;
use App\Services\Teacher\TeacherScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GuruBankSoalController extends Controller
{
    public function __construct(
        protected TeacherScopeService $scopeService
    ) {}

    protected function getGuru()
    {
        return $this->scopeService->getTeacherProfile();
    }

    public function index(Request $request): View
    {
        $guru = $this->getGuru();
        $guruId = $guru?->id_guru ?? 0;
        $search = $request->input('q');

        $query = CbtBankSoal::with(['mapel'])
            ->where('bank_guru_id', $guruId)
            ->orderBy('id_bank', 'desc');

        if ($search) {
            $query->where(function ($sq) use ($search) {
                $sq->where('bank_nama', 'like', "%{$search}%")
                   ->orWhere('bank_kode', 'like', "%{$search}%");
            });
        }

        $banks = $query->paginate(10)->withQueryString();

        $assignment = $this->scopeService->getTeacherAssignment($guru);
        $mapelList = $assignment['assigned_mapels'];
        if ($mapelList->isEmpty() && Auth::user()->isAdmin()) {
            $mapelList = MasterMapel::orderBy('nama_mapel', 'asc')->get();
        }

        return view('guru.bank_soal.index', compact('banks', 'mapelList', 'guru', 'search', 'assignment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'bank_kode' => 'required|string|max:50',
            'bank_nama' => 'required|string|max:150',
            'id_mapel'  => 'required|integer',
        ]);

        $guru = $this->getGuru();
        $guruId = $guru?->id_guru ?? 1;

        $assignment = $this->scopeService->getTeacherAssignment($guru);
        if (!Auth::user()->isAdmin() && !empty($assignment['assigned_mapel_ids'])) {
            if (!in_array((int)$request->input('id_mapel'), $assignment['assigned_mapel_ids'])) {
                return back()->with('error', 'Akses Ditolak: Anda hanya dapat membuat paket bank soal untuk mata pelajaran yang Anda ampu.');
            }
        }

        $activeTp = MasterTp::activeTp()?->id_tp ?? 1;
        $activeSmt = MasterSmt::activeSmt()?->id_smt ?? 1;

        CbtBankSoal::create([
            'bank_kode'        => $request->input('bank_kode'),
            'bank_nama'        => $request->input('bank_nama'),
            'id_mapel'         => $request->input('id_mapel'),
            'bank_guru_id'     => $guruId,
            'bank_level'       => $request->input('bank_level', '10'),
            'bank_kelas'       => serialize([]),
            'tampil_pg'        => (int) $request->input('tampil_pg', 0),
            'bobot_pg'         => (int) $request->input('bobot_pg', 100),
            'tampil_kompleks'  => (int) $request->input('tampil_kompleks', 0),
            'bobot_kompleks'   => (int) $request->input('bobot_kompleks', 0),
            'tampil_jodohkan'  => (int) $request->input('tampil_jodohkan', 0),
            'bobot_jodohkan'   => (int) $request->input('bobot_jodohkan', 0),
            'tampil_isian'     => (int) $request->input('tampil_isian', 0),
            'bobot_isian'      => (int) $request->input('bobot_isian', 0),
            'tampil_esai'      => (int) $request->input('tampil_esai', 0),
            'bobot_esai'       => (int) $request->input('bobot_esai', 0),
            'status'           => 1,
            'id_tp'            => $activeTp,
            'id_smt'           => $activeSmt,
        ]);

        return back()->with('success', 'Paket Bank Soal baru berhasil dibuat.');
    }

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

    public function buatSoal(int $id): View
    {
        $guru = $this->getGuru();
        $bank = CbtBankSoal::with(['mapel'])->findOrFail($id);

        if ($bank->bank_guru_id != ($guru?->id_guru ?? 0) && !Auth::user()->isAdmin()) {
            abort(403, 'Anda hanya berhak mengelola bank soal milik Anda sendiri.');
        }

        $nextNomor = (CbtSoal::where('bank_id', $id)->max('nomor_soal') ?? 0) + 1;
        $soal = null;

        return view('guru.bank_soal.buat_soal', compact('bank', 'nextNomor', 'soal', 'guru'));
    }

    public function editSoal(int $id, int $soalId): View
    {
        $guru = $this->getGuru();
        $bank = CbtBankSoal::with(['mapel'])->findOrFail($id);

        if ($bank->bank_guru_id != ($guru?->id_guru ?? 0) && !Auth::user()->isAdmin()) {
            abort(403, 'Anda hanya berhak mengelola bank soal milik Anda sendiri.');
        }

        $soal = CbtSoal::where('bank_id', $id)->findOrFail($soalId);
        $nextNomor = $soal->nomor_soal;

        return view('guru.bank_soal.buat_soal', compact('bank', 'nextNomor', 'soal', 'guru'));
    }

    public function updateSoal(Request $request, int $soalId): RedirectResponse
    {
        $request->validate([
            'soal'       => 'required|string',
            'jenis_soal' => 'required|integer',
        ]);

        $soal = CbtSoal::findOrFail($soalId);
        $bankId = $soal->bank_id;

        $jawaban = $request->input('jawaban');
        if (is_array($jawaban)) {
            $jawaban = $request->input('jenis_soal') == 2 ? array_values(array_filter($jawaban)) : $jawaban;
        }

        $jenis = (int) $request->input('jenis_soal');

        $soal->update([
            'jenis'      => $jenis,
            'jenis_soal' => $jenis,
            'nomor_soal' => $request->input('nomor_soal', $soal->nomor_soal),
            'soal'       => $request->input('soal'),
            'opsi_a'     => $request->input('opsi_a'),
            'opsi_b'     => $request->input('opsi_b'),
            'opsi_c'     => $request->input('opsi_c'),
            'opsi_d'     => $request->input('opsi_d'),
            'opsi_e'     => $request->input('opsi_e'),
            'jawaban'    => $jawaban,
            'bobot'      => $request->input('bobot', 1.00),
        ]);

        return redirect()->route('guru.bank_soal.show', $bankId)
            ->with('success', 'Butir soal #' . $soal->nomor_soal . ' berhasil diperbarui.');
    }

    public function storeSoal(Request $request, int $bankId): RedirectResponse
    {
        $request->validate([
            'soal'       => 'required|string',
            'jenis_soal' => 'required|integer',
        ]);

        $maxNomor = CbtSoal::where('bank_id', $bankId)->max('nomor_soal') ?? 0;
        $nomorSoal = (int) $request->input('nomor_soal', $maxNomor + 1);
        $jenis = (int) $request->input('jenis_soal');

        $jawaban = $request->input('jawaban');
        if (is_array($jawaban)) {
            $jawaban = $jenis == 2 ? array_values(array_filter($jawaban)) : $jawaban;
        }

        CbtSoal::create([
            'bank_id'    => $bankId,
            'nomor_soal' => $nomorSoal,
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
        ]);

        if ($request->input('action') === 'next') {
            return redirect()->route('guru.bank_soal.buat_soal', $bankId)
                ->with('success', 'Butir soal #' . $nomorSoal . ' berhasil disimpan. Silakan buat butir berikutnya.');
        }

        return redirect()->route('guru.bank_soal.show', $bankId)
            ->with('success', 'Butir soal berhasil ditambahkan.');
    }

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
     * Tampilan form / panduan import soal.
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
     * Download format template soal Excel (CSV) atau Word.
     */
    public function downloadTemplate(string $format = 'csv'): Response
    {
        if ($format === 'csv') {
            $filename = 'template_import_soal_cbt.csv';
            $content = "nomor_soal,jenis_soal,pertanyaan,opsi_a,opsi_b,opsi_c,opsi_d,opsi_e,kunci_jawaban,bobot\n";
            $content .= "1,1,\"Ibukota Negara Indonesia yang baru adalah?\",\"Jakarta\",\"Nusantara (IKN)\",\"Surabaya\",\"Bandung\",\"Medan\",\"B\",1.00\n";
            $content .= "2,1,\"Berapakah hasil dari 15 x 6?\",\"80\",\"85\",\"90\",\"95\",\"100\",\"C\",1.00\n";
            $content .= "3,4,\"Lembaga negara pembuat undang-undang di Indonesia adalah?\",\"\",\"\",\"\",\"\",\"DPR\",2.00\n";
            $content .= "4,5,\"Jelaskan secara ringkas pengertian fotosintesis pada tumbuhan!\",\"\",\"\",\"\",\"\",\"Rubrik: proses pembuatan makanan oleh tumbuhan menggunakan sinar matahari\",3.00\n";

            return response($content, 200, [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // Format Word text / markdown guide
        $filename = 'template_soal_word.doc';
        $content = "FORMAT PENYUSUNAN SOAL WORD CBT\n\n";
        $content .= "[SOAL PG]\n1. Ibukota Negara Indonesia yang baru adalah?\nA. Jakarta\nB. Nusantara (IKN)\nC. Surabaya\nD. Bandung\nE. Medan\nKUNCI: B\n\n";
        $content .= "[SOAL ISIAN]\n2. Lembaga pembuat undang-undang adalah?\nKUNCI: DPR\n\n";
        $content .= "[SOAL ESAI]\n3. Jelaskan proses fotosintesis pada tumbuhan!\nPANDUAN: Proses pembuatan makanan dengan sinar matahari.\n";

        return response($content, 200, [
            'Content-Type'        => 'application/msword; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Proses import file CSV butir soal ke bank soal guru.
     */
    public function importSoal(Request $request, int $bankId): RedirectResponse
    {
        $request->validate([
            'file_soal' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $bank = CbtBankSoal::findOrFail($bankId);
        $file = $request->file('file_soal');
        $path = $file->getRealPath();

        $rows = array_map('str_getcsv', file($path));
        if (count($rows) < 2) {
            return back()->with('error', 'File template kosong atau format tidak sesuai.');
        }

        $header = array_map('trim', array_map('strtolower', array_shift($rows)));
        $maxNomor = CbtSoal::where('bank_id', $bankId)->max('nomor_soal') ?? 0;
        $count = 0;

        DB::transaction(function () use ($rows, $bankId, &$maxNomor, &$count) {
            foreach ($rows as $row) {
                if (empty($row) || count($row) < 3) continue;

                $maxNomor++;
                $jenis = isset($row[1]) && is_numeric($row[1]) ? (int)$row[1] : 1;
                $pertanyaan = $row[2] ?? '';
                $opsiA = $row[3] ?? null;
                $opsiB = $row[4] ?? null;
                $opsiC = $row[5] ?? null;
                $opsiD = $row[6] ?? null;
                $opsiE = $row[7] ?? null;
                $kunci = $row[8] ?? '';
                $bobot = isset($row[9]) && is_numeric($row[9]) ? (float)$row[9] : 1.00;

                if (!empty(trim($pertanyaan))) {
                    CbtSoal::create([
                        'bank_id'    => $bankId,
                        'nomor_soal' => $maxNomor,
                        'jenis'      => $jenis,
                        'jenis_soal' => $jenis,
                        'soal'       => $pertanyaan,
                        'opsi_a'     => $opsiA,
                        'opsi_b'     => $opsiB,
                        'opsi_c'     => $opsiC,
                        'opsi_d'     => $opsiD,
                        'opsi_e'     => $opsiE,
                        'jawaban'    => $kunci,
                        'bobot'      => $bobot,
                    ]);
                    $count++;
                }
            }
        });

        return redirect()->route('guru.bank_soal.show', $bankId)
            ->with('success', "Sebanyak {$count} butir soal berhasil diimport ke dalam Bank Soal.");
    }
}
