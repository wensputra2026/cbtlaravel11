@extends('layouts.admin')

@section('title', 'Data Alumni Siswa')
@section('page_title', 'Arsip Data Siswa Lulus / Alumni')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-slate-800">Arsip Siswa Lulus (Alumni)</h3>
            <p class="text-xs text-slate-500">Data rekam historis siswa yang telah menyelesaikan masa studi atau berstatus non-aktif</p>
        </div>
        <div class="w-full sm:w-80">
            <form action="{{ route('admin.master.alumni') }}" method="GET">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama atau NISN alumni..." class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 shadow-sm">
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Nama Alumni</th>
                        <th class="py-3 px-4">NISN / NIS</th>
                        <th class="py-3 px-4">Username Login</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alumni as $idx => $s)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 text-slate-500">{{ $alumni->firstItem() + $idx }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800">{{ $s->nama }}</td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">{{ $s->nisn ?? '-' }}</td>
                            <td class="py-3.5 px-4 font-mono text-brand-600">{{ $s->username }}</td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-slate-100 text-slate-600">
                                    Lulus / Arsip
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">Tidak ada data alumni yang diarsipkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($alumni->hasPages())
            <div class="pt-4 border-t border-slate-100 mt-4">
                {{ $alumni->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
