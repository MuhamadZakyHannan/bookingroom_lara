# MeetSpace Laravel

Migrasi bertahap aplikasi pemesanan ruang rapat dari PHP MVC native ke Laravel 12.

## Persyaratan

- PHP 8.2 atau lebih baru
- Composer 2
- Node.js dan npm
- MySQL/MariaDB dari XAMPP
- Database lama `meetspace_db`

## Menjalankan di XAMPP

1. Pastikan Apache dan MySQL aktif.
2. Salin `.env.example` menjadi `.env` bila `.env` belum tersedia.
3. Jalankan:

```powershell
composer install
npm install
npm run build
php artisan key:generate
```

4. Buka:

```text
http://localhost/Room_Booking_System_Laravel/public
```

Konfigurasi awal menggunakan database lama sehingga akun, ruangan, booking, display, dan notifikasi tetap terbaca tanpa menyalin data.

## Peringatan database

Jangan menjalankan `php artisan migrate:fresh`, `migrate:refresh`, atau `db:wipe` terhadap `meetspace_db`. Perintah tersebut dapat menghapus data aplikasi lama. Migration di repository disediakan untuk database baru dan pengujian otomatis.

## Pengujian

```powershell
php artisan test
```

Test menggunakan SQLite sementara dan tidak mengubah `meetspace_db`.

## Status migrasi

Sudah tersedia:

- Instalasi Laravel 12 yang kompatibel dengan PHP 8.2 XAMPP.
- Koneksi aman ke skema database lama.
- Model Eloquent untuk pengguna, ruangan, booking, display, dan notifikasi.
- Login, logout, dan pendaftaran dengan password kuat.
- Middleware role admin.
- Dashboard dengan statistik, pencarian ruangan, jadwal hari ini, persetujuan, dan monitoring display.
- Pembuatan serta pembatalan booking dengan validasi kapasitas dan bentrok jadwal.
- Approval/reject admin dengan perlindungan terhadap jadwal terkonfirmasi yang bentrok.
- Analisis konflik menggunakan metode SAW dengan lima kriteria, ranking transparan, dan penerapan keputusan atomik.
- Pengelolaan ruangan oleh admin, termasuk foto, kapasitas, fasilitas, lokasi, dan status operasional.
- Pengelolaan role pengguna dengan perlindungan akun sendiri, administrator terakhir, serta riwayat booking.
- Registrasi perangkat display dengan token acak, status aktivitas, dan rotasi token.
- Halaman booking pengguna dan otorisasi berbasis kepemilikan.
- Check-in/check-out, grace period, no-show, serta auto check-out.
- Kalender bulanan dan daftar agenda tanpa tampilan mingguan.
- Command attendance yang aman dijalankan terjadwal.
- Light mode dan dark mode.

Tahap berikutnya:

- Laporan, statistik, dan ekspor.
- Monitor lobby dan monitor pintu.

Sampai seluruh modul selesai dan tervalidasi, aplikasi PHP lama di `Room_Booking_System` tetap menjadi aplikasi utama dan jalur rollback.

Penjelasan batas lapisan, transaksi, concurrency, dan keamanan tersedia di [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

## Menjalankan scheduler

Untuk pengembangan lokal:

```powershell
php artisan schedule:work
```

Untuk deployment, jalankan `php artisan schedule:run` setiap menit melalui Windows Task Scheduler.

## Panduan admin

1. Masuk menggunakan akun dengan role `admin`.
2. Buka menu **Persetujuan**. Navigasi administrasi berisi:
   - **Persetujuan** untuk menerima atau menolak satu pengajuan.
   - **Konflik SAW** untuk melihat ranking jadwal yang bertabrakan dan menerapkan rekomendasi.
   - **Ruangan** untuk menambah dan memperbarui data ruang rapat.
   - **Pengguna** untuk mengubah role akun. Akun baru dibuat melalui halaman daftar.
   - **Display** untuk mendaftarkan monitor pintu dan mengganti token akses.
3. Untuk ruang yang pernah digunakan, pilih status **Perawatan** alih-alih menghapusnya. Sistem sengaja menolak penghapusan ruang atau pengguna yang memiliki riwayat booking.
4. Mengganti token display langsung membatalkan token sebelumnya. Perangkat perlu dikonfigurasi ulang dengan token baru.

## Keputusan SAW

SAW digunakan hanya pada kelompok booking aktif di ruangan dan tanggal yang sama dengan waktu saling tumpang tindih. Bobotnya adalah tingkat kepentingan 30%, jumlah peserta 20%, durasi 15%, waktu pengajuan 20%, dan frekuensi penggunaan divisi 15%.

Admin tetap menjadi pengambil keputusan. Saat tombol **Terapkan Rekomendasi** ditekan, server menghitung ulang data terbaru dalam transaksi, mengonfirmasi pemenang, dan membatalkan booking lain di kelompok konflik. Keputusan tidak dapat diterapkan jika salah satu jadwal sudah dimulai.
