@extends('layouts.guru')

@section('title', 'Dashboard Guru & Pengawas')
@section('page_title', 'Selamat Datang, ' . ($guru?->nama_guru ?? Auth::user()->first_name ?? Auth::user()->username))

@section('content')
<div class="space-y-6 w-full">

    <!-- Welcome & Live Token Banner -->
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white border border-emerald-500/30 rounded-3xl p-6 shadow-md flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <img src="{{ $guru?->foto_url ?? asset('assets/img/guru-default.png') }}" alt="{{ $guru?->nama_guru }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-white/40 shadow-md">
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/20 text-white border border-white/30">
                    Guru Pengajar & Pengawas Ujian
                </span>
                <h3 class="text-xl font-black text-white mt-1">{{ $guru?->nama_guru ?? Auth::user()->username }}</h3>
                <p class="text-xs text-emerald-100 font-mono">NIP: {{ $guru?->nip ?? '-' }} &bull; Username: {{ Auth::user()->username }}</p>
            </div>
        </div>

        <!-- Token Box for Proctoring -->
        <div class="bg-white/15 backdrop-blur-md border border-white/25 px-6 py-3 rounded-2xl text-center shadow-inner">
            <span class="text-[10px] uppercase font-bold text-emerald-100 block tracking-wider">Token Ujian Aktif</span>
            <div class="font-mono text-3xl font-black tracking-widest text-white py-0.5 select-all">
                {{ $currentToken }}
            </div>
            <span class="text-[10px] text-emerald-100/90">Berlaku untuk seluruh ruang ujian</span>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200/90 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Bank Soal Dibuat</span>
                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600">📝</span>
            </div>
            <div class="text-2xl font-black text-slate-800">{{ count($myBanks) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Paket bank soal milik Anda</div>
        </div>

        <div class="bg-white border border-slate-200/90 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Jadwal Pengawasan</span>
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">👁️</span>
            </div>
            <div class="text-2xl font-black text-slate-800">{{ count($myPengawasan) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Penugasan ruang & sesi</div>
        </div>

        <div class="bg-white border border-slate-200/90 p-4 rounded-2xl shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Status Server CBT</span>
                <span class="p-1.5 rounded-lg bg-teal-50 text-teal-600">🟢</span>
            </div>
            <div class="text-2xl font-black text-teal-600">Online</div>
            <div class="text-[11px] text-slate-400 mt-1">Server lokal beroperasi normal</div>
        </div>
    </div>

    <!-- 2 Column Section: Jadwal Pengawasan & Bank Soal -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Jadwal Pengawasan Ruang -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">📋</span>
                    <h4 class="font-bold text-sm text-slate-800">Penugasan Pengawasan Anda</h4>
                </div>
                <a href="{{ route('guru.pengawasan.index') }}" class="text-xs font-semibold text-emerald-600 hover:underline">
                    Semua Penugasan &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($myPengawasan as $pengawas)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-800 text-xs">
                                {{ $pengawas->jadwal->bankSoal->bank_nama ?? 'Ujian' }}
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Ruang: <strong class="text-slate-700">{{ $pengawas->ruang->nama_ruang ?? '-' }}</strong> &bull; 
                                Sesi: <strong class="text-slate-700">{{ $pengawas->sesi->nama_sesi ?? '-' }}</strong>
                            </div>
                        </div>
                        <a href="{{ route('guru.pengawasan.monitor', $pengawas->id_jadwal) }}" class="px-3 py-1 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-600 hover:text-white transition">
                            Masuk Ruang Monitor &rarr;
                        </a>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        Belum ada penugasan ruang pengawasan ujian untuk Anda.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Bank Soal Saya -->
        <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600">📝</span>
                    <h4 class="font-bold text-sm text-slate-800">Paket Bank Soal Terakhir</h4>
                </div>
                <a href="{{ route('guru.bank_soal.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">
                    Kelola Soal &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($myBanks->take(5) as $bank)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-slate-800 text-xs">{{ $bank->bank_nama }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Mapel: <strong class="text-slate-700">{{ $bank->mapel->nama_mapel ?? '-' }}</strong> &bull; 
                                Kode: <strong class="font-mono text-slate-700">{{ $bank->bank_kode }}</strong>
                            </div>
                        </div>
                        <a href="{{ route('guru.bank_soal.show', $bank->id_bank) }}" class="px-3 py-1 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                            Edit Soal
                        </a>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-slate-400">
                        Anda belum membuat paket bank soal.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
