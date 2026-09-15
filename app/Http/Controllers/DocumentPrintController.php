<?php

namespace App\Http\Controllers;

use App\Models\CbtJadwal;
use App\Models\KelasSiswa;
use App\Models\MasterKelas;
use App\Models\MasterSiswa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentPrintController extends Controller
{
    /**
     * Cetak Kartu Peserta Ujian CBT (Format A4 - 8 kartu per lembar).
     */
    public function kartuPeserta(Request $request): View
    {
        $kelasId = $request->input('kelas_id');
        $query = MasterSiswa::with('kelasSiswa.kelas');

        if ($kelasId) {
            $siswaIds = KelasSiswa::where('id_kelas', $kelasId)->pluck('id_siswa');
            $query->whereIn('id_siswa', $siswaIds);
        }

        $siswas = $query->orderBy('nama', 'asc')->get();
        $kelasList = MasterKelas::all();

        return view('print.kartu_peserta', [
            'siswas'    => $siswas,
            'kelasList' => $kelasList,
            'kelasId'   => $kelasId,
        ]);
    }

    /**
     * Cetak Daftar Hadir Peserta Ujian.
     */
    public function daftarHadir(Request $request, int $jadwalId): View
    {
        $jadwal = CbtJadwal::with('bankSoal.mapel')->findOrFail($jadwalId);
        $bank = $jadwal->bankSoal;
        $kelasIds = $bank->kelas_ids;

        $siswaQuery = MasterSiswa::with('kelasSiswa.kelas');
        if (!empty($kelasIds)) {
            $siswaIds = KelasSiswa::whereIn('id_kelas', $kelasIds)->pluck('id_siswa');
            $siswaQuery->whereIn('id_siswa', $siswaIds);
        }

        $siswas = $siswaQuery->orderBy('nama', 'asc')->get();

        return view('print.daftar_hadir', [
            'jadwal' => $jadwal,
            'bank'   => $bank,
            'siswas' => $siswas,
        ]);
    }

    /**
     * Cetak Berita Acara Pelaksanaan Ujian.
     */
    public function beritaAcara(Request $request, int $jadwalId): View
    {
        $jadwal = CbtJadwal::with('bankSoal.mapel')->findOrFail($jadwalId);
        $bank = $jadwal->bankSoal;
        $kelasIds = $bank->kelas_ids;

        $totalSiswa = 0;
        if (!empty($kelasIds)) {
            $totalSiswa = KelasSiswa::whereIn('id_kelas', $kelasIds)->count();
        }

        return view('print.berita_acara', [
            'jadwal'     => $jadwal,
            'bank'       => $bank,
            'totalSiswa' => $totalSiswa,
        ]);
    }

    /**
     * Cetak Denah Tempat Duduk Ruang Ujian.
     */
    public function denahRuang(Request $request): View
    {
        $ruang = $request->input('ruang', 'Ruang 01');
        $kapasitas = (int) $request->input('kapasitas', 20);

        return view('print.denah_ruang', [
            'ruang'     => $ruang,
            'kapasitas' => $kapasitas,
        ]);
    }

    /**
     * Cetak Daftar Hadir Pengawas Ruang.
     */
    public function daftarHadirPengawas(Request $request): View
    {
        $pengawas = \App\Models\CbtPengawas::with(['guru', 'jadwal.bankSoal.mapel', 'ruang', 'sesi'])->get();
        return view('print.daftar_hadir_pengawas', compact('pengawas'));
    }

    /**
     * Cetak Jadwal Pengawas Ruang.
     */
    public function jadwalPengawas(Request $request): View
    {
        $pengawas = \App\Models\CbtPengawas::with(['guru', 'jadwal.bankSoal.mapel', 'ruang', 'sesi'])->get();
        return view('print.jadwal_pengawas', compact('pengawas'));
    }

    /**
     * Cetak Kartu / Akun Login Siswa Massal.
     */
    public function kartuLogin(Request $request): View
    {
        $kelasId = $request->input('kelas_id');
        $query = MasterSiswa::with('kelasSiswa.kelas');

        if ($kelasId) {
            $siswaIds = KelasSiswa::where('id_kelas', $kelasId)->pluck('id_siswa');
            $query->whereIn('id_siswa', $siswaIds);
        }

        $siswas = $query->orderBy('nama', 'asc')->get();
        $kelasList = MasterKelas::all();

        return view('print.kartu_login', compact('siswas', 'kelasList', 'kelasId'));
    }
}
