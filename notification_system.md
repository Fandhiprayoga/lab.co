# Sistem Notifikasi In-App

Dokumentasi penggunaan dan cara mengintegrasikan fitur notifikasi ke modul-modul lain di aplikasi LabCorner.

---

## Arsitektur

```
NotificationService (Library)
  └── sendToUser(userId, type, context)    → kirim ke 1 user
  └── sendToRole(role, type, context)      → kirim ke semua user dalam role

notification_helper.php
  └── send_notification(userId, type, context)   → wrapper sendToUser
  └── notify_role(role, type, context)            → wrapper sendToRole

NotificationModel
  └── getUnreadCount(userId)
  └── getRecent(userId, limit)
  └── markRead(id, userId)
  └── markAllRead(userId)
  └── trimOld(userId, max=100)    ← dipanggil otomatis saat insert
  └── deleteOwned(id, userId)
  └── getPaginated(userId, perPage)

NotificationController
  └── GET  /notifications                  → halaman full list
  └── GET  /notifications/unread-count     → JSON {count: N} (polling badge)
  └── GET  /notifications/recent           → JSON 10 notif terbaru (dropdown)
  └── POST /notifications/:id/read         → tandai 1 notif dibaca
  └── POST /notifications/read-all         → tandai semua dibaca
  └── DELETE /notifications/:id            → hapus 1 notif
```

---

## Cara Menggunakan Helper

Helper `notification` sudah otomatis dimuat di semua controller (terdaftar di `BaseController::$helpers`). Tidak perlu `helper('notification')` manual.

### Kirim ke 1 user spesifik

```php
send_notification(
    $userId,          // int — ID user penerima (Shield user ID)
    'loan.submitted', // string — tipe notifikasi (lihat daftar tipe di bawah)
    [
        'proposal_code' => 'PROP-20260531-ABCDEF',  // placeholder untuk template pesan
        'url'           => base_url('loans/5'),      // URL tujuan saat notif diklik (opsional)
        'reference_id'  => 5,                        // ID referensi (opsional, disimpan di data JSON)
    ]
);
```

### Kirim ke semua user dalam satu role

```php
notify_role(
    'laboran',        // string — nama role target
    'loan.submitted', // string — tipe notifikasi
    [
        'proposal_code' => 'PROP-20260531-ABCDEF',
        'url'           => base_url('admin/loans/proposals/5'),
    ]
);
```

Role yang tersedia: `superadmin`, `laboran`, `asisten`, `kepala_lab`, `dosen`, `mahasiswa`

---

## Daftar Tipe Notifikasi

| Type | Judul | Kapan digunakan |
|------|-------|-----------------|
| `loan.submitted` | Proposal Baru Masuk | Setelah proposal dikirim oleh peminjam |
| `loan.approved_l1` | Proposal Disetujui (L1) | Laboran menyetujui, lanjut ke Kepala Lab |
| `loan.approved_l2` | Proposal Disetujui | Proposal final disetujui (L2 atau L1-only) |
| `loan.rejected_l1` | Proposal Ditolak | Laboran menolak proposal |
| `loan.rejected_l2` | Proposal Ditolak | Kepala Lab menolak proposal |
| `loan.checked_out` | Checkout Berhasil | Barang/lab sudah di-checkout |
| `loan.checked_in` | Pengembalian Dicatat | Barang/lab sudah dikembalikan |
| `bhp.submitted` | Permintaan BHP Baru | Permintaan bahan habis pakai dikirim |
| `bhp.approved` | Permintaan BHP Disetujui | Laboran menyetujui permintaan BHP |
| `bhp.rejected` | Permintaan BHP Ditolak | Laboran menolak permintaan BHP |
| `bhp.disbursed` | BHP Siap Diambil | Bahan sudah disiapkan laboran |
| `bhp.realized` | BHP Telah Direalisasi | Realisasi selesai dicatat |
| `visit.force_checkout` | Force Checkout Kunjungan | Admin men-force checkout pengunjung |

### Menambah Tipe Baru

Edit `app/Libraries/NotificationService.php`, tambahkan entry baru di konstanta `TYPES`:

```php
public const TYPES = [
    // ... existing types ...

    'maintenance.scheduled' => [
        'title'   => 'Jadwal Perawatan',
        'message' => 'Aset {asset_name} dijadwalkan perawatan pada {scheduled_date}.',
        'icon'    => 'fas fa-wrench',
        'color'   => 'warning',  // primary | success | warning | danger | info | secondary
    ],
];
```

