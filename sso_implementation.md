# Rencana Implementasi Login SSO Institusi

**Tanggal rencana**: 30 Mei 2026  
**Status**: Menunggu endpoint SSO dari institusi  
**Teknis SSO**: Custom API (bukan OAuth2/SAML) — POST credential → token → GET header data

---

## Alur Teknis

```
User isi form SSO (username + password)
        ↓
POST $baseUrl/loginEndpoint
Body: { username, password }
        ↓
Terima token (string)
        ↓
GET $baseUrl/profileEndpoint
Header: Authorization: Bearer {token}
        ↓
Terima data header user { username, email, prodi, nim, role, ... }
        ↓ token dibuang (tidak disimpan)
Cocokkan by username ke tabel users lokal
        ↓
[Skenario A / B]
```

---

## Skenario A — Akun sudah ada di lokal

```
Username dari SSO = username lokal → KETEMU
  → sync: update user_profiles (prodi, nim_nik, sso_id, sso_provider)
  → Shield session login
  → redirect /dashboard
```

## Skenario B — Akun belum ada di lokal

```
Username dari SSO ≠ semua username lokal → TIDAK KETEMU
  → Auto create user baru:
      username = dari SSO
      email    = dari SSO
      active   = 1  (langsung aktif, skip email verifikasi)
  → Assign group/role dari mapping $roleMap:
      role SSO "mahasiswa"  → Shield group "mahasiswa"
      role SSO "dosen"      → Shield group "dosen"
      role SSO "karyawan"   → Shield group "laboran"
      tidak dikenali        → Shield group $defaultRole ("mahasiswa")
  → Insert user_profiles (prodi, nim_nik, sso_id, sso_provider)
  → Shield session login
  → redirect /dashboard
```

---

## Files yang Perlu Dibuat / Diubah

### 1. Migration — `app/Database/Migrations/2026-05-30-000002_AddSsoToUserProfiles.php`

Tambah 2 kolom ke tabel `user_profiles`:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `sso_id` | VARCHAR(100) NULL | ID unik user dari respons SSO |
| `sso_provider` | VARCHAR(50) NULL | Nama provider, misal: `'institusi'` |

```php
$this->forge->addColumn('user_profiles', [
    'sso_id' => [
        'type'       => 'VARCHAR',
        'constraint' => 100,
        'null'       => true,
        'default'    => null,
        'after'      => 'phone',
    ],
    'sso_provider' => [
        'type'       => 'VARCHAR',
        'constraint' => 50,
        'null'       => true,
        'default'    => null,
        'after'      => 'sso_id',
    ],
]);
```

---

### 2. Config — `app/Config/Sso.php`

```php
<?php
namespace Config;
use CodeIgniter\Config\BaseConfig;

class Sso extends BaseConfig
{
    /** Aktifkan tombol SSO di login page. Set true jika endpoint sudah siap. */
    public bool $enabled = false;

    public string $buttonLabel = 'Login dengan SSO Institusi';

    // TODO: isi setelah endpoint tersedia
    public string $baseUrl          = '';
    public string $loginEndpoint    = '/auth/login';
    public string $profileEndpoint  = '/user/profile';
    public string $fieldUsername    = 'username';
    public string $fieldPassword    = 'password';

    /**
     * Mapping field respons SSO → field lokal.
     * Key = nama field di JSON respons SSO
     * Value = nama field lokal
     * TODO: sesuaikan setelah melihat struktur respons API
     */
    public array $responseMap = [
        'token'    => 'token',
        'username' => 'username',
        'email'    => 'email',
        'prodi'    => 'prodi',
        'nim'      => 'nim_nik',
        'id'       => 'sso_id',
        'role'     => 'role',
    ];

    /**
     * Mapping role dari SSO → Shield group lokal.
     * Key = nilai role yang dikembalikan SSO API
     * Value = nama group di AuthGroups
     * TODO: sesuaikan setelah dokumentasi API tersedia
     */
    public array $roleMap = [
        'mahasiswa' => 'mahasiswa',
        'dosen'     => 'dosen',
        'karyawan'  => 'laboran',
        'staff'     => 'laboran',
    ];

    /** Role default jika role dari SSO tidak ada di $roleMap */
    public string $defaultRole = 'mahasiswa';

    /** Timeout HTTP request ke SSO API (detik) */
    public int $timeout = 10;

    /** Nama provider (disimpan ke kolom sso_provider) */
    public string $providerName = 'institusi';
}
```

