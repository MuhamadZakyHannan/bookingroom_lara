# MeetSpace Laravel — migrasi versi finale

Hasil migrasi Laravel 12 dari `C:\xampp\htdocs\Room_Booking_System`, diperiksa terakhir pada 4 Oktober 2026. Antarmuka Blade mengikuti dashboard Pelindo versi finale: tema biru, logo/foto/avatar lama, drawer menu, tema terang/gelap, filter ruangan, ringkasan, dan monitoring display.

## Hasil yang sudah tersedia di komputer ini

- Kode: folder `outputs/meetspace-laravel`.
- Aplikasi: http://127.0.0.1:8000/login saat server lokal berjalan.
- Login menggunakan username dan kata sandi aplikasi lama. Hash kata sandi, username, dan peran dipertahankan; tidak dibuat kata sandi baru untuk akun hasil impor.
- Salinan data terbaru: 10 pengguna, 6 ruangan, 3 booking, 6 display, 1 surat pendukung, 4 notifikasi.
- Database runtime lokal: `database/finale.sqlite`, dipilih melalui `.env` lokal. Database aplikasi XAMPP sumber tidak diubah.
- Database MySQL untuk verifikasi: `meetspace_laravel_validation_20260928`. Ini berisi salinan pengujian dan fixture persetujuan bersamaan, bukan database operasional.

Jika server berhenti, buka terminal di folder kode dan jalankan:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Atau jalankan `start-laravel.cmd`. Folder lokal sudah memiliki dependensi Composer. Arsip ZIP berisi kode dan snapshot migrasi, tetapi mengecualikan vendor, `.env`, database runtime, log, serta cache.

## Modul

| Modul | Implementasi Laravel |
|---|---|
| Login | Username atau email, session Laravel, pembatasan percobaan login, password baru minimal 8 karakter dengan huruf besar/kecil dan angka |
| Peran | User, Admin, Super Admin; hanya Super Admin mengelola pengguna/divisi |
| Dashboard | Logo/foto ruangan versi finale, pencarian AJAX tanpa pindah halaman, filter kapasitas/status, popup pemesanan, persetujuan cepat, monitoring display |
| Booking | Alur satu kolom dengan langkah bernomor seperti versi PHP awal, jam WIB format 24 jam, buat, lihat milik sendiri/semua untuk admin, edit, pindah ruang, batal dengan alasan, ketersediaan langsung |
| Persetujuan | Setujui/tolak cepat dari dashboard, riwayat keputusan, relokasi beralasan, notifikasi, dan kunci transaksi per ruangan |
| Prioritas konflik | Lima pertimbangan dengan porsi yang dapat diatur Super Admin, urutan rekomendasi, keputusan langsung oleh admin, dan snapshot keputusan |
| Siklus jadwal | Booking disetujui aktif sesuai waktu reservasi; sinkronisasi otomatis melalui scheduler dan sebagai fallback saat halaman dibuka |
| Surat pendukung | PDF/JPG/PNG maksimal 5 MB sejak pengajuan awal, penyimpanan privat, pratinjau/unduh terautentikasi |
| Notifikasi | Database langsung, badge/suara diperbarui 20 detik, tandai semua dibaca, email opsional lewat antrean |
| Kalender | Kalender bulanan dan agenda seluler otomatis, pencarian langsung, warna per ruangan, filter, modal detail, dan klik tanggal untuk mengisi formulir |
| Ruangan & akun | Kelola ruangan, foto, kapasitas, status pemeliharaan, pengguna, avatar, dan divisi; data bersejarah dikunci agar jejak audit tidak terhapus |
| Display | URL tersendiri untuk setiap ruangan dengan pemilih dropdown, agenda polling 5 detik, jam dan timestamp WIB, serta monitor lobby publik berupa jadwal seluruh ruangan dengan auto-scroll |
| Riwayat & laporan | Empat KPI, filter tanggal/ruangan/status, pencarian langsung dan saran, modal detail, cetak per agenda, CSV/PDF resmi, serta perlindungan formula spreadsheet |
| Statistik & analisis | Preset periode, enam KPI, tren bulanan, proporsi divisi, popularitas dan matriks utilisasi ruangan, jam/hari sibuk, status, serta pemesan teraktif |

## Instalasi baru dari ZIP

