<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtJadwal;
use App\Models\CbtSiswa;
use App\Models\MasterSiswa;
use App\Services\Exam\ExamGradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CbtNilaiController extends Controller
{
    public function __construct(
        protected ExamGradingService $gradingService
    ) {}

    /**
     * Halaman Rekapitulasi Hasil Ujian Per Jadwal dengan Filter Tahun Ajaran.
     */
    public function index(Request $request): View
    {
        $academicService = app(\App\Services\AcademicYear\AcademicYearService::class);
        $allYears = $academicService->getAllYears();
        $selectedYearId = $request->input('tahun_ajaran_id') ?? $academicService->getSelectedYear()?->id;
        $selectedYear = $selectedYearId ? \App\Models\RefTahunAjaran::find($selectedYearId) : $academicService->getSelectedYear();

        $jadwalQuery = CbtJadwal::with('bankSoal.mapel')->orderBy('id_jadwal', 'desc');

        if ($selectedYear) {
            $matchedTp = \App\Models\MasterTp::where('tahun', $selectedYear->tahun)->first();
            if ($matchedTp) {
                $jadwalQuery->where('id_tp', $matchedTp->id_tp);
            }
            if ($selectedYear->semester) {
                $jadwalQuery->where('id_smt', (int) $selectedYear->semester);
            }
        }

        $jadwalList = $jadwalQuery->get();

        $jadwalId = $request->input('jadwal_id') ?? $jadwalList->first()?->id_jadwal;
        $selectedJadwal = null;
        $pesertaList = collect();

        if ($jadwalId) {
            $selectedJadwal = CbtJadwal::with('bankSoal.mapel')->find($jadwalId);
            if ($selectedJadwal) {
                $pesertaQuery = CbtSiswa::with(['siswa.kelasSiswa.kelas', 'siswa.rombelTahun'])
                    ->where('id_jadwal', $jadwalId);

                if ($request->filled('q')) {
                    $search = $request->input('q');
                    $pesertaQuery->whereHas('siswa', function ($sq) use ($search) {
                        $sq->where('nama', 'like', "%{$search}%")
                           ->orWhere('nisn', 'like', "%{$search}%");
                    });
                }

                $pesertaList = $pesertaQuery->orderBy('id_cbt_siswa', 'asc')
                    ->paginate(10)
                    ->withQueryString();
            }
        }

        return view('admin.cbt.nilai.index', compact('jadwalList', 'selectedJadwal', 'pesertaList', 'jadwalId', 'allYears', 'selectedYear'));
    }

    /**
     * Detail Lembar Jawaban & Koreksi Esai Peserta.
     */
    public function showJawaban(int $cbtSiswaId): View
    {
        $cbtSiswa = CbtSiswa::with(['siswa', 'jadwal.bankSoal'])->findOrFail($cbtSiswaId);
        $jawabanList = is_string($cbtSiswa->jawaban) ? json_decode($cbtSiswa->jawaban, true) : ($cbtSiswa->jawaban ?? []);
        $nilaiInput = is_string($cbtSiswa->nilai_input) ? json_decode($cbtSiswa->nilai_input, true) : ($cbtSiswa->nilai_input ?? []);

        return view('admin.cbt.nilai.koreksi', compact('cbtSiswa', 'jawabanList', 'nilaiInput'));
    }

    /**
     * Simpan Koreksi Esai Manual.
     */
    public function updateKoreksi(Request $request, int $cbtSiswaId): RedirectResponse
    {
        $cbtSiswa = CbtSiswa::findOrFail($cbtSiswaId);
        $nilaiEsai = (float) $request->input('nilai_esai', 0);

        $nilaiInput = is_string($cbtSiswa->nilai_input) ? json_decode($cbtSiswa->nilai_input, true) : ($cbtSiswa->nilai_input ?? []);
        if (!is_array($nilaiInput)) {
            $nilaiInput = [];
        }

        $nilaiInput['dikoreksi'] = '1';
        $nilaiInput['essai_nilai'] = $nilaiEsai;

        $cbtSiswa->nilai_input = json_encode($nilaiInput);
        $cbtSiswa->save();

        return back()->with('success', 'Hasil koreksi esai berhasil disimpan.');
    }

    /**
     * Ekspor Nilai ke format Microsoft Excel (.xls).
     */
    public function export(int $jadwalId): Response
    {
        $jadwal = CbtJadwal::with('bankSoal.mapel')->findOrFail($jadwalId);
        $peserta = CbtSiswa::with('siswa.kelasSiswa.kelas')
            ->where('id_jadwal', $jadwalId)
            ->get();

        $mapel = $jadwal->bankSoal->mapel->nama_mapel ?? 'Mapel';
        $namaBank = $jadwal->bankSoal->bank_nama ?? 'Ujian';
        $filename = 'Rekap_Nilai_' . preg_replace('/[^A-Za-z0-9_]/', '_', $namaBank) . '_' . date('Ymd_His') . '.xls';

        $headers = ['No', 'NISN', 'Nama Siswa', 'Kelas', 'Status Ujian', 'Waktu Mulai', 'Waktu Selesai', 'Nilai PG', 'Nilai Esai', 'Nilai Akhir'];
        $rows = [];

        foreach ($peserta as $idx => $p) {
            $siswa = $p->siswa;
            $nama = $siswa->nama ?? '-';
            $nisn = $siswa->nisn ?? '-';
            $kelas = $siswa->kelasSiswa->first()?->kelas->nama_kelas ?? '-';
            $status = $p->status == 2 ? 'Selesai' : ($p->status == 1 ? 'Sedang Mengerjakan' : 'Belum Mulai');
            
            $input = is_string($p->nilai_input) ? json_decode($p->nilai_input, true) : ($p->nilai_input ?? []);
            $nilaiPg = (float)($input['pg_nilai'] ?? 0);
            $nilaiEsai = (float)($input['essai_nilai'] ?? 0);
            $nilaiAkhir = round($nilaiPg + $nilaiEsai, 2);

            $rows[] = [
                $idx + 1,
                $nisn,
                $nama,
                $kelas,
                $status,
                $p->mulai ?? '-',
                $p->selesai ?? '-',
                $nilaiPg,
                $nilaiEsai,
                $nilaiAkhir,
            ];
        }

        return \App\Services\Export\ExcelExportService::download(
            'Rekap Nilai ' . mb_substr($namaBank, 0, 20),
            $headers,
            $rows,
            $filename
        );
    }
}
