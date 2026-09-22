
function processNilaiPg(soals, jawabans, bobot_pg = 25, tampil_pg = 10) {
  const pointPerSoal = tampil_pg > 0 ? bobot_pg / tampil_pg : 0;
  let benar_pg = 0;
  const detail = [];

  soals.forEach((soal) => {
    if (soal.jenis !== '1') return;
    const noSoal = soal.nomor_soal;
    const kunci = (soal.jawaban || '').toString().toUpperCase();
    const jawab = jawabans.find(j => j.id_soal == soal.id_soal);
    const jSiswa = jawab ? (jawab.jawaban_siswa || '').toString().toUpperCase() : '';
    const benar = kunci === jSiswa;
    if (benar) benar_pg++;

    detail.push({
      nomor: noSoal,
      id_soal: soal.id_soal,
      jawaban_benar: kunci,
      jawaban_siswa: jSiswa,
      analisa: benar ? '✔️' : '❌',
      skor_jawaban: benar ? pointPerSoal : 0,
      point_soal: pointPerSoal
    });
  });

  return {
    skor_pg: {
      detail,
      skor_total: Math.round((benar_pg * pointPerSoal + Number.EPSILON) * 100) / 100
    },
    pg_benar: benar_pg
  };
}

function processNilaiKompleks(soals, jawabans, bobot = 25, tampil = 10) {
  const pointPerSoal = tampil > 0 ? bobot / tampil : 0;
  let skor_total = 0;
  const detail = [];

  soals.forEach((soal) => {
    if (soal.jenis !== '2') return;
    const kunci = Array.isArray(soal.jawaban) ? soal.jawaban.map(s => s.toUpperCase()) : [];
    const jawab = jawabans.find(j => j.id_soal == soal.id_soal);
    const jSiswa = Array.isArray(jawab?.jawaban_siswa) ? jawab.jawaban_siswa.map(s => s.toUpperCase()) : [];

    const benarItem = jSiswa.filter(j => kunci.includes(j));
    const skor = kunci.length > 0 ? (pointPerSoal / kunci.length) * benarItem.length : 0;
    skor_total += skor;

    detail.push({
      nomor: soal.nomor_soal,
      id_soal: soal.id_soal,
      jawaban_benar: kunci,
      jawaban_siswa: jSiswa,
      analisa: skor === pointPerSoal ? '✔️' : skor > 0 ? '⚠️' : '❌',
      skor_jawaban: skor,
      point_soal: pointPerSoal
    });
  });

  return {
    skor_kompleks: {
      detail,
      skor_total: Math.round(skor_total * 100) / 100
    }
  };
}

function processNilaiIsian(soals, jawabans, bobot = 25, tampil = 10) {
  const pointPerSoal = tampil > 0 ? bobot / tampil : 0;
  let skor_total = 0;
  let benar = 0;
  const detail = [];

  soals.forEach((soal) => {
    if (soal.jenis !== '4') return;
    const jSiswa = jawabans.find(j => j.id_soal == soal.id_soal);
    const siswa = (jSiswa?.jawaban_siswa || '').trim().toLowerCase();
    const kunci = (soal.jawaban || '').trim().toLowerCase();
    const isBenar = siswa === kunci;
    if (isBenar) {
      skor_total += pointPerSoal;
      benar++;
    }

    detail.push({
      nomor: soal.nomor_soal,
      id_soal: soal.id_soal,
      jawaban_benar: kunci,
      jawaban_siswa: siswa,
      analisa: isBenar ? '✔️' : '❌',
      skor_jawaban: isBenar ? pointPerSoal : 0,
      point_soal: pointPerSoal
    });
  });

  return {
    skor_isian: {
      detail,
      skor_total: Math.round(skor_total * 100) / 100,
      jml_benar: benar
    }
  };
}

function processNilaiEssai(soals, jawabans, bobot = 25, tampil = 10) {
  const pointPerSoal = tampil > 0 ? bobot / tampil : 0;
  let skor_total = 0;
  let benar = 0;
  const detail = [];

  soals.forEach((soal) => {
    if (soal.jenis !== '5') return;
    const jSiswa = jawabans.find(j => j.id_soal == soal.id_soal);
    const siswa = (jSiswa?.jawaban_siswa || '').trim().toLowerCase();
    const kunci = (soal.jawaban || '').trim().toLowerCase();
    const isBenar = siswa === kunci;
    if (isBenar) {
      skor_total += pointPerSoal;
      benar++;
    }

    detail.push({
      nomor: soal.nomor_soal,
      id_soal: soal.id_soal,
      jawaban_benar: kunci,
      jawaban_siswa: siswa,
      analisa: isBenar ? '✔️' : '❌',
      skor_jawaban: isBenar ? pointPerSoal : 0,
      point_soal: pointPerSoal
    });
  });

  return {
    skor_essai: {
      detail,
      skor_total: Math.round(skor_total * 100) / 100,
      jml_benar: benar
    }
  };
}

function hitungTotalNilai(results) {
  return Math.round(
    (results?.skor_pg?.skor_total || 0) +
    (results?.skor_kompleks?.skor_total || 0) +
    (results?.skor_isian?.skor_total || 0) +
    (results?.skor_essai?.skor_total || 0)
  * 100) / 100;
}

function processNilaiJodohkan(soals, jawabans, bobot = 25, tampil = 10) {
  const pointPerSoal = tampil > 0 ? bobot / tampil : 0;
  let skor_total = 0;
  const detail = [];

  soals.forEach((soal) => {
    if (soal.jenis !== '3') return;
    const jawabanBenar = soal.jawaban || {};
    const jawab = jawabans.find(j => j.id_soal == soal.id_soal);
    const jawabanSiswa = jawab?.jawaban_siswa || {};

    // Format links: { 1: ['A', 'C'], 2: ['B'], ... }
    const kunciLinks = jawabanBenar.links || {};
    const siswaLinks = jawabanSiswa.links || {};
    let totalItem = 0;
    let benarItem = 0;

    for (const key in kunciLinks) {
      const kunciSub = kunciLinks[key] || [];
      const siswaSub = siswaLinks[key] || [];
      totalItem += kunciSub.length;
      benarItem += siswaSub.filter(j => kunciSub.includes(j)).length;
    }

    const skor = totalItem > 0 ? (pointPerSoal / totalItem) * benarItem : 0;
    skor_total += skor;

    detail.push({
      nomor: soal.nomor_soal,
      id_soal: soal.id_soal,
      jawaban_benar: kunciLinks,
      jawaban_siswa: siswaLinks,
      analisa: skor === pointPerSoal ? '✔️' : skor > 0 ? '⚠️' : '❌',
      skor_jawaban: Math.round(skor * 100) / 100,
      point_soal: pointPerSoal
    });
  });

  return {
    skor_jodohkan: {
      detail,
      skor_total: Math.round(skor_total * 100) / 100
    }
  };
}