Memerlukan PHP 8.2+, Composer, `pdo_mysql` untuk MySQL atau `pdo_sqlite` untuk SQLite, serta ekstensi standar Laravel (`mbstring`, `openssl`, `fileinfo`, XML, dll.). `gd` digunakan untuk pengolahan gambar dan PDF. Tidak memerlukan build npm untuk menjalankan antarmuka.

```powershell
cd C:\lokasi\meetspace-laravel
composer install
Copy-Item .env.example .env
php artisan key:generate
```

### Pilihan A — MySQL/MariaDB XAMPP

1. Jalankan MySQL di XAMPP.
2. Buat **database baru kosong**, misalnya `meetspace_laravel`, melalui phpMyAdmin. Gunakan `utf8mb4_unicode_ci`.
3. Ubah `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=meetspace_laravel
DB_USERNAME=root
DB_PASSWORD=
APP_URL=http://127.0.0.1:8000
```

4. Jalankan:

```powershell
php artisan config:clear
php artisan migrate
php artisan legacy:import-current
php artisan storage:link
php artisan serve --host=127.0.0.1 --port=8000
```

Importer menolak database tujuan yang sudah berisi data. Gunakan nama database lain jika ingin mengulang. Jangan mengimpor SQL lama langsung ke tabel Laravel atau menggunakan `migrate:fresh` pada database yang perlu dipertahankan.

### Pilihan B — SQLite lokal

Biarkan `DB_CONNECTION=sqlite` dalam `.env`, lalu:

```powershell
New-Item -ItemType File database/database.sqlite
php artisan migrate
php artisan legacy:import-current
php artisan storage:link
php artisan serve
```

Jika memilih file SQLite lain, isi `DB_DATABASE` dengan path absolutnya. Gunakan MySQL/MariaDB untuk pemakaian bersama agar penguncian baris `FOR UPDATE` berjalan sesuai rancangan.

### Data demo, sebagai alternatif impor

Pada database kosong setelah `php artisan migrate`, jalankan `php artisan db:seed` **sebagai pengganti** importer. Seeder menolak database berisi data.

| Akun demo | Username | Password |
|---|---|---|
| Super Admin | `admin.demo` | `DemoAdmin123!` |
| User | `user.demo` | `DemoUser123!` |

Akun ini hanya dibuat oleh seeder demo. Akun hasil impor tetap menggunakan kredensial lama.

## Mengambil snapshot versi finale yang lebih baru

Ekspor hanya membaca tabel yang dibutuhkan dalam transaksi read-only, tanpa memuat bootstrap atau job otomatis aplikasi lama.

```powershell
php scripts/export-legacy.php "C:\xampp\htdocs\Room_Booking_System" "C:\lokasi\snapshot-baru.json"
php artisan legacy:import-current "C:\lokasi\snapshot-baru.json" --documents="C:\xampp\private\Room_Booking_System\booking-documents"
```

Jalankan importer pada database Laravel kosong. Parameter `--documents` harus menunjuk ke folder privat sebenarnya dari konfigurasi aplikasi lama. Foto baru perlu disalin dari `public/rooms` aplikasi lama ke `public/rooms` Laravel.

Snapshot bawaan ada di `database/legacy/current.json`, dan surat pendukung di `database/legacy/documents`. Paket ini memuat data pengguna, hash password, token display, dan surat dari aplikasi Anda; simpan sebagai paket internal. Importer memeriksa SHA-256 dokumen sebelum menulis data. Lampiran hasil impor disalin ke `storage/app/private/booking-documents`.

Data ZIP/SQL awal September masih didukung oleh `php artisan legacy:import`. Importer lama memakai `database/legacy/meetspace.json`: timestamp dump UTC dikonversi ke WIB, K1 ditandai belum diverifikasi, dan divisi akun perlu ditetapkan. Importer finale memakai timestamp WIB dan `activity_type` untuk K1. Jangan mencampur kedua importer pada satu database tujuan. Jika hasil impor SQL awal belum memiliki Super Admin, jalankan `php artisan users:make-super-admin email-akun-admin-lama` satu kali, lalu tetapkan divisi akun melalui halaman Kelola Pengguna.

## Aturan prioritas konflik

Nilai awal ada di `config/meetspace.php`. Setelah instalasi, Super Admin dapat mengubah porsinya melalui menu **Pengaturan Prioritas**. Nilai tersimpan di tabel `priority_settings`, harus berjumlah 100%, dan dipakai untuk perbandingan berikutnya. Zona waktu aplikasi: `Asia/Jakarta` (WIB).

