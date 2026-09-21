# Arsitektur MeetSpace Laravel

## Tujuan

Kode disusun sebagai modular monolith. Struktur ini mempertahankan kemudahan deployment satu aplikasi, tetapi memisahkan aturan bisnis agar dapat diuji dan dikembangkan tanpa mengikatnya ke HTTP atau Blade.

## Lapisan

```text
HTTP Request
  -> Form Request (otorisasi dan validasi bentuk input)
  -> Controller (orkestrasi HTTP dan redirect/response)
  -> Service (aturan bisnis dan transaksi)
  -> Eloquent Model (relasi dan persistensi)
  -> MySQL
```

- `app/Enums`: nilai status dan jenis kegiatan yang valid.
- `app/Data`: data transfer object immutable antar-lapisan.
- `app/Http/Requests`: validasi input pada batas aplikasi.
- `app/Policies`: otorisasi berbasis kepemilikan resource.
- `app/Services`: transaksi, konflik jadwal, approval, dan attendance.
- `app/Http/Controllers`: controller tipis tanpa query bisnis kompleks.
- `app/Console/Commands`: proses yang dapat dijalankan scheduler.
- `resources/views`: Blade hanya menangani presentasi.

## Aturan pengembangan

1. Controller tidak boleh berisi transaksi atau aturan status booking.
2. Semua perubahan booking harus melalui service terkait.
3. Input baru harus menggunakan Form Request.
4. Akses booking milik pengguna harus melalui policy.
5. Status baru harus ditambahkan sebagai enum dan disertai migration/test.
6. Query daftar harus menggunakan pagination dan eager loading.
7. Operasi yang rawan race condition harus memakai transaksi serta row locking.
8. Setiap perbaikan aturan bisnis wajib memiliki feature test atau unit test.

## Modul administrasi

Controller administrasi dipisahkan per resource (`Booking`, `Conflict`, `Room`, `User`, dan `RoomDisplay`). Setiap mutasi menerima input melalui Form Request dan mendelegasikan aturan bisnis ke service terkait.

- `RoomAdministrationService` mengelola lifecycle foto dan mencegah penghapusan ruangan yang memiliki booking atau perangkat display.
- `UserAdministrationService` mencegah perubahan role akun sendiri, penghapusan riwayat booking, dan hilangnya administrator terakhir.
- `RoomDisplayService` mengunci baris ruangan ketika mendaftarkan display sehingga dua request paralel tidak dapat membuat dua display untuk ruangan yang sama.
- Token display dibuat dari sumber acak kriptografis, tidak diterima dari input pengguna, dan dapat dirotasi.

Pembuatan akun dilakukan melalui alur registrasi pengguna. Halaman admin hanya mengatur role dan lifecycle akun agar tidak ada dua implementasi aturan password.

## Konflik dan SAW

`SawDecisionService` memiliki dua tanggung jawab bisnis yang berhubungan erat:

1. Membentuk connected component dari booking yang overlap pada ruangan dan tanggal yang sama.
2. Menghitung matriks SAW dan menerapkan rekomendasi secara atomik.

Kriteria SAW:

| Kriteria | Jenis | Bobot |
| --- | --- | ---: |
| Tingkat kepentingan kegiatan | Benefit | 30% |
| Jumlah peserta | Benefit | 20% |
| Durasi penggunaan | Cost | 15% |
| Jarak waktu pengajuan | Benefit | 20% |
| Frekuensi divisi pada bulan berjalan | Cost | 15% |

Frekuensi divisi dimodelkan sebagai biaya nyata: semakin sering divisi memakai ruangan, semakin besar nilai cost. Ini memperbaiki kontradiksi implementasi lama yang memberi skor mentah tinggi kepada pemakaian rendah tetapi kemudian menormalisasikannya sebagai cost.

Saat rekomendasi diterapkan, service mengunci ruangan dan seluruh booking aktif pada tanggal tersebut, membentuk ulang kelompok konflik, lalu menghitung ulang pemenang. ID booking kalah tidak pernah dipercaya dari form browser. Pendekatan ini mencegah keputusan berdasarkan data basi dan manipulasi ID.

## Konsistensi booking

Pembuatan dan approval mengunci baris ruangan selama pemeriksaan konflik. Ini membuat dua request paralel untuk ruangan yang sama diproses berurutan dan mencegah jadwal terkonfirmasi ganda.

Rumus overlap yang digunakan:

```text
existing.start < requested.end AND existing.end > requested.start
```

Pertemuan yang tepat bersambung, misalnya `09:00–10:00` dan `10:00–11:00`, tidak dianggap bentrok.

## Attendance

- Check-in dibuka 15 menit sebelum jadwal.
- Grace period berakhir 15 menit setelah jadwal mulai.
- Booking tanpa check-in setelah grace period menjadi `no_show`.
- Booking yang sudah check-in otomatis check-out setelah waktu selesai.
- Nilai dapat diubah melalui environment variable tanpa menyentuh kode.

Scheduler menjalankan:

```text
attendance:process
```

setiap menit dan menggunakan `withoutOverlapping()`.

## Keamanan

- CSRF aktif untuk seluruh form web.
- Login/registrasi dan aksi attendance dibatasi rate limiter.
- Policy mencegah IDOR pada booking pengguna lain.
- Role admin divalidasi middleware server-side.
- Query menggunakan Eloquent/query builder terparameterisasi.
- Password baru menggunakan hash Laravel dan aturan kompleksitas.
- Security headers diterapkan secara global.
- Token display disembunyikan dari serialisasi model.
- Upload foto dibatasi pada gambar JPG/PNG/WebP maksimal 5 MB dan disimpan dengan nama UUID.
- Penghapusan entitas bersejarah ditolak untuk mencegah kehilangan data akibat foreign key cascade.
- Token display panjang, acak, unik, dan dapat dirotasi dari panel admin.
- Production harus memakai HTTPS dan `APP_DEBUG=false`.

## Strategi migrasi

Laravel membaca database `meetspace_db` yang sama selama masa transisi. Jangan menjalankan perintah database destruktif pada database tersebut. Aplikasi PHP lama tetap menjadi fallback sampai seluruh modul Laravel lolos uji penerimaan.
