<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtBankSoal;
use App\Models\CbtSoal;
use App\Models\MasterGuru;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\MasterTp;
use App\Models\MasterSmt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CbtBankSoalController extends Controller
{
    /**
     * Daftar seluruh Bank Soal ujian.
     */
    public function index(Request $request): View
    {
        $mapelId = $request->input('mapel_id');
        $search = $request->input('q');
        $query = CbtBankSoal::with(['mapel', 'guru'])->orderBy('id_bank', 'desc');

        if ($mapelId) {
            $query->where('id_mapel', $mapelId);
        }

        if ($search) {
            $query->where(function ($sq) use ($search) {
                $sq->where('bank_nama', 'like', "%{$search}%")
                   ->orWhere('bank_kode', 'like', "%{$search}%");
            });
        }

        $banks = $query->paginate(10)->withQueryString();
        $mapelList = MasterMapel::orderBy('nama_mapel', 'asc')->get();
        $guruList = MasterGuru::orderBy('nama_guru', 'asc')->get();
        $kelasList = MasterKelas::orderBy('nama_kelas', 'asc')->get();

        return view('admin.cbt.bank_soal.index', compact('banks', 'mapelList', 'guruList', 'kelasList', 'mapelId', 'search'));
    }

    /**
     * Simpan Bank Soal Baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'bank_kode' => 'required|string|max:50',
            'bank_nama' => 'required|string|max:150',
            'id_mapel'  => 'required|integer',
        ]);

        $activeTp = MasterTp::activeTp()?->id_tp ?? 1;
        $activeSmt = MasterSmt::activeSmt()?->id_smt ?? 1;

        $bankKelas = $request->input('bank_kelas', []);
        if (is_array($bankKelas)) {
            $bankKelas = array_values(array_filter(array_map('intval', $bankKelas)));
        }

        CbtBankSoal::create([
            'bank_kode'        => $request->input('bank_kode'),
            'bank_nama'        => $request->input('bank_nama'),
            'id_mapel'         => $request->input('id_mapel'),
            'bank_guru_id'     => $request->input('bank_guru_id') ?? 1,
            'bank_level'       => $request->input('bank_level') ?? '10',
            'bank_kelas'       => $bankKelas,
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

        return back()->with('success', 'Paket Bank Soal berhasil dibuat.');
    }

    /**
     * Detail butir-butir soal di dalam Bank Soal.
     */
    public function show(int $id): View
    {
        $bank = CbtBankSoal::with(['mapel', 'soals'])->findOrFail($id);
        $soals = CbtSoal::where('bank_id', $id)->orderBy('nomor_soal', 'asc')->get();

        return view('admin.cbt.bank_soal.detail', compact('bank', 'soals'));
    }

    /**
     * Halaman pembuatan butir soal baru (Garuda CBT buatsoal parity).
     */
    public function buatSoal(int $id): View
    {
        $bank = CbtBankSoal::with('mapel')->findOrFail($id);
        $nextNomor = (CbtSoal::where('bank_id', $id)->max('nomor_soal') ?? 0) + 1;
        $soal = null;

        return view('admin.cbt.bank_soal.buat_soal', compact('bank', 'nextNomor', 'soal'));
    }

    /**
     * Halaman edit butir soal.
     */
    public function editSoal(int $id, int $soalId): View
    {
        $bank = CbtBankSoal::with('mapel')->findOrFail($id);
        $soal = CbtSoal::where('bank_id', $id)->findOrFail($soalId);
        $nextNomor = $soal->nomor_soal;

        return view('admin.cbt.bank_soal.buat_soal', compact('bank', 'nextNomor', 'soal'));
    }

    /**
     * Update butir soal.
     */
    public function updateSoal(Request $request, int $soalId): RedirectResponse
    {
        $request->validate([
            'soal'       => 'required|string',
            'jenis_soal' => 'required|integer',
        ]);

        $soal = CbtSoal::findOrFail($soalId);
        $bankId = $soal->bank_id;

        $jawaban = $request->input('jawaban');
        if ($request->input('jenis_soal') == 2 && is_array($jawaban)) {
            $jawaban = array_values(array_filter($jawaban));
        }

        $soal->update([
            'jenis_soal' => $request->input('jenis_soal'),
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

        return redirect()->route('admin.cbt.bank_soal.show', $bankId)
            ->with('success', 'Butir soal #' . $soal->nomor_soal . ' berhasil diperbarui.');
    }

    /**
     * Tambah / Simpan Butir Soal Baru.
     */
    public function storeSoal(Request $request, int $bankId): RedirectResponse
    {
        $request->validate([
            'soal'       => 'required|string',
            'jenis_soal' => 'required|integer', // 1=PG, 2=Kompleks, 3=Jodohkan, 4=Isian, 5=Esai
        ]);

        $maxNomor = CbtSoal::where('bank_id', $bankId)->max('nomor_soal') ?? 0;
        $nomorSoal = (int) $request->input('nomor_soal', $maxNomor + 1);

        $jawaban = $request->input('jawaban');
        if ($request->input('jenis_soal') == 2 && is_array($jawaban)) {
            $jawaban = array_values(array_filter($jawaban));
        }

        CbtSoal::create([
            'bank_id'    => $bankId,
            'nomor_soal' => $nomorSoal,
            'jenis_soal' => $request->input('jenis_soal'),
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
            return redirect()->route('admin.cbt.bank_soal.buat_soal', $bankId)
                ->with('success', 'Butir soal #' . $nomorSoal . ' berhasil disimpan. Silakan buat butir berikutnya.');
        }

        return redirect()->route('admin.cbt.bank_soal.show', $bankId)
            ->with('success', 'Butir soal baru berhasil disimpan.');
    }

    /**
     * Hapus Butir Soal.
     */
    public function deleteSoal(int $soalId): RedirectResponse
    {
        CbtSoal::where('id_soal', $soalId)->delete();
        return back()->with('success', 'Butir soal berhasil dihapus.');
    }

    /**
     * Duplikasi Bank Soal.
     */
    public function duplicate(int $id): RedirectResponse
    {
        $source = CbtBankSoal::with('soals')->findOrFail($id);

        DB::transaction(function () use ($source) {
            $clone = $source->replicate();
            $clone->bank_kode = $source->bank_kode . '_COPY';
            $clone->bank_nama = $source->bank_nama . ' (Salinan)';
            $clone->save();

            foreach ($source->soals as $soal) {
                $cloneSoal = $soal->replicate();
                $cloneSoal->bank_id = $clone->id_bank;
                $cloneSoal->save();
            }
        });

        return back()->with('success', 'Bank Soal beserta seluruh butir soal berhasil diduplikasi.');
    }

    /**
     * Hapus Bank Soal.
     */
    public function destroy(int $id): RedirectResponse
    {
        CbtSoal::where('bank_id', $id)->delete();
        CbtBankSoal::where('id_bank', $id)->delete();

        return back()->with('success', 'Bank Soal berhasil dihapus.');
    }
}
