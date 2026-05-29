<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Aktivasi Akun</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #6777ef; padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Aktivasi Akun LabCorner</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 40px 20px 40px;">
                            <p style="color: #555; font-size: 16px; margin: 0 0 16px 0;">
                                Halo <strong><?= esc($user->username) ?></strong>,
                            </p>
                            <p style="color: #555; font-size: 15px; margin: 0 0 24px 0;">
                                Terima kasih telah mendaftar di <strong>LabCorner</strong>. 
                                Gunakan kode aktivasi berikut untuk mengaktifkan akun Anda:
                            </p>

                            <!-- OTP Code Box -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 20px 0;">
                                        <div style="display: inline-block; background-color: #f0f2ff; border: 2px dashed #6777ef; border-radius: 8px; padding: 20px 40px;">
                                            <span style="font-size: 42px; font-weight: bold; color: #6777ef; letter-spacing: 10px;"><?= esc($code) ?></span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #888; font-size: 13px; text-align: center; margin: 0 0 24px 0;">
                                Kode ini hanya berlaku untuk satu kali penggunaan.
                            </p>

                            <p style="color: #555; font-size: 14px; margin: 0 0 8px 0;">
                                Jika Anda tidak mendaftar di LabCorner, abaikan email ini.
                            </p>
                        </td>
                    </tr>

                    <!-- Device Info -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px;">
                            <table width="100%" cellpadding="8" cellspacing="0" style="background-color: #f9f9f9; border-radius: 6px; font-size: 13px; color: #777;">
                                <tr>
                                    <td><strong>Username</strong></td>
                                    <td><?= esc($user->username) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>IP Address</strong></td>
                                    <td><?= esc($ipAddress) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Perangkat</strong></td>
                                    <td><?= esc($userAgent) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Waktu</strong></td>
                                    <td><?= esc($date) ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f0f0f0; padding: 20px; text-align: center;">
                            <p style="color: #aaa; font-size: 12px; margin: 0;">
                                &copy; <?= date('Y') ?> LabCorner &mdash; Sistem Manajemen Laboratorium
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