**Placeholder**: tulis `{nama_key}` di `title` atau `message`. Nilai diisi dari array `$context` saat memanggil helper. Key `url` dan `reference_id` adalah reserved dan tidak dinterpolasi ke pesan.

---

## Implementasi di Modul Lain

### Contoh: LoanProposalController

```php
// submit() — peminjam kirim proposal
public function submit(int $id)
{
    // ... validasi & update status ke waiting_l1 ...

    // Notifikasi ke semua laboran
    notify_role('laboran', 'loan.submitted', [
        'proposal_code' => $proposal['proposal_code'],
        'url'           => base_url('admin/loans/proposals/' . $id),
    ]);

    return redirect()->to(base_url('loans/' . $id))->with('success', 'Proposal berhasil dikirim.');
}

// approveL1() — laboran menyetujui
public function approveL1(int $id)
{
    // ... update status ...

    if ($proposal['requires_l2']) {
        // Masih butuh persetujuan Kepala Lab
        notify_role('kepala_lab', 'loan.approved_l1', [
            'proposal_code' => $proposal['proposal_code'],
            'url'           => base_url('admin/loans/proposals/' . $id),
        ]);
    } else {
        // Langsung disetujui (tidak butuh L2)
        send_notification($proposal['proposer_id'], 'loan.approved_l2', [
            'proposal_code' => $proposal['proposal_code'],
            'url'           => base_url('loans/' . $id),
        ]);
    }
}

// rejectL1() — laboran menolak
public function rejectL1(int $id)
{
    // ... update status ...

    send_notification($proposal['proposer_id'], 'loan.rejected_l1', [
        'proposal_code' => $proposal['proposal_code'],
        'url'           => base_url('loans/' . $id),
    ]);
}

// approveL2() — kepala lab menyetujui
public function approveL2(int $id)
{
    // ... update status ...

    send_notification($proposal['proposer_id'], 'loan.approved_l2', [
        'proposal_code' => $proposal['proposal_code'],
        'url'           => base_url('loans/' . $id),
    ]);
}

// rejectL2() — kepala lab menolak
public function rejectL2(int $id)
{
    // ... update status ...

    send_notification($proposal['proposer_id'], 'loan.rejected_l2', [
        'proposal_code' => $proposal['proposal_code'],
        'url'           => base_url('loans/' . $id),
    ]);
}

// checkout() — laboran melakukan checkout
public function checkout(int $id)
{
    // ... proses checkout ...

    send_notification($proposal['proposer_id'], 'loan.checked_out', [
        'proposal_code' => $proposal['proposal_code'],
        'url'           => base_url('loans/' . $id),
    ]);
}

// checkin() — laboran mencatat pengembalian
public function checkin(int $id)
{
    // ... proses checkin ...

    send_notification($proposal['proposer_id'], 'loan.checked_in', [
        'proposal_code' => $proposal['proposal_code'],
        'url'           => base_url('loans/' . $id),
    ]);
}
```

---

### Contoh: ConsumableController (BHP)

```php
// submit() — user submit permintaan BHP
public function submit(string $publicId)
{
    // ... update status ...

    notify_role('laboran', 'bhp.submitted', [
        'request_code' => $request['request_code'],
        'url'          => base_url('admin/consumables/requests/' . $publicId),
    ]);
}

// approve() — laboran menyetujui
public function approve(string $publicId)
{
    // ... update status ...

    send_notification($request['proposer_id'], 'bhp.approved', [
        'request_code' => $request['request_code'],
        'url'          => base_url('consumables/requests/' . $publicId),
    ]);
}

// reject() — laboran menolak
public function reject(string $publicId)
{
    // ... update status ...

    send_notification($request['proposer_id'], 'bhp.rejected', [
        'request_code' => $request['request_code'],
        'url'          => base_url('consumables/requests/' . $publicId),
    ]);
}

// disburse() — laboran serahkan bahan
public function disburse(string $publicId)
{
    // ... update status ...

    send_notification($request['proposer_id'], 'bhp.disbursed', [
        'request_code' => $request['request_code'],
        'url'          => base_url('consumables/requests/' . $publicId),
    ]);
}

// storeRealization() — selesai realisasi
public function storeRealization(string $publicId)
{
    // ... update status ...

    send_notification($request['proposer_id'], 'bhp.realized', [
        'request_code' => $request['request_code'],
        'url'          => base_url('consumables/requests/' . $publicId),
    ]);
}
```

---