---

### 3. Service — `app/Libraries/SsoService.php`

```php
<?php
namespace App\Libraries;

use Config\Sso;
use CodeIgniter\Shield\Models\UserModel;
use App\Models\UserProfileModel;
use RuntimeException;

class SsoService
{
    protected Sso $config;

    public function __construct()
    {
        $this->config = config('Sso');
    }

    /**
     * Step 1: Kirim credential ke SSO API.
     * @throws RuntimeException
     * @return string $token
     */
    public function authenticate(string $username, string $password): string
    {
        $client = \Config\Services::curlrequest(['timeout' => $this->config->timeout]);

        try {
            $response = $client->post($this->config->baseUrl . $this->config->loginEndpoint, [
                'form_params' => [
                    $this->config->fieldUsername => $username,
                    $this->config->fieldPassword => $password,
                ],
            ]);
        } catch (\Exception $e) {
            throw new RuntimeException('SSO tidak dapat dihubungi. Coba beberapa saat lagi.');
        }

        $body = json_decode($response->getBody(), true);

        $tokenField = $this->config->responseMap['token'] ?? 'token';
        if (empty($body[$tokenField])) {
            throw new RuntimeException('Username atau password SSO tidak valid.');
        }

        return $body[$tokenField];
    }

    /**
     * Step 2: Ambil data header/profil user dari SSO menggunakan token.
     * Token tidak disimpan setelah method ini selesai.
     * @throws RuntimeException
     */
    public function getUserHeader(string $token): array
    {
        $client = \Config\Services::curlrequest(['timeout' => $this->config->timeout]);

        try {
            $response = $client->get($this->config->baseUrl . $this->config->profileEndpoint, [
                'headers' => ['Authorization' => 'Bearer ' . $token],
            ]);
        } catch (\Exception $e) {
            throw new RuntimeException('Gagal mengambil data profil dari SSO.');
        }

        $data = json_decode($response->getBody(), true);
        if (empty($data)) {
            throw new RuntimeException('Respons profil SSO tidak valid.');
        }

        // Normalisasi field sesuai responseMap
        $map = $this->config->responseMap;
        return [
            'username'     => $data[$map['username'] ?? 'username'] ?? '',
            'email'        => $data[$map['email']    ?? 'email']    ?? '',
            'prodi'        => $data[$map['prodi']    ?? 'prodi']    ?? null,
            'nim_nik'      => $data[$map['nim']      ?? 'nim']      ?? null,
            'sso_id'       => $data[$map['id']       ?? 'id']       ?? null,
            'role'         => $data[$map['role']      ?? 'role']     ?? null,
        ];
    }

    /**
     * Step 3: Cocokkan atau buat akun lokal berdasarkan username SSO.
     * Skenario A: username ada → sync profil
     * Skenario B: username tidak ada → auto register + auto aktif + assign role
     */
    public function loginOrRegister(array $ssoData): \CodeIgniter\Shield\Entities\User
    {
        $users        = auth()->getProvider();
        $profileModel = new UserProfileModel();

        // Cari user lokal by username
        $user = $users->findByCredentials(['username' => $ssoData['username']]);

        if ($user === null) {
            // --- Skenario B: auto register ---
            $user = new \CodeIgniter\Shield\Entities\User([
                'username' => $ssoData['username'],
                'active'   => 1,
            ]);
            $user->createEmailIdentity([
                'email'    => $ssoData['email'],
                'password' => bin2hex(random_bytes(16)), // password acak, user tidak tahu
            ]);
            $users->save($user);
            $user = $users->findByCredentials(['username' => $ssoData['username']]);

            // Assign role dari SSO
            $role       = $ssoData['role'] ?? null;
            $roleMap    = $this->config->roleMap;
            $shieldGroup = $roleMap[$role] ?? $this->config->defaultRole;
            $user->addGroup($shieldGroup);
        }

        // Sync user_profiles (Skenario A dan B)
        $profileModel->upsert((int) $user->id, [
            'prodi'        => $ssoData['prodi']   ?? null,
            'nim_nik'      => $ssoData['nim_nik']  ?? null,
            'sso_id'       => $ssoData['sso_id']   ?? null,
            'sso_provider' => config('Sso')->providerName,
        ]);

        return $user;
    }
}
```

