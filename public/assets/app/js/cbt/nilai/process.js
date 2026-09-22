(function (global, factory) {
    if (typeof module === "object" && typeof module.exports === "object") {
        // CommonJS/Node.js support
        module.exports = factory();
    } else {
        // Browser support
        global.ProcessNilai = factory();
    }
}(typeof window !== "undefined" ? window : this, function () {
    return {
        createPointSiswa(jadwal) {
            let result = [];
            const statusSiswa = jadwal.sesisiswa
            const typeQuery = jadwal.type_query
            const info = jadwal.bank
            const katrol = jadwal.katrol
            const soals = jadwal.bank.soal;

            statusSiswa.forEach((status) => {
                const jsonSoal = JSON.parse(status.cbtsiswa?.soal || '[]');
                status.cbtsiswa.soal = jsonSoal
                const jsonJawaban = JSON.parse(status.cbtsiswa?.jawaban || '[]');
                status.cbtsiswa.jawaban = jsonJawaban
                const jsonNilai = JSON.parse(status.cbtsiswa?.nilai || '[]');
                status.cbtsiswa.nilai = jsonNilai
                let nilai = JSON.parse(status.cbtsiswa?.nilai_koreksi || '{}') || {};

                const jawabansSiswa = [];
                jsonSoal.forEach((soal) => {
                    jawabansSiswa.push(processSoal(soal, soals, jsonJawaban, jsonNilai));
                });

                if (typeQuery > 1) {
                    nilai.jawaban_siswa = jawabansSiswa;
                }
                console.log('siswa', jawabansSiswa)
                const skorPg = processNilaiPg(info, jawabansSiswa['1']);
                nilai.skor_pg = skorPg
                //processNilaiKompleks(info, jawabansSiswa['2'], bobot, nilai);
                //processNilaiJodohkan(info, jawabansSiswa['3'], bobot, nilai);
                //processNilaiIsian(info, jawabansSiswa['4'], bobot, nilai);
                //processNilaiEsai(info, jawabansSiswa['5'], bobot, nilai);

                /*
                nilai.skor_total = Math.round(
                    (nilai.skor_pg || 0) +
                    (nilai.skor_kompleks || 0) +
                    (nilai.skor_jodohkan || 0) +
                    (nilai.skor_isian || 0) +
                    (nilai.skor_essai || 0),
                    2
                );
                 */

                //applyKatrol(katrol, nilai);

                const durasi = initializeDurasi(status.cbtsiswa);

                let mulai = '- -  :  - -';
                let selesai = '- -  :  - -';
                let sudahMulai = false;
                let sudahSelesai = false;

                const logs = jadwal.logs.filter((log => log.id_siswa === status.siswa_id && log.id_jadwal === jadwal.id_jadwal))
                logs.forEach((log) => {
                    if (log.log_type === '1') {
                        if (log) {
                            mulai = new Date(log.log_time).toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            sudahMulai = true;
                        }
                    } else if (log) {
                        selesai = new Date(log.log_time).toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        sudahSelesai = true;
                    }
                });

                let durSiswa = '';
                if (sudahMulai) {
                    let lamanya = durasi.lama_ujian;
                    if (!lamanya.includes(':')) {
                        const lama = new Date(durasi.selesai) - new Date(durasi.mulai);
                        lamanya = new Date(lama).toISOString().substr(11, 8); // Format HH:mm:ss
                    }
                    const [hours, minutes, seconds] = (lamanya || '').split(':').map((val) => parseInt(val, 10));
                    const ej = hours ? `${hours}j ` : '';
                    const em = minutes ? `${minutes}m ` : '';
                    const ed = seconds ? `${seconds}d` : '';

                    durSiswa = ej + em + ed;
                }

                const iconLoading = sudahMulai ? '<i class="fa fa-spinner fa-spin"></i> ' : '';
                const loading = sudahSelesai ? '<i class="fa fa-check"></i> ' : iconLoading;

                durasi.mulai_ujian = mulai;
                durasi.selesai_ujian = selesai;
                durasi.status_ujian = loading + durSiswa;

                status.cbtsiswa.durasi = durasi;
                status.cbtsiswa.nilai = nilai;
                // delete status.cbtsiswa; // Opsional: menghapus cbtsiswa jika tidak diperlukan
                status.cbtsiswa.nilai_koreksi = nilai
                result.push(status)
            });

            return result
        }
    };
}));