### Contoh: LabVisitController (Kunjungan)

```php
// forceCheckout() — admin force checkout pengunjung
public function forceCheckout(int $id)
{
    // ... update checked_out_at ...

    notify_role('laboran', 'visit.force_checkout', [
        'visitor_name' => $visit['visitor_name'],
        'lab_name'     => $lab['name'],
        'url'          => base_url('admin/visits'),
    ]);

    return $this->response->setJSON(['success' => true]);
}
```

---

## Checklist Integrasi

Untuk setiap titik trigger di controller:

- [ ] Identifikasi siapa **penerima** (1 user atau 1 role)
- [ ] Pilih **tipe** dari daftar di atas (atau buat tipe baru di `NotificationService::TYPES`)
- [ ] Tentukan **URL tujuan** — URL halaman yang relevan untuk penerima (`url` key di context)
- [ ] Tambahkan **placeholder** yang dibutuhkan template pesan (misal `proposal_code`, `request_code`)
- [ ] Panggil `send_notification()` atau `notify_role()` **setelah** operasi DB berhasil (jangan sebelum)
- [ ] Test: cek badge muncul di navbar & sidebar penerima, klik notif → redirect ke URL yang benar

---

## Tabel Database

```sql
notifications
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  user_id      INT UNSIGNED NOT NULL          -- penerima
  type         VARCHAR(100) NOT NULL          -- e.g. 'loan.submitted'
  title        VARCHAR(255) NOT NULL
  message      TEXT NOT NULL
  data         JSON NULL                      -- {icon, color, url, reference_id}
  read_at      DATETIME NULL                  -- NULL = belum dibaca
  created_at   DATETIME NULL
  updated_at   DATETIME NULL

Index: (user_id, read_at), (created_at)
```

**Auto-trim**: setiap user maksimum menyimpan **100 notifikasi**. Notifikasi terlama otomatis dihapus saat insert baru melewati batas ini (`NotificationModel::trimOld()`).

---

## CLI — Testing Notifikasi

Command `notify:test` tersedia untuk mengirim notifikasi test tanpa harus memicu alur bisnis.

### Menampilkan semua tipe

```bash
php spark notify:test --list
```

### Kirim ke user spesifik

```bash
# Kirim ke user ID 1, tipe default (loan.submitted)
php spark notify:test --user=1

# Kirim ke user ID 3 dengan tipe tertentu
php spark notify:test --user=3 --type=bhp.approved

# Sertakan URL tujuan
php spark notify:test --user=3 --type=bhp.approved --url=/consumables/requests/abc-uuid
```

### Blast ke semua user dalam satu role

```bash
php spark notify:test --role=laboran --type=loan.submitted
php spark notify:test --role=kepala_lab --type=loan.approved_l1
php spark notify:test --role=mahasiswa --type=bhp.disbursed
```

Role tersedia: `superadmin`, `laboran`, `asisten`, `kepala_lab`, `dosen`, `mahasiswa`

### Blast ke SEMUA user terdaftar

```bash
php spark notify:test --all --type=loan.submitted
```

> **Hati-hati** — `--all` mengirim ke setiap user di tabel `users`. Gunakan hanya di environment development.

### Mode interaktif (tanpa opsi)

```bash
php spark notify:test
```

Akan muncul prompt untuk memilih target (user / role / all) dan tipe notifikasi secara bertahap.

### Contoh output

```
  ✓ Notifikasi [loan.submitted] dikirim ke user ID 1
  ✓ Notifikasi [bhp.approved] dikirim ke 4 user dalam role 'laboran'
  ✓ Notifikasi [loan.submitted] dikirim ke 12 user
```

---

## Files yang Terlibat

| File | Peran |
|------|-------|
| `app/Database/Migrations/2026-05-31-100000_CreateNotificationsTable.php` | Skema tabel |
| `app/Models/NotificationModel.php` | Query DB |
| `app/Libraries/NotificationService.php` | Logic inti — definisi tipe, interpolasi, kirim |
| `app/Helpers/notification_helper.php` | Fungsi global `send_notification()` & `notify_role()` |
| `app/Controllers/NotificationController.php` | REST endpoints |
| `app/Views/notifications/index.php` | Halaman `/notifications` |
| `app/Views/partials/navbar.php` | Bell icon + dropdown + JS mark-read |
| `app/Views/partials/sidebar.php` | Menu "Notifikasi" + badge |
| `app/Views/layouts/app.php` | Polling badge setiap 30 detik |
