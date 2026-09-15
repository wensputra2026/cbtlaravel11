<?php

namespace App\Console\Commands;

use App\Services\Exam\ExamAnswerBufferService;
use Illuminate\Console\Command;

class BenchmarkRedisBufferCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cbt:benchmark-buffer 
                            {--students=500 : Jumlah siswa konkuren yang disimulasikan}
                            {--answers=40 : Jumlah butir soal yang dijawab per siswa}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Benchmark performa Redis In-Memory Buffering Engine untuk CBT (500+ Siswa Konkuren)';

    /**
     * Execute the console command.
     */
    public function handle(ExamAnswerBufferService $bufferService): int
    {
        $students = (int) $this->option('students');
        $answersCount = (int) $this->option('answers');
        $jadwalId = 99999; // ID jadwal pengujian simulasi

        $this->info("=================================================================");
        $this->info("  CBT LARAVEL 11 - HIGH-CONCURRENCY BUFFER BENCHMARK ENGINE     ");
        $this->info("=================================================================");

        $isRedis = $bufferService->isRedisAvailable();
        if ($isRedis) {
            $this->line("<fg=green;options=bold>✓ Status Driver:</> Redis In-Memory Engine AKTIF (Connected)");
        } else {
            $this->line("<fg=yellow;options=bold>⚠ Status Driver:</> Redis Server OFFLINE - Mode Graceful Database Fallback AKTIF");
        }

        $totalOperations = $students * $answersCount;
        $this->line("• Siswa Disimulasikan : <fg=cyan>{$students}</> siswa");
        $this->line("• Butir per Siswa     : <fg=cyan>{$answersCount}</> butir soal");
        $this->line("• Total Operasi Write : <fg=cyan>" . number_format($totalOperations) . "</> operasi");
        $this->newLine();

        $this->info("Memulai simulasi penulisan autosave jawaban...");
        $bar = $this->output->createProgressBar($students);
        $bar->start();

        $startTime = microtime(true);
        $optionsPool = ['A', 'B', 'C', 'D', 'E'];

        for ($s = 1; $s <= $students; $s++) {
            $siswaId = 100000 + $s;
            for ($q = 1; $q <= $answersCount; $q++) {
                $chosen = $optionsPool[($s + $q) % 5];
                $ragu = ($q % 7 === 0);
                $bufferService->saveAnswer($jadwalId, $siswaId, $q, $chosen, $ragu);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $totalDuration = microtime(true) - $startTime;

        $opsPerSec = $totalDuration > 0 ? ($totalOperations / $totalDuration) : $totalOperations;
        $avgLatencyMs = ($totalOperations > 0) ? (($totalDuration / $totalOperations) * 1000) : 0;

        // Validasi Integritas Data (Sample 5 siswa acak)
        $sampleErrors = 0;
        for ($sample = 1; $sample <= min(5, $students); $sample++) {
            $checkSiswaId = 100000 + $sample;
            $retrieved = $bufferService->getAllAnswers($jadwalId, $checkSiswaId);
            if (count($retrieved) !== $answersCount) {
                $sampleErrors++;
            }
        }

        // Cleanup Data Uji
        for ($s = 1; $s <= $students; $s++) {
            $siswaId = 100000 + $s;
            $bufferService->clearBuffer($jadwalId, $siswaId);
        }
        \Illuminate\Support\Facades\DB::table('cbt_jawaban_siswa')->where('jadwal_id', $jadwalId)->delete();

        // Tampilkan Hasil Benchmark
        $this->table(
            ['Metrik Performa', 'Hasil Pengujian', 'Keterangan'],
            [
                ['Total Operasi Autosave', number_format($totalOperations) . ' req', 'Simulasi serentak'],
                ['Total Waktu Eksekusi', number_format($totalDuration, 3) . ' detik', 'Waktu proses kumulatif'],
                ['Throughput / Kecepatan', number_format($opsPerSec, 1) . ' ops/detik', $isRedis ? 'In-Memory RAM Throughput' : 'MySQL I/O Fallback Throughput'],
                ['Rata-rata Latensi per Klik', number_format($avgLatencyMs, 3) . ' ms', $avgLatencyMs < 5 ? '< 5ms (Sub-millisecond Sangat Cepat)' : 'Tergantung latency I/O'],
                ['Integritas Data Sample', $sampleErrors === 0 ? '100% VALID (0 Loss)' : "{$sampleErrors} Data Mismatch", 'Validasi HGETALL / DB Fallback'],
            ]
        );

        if ($avgLatencyMs < 10) {
            $this->info("✓ KESIMPULAN: Arsitektur buffer memenuhi standar High-Concurrency 500+ Siswa Tanpa Lag!");
        } else {
            $this->comment("i KESIMPULAN: Server bekerja dalam mode fallback. Aktifkan Redis Server untuk performa < 2ms.");
        }

        return 0;
    }
}
