<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtPengawas;
use App\Models\CbtRuang;
use App\Models\CbtSesi;
use App\Models\CbtSesiSiswa;
use App\Models\CbtJadwal;
use App\Models\MasterGuru;
use App\Models\MasterKelas;
use App\Models\MasterSiswa;
use App\Models\MasterTp;
use App\Models\MasterSmt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CbtSesiRuangController extends Controller
{
    /**
     * Halaman Manajemen Sesi & Ruang Ujian serta Alokasi Siswa.
     */
    public function index(Request $request): View
    {
        $ruangList = CbtRuang::all();
        $sesiList  = CbtSesi::all();
        $pengawas  = CbtPengawas::with(['guru', 'jadwal.bankSoal', 'ruang', 'sesi'])->orderBy('id_pengawas', 'desc')->get();
        $guruList  = MasterGuru::orderBy('nama_guru', 'asc')->get();
        $jadwalList= CbtJadwal::with('bankSoal')->where('status', 1)->get();

        return view('admin.cbt.sesi_ruang', compact(
            'ruangList',
            'sesiList',
            'pengawas',
            'guruList',
            'jadwalList'
        ));
    }

    public function storeRuang(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_ruang' => 'required|string|max:50',
            'kode_ruang' => 'required|string|max:20',
        ]);

        CbtRuang::create($request->only('nama_ruang', 'kode_ruang'));
        return back()->with('success', 'Ruang ujian berhasil ditambahkan.');
    }

    public function storeSesi(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_sesi'     => 'required|string|max:50',
            'kode_sesi'     => 'required|string|max:20',
            'waktu_mulai'   => 'required',
            'waktu_selesai' => 'required',
        ]);

        CbtSesi::create($request->only('nama_sesi', 'kode_sesi', 'waktu_mulai', 'waktu_selesai'));
        return back()->with('success', 'Sesi ujian berhasil ditambahkan.');
    }

    public function assignPengawas(Request $request): RedirectResponse
    {
        $request->validate([
            'id_guru'   => 'required|integer',
            'id_jadwal' => 'required|integer',
            'id_ruang'  => 'required|integer',
            'id_sesi'   => 'required|integer',
        ]);

        $activeTp = MasterTp::activeTp()?->id_tp ?? 1;
        $activeSmt = MasterSmt::activeSmt()?->id_smt ?? 1;

        CbtPengawas::create([
            'id_tp'     => $activeTp,
            'id_smt'    => $activeSmt,
            'id_guru'   => $request->input('id_guru'),
            'id_jadwal' => $request->input('id_jadwal'),
            'id_ruang'  => $request->input('id_ruang'),
            'id_sesi'   => $request->input('id_sesi'),
            'jam_ke'    => $request->input('jam_ke', '1'),
        ]);

        return back()->with('success', 'Penugasan guru pengawas berhasil disimpan.');
    }

    public function deletePengawas(int $id): RedirectResponse
    {
        CbtPengawas::where('id_pengawas', $id)->delete();
        return back()->with('success', 'Penugasan pengawas ujian berhasil dihapus.');
    }
}
