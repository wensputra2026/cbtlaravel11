@extends('layouts.guru')

@section('title', 'Koreksi: ' . ($cbtSiswa->siswa->nama ?? 'Siswa'))
@section('page_title', 'Koreksi Hasil Siswa')

@section('content')
<div class="space-y-6 w-full" x-data="koreksiEngine({
    totalPg: {{ $scores['pg'] }},
    totalKomp: {{ $scores['kompleks'] }},
    totalJodoh: {{ $scores['jodohkan'] }},
    totalIsian: {{ $scores['isian'] }},
    initialEsai: {{ $scores['esai'] }},
    actionTandai: '{{ route('guru.koreksi.tandai', $cbtSiswa->id_cbt_siswa) }}'
})">

    <!-- Header Navigasi -->
    <div class="bg-white border border-slate-200 p-5 rounded-2xl shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('guru.koreksi.peserta', $cbtSiswa->id_jadwal) }}" class="text-xs text-emerald-600 hover:text-emerald-700 flex items-center gap-1 font-semibold">
                    &larr; Kembali ke Daftar Peserta Ujian
                </a>
            </div>
            <h1 class="text-xl font-black text-slate-900 tracking-wide">Koreksi Lembar Jawaban Peserta</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Periksa kesesuaian jawaban siswa dengan kunci & berikan penilaian butir esai / uraian.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.history.back()" type="button" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 border border-slate-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali</span>
            </button>
        </div>
    </div>

    <!-- Identitas Peserta, Jadwal, & Ringkasan Nilai (Paritas Garuda CBT) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Kolom 1: Data Siswa -->
            <div class="space-y-3 border-b md:border-b-0 md:border-r border-slate-100 pb-4 md:pb-0 md:pr-4">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Nama Peserta</span>
                    <strong class="text-sm font-black text-slate-900">{{ $cbtSiswa->siswa->nama ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">N I S / N I S N</span>
                    <span class="text-xs font-mono font-bold text-slate-800">{{ $cbtSiswa->siswa->nis ?? '-' }} / {{ $cbtSiswa->siswa->nisn ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Kelas</span>
                    <span class="text-xs font-bold text-slate-800">{{ $cbtSiswa->siswa->kelasSiswa->first()?->kelas->nama_kelas ?? ($cbtSiswa->siswa->kelas_nama ?? '-') }}</span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Nomor Peserta</span>
                    <span class="text-xs font-mono font-bold text-emerald-700">{{ $noPeserta }}</span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Sesi Ujian</span>
                    <span class="text-xs font-semibold text-slate-700">{{ $sesiSiswa->sesi->kode_sesi ?? ($sesiSiswa->sesi->nama_sesi ?? 'Sesi 1') }}</span>
                </div>
            </div>

            <!-- Kolom 2: Info Ujian & Ruang -->
            <div class="space-y-3 border-b md:border-b-0 md:border-r border-slate-100 pb-4 md:pb-0 md:pr-4">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Ruang Ujian</span>
                    <strong class="text-xs font-bold text-slate-800">{{ $sesiSiswa->ruang->kode_ruang ?? ($sesiSiswa->ruang->nama_ruang ?? 'Ruang 1') }}</strong>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Mata Pelajaran</span>
                    <strong class="text-xs font-bold text-slate-900">{{ $bank->mapel->nama_mapel ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Guru Pengampu</span>
                    <span class="text-xs font-medium text-slate-700">{{ $bank->guru->nama_guru ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Jenis Ujian</span>
                    <span class="inline-block px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-800 text-[11px] font-bold">
                        {{ $cbtSiswa->jadwal->jenis->kode_jenis ?? ($cbtSiswa->jadwal->jenis->nama_jenis ?? 'CBT') }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Tahun Pelajaran</span>
                    <span class="text-xs font-medium text-slate-700">{{ $cbtSiswa->jadwal->tp->tahun ?? '2024/2025' }}</span>
                </div>
            </div>

            <!-- Kolom 3: Tabel Rekap Skor & Nilai Akhir -->
            <div class="flex flex-col justify-between space-y-4">
                <div class="bg-slate-50 border border-slate-200 rounded-xl overflow-hidden">
                    <div class="grid grid-cols-2 divide-x divide-slate-200 border-b border-slate-200 text-center">
                        <div class="p-3">
                            <span class="text-[10px] uppercase font-bold text-slate-500 block">PG (Pilihan Ganda)</span>
                            <span class="font-mono text-base font-bold text-blue-600">{{ number_format($scores['pg'], 2) }}</span>
                        </div>
                        <div class="p-3">
                            <span class="text-[10px] uppercase font-bold text-slate-500 block">ES (Uraian/Esai)</span>
                            <span class="font-mono text-base font-bold text-rose-600" x-text="totalEsai.toFixed(2)">{{ number_format($scores['esai'], 2) }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 divide-x divide-slate-200 border-b border-slate-200 text-center bg-slate-50/70">
                        <div class="p-2">
                            <span class="text-[9px] uppercase font-bold text-slate-400 block">PK (Kompleks)</span>
                            <span class="font-mono text-xs font-bold text-purple-600">{{ number_format($scores['kompleks'], 2) }}</span>
                        </div>
                        <div class="p-2">
                            <span class="text-[9px] uppercase font-bold text-slate-400 block">JO (Jodohkan)</span>
                            <span class="font-mono text-xs font-bold text-amber-600">{{ number_format($scores['jodohkan'], 2) }}</span>
                        </div>
                        <div class="p-2">
                            <span class="text-[9px] uppercase font-bold text-slate-400 block">IS (Isian)</span>
                            <span class="font-mono text-xs font-bold text-teal-600">{{ number_format($scores['isian'], 2) }}</span>
                        </div>
                    </div>
                    <!-- Nilai Total Akhir -->
                    <div class="p-4 bg-emerald-50/60 text-center">
                        <span class="text-[11px] font-black uppercase text-emerald-800 tracking-wider block">TOTAL NILAI AKHIR</span>
                        <div class="font-mono text-4xl font-black text-slate-900 my-1" x-text="totalScore.toFixed(2)">
                            {{ number_format($scores['total'], 2) }}
                        </div>
                        <div class="mt-2">
                            @if($hanyaPG)
                                <span class="inline-block px-3 py-1 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold">
                                    Soal PG tidak perlu koreksi
                                </span>
                            @elseif($isDikoreksi)
                                <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                    ✓ Sudah dikoreksi
                                </span>
                            @else
                                <span class="inline-block px-3 py-1 rounded-full bg-rose-100 text-rose-800 text-[10px] font-bold">
                                    ● Belum dikoreksi
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                @if(!$hanyaPG)
                <button type="button" @click="tandaiDikoreksi()" :disabled="isMarking" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Tandai Sudah Dikoreksi</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- V. URAIAN / ESAI FORM PENILAIAN (Jika ada butir Esai) -->
    @if(count($grouped['esai']) > 0)
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">V. LEMBAR SOAL & JAWABAN URAIAN (ESAI)</h3>
            </div>
            <div class="text-xs text-slate-500">
                Bobot: <strong class="text-slate-800">{{ $bank->bobot_esai }}</strong> &bull; 
                Jumlah Soal: <strong class="text-slate-800">{{ $bank->tampil_esai }}</strong> &bull; 
                Point Maks / Soal: <strong class="text-emerald-700 font-mono">{{ number_format($maxPointEsai, 2) }}</strong>
            </div>
        </div>

        <div class="p-3.5 bg-rose-50/60 border border-rose-200 rounded-xl text-xs text-rose-800 leading-relaxed flex items-start gap-2.5">
            <span class="text-base">📌</span>
            <div>
                <strong>Panduan Pemeriksaan Esai:</strong>
                <ul class="list-disc list-inside mt-1 space-y-0.5 text-[11px] text-rose-700">
                    <li>Bandingkan jawaban uraian yang diketik siswa dengan rubrik / pedoman kunci jawaban dari guru.</li>
                    <li>Utamakan memeriksa butir dengan icon <span class="text-amber-600 font-bold">⚠️ Kuning</span> (belum dinilai).</li>
                    <li>Isikan skor perolehan siswa pada kolom <strong>Nilai Guru</strong> (maksimal <strong>{{ number_format($maxPointEsai, 2) }}</strong> point per butir).</li>
                    <li>Klik tombol <strong>Simpan Nilai Esai</strong> di bawah untuk menyimpan perubahan secara permanen.</li>
                </ul>
            </div>
        </div>

        <form action="{{ route('guru.koreksi.store', $cbtSiswa->id_cbt_siswa) }}" method="POST" class="space-y-4">
            @csrf
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-3 px-3.5 w-14 text-center">No</th>
                            <th class="py-3 px-4 min-w-[280px]">Soal Uraian</th>
                            <th class="py-3 px-4 min-w-[200px]">Pedoman Rubrik / Kunci Guru</th>
                            <th class="py-3 px-4 min-w-[260px]">Jawaban Siswa</th>
                            <th class="py-3 px-3 text-center w-20">Status</th>
                            <th class="py-3 px-4 w-36 text-center">Nilai Guru<br><span class="text-[9px] text-slate-400 font-normal">Max: {{ number_format($maxPointEsai, 2) }}</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @foreach($grouped['esai'] as $es)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-3.5 text-center font-bold text-slate-500 align-top">
                                #{{ $es['nomor'] }}
                            </td>
                            <td class="py-3.5 px-4 align-top leading-relaxed text-slate-900 font-medium">
                                <div class="prose prose-xs max-w-none">
                                    {!! $es['soal'] !!}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 align-top text-slate-600 bg-slate-50/40">
                                <div class="text-[11px] leading-relaxed">
                                    {!! $es['jawaban_benar'] !!}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 align-top">
                                <div class="p-2.5 rounded-lg bg-emerald-50/40 border border-emerald-200 text-slate-900 leading-relaxed text-xs">
                                    @if(empty(strip_tags($es['jawaban_siswa'])))
                                        <em class="text-slate-400">(Siswa tidak mengisi jawaban)</em>
                                    @else
                                        {!! $es['jawaban_siswa'] !!}
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-3 text-center align-top">
                                @if($es['skor'] > 0)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold" title="Sudah Dinilai">
                                        ✓
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-700 text-xs font-bold" title="Belum Dinilai / 0">
                                        ⚠️
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 align-top text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <input type="number" 
                                           step="0.1" 
                                           min="0" 
                                           max="{{ $es['max_point'] }}" 
                                           name="skor_soal[{{ $es['id_soal'] }}]" 
                                           value="{{ $es['skor'] }}" 
                                           @input="updateEsaiScore()"
                                           class="esai-score-input w-24 px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-sm font-mono font-bold text-center text-slate-900 focus:outline-none focus:border-rose-500 shadow-2xs">
                                </div>
                                <span class="text-[10px] text-slate-400 block mt-1">Maks: {{ $es['max_point'] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold text-slate-900">
                        <tr>
                            <td colspan="5" class="py-3.5 px-4 text-right uppercase tracking-wider text-xs">
                                Total Skor Jawaban Uraian (Esai):
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono text-base text-rose-600">
                                <span x-text="totalEsai.toFixed(2)">{{ number_format($scores['esai'], 2) }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex items-center justify-end pt-2">
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    <span>Simpan Nilai Esai</span>
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- I. PILIHAN GANDA (PG) -->
    @if(count($grouped['pg']) > 0)
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4" x-data="{ openPg: true }">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 cursor-pointer select-none" @click="openPg = !openPg">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">I. PILIHAN GANDA (PG)</h3>
                <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[10px] font-bold">
                    {{ count($grouped['pg']) }} Butir Soal
                </span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-600">Skor: <span class="text-blue-600 font-mono">{{ number_format($scores['pg'], 2) }}</span></span>
                <span class="text-slate-400 text-xs font-bold" x-text="openPg ? '▲ Tutup' : '▼ Buka'"></span>
            </div>
        </div>

        <div x-show="openPg" x-collapse class="space-y-4">
            <div class="p-3 bg-blue-50/60 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-center justify-between">
                <span>Bobot: <strong>{{ $bank->bobot_pg }}</strong> &bull; Jumlah Soal: <strong>{{ $bank->tampil_pg }}</strong> &bull; Max Point Persoal: <strong>{{ number_format($maxPointPg, 2) }}</strong></span>
                <span class="text-[11px] text-blue-700 font-medium">Point PG dinilai otomatis oleh sistem</span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="py-2.5 px-3 text-center w-12">No</th>
                            <th class="py-2.5 px-4 min-w-[300px]">Soal</th>
                            <th class="py-2.5 px-4 min-w-[200px]">Pilihan Opsi</th>
                            <th class="py-2.5 px-3 text-center w-24">Kunci</th>
                            <th class="py-2.5 px-3 text-center w-24">Jawaban Siswa</th>
                            <th class="py-2.5 px-3 text-center w-20">Analisa</th>
                            <th class="py-2.5 px-3 text-center w-24">Point</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @foreach($grouped['pg'] as $pg)
                        <tr class="hover:bg-slate-50/60 transition {{ $pg['is_benar'] ? '' : 'bg-rose-50/30' }}">
                            <td class="py-3 px-3 text-center font-bold text-slate-500 align-top">#{{ $pg['nomor'] }}</td>
                            <td class="py-3 px-4 align-top leading-relaxed text-slate-900">
                                {!! $pg['soal'] !!}
                            </td>
                            <td class="py-3 px-4 align-top text-[11px] text-slate-600">
                                <ol type="A" class="space-y-1 list-[upper-alpha] list-inside">
                                    @foreach($pg['opsi'] as $huruf => $teksOpsi)
                                        @if(!empty($teksOpsi))
                                            <li class="{{ $pg['jawaban_benar'] === $huruf ? 'text-emerald-700 font-bold' : '' }}">
                                                {!! strip_tags($teksOpsi, '<img><b><i><u>') !!}
                                            </li>
                                        @endif
                                    @endforeach
                                </ol>
                            </td>
                            <td class="py-3 px-3 text-center font-mono font-bold text-emerald-700 align-top">{{ $pg['jawaban_benar'] }}</td>
                            <td class="py-3 px-3 text-center font-mono font-bold align-top {{ $pg['is_benar'] ? 'text-emerald-700' : 'text-rose-600' }}">
                                {{ $pg['jawaban_siswa'] }}
                            </td>
                            <td class="py-3 px-3 text-center align-top">
                                @if($pg['is_benar'])
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold" title="Benar">✓</span>
                                @else
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rose-100 text-rose-700 text-xs font-bold" title="Salah">✗</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center font-mono font-bold align-top {{ $pg['is_benar'] ? 'text-blue-600' : 'text-slate-400' }}">
                                {{ number_format($pg['skor'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 border-t border-slate-200 font-bold text-slate-900">
                        <tr>
                            <td colspan="6" class="py-3 px-4 text-right uppercase text-xs tracking-wider">Total Skor Pilihan Ganda:</td>
                            <td class="py-3 px-3 text-center font-mono text-blue-600 text-sm">{{ number_format($scores['pg'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- IV. ISIAN SINGKAT (Jika ada) -->
    @if(count($grouped['isian']) > 0)
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4" x-data="{ openIsian: true }">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 cursor-pointer select-none" @click="openIsian = !openIsian">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-teal-500"></span>
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">IV. ISIAN SINGKAT</h3>
                <span class="px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 text-[10px] font-bold">
                    {{ count($grouped['isian']) }} Butir Soal
                </span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-600">Skor: <span class="text-teal-600 font-mono">{{ number_format($scores['isian'], 2) }}</span></span>
                <span class="text-slate-400 text-xs font-bold" x-text="openIsian ? '▲ Tutup' : '▼ Buka'"></span>
            </div>
        </div>

        <div x-show="openIsian" x-collapse class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-2.5 px-3 text-center w-12">No</th>
                        <th class="py-2.5 px-4 min-w-[280px]">Soal Isian</th>
                        <th class="py-2.5 px-4 min-w-[180px]">Kunci Jawaban Eksak</th>
                        <th class="py-2.5 px-4 min-w-[180px]">Jawaban Siswa</th>
                        <th class="py-2.5 px-3 text-center w-20">Analisa</th>
                        <th class="py-2.5 px-3 text-center w-24">Point</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @foreach($grouped['isian'] as $is)
                    <tr class="hover:bg-slate-50/60 transition {{ $is['is_benar'] ? '' : 'bg-rose-50/30' }}">
                        <td class="py-3 px-3 text-center font-bold text-slate-500 align-top">#{{ $is['nomor'] }}</td>
                        <td class="py-3 px-4 align-top leading-relaxed text-slate-900 font-medium">
                            {!! $is['soal'] !!}
                        </td>
                        <td class="py-3 px-4 align-top font-mono font-bold text-emerald-700">
                            {{ $is['jawaban_benar'] }}
                        </td>
                        <td class="py-3 px-4 align-top font-mono font-bold {{ $is['is_benar'] ? 'text-emerald-700' : 'text-rose-600' }}">
                            {{ $is['jawaban_siswa'] }}
                        </td>
                        <td class="py-3 px-3 text-center align-top">
                            @if($is['is_benar'])
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold" title="Benar">✓</span>
                            @else
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-rose-100 text-rose-700 text-xs font-bold" title="Salah">✗</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center font-mono font-bold align-top {{ $is['is_benar'] ? 'text-teal-600' : 'text-slate-400' }}">
                            {{ number_format($is['skor'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<script>
function koreksiEngine(config) {
    return {
        totalPg: config.totalPg || 0,
        totalKomp: config.totalKomp || 0,
        totalJodoh: config.totalJodoh || 0,
        totalIsian: config.totalIsian || 0,
        totalEsai: config.initialEsai || 0,
        actionTandai: config.actionTandai,
        isMarking: false,

        get totalScore() {
            return this.totalPg + this.totalKomp + this.totalJodoh + this.totalIsian + this.totalEsai;
        },

        updateEsaiScore() {
            let sum = 0;
            document.querySelectorAll('.esai-score-input').forEach(input => {
                let val = parseFloat(input.value) || 0;
                let max = parseFloat(input.getAttribute('max')) || 100;
                if (val > max) val = max;
                if (val < 0) val = 0;
                sum += val;
            });
            this.totalEsai = sum;
        },

        async tandaiDikoreksi() {
            if (window.Swal) {
                const res = await Swal.fire({
                    title: 'Tandai Sudah Dikoreksi?',
                    text: 'Status lembar ujian peserta ini akan diubah menjadi SUDAH DIKOREKSI.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#059669',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Tandai Sekarang',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                });
                if (!res.isConfirmed) return;
            } else {
                if (!confirm('Tandai lembar ujian ini sudah dikoreksi?')) return;
            }

            this.isMarking = true;
            try {
                let res = await fetch(this.actionTandai, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                let data = await res.json();
                if (window.Swal) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message || 'Jawaban berhasil ditandai sudah dikoreksi.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
                window.location.reload();
            } catch(e) {
                this.isMarking = false;
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menandai status koreksi.'
                    });
                } else {
                    alert('Gagal menandai status koreksi');
                }
            }
        }
    };
}
</script>
@endsection
