# Garis Besar Aplikasi dan Progres Pengerjaan

Dokumen ini merangkum gambaran umum aplikasi laboratorium ini dan status pekerjaan yang sudah ada di kode saat ini.

## 1. Gambaran Umum Aplikasi

Aplikasi ini adalah sistem informasi laboratorium berbasis CodeIgniter 4 untuk membantu pengelolaan proses akademik dan operasional lab. Fokus utamanya mencakup pengelolaan fasilitas laboratorium, alur layanan untuk mahasiswa/dosen/laboran, serta proses administratif yang biasanya masih manual.

Secara garis besar, aplikasi ini mengarah ke beberapa area utama:

- manajemen peminjaman lab dan alat
- surat bebas lab
- rekrutmen dan pengelolaan asisten praktikum
- sertifikasi
- manajemen tahun akademik
- manajemen data master laboratorium
- notifikasi dan kontrol akses berbasis role/permission

## 2. Arsitektur Singkat

- Framework: CodeIgniter 4
- Pola akses: role-based access control berbasis permission
- Arsitektur tampilan: controller, model, view, dan layout terpusat
- Fitur notifikasi: sudah disediakan service notifikasi internal
- Struktur menu: sudah dikontrol lewat registry menu dan permission

## 3. Apa yang Sudah Dikerjakan

### A. Pondasi Aplikasi

- Struktur aplikasi CodeIgniter 4 sudah tersedia.
- Sistem autentikasi, role, dan permission sudah disiapkan.
- Menu sidebar dan hak akses sudah dipetakan per role.
- Notification service sudah tersedia untuk event tertentu.
- Pola render view dan layout aplikasi sudah distandardisasi.

### B. Modul Peminjaman Lab dan Alat

- Modul peminjaman lab/alat sudah dibangun.
- Sudah ada alur proposal, status, approval bertahap, dan pencatatan riwayat.
- Sudah tersedia master data pendukung seperti lab, aset, kategori, unit, fakultas, dan program studi.
- Sudah ada fitur inventaris seperti mutasi, maintenance, dokumentasi aset, foto lab, dan histori kondisi lab.

### C. Modul Surat Bebas Lab

- Modul surat bebas lab sudah diimplementasikan.
- Ada controller, model, migration, dan view khusus untuk pengajuan serta verifikasi.
- Sudah ada status proses seperti diajukan, terbit, ditolak, dan dibatalkan.
- Sudah ada tampilan beranda, daftar pengajuan, detail, filter, dan export riwayat.

### D. Modul Oprek Asprak

- Modul open recruitment asisten sudah tersedia.
- Sudah ada komponen campaign, pendaftaran, dokumen, penilaian, keputusan akhir, onboarding, dan activity log.
- Alur oprek sudah terhubung dengan tahun akademik aktif.
- Sudah ada pembatasan akses berdasarkan lab yang ditugaskan ke pengguna.

### E. Modul Sertifikasi

- Modul sertifikasi sudah tersedia.
- Sudah ada manajemen template sertifikat dan komponen template.
- Sudah ada penerbitan sertifikat individual dan bulk via CSV.
- Sudah ada halaman detail, rendering sertifikat, notifikasi penerbitan, dan verifikasi publik.

### F. Manajemen Tahun Akademik

- Manajemen tahun akademik sudah dibangun di area admin.
- Sudah ada fitur tambah, edit, hapus, dan aktivasi tahun akademik.
- Modul lain seperti oprek sudah memakai tahun akademik aktif sebagai dasar proses.

## 4. Gambaran Status Modul per Area

### Sudah Siap / Sudah Ada Implementasi

- Peminjaman lab dan alat
- Surat bebas lab
- Oprek asprak
- Sertifikasi
- Manajemen tahun akademik
- Master data laboratorium
- Notifikasi internal
- Role dan permission

### Sudah Ada Fondasi, Tapi Belum Tampak Sebagai Modul Penuh

- Praktikum sebagai proses operasional end-to-end
- Manajemen honor asisten secara khusus
- Integrasi praktikum yang menyatu ke dosen, asisten, tahun akademik, dan penilaian

### Masih Perlu Diperjelas / Dipecah Lebih Detail

- Flow praktikum lengkap dari oprek sampai rekap akhir
- Alur approval honor asisten
- Struktur data praktikum per semester atau tahun akademik
- Integrasi antar modul agar tidak berdiri sendiri

## 5. Kesimpulan Sementara

Aplikasi ini sudah punya fondasi yang cukup kuat untuk sistem laboratorium kampus. Dari sisi implementasi, modul yang paling jelas sudah berjalan adalah peminjaman, bebas lab, oprek asprak, sertifikasi, dan manajemen tahun akademik. Ke depan, area yang paling logis untuk dilengkapi adalah modul praktikum yang menyatukan alur dosen, asisten, honor, dan pengelolaan semester secara utuh.

## 6. Catatan Untuk Pengembangan Berikutnya

- Pastikan setiap modul punya alur status yang konsisten.
- Satukan permission agar hak akses mudah dipelihara.
- Lengkapi integrasi antar modul supaya data tidak duplikat.
- Dokumentasikan proses bisnis praktikum sebelum masuk implementasi penuh.
