<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtNomorPeserta;
use App\Models\CbtRuang;
use App\Models\CbtSesi;
use App\Models\CbtSesiSiswa;
use App\Models\KelasSiswa;
use App\Models\MasterKelas;
use App\Models\MasterSiswa;
use App\Models\MasterTp;
use App\Models\MasterSmt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CbtAlokasiController extends Controller
{
    // =========================================================================
    // 1. ALOKASI SESI & RUANG SISWA
    // =========================================================================
    public function indexSesiSiswa(Request $request): View
    {
        $kelasId = $request->input('kelas_id');
        $ruangId = $request->input('ruang_id');
        $sesiId  = $request->input('sesi_id');

        $query = MasterSiswa::with(['kelasSiswa.kelas', 'sesiSiswa.ruang', 'sesiSiswa.sesi']);

        if ($kelasId) {
            $siswaIds = KelasSiswa::where('id_kelas', $kelasId)->pluck('id_siswa');
            $query->whereIn('id_siswa', $siswaIds);
        }

        if ($ruangId) {
            $siswaIds = CbtSesiSiswa::where('ruang_id', $ruangId)->pluck('siswa_id');
            $query->whereIn('id_siswa', $siswaIds);
        }

        if ($sesiId) {
            $siswaIds = CbtSesiSiswa::where('sesi_id', $sesiId)->pluck('siswa_id');
            $query->whereIn('id_siswa', $siswaIds);
        }

        $siswas = $query->orderBy('nama', 'asc')->paginate(10)->withQueryString();

        $kelasList = MasterKelas::orderBy('nama_kelas', 'asc')->get();
        $ruangList = CbtRuang::all();
        $sesiList  = CbtSesi::all();

        return view('admin.cbt.alokasi_sesi', compact(
            'siswas',
            'kelasList',
            'ruangList',
            'sesiList',
            'kelasId',
            'ruangId',
            'sesiId'
        ));
    }

    public function storeSesiSiswa(Request $request): RedirectResponse
    {
        $request->validate([
            'siswa_id' => 'required|integer',
            'ruang_id' => 'required|integer',
            'sesi_id'  => 'required|integer',
        ]);

        $activeTp = MasterTp::activeTp()?->id_tp ?? 1;
        $activeSmt = MasterSmt::activeSmt()?->id_smt ?? 1;

        $kelasSiswa = KelasSiswa::where('id_siswa', $request->input('siswa_id'))->first();
        $kelasId = $kelasSiswa?->id_kelas ?? 1;

        CbtSesiSiswa::updateOrCreate(
            ['siswa_id' => $request->input('siswa_id')],
            [
                'kelas_id' => $kelasId,
                'ruang_id' => $request->input('ruang_id'),
                'sesi_id'  => $request->input('sesi_id'),
                'tp_id'    => $activeTp,
                'smt_id'   => $activeSmt,
            ]
        );

        return back()->with('success', 'Alokasi sesi dan ruang peserta berhasil diperbarui.');
    }

    public function autoAlokasi(Request $request): RedirectResponse
    {
        $request->validate([
            'id_kelas' => 'required|integer',
            'ruang_id' => 'required|integer',
            'sesi_id'  => 'required|integer',
        ]);

        $kelasId = (int) $request->input('id_kelas');
        $ruangId = (int) $request->input('ruang_id');
        $sesiId  = (int) $request->input('sesi_id');

        $activeTp = MasterTp::activeTp()?->id_tp ?? 1;
        $activeSmt = MasterSmt::activeSmt()?->id_smt ?? 1;

        $siswaIds = KelasSiswa::where('id_kelas', $kelasId)->pluck('id_siswa');

        foreach ($siswaIds as $sid) {
            CbtSesiSiswa::updateOrCreate(
                ['siswa_id' => $sid],
                [
                    'kelas_id' => $kelasId,
                    'ruang_id' => $ruangId,
                    'sesi_id'  => $sesiId,
                    'tp_id'    => $activeTp,
                    'smt_id'   => $activeSmt,
                ]
            );
        }

        return back()->with('success', "Seluruh siswa kelas tersebut berhasil dialokasikan ke Ruang & Sesi terpilih.");
    }

    // =========================================================================
    // 2. NOMOR PESERTA UJIAN
    // =========================================================================
    public function indexNomorPeserta(Request $request): View
    {
        $kelasId = $request->input('kelas_id');
        $query = MasterSiswa::with(['nomorPeserta', 'kelasSiswa.kelas']);

        if ($kelasId) {
            $siswaIds = KelasSiswa::where('id_kelas', $kelasId)->pluck('id_siswa');
            $query->whereIn('id_siswa', $siswaIds);
        }

        $siswas = $query->orderBy('nama', 'asc')->paginate(10)->withQueryString();
        $kelasList = MasterKelas::orderBy('nama_kelas', 'asc')->get();

        return view('admin.cbt.nomor_peserta', compact('siswas', 'kelasList', 'kelasId'));
    }

    public function generateNomorPeserta(Request $request): RedirectResponse
    {
        $prefix = $request->input('prefix', '26-01');
        $activeTp = MasterTp::activeTp()?->id_tp ?? 1;

        $siswas = MasterSiswa::orderBy('id_siswa', 'asc')->get();
        $counter = 1;

        DB::transaction(function () use ($siswas, $prefix, $activeTp, &$counter) {
            foreach ($siswas as $s) {
                $nomor = sprintf('%s-%04d', $prefix, $counter);
                CbtNomorPeserta::updateOrCreate(
                    ['id_siswa' => $s->id_siswa, 'id_tp' => $activeTp],
                    ['nomor_peserta' => $nomor]
                );
                $counter++;
            }
        });

        return back()->with('success', 'Nomor peserta ujian berhasil digenerate otomatis untuk seluruh siswa.');
    }
}
