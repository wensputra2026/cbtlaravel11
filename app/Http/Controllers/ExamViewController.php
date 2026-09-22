<?php

namespace App\Http\Controllers;

use App\Models\CbtJadwal;
use App\Models\CbtJadwalClean;
use App\Models\MasterSiswa;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExamViewController extends Controller
{
    /**
     * Tampilan antarmuka pengerjaan ujian peserta CBT (Tahap 4 - Zero-Latency Alpine.js).
     *
     * Route: GET /cbt/ujian/{jadwalId} atau GET /exam/session/{jadwalId}
     */
    public function showExam(Request $request, int $jadwalId): View
    {
        $user = Auth::user();
        
        $siswa = null;
        if ($user) {
            $siswa = Siswa::with(['kelas', 'sesi', 'ruang'])->where('user_id', $user->id)->first()
                ?? MasterSiswa::with('kelasSiswa.kelas')->where('username', $user->username)->first()
                ?? $user->siswa;
        }

        // Ambil data jadwal (skema bersih diprioritaskan, fallback ke legacy)
        $jadwal = CbtJadwalClean::with(['bankSoal.mapel'])->find($jadwalId);
        $namaUjian = 'Ujian CBT';
        $namaMapel = 'Mata Pelajaran';
        $durasiMenit = 90;
        $pakaiToken = false;
        $tampilkanNilai = false;

        if ($jadwal) {
            $namaUjian = $jadwal->nama_ujian;
            $namaMapel = $jadwal->bankSoal?->mapel?->nama_mapel ?? 'Mata Pelajaran';
            $durasiMenit = (int) $jadwal->durasi_menit;
            $pakaiToken = (bool) $jadwal->pakai_token;
            $tampilkanNilai = (bool) $jadwal->tampilkan_nilai;
        } else {
            $legacyJadwal = CbtJadwal::with(['bankSoal.mapel', 'jenis'])->findOrFail($jadwalId);
            $namaJenis = $legacyJadwal->jenis?->nama_jenis;
            $namaUjian = ($namaJenis ? $namaJenis . ' - ' : '') . ($legacyJadwal->bankSoal?->bank_nama ?? 'Ujian CBT');
            $namaMapel = $legacyJadwal->bankSoal?->mapel?->nama_mapel ?? 'Mata Pelajaran';
            $durasiMenit = (int) $legacyJadwal->durasi_ujian;
            $pakaiToken = (bool) $legacyJadwal->token;
            $tampilkanNilai = (bool) $legacyJadwal->hasil_tampil;
            $jadwal = $legacyJadwal;
        }

        $namaSiswa = $siswa?->nama_lengkap ?? $siswa?->nama ?? $user?->username ?? 'Peserta CBT';
        $nis = $siswa?->nis ?? '-';
        $nisn = $siswa?->nisn ?? '-';
        $kelas = $siswa?->kelas?->nama_kelas ?? $siswa?->kelasSiswa?->first()?->kelas?->nama_kelas ?? '-';
        $ruang = $siswa?->ruang?->nama_ruang ?? 'Ruang 01';
        $sesi = $siswa?->sesi?->nama_sesi ?? 'Sesi 1';
        $foto = $siswa?->foto ? asset('uploads/foto_siswa/' . $siswa->foto) : null;

        $siswaId = (int) ($siswa?->id ?? $siswa?->id_siswa ?? 1);

        // Generate / ambil auth token sesi ujian
        $authToken = '';
        if ($user) {
            $authToken = \Illuminate\Support\Str::random(40);
            \App\Services\Exam\ExamRedisBuffer::set("cbt_auth_user:{$authToken}", (string) $user->id, 28800);
        }

        // Tentukan device token
        $deviceToken = $request->header('X-Device-Token')
            ?? $request->input('device_token')
            ?? session()->getId()
            ?? ('dev_' . $siswaId);

        return view('cbt.ujian', [
            'user'           => $user,
            'siswa'          => $siswa,
            'siswaId'        => $siswaId,
            'namaSiswa'      => $namaSiswa,
            'nis'            => $nis,
            'nisn'           => $nisn,
            'kelas'          => $kelas,
            'ruang'          => $ruang,
            'sesi'           => $sesi,
            'foto'           => $foto,
            'jadwal'         => $jadwal,
            'jadwalId'       => (int) $jadwalId,
            'namaUjian'      => $namaUjian,
            'namaMapel'      => $namaMapel,
            'durasiMenit'    => $durasiMenit,
            'pakaiToken'     => $pakaiToken,
            'tampilkanNilai' => $tampilkanNilai,
            'token'          => $request->input('token') ?? session('exam_token_' . $jadwalId, ''),
            'authToken'      => $authToken,
            'deviceToken'    => $deviceToken,
            'apiBase'        => url('/api/cbt'),
        ]);
    }
}
