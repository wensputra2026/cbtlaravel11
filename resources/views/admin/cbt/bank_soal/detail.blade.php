@extends('layouts.admin')

@section('title', 'Detail Butir Soal: ' . $bank->bank_nama)
@section('page_title', 'Kelola Butir Soal - ' . $bank->bank_kode)

@section('content')
<div class="space-y-6" x-data="{ openModalSoal: false, jenisSoal: 1 }">

    <!-- Header Actions & Meta Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('admin.cbt.bank_soal.index') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:text-brand-500 flex items-center gap-1 font-semibold">
                        &larr; Kembali ke Daftar Bank Soal
                    </a>
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ $bank->bank_nama }}</h3>
                <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-1 flex-wrap">
                    <span>Kode: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ $bank->bank_kode }}</strong></span>
                    &bull;
                    <span>Mapel: <strong class="text-slate-800 dark:text-slate-200">{{ $bank->mapel->nama_mapel ?? '-' }}</strong></span>
                    &bull;
                    <span>Tingkat: <strong class="text-slate-800 dark:text-slate-200">Kelas {{ $bank->bank_level }}</strong></span>
                    &bull;
                    <span>Total Butir Terinput: <strong class="text-emerald-600 dark:text-emerald-400">{{ count($soals) }} Soal</strong></span>
                </div>
            </div>
            <div>
                <button @click="openModalSoal = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Butir Soal</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Question List -->
    <div class="space-y-4">
        @forelse($soals as $idx => $soal)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-slate-300 dark:hover:border-slate-700 transition">
                <div class="flex items-start justify-between gap-4 pb-3 border-b border-slate-100 dark:border-slate-800/80 mb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-xs text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700">
                            #{{ $soal->nomor_soal ?? ($idx + 1) }}
                        </span>
                        @php
                            $badgeColor = match((int)$soal->jenis_soal) {
                                1 => 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-500/20',
                                2 => 'bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-200 dark:border-purple-500/20',
                                3 => 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-500/20',
                                4 => 'bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400 border-teal-200 dark:border-teal-500/20',
                                default => 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-500/20',
                            };
                            $badgeLabel = match((int)$soal->jenis_soal) {
                                1 => 'Pilihan Ganda',
                                2 => 'PG Kompleks',
                                3 => 'Menjodohkan',
                                4 => 'Isian Singkat',
                                default => 'Esai / Uraian',
                            };
                        @endphp
                        <span class="px-2.5 py-0.5 rounded text-[11px] font-bold border {{ $badgeColor }}">
                            {{ $badgeLabel }}
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Bobot: {{ $soal->bobot ?? 1 }}</span>
                    </div>
                    <form action="{{ route('admin.cbt.bank_soal.soal.destroy', $soal->id_soal) }}" method="POST" onsubmit="return confirm('Hapus butir soal ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1 text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/20 rounded-lg transition" title="Hapus Butir Soal">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>

                <!-- Konten Soal HTML -->
                <div class="text-sm text-slate-800 dark:text-slate-200 leading-relaxed prose dark:prose-invert max-w-none mb-4">
                    {!! $soal->soal !!}
                </div>

                <!-- Opsi Jawaban untuk PG -->
                @if($soal->jenis_soal == 1 || $soal->jenis_soal == 2)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                        @foreach(['a' => $soal->opsi_a, 'b' => $soal->opsi_b, 'c' => $soal->opsi_c, 'd' => $soal->opsi_d, 'e' => $soal->opsi_e] as $key => $opsi)
                            @if(!empty($opsi))
                                @php
                                    $isKunci = false;
                                    if (is_string($soal->jawaban)) {
                                        $isKunci = strtoupper(trim($soal->jawaban)) === strtoupper(trim($key));
                                    } elseif (is_array($soal->jawaban)) {
                                        $isKunci = in_array(strtoupper(trim($key)), array_map('strtoupper', array_map('trim', array_filter($soal->jawaban, 'is_string'))));
                                    }

                                    $opsiContent = is_array($opsi) ? ($opsi['text'] ?? json_encode($opsi)) : (string)$opsi;
                                @endphp
                                <div class="p-2.5 rounded-xl border flex items-center gap-2.5 {{ $isKunci ? 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300' : 'bg-slate-50 dark:bg-slate-950/60 border-slate-200 dark:border-slate-800/80 text-slate-700 dark:text-slate-300' }}">
                                    <span class="w-5 h-5 rounded flex items-center justify-center font-bold text-[11px] {{ $isKunci ? 'bg-emerald-600 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                        {{ strtoupper($key) }}
                                    </span>
                                    <span class="flex-1">{!! $opsiContent !!}</span>
                                    @if($isKunci)
                                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">&check; Kunci</span>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    @php
                        $jawabanDisplay = '-';
                        if (is_string($soal->jawaban)) {
                            $jawabanDisplay = $soal->jawaban;
                        } elseif (is_array($soal->jawaban)) {
                            $jawabanDisplay = implode(', ', array_map(function($v) {
                                return is_array($v) ? json_encode($v) : (string)$v;
                            }, $soal->jawaban));
                        } elseif (!empty($soal->jawaban)) {
                            $jawabanDisplay = json_encode($soal->jawaban);
                        }
                    @endphp
                    <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-700 dark:text-slate-300">
                        <span class="text-slate-500 dark:text-slate-400 font-semibold">Kunci Jawaban / Pedoman Penilaian:</span>
                        <div class="mt-1 font-mono text-emerald-600 dark:text-emerald-400 font-bold">{{ $jawabanDisplay }}</div>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xl mx-auto mb-3">📝</div>
                <h4 class="font-bold text-slate-800 dark:text-white text-sm">Belum Ada Butir Soal</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Bank soal ini masih kosong. Klik tombol "Tambah Butir Soal" di atas untuk mulai membuat soal.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal Tambah Butir Soal -->
    <div x-show="openModalSoal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="openModalSoal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Tambah Butir Soal Baru</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Pilih jenis soal (PG, Kompleks, Jodohkan, Isian Singkat, atau Esai)</p>
            
            <form action="{{ route('admin.cbt.bank_soal.soal.store', $bank->id_bank) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Soal</label>
                        <select name="jenis_soal" x-model="jenisSoal" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                            <option value="1">1. Pilihan Ganda (PG Biasa)</option>
                            <option value="2">2. Pilihan Ganda Kompleks</option>
                            <option value="3">3. Menjodohkan</option>
                            <option value="4">4. Isian Singkat</option>
                            <option value="5">5. Esai / Uraian</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Bobot Soal</label>
                        <input type="number" step="0.5" name="bobot" value="1.0" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pertanyaan / Narasi Soal</label>
                    <textarea name="soal" rows="4" placeholder="Tuliskan pertanyaan atau deskripsi soal di sini..." required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-sans"></textarea>
                </div>

                <!-- Input Opsi untuk Pilihan Ganda -->
                <div x-show="jenisSoal == 1 || jenisSoal == 2" class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <label class="block text-xs font-bold text-slate-800 dark:text-white mb-2">Pilihan Jawaban (A - E)</label>
                    @foreach(['a', 'b', 'c', 'd', 'e'] as $opt)
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-xs text-brand-600 dark:text-brand-400 shrink-0 uppercase border border-slate-200 dark:border-slate-700">
                                {{ $opt }}
                            </span>
                            <input type="text" name="opsi_{{ $opt }}" placeholder="Teks pilihan {{ strtoupper($opt) }}..." class="flex-1 px-3 py-1.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        </div>
                    @endforeach
                </div>

                <!-- Kunci Jawaban -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kunci Jawaban</label>
                    <input type="text" name="jawaban" placeholder="Untuk PG: A / B / C / D / E. Untuk Isian/Esai: kata kunci." required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white uppercase focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="openModalSoal = false" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30">
                        Simpan Butir Soal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/katex/katex.min.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('vendor/katex/katex.min.js') }}"></script>
<script src="{{ asset('vendor/katex/auto-render.min.js') }}"></script>
<script src="{{ asset('vendor/katex/contrib/mhchem.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof renderMathInElement === 'function') {
        renderMathInElement(document.body, {
            delimiters: [
                { left: '$$', right: '$$', display: true },
                { left: '$', right: '$', display: false },
                { left: '\\(', right: '\\)', display: false },
                { left: '\\[', right: '\\]', display: true }
            ],
            ignoredTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code', 'option', 'input'],
            throwOnError: false
        });
    }
});
</script>
@endpush
