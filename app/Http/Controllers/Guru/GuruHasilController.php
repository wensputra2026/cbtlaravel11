<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\CbtJadwal;
use App\Models\CbtSiswa;
use App\Models\CbtSoal;
use App\Models\MasterGuru;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuruHasilController extends Controller
{
    protected function getGuru(): ?MasterGuru
    {
        $user = Auth::user();
        return MasterGuru::where('id_user', $user->id)
            ->orWhere('username', $user->username)
            ->first();
    }

    /**
     * Rekapitulasi perolehan nilai siswa per jadwal ujian yang diampu guru.
     */
    public function index(Request $request): View
    {
        $guru = $this->getGuru();
        $guruId = $guru?->id_guru ?? 0;
        $jadwalId = $request->input('jadwal_id');

        // Jadwal ujian milik guru ini
        $jadwalList = CbtJadwal::with('bankSoal.mapel')
            ->whereHas('bankSoal', function ($q) use ($guruId) {
                if (!Auth::user()->isAdmin()) {
                    $q->where('bank_guru_id', $guruId);
                }
            })
            ->orderBy('id_jadwal', 'desc')
            ->get();

        $selectedJadwal = null;
        $pesertaList = [];
        $stats = [
            'total' => 0,
            'tertinggi' => 0,
            'terendah' => 0,
            'rata_rata' => 0,
            'tuntas' => 0,
            'belum_tuntas' => 0,
        ];

        if ($jadwalId) {
            $selectedJadwal = CbtJadwal::with('bankSoal.mapel')->find($jadwalId);
            if ($selectedJadwal) {
                $pesertaList = CbtSiswa::with(['siswa.kelasSiswa.kelas', 'siswa.nomorPeserta'])
                    ->where('id_jadwal', $jadwalId)
                    ->orderBy('id_cbt_siswa', 'asc')
                    ->get();

                $stats['total'] = count($pesertaList);
                $nilaiArr = [];
                $kkm = 75;

                foreach ($pesertaList as $p) {
                    $input = is_string($p->nilai_input) ? json_decode($p->nilai_input, true) : ($p->nilai_input ?? []);
                    $pg = (float)($input['pg_nilai'] ?? 0);
                    $esai = (float)($input['essai_nilai'] ?? 0);
                    $total = $pg + $esai;
                    $p->skor_total = $total;
                    $p->nilai_pg = $pg;
                    $p->nilai_esai = $esai;
                    $nilaiArr[] = $total;

                    if ($total >= $kkm) {
                        $stats['tuntas']++;
                    } else {
                        $stats['belum_tuntas']++;
                    }
                }

                if (!empty($nilaiArr)) {
                    $stats['tertinggi'] = max($nilaiArr);
                    $stats['terendah'] = min($nilaiArr);
                    $stats['rata_rata'] = round(array_sum($nilaiArr) / count($nilaiArr), 1);
                }
            }
        }

        return view('guru.hasil.index', compact('guru', 'jadwalList', 'selectedJadwal', 'pesertaList', 'stats', 'jadwalId'));
    }

    /**
     * Ekspor Nilai ke format CSV/Excel untuk jadwal yang diampu.
     */
    public function export(int $jadwalId): Response
    {
        $guru = $this->getGuru();
        $guruId = $guru?->id_guru ?? 0;

        $jadwal = CbtJadwal::with('bankSoal.mapel')->findOrFail($jadwalId);

        if ($jadwal->bankSoal?->bank_guru_id != $guruId && !Auth::user()->isAdmin()) {
            abort(403, 'Anda tidak berhak mengekspor nilai ujian ini.');
        }

        $peserta = CbtSiswa::with('siswa.kelasSiswa.kelas', 'siswa.nomorPeserta')
            ->where('id_jadwal', $jadwalId)
            ->get();

        $namaBank = $jadwal->bankSoal->bank_nama ?? 'Ujian';
        $filename = 'Nilai_Guru_' . preg_replace('/[^A-Za-z0-9_]/', '_', $namaBank) . '_' . date('Ymd_His') . '.csv';

        $output = "No,Nomor Peserta,NISN,Nama Siswa,Kelas,Status,Waktu Selesai,Nilai PG,Nilai Esai,Nilai Akhir\n";

        foreach ($peserta as $idx => $p) {
            $siswa = $p->siswa;
            $nama = str_replace([',', '"'], ' ', $siswa->nama ?? '-');
            $nisn = $siswa->nisn ?? '-';
            $nomorPeserta = $siswa->nomorPeserta?->nomor_peserta ?? '-';
            $kelas = $siswa->kelasSiswa->first()?->kelas->nama_kelas ?? '-';
            $status = $p->status == 2 ? 'Selesai' : ($p->status == 1 ? 'Sedang Mengerjakan' : 'Belum Mulai');

            $input = is_string($p->nilai_input) ? json_decode($p->nilai_input, true) : ($p->nilai_input ?? []);
            $pg = (float)($input['pg_nilai'] ?? 0);
            $esai = (float)($input['essai_nilai'] ?? 0);
            $akhir = $pg + $esai;

            $output .= ($idx + 1) . ",\"{$nomorPeserta}\",{$nisn},\"{$nama}\",{$kelas},{$status},\"{$p->selesai}\",{$pg},{$esai},{$akhir}\n";
        }

        return response($output, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Analisis butir soal milik guru pengampu.
     */
    public function analisis(Request $request): View
    {
        $guru = $this->getGuru();
        $guruId = $guru?->id_guru ?? 0;
        $jadwalId = $request->input('jadwal_id');

        $jadwalList = CbtJadwal::with('bankSoal.mapel')
            ->whereHas('bankSoal', function ($q) use ($guruId) {
                if (!Auth::user()->isAdmin()) {
                    $q->where('bank_guru_id', $guruId);
                }
            })
            ->orderBy('id_jadwal', 'desc')
            ->get();

        $selectedJadwal = null;
        $analisisSoal = [];
        $rekapStat = [
            'total_peserta' => 0,
            'nilai_tertinggi' => 0,
            'nilai_terendah' => 0,
            'nilai_rata' => 0,
            'tuntas' => 0,
            'belum_tuntas' => 0,
        ];

        if ($jadwalId) {
            $selectedJadwal = CbtJadwal::with(['bankSoal.mapel', 'bankSoal.soals'])->find($jadwalId);

            if ($selectedJadwal) {
                $peserta = CbtSiswa::where('id_jadwal', $jadwalId)->get();
                $rekapStat['total_peserta'] = count($peserta);

                $nilaiList = [];
                $kkm = 75;

                foreach ($peserta as $p) {
                    $input = is_string($p->nilai_input) ? json_decode($p->nilai_input, true) : ($p->nilai_input ?? []);
                    $pg = (float)($input['pg_nilai'] ?? 0);
                    $esai = (float)($input['essai_nilai'] ?? 0);
                    $total = $pg + $esai;
                    $nilaiList[] = $total;

                    if ($total >= $kkm) {
                        $rekapStat['tuntas']++;
                    } else {
                        $rekapStat['belum_tuntas']++;
                    }
                }

                if (!empty($nilaiList)) {
                    $rekapStat['nilai_tertinggi'] = max($nilaiList);
                    $rekapStat['nilai_terendah']  = min($nilaiList);
                    $rekapStat['nilai_rata']      = round(array_sum($nilaiList) / count($nilaiList), 1);
                }

                $soals = CbtSoal::where('bank_id', $selectedJadwal->id_bank)->orderBy('nomor_soal', 'asc')->get();
                foreach ($soals as $soal) {
                    $benar = 0;
                    $dijawab = 0;

                    foreach ($peserta as $p) {
                        $jawaban = is_string($p->jawaban) ? json_decode($p->jawaban, true) : ($p->jawaban ?? []);
                        if (isset($jawaban[$soal->nomor_soal])) {
                            $dijawab++;
                            $ansRaw = $jawaban[$soal->nomor_soal];
                            $keyRaw = $soal->jawaban;

                            if (is_array($ansRaw) || is_array($keyRaw)) {
                                if (is_array($ansRaw) && is_array($keyRaw) && $ansRaw == $keyRaw) {
                                    $benar++;
                                }
                            } else {
                                $ans = strtoupper(trim((string)$ansRaw));
                                $key = strtoupper(trim((string)$keyRaw));
                                if ($ans !== '' && $ans === $key) {
                                    $benar++;
                                }
                            }
                        }
                    }

                    $persenBenar = $dijawab > 0 ? round(($benar / $dijawab) * 100, 1) : 0;
                    $kategori = 'Sedang';
                    if ($persenBenar >= 75) {
                        $kategori = 'Mudah';
                    } elseif ($persenBenar < 35) {
                        $kategori = 'Sukar';
                    }

                    $analisisSoal[] = [
                        'nomor'        => $soal->nomor_soal,
                        'jenis'        => $soal->jenis ?? $soal->jenis_soal ?? 1,
                        'kunci'        => is_array($soal->jawaban) ? 'Kompleks' : (string)($soal->jawaban ?? '-'),
                        'peserta'      => $dijawab,
                        'benar'        => $benar,
                        'persen_benar' => $persenBenar,
                        'tingkat'      => $kategori,
                    ];
                }
            }
        }

        return view('guru.analisis.index', compact('guru', 'jadwalList', 'selectedJadwal', 'analisisSoal', 'rekapStat', 'jadwalId'));
    }
}
