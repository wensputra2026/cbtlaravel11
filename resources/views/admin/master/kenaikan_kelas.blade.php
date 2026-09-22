@extends('layouts.admin')

@section('title', 'Kenaikan & Mutasi Rombel Kelas')
@section('page_title', 'Manajemen Kenaikan Kelas & Mutasi Rombel')

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: 'kenaikan',
    selectAll: false,
    selectedSiswa: [],
    toggleAll() {
        if (this.selectAll) {
            let checkboxes = document.querySelectorAll('.siswa-checkbox');
            this.selectedSiswa = Array.from(checkboxes).map(cb => cb.value);
        } else {
            this.selectedSiswa = [];
        }
    }
}">

    <!-- Header Actions & Tabs -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800">Siklus Tahunan Siswa & Rombel</h3>
            <p class="text-xs text-slate-500">Kelola kenaikan kelas ke tahun ajaran baru, perpindahan rombel, serta kelulusan alumni tingkat akhir</p>
        </div>

        <!-- Navigation Tabs -->
        <div class="inline-flex p-1 bg-slate-200 rounded-xl">
            <button @click="activeTab = 'kenaikan'" :class="activeTab === 'kenaikan' ? 'bg-white text-brand-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition">
                🚀 Kenaikan Kelas
            </button>
            <button @click="activeTab = 'mutasi'" :class="activeTab === 'mutasi' ? 'bg-white text-brand-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition">
                🔄 Mutasi Rombel
            </button>
            <button @click="activeTab = 'kelulusan'" :class="activeTab === 'kelulusan' ? 'bg-white text-brand-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition">
                🎓 Kelulusan (Alumni)
            </button>
        </div>
    </div>

    <!-- Filter Kelas & Tahun Ajaran Asal -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
        <form action="{{ route('admin.master.kenaikan_kelas') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Tahun Ajaran Sumber</label>
                <select name="tahun_ajaran_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    @foreach($allYears as $y)
                        <option value="{{ $y->id }}" {{ $selectedYearId == $y->id ? 'selected' : '' }}>
                            {{ $y->nama_lengkap ?? "T.P. {$y->tahun}" }} {{ $y->is_active ? '(Aktif Sistem)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 mb-1">Kelas / Rombel Asal</label>
                <select name="kelas_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                        @php $kid = $k->id_kelas ?? $k->id; @endphp
                        <option value="{{ $kid }}" {{ $selectedKelasId == $kid ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Main Form & Table Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Table: Daftar Siswa Terpilih (2 Kolom) -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h4 class="text-sm font-bold text-slate-800">Daftar Siswa di Kelas Ini</h4>
                    <p class="text-xs text-slate-500">Centang siswa yang akan diproses secara massal</p>
                </div>
                <div class="text-xs font-semibold text-brand-600">
                    <span x-text="selectedSiswa.length">0</span> siswa terpilih
                </div>
            </div>

            <div class="overflow-x-auto max-h-[500px]">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200 sticky top-0">
                        <tr>
                            <th class="py-3 px-3 w-10 text-center">
                                <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            </th>
                            <th class="py-3 px-3 w-12">No</th>
                            <th class="py-3 px-3">Nama Lengkap</th>
                            <th class="py-3 px-3">NISN</th>
                            <th class="py-3 px-3 text-center">Status Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if($enrollments->isNotEmpty())
                            @foreach($enrollments as $idx => $e)
                                @php $s = $e->masterSiswa ?? $e->siswa; @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-3 text-center">
                                        <input type="checkbox" value="{{ $e->siswa_id }}" x-model="selectedSiswa" class="siswa-checkbox rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    </td>
                                    <td class="py-3 px-3 text-slate-500">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-3 font-bold text-slate-800">{{ $s?->nama ?? $s?->nama_lengkap ?? 'Siswa' }}</td>
                                    <td class="py-3 px-3 font-mono text-slate-500">{{ $s?->nisn ?? '-' }}</td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $e->status === 'aktif' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-600' }}">
                                            {{ ucfirst($e->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @elseif($legacySiswa->isNotEmpty())
                            @foreach($legacySiswa as $idx => $ls)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-3 text-center">
                                        <input type="checkbox" value="{{ $ls->id_siswa }}" x-model="selectedSiswa" class="siswa-checkbox rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    </td>
                                    <td class="py-3 px-3 text-slate-500">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-3 font-bold text-slate-800">{{ $ls->nama }}</td>
                                    <td class="py-3 px-3 font-mono text-slate-500">{{ $ls->nisn ?? '-' }}</td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600">
                                            Aktif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">
                                    Tidak ada data siswa ditemukan pada kelas dan tahun ajaran ini.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Card: Form Aksi Sesuai Tab (1 Kolom) -->
        <div class="space-y-6">

            <!-- TAB 1: FORM KENAIKAN KELAS -->
            <div x-show="activeTab === 'kenaikan'" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h4 class="text-sm font-bold text-slate-800">Proses Kenaikan Kelas</h4>
                    <p class="text-[11px] text-slate-500">Promosikan siswa terpilih ke tingkatan rombel di Tahun Ajaran Baru</p>
                </div>

                <form action="{{ route('admin.master.kenaikan_kelas.promote') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="tahun_asal_id" value="{{ $selectedYearId }}">
                    <input type="hidden" name="kelas_asal_id" value="{{ $selectedKelasId }}">
                    
                    <!-- Hidden inputs untuk siswa terpilih -->
                    <template x-for="id in selectedSiswa" :key="id">
                        <input type="hidden" name="siswa_ids[]" :value="id">
                    </template>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tahun Ajaran Tujuan</label>
                        <select name="tahun_tujuan_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            @foreach($allYears as $y)
                                <option value="{{ $y->id }}">{{ $y->nama_lengkap ?? "T.P. {$y->tahun}" }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Kelas Tujuan</label>
                        <select name="kelas_tujuan_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            <option value="">-- Pilih Kelas Tujuan --</option>
                            @foreach($kelasList as $k)
                                @php $kid = $k->id_kelas ?? $k->id; @endphp
                                <option value="{{ $kid }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Keputusan Kenaikan</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2 border border-slate-200 rounded-xl text-xs cursor-pointer hover:bg-slate-50">
                                <input type="radio" name="action_type" value="naik" checked class="text-brand-600 focus:ring-brand-500">
                                <span class="font-bold text-emerald-600">Naik Kelas</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 border border-slate-200 rounded-xl text-xs cursor-pointer hover:bg-slate-50">
                                <input type="radio" name="action_type" value="tinggal" class="text-brand-600 focus:ring-brand-500">
                                <span class="font-bold text-rose-600">Tinggal Kelas</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="selectedSiswa.length === 0" class="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-500 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-xl text-xs font-bold transition shadow-sm">
                            Eksekusi Kenaikan Kelas (<span x-text="selectedSiswa.length">0</span>)
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 2: FORM PINDAH KELAS / MUTASI -->
            <div x-show="activeTab === 'mutasi'" style="display: none;" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h4 class="text-sm font-bold text-slate-800">Pindah Rombel (Mutasi)</h4>
                    <p class="text-[11px] text-slate-500">Pindahkan siswa ke rombel lain dalam tahun ajaran yang sedang berjalan</p>
                </div>

                <form action="{{ route('admin.master.kenaikan_kelas.switch') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYearId }}">
                    
                    <template x-for="id in selectedSiswa" :key="id">
                        <input type="hidden" name="siswa_ids[]" :value="id">
                    </template>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Rombel / Kelas Baru</label>
                        <select name="kelas_tujuan_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                            <option value="">-- Pilih Kelas Baru --</option>
                            @foreach($kelasList as $k)
                                @php $kid = $k->id_kelas ?? $k->id; @endphp
                                <option value="{{ $kid }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Alasan Perpindahan</label>
                        <input type="text" name="alasan" placeholder="Misal: Penyesuaian peminatan jurusan" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="selectedSiswa.length === 0" class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-500 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-xl text-xs font-bold transition shadow-sm">
                            Pindahkan Siswa (<span x-text="selectedSiswa.length">0</span>)
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB 3: FORM KELULUSAN / ALUMNI -->
            <div x-show="activeTab === 'kelulusan'" style="display: none;" class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h4 class="text-sm font-bold text-slate-800">Kelulusan Siswa (Set Alumni)</h4>
                    <p class="text-[11px] text-slate-500">Nyatakan kelulusan untuk siswa tingkat akhir dan arsipkan ke buku alumni</p>
                </div>

                <form action="{{ route('admin.master.kenaikan_kelas.graduate') }}" method="POST" class="space-y-3" onsubmit="return confirm('Apakah Anda yakin ingin menyatakan lulus dan mengarsipkan siswa terpilih menjadi ALUMNI?')">
                    @csrf
                    <input type="hidden" name="tahun_ajaran_id" value="{{ $selectedYearId }}">
                    
                    <template x-for="id in selectedSiswa" :key="id">
                        <input type="hidden" name="siswa_ids[]" :value="id">
                    </template>

                    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 leading-relaxed">
                        Siswa yang dinyatakan lulus akan dinonaktifkan dari rombel kelas aktif, dan seluruh riwayat nilai serta ujian tetap tersimpan aman di database. Siswa akan muncul pada menu <strong>Arsip Alumni Siswa</strong>.
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="selectedSiswa.length === 0" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-xl text-xs font-bold transition shadow-sm">
                            Nyatakan Lulus & Arsipkan (<span x-text="selectedSiswa.length">0</span>)
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