| Kriteria | Bobot | Tipe | Nilai |
|---|---:|---|---|
| K1 Kepentingan | 30% | Benefit | Direksi/eksternal=5; antar divisi=4; internal divisi=3; sosialisasi/pelatihan=2; rutin=1 |
| K2 Jumlah peserta | 20% | Benefit | >20=4; 11–20=3; 5–10=2; <5=1 |
| K3 Durasi | 15% | Cost | ≤1 jam=1; >1–2=2; >2–3=3; >3=4 |
| K4 Waktu pengajuan | 20% | Benefit | H-3 atau lebih awal=3; H-1/H-2=2; hari yang sama=1 |
| K5 Frekuensi divisi | 15% | Cost | 0–2 kali=1; 3–5 kali=2; ≥6 kali=3 |

K5 menerapkan pemerataan: karena normalisasi Cost adalah `min(x)/x`, divisi yang jarang memakai ruangan diberi nilai 1 dan memperoleh kontribusi lebih tinggi daripada divisi yang sering memakai dengan nilai 3. Frekuensi dihitung dari booking disetujui dan selesai pada bulan jadwal, untuk divisi yang sama, tidak termasuk alternatif itu sendiri.

Benefit: `rij = xij / max(xj)`. Cost: `rij = min(xj) / xij`. Skor: `Vi = Σ(wj × rij)`. Seri diurutkan menurut waktu pengajuan lebih awal, kemudian ID lebih kecil.

Contoh Anda: A1 `[4,3,2,3,2]` menghasilkan **1,000000**; A2 `[3,2,2,1,3]` menghasilkan **0,675000**.

- Pengajuan bentrok tetap masuk sebagai pending untuk evaluasi admin.
- Booking admin tanpa konflik langsung disetujui, mengikuti perilaku finale.
- Admin meninjau urutan rekomendasi dan memilih pengajuan; keputusan tetap dilakukan admin.
- Halaman Kelola Booking menyatukan tab Konflik & Prioritas serta Semua Pemesanan. Tabel konflik memakai nama pertimbangan yang mudah dipahami, nilai prioritas, urutan, dan tombol keputusan. Istilah perhitungan internal tidak ditampilkan pada antarmuka. Halaman memeriksa perubahan data otomatis setiap 10 detik.
- Persetujuan menolak alternatif pending yang bentrok **langsung** dengan pemenang. Pada rantai A–B–C, memilih A tidak otomatis menolak C jika A dan C tidak beririsan.
- Jadwal yang sudah disetujui tidak ditimpa oleh keputusan baru. Ubah/batalkan secara eksplisit bila memang diperlukan.
- Snapshot menyimpan bobot, nilai, normalisasi, skor, dan alasan saat keputusan dilakukan.
- Perubahan porsi tidak mengubah snapshot keputusan lama.
- Edit memperbarui waktu pengajuan dan pengingat. UI menjelaskan hal ini sebelum disimpan.

## Siklus jadwal

- Booking yang disetujui dianggap sedang digunakan selama waktu reservasi.
- Display pintu, monitor lobby, dan status ketersediaan mengikuti waktu mulai dan selesai booking.
- Pending yang melewati jam mulai menjadi expired melalui scheduler atau sinkronisasi ringan saat halaman dibuka.
- Booking disetujui yang melewati jam selesai menjadi completed melalui mekanisme yang sama.
- Migrasi mempertahankan timestamp lama sebagai arsip, tetapi status kehadiran lama tidak lagi dipakai oleh aplikasi.

## Notifikasi dan email

Notifikasi aplikasi langsung aktif tanpa worker. Notifikasi dibuat untuk pengajuan/konflik, keputusan, perubahan booking, pembatalan, penyelesaian jadwal, dan pengingat.

Untuk pengingat 30 menit sebelum jadwal serta kedaluwarsa otomatis, jalankan terminal terpisah:

```powershell
php artisan schedule:work
```

Untuk email, isi SMTP di `.env`, misalnya:

```dotenv
BOOKING_EMAIL_NOTIFICATIONS=true
MAIL_MAILER=smtp
MAIL_HOST=smtp.perusahaan.example
MAIL_PORT=587
MAIL_SCHEME=smtp
MAIL_USERNAME=akun-smtp
MAIL_PASSWORD="isi-password-smtp"
MAIL_FROM_ADDRESS=ruangrapat@perusahaan.example
MAIL_FROM_NAME=MeetSpace
QUEUE_CONNECTION=database
```

Lalu:

```powershell
php artisan config:clear
php artisan queue:work --tries=3
```

