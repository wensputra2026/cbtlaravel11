@extends('layouts.admin')

@section('title', ($soal ? 'Edit Butir Soal #' . $soal->nomor_soal : 'Buat Butir Soal Baru') . ' | ' . $bank->bank_nama)
@section('page_title', ($soal ? 'Edit Butir Soal #' . $soal->nomor_soal : 'Buat Butir Soal') . ' - ' . $bank->bank_kode)

@section('content')
@php
    $isEdit = (bool)$soal;
    $currJenis = old('jenis_soal', $soal->jenis_soal ?? 1);
    $currJawaban = old('jawaban', $soal->jawaban ?? 'A');
    if (is_string($currJawaban) && ($currJenis == 2 || str_starts_with($currJawaban, '['))) {
        $currJawaban = json_decode($currJawaban, true) ?? [$currJawaban];
    }
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/katex/katex.min.css') }}">
@endpush

<div class="space-y-6" x-data="{ 
    jenisSoal: {{ (int)$currJenis }},
    bobot: '{{ old('bobot', $soal->bobot ?? '1.00') }}',
    soalText: @json(old('soal', $soal->soal ?? '')),
    renderKaTeX() {
        this.$nextTick(() => {
            const previewEl = document.getElementById('katexEditorPreview');
            if (previewEl && typeof renderMathInElement === 'function') {
                try {
                    renderMathInElement(previewEl, {
                        delimiters: [
                            { left: '$$', right: '$$', display: true },
                            { left: '$', right: '$', display: false },
                            { left: '\\(', right: '\\)', display: false },
                            { left: '\\[', right: '\\]', display: true }
                        ],
                        ignoredTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code', 'option'],
                        throwOnError: false
                    });
                } catch (e) {
                    console.warn(e);
                }
            }
        });
    }
}" x-init="renderKaTeX()">

    <!-- Breadcrumb & Header Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.cbt.bank_soal.show', $bank->id_bank) }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1.5 font-semibold mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Detail Bank Soal</span>
                </a>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">
                    {{ $isEdit ? 'Edit Butir Soal #' . $soal->nomor_soal : 'Tambah Butir Soal Baru' }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Paket: <strong class="text-slate-800 dark:text-slate-200">{{ $bank->bank_nama }}</strong> ({{ $bank->bank_kode }}) &bull; 
                    Mapel: <strong class="text-slate-800 dark:text-slate-200">{{ $bank->mapel->nama_mapel ?? '-' }}</strong> &bull; 
                    Tingkat: <strong class="text-slate-800 dark:text-slate-200">Kelas {{ $bank->bank_level }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-brand-50 dark:bg-brand-600/20 text-brand-600 dark:text-brand-300 border border-brand-200 dark:border-brand-500/30">
                    Mode: {{ $isEdit ? 'Perbarui Butir' : 'Input Baru' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Main Editor Form -->
    <form action="{{ $isEdit ? route('admin.cbt.bank_soal.update_soal', $soal->id_soal) : route('admin.cbt.bank_soal.store_soal', $bank->id_bank) }}" method="POST" class="space-y-6">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <!-- Card 1: Pengaturan Butir Soal -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <h4 class="text-sm font-bold text-slate-900 dark:text-white pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                <span class="text-brand-600">⚙️</span>
                <span>Tipe Soal & Bobot Nilai</span>
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Jenis Soal (5 Tipe Garuda CBT) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Model / Tipe Soal</label>
                    <select name="jenis_soal" x-model.number="jenisSoal" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="1">1. Pilihan Ganda Biasa (1 Kunci)</option>
                        <option value="2">2. Pilihan Ganda Kompleks (Multi Kunci)</option>
                        <option value="3">3. Menjodohkan (Matching Pairs)</option>
                        <option value="4">4. Isian Singkat</option>
                        <option value="5">5. Esai / Uraian Bebas</option>
                    </select>
                </div>

                <!-- Nomor Soal -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nomor Urut Soal</label>
                    <input type="number" name="nomor_soal" value="{{ old('nomor_soal', $nextNomor) }}" min="1" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white font-mono focus:outline-none focus:border-brand-500">
                </div>

                <!-- Bobot Nilai -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Bobot Nilai Butir</label>
                    <input type="number" step="0.1" name="bobot" x-model="bobot" min="0.1" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white font-mono focus:outline-none focus:border-brand-500">
                </div>
            </div>
        </div>

        <!-- Card 2: Konten Pertanyaan Soal -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <h4 class="text-sm font-bold text-slate-900 dark:text-white pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                <span class="text-amber-500">📝</span>
                <span>Isi Teks Soal & Stimulus</span>
            </h4>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Pertanyaan / Narasi / Stimulus (Mendukung Format HTML & KaTeX LaTeX)</label>
                <textarea name="soal" 
                          rows="6" 
                          required 
                          x-model="soalText"
                          @input="renderKaTeX()"
                          placeholder="Tuliskan pertanyaan atau stimulus ujian di sini. Contoh rumus: $f(x) = \frac{a}{b}$ atau $$E = mc^2$$" 
                          class="w-full p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-sans leading-relaxed">{{ old('soal', $soal->soal ?? '') }}</textarea>
                <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 block">
                    Tip: Ketik rumus matematika dengan tanda dollar <code>$rumus$</code> (inline) atau <code>$$rumus$$</code> (display block), atau tag gambar <code>&lt;img src="..."&gt;</code>.
                </span>

                <!-- Live Preview KaTeX Native Box -->
                <div class="mt-3 p-4 bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                            Pratinjau Rumus Matematika (KaTeX Native Offline):
                        </span>
                        <span class="text-[10px] text-brand-600 dark:text-brand-400 font-medium">100% Offline KaTeX Engine</span>
                    </div>
                    <div id="katexEditorPreview" 
                         class="prose dark:prose-invert max-w-none text-xs text-slate-800 dark:text-slate-200 min-h-[48px] p-3 bg-white dark:bg-slate-900 rounded-lg border border-slate-200/80 dark:border-slate-800/80 leading-relaxed" 
                         x-html="soalText || '<em class=\'text-slate-400\'>Ketik pertanyaan atau rumus LaTeX di atas untuk melihat pratinjau instan...</em>'"></div>
                </div>
            </div>
        </div>

        <!-- Card 3: Pilihan Jawaban & Kunci (Dinamis Berdasarkan Tipe) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <h4 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="text-emerald-500">🎯</span>
                    <span>Opsi Jawaban & Penetapan Kunci</span>
                </h4>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20" x-text="jenisSoal === 1 ? 'Pilihan Ganda (Tunggal)' : (jenisSoal === 2 ? 'Kompleks (Banyak Kunci)' : (jenisSoal === 3 ? 'Menjodohkan' : (jenisSoal === 4 ? 'Isian Eksak' : 'Esai')))">
                </span>
            </div>

            <!-- Bagian 1 & 2: Opsi A - E (Untuk PG Biasa & PG Kompleks) -->
            <div x-show="jenisSoal === 1 || jenisSoal === 2" class="space-y-3">
                @foreach(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E'] as $key => $letter)
                    @php
                        $opsiVal = $soal ? ($soal->{'opsi_' . $key} ?? '') : '';
                        if (is_array($opsiVal)) {
                            $opsiVal = $opsiVal['text'] ?? json_encode($opsiVal);
                        }
                        $isKeySelected = false;
                        if ($isEdit) {
                            if (is_array($currJawaban)) {
                                $isKeySelected = in_array(strtoupper($key), array_map('strtoupper', array_map('trim', $currJawaban)));
                            } else {
                                $isKeySelected = (strtoupper(trim((string)$currJawaban)) === strtoupper($key));
                            }
                        } elseif ($key === 'a') {
                            $isKeySelected = true;
                        }
                    @endphp
                    <div class="p-3 bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 rounded-xl flex items-start gap-3">
                        <div class="pt-2 flex items-center gap-2 shrink-0">
                            <!-- Radio untuk PG Biasa -->
                            <template x-if="jenisSoal === 1">
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="radio" name="jawaban" value="{{ $key }}" {{ ($isKeySelected && (int)$currJenis === 1) ? 'checked' : '' }} class="text-emerald-600 focus:ring-0">
                                    <span class="w-6 h-6 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center">{{ $letter }}</span>
                                </label>
                            </template>

                            <!-- Checkbox untuk PG Kompleks -->
                            <template x-if="jenisSoal === 2">
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox" name="jawaban[]" value="{{ $key }}" {{ ($isKeySelected && (int)$currJenis === 2) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-0">
                                    <span class="w-6 h-6 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center">{{ $letter }}</span>
                                </label>
                            </template>
                        </div>
                        <div class="flex-1">
                            <input type="text" name="opsi_{{ $key }}" value="{{ old('opsi_' . $key, $opsiVal) }}" placeholder="Isi pilihan jawaban {{ $letter }}..." class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        </div>
                    </div>
                @endforeach
                <p class="text-[11px] text-slate-400 dark:text-slate-500 pt-1">
                    * Centang / Pilih radio di sebelah kiri huruf opsi untuk menetapkan opsi tersebut sebagai kunci jawaban yang benar.
                </p>
            </div>

            <!-- Bagian 3: Menjodohkan (Matching Pairs) -->
            <div x-show="jenisSoal === 3" class="space-y-3" style="display: none;">
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    Masukkan pasangan soal (premis kiri) dan respon jawaban (kolom kanan):
                </p>
                @php
                    $jodohkanOpsiA = $soal->opsi_a ?? '';
                    $jodohkanJawaban = is_array($soal?->jawaban) ? json_encode($soal->jawaban, JSON_PRETTY_PRINT) : ($soal->jawaban ?? '');
                @endphp
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Daftar Baris Premis (Kiri)</label>
                    <textarea name="opsi_a" rows="3" placeholder="Contoh: Ibu Kota Indonesia&#10;Mata Uang Jepang&#10;Lagu Kebangsaan" class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">{{ old('opsi_a', $jodohkanOpsiA) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pasangan Kunci Jawaban Benar (Format JSON / Teks Pasangan)</label>
                    <textarea name="jawaban" rows="3" placeholder='Contoh: {"1":"Nusantara", "2":"Yen", "3":"Indonesia Raya"}' class="w-full p-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">{{ old('jawaban', $jodohkanJawaban) }}</textarea>
                </div>
            </div>

            <!-- Bagian 4: Isian Singkat -->
            <div x-show="jenisSoal === 4" class="space-y-3" style="display: none;">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kunci Jawaban Isian Singkat (Case-Insensitive)</label>
                    <input type="text" name="jawaban" value="{{ old('jawaban', is_string($currJawaban) ? $currJawaban : '') }}" placeholder="Contoh: Fotosintesis" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-emerald-600 dark:text-emerald-400 focus:outline-none focus:border-brand-500">
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 block">
                        Jawaban siswa akan dicocokkan otomatis oleh mesin CBT tanpa membedakan huruf besar/kecil.
                    </span>
                </div>
            </div>

            <!-- Bagian 5: Esai / Uraian Bebas -->
            <div x-show="jenisSoal === 5" class="space-y-3" style="display: none;">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Kunci Jawaban / Pedoman Penskoran Esai</label>
                    <textarea name="jawaban" rows="4" placeholder="Tuliskan kata kunci atau pedoman rubrik penskoran untuk guru pengoreksi..." class="w-full p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">{{ old('jawaban', is_string($currJawaban) ? $currJawaban : '') }}</textarea>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 block">
                        Soal esai akan dinilai secara manual pada menu Koreksi Esai oleh Guru Pengampu.
                    </span>
                </div>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="flex items-center justify-between gap-4 pt-2">
            <a href="{{ route('admin.cbt.bank_soal.show', $bank->id_bank) }}" class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-xl transition">
                Batal & Kembali
            </a>
            <div class="flex items-center gap-3">
                @if(!$isEdit)
                    <button type="submit" name="action" value="next" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Simpan & Tambah Butir Berikutnya</span>
                    </button>
                @endif
                <button type="submit" name="action" value="save" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-md shadow-brand-600/30 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ $isEdit ? 'Simpan Perubahan Soal' : 'Simpan & Selesai' }}</span>
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/katex/katex.min.js') }}"></script>
<script src="{{ asset('vendor/katex/auto-render.min.js') }}"></script>
<script src="{{ asset('vendor/katex/contrib/mhchem.min.js') }}"></script>
@endpush
