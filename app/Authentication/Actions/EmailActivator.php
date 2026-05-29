<?php

namespace App\Authentication\Actions;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Actions\EmailActivator as ShieldEmailActivator;
use CodeIgniter\Shield\Exceptions\RuntimeException;

/**
 * Custom EmailActivator yang menangani kegagalan kirim email
 * secara graceful (tidak crash), dan menampilkan pesan error
 * yang ramah kepada pengguna.
 */
class EmailActivator extends ShieldEmailActivator
{
    /**
     * Override show() untuk menangkap exception saat email gagal dikirim,
     * sehingga tidak menghasilkan halaman error 500.
     */
    public function show(): string
    {
        try {
            return parent::show();
        } catch (RuntimeException $e) {
            // Email gagal terkirim — tampilkan pesan error ramah
            // tanpa crash halaman
            $authenticator = auth('session')->getAuthenticator();
            $user          = $authenticator->getPendingUser();

            log_message('error', '[EmailActivator] Gagal kirim email aktivasi ke {email}: {msg}', [
                'email' => $user?->email ?? 'unknown',
                'msg'   => $e->getMessage(),
            ]);

            session()->setFlashdata('activation_error',
                'Email aktivasi gagal dikirim ke <strong>' . esc($user?->email ?? '') . '</strong>. '
                . 'Periksa konfigurasi SMTP di pengaturan admin, atau gunakan tombol Kirim Ulang di bawah.'
            );

            return $this->view(
                setting('Auth.views')['action_email_activate_show'],
                ['user' => $user]
            );
        }
    }
}
