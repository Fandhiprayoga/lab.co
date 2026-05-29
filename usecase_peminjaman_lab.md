# Use Case & Actor - Modul Peminjaman Lab dan Alat

Dokumen ini mendeskripsikan daftar aktor dan *use case* spesifik untuk Modul Manajemen Peminjaman Lab dan Alat, disesuaikan tanpa adanya sistem denda finansial.

---

## 1. Daftar Aktor (Actors)

Dalam modul ini, terdapat 4 aktor manusia dan 1 aktor sistem (otomatisasi).

| Aktor | Deskripsi Peran dalam Modul Peminjaman |
| :--- | :--- |
| **Peminjam** | Pengguna akhir (Mahasiswa/Dosen/Peneliti) yang membutuhkan fasilitas lab atau alat, dengan akses sesuai ketentuan prodi dan ketersediaan fasilitas. |
| **Laboran** | Petugas operasional yang menjaga lab tertentu berdasarkan *mapping* fakultas/prodi dan laboratorium. Bertugas mengeksekusi serah-terima fisik dan menjadi pintu gerbang pertama persetujuan peminjaman. |
| **Kepala Lab** | Dosen penanggung jawab laboratorium. Terlibat dalam persetujuan tingkat lanjut (Level-2 Approval) untuk peminjaman. |
| **Admin Sistem** | Super-user yang mengatur tata letak data modul, pengguna, dan konfigurasi umum peminjaman. |
| **Sistem** | *Background job* (aktor otomatis) yang menjalankan tugas seperti notifikasi, pembatalan otomatis, dan pembaruan status keterlambatan. |

---

## 2. Daftar Use Case (Berdasarkan Aktor)

### A. Aktor: Peminjam
* **UC-01: Melihat Katalog dan Ketersediaan**
  * Mencari alat/ruang lab berdasarkan kategori, ketersediaan (*real-time*), atau lokasi lab.
  * Melihat spesifikasi alat dan informasi durasi peminjamannya.
* **UC-02: Membuat Permohonan Peminjaman**
  * Memilih barang/ruang, mengisi tanggal & jam ambil, tanggal & jam kembali, serta tujuan penggunaan.
  * Mengunggah dokumen pendukung (jika diminta, misal: surat izin penelitian).
* **UC-03: Membatalkan Permohonan**
  * Membatalkan *request* peminjaman selama statusnya masih "Menunggu Persetujuan" (belum diproses laboran).
* **UC-04: Melacak Status Peminjaman**
  * Melihat status pengajuan peminjaman yang sedang berlangsung.
* **UC-05: Melihat Riwayat Peminjaman**
  * Melihat histori peminjaman sebelumnya beserta ringkasan hasil pengembalian.

### B. Aktor: Laboran
* **UC-06: Mengelola Permohonan Masuk (Approval Level 1)**
  * Melihat daftar *request* yang masuk khusus untuk lab yang menjadi tanggung jawabnya.
  * Menyetujui (*Approve*), Menolak (*Reject*) dengan catatan, atau merevisi jumlah/durasi yang diajukan peminjam.
* **UC-07: Memproses Serah Terima (Check-out Alat/Ruang)**
  * Memverifikasi kehadiran peminjam di lab.
  * Mengubah status sistem menjadi "Sedang Dipinjam".
  * Mencatat kondisi fisik alat saat diserahkan.
* **UC-08: Memproses Pengembalian (Check-in Alat/Ruang)**
  * Menerima barang kembali dari peminjam.
  * Mengecek kondisi alat dan mencatatnya (Sesuai/Rusak/Hilang).
  * Menyelesaikan siklus peminjaman untuk membebaskan kuota alat.
* **UC-09: Mencatatkan Laporan Kerusakan/Kehilangan**
  * Memicu *flagging* jika barang dikembalikan dalam keadaan rusak/hilang untuk proses tindak lanjut administrasi.

### C. Aktor: Kepala Lab
* **UC-10: Memvalidasi Peminjaman (Approval Level 2)**
  * Menyetujui permohonan peminjaman.
* **UC-11: Melihat Dasbor Pemanfaatan Lab (Analytics)**
  * Melihat statistik utilisasi ruang dan alat untuk keperluan pelaporan atau borang akreditasi.

### D. Aktor: Admin Sistem
* **UC-12: Mengelola Master Data Peminjaman**
  * Menambah/mengubah data Fakultas, Prodi, Alat, Ruang Lab, Kategori, jadwal operasional, dan data pendukung peminjaman.
* **UC-13: Mengelola Pengguna & Hak Akses Modul**
  * Mengatur akun pengguna modul peminjaman, peran, serta cakupan akses per laboratorium.
  * Menetapkan *mapping* Laboran ke Lab, Lab ke Prodi/Fakultas, serta Alat dan Ruang ke Lab agar alur persetujuan dan visibilitas data sesuai struktur organisasi.

### E. Aktor: Sistem (Otomatisasi)
* **UC-14: Eksekusi Pembatalan Otomatis (Auto-Cancel)**
  * Membatalkan peminjaman yang sudah di-*approve* namun tidak diambil oleh peminjam melebihi batas toleransi waktu yang ditentukan.
* **UC-15: Pengiriman Notifikasi & Reminder**
  * Mengirim *push notification* / email pengingat (contoh: H-1 sebelum tenggat waktu pengembalian).
* **UC-16: Penandaan Keterlambatan Otomatis**
  * Menandai transaksi sebagai terlambat secara otomatis apabila melewati batas waktu pengembalian dan mengirim notifikasi tindak lanjut.

---

## 3. Alur Status Peminjaman (State Machine / Lifecycle)

Siklus hidup status sebuah transaksi peminjaman bergerak mengikuti alur berikut:

`Draft` -> `Menunggu Persetujuan (Laboran)` -> `Menunggu Persetujuan (Ka. Lab - *Jika Perlu*)` -> `Disetujui (Menunggu Diambil)` -> `Sedang Dipinjam (Barang di tangan user)` -> `Dikembalikan (Selesai)`

**Status Pengecualian (Exceptions):**
* `Ditolak`: Oleh Laboran atau Kepala Lab.
* `Dibatalkan`: Oleh Peminjam sebelum diproses, atau oleh Sistem karena melewati batas waktu pengambilan.
* `Terlambat`: Melewati batas waktu pengembalian yang disepakati dan memicu notifikasi tindak lanjut.
* `Bermasalah`: Dikembalikan namun dilaporkan dalam kondisi rusak/hilang oleh Laboran.
