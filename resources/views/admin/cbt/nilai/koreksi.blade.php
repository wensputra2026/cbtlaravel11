@extends('layouts.admin')

@section('title', 'Koreksi Lembar Jawaban: ' . ($cbtSiswa->siswa->nama ?? 'Siswa'))
@section('page_title', 'Lembar Jawaban & Koreksi Esai')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Student Meta -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('admin.cbt.nilai.index', ['jadwal_id' => $cbtSiswa->id_jadwal]) }}" class="text-xs text-brand-400 hover:text-brand-300 flex items-center gap-1 font-semibold">
                        &larr; Kembali ke Rekap Nilai
                    </a>
                </div>
                <h3 class="text-lg font-black text-slate-900">{{ $cbtSiswa->siswa->nama ?? 'Peserta' }}</h3>
                <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                    <span>NISN: <strong class="text-slate-800 font-mono">{{ $cbtSiswa->siswa->nisn ?? '-' }}</strong></span>
                    &bull;
                    <span>Ujian: <strong class="text-slate-800">{{ $cbtSiswa->jadwal->bankSoal->bank_nama ?? '-' }}</strong></span>
                    &bull;
                    <span>Mulai: <strong class="text-slate-800">{{ $cbtSiswa->mulai ?? '-' }}</strong></span>
                    &bull;
                    <span>Selesai: <strong class="text-slate-800">{{ $cbtSiswa->selesai ?? '-' }}</strong></span>
                </div>
            </div>

            <!-- Score Summary Card -->
            <div class="flex items-center gap-4 bg-white p-4 rounded-xl border border-slate-200">
                <div class="text-center">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block">Nilai PG</span>
                    <span class="font-mono text-xl font-bold text-blue-400">{{ number_format($nilaiInput['pg_nilai'] ?? 0, 1) }}</span>
                </div>
                <div class="w-px h-8 bg-slate-800"></div>
                <div class="text-center">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block">Nilai Esai</span>
                    <span class="font-mono text-xl font-bold text-rose-400">{{ number_format($nilaiInput['essai_nilai'] ?? 0, 1) }}</span>
                </div>
                <div class="w-px h-8 bg-slate-800"></div>
                <div class="text-center">
                    <span class="text-[10px] uppercase font-bold text-brand-400 block">Total Akhir</span>
                    <span class="font-mono text-2xl font-black text-brand-300">
                        {{ number_format(($nilaiInput['pg_nilai'] ?? 0) + ($nilaiInput['essai_nilai'] ?? 0), 1) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Koreksi Esai Form Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <h4 class="font-bold text-sm text-slate-900 mb-2 flex items-center gap-2">
            <span>✏️</span> Input Penilaian Esai / Uraian
        </h4>
        <p class="text-xs text-slate-500 mb-4">Masukkan nilai perolehan esai hasil pemeriksaan manual penguji:</p>

        <form action="{{ route('admin.cbt.nilai.update_koreksi', $cbtSiswa->id_cbt_siswa) }}" method="POST" class="flex items-end gap-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Skor / Nilai Esai</label>
                <input type="number" step="0.1" name="nilai_esai" value="{{ $nilaiInput['essai_nilai'] ?? 0 }}" required class="w-44 px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-mono font-bold text-slate-900 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-brand-600/30 transition">
                Simpan Penilaian Esai
            </button>
        </form>
    </div>

    <!-- Lembar Jawaban Detail Grid -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <h4 class="font-bold text-sm text-slate-900 mb-4 flex items-center gap-2">
            <span>📋</span> Data Lembar Jawaban Peserta
        </h4>

        <div class="space-y-3">
            @if(empty($jawabanList))
                <div class="py-8 text-center text-xs text-slate-500">
                    Tidak ada rekaman jawaban tersimpan untuk peserta ini.
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2">
                    @foreach($jawabanList as $num => $ans)
                        <div class="p-3 bg-white border border-slate-200 rounded-xl">
                            <div class="text-[10px] text-slate-500 font-semibold mb-1">Nomor #{{ $num }}</div>
                            <div class="font-mono text-sm font-bold text-brand-300 truncate">
                                {{ is_array($ans) ? json_encode($ans) : ($ans ?: '-') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
