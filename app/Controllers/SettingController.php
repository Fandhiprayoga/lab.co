<?php

namespace App\Controllers;

class SettingController extends BaseController
{
    /**
     * Default setting values
     */
    private array $defaults = [
        'App.siteName'        => 'CI4 Shield RBAC',
        'App.siteNameShort'   => 'C4',
        'App.siteDescription' => 'Boilerplate CodeIgniter 4 dengan Shield RBAC',
        'App.siteFooter'      => 'CI4 Shield RBAC Boilerplate',
        'App.siteVersion'     => '1.0.0',
        'App.siteLogo'        => '',
        'App.siteFavicon'     => '',
        'App.maintenanceMode' => '0',
        'App.maintenanceMsg'  => 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.',
        'App.defaultRole'     => 'user',
        'Auth.allowRegistration' => true,
        'Mail.protocol'       => 'smtp',
        'Mail.hostname'       => '',
        'Mail.port'           => '587',
        'Mail.username'       => '',
        'Mail.password'       => '',
        'Mail.encryption'     => 'tls',
        'Mail.fromEmail'      => 'noreply@example.com',
        'Mail.fromName'       => 'CI4 RBAC',
        'App.navbarBg'        => '#6777ef',
        'App.sidebarActive'   => '#6777ef',
    ];

    /**
     * Default file paths for branding assets
     */
    private string $defaultLogo    = 'assets/img/stisla-fill.svg';
    private string $defaultFavicon = 'assets/img/stisla-fill.svg';

    /**
     * Halaman pengaturan — tab-based
     */
    public function index()
    {
        $activeTab = $this->request->getGet('tab') ?? 'general';

        $authGroups = config('AuthGroups');

        $data = [
            'title'      => 'Pengaturan',
            'page_title' => 'Pengaturan Sistem',
            'activeTab'  => $activeTab,
            'groups'     => $authGroups->groups,
            'settings'   => $this->getAllSettings(),
        ];

        return $this->renderView('settings/index', $data);
    }

