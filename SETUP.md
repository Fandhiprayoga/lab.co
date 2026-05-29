# CodeIgniter 4 + Shield RBAC Boilerplate

Boilerplate project CodeIgniter 4 dengan **CodeIgniter Shield** untuk autentikasi dan **Role-Based Access Control (RBAC)**, menggunakan template dashboard **Stisla**.

## Fitur

- ✅ Autentikasi (Login, Register, Logout) menggunakan CodeIgniter Shield
- ✅ Role-Based Access Control (RBAC) dengan 4 role default
- ✅ Template Dashboard Stisla yang sudah di-slice
- ✅ Manajemen User (CRUD)
- ✅ Manajemen Role & Permission Matrix
- ✅ Profil User
- ✅ Pengaturan Sistem
- ✅ Filter berdasarkan Role dan Permission
- ✅ Dynamic Sidebar berdasarkan permission user

## Roles Default

| Role | Deskripsi |
|------|-----------|
| **Super Admin** | Kontrol penuh terhadap seluruh sistem |
| **Admin** | Administrator harian sistem |
| **Manager** | Melihat laporan dan mengelola data |
| **User** | Pengguna umum dengan akses terbatas |

## Instalasi

### 1. Clone / Copy Project

```bash
cd ci4-app
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Konfigurasi Environment

Copy file `.env` dan sesuaikan konfigurasi database:

```env
database.default.hostname = localhost
database.default.database = ci4_shield_rbac
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

### 4. Buat Database

Buat database MySQL dengan nama `ci4_shield_rbac` (atau sesuai konfigurasi).

### 5. Jalankan Migration

```bash
php spark migrate --all
```

### 6. Jalankan Seeder

```bash
php spark db:seed UserSeeder
```

### 7. Jalankan Server

```bash
php spark serve
```

Akses di browser: `http://localhost:8080`

## Akun Default

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@example.com | password123 |
| Admin | admin@example.com | password123 |
| Manager | manager@example.com | password123 |
| User | user@example.com | password123 |

## Struktur Folder

```
app/
├── Config/
│   ├── Auth.php              # Konfigurasi Shield
│   ├── AuthGroups.php        # Definisi Role, Permission, Matrix
│   ├── Filters.php           # Filter aliases
│   └── Routes.php            # Routing aplikasi
├── Controllers/
│   ├── BaseController.php    # Base controller dengan renderView()
│   ├── AuthController.php    # Override login/register Shield
│   ├── DashboardController.php
│   ├── UserController.php    # CRUD User
│   ├── RoleController.php    # View Roles & Permissions
│   ├── ProfileController.php
│   └── SettingController.php
├── Database/
│   └── Seeds/
│       └── UserSeeder.php    # Seeder user default
├── Filters/
│   ├── RoleFilter.php        # Filter berdasarkan role
│   └── PermissionFilter.php  # Filter berdasarkan permission
└── Views/
    ├── layouts/
    │   ├── app.php           # Layout utama dashboard
    │   └── auth.php          # Layout halaman auth
    ├── partials/
    │   ├── navbar.php        # Navbar dengan user dropdown
    │   ├── sidebar.php       # Sidebar dinamis per permission
    │   └── footer.php        # Footer
    ├── auth/
    │   ├── login.php
    │   └── register.php
    ├── dashboard/
    │   └── index.php
    ├── users/
    │   ├── index.php
    │   ├── create.php
    │   └── edit.php
    ├── roles/
    │   ├── index.php
    │   └── permissions.php
    ├── profile/
    │   └── index.php
    └── settings/
        └── index.php
public/
└── assets/                   # Asset template Stisla
    ├── css/
    ├── js/
    ├── img/
    └── fonts/
```

## Penggunaan RBAC

### Melindungi Route dengan Role

```php
// Hanya superadmin dan admin yang bisa akses
$routes->get('admin/panel', 'Admin::index', ['filter' => 'role:superadmin,admin']);
```

### Melindungi Route dengan Permission

```php
// Hanya yang punya permission users.create
$routes->get('users/create', 'User::create', ['filter' => 'permission:users.create']);
```

### Cek Permission di Controller

```php
$user = auth()->user();

if ($user->can('users.edit')) {
    // boleh edit
}

if ($user->inGroup('superadmin')) {
    // adalah superadmin
}
```

### Cek Permission di View

