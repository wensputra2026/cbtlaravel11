@extends('layouts.guru')

@section('title', 'Kelola Soal: ' . $bank->bank_nama)
@section('page_title', 'Kelola Butir Soal - ' . $bank->bank_kode)

@section('content')
<div class="space-y-6" x-data="{ openModalSoal: false, jenisSoal: 1 }">

    <!-- Header & Bank Info Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('guru.bank_soal.index') }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 font-semibold">
                        &larr; Kembali ke Bank Soal
                    </a>
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ $bank->bank_nama }}</h3>
                <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-1">
                    <span>Kode: <strong class="text-slate-700 dark:text-slate-200 font-mono">{{ $bank->bank_kode }}</strong></span>
                    &bull;
                    <span>Mapel: <strong class="text-slate-700 dark:text-slate-200">{{ $bank->mapel->nama_mapel ?? '-' }}</strong></span>
                    &bull;
                    <span>Tingkat: <strong class="text-slate-700 dark:text-slate-200">Kelas {{ $bank->bank_level }}</strong></span>
                    &bull;
                    <span>Total Butir: <strong class="text-emerald-600 dark:text-emerald-400">{{ count($soals) }} Soal</strong></span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('guru.bank_soal.import', $bank->id_bank) }}" class="px-4 py-2 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import Soal</span>
                </a>
                <button @click="openModalSoal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/30 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Butir Soal</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Soal List -->
    <div class="space-y-4">
        @forelse($soals as $idx => $soal)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4 pb-3 border-b border-slate-100 dark:border-slate-800/80 mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-xs text-slate-800 dark:text-white">
                            #{{ $soal->nomor_soal ?? ($idx + 1) }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded text-[11px] font-bold border {{ $soal->jenis_soal == 1 || $soal->jenis == 1 ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-500/20' : ($soal->jenis_soal == 5 || $soal->jenis == 5 ? 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-500/20' : 'bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-500/20') }}">
                            @if($soal->jenis_soal == 1 || $soal->jenis == 1)
                                Pilihan Ganda
                            @elseif($soal->jenis_soal == 2 || $soal->jenis == 2)
                                PG Kompleks
                            @elseif($soal->jenis_soal == 3 || $soal->jenis == 3)
                                Menjodohkan
                            @elseif($soal->jenis_soal == 4 || $soal->jenis == 4)
                                Isian Singkat
                            @else
                                Esai / Uraian
                            @endif
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Bobot: {{ $soal->bobot ?? 1 }}</span>
                    </div>
                    <form action="{{ route('guru.bank_soal.delete_soal', $soal->id_soal) }}" method="POST" onsubmit="return confirm('Hapus butir soal nomor #{{ $soal->nomor_soal }}?');" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1 text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/20 rounded-md transition" title="Hapus Soal">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>

                <div class="text-sm text-slate-800 dark:text-slate-200 leading-relaxed max-w-none mb-4">
                    {!! $soal->soal !!}
                </div>

                @if($soal->jenis_soal == 1 || $soal->jenis == 1)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                        @foreach(['a' => $soal->opsi_a, 'b' => $soal->opsi_b, 'c' => $soal->opsi_c, 'd' => $soal->opsi_d, 'e' => $soal->opsi_e] as $key => $opsi)
                            @if(!empty($opsi))
                                <div class="p-2.5 rounded-xl border {{ strtoupper($soal->jawaban) === strtoupper($key) ? 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-300 dark:border-emerald-500/40 text-emerald-800 dark:text-emerald-300 font-semibold' : 'bg-slate-50 dark:bg-slate-950/60 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300' }} flex items-start gap-2">
                                    <span class="w-5 h-5 rounded {{ strtoupper($soal->jawaban) === strtoupper($key) ? 'bg-emerald-600 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }} flex items-center justify-center font-bold text-[10px] shrink-0 uppercase">
                                        {{ $key }}
                                    </span>
                                    <span class="flex-1">{!! $opsi !!}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 text-xs text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <span class="font-bold text-amber-600 dark:text-amber-400">Kunci Jawaban / Rubrik:</span>
                        <span class="font-mono">{{ is_string($soal->jawaban) ? $soal->jawaban : json_encode($soal->jawaban) }}</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xl mx-auto mb-3">📝</div>
                <h4 class="font-bold text-slate-900 dark:text-white text-sm">Belum Ada Butir Soal</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Klik tombol "Tambah Butir Soal" atau "Import Soal" di atas untuk mulai mengisi paket ujian.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal Tambah Soal (Dukungan 5 Tipe Soal Garuda CBT) -->
    <div x-show="openModalSoal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModalSoal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Tambah Butir Soal Baru</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Mendukung 5 jenis butir soal standar Garuda CBT</p>
            
            <form action="{{ route('guru.bank_soal.store_soal', $bank->id_bank) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Butir Soal</label>
                        <select name="jenis_soal" x-model="jenisSoal" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                            <option value="1">1. Pilihan Ganda Biasa (PG)</option>
                            <option value="2">2. Pilihan Ganda Kompleks (Multi Jawaban)</option>
                            <option value="3">3. Menjodohkan (Matching)</option>
                            <option value="4">4. Isian Singkat</option>
                            <option value="5">5. Uraian / Esai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bobot Soal</label>
                        <input type="number" step="0.5" name="bobot" value="1.0" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pertanyaan / Narasi Butir Soal</label>
                    <textarea name="soal" rows="4" placeholder="Tuliskan pertanyaan soal..." required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <!-- Opsi Jawaban (Khusus PG & PG Kompleks) -->
                <div x-show="jenisSoal == 1 || jenisSoal == 2" class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <label class="block text-xs font-bold text-slate-900 dark:text-white mb-2">Pilihan Jawaban (A - E)</label>
                    @foreach(['a', 'b', 'c', 'd', 'e'] as $opt)
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-xs text-emerald-600 dark:text-emerald-400 shrink-0 uppercase">{{ $opt }}</span>
                            <input type="text" name="opsi_{{ $opt }}" placeholder="Teks opsi {{ strtoupper($opt) }}..." class="flex-1 px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                        </div>
                    @endforeach
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kunci Jawaban</label>
                    <input type="text" name="jawaban" placeholder="Contoh PG: A, PG Kompleks: A,C, Isian: kata_kunci, Esai: rubrik penilaian" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 font-mono">
                    <p class="text-[11px] text-slate-500 mt-1">Untuk tipe Pilihan Ganda isikan huruf kunci (misal: A/B/C/D/E).</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModalSoal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-emerald-600/30">Simpan Butir Soal</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
