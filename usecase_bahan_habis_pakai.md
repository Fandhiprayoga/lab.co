# Use Case & Actor - Modul Manajemen Bahan Habis Pakai

Dokumen ini mendeskripsikan daftar aktor dan use case spesifik untuk Modul Manajemen Bahan Habis Pakai, menggunakan aktor yang telah ditetapkan pada sistem.

---

## 1. Daftar Aktor (Actors)

Dalam modul ini, terdapat 3 aktor manusia dan 1 aktor sistem (otomatisasi).

| Aktor | Deskripsi Peran dalam Modul Bahan Habis Pakai |
| :--- | :--- |
| **Laboran** | Petugas operasional laboratorium yang mengajukan kebutuhan pemakaian internal, menyiapkan dan menyalurkan bahan, serta mencatat realisasi penggunaan dan kondisi stok. |
| **Kepala Lab** | Dosen penanggung jawab laboratorium yang memvalidasi permintaan tertentu dan memantau pemanfaatan bahan pada level kebijakan laboratorium. |
| **Admin Sistem** | Super-user yang mengelola master data, konfigurasi, akun, hak akses, serta pemetaan organisasi untuk memastikan alur persetujuan berjalan benar. |
| **Sistem** | Background job yang menjalankan otomatisasi seperti notifikasi stok minimum, pengingat approval, pembatalan otomatis, dan penandaan bahan kedaluwarsa. |

---

## 2. Daftar Use Case (Berdasarkan Aktor)

### A. Aktor: Laboran
* **UC-BHP-01: Melihat Katalog Bahan dan Ketersediaan**
  * Mencari bahan berdasarkan kategori, nama, satuan, lokasi penyimpanan, atau status ketersediaan.
  * Melihat informasi stok tersedia, batas minimum, dan ketentuan pemakaian.
* **UC-BHP-02: Membuat Permintaan Penggunaan Bahan**
  * Menginput kebutuhan bahan untuk kegiatan praktikum, riset, atau operasional laboratorium.
  * Mengisi jumlah rencana pakai, tujuan penggunaan, dan jadwal kebutuhan.
* **UC-BHP-03: Mengubah atau Membatalkan Permintaan**
  * Mengubah detail jumlah atau tujuan selama status masih menunggu persetujuan.
  * Membatalkan permintaan sebelum bahan diproses untuk pengeluaran.
* **UC-BHP-04: Memproses Pengeluaran Bahan**
  * Menyiapkan bahan sesuai jumlah disetujui.
  * Mengeluarkan bahan dari stok dan mengubah status menjadi dalam penggunaan.
* **UC-BHP-05: Mencatat Realisasi Penggunaan**
  * Mencatat jumlah aktual terpakai pada akhir kegiatan.
  * Menutup transaksi setelah realisasi penggunaan tervalidasi.
* **UC-BHP-06: Mencatat Penyesuaian Stok Operasional**
  * Mencatat susut, rusak, tumpah, atau bahan tidak layak pakai sebagai penyesuaian stok.

### B. Aktor: Kepala Lab
* **UC-BHP-07: Memvalidasi Permintaan Bahan (Jika Perlu)**
  * Menyetujui permintaan bahan tertentu berdasarkan kebijakan (misalnya bahan terbatas, mahal, atau berisiko).
* **UC-BHP-08: Melihat Dasbor Konsumsi Bahan (Analytics)**
  * Melihat tren pemakaian, bahan paling sering digunakan, dan rasio penggunaan per periode untuk kebutuhan pelaporan.

### C. Aktor: Admin Sistem
* **UC-BHP-09: Mengelola Master Data Bahan Habis Pakai**
  * Menambah/mengubah data bahan, kategori, satuan, batas minimum stok, lokasi penyimpanan, dan parameter kedaluwarsa.
* **UC-BHP-10: Mengelola Pengguna dan Hak Akses Modul**
  * Mengatur akun, peran, cakupan akses, serta mapping laboran ke lab dan lab ke prodi/fakultas.
  * Mengatur aturan approval, batas kuota bahan, dan visibilitas data lintas unit.

### D. Aktor: Sistem (Otomatisasi)
* **UC-BHP-11: Eksekusi Pembatalan Otomatis Permintaan**
  * Membatalkan permintaan yang sudah disetujui tetapi tidak diproses melewati batas waktu aktif permintaan.
* **UC-BHP-12: Pengiriman Notifikasi dan Reminder**
  * Mengirim notifikasi untuk status persetujuan, pengeluaran bahan, dan tindak lanjut permintaan.
* **UC-BHP-13: Notifikasi Stok Minimum dan Kedaluwarsa**
  * Menandai bahan di bawah stok minimum dan mengirim peringatan restock.
  * Menandai bahan yang mendekati/melewati tanggal kedaluwarsa untuk ditindaklanjuti.

---

## 3. Alur Status Permintaan Bahan (State Machine / Lifecycle)

Siklus hidup status sebuah transaksi penggunaan bahan habis pakai bergerak mengikuti alur berikut:

Draft -> Menunggu Persetujuan (Ka. Lab - Jika Perlu) -> Disetujui (Siap Diproses) -> Dikeluarkan (Dalam Penggunaan) -> Selesai (Realisasi Tercatat)

**Status Pengecualian (Exceptions):**
* Ditolak: Oleh Laboran atau Kepala Lab disertai alasan.
* Dibatalkan: Oleh Laboran sebelum diproses atau oleh Sistem karena lewat batas waktu aktif permintaan.
* Kedaluwarsa Permintaan: Permintaan tidak dilanjutkan dalam jangka waktu aktif transaksi.
* Bermasalah: Terdapat anomali realisasi, mismatch stok, atau bahan dilaporkan rusak/tidak layak saat proses.
