<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Controllers\RegisterController as ShieldRegisterController;
use CodeIgniter\Shield\Models\UserIdentityModel;

class RegisterController extends ShieldRegisterController
{
    /**
     * Attempts to register the user with email domain restriction.
     */
    public function registerAction(): RedirectResponse
    {
        if (auth()->loggedIn()) {
            return redirect()->to(config('Auth')->registerRedirect());
        }

        // Check if registration is allowed
        if (! setting('Auth.allowRegistration')) {
            return redirect()->back()->withInput()
                ->with('error', lang('Auth.registerDisabled'));
        }

        // Validate allowed email domains
        $allowedDomains = setting('App.allowedEmailDomains');
        if (! empty($allowedDomains)) {
            $domains = array_filter(array_map('trim', explode(',', (string) $allowedDomains)));

            if (! empty($domains)) {
                $email       = strtolower(trim((string) $this->request->getPost('email')));
                $atPos       = strrpos($email, '@');
                $emailDomain = $atPos !== false ? substr($email, $atPos + 1) : '';

                $allowed = false;
                foreach ($domains as $domain) {
                    if (strtolower(trim($domain)) === $emailDomain) {
                        $allowed = true;
                        break;
                    }
                }

                if (! $allowed) {
                    $domainList = implode(', @', array_map('trim', $domains));
                    return redirect()->back()->withInput()
                        ->with('errors', ['Hanya email institusi yang diizinkan mendaftar. Gunakan email dengan domain: @' . $domainList]);
                }
            }
        }

        return parent::registerAction();
    }

    /**
     * Membatalkan proses aktivasi yang sedang pending.
     * Menghapus auth_action dari sesi dan identity email_activate dari DB,
     * lalu mengarahkan kembali ke halaman registrasi.
     */
    public function cancelActivation(): RedirectResponse
    {
        /** @var \CodeIgniter\Shield\Authentication\Authenticators\Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();
        $user          = $authenticator->getPendingUser();

        // Hapus identity email_activate dari database jika ada
        if ($user !== null) {
            /** @var UserIdentityModel $identityModel */
            $identityModel = model(UserIdentityModel::class);
            $identityModel->deleteIdentitiesByType($user, 'email_activate');
        }

        // Hapus auth_action dari sesi agar tidak stuck
        session()->remove('auth_action');
        session()->remove('auth_action_message');

        // Logout user yang pending
        auth()->logout();

        return redirect()->to(url_to('register'))
            ->with('message', 'Pendaftaran dibatalkan. Silakan daftar ulang dengan email yang valid.');
    }
}