Akun legacy menyimpan username pada kolom email pada beberapa kasus. Super Admin perlu mengisi email valid sebelum akun tersebut menerima email. Sistem tetap membuat notifikasi aplikasi jika alamat email tidak valid. Email diantrekan setelah transaksi berhasil; kegagalan SMTP tidak membatalkan booking. Pengiriman ke SMTP sungguhan belum diuji karena kredensial belum disediakan. Default email tetap nonaktif.

## Menjalankan dengan Apache XAMPP

Jalankan melalui `php artisan serve` untuk cara paling sederhana. Jika memakai Apache, arahkan `DocumentRoot`/VirtualHost **ke folder `public` Laravel**, bukan root proyek, dan aktifkan `mod_rewrite` serta `AllowOverride All`. Isi `APP_URL` sesuai alamat VirtualHost. File `.env`, database, dan dokumen privat tidak boleh berada di root publik Apache.

## Berkas utama

- `routes/web.php`: route dan middleware peran, termasuk redirect bookmark `.php` lama.
- `app/Services/BookingService.php`: transaksi booking dan pemeriksaan konflik.
- `app/Services/BookingLifecycleService.php`: transisi kedaluwarsa/selesai yang dipakai scheduler dan fallback permintaan halaman.
- `app/Services/BookingVersionService.php`: tanda versi data untuk penyegaran otomatis halaman Kelola Booking.
- `app/Services/SawService.php`: transformasi kriteria dan perhitungan SAW.
- `app/Services/PriorityWeightService.php`: sumber tunggal porsi penilaian aktif dan penyimpanan perubahan Super Admin.
- `app/Services/ConflictAnalysisService.php`: membentuk kelompok konflik aktif untuk halaman booking dan halaman analisis tanpa menduplikasi logika controller.
- `app/Services/RoomAvailabilityService.php`: status ruangan langsung untuk formulir booking.
- `app/Services/RoomCatalogService.php`: query pencarian/filter ruangan yang dipakai dashboard dan endpoint AJAX.
- `app/Services/BookingReportService.php`, `StatisticsService.php`: query laporan dan agregasi statistik terpusat.
- `app/Services/DisplayStatusService.php`: status pintu/lobby; lobby mengambil data dalam query gabungan agar polling tidak menimbulkan N+1 query.
- `app/Services/DocumentService.php`: lampiran privat dan penggantian berversi.
- `app/Http/Controllers/Admin`: controller admin terpisah menurut ruangan, pengguna, display, laporan, dan kriteria booking.
- `app/Http/Requests`: validasi booking dan filter laporan terpusat.
- `app/Console/Commands/ImportCurrent.php`: impor versi finale.
- `app/Console/Commands/RemindBookings.php`, `ExpireBookings.php`: proses terjadwal.
- `resources/views`: tampilan Blade.
- `public/css/finale.css`, `public/css/admin-management.css`, `public/js`: tampilan serta interaksi dashboard, booking, kalender, display, notifikasi, dan tabel administrasi.
- `tests/Feature`: pengujian alur dan migrasi.

## Verifikasi pada 4 Oktober 2026

- `php artisan test`: **62 pengujian, 476 assertions lulus** pada SQLite terisolasi.
- Migrasi dan impor snapshot finale: berhasil pada MySQL/MariaDB XAMPP di database validasi terpisah; jumlah semua tabel sesuai snapshot.
- Dua proses persetujuan MySQL bersamaan: tepat satu confirmed dan satu rejected, tanpa double booking.
- Browser: login akun QA sementara, pencarian AJAX tanpa reload, popup booking, empat langkah form satu kolom, jam WIB 24 jam tanpa AM/PM, keterbacaan mode terang, kalender agenda seluler, halaman Kelola Booking, dan monitor lobby publik diperiksa. Tidak ditemukan error JavaScript; akun QA sementara dihapus setelah pemeriksaan.
- Ekspor PDF menghasilkan berkas PDF valid, CSV menetralkan formula spreadsheet.
- Kompilasi Blade dan cache route berhasil; kode PHP diformat menggunakan Laravel Pint.
- `composer audit --locked`: tidak menemukan advisori keamanan setelah `league/commonmark` diperbarui ke 2.10.3.

Pengujian ini bukan uji beban skala produksi. Untuk penggunaan bersama, konfigurasi SMTP, scheduler, queue worker, backup, HTTPS, dan `APP_DEBUG=false` sesuai lingkungan server.
