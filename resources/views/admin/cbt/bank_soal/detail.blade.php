@extends('layouts.admin')

@section('title', 'Detail Soal - ' . $bank->bank_kode)
@section('page_title', 'Detail Bank Soal: ' . $bank->bank_kode)

@push('styles')
<!-- SweetAlert2 Local CSS -->
<link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert2.min.css') }}">
<style>
    .my-shadow {
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.07), 0 1px 2px 0 rgba(0, 0, 0, 0.04);
    }
    .table-soal th, .table-soal td {
        border: 1px solid #e2e8f0;
        padding: 0.5rem 0.75rem;
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #cbd5e1;
    }
    .check-box-custom {
        width: 1.1rem;
        height: 1.1rem;
        accent-color: #4f46e5;
        cursor: pointer;
    }
    .tab-link-active {
        background-color: #4f46e5 !important;
        color: #ffffff !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-5" x-data="{
    activeTab: 'ganda',
    openModalTambah: false,
    jenisTambah: 1,
    opsiCount: {{ (int)($bank->opsi ?? 5) }}
}">

    <!-- Top Action Bar & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white border border-slate-200 rounded-2xl p-4 my-shadow">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.cbt.bank_soal.index') }}" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali</span>
            </a>
            <div>
                <h1 class="text-base font-black text-slate-900 leading-tight">Detail Soal: {{ $bank->bank_kode }}</h1>
                <p class="text-xs text-slate-500">{{ $bank->mapel->nama_mapel ?? 'Mata Pelajaran' }} &bull; Kelas {{ $bank->bank_level }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="window.location.reload()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 border border-slate-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Reload</span>
            </button>
            <button type="button" id="btn-download-word" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition flex items-center gap-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Download Soal</span>
            </button>
            <button type="button" @click="openModalTambah = true" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah/Edit Soal</span>
            </button>
        </div>
    </div>

    <!-- Top Metadata Card (Kode, Mapel, Guru, Kelas, Totals, Status) -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 my-shadow">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
            <!-- Kolom 1: Spesifikasi Bank Soal -->
            <div class="md:col-span-5 border-b md:border-b-0 md:border-r border-slate-100 pb-4 md:pb-0 md:pr-4">
                <ul class="divide-y divide-slate-100 text-xs text-slate-600">
                    <li class="py-2 flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Kode Bank Soal</span>
                        <strong class="text-slate-900 font-mono text-sm font-bold">{{ $bank->bank_kode }}</strong>
                    </li>
                    <li class="py-2 flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Mata Pelajaran</span>
                        <strong class="text-slate-900 font-bold text-right">{{ $bank->mapel->nama_mapel ?? '-' }}</strong>
                    </li>
                    <li class="py-2 flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Guru Pengampu</span>
                        <strong class="text-slate-900 font-bold text-right">{{ $bank->guru->nama_guru ?? '-' }}</strong>
                    </li>
                    <li class="py-2 flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Kelas / Rombel</span>
                        <strong class="text-slate-900 font-bold text-right">{{ $kelasFormatted }}</strong>
                    </li>
                </ul>
            </div>

            <!-- Kolom 2: Rangkuman Jumlah Soal -->
            <div class="md:col-span-4 border-b md:border-b-0 md:border-r border-slate-100 pb-4 md:pb-0 md:px-4">
                <ul class="divide-y divide-slate-100 text-xs text-slate-600">
                    <li class="py-2 flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Total Seharusnya</span>
                        <strong class="text-slate-900 font-bold text-sm">{{ $totalSoalSeharusnya }} Soal</strong>
                    </li>
                    <li class="py-2 flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Total Soal dibuat</span>
                        <strong class="text-slate-900 font-bold text-sm">{{ $totalSoalDibuat }} Butir</strong>
                    </li>
                    <li class="py-2 flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Total ditampilkan</span>
                        <strong class="text-slate-900 font-bold text-sm">{{ $totalSoalTampil }} Butir</strong>
                    </li>
                    <li class="py-2 flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Keterangan</span>
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $statusSoal === 'SELESAI' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $statusSoal }}
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Kolom 3: Status Pembuatan Box -->
            <div class="md:col-span-3 flex flex-col items-center justify-center text-center p-4 rounded-xl {{ $statusSoal === 'SELESAI' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }} shadow-xs">
                <span class="text-xs font-semibold uppercase tracking-wider opacity-90">Pembuatan Soal</span>
                <span class="text-xl font-black mt-1 tracking-wide">{{ $statusSoal }}</span>
                <span class="text-[11px] mt-1.5 opacity-95 leading-tight font-medium max-w-[200px]">{{ $ketSoal }}</span>
            </div>

            <!-- Banner Warning Garuda CBT -->
            <div class="col-span-12">
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    <div>
                        Jika keterangan pembuatan soal sudah <strong>SELESAI</strong> tapi butir soal tidak muncul pada lembar ujian siswa, klik tombol 
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-brand-600 text-white font-bold text-[11px]">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Simpan Soal Terpilih
                        </span> 
                        di bawah pada masing-masing tab jenis soal.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigasi 5 Jenis Soal Garuda CBT -->
    <div class="bg-white border border-slate-200 rounded-2xl my-shadow overflow-hidden">
        <!-- Tab Header Pills -->
        <div class="p-3 border-b border-slate-200 bg-slate-50/50 flex flex-wrap items-center gap-2">
            <!-- 1. Pilihan Ganda -->
            <button type="button" @click="activeTab = 'ganda'" :class="activeTab === 'ganda' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <span>Pilihan Ganda</span>
                @if($badgePg === 'success')
                    <span class="text-emerald-500 font-black text-sm" title="Jumlah soal memenuhi target">&check;</span>
                @else
                    <span class="text-rose-500 font-black text-sm" title="Jumlah soal belum memenuhi target">&excl;</span>
                @endif
            </button>

            <!-- 2. PG Kompleks -->
            <button type="button" @click="activeTab = 'kompleks'" :class="activeTab === 'kompleks' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <span>Pil. Ganda Kompleks</span>
                @if($badgeKompleks === 'success')
                    <span class="text-emerald-500 font-black text-sm">&check;</span>
                @else
                    <span class="text-rose-500 font-black text-sm">&excl;</span>
                @endif
            </button>

            <!-- 3. Menjodohkan -->
            <button type="button" @click="activeTab = 'jodoh'" :class="activeTab === 'jodoh' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <span>Menjodohkan</span>
                @if($badgeJodoh === 'success')
                    <span class="text-emerald-500 font-black text-sm">&check;</span>
                @else
                    <span class="text-rose-500 font-black text-sm">&excl;</span>
                @endif
            </button>

            <!-- 4. Isian Singkat -->
            <button type="button" @click="activeTab = 'isian'" :class="activeTab === 'isian' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <span>Isian Singkat</span>
                @if($badgeIsian === 'success')
                    <span class="text-emerald-500 font-black text-sm">&check;</span>
                @else
                    <span class="text-rose-500 font-black text-sm">&excl;</span>
                @endif
            </button>

            <!-- 5. Essai / Uraian -->
            <button type="button" @click="activeTab = 'esai'" :class="activeTab === 'esai' ? 'bg-brand-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <span>Essai / Uraian</span>
                @if($badgeEsai === 'success')
                    <span class="text-emerald-500 font-black text-sm">&check;</span>
                @else
                    <span class="text-rose-500 font-black text-sm">&excl;</span>
                @endif
            </button>
        </div>

        <!-- Tab Body Contents -->
        <div class="p-5">

            <!-- ============================================================= -->
            <!-- TAB 1: PILIHAN GANDA                                          -->
            <!-- ============================================================= -->
            <div x-show="activeTab === 'ganda'" x-cloak class="space-y-4">
                <!-- Summary Table PG -->
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-xs text-left border-collapse table-bordered">
                        <thead>
                            <tr class="bg-slate-100 text-slate-800 text-center font-bold">
                                <th class="p-2 border border-slate-300">Jenis Soal</th>
                                <th class="p-2 border border-slate-300" colspan="2">Jumlah Soal</th>
                                <th class="p-2 border border-slate-300">Bobot Nilai</th>
                                <th class="p-2 border border-slate-300">Point Per-nomor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">Pilihan Ganda</td>
                                <td class="p-2 border border-slate-300 font-medium">Seharusnya</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ $tampilPg }}</td>
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">{{ $bobotPg }}</td>
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">{{ $pointPg }}</td>
                            </tr>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td class="p-2 border border-slate-300 font-medium">Telah dibuat</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ count($soalsPg) }}</td>
                            </tr>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td class="p-2 border border-slate-300 font-medium">Ditampilkan</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ $totalPgTampil }}</td>
                            </tr>
                            @if(count($soalsPg) < $tampilPg || $totalPgTampil < $tampilPg || $isLocked)
                                <tr class="bg-rose-50 text-rose-800">
                                    <td colspan="5" class="p-3 border border-slate-300">
                                        <span class="font-bold">Info:</span>
                                        <ul class="list-disc pl-5 mt-1 space-y-0.5 text-[11px]">
                                            @if(count($soalsPg) < $tampilPg)
                                                <li>Soal PILIHAN GANDA masih kurang, klik tombol <strong>(+ Tambah/Edit Soal)</strong> untuk menambahkan.</li>
                                            @endif
                                            @if(count($soalsPg) > 0 && $tampilPg === 0)
                                                <li>Ada soal PILIHAN GANDA tapi pengaturan tampil adalah 0.</li>
                                            @endif
                                            @if($totalPgTampil < $tampilPg)
                                                <li>Jumlah soal yang dicentang/ditampilkan belum sesuai target yang seharusnya ({{ $totalPgTampil }} / {{ $tampilPg }}).</li>
                                            @endif
                                            @if($isScheduled)
                                                <li>Soal sudah dijadwalkan pada agenda ujian aktif.</li>
                                            @endif
                                            @if($totalSiswa > 0)
                                                <li>Soal sedang atau sudah digunakan oleh {{ $totalSiswa }} siswa.</li>
                                            @endif
                                        </ul>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Questions List Table PG -->
                @if(count($soalsPg) > 0)
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <!-- Table Action Toolbar -->
                        <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 flex items-center justify-between flex-wrap gap-2 text-xs">
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 select-none">
                                <input type="checkbox" id="check-all-pg" class="check-box-custom" {{ $isLocked ? 'disabled' : '' }}>
                                <span>Pilih Semua PG</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <span>Jumlah PG terpilih: <b id="total-selected-pg" class="text-sm font-bold text-brand-600">{{ $totalPgTampil }}</b></span>
                                <button type="button" class="btn-save-selected px-3.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold shadow-xs transition flex items-center gap-1.5" data-jenis="1" data-target="{{ $tampilPg }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    <span>Simpan Soal Terpilih</span>
                                </button>
                            </div>
                        </div>

                        <!-- Rows PG -->
                        <form id="form-pg" class="m-0">
                            <table class="w-full text-xs text-left border-collapse" id="table-pg">
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($soalsPg as $idx => $soal)
                                        <tr class="hover:bg-slate-50/70 transition" data-id="{{ $soal->id_soal }}">
                                            <td class="w-12 text-center p-3 align-top">
                                                <input type="checkbox" name="soal[]" value="{{ $soal->id_soal }}" class="check-soal-pg check-box-custom" {{ $soal->tampilkan ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                            </td>
                                            <td class="w-10 text-center p-3 align-top font-bold text-slate-700">
                                                {{ $soal->nomor_soal }}.
                                            </td>
                                            <td class="p-3 align-top space-y-2">
                                                <div class="text-slate-900 leading-relaxed text-sm">{!! $soal->soal !!}</div>
                                                <ul class="list-[upper-alpha] pl-5 space-y-1 text-slate-700 text-xs">
                                                    @if(!empty($soal->opsi_a)) <li>{!! is_array($soal->opsi_a) ? json_encode($soal->opsi_a) : $soal->opsi_a !!}</li> @endif
                                                    @if(!empty($soal->opsi_b)) <li>{!! is_array($soal->opsi_b) ? json_encode($soal->opsi_b) : $soal->opsi_b !!}</li> @endif
                                                    @if(!empty($soal->opsi_c)) <li>{!! is_array($soal->opsi_c) ? json_encode($soal->opsi_c) : $soal->opsi_c !!}</li> @endif
                                                    @if(!empty($soal->opsi_d) && $bank->opsi >= 4) <li>{!! is_array($soal->opsi_d) ? json_encode($soal->opsi_d) : $soal->opsi_d !!}</li> @endif
                                                    @if(!empty($soal->opsi_e) && $bank->opsi >= 5) <li>{!! is_array($soal->opsi_e) ? json_encode($soal->opsi_e) : $soal->opsi_e !!}</li> @endif
                                                </ul>
                                                <div class="text-xs text-slate-800">
                                                    Jawaban: <strong class="text-emerald-700 font-bold font-mono">{{ strtoupper(is_array($soal->jawaban) ? json_encode($soal->jawaban) : (string)$soal->jawaban) }}</strong>
                                                </div>
                                            </td>
                                            <td class="w-20 text-right p-3 align-top">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <button type="button" class="btn-hapus-soal p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition" data-id="{{ $soal->id_soal }}" data-nomor="{{ $soal->nomor_soal }}" data-jenis="Pilihan Ganda" {{ $isLocked ? 'disabled' : '' }} title="Hapus Soal">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </form>
                    </div>
                @else
                    <div class="p-8 text-center bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-xs">
                        Tidak ada soal Pilihan Ganda pada bank soal ini.
                    </div>
                @endif
            </div>

            <!-- ============================================================= -->
            <!-- TAB 2: PILIHAN GANDA KOMPLEKS                                 -->
            <!-- ============================================================= -->
            <div x-show="activeTab === 'kompleks'" x-cloak class="space-y-4">
                <!-- Summary Table Kompleks -->
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-xs text-left border-collapse table-bordered">
                        <thead>
                            <tr class="bg-slate-100 text-slate-800 text-center font-bold">
                                <th class="p-2 border border-slate-300">Jenis Soal</th>
                                <th class="p-2 border border-slate-300" colspan="2">Jumlah Soal</th>
                                <th class="p-2 border border-slate-300">Bobot Nilai</th>
                                <th class="p-2 border border-slate-300">Point Per-nomor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">Pil. Ganda Kompleks</td>
                                <td class="p-2 border border-slate-300 font-medium">Seharusnya</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ $tampilKompleks }}</td>
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">{{ $bobotKompleks }}</td>
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">{{ $pointKompleks }}</td>
                            </tr>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td class="p-2 border border-slate-300 font-medium">Telah dibuat</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ count($soalsKompleks) }}</td>
                            </tr>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td class="p-2 border border-slate-300 font-medium">Ditampilkan</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ $totalKompleksTampil }}</td>
                            </tr>
                            @if(count($soalsKompleks) < $tampilKompleks || $totalKompleksTampil < $tampilKompleks)
                                <tr class="bg-rose-50 text-rose-800">
                                    <td colspan="5" class="p-3 border border-slate-300">
                                        <span class="font-bold">Info:</span>
                                        <ul class="list-disc pl-5 mt-1 space-y-0.5 text-[11px]">
                                            @if(count($soalsKompleks) < $tampilKompleks)
                                                <li>Soal PILIHAN GANDA KOMPLEKS masih kurang dari target yang ditentukan.</li>
                                            @endif
                                            @if($totalKompleksTampil < $tampilKompleks)
                                                <li>Jumlah soal yang ditampilkan belum sama dengan target seharusnya.</li>
                                            @endif
                                        </ul>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if(count($soalsKompleks) > 0)
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 flex items-center justify-between flex-wrap gap-2 text-xs">
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 select-none">
                                <input type="checkbox" id="check-all-kompleks" class="check-box-custom" {{ $isLocked ? 'disabled' : '' }}>
                                <span>Pilih Semua Kompleks</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <span>Jumlah soal terpilih: <b id="total-selected-kompleks" class="text-sm font-bold text-brand-600">{{ $totalKompleksTampil }}</b></span>
                                <button type="button" class="btn-save-selected px-3.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold shadow-xs transition flex items-center gap-1.5" data-jenis="2" data-target="{{ $tampilKompleks }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    <span>Simpan Soal Terpilih</span>
                                </button>
                            </div>
                        </div>

                        <form id="form-kompleks" class="m-0">
                            <table class="w-full text-xs text-left border-collapse" id="table-kompleks">
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($soalsKompleks as $soal)
                                        <tr class="hover:bg-slate-50/70 transition" data-id="{{ $soal->id_soal }}">
                                            <td class="w-12 text-center p-3 align-top">
                                                <input type="checkbox" name="soal[]" value="{{ $soal->id_soal }}" class="check-soal-kompleks check-box-custom" {{ $soal->tampilkan ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                            </td>
                                            <td class="w-10 text-center p-3 align-top font-bold text-slate-700">
                                                {{ $soal->nomor_soal }}.
                                            </td>
                                            <td class="p-3 align-top space-y-2">
                                                <div class="text-slate-900 leading-relaxed text-sm">{!! $soal->soal !!}</div>
                                                <div class="pl-3 border-l-2 border-slate-200 space-y-1">
                                                    @php
                                                        $opsis = is_array($soal->opsi_a) ? $soal->opsi_a : (array)$soal->opsi_a;
                                                        $jwbArr = is_array($soal->jawaban) ? $soal->jawaban : [$soal->jawaban];
                                                    @endphp
                                                    @foreach($opsis as $key => $optVal)
                                                        @if(!empty($optVal))
                                                            <div class="text-xs text-slate-700">
                                                                <strong class="font-mono text-slate-900 uppercase">{{ $key }}.</strong> {!! is_array($optVal) ? json_encode($optVal) : $optVal !!}
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                                <div class="text-xs text-slate-800">
                                                    Kunci Jawaban: <strong class="text-purple-700 font-bold font-mono">{{ strtoupper(implode(', ', array_filter($jwbArr))) }}</strong>
                                                </div>
                                            </td>
                                            <td class="w-20 text-right p-3 align-top">
                                                <button type="button" class="btn-hapus-soal p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition" data-id="{{ $soal->id_soal }}" data-nomor="{{ $soal->nomor_soal }}" data-jenis="PG Kompleks" {{ $isLocked ? 'disabled' : '' }}>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </form>
                    </div>
                @else
                    <div class="p-8 text-center bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-xs">
                        Tidak ada soal Pilihan Ganda Kompleks pada bank soal ini.
                    </div>
                @endif
            </div>

            <!-- ============================================================= -->
            <!-- TAB 3: MENJODOHKAN                                            -->
            <!-- ============================================================= -->
            <div x-show="activeTab === 'jodoh'" x-cloak class="space-y-4">
                <!-- Summary Table Menjodohkan -->
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-xs text-left border-collapse table-bordered">
                        <thead>
                            <tr class="bg-slate-100 text-slate-800 text-center font-bold">
                                <th class="p-2 border border-slate-300">Jenis Soal</th>
                                <th class="p-2 border border-slate-300" colspan="2">Jumlah Soal</th>
                                <th class="p-2 border border-slate-300">Bobot Nilai</th>
                                <th class="p-2 border border-slate-300">Point Per-nomor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">Menjodohkan</td>
                                <td class="p-2 border border-slate-300 font-medium">Seharusnya</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ $tampilJodoh }}</td>
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">{{ $bobotJodoh }}</td>
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">{{ $pointJodoh }}</td>
                            </tr>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td class="p-2 border border-slate-300 font-medium">Telah dibuat</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ count($soalsJodoh) }}</td>
                            </tr>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td class="p-2 border border-slate-300 font-medium">Ditampilkan</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ $totalJodohTampil }}</td>
                            </tr>
                            @if(count($soalsJodoh) < $tampilJodoh || $totalJodohTampil < $tampilJodoh)
                                <tr class="bg-rose-50 text-rose-800">
                                    <td colspan="5" class="p-3 border border-slate-300">
                                        <span class="font-bold">Info:</span>
                                        <ul class="list-disc pl-5 mt-1 space-y-0.5 text-[11px]">
                                            @if(count($soalsJodoh) < $tampilJodoh)
                                                <li>Soal MENJODOHKAN masih kurang dari target yang ditentukan.</li>
                                            @endif
                                            @if($totalJodohTampil < $tampilJodoh)
                                                <li>Jumlah soal yang ditampilkan belum sama dengan target seharusnya.</li>
                                            @endif
                                        </ul>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if(count($soalsJodoh) > 0)
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 flex items-center justify-between flex-wrap gap-2 text-xs">
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 select-none">
                                <input type="checkbox" id="check-all-jodoh" class="check-box-custom" {{ $isLocked ? 'disabled' : '' }}>
                                <span>Pilih Semua Menjodohkan</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <span>Jumlah soal terpilih: <b id="total-selected-jodoh" class="text-sm font-bold text-brand-600">{{ $totalJodohTampil }}</b></span>
                                <button type="button" class="btn-save-selected px-3.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold shadow-xs transition flex items-center gap-1.5" data-jenis="3" data-target="{{ $tampilJodoh }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    <span>Simpan Soal Terpilih</span>
                                </button>
                            </div>
                        </div>

                        <form id="form-jodoh" class="m-0">
                            <table class="w-full text-xs text-left border-collapse" id="table-jodoh">
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($soalsJodoh as $soal)
                                        <tr class="hover:bg-slate-50/70 transition" data-id="{{ $soal->id_soal }}">
                                            <td class="w-12 text-center p-3 align-top">
                                                <input type="checkbox" name="soal[]" value="{{ $soal->id_soal }}" class="check-soal-jodoh check-box-custom" {{ $soal->tampilkan ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                            </td>
                                            <td class="w-10 text-center p-3 align-top font-bold text-slate-700">
                                                {{ $soal->nomor_soal }}.
                                            </td>
                                            <td class="p-3 align-top space-y-3">
                                                <div class="text-slate-900 leading-relaxed text-sm">{!! $soal->soal !!}</div>
                                                
                                                <!-- Preview Matriks Menjodohkan -->
                                                @php
                                                    $jawabanData = is_array($soal->jawaban) ? $soal->jawaban : json_decode($soal->jawaban, true);
                                                    $matrix = $jawabanData['jawaban'] ?? null;
                                                @endphp
                                                @if(is_array($matrix) && count($matrix) > 1)
                                                    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                                                        <table class="w-full text-xs border-collapse">
                                                            <thead>
                                                                <tr class="bg-slate-100 text-slate-800">
                                                                    @foreach($matrix[0] as $colIdx => $colHeader)
                                                                        <th class="p-2 border border-slate-200 text-center font-bold">
                                                                            {!! strip_tags($colHeader) ?: '#' !!}
                                                                        </th>
                                                                    @endforeach
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @for($r = 1; $r < count($matrix); $r++)
                                                                    <tr>
                                                                        @foreach($matrix[$r] as $cIdx => $cell)
                                                                            <td class="p-2 border border-slate-200 {{ $cIdx === 0 ? 'font-medium text-slate-800' : 'text-center' }}">
                                                                                @if($cIdx === 0)
                                                                                    {!! strip_tags($cell) !!}
                                                                                @else
                                                                                    @if((string)$cell === '1')
                                                                                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 font-bold inline-flex items-center justify-center text-xs">&check;</span>
                                                                                    @else
                                                                                        <span class="text-slate-300">-</span>
                                                                                    @endif
                                                                                @endif
                                                                            </td>
                                                                        @endforeach
                                                                    </tr>
                                                                @endfor
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="p-2.5 bg-slate-50 rounded-lg text-xs font-mono text-slate-700 border border-slate-200">
                                                        {{ is_array($soal->jawaban) ? json_encode($soal->jawaban, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $soal->jawaban }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="w-20 text-right p-3 align-top">
                                                <button type="button" class="btn-hapus-soal p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition" data-id="{{ $soal->id_soal }}" data-nomor="{{ $soal->nomor_soal }}" data-jenis="Menjodohkan" {{ $isLocked ? 'disabled' : '' }}>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </form>
                    </div>
                @else
                    <div class="p-8 text-center bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-xs">
                        Tidak ada soal Menjodohkan pada bank soal ini.
                    </div>
                @endif
            </div>

            <!-- ============================================================= -->
            <!-- TAB 4: ISIAN SINGKAT                                          -->
            <!-- ============================================================= -->
            <div x-show="activeTab === 'isian'" x-cloak class="space-y-4">
                <!-- Summary Table Isian -->
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-xs text-left border-collapse table-bordered">
                        <thead>
                            <tr class="bg-slate-100 text-slate-800 text-center font-bold">
                                <th class="p-2 border border-slate-300">Jenis Soal</th>
                                <th class="p-2 border border-slate-300" colspan="2">Jumlah Soal</th>
                                <th class="p-2 border border-slate-300">Bobot Nilai</th>
                                <th class="p-2 border border-slate-300">Point Per-nomor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">Isian Singkat</td>
                                <td class="p-2 border border-slate-300 font-medium">Seharusnya</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ $tampilIsian }}</td>
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">{{ $bobotIsian }}</td>
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">{{ $pointIsian }}</td>
                            </tr>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td class="p-2 border border-slate-300 font-medium">Telah dibuat</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ count($soalsIsian) }}</td>
                            </tr>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td class="p-2 border border-slate-300 font-medium">Ditampilkan</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ $totalIsianTampil }}</td>
                            </tr>
                            @if(count($soalsIsian) < $tampilIsian || $totalIsianTampil < $tampilIsian)
                                <tr class="bg-rose-50 text-rose-800">
                                    <td colspan="5" class="p-3 border border-slate-300">
                                        <span class="font-bold">Info:</span>
                                        <ul class="list-disc pl-5 mt-1 space-y-0.5 text-[11px]">
                                            @if(count($soalsIsian) < $tampilIsian)
                                                <li>Soal ISIAN SINGKAT masih kurang dari target.</li>
                                            @endif
                                            @if($totalIsianTampil < $tampilIsian)
                                                <li>Jumlah soal yang ditampilkan tidak sama dengan seharusnya.</li>
                                            @endif
                                        </ul>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if(count($soalsIsian) > 0)
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 flex items-center justify-between flex-wrap gap-2 text-xs">
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 select-none">
                                <input type="checkbox" id="check-all-isian" class="check-box-custom" {{ $isLocked ? 'disabled' : '' }}>
                                <span>Pilih Semua Isian</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <span>Jumlah soal terpilih: <b id="total-selected-isian" class="text-sm font-bold text-brand-600">{{ $totalIsianTampil }}</b></span>
                                <button type="button" class="btn-save-selected px-3.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold shadow-xs transition flex items-center gap-1.5" data-jenis="4" data-target="{{ $tampilIsian }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    <span>Simpan Soal Terpilih</span>
                                </button>
                            </div>
                        </div>

                        <form id="form-isian" class="m-0">
                            <table class="w-full text-xs text-left border-collapse" id="table-isian">
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($soalsIsian as $soal)
                                        <tr class="hover:bg-slate-50/70 transition" data-id="{{ $soal->id_soal }}">
                                            <td class="w-12 text-center p-3 align-top">
                                                <input type="checkbox" name="soal[]" value="{{ $soal->id_soal }}" class="check-soal-isian check-box-custom" {{ $soal->tampilkan ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                            </td>
                                            <td class="w-10 text-center p-3 align-top font-bold text-slate-700">
                                                {{ $soal->nomor_soal }}.
                                            </td>
                                            <td class="p-3 align-top space-y-2">
                                                <div class="text-slate-900 leading-relaxed text-sm">{!! $soal->soal !!}</div>
                                                <div class="text-xs text-slate-800">
                                                    Jawaban: <strong class="text-teal-700 font-bold font-mono">{{ is_array($soal->jawaban) ? implode(', ', $soal->jawaban) : $soal->jawaban }}</strong>
                                                </div>
                                            </td>
                                            <td class="w-20 text-right p-3 align-top">
                                                <button type="button" class="btn-hapus-soal p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition" data-id="{{ $soal->id_soal }}" data-nomor="{{ $soal->nomor_soal }}" data-jenis="Isian Singkat" {{ $isLocked ? 'disabled' : '' }}>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </form>
                    </div>
                @else
                    <div class="p-8 text-center bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-xs">
                        Tidak ada soal Isian Singkat pada bank soal ini.
                    </div>
                @endif
            </div>

            <!-- ============================================================= -->
            <!-- TAB 5: ESSAI / URAIAN                                         -->
            <!-- ============================================================= -->
            <div x-show="activeTab === 'esai'" x-cloak class="space-y-4">
                <!-- Summary Table Esai -->
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-xs text-left border-collapse table-bordered">
                        <thead>
                            <tr class="bg-slate-100 text-slate-800 text-center font-bold">
                                <th class="p-2 border border-slate-300">Jenis Soal</th>
                                <th class="p-2 border border-slate-300" colspan="2">Jumlah Soal</th>
                                <th class="p-2 border border-slate-300">Bobot Nilai</th>
                                <th class="p-2 border border-slate-300">Point Per-nomor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">Uraian / Essai</td>
                                <td class="p-2 border border-slate-300 font-medium">Seharusnya</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ $tampilEsai }}</td>
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">{{ $bobotEsai }}</td>
                                <td rowspan="3" class="p-2.5 text-center align-middle font-bold border border-slate-300">{{ $pointEsai }}</td>
                            </tr>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td class="p-2 border border-slate-300 font-medium">Telah dibuat</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ count($soalsEsai) }}</td>
                            </tr>
                            <tr class="bg-emerald-50/60 text-slate-800">
                                <td class="p-2 border border-slate-300 font-medium">Ditampilkan</td>
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ $totalEsaiTampil }}</td>
                            </tr>
                            @if(count($soalsEsai) < $tampilEsai || $totalEsaiTampil < $tampilEsai)
                                <tr class="bg-rose-50 text-rose-800">
                                    <td colspan="5" class="p-3 border border-slate-300">
                                        <span class="font-bold">Info:</span>
                                        <ul class="list-disc pl-5 mt-1 space-y-0.5 text-[11px]">
                                            @if(count($soalsEsai) < $tampilEsai)
                                                <li>Soal URAIAN/ESSAI masih kurang dari target.</li>
                                            @endif
                                            @if($totalEsaiTampil < $tampilEsai)
                                                <li>Jumlah soal yang ditampilkan tidak sama dengan seharusnya.</li>
                                            @endif
                                        </ul>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if(count($soalsEsai) > 0)
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 flex items-center justify-between flex-wrap gap-2 text-xs">
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800 select-none">
                                <input type="checkbox" id="check-all-esai" class="check-box-custom" {{ $isLocked ? 'disabled' : '' }}>
                                <span>Pilih Semua Essai</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <span>Jumlah soal terpilih: <b id="total-selected-esai" class="text-sm font-bold text-brand-600">{{ $totalEsaiTampil }}</b></span>
                                <button type="button" class="btn-save-selected px-3.5 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-bold shadow-xs transition flex items-center gap-1.5" data-jenis="5" data-target="{{ $tampilEsai }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    <span>Simpan Soal Terpilih</span>
                                </button>
                            </div>
                        </div>

                        <form id="form-esai" class="m-0">
                            <table class="w-full text-xs text-left border-collapse" id="table-esai">
                                <tbody class="divide-y divide-slate-200">
                                    @foreach($soalsEsai as $soal)
                                        <tr class="hover:bg-slate-50/70 transition" data-id="{{ $soal->id_soal }}">
                                            <td class="w-12 text-center p-3 align-top">
                                                <input type="checkbox" name="soal[]" value="{{ $soal->id_soal }}" class="check-soal-esai check-box-custom" {{ $soal->tampilkan ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                            </td>
                                            <td class="w-10 text-center p-3 align-top font-bold text-slate-700">
                                                {{ $soal->nomor_soal }}.
                                            </td>
                                            <td class="p-3 align-top space-y-2">
                                                <div class="text-slate-900 leading-relaxed text-sm">{!! $soal->soal !!}</div>
                                                <div class="text-xs text-slate-800">
                                                    Kunci/Pedoman Jawaban: <strong class="text-amber-800 font-bold font-mono">{{ is_array($soal->jawaban) ? json_encode($soal->jawaban) : $soal->jawaban }}</strong>
                                                </div>
                                            </td>
                                            <td class="w-20 text-right p-3 align-top">
                                                <button type="button" class="btn-hapus-soal p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition" data-id="{{ $soal->id_soal }}" data-nomor="{{ $soal->nomor_soal }}" data-jenis="Essai/Uraian" {{ $isLocked ? 'disabled' : '' }}>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </form>
                    </div>
                @else
                    <div class="p-8 text-center bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-xs">
                        Tidak ada soal Uraian / Essai pada bank soal ini.
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Hidden Container untuk Download Word Document -->
    <div id="for-export-doc" class="hidden">
        <h2>BANK SOAL: {{ $bank->bank_kode }}</h2>
        <p><strong>Mata Pelajaran:</strong> {{ $bank->mapel->nama_mapel ?? '-' }} | <strong>Kelas:</strong> {{ $bank->bank_level }} ({{ $kelasFormatted }}) | <strong>Guru:</strong> {{ $bank->guru->nama_guru ?? '-' }}</p>
        <hr>

        @if(count($soalsPg) > 0)
            <h3>I. Soal Pilihan Ganda</h3>
            <ol>
                @foreach($soalsPg as $sp)
                    <li style="margin-bottom: 12px;">
                        <div>{!! $sp->soal !!}</div>
                        <ul style="list-style-type: upper-alpha; padding-left: 20px;">
                            @if(!empty($sp->opsi_a)) <li>{!! is_array($sp->opsi_a) ? json_encode($sp->opsi_a) : $sp->opsi_a !!}</li> @endif
                            @if(!empty($sp->opsi_b)) <li>{!! is_array($sp->opsi_b) ? json_encode($sp->opsi_b) : $sp->opsi_b !!}</li> @endif
                            @if(!empty($sp->opsi_c)) <li>{!! is_array($sp->opsi_c) ? json_encode($sp->opsi_c) : $sp->opsi_c !!}</li> @endif
                            @if(!empty($sp->opsi_d)) <li>{!! is_array($sp->opsi_d) ? json_encode($sp->opsi_d) : $sp->opsi_d !!}</li> @endif
                            @if(!empty($sp->opsi_e)) <li>{!! is_array($sp->opsi_e) ? json_encode($sp->opsi_e) : $sp->opsi_e !!}</li> @endif
                        </ul>
                        <p><strong>Kunci:</strong> {{ strtoupper(is_array($sp->jawaban) ? json_encode($sp->jawaban) : (string)$sp->jawaban) }}</p>
                    </li>
                @endforeach
            </ol>
        @endif

        @if(count($soalsKompleks) > 0)
            <h3>II. Soal Pilihan Ganda Kompleks</h3>
            <ol>
                @foreach($soalsKompleks as $sk)
                    <li style="margin-bottom: 12px;">
                        <div>{!! $sk->soal !!}</div>
                        <p><strong>Kunci:</strong> {{ is_array($sk->jawaban) ? implode(', ', $sk->jawaban) : $sk->jawaban }}</p>
                    </li>
                @endforeach
            </ol>
        @endif

        @if(count($soalsJodoh) > 0)
            <h3>III. Soal Menjodohkan</h3>
            <ol>
                @foreach($soalsJodoh as $sj)
                    <li style="margin-bottom: 12px;">
                        <div>{!! $sj->soal !!}</div>
                    </li>
                @endforeach
            </ol>
        @endif

        @if(count($soalsIsian) > 0)
            <h3>IV. Soal Isian Singkat</h3>
            <ol>
                @foreach($soalsIsian as $si)
                    <li style="margin-bottom: 12px;">
                        <div>{!! $si->soal !!}</div>
                        <p><strong>Kunci:</strong> {{ is_array($si->jawaban) ? implode(', ', $si->jawaban) : $si->jawaban }}</p>
                    </li>
                @endforeach
            </ol>
        @endif

        @if(count($soalsEsai) > 0)
            <h3>V. Soal Essai / Uraian</h3>
            <ol>
                @foreach($soalsEsai as $se)
                    <li style="margin-bottom: 12px;">
                        <div>{!! $se->soal !!}</div>
                        <p><strong>Pedoman Jawaban:</strong> {{ is_array($se->jawaban) ? json_encode($se->jawaban) : $se->jawaban }}</p>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>

    <!-- Modal Tambah / Edit Butir Soal Baru -->
    <div x-show="openModalTambah" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="openModalTambah = false" class="bg-white border border-slate-200 rounded-2xl w-full max-w-2xl p-6 my-shadow max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="text-base font-black text-slate-900">Tambah Butir Soal Baru</h3>
                    <p class="text-xs text-slate-500">Bank Soal: {{ $bank->bank_kode }} &bull; {{ $bank->mapel->nama_mapel ?? '-' }}</p>
                </div>
                <button type="button" @click="openModalTambah = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('admin.cbt.bank_soal.soal.store', $bank->id_bank) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Soal</label>
                    <select name="jenis" x-model="jenisTambah" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 font-semibold">
                        <option value="1">1. Pilihan Ganda (PG Biasa)</option>
                        <option value="2">2. Pilihan Ganda Kompleks</option>
                        <option value="3">3. Menjodohkan</option>
                        <option value="4">4. Isian Singkat</option>
                        <option value="5">5. Essai / Uraian</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Teks Pertanyaan / Soal</label>
                    <textarea name="soal" rows="4" placeholder="Tuliskan pertanyaan soal di sini..." required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500 font-sans"></textarea>
                </div>

                <!-- Input Pilihan Jawaban untuk PG -->
                <div x-show="jenisTambah == 1 || jenisTambah == 2" class="space-y-2 pt-2 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-900">Pilihan Jawaban (A - E)</label>
                    @foreach(['a', 'b', 'c', 'd', 'e'] as $opt)
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-brand-600 shrink-0 uppercase">
                                {{ $opt }}
                            </span>
                            <input type="text" name="opsi_{{ $opt }}" placeholder="Pilihan {{ strtoupper($opt) }}..." class="flex-1 px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:border-brand-500">
                        </div>
                    @endforeach
                </div>

                <!-- Kunci Jawaban -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kunci Jawaban</label>
                    <input type="text" name="jawaban" placeholder="PG: A / B / C / D / E. Kompleks: A,C. Isian/Esai: Kata Kunci." required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 uppercase focus:outline-none focus:border-brand-500 font-mono">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="openModalTambah = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold shadow-xs">
                        Simpan Butir Soal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<!-- Local Vendor jQuery & SweetAlert2 (100% Offline) -->
<script src="{{ asset('assets/vendor/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendor/sweetalert2.all.min.js') }}"></script>

<script>
    const baseBankId = '{{ $bank->id_bank }}';
    const csrfToken = '{{ csrf_token() }}';

    $(document).ready(function() {
        // Setup CSRF untuk semua request AJAX jQuery
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        // 1. Checkbox "Pilih Semua" Handler untuk PG
        $('#check-all-pg').on('change', function() {
            $('.check-soal-pg').prop('checked', this.checked);
            updateSelectedCount('pg');
        });
        $('.check-soal-pg').on('change', function() {
            updateSelectedCount('pg');
        });

        // 2. Checkbox "Pilih Semua" Handler untuk Kompleks
        $('#check-all-kompleks').on('change', function() {
            $('.check-soal-kompleks').prop('checked', this.checked);
            updateSelectedCount('kompleks');
        });
        $('.check-soal-kompleks').on('change', function() {
            updateSelectedCount('kompleks');
        });

        // 3. Checkbox "Pilih Semua" Handler untuk Menjodohkan
        $('#check-all-jodoh').on('change', function() {
            $('.check-soal-jodoh').prop('checked', this.checked);
            updateSelectedCount('jodoh');
        });
        $('.check-soal-jodoh').on('change', function() {
            updateSelectedCount('jodoh');
        });

        // 4. Checkbox "Pilih Semua" Handler untuk Isian
        $('#check-all-isian').on('change', function() {
            $('.check-soal-isian').prop('checked', this.checked);
            updateSelectedCount('isian');
        });
        $('.check-soal-isian').on('change', function() {
            updateSelectedCount('isian');
        });

        // 5. Checkbox "Pilih Semua" Handler untuk Esai
        $('#check-all-esai').on('change', function() {
            $('.check-soal-esai').prop('checked', this.checked);
            updateSelectedCount('esai');
        });
        $('.check-soal-esai').on('change', function() {
            updateSelectedCount('esai');
        });

        function updateSelectedCount(type) {
            const checked = $(`.check-soal-${type}:checked`).length;
            const total = $(`.check-soal-${type}`).length;
            $(`#total-selected-${type}`).text(checked);
            $(`#check-all-${type}`).prop('checked', total > 0 && checked === total);
        }

        // Simpan Soal Terpilih AJAX Handler (Matching Garuda CBT saveSelected)
        $('.btn-save-selected').on('click', function(e) {
            e.preventDefault();
            const jenis = $(this).data('jenis');
            const target = parseInt($(this).data('target'), 10) || 0;
            
            let formId = '#form-pg';
            let checkClass = '.check-soal-pg';
            if (jenis == 2) { formId = '#form-kompleks'; checkClass = '.check-soal-kompleks'; }
            if (jenis == 3) { formId = '#form-jodoh'; checkClass = '.check-soal-jodoh'; }
            if (jenis == 4) { formId = '#form-isian'; checkClass = '.check-soal-isian'; }
            if (jenis == 5) { formId = '#form-esai'; checkClass = '.check-soal-esai'; }

            const checkedCount = $(`${checkClass}:checked`).length;

            if (checkedCount !== target && target > 0) {
                Swal.fire({
                    title: "Info",
                    html: `Jumlah butir terpilih: <b>${checkedCount}</b><br>Seharusnya: <b>${target}</b>`,
                    icon: "warning",
                    confirmButtonColor: "#4f46e5"
                });
                return;
            }

            const uncheckIds = [];
            $(`${checkClass}:not(:checked)`).each(function() {
                uncheckIds.push($(this).val());
            });

            const formData = new FormData($(formId)[0]);
            formData.append('id_bank', baseBankId);
            formData.append('jenis', jenis);
            formData.append('uncheck', JSON.stringify(uncheckIds));

            Swal.fire({
                title: "Menyimpan Soal Terpilih",
                text: "Sedang memproses, mohon tunggu...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('admin.cbt.bank_soal.save_selected') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(res) {
                    Swal.fire({
                        title: "Berhasil!",
                        html: `${res.check || checkedCount} butir soal terpilih berhasil disimpan.`,
                        icon: "success",
                        confirmButtonColor: "#4f46e5"
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: "Gagal!",
                        text: xhr.responseJSON?.message || "Terjadi kesalahan saat menyimpan soal terpilih.",
                        icon: "error",
                        confirmButtonColor: "#e11d48"
                    });
                }
            });
        });

        // Hapus Soal Handler (Matching Garuda CBT hapusSoal)
        $('.btn-hapus-soal').on('click', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const nomor = $(this).data('nomor');
            const jenis = $(this).data('jenis');

            Swal.fire({
                title: "Hapus Butir Soal?",
                html: `Soal berikut akan dihapus:<br>Nomor: <b>#${nomor}</b><br>Jenis: <b>${jenis}</b>`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#e11d48",
                cancelButtonColor: "#64748b",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        text: "Menghapus butir soal...",
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('admin.cbt.bank_soal.hapus_soal_ajax') }}",
                        type: "POST",
                        data: {
                            id_bank: baseBankId,
                            soal_id: id
                        },
                        dataType: "json",
                        success: function(res) {
                            Swal.fire({
                                title: "Berhasil!",
                                text: res.message || "Butir soal berhasil dihapus.",
                                icon: "success",
                                confirmButtonColor: "#4f46e5"
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: "Gagal!",
                                text: xhr.responseJSON?.message || "Gagal menghapus butir soal.",
                                icon: "error",
                                confirmButtonColor: "#e11d48"
                            });
                        }
                    });
                }
            });
        });

        // Download Word Document (.doc file export)
        $('#btn-download-word').on('click', function() {
            const htmlContent = document.getElementById('for-export-doc').innerHTML;
            const fullDoc = `
                <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
                <head><meta charset='utf-8'><title>Soal {{ $bank->bank_kode }}</title>
                <style>
                    body { font-family: 'Calibri', 'Arial', sans-serif; font-size: 11pt; }
                    table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
                    th, td { border: 1px solid black; padding: 6px; }
                    th { background-color: #f2f2f2; }
                </style>
                </head>
                <body>
                    ${htmlContent}
                </body>
                </html>
            `;
            const blob = new Blob(['\ufeff' + fullDoc], { type: 'application/msword' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Soal_{{ $bank->bank_kode }}_{{ $bank->mapel->kode ?? 'MAPEL' }}_Kls{{ $bank->bank_level }}.doc`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
    });
</script>
@endpush
