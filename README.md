# 🦅 CBT Laravel 11 — Garuda CBT Next Generation

> **Sistem Ujian Berbasis Komputer (Computer Based Test - CBT) Modern untuk Sekolah dan Madrasah di Seluruh Indonesia.**  
> Dibangun di atas fondasi **Laravel 11**, arsitektur database kompatibel penuh dengan **Garuda CBT**, tampilan bersih **100% Light Theme**, serta mendukung **5 Jenis Soal AKM** (Asesmen Kompetensi Minimum).

---

## 🌟 Keunggulan & Fitur Utama

- ⚡ **Laravel 11 High Performance Engine**: Dioptimalkan untuk menangani ratusan hingga ribuan siswa ujian secara serentak (*high-concurrency*).
- 📝 **5 Tipe Soal AKM Lengkap**:
  1. **Pilihan Ganda (PG 1)** — Satu jawaban benar (A–E).
  2. **Pilihan Ganda Kompleks (PG 2)** — Multi jawaban benar (Checkbox, True/False, Yes/No).
  3. **Menjodohkan (Matching)** — Menghubungkan premis baris dan kolom.
  4. **Isian Singkat** — Penilaian kata/frasa eksak secara otomatis.
  5. **Uraian / Essai** — Jawaban deskriptif dilengkapi pedoman penskoran guru.
- 📄 **Import Soal Modern (Word .docx & Excel .xlsx)**:
  - Format Word resmi AKM dengan tabel petunjuk warna biru & hijau.
  - Format Excel murni spreadsheet (`.xlsx`), bukan file teks CSV.
  - Parser cerdas otomatis membaca kunci, opsi multi-baris, bobot, dan gambar.
- 🔒 **Sistem Keamanan Ujian (Anti-Cheat & Device Lock)**:
  - Kunci 1 perangkat per siswa (*single device lock*).
  - Deteksi perpindahan tab / jendela peramban.
  - Auto-save jawaban instan tiap detik ke server via Background API.
  - Token ujian dinamis berkala (bisa diaktifkan/dinonaktifkan per jadwal).
- 📊 **Manajemen Akademik & Kurikulum**:
  - Paritas penuh menu Master Data: Tahun Pelajaran, Semester, Jurusan, Kelas/Rombel, dan Mata Pelajaran (Kelompok Utama & Sub Kelompok).
  - Kenaikan kelas, mutasi, kelulusan, dan arsip alumni.
  - Cetak Kartu Peserta, Naskah Soal Word untuk ujian kertas, Daftar Hadir, Berita Acara, dan Rekap Nilai Excel.
- 👥 **Akses Multi-Role**:
  - **Administrator**: Kendali penuh sistem, backup database, alokasi ruang, dan pengaturan sekolah.
  - **Guru / Pengawas**: Kelola bank soal, jadwal ujian, pengawasan real-time status siswa (*live monitor*), dan koreksi essai.
  - **Siswa**: Antarmuka ujian responsif, ramah ponsel, tablet, dan PC laboratorium.

---

## 💻 Persyaratan Sistem (System Requirements)

Pastikan server atau komputer Anda memenuhi spesifikasi berikut sebelum instalasi:

| Komponen | Spesifikasi Minimum | Rekomendasi |
| :--- | :--- | :--- |
| **Sistem Operasi** | Windows / Linux (Ubuntu 20.04/22.04/24.04) / MacOS | Ubuntu 22.04 / 24.04 LTS |
| **Web Server** | Apache 2.4+ atau Nginx 1.20+ | Nginx (aaPanel / LEMP) |
| **PHP** | PHP 8.2 atau PHP 8.3 / 8.4 | PHP 8.2 / 8.3 |
| **Database** | MySQL 5.7+ / 8.0+ atau MariaDB 10.3+ | MySQL 8.0 / MariaDB 10.6+ |
| **Ekstensi PHP** | `pdo_mysql`, `mbstring`, `openssl`, `xml`, `curl`, `zip`, `gd`, `fileinfo`, `bcmath` | Seluruh ekstensi aktif |
| **Composer** | Composer versi 2.x | Versi terbaru |

---

## 🚀 Panduan Instalasi (Step-by-Step)

### Opsi 1: Instalasi di Localhost (Laragon / XAMPP Windows)

1. **Buka Terminal / PowerShell di folder web server Anda:**
   ```bash
   cd C:\laragon\www
   # atau cd C:\xampp\htdocs
   ```

2. **Clone Repository ini:**
   ```bash
   git clone https://github.com/wensputra2026/cbtlaravel11.git cbtlaravel
   cd cbtlaravel
   ```

3. **Install Dependensi Composer:**
   ```bash
   composer install --optimize-autoloader
   ```

4. **Konfigurasi File Environment (`.env`):**
   Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   # Di Windows CMD/PowerShell:
   copy .env.example .env
   ```

5. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

6. **Buat Database dan Sesuaikan `.env`:**
   Buka file `.env` menggunakan teks editor (Notepad, VSCode), atur koneksi database:
   ```env
   APP_NAME="Garuda CBT Next Gen"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://cbtlaravel.test

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=cbtlaravel
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. **Import Skema & Data Database:**
   - Buat database kosong bernama `cbtlaravel` via phpMyAdmin atau HeidiSQL.
   - Import file SQL database awal ke dalam database tersebut.

8. **Buat Symlink Storage:**
   ```bash
   php artisan storage:link
   ```