    /**
     * Update pengaturan umum
     */
    public function updateGeneral()
    {
        $rules = [
            'site_name'        => 'required|max_length[100]',
            'site_name_short'  => 'permit_empty|max_length[10]',
            'site_description' => 'permit_empty|max_length[255]',
            'site_footer'      => 'permit_empty|max_length[100]',
            'site_version'     => 'permit_empty|max_length[20]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        setting('App.siteName', $this->request->getPost('site_name'));
        setting('App.siteNameShort', $this->request->getPost('site_name_short'));
        setting('App.siteDescription', $this->request->getPost('site_description'));
        setting('App.siteFooter', $this->request->getPost('site_footer'));
        setting('App.siteVersion', $this->request->getPost('site_version'));

        return redirect()->to('/admin/settings?tab=general')->with('success', 'Pengaturan umum berhasil diperbarui.');
    }

    /**
     * Update pengaturan autentikasi
     */
    public function updateAuth()
    {
        $rules = [
            'default_role'       => 'required',
            'allow_registration' => 'permit_empty',
            'maintenance_mode'   => 'permit_empty',
            'maintenance_msg'    => 'permit_empty|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        setting('App.defaultRole', $this->request->getPost('default_role'));
        setting('Auth.allowRegistration', $this->request->getPost('allow_registration') ? true : false);
        setting('App.maintenanceMode', $this->request->getPost('maintenance_mode') ? '1' : '0');
        setting('App.maintenanceMsg', $this->request->getPost('maintenance_msg') ?? '');

        return redirect()->to('/admin/settings?tab=auth')->with('success', 'Pengaturan autentikasi berhasil diperbarui.');
    }

    /**
     * Update pengaturan email
     */
    public function updateMail()
    {
        $rules = [
            'mail_protocol'   => 'required|in_list[smtp,sendmail,mail]',
            'mail_hostname'   => 'permit_empty|max_length[255]',
            'mail_port'       => 'permit_empty|numeric',
            'mail_username'   => 'permit_empty|max_length[255]',
            'mail_password'   => 'permit_empty|max_length[255]',
            'mail_encryption' => 'required|in_list[tls,ssl,none]',
            'mail_from_email' => 'permit_empty|valid_email',
            'mail_from_name'  => 'permit_empty|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        setting('Mail.protocol', $this->request->getPost('mail_protocol'));
        setting('Mail.hostname', $this->request->getPost('mail_hostname') ?? '');
        setting('Mail.port', $this->request->getPost('mail_port') ?? '587');
        setting('Mail.username', $this->request->getPost('mail_username') ?? '');
        setting('Mail.encryption', $this->request->getPost('mail_encryption'));
        setting('Mail.fromEmail', $this->request->getPost('mail_from_email') ?? '');
        setting('Mail.fromName', $this->request->getPost('mail_from_name') ?? '');

        // Password hanya di-update jika diisi
        $password = $this->request->getPost('mail_password');
        if (! empty($password)) {
            setting('Mail.password', $password);
        }

        return redirect()->to('/admin/settings?tab=mail')->with('success', 'Pengaturan email berhasil diperbarui.');
    }

    /**
     * Update branding (logo & favicon)
     */
    public function updateBranding()
    {
        $uploadPath = FCPATH . 'uploads/branding';

        // Pastikan direktori ada
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $logo    = $this->request->getFile('site_logo');
        $favicon = $this->request->getFile('site_favicon');

        // Upload Logo
        if ($logo && $logo->isValid() && ! $logo->hasMoved()) {
            $validLogo = $this->validate([
                'site_logo' => 'uploaded[site_logo]|max_size[site_logo,2048]|is_image[site_logo]|mime_in[site_logo,image/png,image/jpeg,image/svg+xml,image/webp]',
            ]);

            if (! $validLogo) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Hapus file lama jika ada
            $oldLogo = setting('App.siteLogo');
            if (! empty($oldLogo) && file_exists(FCPATH . $oldLogo)) {
                unlink(FCPATH . $oldLogo);
            }

            $logoName = 'logo_' . time() . '.' . $logo->getExtension();
            $logo->move($uploadPath, $logoName);
            setting('App.siteLogo', 'uploads/branding/' . $logoName);
        }

        // Upload Favicon
        if ($favicon && $favicon->isValid() && ! $favicon->hasMoved()) {
            $validFav = $this->validate([
                'site_favicon' => 'uploaded[site_favicon]|max_size[site_favicon,1024]|is_image[site_favicon]|mime_in[site_favicon,image/png,image/x-icon,image/svg+xml,image/vnd.microsoft.icon,image/webp]',
            ]);

            if (! $validFav) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            // Hapus file lama jika ada
            $oldFavicon = setting('App.siteFavicon');
            if (! empty($oldFavicon) && file_exists(FCPATH . $oldFavicon)) {
                unlink(FCPATH . $oldFavicon);
            }

            $favName = 'favicon_' . time() . '.' . $favicon->getExtension();
            $favicon->move($uploadPath, $favName);
            setting('App.siteFavicon', 'uploads/branding/' . $favName);
        }

        return redirect()->to('/admin/settings?tab=general')->with('success', 'Branding berhasil diperbarui.');
    }

    /**
     * Update pengaturan tampilan (warna navbar & sidebar)
     */
    public function updateAppearance()
    {
        $rules = [
            'navbar_bg'      => 'required|max_length[7]|regex_match[/^#[0-9A-Fa-f]{6}$/]',
            'sidebar_active' => 'required|max_length[7]|regex_match[/^#[0-9A-Fa-f]{6}$/]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        setting('App.navbarBg', $this->request->getPost('navbar_bg'));
        setting('App.sidebarActive', $this->request->getPost('sidebar_active'));

        return redirect()->to('/admin/settings?tab=appearance')->with('success', 'Pengaturan tampilan berhasil diperbarui.');
    }

    /**
     * Reset semua pengaturan ke default
     */
    public function resetDefaults()
    {
        $tab = $this->request->getPost('tab') ?? 'general';

        // Tentukan key mana yang di-reset berdasarkan tab
        $keysToReset = match ($tab) {
            'general' => ['App.siteName', 'App.siteNameShort', 'App.siteDescription', 'App.siteFooter', 'App.siteVersion', 'App.siteLogo', 'App.siteFavicon'],
            'auth'    => ['App.defaultRole', 'Auth.allowRegistration', 'App.maintenanceMode', 'App.maintenanceMsg'],
            'mail'       => ['Mail.protocol', 'Mail.hostname', 'Mail.port', 'Mail.username', 'Mail.password', 'Mail.encryption', 'Mail.fromEmail', 'Mail.fromName'],
            'appearance' => ['App.navbarBg', 'App.sidebarActive'],
            default      => array_keys($this->defaults),
        };

        // Hapus file branding jika reset general
        if ($tab === 'general') {
            $oldLogo = setting('App.siteLogo');
            if (! empty($oldLogo) && file_exists(FCPATH . $oldLogo)) {
                unlink(FCPATH . $oldLogo);
            }
            $oldFavicon = setting('App.siteFavicon');
            if (! empty($oldFavicon) && file_exists(FCPATH . $oldFavicon)) {
                unlink(FCPATH . $oldFavicon);
            }
        }

        // Hapus setting dari DB sehingga kembali ke default config
        foreach ($keysToReset as $key) {
            setting()->forget($key);
        }

        return redirect()->to('/admin/settings?tab=' . $tab)->with('success', 'Pengaturan berhasil direset ke default.');
    }

    /**
     * Ambil semua settings, gunakan default jika belum ada di DB
     */
    private function getAllSettings(): array
    {
        $result = [];

        foreach ($this->defaults as $key => $default) {
            $value = setting($key);
            $result[$key] = $value ?? $default;
        }

        return $result;
    }
}