---

### 4. Controller — `app/Controllers/SsoController.php`

```php
<?php
namespace App\Controllers;

use App\Libraries\SsoService;

class SsoController extends BaseController
{
    public function login()
    {
        if (! config('Sso')->enabled) {
            return redirect()->to('/login')->with('error', 'Login SSO belum tersedia.');
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[100]',
            'password' => 'required|min_length[3]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to('/login')
                ->withInput()
                ->with('sso_errors', $this->validator->getErrors());
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        try {
            $sso   = new SsoService();
            $token = $sso->authenticate($username, $password);
            $data  = $sso->getUserHeader($token);
            $user  = $sso->loginOrRegister($data);

            auth()->login($user);

            return redirect()->to('/dashboard');

        } catch (\RuntimeException $e) {
            return redirect()->to('/login')
                ->with('sso_error', $e->getMessage());
        }
    }
}
```

---

### 5. Route — `app/Config/Routes.php`

Tambahkan di bagian **Public Routes** (sebelum `session` filter group):

```php
// SSO Login
$routes->post('auth/sso-login', 'SsoController::login');
```

---

### 6. View — `app/Views/auth/login.php`

Tambahkan di bawah form login utama (sebelum `</div>` card-body):

```php
<?php if (config('Sso')->enabled): ?>
  <div class="text-center text-muted mt-4 mb-2">— atau —</div>

  <?php if (session('sso_error')): ?>
    <div class="alert alert-danger"><?= session('sso_error') ?></div>
  <?php endif ?>

  <form method="POST" action="<?= base_url('auth/sso-login') ?>">
    <?= csrf_field() ?>
    <div class="form-group">
      <label>Username SSO</label>
      <input type="text" name="username" class="form-control"
             value="<?= old('username') ?>" placeholder="Username akun institusi" required>
    </div>
    <div class="form-group">
      <label>Password SSO</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-outline-primary btn-block">
      <i class="fas fa-university mr-1"></i> <?= esc(config('Sso')->buttonLabel) ?>
    </button>
  </form>
<?php endif ?>
```

---

## Cara Aktivasi (ketika endpoint tersedia)

Edit `app/Config/Sso.php`:
```php
public bool   $enabled         = true;                           // 1. nyalakan
public string $baseUrl         = 'https://sso.kampus.ac.id/api'; // 2. isi URL
public string $loginEndpoint   = '/auth/login';                  // 3. sesuaikan path
public string $profileEndpoint = '/user/profile';                // 4. sesuaikan path
```

Lalu sesuaikan `$responseMap` dan `$roleMap` sesuai dokumentasi API institusi.

Jalankan migration:
```bash
php spark migrate
```

---

## Checklist Implementasi

- [ ] Buat migration `2026-05-30-000002_AddSsoToUserProfiles.php`
- [ ] Buat `app/Config/Sso.php`
- [ ] Buat `app/Libraries/SsoService.php`
- [ ] Buat `app/Controllers/SsoController.php`
- [ ] Update `app/Config/Routes.php` — tambah route POST `auth/sso-login`
- [ ] Update `app/Views/auth/login.php` — tambah form SSO
- [ ] Jalankan `php spark migrate`
- [ ] Test login lokal masih normal
- [ ] (Nanti) Isi endpoint di `Sso.php` → test Skenario A & B
