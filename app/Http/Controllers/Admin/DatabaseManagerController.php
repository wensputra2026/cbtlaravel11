<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CbtSiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DatabaseManagerController extends Controller
{
    /**
     * Halaman Manajer Database & Backup.
     */
    public function index(): View
    {
        $dbName = DB::getDatabaseName();
        $tables = DB::select('SHOW TABLE STATUS');

        $totalSize = 0;
        $tableCount = count($tables);

        foreach ($tables as $t) {
            $totalSize += ($t->Data_length + $t->Index_length);
        }

        $sizeMb = round($totalSize / 1024 / 1024, 2);

        return view('admin.setting.database', compact('dbName', 'tables', 'tableCount', 'sizeMb'));
    }

    /**
     * Backup Database ke file .sql dan langsung download ke client.
     */
    public function backup(): Response
    {
        $dbName = DB::getDatabaseName();
        $tables = DB::select('SHOW TABLES');
        $keyName = 'Tables_in_' . $dbName;

        $sqlDump = "-- ========================================================\n";
        $sqlDump .= "-- SMANBEN-CBT Database Backup\n";
        $sqlDump .= "-- Database: {$dbName}\n";
        $sqlDump .= "-- Tanggal : " . date('Y-m-d H:i:s') . "\n";
        $sqlDump .= "-- ========================================================\n\n";
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableObj) {
            $table = $tableObj->$keyName;

            // Jangan dump view (check if view)
            $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
            if (empty($createTable)) continue;

            $createStatement = $createTable[0]->{'Create Table'} ?? null;
            if (!$createStatement) continue; // It's a view

            $sqlDump .= "-- --------------------------------------------------------\n";
            $sqlDump .= "-- Struktur tabel `{$table}`\n";
            $sqlDump .= "-- --------------------------------------------------------\n";
            $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sqlDump .= $createStatement . ";\n\n";

            // Dump data
            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $sqlDump .= "-- Data untuk tabel `{$table}`\n";
                foreach ($rows->chunk(100) as $chunk) {
                    foreach ($chunk as $row) {
                        $values = [];
                        foreach ((array)$row as $val) {
                            if (is_null($val)) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = "'" . addslashes((string)$val) . "'";
                            }
                        }
                        $sqlDump .= "INSERT INTO `{$table}` VALUES (" . implode(", ", $values) . ");\n";
                    }
                }
                $sqlDump .= "\n";
            }
        }

        $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $filename = 'SMANBEN_CBT_Backup_' . date('Ymd_His') . '.sql';

        return response($sqlDump, 200, [
            'Content-Type'        => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Bersihkan riwayat sesi ujian dan log.
     */
    public function clearData(Request $request): RedirectResponse
    {
        $mode = $request->input('mode');

        if ($mode === 'completed') {
            $count = CbtSiswa::where('status', 2)->delete();
            return back()->with('success', "Sebanyak {$count} riwayat ujian selesai berhasil dibersihkan.");
        }

        if ($mode === 'logs' && Schema::hasTable('log')) {
            DB::table('log')->truncate();
            return back()->with('success', 'Seluruh riwayat aktivitas (log) berhasil dikosongkan.');
        }

        if ($mode === 'locks') {
            try {
                $keys = Redis::keys('cbt_device_lock:*');
                if (!empty($keys)) {
                    Redis::del($keys);
                }
            } catch (\Throwable $e) {}
            return back()->with('success', 'Seluruh kunci login perangkat berhasil di-reset.');
        }

        return back()->with('error', 'Pilihan pembersihan data tidak valid.');
    }
}