function initializeDurasi(cbtsiswa) {
    return {
        status: cbtsiswa?.status || '',
        mulai: cbtsiswa?.mulai || '',
        selesai: cbtsiswa?.selesai || '',
        lama_ujian: cbtsiswa?.lama_ujian || '',
        reset_izin: cbtsiswa?.reset_izin || '',
        reset_waktu: cbtsiswa?.reset_waktu || ''
    };
}

function processSoal(soal, soals, jsonJawaban, jsonNilai) {
    const idxSoal = soals.findIndex((s) => s.id_soal === soal.id_soal);
    const idxJawaban = jsonJawaban.findIndex((j) => j.id_soal === soal.id_soal);
    const idxNilai = jsonNilai.findIndex((n) => n.id_soal === soal.id_soal);

    let jawabansSiswa = {}
    if (idxSoal !== -1) {
        const dataSoal = soals[idxSoal];
        const noSoal = dataSoal.nomor_soal;

        soal.mapel_id = dataSoal.mapel_id || '';
        soal.jenis = dataSoal.jenis || '';
        soal.nomor_soal = dataSoal.nomor_soal || '';
        soal.soal = dataSoal.soal || '';
        //soal.opsi_a = dataSoal.jenis === '2' ? maybeUnserialize(dataSoal.opsi_a || '') : dataSoal.opsi_a || '';

        let jawabanSiswa = jsonJawaban[idxJawaban] || {};

        if (dataSoal.jenis === '3') {
            const arrAlphabet = [...'ABCDEFGHIJKLMNOPQRSTUVWXYZ'];

            if (!jawabanSiswa.jawaban_siswa?.links) {
                const arrJwbnSiswa = [];
                if (jawabanSiswa.jawaban_siswa?.jawaban) {
                    jawabanSiswa.jawaban_siswa.jawaban.forEach((jbs, idx) => {
                        if (idx > 0) {
                            arrJwbnSiswa[idx] = [];
                            jbs.forEach((jb, idxs) => {
                                if (idxs > 0 && jb === '1') {
                                    arrJwbnSiswa[idx].push(arrAlphabet[idxs - 1]);
                                }
                            });
                        }
                    });
                }
                jawabanSiswa.jawaban_siswa = {links: arrJwbnSiswa};
            }

            const arrJwbn = [];
            soal.jawaban_benar?.jawaban?.forEach((jbs, idx) => {
                if (idx > 0) {
                    arrJwbn[idx] = [];
                    jbs.forEach((jb, idxs) => {
                        if (idxs > 0 && jb === '1') {
                            arrJwbn[idx].push(arrAlphabet[idxs - 1]);
                        }
                    });
                }
            });
            soal.jawaban_benar.links = arrJwbn;
        }

        soal.opsi_b = dataSoal.opsi_b || '';
        soal.opsi_c = dataSoal.opsi_c || '';
        soal.opsi_d = dataSoal.opsi_d || '';
        soal.opsi_e = dataSoal.opsi_e || '';
        soal.tampilkan = dataSoal.tampilkan || '';

        jawabansSiswa[soal.jenis_soal] = jawabansSiswa[soal.jenis_soal] || {};
        jawabansSiswa[soal.jenis_soal][noSoal] = {
            soal,
            jawab: jawabanSiswa,
            nilai: jsonNilai[idxNilai] || {},
        }
    }
    console.log('siswa', jawabansSiswa)
    return jawabansSiswa
}

function assignSoalDetails(soal, dataSoal) {
    soal.opsi_a = dataSoal.jenis === '2' ? maybeUnserialize(dataSoal.opsi_a || '') : dataSoal.opsi_a || '';
    soal.opsi_b = dataSoal.opsi_b || '';
    soal.opsi_c = dataSoal.opsi_c || '';
    soal.opsi_d = dataSoal.opsi_d || '';
    soal.opsi_e = dataSoal.opsi_e || '';
}

function applyKatrol(katrol, nilai) {
    if (katrol && katrol.ya) {
        const total = nilai.skor_total;
        katrol.xa = Math.max(katrol.xa, total);
        katrol.xb = Math.min(katrol.xb, total);
        nilai.skor_katrol = parseFloat(
            (((katrol.ya - katrol.yb) / 100) * total + katrol.yb).toFixed(2)
        );
    } else {
        nilai.skor_katrol = '';
    }
}

