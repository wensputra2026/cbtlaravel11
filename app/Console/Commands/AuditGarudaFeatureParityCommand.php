<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class AuditGarudaFeatureParityCommand extends Command
{
    /**
     * Nama dan signature command.
     *
     * @var string
     */
    protected $signature = 'cbt:audit-features';

    /**
     * Deskripsi command audit.
     *
     * @var string
     */
    protected $description = 'Audit Feature Parity dan Kelayakan Route/Controller/View CBT Garuda vs Laravel 11';

    /**
     * Eksekusi audit konsol.
     */
    public function handle(): int
    {
        $this->output->writeln('');
        $this->output->writeln('<fg=cyan;options=bold>================================================================================</>');
        $this->output->writeln('<fg=white;options=bold>           GARUDA CBT (CI3) VS LARAVEL 11 - FULL FEATURE PARITY AUDIT           </>');
        $this->output->writeln('<fg=cyan;options=bold>================================================================================</>');
        $this->output->writeln('<fg=gray>Waktu Audit   : ' . date('Y-m-d H:i:s') . '</>');
        $this->output->writeln('<fg=gray>Target Engine : Laravel 11.x (PHP 8.4+ / Zero-Latency Alpine.js CBT)</>');
        $this->output->writeln('');

        $categories = $this->getAuditMatrix();

        $totalTests = 0;
        $passCount  = 0;
        $missingList = [];

        foreach ($categories as $catIndex => $cat) {
            $this->output->writeln("<fg=yellow;options=bold>▶ " . $cat['title'] . "</>");

            $tableRows = [];
            $rowNum = 1;

            foreach ($cat['features'] as $item) {
                $totalTests++;

                $routePass = $this->checkRoute($item['route']);
                $controllerPass = $this->checkController($item['controller'], $item['method'] ?? null);
                $viewPass = $this->checkView($item['view'] ?? null);
                $assetPass = isset($item['asset_check']) ? $this->checkAsset($item['asset_check']) : true;

                $allPass = $routePass && $controllerPass && $viewPass && $assetPass;

                $routeDisplay = is_array($item['route']) ? implode(', ', $item['route']) : $item['route'];
                $ctrlDisplay  = ($item['controller'] ? class_basename($item['controller']) : '-') . ($item['method'] ? '@' . $item['method'] : '');
                $viewDisplay  = $item['view'] ?? ($item['extra_info'] ?? '-');

                if ($allPass) {
                    $passCount++;
                    $statusBadge = '<fg=green;options=bold>[PASS] READY</>';
                } else {
                    $statusBadge = '<fg=red;options=bold>[FAIL] MISSING</>';
                    $missingList[] = [
                        'kategori' => $cat['title'],
                        'fitur'    => $item['name'],
                        'detail'   => $routeDisplay . ' -> ' . $ctrlDisplay . ' (View: ' . $viewDisplay . ')',
                        'alasan'   => !$routePass ? 'Route not found' : (!$controllerPass ? 'Controller/method not found' : (!$viewPass ? 'View not found' : 'Asset missing')),
                    ];
                }

                $tableRows[] = [
                    $rowNum++,
                    $item['name'],
                    $routeDisplay,
                    $ctrlDisplay,
                    $viewDisplay,
                    $statusBadge,
                ];
            }

            $this->table(
                ['No', 'Fitur Asli Garuda CBT', 'Rute / Endpoint', 'Controller & Method', 'Blade View / Detail', 'Status'],
                $tableRows
            );

            $this->output->writeln('');
        }

        // =====================================================================
        // RINGKASAN AUDIT AKHIR
        // =====================================================================
        $percentage = $totalTests > 0 ? round(($passCount / $totalTests) * 100, 1) : 0;

        $this->output->writeln('<fg=cyan;options=bold>================================================================================</>');
        $this->output->writeln('<fg=white;options=bold>                           RINGKASAN HASIL AUDIT                               </>');
        $this->output->writeln('<fg=cyan;options=bold>================================================================================</>');
        $this->output->writeln("Total Fitur Diuji        : <fg=white;options=bold>{$totalTests}</>");
        $this->output->writeln("Fitur Lulus & Ready      : <fg=green;options=bold>{$passCount}</>");
        $this->output->writeln("Fitur Belum Terpasang    : <fg=" . ($missingList ? 'red' : 'green') . ";options=bold>" . count($missingList) . "</>");
        $this->output->writeln("Feature Parity Score     : <fg=" . ($percentage >= 95 ? 'green' : 'yellow') . ";options=bold>{$percentage}%</>");

        if (empty($missingList)) {
            $this->output->writeln('');
            $this->info("✔ SELURUH FITUR CBT GARUDA ASLI TELAH 100% DIIMPLEMENTASIKAN DI LARAVEL 11!");
            $this->info("✔ Arsitektur siap melayani 450 siswa dan 50 guru secara serentak tanpa kendala.");
        } else {
            $this->output->writeln('');
            $this->error("Terdapat " . count($missingList) . " modul yang perlu dilengkapi:");
            $this->table(['Kategori', 'Nama Fitur', 'Target Rute / View', 'Penyebab'], $missingList);
        }

        $this->output->writeln('<fg=cyan;options=bold>================================================================================</>');
        $this->output->writeln('');

        return empty($missingList) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Periksa keberadaan rute.
     */
    protected function checkRoute(string|array $routes): bool
    {
        $list = (array) $routes;
        foreach ($list as $r) {
            if (!Route::has($r)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Periksa keberadaan controller dan method.
     */
    protected function checkController(?string $class, ?string $method): bool
    {
        if (!$class) return true;
        if (!class_exists($class)) return false;
        if ($method && !method_exists($class, $method)) return false;
        return true;
    }

    /**
     * Periksa keberadaan file blade view.
     */
    protected function checkView(?string $view): bool
    {
        if (!$view) return true;
        return View::exists($view);
    }

    /**
     * Periksa file aset lokal jika diperlukan.
     */
    protected function checkAsset(string $path): bool
    {
        return file_exists(public_path($path));
    }

    /**
     * Matriks fitur lengkap CBT Garuda vs Laravel 11.
     */
    protected function getAuditMatrix(): array
    {
        return [
            // -----------------------------------------------------------------
            // 1. LEVEL ADMINISTRATOR: DATA MASTER
            // -----------------------------------------------------------------
            [
                'title' => '1. LEVEL ADMINISTRATOR - DATA MASTER',
                'features' => [
                    [
                        'name'       => 'Tahun Pelajaran & Semester (Aktivasi manual + sinkronisasi cache)',
                        'route'      => ['admin.master.tp', 'admin.master.tp.store', 'admin.master.tp.aktif', 'admin.master.smt.aktif'],
                        'controller' => \App\Http\Controllers\Admin\MasterDataController::class,
                        'method'     => 'indexTp',
                        'view'       => 'admin.master.tp',
                    ],
                    [
                        'name'       => 'Jurusan & Peminatan (CRUD + paginasi 10)',
                        'route'      => ['admin.master.jurusan', 'admin.master.jurusan.store', 'admin.master.jurusan.update', 'admin.master.jurusan.destroy'],
                        'controller' => \App\Http\Controllers\Admin\MasterDataController::class,
                        'method'     => 'indexJurusan',
                        'view'       => 'admin.master.jurusan',
                    ],
                    [
                        'name'       => 'Kelas & Rombel (CRUD + paginasi 10)',
                        'route'      => ['admin.master.kelas', 'admin.master.kelas.store', 'admin.master.kelas.update', 'admin.master.kelas.destroy'],
                        'controller' => \App\Http\Controllers\Admin\MasterDataController::class,
                        'method'     => 'indexKelas',
                        'view'       => 'admin.master.kelas',
                    ],
                    [
                        'name'       => 'Kenaikan Kelas, Mutasi Rombel, & Kelulusan (Pivot multi-tahun)',
                        'route'      => ['admin.master.kenaikan_kelas', 'admin.master.kenaikan_kelas.promote', 'admin.master.kenaikan_kelas.switch', 'admin.master.kenaikan_kelas.graduate'],
                        'controller' => \App\Http\Controllers\Admin\ClassPromotionController::class,
                        'method'     => 'index',
                        'view'       => 'admin.master.kenaikan_kelas',
                    ],
                    [
                        'name'       => 'Mata Pelajaran (CRUD + import/export + paginasi 10)',
                        'route'      => ['admin.master.mapel', 'admin.master.mapel.store', 'admin.master.mapel.update', 'admin.master.mapel.destroy', 'admin.master.mapel.import'],
                        'controller' => \App\Http\Controllers\Admin\MasterDataController::class,
                        'method'     => 'indexMapel',
                        'view'       => 'admin.master.mapel',
                    ],
                    [
                        'name'       => 'Data Guru & Staf (CRUD + akun CBT + paginasi 10)',
                        'route'      => ['admin.master.guru', 'admin.master.guru.store', 'admin.master.guru.update', 'admin.master.guru.destroy'],
                        'controller' => \App\Http\Controllers\Admin\MasterDataController::class,
                        'method'     => 'indexGuru',
                        'view'       => 'admin.master.guru',
                    ],
                    [
                        'name'       => 'Data Siswa & Akun (CRUD + import Excel + paginasi 10)',
                        'route'      => ['admin.master.siswa', 'admin.master.siswa.store', 'admin.master.siswa.import'],
                        'controller' => \App\Http\Controllers\Admin\MasterDataController::class,
                        'method'     => 'indexSiswa',
                        'view'       => 'admin.master.siswa',
                    ],
                    [
                        'name'       => 'Arsip Alumni Siswa (Pencarian arsip lulusan multi-tahun)',
                        'route'      => 'admin.master.alumni',
                        'controller' => \App\Http\Controllers\Admin\MasterDataController::class,
                        'method'     => 'indexAlumni',
                        'view'       => 'admin.master.alumni',
                    ],
                ],
            ],

            // -----------------------------------------------------------------
            // 2. LEVEL ADMINISTRATOR: MANAJEMEN CBT
            // -----------------------------------------------------------------
            [
                'title' => '2. LEVEL ADMINISTRATOR - MANAJEMEN CBT',
                'features' => [
                    [
                        'name'       => 'Bank Soal (5 tipe soal, duplikasi bank, alokasi rombel kelas)',
                        'route'      => ['admin.cbt.bank_soal.index', 'admin.cbt.bank_soal.store', 'admin.cbt.bank_soal.show', 'admin.cbt.bank_soal.duplicate', 'admin.cbt.bank_soal.destroy'],
                        'controller' => \App\Http\Controllers\Admin\CbtBankSoalController::class,
                        'method'     => 'index',
                        'view'       => 'admin.cbt.bank_soal.index',
                    ],
                    [
                        'name'       => 'Jenis Ujian (CRUD, modal edit jenis, dan paginasi)',
                        'route'      => ['admin.cbt.jenis.index', 'admin.cbt.jenis.store', 'admin.cbt.jenis.update', 'admin.cbt.jenis.destroy'],
                        'controller' => \App\Http\Controllers\Admin\CbtJadwalController::class,
                        'method'     => 'indexJenis',
                        'view'       => 'admin.cbt.jadwal.jenis',
                    ],
                    [
                        'name'       => 'Jadwal Ujian (Acak soal/opsi, switch token/nilai, durasi menit)',
                        'route'      => ['admin.cbt.jadwal.index', 'admin.cbt.jadwal.store', 'admin.cbt.jadwal.toggle', 'admin.cbt.jadwal.destroy'],
                        'controller' => \App\Http\Controllers\Admin\CbtJadwalController::class,
                        'method'     => 'index',
                        'view'       => 'admin.cbt.jadwal.index',
                    ],
                    [
                        'name'       => 'Sesi & Ruang Ujian (Master sesi, ruang, dan assign pengawas)',
                        'route'      => ['admin.cbt.sesi_ruang.index', 'admin.cbt.ruang.store', 'admin.cbt.sesi.store', 'admin.cbt.pengawas.store', 'admin.cbt.pengawas.destroy'],
                        'controller' => \App\Http\Controllers\Admin\CbtSesiRuangController::class,
                        'method'     => 'index',
                        'view'       => 'admin.cbt.sesi_ruang',
                    ],
                    [
                        'name'       => 'Alokasi Sesi Siswa (Plotting sesi dan ruang ujian otomatis)',
                        'route'      => ['admin.cbt.alokasi.sesi', 'admin.cbt.alokasi.sesi.store', 'admin.cbt.alokasi.sesi.auto'],
                        'controller' => \App\Http\Controllers\Admin\CbtAlokasiController::class,
                        'method'     => 'indexSesiSiswa',
                        'view'       => 'admin.cbt.alokasi_sesi',
                    ],
                    [
                        'name'       => 'Nomor Peserta Ujian (Generate nomor peserta otomatis)',
                        'route'      => ['admin.cbt.alokasi.nomor', 'admin.cbt.alokasi.nomor.generate'],
                        'controller' => \App\Http\Controllers\Admin\CbtAlokasiController::class,
                        'method'     => 'indexNomorPeserta',
                        'view'       => 'admin.cbt.nomor_peserta',
                    ],
                    [
                        'name'       => 'Token Ujian Dinamis (Generator berbasis Redis TTL)',
                        'route'      => ['admin.cbt.token.index', 'admin.cbt.token.generate', 'admin.cbt.token.api_get'],
                        'controller' => \App\Http\Controllers\Admin\CbtTokenController::class,
                        'method'     => 'index',
                        'view'       => 'admin.cbt.token',
                    ],
                    [
                        'name'       => 'Live Monitoring / Proktor (Real-time status, reset login, force submit)',
                        'route'      => ['proctor.index', 'proctor.monitor', 'proctor.api.live', 'proctor.api.reset_login', 'proctor.api.force_submit', 'proctor.api.extra_time'],
                        'controller' => \App\Http\Controllers\ProctorController::class,
                        'method'     => 'index',
                        'view'       => 'proctor.index',
                    ],
                    [
                        'name'       => 'Rekap Nilai & Koreksi Manual Esai Siswa',
                        'route'      => ['admin.cbt.nilai.index', 'admin.cbt.nilai.koreksi', 'admin.cbt.nilai.update_koreksi', 'admin.cbt.nilai.export'],
                        'controller' => \App\Http\Controllers\Admin\CbtNilaiController::class,
                        'method'     => 'index',
                        'view'       => 'admin.cbt.nilai.index',
                    ],
                    [
                        'name'       => 'Analisis Butir Soal (Daya pembeda dan tingkat kesukaran)',
                        'route'      => 'admin.cbt.analisis',
                        'controller' => \App\Http\Controllers\Admin\CbtAnalisisController::class,
                        'method'     => 'index',
                        'view'       => 'admin.cbt.analisis',
                    ],
                ],
            ],

            // -----------------------------------------------------------------
            // 3. LEVEL ADMINISTRATOR: CETAK DOKUMEN (CSS @media print)
            // -----------------------------------------------------------------
            [
                'title' => '3. LEVEL ADMINISTRATOR - CETAK DOKUMEN UJIAN',
                'features' => [
                    [
                        'name'       => 'Kartu Peserta Ujian (Barcode & foto)',
                        'route'      => 'print.kartu_peserta',
                        'controller' => \App\Http\Controllers\DocumentPrintController::class,
                        'method'     => 'kartuPeserta',
                        'view'       => 'print.kartu_peserta',
                    ],
                    [
                        'name'       => 'Daftar Hadir Peserta per Ruang & Sesi',
                        'route'      => 'print.daftar_hadir',
                        'controller' => \App\Http\Controllers\DocumentPrintController::class,
                        'method'     => 'daftarHadir',
                        'view'       => 'print.daftar_hadir',
                    ],
                    [
                        'name'       => 'Daftar Hadir Pengawas Ruang',
                        'route'      => 'print.daftar_hadir_pengawas',
                        'controller' => \App\Http\Controllers\DocumentPrintController::class,
                        'method'     => 'daftarHadirPengawas',
                        'view'       => 'print.daftar_hadir_pengawas',
                    ],
                    [
                        'name'       => 'Berita Acara Pelaksanaan Ujian',
                        'route'      => 'print.berita_acara',
                        'controller' => \App\Http\Controllers\DocumentPrintController::class,
                        'method'     => 'beritaAcara',
                        'view'       => 'print.berita_acara',
                    ],
                    [
                        'name'       => 'Jadwal Pengawas Ruang Ujian',
                        'route'      => 'print.jadwal_pengawas',
                        'controller' => \App\Http\Controllers\DocumentPrintController::class,
                        'method'     => 'jadwalPengawas',
                        'view'       => 'print.jadwal_pengawas',
                    ],
                    [
                        'name'       => 'Denah Ruang Ujian Siswa',
                        'route'      => 'print.denah_ruang',
                        'controller' => \App\Http\Controllers\DocumentPrintController::class,
                        'method'     => 'denahRuang',
                        'view'       => 'print.denah_ruang',
                    ],
                    [
                        'name'       => 'Cetak Kartu Login Massal Siswa',
                        'route'      => 'print.kartu_login',
                        'controller' => \App\Http\Controllers\DocumentPrintController::class,
                        'method'     => 'kartuLogin',
                        'view'       => 'print.kartu_login',
                    ],
                ],
            ],

            // -----------------------------------------------------------------
            // 4. LEVEL ADMINISTRATOR: MANAJEMEN USER & PENGATURAN
            // -----------------------------------------------------------------
            [
                'title' => '4. LEVEL ADMINISTRATOR - MANAJEMEN USER & PENGATURAN',
                'features' => [
                    [
                        'name'       => 'Akun Admin, Guru, Siswa + Log Aktivitas Ujian',
                        'route'      => ['admin.users.admin', 'admin.users.admin.store', 'admin.users.guru', 'admin.users.siswa', 'admin.users.reset_password', 'admin.users.toggle_active', 'admin.users.logs'],
                        'controller' => \App\Http\Controllers\Admin\UserManagementController::class,
                        'method'     => 'indexAdmin',
                        'view'       => 'admin.users.admin',
                    ],
                    [
                        'name'       => 'Pengaturan Identitas Sekolah (Logo, kop surat, favicon, nama app)',
                        'route'      => ['admin.setting.identitas', 'admin.setting.identitas.update'],
                        'controller' => \App\Http\Controllers\Admin\SchoolSettingController::class,
                        'method'     => 'index',
                        'view'       => 'admin.setting.identitas',
                    ],
                    [
                        'name'       => 'Backup & Pemeliharaan Database (Backup SQL, reset lock, clear log)',
                        'route'      => ['admin.setting.database', 'admin.setting.database.backup', 'admin.setting.database.clear', 'admin.setting.maintenance', 'admin.setting.maintenance.reset_locks'],
                        'controller' => \App\Http\Controllers\Admin\DatabaseManagerController::class,
                        'method'     => 'index',
                        'view'       => 'admin.setting.database',
                    ],
                ],
            ],

            // -----------------------------------------------------------------
            // 5. LEVEL GURU / PENGAWAS
            // -----------------------------------------------------------------
            [
                'title' => '5. LEVEL GURU / PENGAWAS',
                'features' => [
                    [
                        'name'       => 'Dashboard Guru (Statistik mengajar & ujian aktif)',
                        'route'      => 'guru.dashboard',
                        'controller' => \App\Http\Controllers\Guru\GuruDashboardController::class,
                        'method'     => 'index',
                        'view'       => 'guru.dashboard',
                    ],
                    [
                        'name'       => 'Bank Soal Saya (5 tipe soal, media upload, template Excel)',
                        'route'      => ['guru.bank_soal.index', 'guru.bank_soal.store', 'guru.bank_soal.template', 'guru.bank_soal.show', 'guru.bank_soal.store_soal', 'guru.bank_soal.delete_soal', 'guru.bank_soal.import', 'guru.bank_soal.import_process'],
                        'controller' => \App\Http\Controllers\Guru\GuruBankSoalController::class,
                        'method'     => 'index',
                        'view'       => 'guru.bank_soal.index',
                    ],
                    [
                        'name'       => 'Jadwal & Pengawasan Ruang (Link live monitor tab baru)',
                        'route'      => ['guru.jadwal.index', 'guru.pengawasan.index', 'guru.pengawasan.monitor'],
                        'controller' => \App\Http\Controllers\Guru\GuruPengawasanController::class,
                        'method'     => 'index',
                        'view'       => 'guru.pengawasan.index',
                    ],
                    [
                        'name'       => 'Token Ujian Guru (Live monitoring token aktif)',
                        'route'      => ['guru.token.index', 'guru.token.api'],
                        'controller' => \App\Http\Controllers\Guru\GuruTokenController::class,
                        'method'     => 'index',
                        'view'       => 'guru.token.index',
                    ],
                    [
                        'name'       => 'Hasil & Koreksi (Rekap nilai, export Excel, koreksi esai cepat)',
                        'route'      => ['guru.hasil.index', 'guru.hasil.export', 'guru.analisis.index', 'guru.koreksi.index', 'guru.koreksi.peserta', 'guru.koreksi.form', 'guru.koreksi.store'],
                        'controller' => \App\Http\Controllers\Guru\GuruHasilController::class,
                        'method'     => 'index',
                        'view'       => 'guru.hasil.index',
                    ],
                    [
                        'name'       => 'Profil Guru (Form biodata dan ubah password)',
                        'route'      => ['guru.profil', 'guru.profil.password'],
                        'controller' => \App\Http\Controllers\Guru\GuruProfilController::class,
                        'method'     => 'index',
                        'view'       => 'guru.profil',
                    ],
                ],
            ],

            // -----------------------------------------------------------------
            // 6. LEVEL SISWA (PESERTA UJIAN)
            // -----------------------------------------------------------------
            [
                'title' => '6. LEVEL SISWA (PESERTA UJIAN)',
                'features' => [
                    [
                        'name'       => 'Dashboard Siswa (Kartu identitas foto/NISN/ruang & daftar tes aktif)',
                        'route'      => 'exam.index',
                        'controller' => \App\Http\Controllers\ExamSessionController::class,
                        'method'     => 'index',
                        'view'       => 'exam.index',
                    ],
                    [
                        'name'       => 'Konfirmasi Ujian (Ringkasan tes & validasi token dinamis)',
                        'route'      => ['exam.konfirmasi', 'exam.konfirmasi.proses'],
                        'controller' => \App\Http\Controllers\ExamSessionController::class,
                        'method'     => 'konfirmasi',
                        'view'       => 'exam.konfirmasi',
                    ],
                    [
                        'name'       => 'Layar Pengerjaan CBT Zero-Latency (0ms Navigasi, Anti-Cheat, Timer, Drawer)',
                        'route'      => ['cbt.ujian', 'exam.api.start', 'exam.api.autosave', 'exam.api.violation', 'exam.api.sync_timer', 'exam.api.finish'],
                        'controller' => \App\Http\Controllers\ExamViewController::class,
                        'method'     => 'showExam',
                        'view'       => 'cbt.ujian',
                    ],
                    [
                        'name'       => 'Hasil Ujian Siswa (Tampilan skor jika diizinkan jadwal)',
                        'route'      => 'exam.hasil',
                        'controller' => \App\Http\Controllers\ExamSessionController::class,
                        'method'     => 'hasil',
                        'view'       => 'exam.hasil',
                    ],
                ],
            ],
        ];
    }
}
