<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelasSiswa;
use App\Models\MasterKelas;
use App\Models\MasterMapel;
use App\Models\MasterSiswa;
use App\Models\MasterTp;
use App\Models\MasterSmt;
use App\Models\RefTahunAjaran;
use App\Models\SiswaMapelPilihan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CbtAlokasiPilihanController extends Controller
{
    /**
     * Tampilkan halaman matriks alokasi mapel pilihan per rombel kelas (Fase F).
     */
    public function index(Request $request): View
    {
        $activeTp = MasterTp::activeTp();
        $activeSmt = MasterSmt::activeSmt();
        $activeTa = RefTahunAjaran::active()->first() ?? RefTahunAjaran::latest()->first();

        // Ambil semua daftar kelas
        $kelasList = MasterKelas::orderBy('level_id', 'asc')
            ->orderBy('nama_kelas', 'asc')
            ->get();

        // Default kelas terpilih: cari kelas tingkat 11/12 (Fase F) terlebih dahulu jika belum ditentukan
        $defaultKelasId = $kelasList->whereIn('level_id', [11, 12])->first()?->id_kelas ?? $kelasList->first()?->id_kelas;
        $selectedKelasId = (int) $request->input('kelas_id', $defaultKelasId);

        $selectedKelas = MasterKelas::find($selectedKelasId);

        // Ambil seluruh mapel pilihan (Fase F)
        $mapelPilihanList = MasterMapel::where('is_pilihan', true)
            ->orderBy('nama_mapel', 'asc')
            ->get();

        // Ambil siswa dalam kelas terpilih
        $siswas = collect();
        $matrixAllocations = [];

        if ($selectedKelas) {
            $siswaIds = KelasSiswa::where('id_kelas', $selectedKelasId)->pluck('id_siswa');
            $siswas = MasterSiswa::whereIn('id_siswa', $siswaIds)
                ->orderBy('nama', 'asc')
                ->get();

            if ($activeTa) {
                $allocations = SiswaMapelPilihan::whereIn('siswa_id', $siswaIds)
                    ->where('tahun_ajaran_id', $activeTa->id)
                    ->get();

                foreach ($allocations as $item) {
                    $matrixAllocations[$item->siswa_id][] = (int) $item->mapel_id;
                }
            }
        }

        return view('admin.cbt.alokasi_pilihan', compact(
            'kelasList',
            'selectedKelas',
            'selectedKelasId',
            'mapelPilihanList',
            'siswas',
            'matrixAllocations',
            'activeTa',
            'activeTp',
            'activeSmt'
        ));
    }

    /**
     * Simpan matriks alokasi mapel pilihan untuk seluruh siswa di kelas terpilih.
     */
    public function saveMatrix(Request $request): RedirectResponse
    {
        $request->validate([
            'kelas_id' => 'required|integer',
            'pilihan' => 'nullable|array',
        ]);

        $kelasId = (int) $request->input('kelas_id');
        $kelas = MasterKelas::findOrFail($kelasId);
        $activeTa = RefTahunAjaran::active()->first() ?? RefTahunAjaran::latest()->first();

        if (!$activeTa) {
            return back()->with('error', 'Tahun ajaran aktif belum disetel.');
        }

        $pilihanInput = $request->input('pilihan', []);
        $classSiswaIds = KelasSiswa::where('id_kelas', $kelasId)->pluck('id_siswa')->toArray();

        // Pilihan mapel valid
        $validMapelIds = MasterMapel::where('is_pilihan', true)->pluck('id_mapel')->toArray();
        $mapelIdToKode = MasterMapel::where('is_pilihan', true)->pluck('kode', 'id_mapel')->toArray();

        DB::transaction(function () use ($classSiswaIds, $activeTa, $pilihanInput, $validMapelIds, $mapelIdToKode) {
            // Hapus alokasi lama untuk kelas ini pada tahun ajaran aktif
            SiswaMapelPilihan::whereIn('siswa_id', $classSiswaIds)
                ->where('tahun_ajaran_id', $activeTa->id)
                ->delete();

            $insertRecords = [];
            $now = now();

            foreach ($classSiswaIds as $siswaId) {
                $selectedMapels = isset($pilihanInput[$siswaId]) && is_array($pilihanInput[$siswaId])
                    ? array_map('intval', $pilihanInput[$siswaId])
                    : [];

                // Filter hanya mapel valid
                $selectedMapels = array_values(array_intersect($selectedMapels, $validMapelIds));

                foreach ($selectedMapels as $mapelId) {
                    $insertRecords[] = [
                        'siswa_id' => $siswaId,
                        'mapel_id' => $mapelId,
                        'tahun_ajaran_id' => $activeTa->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Update juga kolom JSON master_siswa.mapel_pilihan untuk kompatibilitas penuh
                $chosenCodes = [];
                foreach ($selectedMapels as $mid) {
                    if (isset($mapelIdToKode[$mid])) {
                        $chosenCodes[] = $mapelIdToKode[$mid];
                    }
                }

                MasterSiswa::where('id_siswa', $siswaId)->update([
                    'mapel_pilihan' => !empty($chosenCodes) ? json_encode($chosenCodes) : null
                ]);
            }

            if (!empty($insertRecords)) {
                // Chunk insert jika record banyak
                foreach (array_chunk($insertRecords, 200) as $chunk) {
                    SiswaMapelPilihan::insert($chunk);
                }
            }
        });

        return back()->with('success', "Alokasi mata pelajaran pilihan untuk kelas {$kelas->nama_kelas} berhasil disimpan.");
    }

    /**
     * Download format template CSV untuk impor massal alokasi mapel pilihan siswa.
     */
    public function downloadTemplate(Request $request): StreamedResponse
    {
        $kelasId = $request->input('kelas_id');
        $activeTa = RefTahunAjaran::active()->first() ?? RefTahunAjaran::latest()->first();
        $mapelList = MasterMapel::where('is_pilihan', true)->orderBy('nama_mapel')->get();
        $mapelCodes = $mapelList->pluck('kode')->toArray();

        $selectedKelas = $kelasId ? MasterKelas::find($kelasId) : null;
        $filename = $selectedKelas
            ? 'template_mapel_pilihan_' . str_replace(' ', '_', strtolower($selectedKelas->nama_kelas)) . '.csv'
            : 'template_mapel_pilihan_siswa.csv';

        $response = new StreamedResponse(function () use ($selectedKelas, $mapelCodes, $activeTa) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM untuk Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Baris Petunjuk / Header
            fputcsv($handle, ['# PETUNJUK PENGISIAN MAPEL PILIHAN SISWA (FASE F)']);
            fputcsv($handle, ['# Pisahkan kode mapel dengan tanda koma (,) atau titik koma (;)']);
            fputcsv($handle, ['# KODE MAPEL PILIHAN YANG TERSEDIA: ' . implode(', ', $mapelCodes)]);
            fputcsv($handle, ['# CONTOH KODE: BIO_TL; FIS_TL; KIM_TL; MAT_TL']);
            fputcsv($handle, []); // baris kosong

            // Kolom Header
            fputcsv($handle, ['NISN', 'NAMA_SISWA', 'ROMBEL_KELAS', 'KODE_MAPEL_PILIHAN']);

            if ($selectedKelas) {
                $siswaIds = KelasSiswa::where('id_kelas', $selectedKelas->id_kelas)->pluck('id_siswa');
                $siswas = MasterSiswa::whereIn('id_siswa', $siswaIds)->orderBy('nama', 'asc')->get();

                // Ambil alokasi existing jika ada
                $existingMap = [];
                if ($activeTa) {
                    $allocs = SiswaMapelPilihan::with('mapel')
                        ->whereIn('siswa_id', $siswaIds)
                        ->where('tahun_ajaran_id', $activeTa->id)
                        ->get();
                    foreach ($allocs as $a) {
                        $code = $a->mapel?->kode;
                        if ($code) {
                            $existingMap[$a->siswa_id][] = $code;
                        }
                    }
                }

                foreach ($siswas as $s) {
                    $curCodes = isset($existingMap[$s->id_siswa]) ? implode('; ', $existingMap[$s->id_siswa]) : '';
                    fputcsv($handle, [
                        $s->nisn ?: $s->username,
                        $s->nama,
                        $selectedKelas->nama_kelas,
                        $curCodes
                    ]);
                }
            } else {
                // Baris contoh
                fputcsv($handle, ['0051234567', 'Ahmad Dani', 'XI Merdeka 1', 'BIO_TL; FIS_TL; KIM_TL; MAT_TL']);
                fputcsv($handle, ['0057654321', 'Budi Santoso', 'XI Merdeka 1', 'EKO_TL; GEO_TL; SOS_TL; MAT_TL']);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }

    /**
     * Impor massal alokasi mapel pilihan dari file CSV.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file_csv' => 'required|file|mimes:csv,txt|max:4096',
            'target_kelas_id' => 'nullable|integer',
        ]);

        $activeTa = RefTahunAjaran::active()->first() ?? RefTahunAjaran::latest()->first();
        if (!$activeTa) {
            return back()->with('error', 'Tahun ajaran aktif belum ditentukan.');
        }

        $file = $request->file('file_csv');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'Gagal membuka file CSV yang diunggah.');
        }

        // Muat peta mapel pilihan (kode upper -> id_mapel)
        $mapelList = MasterMapel::where('is_pilihan', true)->get();
        $codeToId = [];
        $idToCode = [];
        foreach ($mapelList as $m) {
            $key = strtoupper(trim($m->kode));
            $codeToId[$key] = (int) $m->id_mapel;
            $idToCode[(int) $m->id_mapel] = $m->kode;
            // Also map by name
            $nameKey = strtoupper(trim($m->nama_mapel));
            $codeToId[$nameKey] = (int) $m->id_mapel;
        }

        $processedStudents = 0;
        $totalAllocations = 0;

        DB::transaction(function () use ($handle, $codeToId, $idToCode, $activeTa, &$processedStudents, &$totalAllocations) {
            $headerFound = false;
            $colNisnIdx = 0;
            $colMapelIdx = 3;

            while (($row = fgetcsv($handle, 2000, ',')) !== false) {
                // Abaikan baris kosong atau baris komentar
                if (empty($row) || !isset($row[0])) continue;
                $firstCell = trim($row[0]);
                if (str_starts_with($firstCell, '#') || empty($firstCell)) continue;

                // Deteksi baris header
                if (!$headerFound) {
                    $upperRow = array_map(function ($c) { return strtoupper(trim($c)); }, $row);
                    $nisnIdx = array_search('NISN', $upperRow);
                    if ($nisnIdx !== false) {
                        $headerFound = true;
                        $colNisnIdx = $nisnIdx;
                        // Cari kolom mapel
                        foreach ($upperRow as $idx => $headerName) {
                            if (str_contains($headerName, 'MAPEL') || str_contains($headerName, 'PILIHAN')) {
                                $colMapelIdx = $idx;
                            }
                        }
                        continue;
                    }
                }

                // Ambil NISN/Username siswa
                $nisn = trim($row[$colNisnIdx] ?? '');
                if (empty($nisn) || str_starts_with($nisn, '#')) continue;

                // Cari siswa berdasarkan NISN atau username
                $siswa = MasterSiswa::where('nisn', $nisn)
                    ->orWhere('username', $nisn)
                    ->first();

                if (!$siswa) continue;

                // Parse kolom mapel
                $rawMapel = trim($row[$colMapelIdx] ?? '');
                // Pecah berdasarkan koma, titik koma, spasi, atau pipe
                $splitTokens = preg_split('/[,;|]+/', $rawMapel);
                $matchedMapelIds = [];

                foreach ($splitTokens as $token) {
                    $tokenUpper = strtoupper(trim($token));
                    if (isset($codeToId[$tokenUpper])) {
                        $matchedMapelIds[] = $codeToId[$tokenUpper];
                    }
                }

                $matchedMapelIds = array_unique($matchedMapelIds);

                // Hapus alokasi lama siswa di tahun ajaran ini
                SiswaMapelPilihan::where('siswa_id', $siswa->id_siswa)
                    ->where('tahun_ajaran_id', $activeTa->id)
                    ->delete();

                // Simpan alokasi baru
                $insertRows = [];
                $now = now();
                foreach ($matchedMapelIds as $mid) {
                    $insertRows[] = [
                        'siswa_id' => $siswa->id_siswa,
                        'mapel_id' => $mid,
                        'tahun_ajaran_id' => $activeTa->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $totalAllocations++;
                }

                if (!empty($insertRows)) {
                    SiswaMapelPilihan::insert($insertRows);
                }

                // Update JSON mapel_pilihan pada master_siswa
                $chosenCodes = [];
                foreach ($matchedMapelIds as $mid) {
                    if (isset($idToCode[$mid])) {
                        $chosenCodes[] = $idToCode[$mid];
                    }
                }
                $siswa->update([
                    'mapel_pilihan' => !empty($chosenCodes) ? json_encode($chosenCodes) : null
                ]);

                $processedStudents++;
            }
        });

        fclose($handle);

        return back()->with('success', "Impor selesai: Berhasil memperbarui pilihan untuk {$processedStudents} siswa ({$totalAllocations} alokasi mapel).");
    }
}