```php
<?php if (auth()->user()->can('users.create')): ?>
    <a href="/admin/users/create" class="btn btn-primary">Tambah User</a>
<?php endif; ?>
```

### Menambah Role Baru

Edit file `app/Config/AuthGroups.php`:

```php
public array $groups = [
    // ... role existing ...
    'editor' => [
        'title'       => 'Editor',
        'description' => 'Can manage content.',
    ],
];

public array $matrix = [
    // ... matrix existing ...
    'editor' => [
        'content.create',
        'content.edit',
        'content.delete',
    ],
];
```

### Menambah Permission Baru

```php
public array $permissions = [
    // ... permissions existing ...
    'content.create' => 'Dapat membuat konten',
    'content.edit'   => 'Dapat mengedit konten',
    'content.delete' => 'Dapat menghapus konten',
];
```

## Panduan Membuat Menu/Modul Baru

Berikut langkah-langkah lengkap untuk menambah menu/modul baru dengan RBAC. Contoh: membuat modul **Artikel** (`articles`).

### Langkah 1 — Daftarkan Permission di `app/Config/AuthGroups.php`

Tambahkan permission baru di `$permissions`:

```php
public array $permissions = [
    // ... existing ...
    'articles.list'   => 'Dapat melihat daftar artikel',
    'articles.create' => 'Dapat membuat artikel baru',
    'articles.edit'   => 'Dapat mengedit artikel',
    'articles.delete' => 'Dapat menghapus artikel',
];
```

Lalu assign permission ke role yang sesuai di `$matrix`:

```php
public array $matrix = [
    'superadmin' => [
        'admin.*', 'users.*', 'roles.*', 'dashboard.*', 'reports.*',
        'articles.*',   // <-- tambahkan
    ],
    'admin' => [
        'admin.access', 'users.list', 'users.create', 'users.edit', 'users.delete',
        'dashboard.*', 'reports.*',
        'articles.*',   // <-- tambahkan
    ],
    'manager' => [
        'admin.access', 'users.list', 'dashboard.*', 'reports.*',
        'articles.list', // <-- hanya bisa lihat
    ],
    'user' => [
        'dashboard.access',
    ],
];
```

### Langkah 2 — Buat Migration

```bash
php spark make:migration CreateArticlesTable
```

Edit file migration yang dihasilkan di `app/Database/Migrations/`:

```php
public function up()
{
    $this->forge->addField([
        'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
        'title'      => ['type' => 'VARCHAR', 'constraint' => 255],
        'slug'       => ['type' => 'VARCHAR', 'constraint' => 255],
        'content'    => ['type' => 'TEXT', 'null' => true],
        'author_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        'status'     => ['type' => 'ENUM', 'constraint' => ['draft', 'published'], 'default' => 'draft'],
        'created_at' => ['type' => 'DATETIME', 'null' => true],
        'updated_at' => ['type' => 'DATETIME', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->createTable('articles');
}
```

Lalu jalankan:

```bash
php spark migrate
```

### Langkah 3 — Buat Model

Buat file `app/Models/ArticleModel.php`:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class ArticleModel extends Model
{
    protected $table         = 'articles';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['title', 'slug', 'content', 'author_id', 'status'];
    protected $useTimestamps = true;
}
```

### Langkah 4 — Buat Controller

Buat file `app/Controllers/ArticleController.php`:

```php
<?php

namespace App\Controllers;

use App\Models\ArticleModel;

class ArticleController extends BaseController
{
    protected ArticleModel $articleModel;

    public function __construct()
    {
        $this->articleModel = new ArticleModel();
    }

    public function index()
    {
        $data = [
            'title'      => 'Daftar Artikel',
            'page_title' => 'Daftar Artikel',
            'articles'   => $this->articleModel->findAll(),
        ];

        return $this->renderView('articles/index', $data);
    }

    public function create()
    {
        $data = [
            'title'      => 'Tambah Artikel',
            'page_title' => 'Tambah Artikel',
        ];

        return $this->renderView('articles/create', $data);
    }

    public function store()
    {
        // validasi & simpan
    }

    public function edit($id)
    {
        // tampilkan form edit
    }

    public function update($id)
    {
        // validasi & update
    }

