<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Controllers\RegisterController as ShieldRegisterController;

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
}
