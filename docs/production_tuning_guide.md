# Panduan Hardening & Konfigurasi Runtime Produksi (500+ Siswa Konkuren)

Dokumen ini adalah spesifikasi panduan konfigurasi server, runtime PHP, web server, dan buffer engine untuk mendukung pelaksanaan ujian CBT serentak tanpa latency dan tanpa bottleneck.

---

## 1. Tuning PHP Runtime (`php.ini`)

Gunakan konfigurasi **OPcache** agresif untuk server produksi agar script PHP tidak perlu di-compile ulang pada setiap HTTP request:

```ini
; /etc/php/8.2/fpm/php.ini atau conf.d/10-opcache.ini
[opcache]
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=32
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0       ; 0 = Jangan periksa perubahan file di disk (Mode Produksi Ekstrem)
opcache.save_comments=1             ; Diperlukan untuk parsing atribut/anotasi framework
opcache.fast_shutdown=1

; Realpath Cache
realpath_cache_size=4096K
realpath_cache_ttl=600

; Memory & Execution Limits
memory_limit=512M
max_execution_time=60
upload_max_filesize=64M
post_max_size=64M
```

> [!NOTE]
> Jika `opcache.validate_timestamps=0`, setelah melakukan deployment kode baru, jalankan `php artisan optimize:clear && php artisan optimize` atau restart PHP-FPM (`sudo systemctl reload php8.2-fpm`).

---

## 2. Tuning PHP-FPM Pool (`www.conf`)

Untuk melayani **500+ request simultan** autosave per detik, atur PHP-FPM ke mode `static` agar worker process tetap standby di memori tanpa overhead spawning process:

```ini
; /etc/php/8.2/fpm/pool.d/www.conf
pm = static
pm.max_children = 80               ; Sesuaikan dengan RAM: (Total RAM - 2GB) / 40MB per worker
pm.max_requests = 1000             ; Mencegah memory leak jangka panjang
pm.status_path = /status

listen.backlog = 65535
rlimit_files = 65535
```

---

## 3. Laravel Octane (FrankenPHP / Swoole) - Recommended Next-Gen

Jika menggunakan Laravel Octane dengan **FrankenPHP** atau **Swoole**, aplikasi akan tetap aktif di RAM (*application in-memory daemon*), menurunkan latency dari 20-40ms menjadi **1-3ms**:

```bash
# Menjalankan Octane FrankenPHP pada mode produksi
php artisan octane:start --server=frankenphp --workers=16 --max-requests=10000 --port=8000
```

---

## 4. Redis Memory Policy Tuning (`redis.conf`)

Pastikan Redis dikonfigurasi dengan eviction policy yang aman dan RAM yang memadai:

```ini
# /etc/redis/redis.conf
maxmemory 1gb
maxmemory-policy volatile-lru       ; Hapus key kadaluwarsa tertua jika memori hampir penuh
save ""                             ; Nonaktifkan RDB background snapshot berkala selama ujian untuk zero disk I/O lag
appendonly no                       ; Nonaktifkan AOF selama jam ujian untuk mengurangi I/O disk NVMe/SSD
```

---

## 5. Command Pengujian / Benchmark Buffer

Uji throughput dan latency buffer engine secara lokal kapan saja:

```bash
# Uji 50 siswa x 20 butir soal (1.000 request)
php artisan cbt:benchmark-buffer --students=50 --answers=20

# Uji beban penuh: 500 siswa x 40 butir soal (20.000 request)
php artisan cbt:benchmark-buffer --students=500 --answers=40
```
