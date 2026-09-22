@extends('layouts.admin')

@section('title', $subjudul . ' | ' . $judul)
@section('page_title', $judul)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    /* Card Alert Backgrounds matching Garuda CBT */
    .card.alert-default-primary {
        background-color: #f0f7ff;
        border: 1px solid #cce5ff;
    }
    .card.alert-default-warning {
        background-color: #fffbf0;
        border: 1px solid #ffeeba;
    }
    .card.alert-default-danger {
        background-color: #fff5f5;
        border: 1px solid #f5c6cb;
    }
    .card.alert-default-success {
        background-color: #f0fdf4;
        border: 1px solid #c3e6cb;
    }
    .card.alert-default-secondary {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .select2-container--default .select2-selection--multiple {
        border-color: #cbd5e1;
        border-radius: 0.5rem;
        min-height: 38px;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #3b82f6;
    }
    .select2-container--default .select2-selection--single {
        border-color: #cbd5e1;
        height: 38px;
        border-radius: 0.5rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #1e293b;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>
@endpush

@section('content')
<div class="space-y-5">

    <!-- Header Section -->
    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $judul }}</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Tahun Pelajaran {{ $tp_active->tahun ?? '-' }} Semester {{ $smt_active->smt ?? '-' }}</p>
        </div>
        <div>
            <button onclick="window.history.back();" type="button" 
                    class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                <i class="fas fa-arrow-circle-left"></i>
                <span>Kembali</span>
            </button>
        </div>
    </div>

    <!-- Form Container Card -->
    <form id="create" method="POST" action="{{ route('admin.cbt.bank_soal.save_bank') }}">
        @csrf
        <input type="hidden" name="old_kode" value="{{ $bank->bank_kode ?? '' }}">
        <input type="hidden" name="id_bank" value="{{ $bank->id_bank ?? '' }}">

        <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
            <!-- Card Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/80">
                <h6 class="font-bold text-slate-800 text-sm">
                    {{ $subjudul }} {{ !empty($bank->bank_kode) ? ' - ' . $bank->bank_kode : '' }}
                </h6>
                <div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                        <i class="fa fa-plus"></i>
                        <span>Simpan</span>
                    </button>
                </div>
            </div>

            <!-- Card Body -->
            <div class="p-6 space-y-6">
                <!-- Row 1: Kode, Mapel, Guru, Level, Kelas -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Bank Soal *</label>
                        <input type="text" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono font-bold text-slate-900 uppercase focus:outline-none focus:border-blue-500" 
                               name="kode" maxlength="20" placeholder="Masukan Kode Bank Soal" value="{{ $bank->bank_kode ?? '' }}" required>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran *</label>
                        <select name="mapel" id="select-mapel" class="w-full select2" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($mapels as $m)
                                <option value="{{ $m->id_mapel }}" {{ ($bank->bank_mapel_id == $m->id_mapel) ? 'selected' : '' }}>
                                    {{ $m->nama_mapel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Guru Pengampu *</label>
                        <select name="guru" id="select-guru" class="w-full select2" required>
                            @foreach($gurus as $g)
                                <option value="{{ $g->id_guru }}" {{ ($bank->bank_guru_id == $g->id_guru) ? 'selected' : '' }}>
                                    {{ $g->nama_guru }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Level *</label>
                        <select name="level" id="select-level" class="w-full px-2.5 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-500" required>
                            @foreach($levels as $lvl)
                                <option value="{{ $lvl }}" {{ ($bank->bank_level == $lvl) ? 'selected' : '' }}>{{ $lvl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Kelas *</label>
                        <select name="kelas[]" id="select-kelas" class="w-full select2" multiple="multiple" required>
                            <!-- Populated dynamically via JS -->
                        </select>
                    </div>
                </div>

                <!-- Row 2: Soal PG & Soal Ganda Kompleks -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Soal Pilihan Ganda -->
                    <div class="md:col-span-7">
                        <div class="card alert-default-primary rounded-xl p-4 shadow-2xs">
                            <span class="font-bold text-xs text-blue-900 block mb-2">Soal Pilihan Ganda</span>
                            <div class="grid grid-cols-12 gap-3">
                                <div class="col-span-4">
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Jml. Soal</label>
                                    <input id="jml-pg" type="number" min="0" name="tampil_pg" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-500" value="{{ $bank->tampil_pg ?? 0 }}" required>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Bobot %</label>
                                    <input id="bobot-pg" type="number" min="0" max="100" name="bobot_pg" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-500" value="{{ $bank->bobot_pg ?? 0 }}" required>
                                </div>
                                <div class="col-span-5">
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Opsi Jawaban</label>
                                    <select name="opsi" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-500" required>
                                        <option value="3" {{ ($bank->opsi == 3) ? 'selected' : '' }}>3 (A, B, C)</option>
                                        <option value="4" {{ ($bank->opsi == 4) ? 'selected' : '' }}>4 (A, B, C, D)</option>
                                        <option value="5" {{ ($bank->opsi == 5 || empty($bank->opsi)) ? 'selected' : '' }}>5 (A, B, C, D, E)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Soal Ganda Kompleks -->
                    <div class="md:col-span-5">
                        <div class="card alert-default-warning rounded-xl p-4 shadow-2xs">
                            <span class="font-bold text-xs text-amber-900 block mb-2">Soal Ganda Kompleks</span>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Jml. Soal</label>
                                    <input id="jml-pg2" type="number" min="0" name="tampil_kompleks" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-500" value="{{ $bank->tampil_kompleks ?? 0 }}" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Bobot %</label>
                                    <input id="bobot-pg2" type="number" min="0" max="100" name="bobot_kompleks" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-500" value="{{ $bank->bobot_kompleks ?? 0 }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Menjodohkan, Isian, Essai -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Soal Menjodohkan -->
                    <div class="card alert-default-danger rounded-xl p-4 shadow-2xs">
                        <span class="font-bold text-xs text-rose-900 block mb-2">Soal Menjodohkan</span>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Jml. Soal</label>
                                <input id="jml-jodohkan" type="number" min="0" name="tampil_jodohkan" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-500" value="{{ $bank->tampil_jodohkan ?? 0 }}" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Bobot %</label>
                                <input id="bobot-jodohkan" type="number" min="0" max="100" name="bobot_jodohkan" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-500" value="{{ $bank->bobot_jodohkan ?? 0 }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Soal Isian Singkat -->
                    <div class="card alert-default-success rounded-xl p-4 shadow-2xs">
                        <span class="font-bold text-xs text-emerald-900 block mb-2">Soal Isian Singkat</span>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Jml. Soal</label>
                                <input id="jml-isian" type="number" min="0" name="tampil_isian" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-500" value="{{ $bank->tampil_isian ?? 0 }}" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Bobot %</label>
                                <input id="bobot-isian" type="number" min="0" max="100" name="bobot_isian" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-500" value="{{ $bank->bobot_isian ?? 0 }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Soal Uraian/Essai -->
                    <div class="card alert-default-secondary rounded-xl p-4 shadow-2xs">
                        <span class="font-bold text-xs text-slate-800 block mb-2">Soal Uraian/Essai</span>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Jml. Soal</label>
                                <input id="jml-essai" type="number" min="0" name="tampil_esai" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-500" value="{{ $bank->tampil_esai ?? 0 }}" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Bobot %</label>
                                <input id="bobot-essai" type="number" min="0" max="100" name="bobot_esai" class="w-full px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-500" value="{{ $bank->bobot_esai ?? 0 }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 4: Total & Status -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <!-- Total Soal & Bobot Summary Card -->
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50">
                        <table class="w-full text-center text-xs">
                            <tr class="font-semibold text-slate-600 border-b border-slate-200">
                                <th class="pb-1">Total Soal</th>
                                <th class="pb-1">Total Bobot</th>
                            </tr>
                            <tr class="font-black text-lg">
                                <td id="total-soal" class="text-blue-600 pt-1">0</td>
                                <td id="total-bobot" class="text-emerald-600 pt-1">0</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Mapel Agama -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mapel Agama</label>
                        <select name="soal_agama" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-500" required>
                            @foreach($mapel_agama as $aKey => $aVal)
                                <option value="{{ $aKey }}" {{ ($bank->soal_agama == $aKey) ? 'selected' : '' }}>{{ $aVal }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Bank Soal -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Bank Soal</label>
                        <select name="status" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-blue-500" required>
                            <option value="1" {{ ($bank->status == 1 || $bank->status === null) ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ ($bank->status === 0 || $bank->status === '0') ? 'selected' : '' }}>Non Aktif</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>
    </form>

</div>

@push('scripts')
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>

<script>
    var selectedKelasIds = @json($bank->kelas_ids ?? []);
    var idGuru = '{{ $bank->bank_guru_id ?? "" }}';
    var idMapel = '{{ $bank->bank_mapel_id ?? "" }}';

    $(document).ready(function () {
        var selLevel = $('#select-level');
        var selKelas = $('#select-kelas');
        var selMapel = $('#select-mapel');
        var selGuru = $('#select-guru');

        selMapel.select2({ width: '100%' });
        selKelas.select2({ width: '100%', placeholder: 'Pilih Kelas' });
        selGuru.select2({ width: '100%' });

        function getGuruMapel(mapel) {
            $.ajax({
                url: "{{ route('admin.cbt.bank_soal.get_guru_mapel') }}?id_mapel=" + mapel,
                type: "GET",
                success: function (data) {
                    var opts = '';
                    $.each(data, function (k, v) {
                        var selected = idGuru == k ? 'selected="selected"' : '';
                        opts += '<option value="' + k + '" ' + selected + '>' + v + '</option>';
                    });
                    selGuru.html(opts);
                    idGuru = selGuru.val();
                    getKelasLevel(selLevel.val(), selMapel.val());
                }
            });
        }

        function getKelasLevel(level, mapel) {
            $.ajax({
                url: "{{ route('admin.cbt.bank_soal.get_kelas_level') }}?level=" + level + "&id_guru=" + idGuru + "&mapel=" + mapel,
                type: "GET",
                success: function (data) {
                    selKelas.empty();
                    var kelas = data.kelas || [];
                    for (let i = 0; i < kelas.length; i++) {
                        var isSelected = selectedKelasIds.indexOf(kelas[i].id_kelas) !== -1;
                        var opt = new Option(kelas[i].kode_kelas + ' (' + kelas[i].nama_kelas + ')', kelas[i].id_kelas, false, isSelected);
                        selKelas.append(opt);
                    }
                    selKelas.trigger('change');
                }
            });
        }

        selGuru.on('change', function () {
            idGuru = $(this).val();
            getKelasLevel(selLevel.val(), selMapel.val());
        });

        selLevel.on('change', function () {
            getKelasLevel($(this).val(), selMapel.val());
        });

        selMapel.on('change', function () {
            getGuruMapel($(this).val());
        });

        // Initialize classes
        getKelasLevel(selLevel.val(), selMapel.val());

        // Live calculation of Total Soal and Total Bobot
        var valBobotPg = $('#bobot-pg');
        var valBobotPg2 = $('#bobot-pg2');
        var valBobotJodohkan = $('#bobot-jodohkan');
        var valBobotIsian = $('#bobot-isian');
        var valBobotEssai = $('#bobot-essai');
        var totalBobot = $('#total-bobot');

        var valSoalPg = $('#jml-pg');
        var valSoalPg2 = $('#jml-pg2');
        var valSoalJodohkan = $('#jml-jodohkan');
        var valSoalIsian = $('#jml-isian');
        var valSoalEssai = $('#jml-essai');
        var totalSoal = $('#total-soal');

        function onChangeValueBobot() {
            const bPg = parseInt(valBobotPg.val()) || 0;
            const bPg2 = parseInt(valBobotPg2.val()) || 0;
            const bJodoh = parseInt(valBobotJodohkan.val()) || 0;
            const bIsi = parseInt(valBobotIsian.val()) || 0;
            const bEssai = parseInt(valBobotEssai.val()) || 0;

            const sum = bPg + bPg2 + bJodoh + bIsi + bEssai;
            totalBobot.text(sum + '%');
            if (sum === 100) {
                totalBobot.removeClass('text-amber-600 text-rose-600').addClass('text-emerald-600');
            } else {
                totalBobot.removeClass('text-emerald-600').addClass('text-amber-600');
            }
        }

        function onChangeValueJumlah() {
            const jPg = parseInt(valSoalPg.val()) || 0;
            const jPg2 = parseInt(valSoalPg2.val()) || 0;
            const jJodoh = parseInt(valSoalJodohkan.val()) || 0;
            const jIsi = parseInt(valSoalIsian.val()) || 0;
            const jEssai = parseInt(valSoalEssai.val()) || 0;

            totalSoal.text((jPg + jPg2 + jJodoh + jIsi + jEssai) + '');
        }

        // Attach listeners
        $('#bobot-pg, #bobot-pg2, #bobot-jodohkan, #bobot-isian, #bobot-essai').on('change keyup input', onChangeValueBobot);
        $('#jml-pg, #jml-pg2, #jml-jodohkan, #jml-isian, #jml-essai').on('change keyup input', onChangeValueJumlah);

        onChangeValueBobot();
        onChangeValueJumlah();

        // Form Submit via AJAX matching Garuda CBT
        $('#create').on('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                text: "Silahkan tunggu....",
                allowEscapeKey: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('admin.cbt.bank_soal.save_bank') }}",
                type: "POST",
                dataType: "JSON",
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function (data) {
                    if (data.status) {
                        Swal.fire({
                            title: "Sukses",
                            html: 'Bank soal berhasil disimpan',
                            icon: "success",
                            confirmButtonColor: "#059669",
                            confirmButtonText: "OK"
                        }).then(result => {
                            if (result.value) {
                                window.location.href = "{{ route('admin.cbt.bank_soal.index', ['type' => '0', 'mode' => '1']) }}";
                            }
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: data.errors || data.message || 'Terjadi kesalahan sistem',
                            icon: "error"
                        });
                    }
                },
                error: function (xhr) {
                    var msg = 'Gagal menyimpan bank soal';
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.errors) msg = res.errors;
                        else if (res.message) msg = res.message;
                    } catch(e) {}
                    Swal.fire({
                        title: "Error",
                        text: msg,
                        icon: "error"
                    });
                }
            });
        });

    });
</script>
@endpush
@endsection
