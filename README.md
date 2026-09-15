# CBT Laravel 11 - High-Performance Computer-Based Testing Engine

Sistem Ujian Berbasis Komputer (CBT) generasi baru berbasis **Laravel 11**, dirancang khusus untuk melayani **500+ siswa ujian serentak** dengan performa tinggi, zero latency pada peramban siswa, dan tanpa I/O bottleneck di database MySQL.

---

## 🚀 4 Pilar Arsitektur Performa Tinggi (500+ Concurrent Students)

1. **Optimasi Skema Database & Composite Indexing**
   - Composite index pada tabel transaksi utama:
     - `cbt_ujian_siswa`: `(jadwal_id, siswa_id, status)`
     - `cbt_jawaban_siswa`: `(jadwal_id, siswa_id, soal_id)`
     - `cbt_soal`: `(bank_id, nomor_soal)`
     - `siswa_rombel_tahun`: `(kelas_id, tahun_ajaran_id)`
   - Seluruh tabel InnoDB menggunakan `ROW_FORMAT=DYNAMIC` untuk query cepat dan efisiensi memori.

2. **Redis In-Memory Buffering Engine (Autosave & Graceful Fallback)**
   - Autosave setiap klik jawaban siswa disimpan ke Redis Hash (`cbt_answers:{jadwalId}:{siswaId}`) dengan TTL 24 jam.
   - Zero MySQL disk write per klik autosave saat Redis aktif.
   - **Circuit Breaker & Fallback**: Jika Redis server offline, otomatis beralih sementara ke MySQL tanpa delay timeout socket.
   - Single batch commit via `DB::transaction()` saat siswa menyelesaikan ujian.

3. **Deterministic Seeded Shuffle (Zero DB Random Table)**
   - Pengacakan butir soal dan opsi jawaban (A, B, C, D, E) menggunakan algoritma Fisher-Yates LCG berbasis seed:
     ```php
     $seed = crc32($siswaId . '_' . $jadwalId);
     ```
   - **Zero Database Bloat**: Tanpa membuat baris atau tabel acak baru di MySQL per siswa.
   - **Konsisten**: Urutan soal persis sama saat browser di-refresh atau berpindah komputer.
   - **Sanitasi Kunci**: Jawaban benar dihapus sebelum payload dikirim ke peramban siswa demi integritas ujian.

4. **Production Hardening & Benchmark Tooling**
   - Panduan konfigurasi OPcache, PHP-FPM static pool, dan FrankenPHP / Laravel Octane pada [`docs/production_tuning_guide.md`](docs/production_tuning_guide.md).
   - Artisan benchmark command:
     ```bash
     php artisan cbt:benchmark-buffer --students=500 --answers=40
     ```

---

## 📋 Fitur Master Data & Manajemen CBT

- **Master Siswa & Akun CBT**:
  - Dukungan Kurikulum Merdeka (Mapel Pilihan).
  - Filter interaktif (Pencarian, Status Aktif/Nonaktif/Pindah/Keluar, Rombel Kelas, Tahun Pelajaran).
  - Bulk Actions: Set Pindah, Set Keluar, Set Aktif, Set Nonaktif, Hapus Terpilih.
  - Import Massal Excel (`.xlsx`) dan CSV dengan auto-mapping kelas & template resmi.
  - Ekspor Data CSV per rombel kelas atau seluruh sekolah.
  - Cetak Kartu Peserta Ujian.
- **Master Guru & Jabatan**: Multi-jabatan, pembagian wali kelas, dan penugasan mapel.
- **Master Kelas & Rombel**: Pembuatan rombel massal, sinkronisasi semester, dan detail anggota kelas.
- **Master Mata Pelajaran & Kelompok**: Pengelompokan Umum/Peminatan/Pilihan dan urutan tampil.
- **Bank Soal Multi-Tipe**: Pilihan Ganda (PG), PG Kompleks, Menjodohkan, Isian Singkat, dan Uraian/Esai.
- **Sesi & Ruang Ujian**: Pengaturan alokasi sesi, ruang, dan nomor peserta ujian.
- **Monitoring Ujian Real-Time**: Status pengerjaan siswa, sisa waktu, dan reset login.

---

## 🛠️ Instalasi & Menjalankan Aplikasi

### 1. Kebutuhan Sistem
- PHP >= 8.2 (ekstensi: pdo, pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, zip)
- MySQL / MariaDB >= 8.0 / 10.4
- Redis Server >= 6.0 (opsional untuk in-memory autosave buffer)
- Composer

### 2. Langkah Instalasi
```bash
# Clone repository
git clone https://github.com/wensputra2026/cbtlaravel11.git
cd cbtlaravel11

# Install dependencies
composer install

# Salin konfigurasi environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Konfigurasi database di file .env, kemudian jalankan migrasi
php artisan migrate

# [OPSI 1] Inisialisasi akun default via Seeder
php artisan db:seed

# [OPSI 2] Atau impor database sampel lengkap (soal ujian, master data, jadwal & rombel):
# mysql -u root -p nama_database < database/cbt_sample.sql

# Jalankan server pengembangan
php artisan serve
```

---

## 🔑 Akun Default Login (Default Credentials)

Setelah menjalankan `php artisan db:seed` atau mengimpor `database/cbt_sample.sql`, Anda dapat masuk menggunakan akun default berikut:

| Peran (Role) | Username | Password | Keterangan & Akses Portal |
| :--- | :--- | :--- | :--- |
| **Administrator CBT 1** | `admin` | `admin123` | Akses penuh dashboard proktor & admin (`/admin/dashboard` atau `/auth`) |
| **Administrator CBT 2** | `adminCBT` | `admin123` | Akun administrator utama pengelola CBT |
| **Guru Pengampu** | `guru` | `123456` | Portal guru, penugasan mapel, wali kelas, & koreksi (`/guru`) |
| **Peserta Siswa** | `siswa` | `123456` | Portal ujian siswa (CBT Exam Room) |

---

## 💾 Database Sampel (`database/cbt_sample.sql`)

Repository ini telah dilengkapi dengan basis data sampel siap pakai di folder [`database/cbt_sample.sql`](database/cbt_sample.sql) yang berisi:
- 42 tabel skema InnoDB berformat `ROW_FORMAT=DYNAMIC`.
- Profil sekolah (SMAN Benlutu), Tahun Pelajaran aktif (2025/2026 Genap).
- Master Guru & penugasan wali kelas / jabatan guru.
- Master Kelas & Rombel lengkap (Kelas X, XI, XII).
- Master Mata Pelajaran (Kurikulum Merdeka + Pilihan).
- Bank Soal sampel multi-tipe (Pilihan Ganda, PG Kompleks, Isian, Menjodohkan, Esai).
- Jadwal & Sesi Ruang Ujian.

---

## 📄 Lisensi
Sistem ini dikembangkan untuk kebutuhan operasional CBT sekolah dan institusi pendidikan.