function processNilaiPg(info, adaJawabanPg, jawabansSiswa) {
    const bagi_pg = Math.max(1, info.tampil_pg) / 100;
    const bobot_pg = info.bobot_pg / 100;
    const arrJawabanPg = [];
    let benarPg = 0;
    let skorPg = 0;

    if (info.tampil_pg > 0) {
        if (adaJawabanPg) {
            Object.entries(jawabansSiswa).forEach(([num, jwbPg]) => {
                let benar = false;

                if (jwbPg.jawab) {
                    const jawabanSiswa = (jwbPg.jawab.jawaban_siswa || '').toUpperCase();
                    const jawabanBenar = (jwbPg.soal.jawaban_benar || '').toUpperCase();

                    if (jawabanSiswa === jawabanBenar) {
                        benarPg++;
                        benar = true;
                    }
                }

                const point = benar && info.bobot_pg > 0 ? (info.bobot_pg / info.tampil_pg).toFixed(2) : 0;

                const analisa = benar
                    ? '<i class="fa fa-check-circle text-green text-lg"></i>'
                    : '<i class="fa fa-times-circle text-red text-lg"></i>';

                jwbPg.nilai = { ...jwbPg.nilai, benar, point, analisa };

                arrJawabanPg[num] = {
                    nomor: num,
                    id_soal: jwbPg.soal.id_soal,
                    jawaban_benar: jwbPg.soal.jawaban_benar,
                    jawaban: jwbPg.jawab.jawaban_siswa || '',
                    benar
                };
            });
        } else {
            for (let n = 1; n <= info.tampil_pg; n++) {
                arrJawabanPg[n] = {
                    nomor: n,
                    jawaban_benar: '-',
                    jawaban: '',
                    benar: false
                };
            }
        }

        skorPg = (benarPg / bagi_pg) * bobot_pg;
    }

    arrJawabanPg.sort((a, b) => a.nomor - b.nomor);
    return {jawaban_pg: arrJawabanPg, skor_pg: skorPg.toFixed(2)}
}

function processNilaiKompleks(info, adaJawabanPg2, jawabansSiswa, bobot, nilai) {
    let benarPg2 = 0;
    let skorKoreksiPg2 = 0;
    let otomatisPg2 = 0;

    if (info.tampil_kompleks > 0 && adaJawabanPg2) {
        Object.values(jawabansSiswa).forEach(jawabPg2 => {
            const nilaiPg2 = jawabPg2.nilai;
            const jawabanBenar = jawabPg2.soal.jawaban_benar.map(j => j.toUpperCase()) || [];
            const jawabanSiswa = (jawabPg2.jawab.jawaban_siswa || []).map(j => j.toUpperCase());

            const arrBenar = jawabanSiswa.filter(j => jawabanBenar.includes(j));
            benarPg2 += arrBenar.length / Math.max(jawabanBenar.length, 1);

            const pointItem = info.bobot_kompleks > 0
                ? (info.bobot_kompleks / info.tampil_kompleks).toFixed(2) / Math.max(jawabanBenar.length, 1)
                : 0;

            const point = +(pointItem * arrBenar.length).toFixed(2);

            const analisa = arrBenar.length === jawabanBenar.length
                ? '<i class="fa fa-check-circle text-green text-lg"></i>'
                : arrBenar.length > 0
                    ? '<i class="fa fa-times-circle text-yellow text-lg"></i>'
                    : '<i class="fa fa-times-circle text-red text-lg"></i>';

            Object.assign(nilaiPg2, {
                analisa,
                point: nilaiPg2.nilai_otomatis === '0' ? point : nilaiPg2.nilai_koreksi,
                point_koreksi: nilaiPg2.nilai_koreksi,
                point_otomatis: point
            });

            skorKoreksiPg2 += nilaiPg2.nilai_koreksi;
            otomatisPg2 = nilaiPg2.nilai_otomatis;
        });
    }

    const sPg2 = (benarPg2 / Math.max(bobot.bagi_pg2, 1)) * bobot.bobot_pg2;
    nilai.skor_kompleks = +((nilai.kompleks_nilai || (otomatisPg2 === '0' ? sPg2 : skorKoreksiPg2)).toFixed(2));
}