    public function delete($id)
    {
        // hapus artikel
    }
}
```

### Langkah 5 — Tambah Route di `app/Config/Routes.php`

Tambahkan di dalam group `admin` yang sudah ada:

```php
$routes->group('admin', ['filter' => 'permission:admin.access'], static function ($routes) {

    // ... route existing ...

    // Artikel
    $routes->group('articles', static function ($routes) {
        $routes->get('/', 'ArticleController::index', ['filter' => 'permission:articles.list']);
        $routes->get('create', 'ArticleController::create', ['filter' => 'permission:articles.create']);
        $routes->post('store', 'ArticleController::store', ['filter' => 'permission:articles.create']);
        $routes->get('edit/(:num)', 'ArticleController::edit/$1', ['filter' => 'permission:articles.edit']);
        $routes->post('update/(:num)', 'ArticleController::update/$1', ['filter' => 'permission:articles.edit']);
        $routes->post('delete/(:num)', 'ArticleController::delete/$1', ['filter' => 'permission:articles.delete']);
    });
});
```

### Langkah 6 — Buat View

Buat file view di `app/Views/articles/index.php`:

```php
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4>Daftar Artikel</h4>
        <div class="card-header-action">
          <?php if (auth()->user()->can('articles.create')): ?>
          <a href="<?= base_url('admin/articles/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Artikel
          </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">
        <!-- isi tabel artikel -->
      </div>
    </div>
  </div>
</div>
```

### Langkah 7 — Tambah Menu di Sidebar

Edit file `app/Views/partials/sidebar.php`, tambahkan di dalam section **Administrasi**:

```php
<?php if ($currentUser->can('articles.list')): ?>
<li class="<?= isMenuActive('admin/articles') ?>">
  <a class="nav-link" href="<?= base_url('admin/articles') ?>">
    <i class="fas fa-newspaper"></i> <span>Artikel</span>
  </a>
