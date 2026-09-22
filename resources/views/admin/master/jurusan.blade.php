@extends('layouts.admin')

@section('title', 'Jurusan & Peminatan')
@section('page_title', 'Jurusan & Peminatan Siswa')

@section('content')
<div class="space-y-5" x-data="jurusanManager()">

    <!-- Header Actions & Alert -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800">Daftar Jurusan / Peminatan</h3>
            <p class="text-xs text-slate-500">Pengelompokan peminatan jurusan untuk alokasi kelas dan bank soal CBT</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="window.location.reload()" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-xl text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                <i class="fa fa-sync-alt text-slate-500"></i>
                <span>Muat Ulang</span>
            </button>
            <button type="button" @click="openCreateModal()" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-sm shadow-brand-600/30 transition flex items-center gap-1.5">
                <i class="fa fa-plus"></i>
                <span>Tambah Jurusan</span>
            </button>
        </div>
    </div>

    <!-- Alert Edukasi Garuda CBT -->
    <div class="flex items-center gap-3 p-3.5 bg-sky-50 border border-sky-200 rounded-xl text-xs text-sky-800">
        <i class="fa fa-info-circle text-sky-600 text-base shrink-0"></i>
        <span>Abaikan halaman ini jika sekolah tidak memiliki penjurusan (misal jenjang SMP/MTs, SD/MI, atau Kurikulum Merdeka Fase E).</span>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-800">
            <i class="fa fa-check-circle text-emerald-600 text-base shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800">
            <i class="fa fa-exclamation-circle text-rose-600 text-base shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200 font-bold">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4 w-32">Kode Jurusan</th>
                        <th class="py-3.5 px-4">Nama Jurusan / Peminatan</th>
                        <th class="py-3.5 px-4">Mapel Peminatan</th>
                        <th class="py-3.5 px-4 text-center w-24">Status</th>
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jurusanList as $idx => $jurusan)
                        @php
                            $mapelIds = array_filter(explode(',', (string)$jurusan->mapel_peminatan));
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 text-center font-medium text-slate-500">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-brand-600">
                                <span class="px-2 py-0.5 bg-brand-50 border border-brand-200 rounded-md">
                                    {{ $jurusan->kode_jurusan }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $jurusan->nama_jurusan }}</td>
                            <td class="py-3 px-4">
                                @if(!empty($mapelIds))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($mapelIds as $mid)
                                            @php
                                                $mpl = $mapelList->firstWhere('id_mapel', $mid);
                                            @endphp
                                            @if($mpl)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200" title="{{ $mpl->nama_mapel }}">
                                                    {{ $mpl->kode ?? $mpl->nama_mapel }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">- Semua / Umum -</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Aktif
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="inline-flex items-center gap-1.5">
                                    <!-- Tombol Edit -->
                                    <button type="button" 
                                            @click="openEditModal({
                                                id: {{ $jurusan->id_jurusan }},
                                                kode: '{{ addslashes($jurusan->kode_jurusan) }}',
                                                nama: '{{ addslashes($jurusan->nama_jurusan) }}',
                                                mapel: '{{ $jurusan->mapel_peminatan }}'
                                            })" 
                                            class="p-1.5 text-amber-600 hover:text-amber-700 hover:bg-amber-50 rounded-lg border border-amber-200 transition" 
                                            title="Edit Jurusan">
                                        <i class="fa fa-pencil-alt text-xs"></i>
                                    </button>

                                    <!-- Tombol Hapus -->
                                    <button type="button" 
                                            @click="confirmDelete({{ $jurusan->id_jurusan }}, '{{ addslashes($jurusan->nama_jurusan) }}')" 
                                            class="p-1.5 text-rose-600 hover:text-rose-700 hover:bg-rose-50 rounded-lg border border-rose-200 transition" 
                                            title="Hapus Jurusan">
                                        <i class="fa fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                <i class="fa fa-folder-open text-2xl text-slate-300 mb-2 block"></i>
                                Belum ada data jurusan / peminatan yang tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH JURUSAN -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs transition">
        <div @click.away="showCreateModal = false" class="bg-white border border-slate-200 rounded-2xl shadow-xl w-full max-w-lg p-6 overflow-hidden">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Tambah Jurusan Baru</h3>
                    <p class="text-xs text-slate-500">Tentukan kode, nama jurusan, dan mapel peminatan</p>
                </div>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa fa-times text-sm"></i>
                </button>
            </div>
            
            <form action="{{ route('admin.master.jurusan.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Jurusan <span class="text-rose-500">*</span></label>
                    <input type="text" name="kode_jurusan" placeholder="Contoh: MIPA / IPS / BAHASA / U" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 font-mono uppercase focus:bg-white focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                    <span class="text-[11px] text-slate-400 mt-1 block">Singkatan kode yang mudah dikenali (maksimal 20 karakter).</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Jurusan <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_jurusan" placeholder="Contoh: Matematika dan Ilmu Pengetahuan Alam" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mapel Peminatan (Opsional)</label>
                    <div class="max-h-36 overflow-y-auto p-2.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
                        @foreach($mapelList as $mpl)
                            <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer hover:bg-white p-1 rounded transition">
                                <input type="checkbox" name="mapel[]" value="{{ $mpl->id_mapel }}" class="rounded text-brand-600 focus:ring-brand-500">
                                <span><strong class="font-mono text-slate-500">[{{ $mpl->kode }}]</strong> {{ $mpl->nama_mapel }}</span>
                            </label>
                        @endforeach
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Pilih mata pelajaran yang hanya dipelajari oleh peminatan ini.</span>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-xs font-semibold shadow-sm shadow-brand-600/30 transition flex items-center gap-1.5">
                        <i class="fa fa-save"></i>
                        <span>Simpan Jurusan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT JURUSAN -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs transition">
        <div @click.away="showEditModal = false" class="bg-white border border-slate-200 rounded-2xl shadow-xl w-full max-w-lg p-6 overflow-hidden">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Edit Data Jurusan</h3>
                    <p class="text-xs text-slate-500">Ubah kode atau nama peminatan jurusan</p>
                </div>
                <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa fa-times text-sm"></i>
                </button>
            </div>
            
            <form :action="editActionUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Jurusan <span class="text-rose-500">*</span></label>
                    <input type="text" name="kode_jurusan" x-model="editData.kode" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 font-mono uppercase focus:bg-white focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Jurusan <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_jurusan" x-model="editData.nama" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mapel Peminatan (Opsional)</label>
                    <div class="max-h-36 overflow-y-auto p-2.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1.5">
                        @foreach($mapelList as $mpl)
                            <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer hover:bg-white p-1 rounded transition">
                                <input type="checkbox" name="mapel[]" value="{{ $mpl->id_mapel }}" :checked="isMapelChecked({{ $mpl->id_mapel }})" class="rounded text-brand-600 focus:ring-brand-500">
                                <span><strong class="font-mono text-slate-500">[{{ $mpl->kode }}]</strong> {{ $mpl->nama_mapel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-semibold shadow-sm shadow-amber-500/30 transition flex items-center gap-1.5">
                        <i class="fa fa-save"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Delete Form for SweetAlert2 -->
    <form id="deleteJurusanForm" method="POST" style="display: none;">
        @csrf
    </form>

</div>
@endsection

@push('scripts')
<script>
function jurusanManager() {
    return {
        showCreateModal: false,
        showEditModal: false,
        editActionUrl: '',
        editData: {
            id: '',
            kode: '',
            nama: '',
            mapel: ''
        },
        openCreateModal() {
            this.showCreateModal = true;
        },
        openEditModal(data) {
            this.editData = { ...data };
            this.editActionUrl = '{{ url("admin/master/jurusan/update") }}/' + data.id;
            this.showEditModal = true;
        },
        isMapelChecked(mapelId) {
            if (!this.editData.mapel) return false;
            var arr = this.editData.mapel.split(',');
            return arr.includes(String(mapelId));
        },
        confirmDelete(id, nama) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus Jurusan?',
                    text: 'Apakah Anda yakin ingin menghapus jurusan "' + nama + '"? Aksi ini tidak dapat dibatalkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fa fa-trash-alt mr-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        var form = document.getElementById('deleteJurusanForm');
                        form.action = '{{ url("admin/master/jurusan/delete") }}/' + id;
                        form.submit();
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin ingin menghapus jurusan "' + nama + '"?')) {
                    var form = document.getElementById('deleteJurusanForm');
                    form.action = '{{ url("admin/master/jurusan/delete") }}/' + id;
                    form.submit();
                }
            }
        }
    };
}
</script>
@endpush
