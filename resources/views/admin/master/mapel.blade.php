@extends('layouts.admin')

@section('title', 'Mata Pelajaran')
@section('page_title', 'Mata Pelajaran')

@section('content')
<div class="space-y-6" x-data="{
    // Modal Kelompok Utama
    kelModal: false,
    kelIsEdit: false,
    kelForm: { id_kel_mapel: '', kode_kel_mapel: '', nama_kel_mapel: '', kategori: 'WAJIB', id_parent: 0 },
    openKelModal(item = null) {
        if (item) {
            this.kelIsEdit = true;
            this.kelForm = { id_kel_mapel: item.id_kel_mapel, kode_kel_mapel: item.kode_kel_mapel, nama_kel_mapel: item.nama_kel_mapel, kategori: item.kategori, id_parent: 0 };
        } else {
            this.kelIsEdit = false;
            this.kelForm = { id_kel_mapel: '', kode_kel_mapel: '', nama_kel_mapel: '', kategori: 'WAJIB', id_parent: 0 };
        }
        this.kelModal = true;
    },

    // Modal Sub Kelompok
    subModal: false,
    subIsEdit: false,
    subForm: { id_kel_mapel: '', kode_kel_mapel: '', nama_kel_mapel: '', id_parent: '{{ $kelompokUtama->first()?->id_kel_mapel ?? '' }}' },
    openSubModal(item = null) {
        if (item) {
            this.subIsEdit = true;
            this.subForm = { id_kel_mapel: item.id_kel_mapel, kode_kel_mapel: item.kode_kel_mapel, nama_kel_mapel: item.nama_kel_mapel, id_parent: item.id_parent };
        } else {
            this.subIsEdit = false;
            this.subForm = { id_kel_mapel: '', kode_kel_mapel: '', nama_kel_mapel: '', id_parent: '{{ $kelompokUtama->first()?->id_kel_mapel ?? '' }}' };
        }
        this.subModal = true;
    },

    // Modal Mapel
    mapelModal: false,
    mapelIsEdit: false,
    mapelActionUrl: '{{ route('admin.master.mapel.store') }}',
    mapelForm: { id_mapel: '', nama_mapel: '', kode: '', kelompok: '', urutan_tampil: '{{ count($mapelList) + 1 }}', mapel_agama: '0' },
    openMapelModal(item = null) {
        if (item) {
            this.mapelIsEdit = true;
            this.mapelActionUrl = '{{ url('admin/master/mapel/update') }}/' + item.id_mapel;
            this.mapelForm = {
                id_mapel: item.id_mapel,
                nama_mapel: item.nama_mapel,
                kode: item.kode,
                kelompok: item.kelompok || '',
                urutan_tampil: item.urutan_tampil || 0,
                mapel_agama: item.mapel_agama ? '1' : '0'
            };
        } else {
            this.mapelIsEdit = false;
            this.mapelActionUrl = '{{ route('admin.master.mapel.store') }}';
            this.mapelForm = {
                id_mapel: '',
                nama_mapel: '',
                kode: '',
                kelompok: '{{ $allKelompok->first()?->kode_kel_mapel ?? '' }}',
                urutan_tampil: '{{ count($mapelList) + 1 }}',
                mapel_agama: '0'
            };
        }
        this.mapelModal = true;
    }
}">

    <!-- Alert Notifikasi Flash -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
        </div>
    @endif

    <!-- BAGIAN 1: KELOMPOK UTAMA & SUB KELOMPOK (PARITAS GARUDA CBT DATAMAPEL) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- CARD 1: KELOMPOK UTAMA -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        <span>Kelompok Utama</span>
                    </h4>
                    <p class="text-[11px] text-slate-500">Kategori kelompok kurikulum rapor</p>
                </div>
                <button type="button" @click="openKelModal()" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-200 transition flex items-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 border-collapse">
                    <thead class="bg-slate-50 text-slate-600 text-[11px] uppercase tracking-wider font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Kategori</th>
                            <th class="py-2.5 px-3 text-center w-16">Kode</th>
                            <th class="py-2.5 px-3">Nama Kelompok</th>
                            <th class="py-2.5 px-3 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($kelompokUtama as $ku)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-2.5 px-3">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold text-[10px]">
                                        {{ $ku->kategori }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 font-mono font-bold text-indigo-700 text-center">{{ $ku->kode_kel_mapel }}</td>
                                <td class="py-2.5 px-3 font-medium text-slate-800">{{ $ku->nama_kel_mapel }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="openKelModal({{ json_encode($ku) }})" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form action="{{ route('admin.master.mapel.kelompok.destroy', $ku->id_kel_mapel) }}" method="POST" onsubmit="return confirm('Hapus kelompok utama ini beserta sub kelompoknya?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Belum ada data Kelompok Utama.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CARD 2: SUB KELOMPOK -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                        <span>Sub Kelompok</span>
                    </h4>
                    <p class="text-[11px] text-slate-500">Pembagian turunan dari kelompok utama</p>
                </div>
                <button type="button" @click="openSubModal()" class="px-3 py-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold text-xs rounded-xl border border-sky-200 transition flex items-center gap-1.5 shadow-xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700 border-collapse">
                    <thead class="bg-slate-50 text-slate-600 text-[11px] uppercase tracking-wider font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3 text-center w-16">Kode</th>
                            <th class="py-2.5 px-3">Nama Sub Kelompok</th>
                            <th class="py-2.5 px-3">Kel. Utama</th>
                            <th class="py-2.5 px-3 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($subKelompok as $sk)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-2.5 px-3 font-mono font-bold text-sky-700 text-center">{{ $sk->kode_kel_mapel }}</td>
                                <td class="py-2.5 px-3 font-medium text-slate-800">{{ $sk->nama_kel_mapel }}</td>
                                <td class="py-2.5 px-3 text-slate-500">
                                    {{ $sk->parent?->nama_kel_mapel ?? 'Parent #' . $sk->id_parent }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="openSubModal({{ json_encode($sk) }})" class="p-1.5 text-slate-500 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition" title="Edit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form action="{{ route('admin.master.mapel.kelompok.destroy', $sk->id_kel_mapel) }}" method="POST" onsubmit="return confirm('Hapus sub kelompok ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Belum ada data Sub Kelompok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- BAGIAN 2: DAFTAR MATA PELAJARAN UTAMA -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Daftar Mata Pelajaran</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Kelola seluruh mata pelajaran kurikulum aktif di sekolah</p>
            </div>
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="location.reload()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5 border border-slate-200">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Reload</span>
                </button>
                <button type="button" @click="openMapelModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Mapel</span>
                </button>
            </div>
        </div>

        <!-- Alert Catatan Rapor Garuda CBT -->
        <div class="p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-xl text-xs text-emerald-900 flex items-center gap-2.5">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <strong>Catatan:</strong> <b>Nomor Urut Rapor</b> dan <b>Kelompok</b> diperlukan jika ingin mencetak nilai dan rapor siswa.
            </div>
        </div>

        <!-- Table Mata Pelajaran -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 border-collapse">
                <thead class="bg-slate-50 text-slate-600 text-[11px] uppercase tracking-wider font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-3 text-center w-24">No. Urut Rapor</th>
                        <th class="py-3 px-4">Mata Pelajaran</th>
                        <th class="py-3 px-4 text-center w-40">Kode Mata Pelajaran</th>
                        <th class="py-3 px-4 text-center w-32">Kelompok</th>
                        <th class="py-3 px-3 text-center w-24">Status</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($mapelList as $mapel)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-3 text-center font-bold text-slate-600">
                                {{ $mapel->urutan_tampil ?? '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $mapel->nama_mapel }}</div>
                                @if($mapel->mapel_agama == 1)
                                    <span class="inline-block mt-0.5 px-2 py-0.2 text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded">
                                        Mapel Agama
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold text-emerald-700">
                                {{ $mapel->kode }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if(!empty($mapel->kelompok))
                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-md font-bold text-[11px]">
                                        {{ $mapel->kelompok }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <form action="{{ route('admin.master.mapel.toggle_status', $mapel->id_mapel) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 text-[11px] font-bold rounded-full transition {{ $mapel->status == 1 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200' }}">
                                        {{ $mapel->status == 1 ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" @click="openMapelModal({{ json_encode($mapel) }})" class="p-1.5 bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 rounded-lg border border-slate-200 transition" title="Edit Mapel">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    @if($mapel->deletable != 0)
                                        <form action="{{ route('admin.master.mapel.destroy', $mapel->id_mapel) }}" method="POST" onsubmit="return confirm('Hapus mata pelajaran {{ $mapel->nama_mapel }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 rounded-lg border border-slate-200 transition" title="Hapus Mapel">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">Belum ada data mata pelajaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: KELOMPOK UTAMA -->
    <!-- ========================================================================= -->
    <div x-show="kelModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
        <div @click.away="kelModal = false" class="bg-white border border-slate-200 rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900" x-text="kelIsEdit ? 'Edit Kelompok Utama' : 'Tambah Kelompok Utama'"></h3>
                <button type="button" @click="kelModal = false" class="text-slate-400 hover:text-slate-700">&times;</button>
            </div>

            <form action="{{ route('admin.master.mapel.kelompok.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="id_kel_mapel" :value="kelForm.id_kel_mapel">
                <input type="hidden" name="id_parent" value="0">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Kelompok*</label>
                    <input type="text" name="kode_kel_mapel" x-model="kelForm.kode_kel_mapel" placeholder="Contoh: A, B, C, PEM" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs uppercase font-mono text-slate-900 focus:bg-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kelompok*</label>
                    <input type="text" name="nama_kel_mapel" x-model="kelForm.nama_kel_mapel" placeholder="Contoh: Kelompok A (Wajib)" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori*</label>
                    <select name="kategori" x-model="kelForm.kategori" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-indigo-500">
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}">{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                    <button type="button" @click="kelModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs">Simpan Kelompok</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: SUB KELOMPOK -->
    <!-- ========================================================================= -->
    <div x-show="subModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
        <div @click.away="subModal = false" class="bg-white border border-slate-200 rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900" x-text="subIsEdit ? 'Edit Sub Kelompok' : 'Tambah Sub Kelompok'"></h3>
                <button type="button" @click="subModal = false" class="text-slate-400 hover:text-slate-700">&times;</button>
            </div>

            <form action="{{ route('admin.master.mapel.kelompok.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="id_kel_mapel" :value="subForm.id_kel_mapel">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Sub Kelompok*</label>
                    <input type="text" name="kode_kel_mapel" x-model="subForm.kode_kel_mapel" placeholder="Contoh: C1, C2, C3" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs uppercase font-mono text-slate-900 focus:bg-white focus:outline-none focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Sub Kelompok*</label>
                    <input type="text" name="nama_kel_mapel" x-model="subForm.nama_kel_mapel" placeholder="Contoh: Kelompok C1 (Peminatan MIPA)" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kelompok Utama (Parent)*</label>
                    <select name="id_parent" x-model="subForm.id_parent" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-sky-500">
                        @foreach($kelompokUtama as $ku)
                            <option value="{{ $ku->id_kel_mapel }}">{{ $ku->nama_kel_mapel }} ({{ $ku->kode_kel_mapel }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100">
                    <button type="button" @click="subModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-xs">Simpan Sub Kelompok</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: TAMBAH / EDIT MATA PELAJARAN -->
    <!-- ========================================================================= -->
    <div x-show="mapelModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
        <div @click.away="mapelModal = false" class="bg-white border border-slate-200 rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900" x-text="mapelIsEdit ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran Baru'"></h3>
                <button type="button" @click="mapelModal = false" class="text-slate-400 hover:text-slate-700">&times;</button>
            </div>

            <form :action="mapelActionUrl" method="POST" class="space-y-4">
                @csrf
                <template x-if="mapelIsEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Mata Pelajaran*</label>
                    <input type="text" name="nama_mapel" x-model="mapelForm.nama_mapel" placeholder="Contoh: Matematika Wajib" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Mapel*</label>
                        <input type="text" name="kode" x-model="mapelForm.kode" placeholder="Contoh: MAT-W" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs uppercase font-mono text-slate-900 focus:bg-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. Urut Rapor</label>
                        <input type="number" name="urutan_tampil" x-model="mapelForm.urutan_tampil" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kelompok Mapel</label>
                        <select name="kelompok" x-model="mapelForm.kelompok" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-emerald-500">
                            <option value="">-- Pilih Kelompok --</option>
                            @foreach($allKelompok as $kel)
                                <option value="{{ $kel->kode_kel_mapel }}">{{ $kel->kode_kel_mapel }} - {{ $kel->nama_kel_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Agama</label>
                        <select name="mapel_agama" x-model="mapelForm.mapel_agama" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:outline-none focus:border-emerald-500">
                            <option value="0">Bukan Mapel Agama</option>
                            <option value="1">Mapel Agama (Khusus Siswa Tertentu)</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" @click="mapelModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs">Simpan Mapel</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