function processNilaiJodohkan(info, adaJawabanJodoh, jawabansSiswa, bobot, nilai) {
    const jawabanJodoh = adaJawabanJodoh ? jawabansSiswa : [];
    let benarJod = 0;
    let skorKoreksiJod = 0.0;
    let otomatisJod = 0;

    if (info.tampil_jodohkan > 0 && jawabanJodoh && jawabanJodoh.length > 0) {
        jawabanJodoh.forEach(jawabJod => {
            const nilaiJod = jawabJod.nilai;
            skorKoreksiJod += nilaiJod.nilai_koreksi;

            const typeSoal = jawabJod.soal.jawaban_benar.type;
            const arrSoal = jawabJod.soal.jawaban_benar.jawaban;

            const arrBenar = {};
            let itemBenar = 0;
            let itemKurang = 0;
            let items = 0;

            const pointBenar = info.bobot_jodohkan > 0
                ? (info.bobot_jodohkan / info.tampil_jodohkan).toFixed(2)
                : 0;

            if (jawabJod.soal.jawaban_benar.links && jawabJod.jawab.jawaban_siswa.links) {
                const array1 = jawabJod.soal.jawaban_benar.links;
                const array2 = jawabJod.jawab.jawaban_siswa.links;

                array1.forEach((subArray1, key) => {
                    arrBenar[key] = { benar: 0, kurang: 0 };
                    items += subArray1.length;

                    const subArray2 = array2[key] || [];

                    const sameItems = subArray1.filter(item => subArray2.includes(item));
                    itemBenar += sameItems.length;
                    arrBenar[key].benar += sameItems.length;

                    const diffItems1 = subArray1.filter(item => !subArray2.includes(item));
                    const diffItems2 = subArray2.filter(item => !subArray1.includes(item));
                    itemKurang += diffItems1.length + diffItems2.length;
                    arrBenar[key].kurang += diffItems1.length;
                });
            }

            const pointSoal = items > 0 ? ((itemBenar / items) * pointBenar).toFixed(2) : 0;
            benarJod += items > 0 ? (itemBenar / items) : 0;

            const headSoal = arrSoal.shift();
            const arrJwbSoal = arrSoal.map(kolSoal => {
                const jwb = { title: kolSoal.shift(), subtitle: [] };
                kolSoal.forEach((kol, pos) => {
                    if (kol === '1') {
                        jwb.subtitle.push(headSoal[pos]);
                    }
                });
                return jwb;
            });

            nilaiJod.type_soal = typeSoal;
            nilaiJod.tabel_soal = arrJwbSoal;

            const headJawab = jawabJod.jawab.jawaban_siswa.jawaban.shift() || [];
            const arrJawab = jawabJod.jawab.jawaban_siswa.jawaban || [];

            const arrJwbJawab = arrJawab.map(kolJawab => {
                const jwbs = { title: kolJawab.shift(), subtitle: [] };
                kolJawab.forEach((kol, po) => {
                    if (kol === '1') {
                        jwbs.subtitle.push(headJawab[po]);
                    }
                });
                return jwbs;
            });

            nilaiJod.tabel_jawab = arrJwbJawab;
            nilaiJod.tabel_benar = arrBenar;
            nilaiJod.point_soal = pointSoal;

            nilaiJod.point = nilaiJod.nilai_otomatis === '0' ? parseFloat(pointSoal) : nilaiJod.nilai_koreksi;
            nilaiJod.point_koreksi = nilaiJod.nilai_koreksi;
            nilaiJod.point_otomatis = parseFloat(pointSoal);

            nilaiJod.analisa = itemBenar === items
                ? '<i class="fa fa-check-circle text-green text-lg"></i>'
                : (itemBenar === 0
                    ? '<i class="fa fa-times-circle text-red text-lg"></i>'
                    : '<i class="fa fa-times-circle text-yellow text-lg"></i>');

            otomatisJod = nilaiJod.nilai_otomatis;
        });
    }

    nilai.nilai_jodohkan = jawabanJodoh || [];

    const sJod = bobot.bagi_jodoh > 0
        ? ((benarJod / bobot.bagi_jodoh) * bobot.bobot_jodoh).toFixed(2)
        : 0;

    const inputJod = nilai.jodohkan_nilai || 0;
    const cekOtomatisJod = otomatisJod === 0 ? sJod : skorKoreksiJod;

    nilai.skor_jodohkan = parseFloat(inputJod !== 0 ? inputJod : cekOtomatisJod).toFixed(2);
}