9. **Jalankan Aplikasi:**
   - Jika menggunakan **Laragon**: Cukup reload Apache/Nginx, akses: `http://cbtlaravel.test`
   - Jika menggunakan **PHP Built-in Server**:
     ```bash
     php artisan serve
     ```
     Buka peramban di `http://127.0.0.1:8000`

---

### Opsi 2: Instalasi di VPS / Cloud Server (aaPanel / Ubuntu / Nginx)

1. **Clone ke Folder Web Root:**
   ```bash
   cd /www/wwwroot/
   git clone https://github.com/wensputra2026/cbtlaravel11.git cbt.sekolahanda.sch.id
   cd cbt.sekolahanda.sch.id
   ```

2. **Install Dependensi PHP:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Salin & Atur Konfigurasi `.env`:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   nano .env
   ```
   *Isikan parameter database `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai yang dibuat di panel database.*

4. **Atur Hak Akses Folder (Krusial):**
   Pastikan folder `storage` dan `bootstrap/cache` dapat ditulis oleh web server (`www` atau `www-data`):
   ```bash
   mkdir -p storage/framework/{sessions,views,cache}
   chown -R www:www /www/wwwroot/cbt.sekolahanda.sch.id
   chmod -R 775 storage bootstrap/cache
   ```

5. **Arahkan Web Server Document Root ke `/public`:**
   - Di **aaPanel** -> Web -> Klik Domain -> Masuk tab **Site directory**:
     - *Site directory*: `/www/wwwroot/cbt.sekolahanda.sch.id`
     - *Running directory*: pilih `/public` -> Klik **Save**.
   - Masuk tab **URL rewrite**, pilih template **laravel5**, atau tempelkan rule Nginx berikut:
     ```nginx
     location / {
         try_files $uri $uri/ /index.php?$query_string;
     }
     ```

6. **Generate Storage Link:**
   ```bash
   php artisan storage:link
   ```

---

## 🔑 Akun Default Masuk (Default Credentials)

| Role | Username Bawaan | Password Bawaan | Keterangan |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin` (atau sesuai DB) | Akses penuh menu admin di `/admin/dashboard` |
| **Guru / Pengawas** | *(NIP / Username Guru)* | *(Password Guru)* | Akses bank soal & monitoring di `/guru/dashboard` |
| **Siswa Peserta** | *(Nomor Peserta / NISN)* | *(Password Siswa)* | Halaman tes siswa otomatis setelah login |

> ⚠️ **PENTING**: Segera ubah kata sandi akun administrator setelah proses instalasi pertama kali melalui menu **Pengguna -> Administrator**.

---

## 📋 Panduan Penyusunan Soal (Format AKM)

Saat mengunggah butir soal massal di menu **Bank Soal -> Import Soal**:

1. **Format Microsoft Word (`.docx`)**:
   - Unduh template melalui tombol **"Unduh Format Word (.docx)"** (berkas: `format_soal_akm.docx`).
   - File template memuat 5 tabel berstruktur rapi:
     - **Tabel I**: Pilihan Ganda biasa (Isikan `v` pada kolom KUNCI untuk jawaban yang benar).
     - **Tabel II**: Pilihan Ganda Kompleks (Bisa menandai `v` lebih dari satu baris, atau Benar/Salah).
     - **Tabel III**: Menjodohkan (Kombinasi kode baris dan kode kolom).
     - **Tabel IV**: Isian Singkat.
     - **Tabel V**: Uraian / Essai.
2. **Format Spreadsheet Excel (`.xlsx`)**:
   - Unduh template melalui tombol **"Unduh Format Excel (.xlsx)"**.
   - Kolom baku: `NO`, `JENIS`, `SOAL`, `OPSI`, `JAWABAN`, `KUNCI`, `BOBOT`.
   - Simpan dalam format `.xlsx` standar.

---

## 🛠️ Pemecahan Masalah Umum (Troubleshooting)

### 1. `Failed to open stream: No such file or directory (storage/framework/sessions/...)`
- **Penyebab**: Folder sesi belum terbentuk atau perizinan folder belum diberikan ke peramban web server.
- **Solusi**:
  ```bash
  mkdir -p storage/framework/{sessions,views,cache}
  chmod -R 775 storage bootstrap/cache
  chown -R www:www storage bootstrap/cache
  ```

### 2. Halaman selain Beranda Menghasilkan Error 404 Not Found
- **Penyebab**: Rewrite rule Nginx / Apache `.htaccess` belum aktif.
- **Solusi**:
  - Pada **Nginx**: Tambahkan `try_files $uri $uri/ /index.php?$query_string;` di blok `location /`.
  - Pada **Apache**: Pastikan modul `mod_rewrite` aktif dan file `.htaccess` berada di dalam folder `/public`.

### 3. `Base table or view not found: Table 'users' doesn't exist`
- **Penyebab**: Database belum di-import atau nama database di `.env` salah.
- **Solusi**: Pastikan database SQL sudah di-import lengkap dan cek kembali koneksi pada file `.env`.

---

## 🤝 Kontribusi & Lisensi

Aplikasi ini dikembangkan untuk memajukan digitalisasi pendidikan di Indonesia dan dapat digunakan secara bebas oleh sekolah, madrasah, pesantren, maupun instansi pendidikan lainnya.

Jika Anda menemukan kendala atau ingin berkontribusi dalam perbaikan kode, silakan buat *Pull Request* atau sampaikan melalui menu *Issues* pada repositori GitHub ini.