</li>
<?php endif; ?>
```

### Ringkasan Checklist

| # | File yang diubah/dibuat | Apa yang ditambah |
|---|-------------------------|-------------------|
| 1 | `app/Config/AuthGroups.php` | Permission baru + assign ke matrix role |
| 2 | `app/Database/Migrations/` | Migration tabel baru |
| 3 | `app/Models/` | Model baru |
| 4 | `app/Controllers/` | Controller baru (extend `BaseController`, pakai `renderView()`) |
| 5 | `app/Config/Routes.php` | Route baru dengan filter permission |
| 6 | `app/Views/` | View files (index, create, edit) |
| 7 | `app/Views/partials/sidebar.php` | Menu baru dibungkus `$currentUser->can()` |

> **Prinsip utama:** Permission didaftarkan dulu → assign ke role di matrix → gunakan filter di route → cek di view untuk tampilkan/sembunyikan elemen UI.

---

## Modul Inventaris Aset Lab

Modul `lab_assets` diperluas menjadi sistem pencatatan inventaris lengkap dengan 4 sub-modul pendukung: **Satuan**, **Mutasi Aset**, **Perawatan Aset**, dan **Dokumen Aset**.

### Tabel & Field Baru

| Tabel | Keterangan |
|-------|------------|
| `units` | Master satuan (Buah/pcs, Unit/unit, Set/set, dst.) |
| `lab_assets` (extended) | +16 kolom inventaris: `asset_code`, `serial_number`, `brand`, `model`, `unit_id`, `acquisition_date`, `acquisition_source`, `purchase_price`, `supplier`, `funding_source`, `warranty_until`, `inventory_status`, `responsible_user_id`, `minimum_stock`, `notes`, `updated_by` |
| `asset_movements` | Log mutasi (in/out/transfer/borrow/return/adjustment/disposal) — immutable |
| `asset_maintenances` | Riwayat perawatan (preventive/corrective/calibration/inspection) |
| `asset_documents` | Lampiran file per aset (invoice/BAST/manual/warranty/photo/other) |

### Migration & Seeder

```bash
php spark migrate
php spark db:seed UnitSeeder
```

Migration file:
- `2026-05-29-000001_CreateUnitsTable`
- `2026-05-29-000002_AddInventoryFieldsToLabAssets`
- `2026-05-29-000003_CreateAssetMovementsTable`
- `2026-05-29-000004_CreateAssetMaintenancesTable`
- `2026-05-29-000005_CreateAssetDocumentsTable`

### Folder Storage (Wajib Dibuat)

Dokumen aset disimpan di luar `public/` agar tidak bisa diakses langsung via URL:

```bash
mkdir -p writable/uploads/asset_documents
chmod -R 775 writable/uploads
```

Struktur runtime:

```
writable/uploads/
├── asset_documents/        # dokumen aset (PDF/DOC/XLS/gambar)
│   └── {asset_id}/         # subfolder per aset (auto-create)
└── index.html              # blocker direct listing
```

> File diakses lewat controller `AssetDocumentController::download($id)` agar permission tetap dicek.

### Permission Baru

Tambahan di `app/Config/AuthGroups.php` (sudah ter-assign ke role `admin`):

| Permission | Akses |
|------------|-------|
| `lending.master.units.manage` | Master Satuan CRUD |
| `lending.master.movements.manage` | Mutasi aset |
| `lending.master.maintenances.manage` | Perawatan aset |
| `lending.master.documents.manage` | Dokumen aset |

### URL Modul

| Path | Modul |
|------|-------|
| `/admin/loans/units` | Master Satuan |
| `/admin/loans/assets` | Master Alat (extended) |
| `/admin/loans/movements` | Mutasi Aset (filter `?asset_id=N`) |
| `/admin/loans/maintenances` | Perawatan Aset (filter `?asset_id=N`) |
| `/admin/loans/documents` | Dokumen Aset (filter `?asset_id=N`) |

### Asset Code Auto-Generate

Format default: `LAB{lab_id}-{KAT3}-{YY}-{seq4}` (contoh: `LAB1-MIK-26-0007`).
Dihasilkan otomatis oleh `LoanAssetController::generateAssetCode()` saat field `asset_code` dikosongkan pada form create/edit. Admin tetap boleh override manual (validasi UNIQUE).

### Auto-Hook Loan Flow → Movement

`LoanController` otomatis menulis ke `asset_movements` dan menyesuaikan `inventory_status`:

| Aksi loan | Movement | inventory_status |
|-----------|----------|------------------|
| `checkout()` (status → `borrowed`) | `borrow` (qty negatif) | `dipinjam` |
| `checkin()` kondisi normal | `return` (qty positif) | `aktif` |
| `checkin()` kondisi `hilang` | `disposal` (qty negatif) | `hilang` |

### Auto-Sync Maintenance → Asset Status

`AssetMaintenanceController::syncAssetStatus()` dipanggil setiap CRUD perawatan:

- Ada record `status='in_progress'` → aset `inventory_status='dalam_perbaikan'` + `is_loanable=0`
- Semua perawatan selesai/batal → aset kembali `inventory_status='aktif'` + `is_loanable=1` (jika kondisi bukan `rusak`)

### Helper Upload Aman

File `app/Helpers/upload_helper.php` menyediakan `handleDocumentUpload($file, $subfolder)`:

```php
helper('upload');
$meta = handleDocumentUpload(
    $this->request->getFile('document_file'),
    'asset_documents/' . $assetId
);
// $meta = ['path' => ..., 'size' => ..., 'mime' => ..., 'original' => ...]
```

> **Bug-fix lesson:** SELALU pakai `UploadedFile::getClientExtension()`, JANGAN `getExtension()`. Yang kedua memanggil `finfo_file()` yang crash di macOS karena temp file sudah dibersihkan sebelum validation rule dievaluasi. Helper ini sudah aman by default.

### Verifikasi End-to-End

1. `/admin/loans/units` → CRUD satuan (default 8 satuan dari seeder).
2. `/admin/loans/assets/create` → biarkan `asset_code` kosong → tersimpan dengan kode auto-generate.
3. `/admin/loans/movements/create` → manual `in` qty 5 → `stock_available` & `stock_total` bertambah 5.
4. Approve + check-out loan → cek `/admin/loans/movements?asset_id=N` muncul record `borrow` otomatis.
5. Check-in loan → muncul record `return`, `inventory_status` kembali ke `aktif`.
6. Buat perawatan `in_progress` → aset jadi `dalam_perbaikan` + `is_loanable=0`. Ubah ke `completed` → kembali normal.
7. Upload PDF di `/admin/loans/documents` → tersimpan di `writable/uploads/asset_documents/{id}/`, bisa di-download lewat tombol. **Tidak ada error `finfo_file`.**

---

## Lisensi

MIT License