function processNilaiIsian(info, adaJawabanIsian, jawabansSiswa, bobot, nilai) {
    let skorKoreksiIs = 0.0;
    let otomatisIs = 0;
    let benarIs = 0;

    if (info.tampil_isian > 0 && adaJawabanIsian && jawabansSiswa.length > 0) {
        jawabansSiswa.forEach(jawabIs => {
            const nilaiIs = jawabIs.nilai;
            skorKoreksiIs += nilaiIs.nilai_koreksi;

            const jawabanSiswa = (jawabIs.jawab?.jawaban_siswa || '').toLowerCase();
            const jawabanBenar = (jawabIs.soal?.jawaban_benar || '').toLowerCase();
            const benar = jawabanSiswa === jawabanBenar;

            if (benar) benarIs++;

            const point = benar && info.bobot_isian > 0
                ? (info.bobot_isian / info.tampil_isian).toFixed(2)
                : 0;

            nilaiIs.point = nilaiIs.nilai_otomatis === '0' ? parseFloat(point) : nilaiIs.nilai_koreksi;
            nilaiIs.point_koreksi = nilaiIs.nilai_koreksi;
            nilaiIs.point_otomatis = parseFloat(point);

            nilaiIs.analisa = benar
                ? '<i class="fa fa-check-circle text-green text-lg"></i>'
                : '<i class="fa fa-times-circle text-yellow text-lg"></i>';

            otomatisIs = nilaiIs.nilai_otomatis;
        });
    }

    nilai.nilai_isian = jawabansSiswa || [];
    const sIs = bobot.bagi_isian > 0
        ? (benarIs / bobot.bagi_isian) * bobot.bobot_isian
        : 0;
    const inputIs = nilai.isian_nilai || 0;
    const cekOtomatisIs = otomatisIs === 0 ? sIs : skorKoreksiIs;

    nilai.skor_isian = parseFloat(inputIs !== 0 ? inputIs : cekOtomatisIs).toFixed(2);
}

function processNilaiEsai(info, adaJawabanEssai, jawabansSiswa, bobot, nilai) {
    let skorKoreksiEs = 0.0;
    let otomatisEs = 0;
    let benarEs = 0;

    if (info.tampil_esai > 0 && adaJawabanEssai && jawabansSiswa.length > 0) {
        jawabansSiswa.forEach(jawabEs => {
            const nilaiEs = jawabEs.nilai;
            skorKoreksiEs += parseInt(nilaiEs.nilai_koreksi, 10);

            const jawabanSiswa = (jawabEs.jawab?.jawaban_siswa || '-')
                .trim()
                .toLowerCase()
                .replace(/<\/?[^>]+(>|$)/g, ""); // Menghapus tag HTML
            const jawabanBenar = (jawabEs.soal?.jawaban_benar || '')
                .trim()
                .toLowerCase()
                .replace(/<\/?[^>]+(>|$)/g, ""); // Menghapus tag HTML
            const benar = jawabanSiswa === jawabanBenar;

            if (benar) benarEs++;

            const point = benar && info.bobot_esai > 0
                ? (info.bobot_esai / info.tampil_esai).toFixed(2)
                : 0;

            nilaiEs.point = nilaiEs.nilai_otomatis === '0' ? parseFloat(point) : nilaiEs.nilai_koreksi;
            nilaiEs.point_koreksi = nilaiEs.nilai_koreksi;
            nilaiEs.point_otomatis = parseFloat(point);

            nilaiEs.analisa = benar
                ? '<i class="fa fa-check-circle text-green text-lg"></i>'
                : '<i class="fa fa-times-circle text-yellow text-lg"></i>';

            otomatisEs = nilaiEs.nilai_otomatis;
        });
    }

    const sEs = bobot.bagi_essai > 0
        ? (benarEs / bobot.bagi_essai) * bobot.bobot_essai
        : 0;
    const inputEs = nilai.essai_nilai || 0;
    const cekOtomatisEs = otomatisEs === 0 ? sEs : skorKoreksiEs;

    nilai.skor_essai = parseFloat(inputEs !== 0 ? inputEs : cekOtomatisEs).toFixed(2);
}