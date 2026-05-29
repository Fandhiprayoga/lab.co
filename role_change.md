# Role Change Log

## [2026-05-29] Restrukturisasi Role — Manajemen Laboratorium

### Ringkasan
Mengganti 4 role generik menjadi 6 role spesifik sesuai struktur organisasi laboratorium universitas.

### Role Lama → Role Baru

| Role Lama  | Role Baru    | Keterangan                                              |
| :--------- | :----------- | :------------------------------------------------------ |
| superadmin | superadmin   | Tidak berubah — kontrol penuh seluruh sistem            |
| admin      | laboran      | Digantikan role operasional lab (approval L1, checkout) |
| manager    | kepala_lab   | Digantikan role kepala lab (approval L2, analytics)     |
| user       | dosen        | Dipecah — dosen sebagai peminjam & kelak pengampu praktikum |
| *(baru)*   | asisten      | Role baru — bantu checkout/checkin, tanpa hak approval  |
| *(baru)*   | mahasiswa    | Dipecah dari user — peminjam & kelak peserta praktikum  |

---

### Detail Perubahan per File

#### `app/Config/AuthGroups.php`
- `$defaultGroup`: `'user'` → `'mahasiswa'`
- `$groups`: 4 group lama dihapus, 6 group baru ditambahkan
- `$matrix`: seluruh permission matrix diperbarui

**Permission matrix baru:**

| Role        | Permission Utama                                                                               |
| :---------- | :--------------------------------------------------------------------------------------------- |
| superadmin  | `admin.*`, `users.*`, `roles.*`, `dashboard.*`, `reports.*`, `lending.*`, `visits.*`           |
| laboran     | `admin.access`, `dashboard.*`, `lending` (operational + approval L1), `visits.*`              |
| asisten     | `admin.access`, `dashboard.access`, `lending` (checkout/checkin saja), `visits.*`             |
| kepala_lab  | `admin.access`, `users.list`, `dashboard.*`, `reports.*`, `lending` (approval L2 + analytics) |
| dosen       | `dashboard.access`, `lending` (borrower full — create, submit, track, history)                |
| mahasiswa   | `dashboard.access`, `lending` (borrower full — sama dengan dosen saat ini)                    |

#### `app/Database/Seeds/UserSeeder.php`
- Hapus: `admin@example.com`, `manager@example.com`, `user@example.com`
- Tambah: `laboran@example.com`, `asisten@example.com`, `kepalab@example.com`, `dosen@example.com`, `mahasiswa@example.com`

#### `app/Views/partials/navbar.php`
- `$badgeColors` array diperbarui untuk 6 role baru

#### `app/Views/profile/index.php`
- `match($group)` diperbarui untuk 6 role baru

#### `app/Views/users/index.php`
- `match($group)` diperbarui untuk 6 role baru

#### `app/Views/roles/index.php`
- `match($key)` diperbarui untuk 6 role baru

---

### Badge Color Map

| Role        | Badge Class      |
| :---------- | :--------------- |
| superadmin  | `badge-danger`   |
| laboran     | `badge-warning`  |
| asisten     | `badge-info`     |
| kepala_lab  | `badge-success`  |
| dosen       | `badge-primary`  |
| mahasiswa   | `badge-secondary`|

---

### Alasan Pemisahan `dosen` / `mahasiswa`
Keduanya sengaja dipisah meskipun permission saat ini identik, untuk persiapan fitur **Praktikum**:
- `dosen` → akan menjadi **pengampu praktikum** (kelola jadwal, peserta, penilaian)
- `mahasiswa` → akan menjadi **peserta praktikum** (daftar, ikuti sesi, submit laporan)

---

### File yang Tidak Perlu Diubah
- `app/Config/Routes.php` — filter `role:superadmin` tetap berlaku
- `app/Filters/MaintenanceFilter.php` — hanya cek `superadmin`
- `app/Views/partials/sidebar.php` — hanya cek `superadmin`
- `app/Controllers/UserController.php` — loop groups dari config secara dinamis
- `app/Common.php` — fungsi generik tanpa hardcode role
- `app/Views/loans/*` — string "Laboran"/"Kepala Lab" adalah label display, bukan role check
