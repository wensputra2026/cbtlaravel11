@extends('layouts.admin')

@section('title', 'Bank Soal Ujian')
@section('page_title', 'Bank Soal Ujian')

@push('styles')
<style>
    .text-maroon { color: #b91c1c !important; }
    .bg-maroon { background-color: #b91c1c !important; }
    .text-yellow { color: #f59e0b !important; }
    .bg-yellow { background-color: #f59e0b !important; }
    .btn-disabled { opacity: 0.5; pointer-events: none; cursor: not-allowed !important; }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="adminBankSoalApp()">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Bank Soal</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Tahun Pelajaran {{ $tp_active->tahun ?? '-' }} Semester {{ $smt_active->smt ?? '-' }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Reload -->
            <a href="{{ route('admin.cbt.bank_soal.index', ['type' => $type, 'mode' => $mode, 'id' => $idFilter]) }}" 
               class="px-3 py-1.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                <i class="fa fa-sync text-slate-500"></i>
                <span>Reload</span>
            </a>

            <!-- + Tambah Bank Soal -->
            <a href="{{ route('admin.cbt.bank_soal.create') }}"
               class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                <i class="fas fa-plus-circle"></i>
                <span>Tambah Bank Soal</span>
            </a>

            <!-- Copy Bank Soal -->
            <button type="button" @click="modalCopy = true"
                    class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                <i class="fa fa-copy"></i>
                <span>Copy Bank Soal</span>
            </button>

            <!-- Mode Toggle (List vs Grid) -->
            <div class="inline-flex rounded-lg border border-slate-300 p-0.5 bg-slate-100 shadow-xs">
                <a href="{{ route('admin.cbt.bank_soal.index', array_merge(request()->query(), ['mode' => '1'])) }}" 
                   title="Mode Tabel / List"
                   class="px-2.5 py-1 rounded-md text-xs font-bold transition {{ $mode == '1' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                    <i class="fa fa-list"></i>
                </a>
                <a href="{{ route('admin.cbt.bank_soal.index', array_merge(request()->query(), ['mode' => '2'])) }}" 
                   title="Mode Grid / Kartu"
                   class="px-2.5 py-1 rounded-md text-xs font-bold transition {{ $mode == '2' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                    <i class="fa fa-th-large"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs space-y-5">
        
        <!-- Subjudul & Color Code Legend -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200">
            <div>
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Soal & Paket Ujian</h2>
            </div>
            <!-- Kode Warna Bar -->
            <div class="flex items-center gap-4 flex-wrap text-xs font-semibold bg-slate-50 border border-slate-200 px-3.5 py-2 rounded-xl">
                <span class="text-slate-500 font-bold">Kode Warna:</span>
                <div class="flex items-center gap-1.5">
                    <i class="fas fa-square text-slate-400 text-sm"></i>
                    <span class="text-slate-600 text-[11px]">Tidak digunakan (bisa dihapus)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <i class="fas fa-square text-yellow text-sm"></i>
                    <span class="text-slate-600 text-[11px]">Digunakan jadwal</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <i class="fas fa-square text-maroon text-sm"></i>
                    <span class="text-slate-600 text-[11px]">Digunakan siswa</span>
                </div>
            </div>
        </div>

        <!-- Filter Bar & Bulk Actions -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center bg-slate-50/70 p-3.5 rounded-xl border border-slate-200">
            <!-- Filter Left -->
            <div class="md:col-span-8 flex items-center gap-2.5 flex-wrap">
                <span class="text-xs font-bold text-slate-700">Filter:</span>
                
                <!-- Main Filter Select -->
                <select id="filter_type" x-model="filterType" @change="onFilterTypeChange()"
                        class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-500">
                    @foreach($filters as $val => $lbl)
                        <option value="{{ $val }}" {{ $type == (string)$val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>

                <!-- Sub-Filter Guru -->
                <div x-show="filterType == '1'" x-cloak>
                    <select id="filter_guru" x-model="filterId" @change="applyFilter()"
                            class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-500">
                        <option value="">-- Pilih Guru --</option>
                        @foreach($gurus as $gId => $gName)
                            <option value="{{ $gId }}" {{ ($type == '1' && $idFilter == $gId) ? 'selected' : '' }}>{{ $gName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sub-Filter Mapel -->
                <div x-show="filterType == '2'" x-cloak>
                    <select id="filter_mapel" x-model="filterId" @change="applyFilter()"
                            class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-500">
                        <option value="">-- Pilih Mapel --</option>
                        @foreach($mapels as $mId => $mName)
                            <option value="{{ $mId }}" {{ ($type == '2' && $idFilter == $mId) ? 'selected' : '' }}>{{ $mName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sub-Filter Level -->
                <div x-show="filterType == '3'" x-cloak>
                    <select id="filter_level" x-model="filterId" @change="applyFilter()"
                            class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-500">
                        <option value="">-- Pilih Level Kelas --</option>
                        @foreach($levels as $lVal => $lLbl)
                            <option value="{{ $lVal }}" {{ ($type == '3' && $idFilter == $lVal) ? 'selected' : '' }}>Kelas {{ $lLbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Bulk Delete Right -->
            <div class="md:col-span-4 flex items-center justify-end gap-3">
                <button type="button" @click="submitBulkDelete()" :disabled="selectedIds.length === 0"
                        class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-500 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                    <i class="far fa-trash-alt"></i>
                    <span>Hapus Terpilih (<span x-text="selectedIds.length"></span>)</span>
                </button>
                <div class="flex items-center gap-1.5 pl-2 border-l border-slate-200">
                    <input type="checkbox" id="check-all" @click="toggleSelectAll($event)" :checked="isAllSelected()"
                           class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer">
                    <label for="check-all" class="text-[11px] font-bold text-slate-600 cursor-pointer select-none">Semua</label>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div id="konten" class="pt-2">

            @if(count($banksToday) > 0)

                <!-- ======================================================= -->
                <!-- MODE 1: TABEL / LIST VIEW (Garuda CBT Authentic Parity) -->
                <!-- ======================================================= -->
                @if($mode == '1')
                <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-2xs">
                    <table class="w-full text-left text-xs text-slate-700 border-collapse">
                        <thead class="bg-slate-100/80 text-slate-700 uppercase text-[11px] tracking-wider border-b border-slate-200 font-bold">
                            <tr>
                                <th class="py-3 px-3 w-12 text-center align-middle">No.</th>
                                <th class="py-3 px-4 align-middle">Kode</th>
                                <th class="py-3 px-4 align-middle">Mapel</th>
                                <th class="py-3 px-4 align-middle">Guru</th>
                                <th class="py-3 px-4 align-middle">Kelas</th>
                                <th class="py-3 px-3 text-center align-middle w-24">Sudah<br/>Import</th>
                                <th class="py-3 px-4 text-center align-middle">Aksi</th>
                                <th class="py-3 px-3 text-center align-middle w-12">
                                    <span class="sr-only">Pilih</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white font-medium">
                            @foreach($banksToday as $idx => $bank)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <!-- No -->
                                    <td class="py-3 px-3 text-center align-middle text-slate-400 font-semibold">
                                        {{ $idx + 1 }}
                                    </td>

                                    <!-- Kode dengan Status Square Indicator -->
                                    <td class="py-3 px-4 align-middle">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-square text-base {{ $bank->icon_color }}" 
                                               title="{{ $bank->status_color == 'maroon' ? 'Digunakan siswa' : ($bank->status_color == 'yellow' ? 'Digunakan jadwal' : 'Tidak digunakan (bisa dihapus)') }}"></i>
                                            <span class="font-bold text-slate-900 font-mono text-xs">{{ $bank->bank_kode }}</span>
                                        </div>
                                    </td>

                                    <!-- Mapel & Agama -->
                                    <td class="py-3 px-4 align-middle">
                                        <div class="font-bold text-slate-900 leading-snug">
                                            {{ $bank->mapel->nama_mapel ?? '-' }}
                                        </div>
                                        <div class="mt-0.5">
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                {{ (!empty($bank->soal_agama) && $bank->soal_agama != '-') ? $bank->soal_agama : 'Umum' }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Guru Pembuat -->
                                    <td class="py-3 px-4 align-middle text-slate-800 font-semibold">
                                        {{ $bank->guru->nama_guru ?? '-' }}
                                    </td>

                                    <!-- Kelas Badges -->
                                    <td class="py-3 px-4 align-middle">
                                        <div class="flex items-center gap-1 flex-wrap">
                                            @forelse($bank->kelas_names as $kName)
                                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                    {{ $kName }}
                                                </span>
                                            @empty
                                                <span class="text-slate-400 italic text-[11px]">-</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    <!-- Status Sudah Import -->
                                    <td class="py-3 px-3 text-center align-middle">
                                        @if($bank->total_soal == 0)
                                            <span title="Belum ada butir soal" class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-rose-50 text-rose-600">
                                                <i class="fas fa-times font-bold text-sm"></i>
                                            </span>
                                        @else
                                            <span title="{{ $bank->total_soal }} butir soal tersedia" class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-50 text-emerald-600">
                                                <i class="fas fa-check font-bold text-sm"></i>
                                            </span>
                                            <div class="text-[10px] font-bold text-slate-500 mt-0.5">({{ $bank->total_soal }})</div>
                                        @endif
                                    </td>

                                    <!-- Tombol Aksi -->
                                    <td class="py-3 px-4 text-center align-middle whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
                                            <!-- Edit Bank Soal -->
                                            <a href="{{ $bank->can_edit ? route('admin.cbt.bank_soal.edit', $bank->id_bank) : 'javascript:void(0)' }}"
                                               class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition {{ $bank->can_edit ? 'bg-amber-500 hover:bg-amber-400 text-white shadow-xs' : 'btn-disabled bg-slate-200 text-slate-400' }}"
                                               title="{{ $bank->can_edit ? 'Edit Bank Soal' : 'Terkunci (Sedang/sudah digunakan siswa)' }}">
                                                <i class="fa fa-pencil-alt"></i>
                                            </a>

                                            <!-- Import Soal -->
                                            <button type="button" @click="confirmImport({{ $bank->id_bank }}, {{ $bank->total_soal }})"
                                                    class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition {{ $bank->can_edit ? 'bg-amber-600 hover:bg-amber-500 text-white shadow-xs' : 'btn-disabled bg-slate-200 text-slate-400' }}"
                                                    title="{{ $bank->can_edit ? 'Import Butir Soal' : 'Terkunci' }}">
                                                <i class="fas fa-upload mr-1"></i>Import
                                            </button>

                                            <!-- Detail / Buat Soal -->
                                            <a href="{{ route('admin.cbt.bank_soal.show', $bank->id_bank) }}"
                                               class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs transition flex items-center gap-1">
                                                @if($bank->total_soal == 0)
                                                    <i class="fas fa-plus"></i>
                                                    <span>Buat Soal</span>
                                                @else
                                                    <i class="fas fa-eye"></i>
                                                    <span>Detail</span>
                                                @endif
                                            </a>

                                            <!-- Download Soal Naskah Kertas -->
                                            <a href="{{ route('admin.cbt.bank_soal.download_word', $bank->id_bank) }}"
                                               title="Download Naskah Soal Word (Ujian Kertas)"
                                               class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-blue-600 hover:bg-blue-500 text-white shadow-xs transition">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </td>

                                    <!-- Checkbox Hapus -->
                                    <td class="py-3 px-3 text-center align-middle">
                                        <input type="checkbox" value="{{ $bank->id_bank }}" 
                                               x-model="selectedIds"
                                               class="check-bank w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- ======================================================= -->
                <!-- MODE 2: GRID / CARDS VIEW (Garuda CBT Authentic Parity) -->
                <!-- ======================================================= -->
                @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($banksToday as $bank)
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs flex flex-col hover:shadow-md transition">
                            
                            <!-- Card Header (Color Coded) -->
                            <div class="px-4 py-3 flex items-center justify-between text-white {{ $bank->status_badge }}">
                                <h3 class="font-bold font-mono text-sm tracking-wide flex items-center gap-2">
                                    <i class="fas fa-book"></i>
                                    <span>{{ $bank->bank_kode }}</span>
                                </h3>
                                <div class="flex items-center gap-2">
                                    <!-- Edit Button -->
                                    <a href="{{ $bank->can_edit ? route('admin.cbt.bank_soal.edit', $bank->id_bank) : 'javascript:void(0)' }}"
                                       class="w-7 h-7 rounded-lg bg-white/20 hover:bg-white/30 text-white flex items-center justify-center text-xs transition {{ $bank->can_edit ? '' : 'opacity-50 pointer-events-none' }}"
                                       title="Edit Bank Soal">
                                        <i class="fa fa-pencil-alt"></i>
                                    </a>
                                    <!-- Checkbox -->
                                    <input type="checkbox" value="{{ $bank->id_bank }}" x-model="selectedIds"
                                           class="w-4 h-4 rounded text-blue-600 border-white/40 focus:ring-0 cursor-pointer">
                                </div>
                            </div>

                            <!-- Card Body Specifications -->
                            <div class="p-4 flex-1">
                                <ul class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                                    <li class="py-2 flex justify-between items-center">
                                        <span class="text-slate-500">Guru</span>
                                        <span class="font-bold text-slate-900 truncate max-w-[180px]" title="{{ $bank->guru->nama_guru ?? '-' }}">{{ $bank->guru->nama_guru ?? '-' }}</span>
                                    </li>
                                    <li class="py-2 flex justify-between items-center">
                                        <span class="text-slate-500">Mapel</span>
                                        <span class="font-bold text-slate-900">{{ $bank->mapel->nama_mapel ?? '-' }}</span>
                                    </li>
                                    <li class="py-2 flex justify-between items-start gap-2">
                                        <span class="text-slate-500 shrink-0">Kelas</span>
                                        <span class="font-bold text-slate-900 text-right">
                                            @if(!empty($bank->kelas_names))
                                                {{ implode(', ', $bank->kelas_names) }}
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </li>
                                    <li class="py-2 flex justify-between items-center">
                                        <span class="text-slate-500">Agama</span>
                                        <span class="font-bold text-slate-900">{{ (!empty($bank->soal_agama) && $bank->soal_agama != '-') ? $bank->soal_agama : 'Umum' }}</span>
                                    </li>
                                    <li class="py-2 flex justify-between items-center">
                                        <span class="text-slate-500">Jumlah Soal</span>
                                        <span class="font-bold text-slate-900">
                                            @if($bank->total_soal == 0)
                                                <span class="text-rose-600 font-bold">Belum dibuat</span>
                                            @elseif($bank->total_soal < ($bank->tampil_pg + $bank->tampil_esai))
                                                <span class="text-amber-600 font-bold">Belum selesai ({{ $bank->total_soal }})</span>
                                            @else
                                                <span class="text-emerald-700 font-bold">{{ $bank->total_soal }} Butir</span>
                                            @endif
                                        </span>
                                    </li>
                                    <li class="py-2 flex justify-between items-center">
                                        <span class="text-slate-500">Dibuat</span>
                                        <span class="font-bold text-slate-900">{{ $bank->date ? date('d M Y - H:i', strtotime($bank->date)) : '-' }}</span>
                                    </li>
                                    <li class="py-2 flex justify-between items-center">
                                        <span class="text-slate-500">Status</span>
                                        <span class="font-bold {{ $bank->status == 1 ? 'text-emerald-600' : 'text-slate-400' }}">
                                            {{ $bank->status == 1 ? 'Aktif' : 'Non Aktif' }}
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Card Footer Action Buttons -->
                            <div class="p-3.5 bg-slate-50 border-t border-slate-200 space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="confirmImport({{ $bank->id_bank }}, {{ $bank->total_soal }})"
                                            class="w-full py-2 bg-amber-500 hover:bg-amber-400 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs {{ $bank->can_edit ? '' : 'btn-disabled' }}">
                                        <i class="fas fa-upload"></i>
                                        <span>Import Soal</span>
                                    </button>
                                    <a href="{{ route('admin.cbt.bank_soal.show', $bank->id_bank) }}"
                                       class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                                        @if($bank->total_soal == 0)
                                            <i class="fas fa-plus"></i>
                                            <span>Buat Soal</span>
                                        @else
                                            <i class="fas fa-eye"></i>
                                            <span>Detail Soal</span>
                                        @endif
                                    </a>
                                </div>
                                <div>
                                    <a href="{{ route('admin.cbt.bank_soal.download_word', $bank->id_bank) }}"
                                       class="w-full py-1.5 bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 rounded-xl text-xs font-bold transition flex flex-col items-center justify-center leading-tight shadow-xs">
                                        <span class="flex items-center gap-1.5 text-blue-600">
                                            <i class="fas fa-download"></i>
                                            <span>Download Naskah Soal</span>
                                        </span>
                                        <span class="text-[10px] text-slate-400 italic">untuk keperluan ujian kertas</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif

            @else
                <!-- Alert Belum Ada Bank Soal -->
                <div class="p-6 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs text-center font-medium">
                    <i class="fas fa-info-circle text-base text-amber-600 mb-1"></i>
                    <p class="font-bold text-sm text-amber-900">Belum ada data Bank Soal yang sesuai dengan filter.</p>
                    <p class="mt-1 text-amber-700">Silakan ubah filter atau buat Bank Soal baru dengan menekan tombol <b>Tambah Bank Soal</b> di atas.</p>
                </div>
            @endif

        </div>
    </div>

    <!-- ================================================================= -->
    <!-- MODAL: COPY BANK SOAL (Garuda CBT Authentic Parity)               -->
    <!-- ================================================================= -->
    <div x-show="modalCopy" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/50 backdrop-blur-xs">
        <div @click.away="modalCopy = false" 
             class="bg-white border border-slate-200 rounded-2xl w-full max-w-4xl shadow-2xl max-h-[90vh] flex flex-col overflow-hidden">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                <div>
                    <h3 class="text-base font-black text-slate-900">Salin / Copy Bank Soal</h3>
                    <p class="text-xs text-slate-500 font-medium">Pilih bank soal yang ingin disalin ke Tahun Pelajaran aktif ({{ $tp_active->tahun ?? '-' }} {{ $smt_active->smt ?? '-' }})</p>
                </div>
                <button type="button" @click="modalCopy = false" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg transition">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <!-- Search Bar in Modal -->
            <div class="p-4 border-b border-slate-200 bg-white">
                <input type="text" x-model="searchCopy" placeholder="Cari kode atau mata pelajaran..."
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-500">
            </div>

            <!-- Table of Banks Available to Copy -->
            <div class="flex-1 overflow-y-auto p-4">
                <table class="w-full text-left text-xs text-slate-700 border border-slate-200 divide-y divide-slate-200 rounded-xl overflow-hidden">
                    <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] tracking-wider font-bold">
                        <tr>
                            <th class="py-2.5 px-3 w-12 text-center">No</th>
                            <th class="py-2.5 px-3">Kode</th>
                            <th class="py-2.5 px-3">Mapel</th>
                            <th class="py-2.5 px-3">Guru</th>
                            <th class="py-2.5 px-3 text-center">Tingkat</th>
                            <th class="py-2.5 px-3">TP/SMT</th>
                            <th class="py-2.5 px-3 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-medium">
                        @forelse($allBanksForCopy as $cIdx => $cb)
                            <tr class="hover:bg-slate-50 transition" 
                                x-show="!searchCopy || '{{ strtolower($cb->bank_kode . ' ' . ($cb->mapel->nama_mapel ?? '') . ' ' . ($cb->guru->nama_guru ?? '')) }}'.includes(searchCopy.toLowerCase())">
                                <td class="py-2.5 px-3 text-center text-slate-400">{{ $cIdx + 1 }}</td>
                                <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $cb->bank_kode }}</td>
                                <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $cb->mapel->nama_mapel ?? '-' }}</td>
                                <td class="py-2.5 px-3 text-slate-700">{{ $cb->guru->nama_guru ?? '-' }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Kelas {{ $cb->bank_level }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-slate-500 text-[11px]">TP {{ $cb->id_tp }} / SMT {{ $cb->id_smt }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    <button type="button" @click="executeCopy({{ $cb->id_bank }}, '{{ $cb->bank_kode }}')"
                                            class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold shadow-xs transition flex items-center justify-center gap-1 w-full">
                                        <i class="fa fa-copy"></i>
                                        <span>Copy</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-slate-400">Tidak ada bank soal sebelumnya.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-3 border-t border-slate-200 bg-slate-50 flex justify-end">
                <button type="button" @click="modalCopy = false" 
                        class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition">
                    Tutup
                </button>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script>
function adminBankSoalApp() {
    return {
        mode: '{{ $mode }}',
        filterType: '{{ $type }}',
        filterId: '{{ $idFilter ?? "" }}',
        selectedIds: [],
        allIds: @json(array_map(fn($b) => $b->id_bank, $banksToday)),

        // Copy Modal State
        modalCopy: false,
        searchCopy: '',

        init() {
            // Auto init
        },

        onFilterTypeChange() {
            if (this.filterType === '0') {
                window.location.href = "{{ route('admin.cbt.bank_soal.index') }}?type=0&mode=" + this.mode;
            }
        },

        applyFilter() {
            if (this.filterId) {
                window.location.href = "{{ route('admin.cbt.bank_soal.index') }}?type=" + this.filterType + "&id=" + this.filterId + "&mode=" + this.mode;
            }
        },

        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedIds = [...this.allIds];
            } else {
                this.selectedIds = [];
            }
        },

        isAllSelected() {
            return this.allIds.length > 0 && this.selectedIds.length === this.allIds.length;
        },

        confirmImport(bankId, totalSoal) {
            const targetUrl = "{{ url('/admin/cbt/bank-soal') }}/" + bankId + "/import";
            if (totalSoal > 0) {
                Swal.fire({
                    title: "Import Soal?",
                    html: "Soal sudah ada (" + totalSoal + " butir). Mengimport soal baru akan menambahkan butir soal ke dalam bank soal ini.<br>Lanjutkan import soal?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#059669",
                    cancelButtonColor: "#e11d48",
                    confirmButtonText: "Lanjut",
                    cancelButtonText: "Batal"
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.href = targetUrl;
                    }
                });
            } else {
                window.location.href = targetUrl;
            }
        },

        executeCopy(bankId, bankKode) {
            Swal.fire({
                title: "Copy Bank Soal?",
                html: "Bank Soal <b>" + bankKode + "</b> beserta butir-butir soalnya akan disalin ke Tahun Pelajaran aktif.<br><small class='text-slate-500'>Periksa kembali alokasi kelas bank soal setelah copy.</small>",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#059669",
                cancelButtonColor: "#64748b",
                confirmButtonText: "Copy Sekarang",
                cancelButtonText: "Batal"
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menyalin Bank Soal...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch("{{ url('/admin/cbt/bank-soal/duplicate') }}/" + bankId, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status) {
                            Swal.fire({
                                title: 'Berhasil',
                                text: data.message,
                                icon: 'success'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', data.message || 'Tidak bisa menyalin bank soal', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Gagal memproses duplikasi', 'error');
                    });
                }
            });
        },

        submitBulkDelete() {
            if (this.selectedIds.length === 0) {
                Swal.fire('', 'Pilih minimal satu bank soal yang akan dihapus', 'warning');
                return;
            }

            Swal.fire({
                title: "Anda yakin?",
                text: "Sebanyak " + this.selectedIds.length + " Bank Soal yang terpilih akan dihapus permanen!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#e11d48",
                cancelButtonColor: "#64748b",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal"
            }).then(result => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus data...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    fetch("{{ route('admin.cbt.bank_soal.bulk_delete') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids: this.selectedIds })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status) {
                            Swal.fire({
                                title: 'Berhasil',
                                text: data.message,
                                icon: 'success'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', data.message || 'Tidak bisa menghapus bank soal terpilih', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Koneksi server gagal', 'error');
                    });
                }
            });
        }
    };
}
</script>
@endpush
@endsection
