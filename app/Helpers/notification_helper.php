<?php

use App\Libraries\NotificationService;

if (! function_exists('send_notification')) {
    /**
     * Kirim notifikasi ke satu user.
     *
     * @param int    $userId  ID user penerima
     * @param string $type    Tipe notifikasi (lihat NotificationService::TYPES)
     * @param array  $context Placeholder untuk template pesan + key opsional 'url'
     */
    function send_notification(int $userId, string $type, array $context = []): void
    {
        (new NotificationService())->sendToUser($userId, $type, $context);
    }
}

if (! function_exists('notify_role')) {
    /**
     * Kirim notifikasi ke semua user yang memiliki role tertentu.
     *
     * @param string $role    Nama role (e.g. 'laboran', 'kepala_lab')
     * @param string $type    Tipe notifikasi (lihat NotificationService::TYPES)
     * @param array  $context Placeholder untuk template pesan + key opsional 'url'
     */
    function notify_role(string $role, string $type, array $context = []): void
    {
        (new NotificationService())->sendToRole($role, $type, $context);
    }
}
