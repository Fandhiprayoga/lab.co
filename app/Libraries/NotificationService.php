<?php

namespace App\Libraries;

use App\Models\NotificationModel;

/**
 * NotificationService
 *
 * Layanan terpusat untuk membuat dan mengirim notifikasi in-app.
 *
 * Penggunaan:
 *   (new NotificationService())->sendToUser($userId, 'loan.submitted', ['proposal_code' => 'PROP-XYZ']);
 *   (new NotificationService())->sendToRole('laboran', 'loan.submitted', ['proposal_code' => 'PROP-XYZ', 'url' => '/loans/5']);
 *
 * Atau lewat helper:
 *   send_notification($userId, 'loan.submitted', [...]);
 *   notify_role('laboran', 'loan.submitted', [...]);
 */
class NotificationService
{
    /**
     * Definisi setiap tipe notifikasi.
     *
     * Key   : tipe (digunakan sebagai identifier)
     * title : template judul (supports {placeholder})
     * message : template pesan
     * icon  : Font Awesome class
     * color : Bootstrap contextual color (primary, success, warning, danger, info, secondary)
     */
    public const TYPES = [
        // ---- Loan / Peminjaman ----
        'loan.submitted' => [
            'title'   => 'Proposal Baru Masuk',
            'message' => 'Proposal {proposal_code} memerlukan persetujuan Anda.',
            'icon'    => 'fas fa-file-alt',
            'color'   => 'primary',
        ],
        'loan.approved_l1' => [
            'title'   => 'Proposal Disetujui (L1)',
            'message' => 'Proposal {proposal_code} telah disetujui Laboran dan menunggu persetujuan Kepala Lab.',
            'icon'    => 'fas fa-check-circle',
            'color'   => 'info',
        ],
        'loan.approved_l2' => [
            'title'   => 'Proposal Disetujui',
            'message' => 'Proposal {proposal_code} Anda telah disetujui. Silakan lakukan checkout sesuai jadwal.',
            'icon'    => 'fas fa-check-double',
            'color'   => 'success',
        ],
        'loan.rejected_l1' => [
            'title'   => 'Proposal Ditolak',
            'message' => 'Proposal {proposal_code} Anda ditolak oleh Laboran.',
            'icon'    => 'fas fa-times-circle',
            'color'   => 'danger',
        ],
        'loan.rejected_l2' => [
            'title'   => 'Proposal Ditolak',
            'message' => 'Proposal {proposal_code} Anda ditolak oleh Kepala Lab.',
            'icon'    => 'fas fa-times-circle',
            'color'   => 'danger',
        ],
        'loan.checked_out' => [
            'title'   => 'Checkout Berhasil',
            'message' => 'Proposal {proposal_code} telah di-checkout. Harap kembalikan tepat waktu.',
            'icon'    => 'fas fa-sign-out-alt',
            'color'   => 'warning',
        ],
        'loan.checked_in' => [
            'title'   => 'Pengembalian Dicatat',
            'message' => 'Pengembalian untuk proposal {proposal_code} telah berhasil dicatat.',
            'icon'    => 'fas fa-sign-in-alt',
            'color'   => 'success',
        ],
        'loan.usage_started' => [
            'title'   => 'Penggunaan Ruangan Dimulai',
            'message' => 'Penggunaan ruangan untuk proposal {proposal_code} telah dimulai.',
            'icon'    => 'fas fa-play-circle',
            'color'   => 'primary',
        ],
        'loan.usage_finished' => [
            'title'   => 'Penggunaan Ruangan Selesai',
            'message' => 'Penggunaan ruangan untuk proposal {proposal_code} telah selesai.',
            'icon'    => 'fas fa-flag-checkered',
            'color'   => 'success',
        ],
        'loan.canceled' => [
            'title'   => 'Proposal Dibatalkan',
            'message' => 'Proposal {proposal_code} telah dibatalkan.',
            'icon'    => 'fas fa-ban',
            'color'   => 'secondary',
        ],

        // ---- BHP / Bahan Habis Pakai ----
        'bhp.submitted' => [
            'title'   => 'Permintaan BHP Baru',
            'message' => 'Permintaan BHP {request_code} memerlukan persetujuan Anda.',
            'icon'    => 'fas fa-flask',
            'color'   => 'primary',
        ],
        'bhp.approved' => [
            'title'   => 'Permintaan BHP Disetujui',
            'message' => 'Permintaan BHP {request_code} Anda telah disetujui.',
            'icon'    => 'fas fa-check-circle',
            'color'   => 'success',
        ],
        'bhp.rejected' => [
            'title'   => 'Permintaan BHP Ditolak',
            'message' => 'Permintaan BHP {request_code} Anda ditolak.',
            'icon'    => 'fas fa-times-circle',
            'color'   => 'danger',
        ],
        'bhp.disbursed' => [
            'title'   => 'BHP Siap Diambil',
            'message' => 'BHP untuk permintaan {request_code} telah disiapkan. Silakan ambil ke laboran.',
            'icon'    => 'fas fa-box-open',
            'color'   => 'info',
        ],
        'bhp.realized' => [
            'title'   => 'BHP Telah Direalisasi',
            'message' => 'Realisasi BHP untuk permintaan {request_code} telah selesai dicatat.',
            'icon'    => 'fas fa-clipboard-check',
            'color'   => 'success',
        ],

        // ---- Kunjungan / Visit ----
        'visit.force_checkout' => [
            'title'   => 'Force Checkout Kunjungan',
            'message' => 'Pengunjung {visitor_name} di {lab_name} telah di-force checkout oleh admin.',
            'icon'    => 'fas fa-door-open',
            'color'   => 'warning',
        ],

        // ---- Surat Bebas Lab / Clearance ----
        'clearance.submitted' => [
            'title'   => 'Pengajuan Surat Bebas Lab',
            'message' => 'Pengajuan surat bebas lab {request_code} memerlukan verifikasi Anda.',
            'icon'    => 'fas fa-file-signature',
            'color'   => 'primary',
        ],
        'clearance.approved' => [
            'title'   => 'Surat Bebas Lab Terbit',
            'message' => 'Surat bebas lab {request_code} (No. {letter_number}) telah terbit. Silakan unduh suratnya.',
            'icon'    => 'fas fa-check-circle',
            'color'   => 'success',
        ],
        'clearance.rejected' => [
            'title'   => 'Pengajuan Surat Bebas Lab Ditolak',
            'message' => 'Pengajuan surat bebas lab {request_code} Anda ditolak. Alasan: {reason}',
            'icon'    => 'fas fa-times-circle',
            'color'   => 'danger',
        ],

        // ---- Penetapan Peran ----
        'role.kepala_lab_assigned' => [
            'title'   => 'Anda Ditetapkan sebagai Kepala Lab',
            'message' => 'Anda kini menjabat sebagai Kepala Lab dan bertanggung jawab atas seluruh lab.',
            'icon'    => 'fas fa-user-tie',
            'color'   => 'success',
        ],
        'lab.pic_assigned' => [
            'title'   => 'Anda Ditetapkan sebagai PIC Lab',
            'message' => 'Anda ditetapkan sebagai PIC Laboran penanggung jawab lab {lab_name}.',
            'icon'    => 'fas fa-user-cog',
            'color'   => 'info',
        ],
    ];

    protected NotificationModel $model;

    public function __construct()
    {
        $this->model = new NotificationModel();
    }

    /**
     * Kirim notifikasi ke satu user.
     *
     * @param int    $userId  ID user penerima (dari tabel users Shield)
     * @param string $type    Tipe notifikasi — gunakan konstanta TYPES
     * @param array  $context Placeholder untuk interpolasi pesan, plus key opsional 'url'
     */
    public function sendToUser(int $userId, string $type, array $context = []): void
    {
        $def = self::TYPES[$type] ?? null;

        if ($def === null) {
            log_message('warning', "NotificationService: unknown type '{$type}'");
            return;
        }

        $title   = $this->interpolate($def['title'], $context);
        $message = $this->interpolate($def['message'], $context);

        // Metadata yang disimpan ke kolom JSON 'data'
        $data = [
            'icon'  => $def['icon'],
            'color' => $def['color'],
            'url'   => $context['url'] ?? null,
        ];

        // Tambahkan reference_id jika ada
        if (isset($context['reference_id'])) {
            $data['reference_id'] = $context['reference_id'];
        }

        $this->model->insert([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'data'    => json_encode($data),
            'read_at' => null,
        ]);

        // Auto-trim: jaga agar tidak melebihi 100 notif per user
        $this->model->trimOld($userId, 100);
    }

    /**
     * Kirim notifikasi ke semua user yang memiliki role tertentu.
     *
     * @param string $role    Nama role (e.g. 'laboran', 'kepala_lab', 'superadmin')
     * @param string $type    Tipe notifikasi
     * @param array  $context Placeholder + 'url'
     */
    public function sendToRole(string $role, string $type, array $context = []): void
    {
        $db = \Config\Database::connect();

        // Ambil semua user_id yang aktif dan memiliki role tersebut
        // Shield menyimpan role di tabel auth_groups_users
        $userIds = $db->table('auth_groups_users')
                      ->select('user_id')
                      ->where('group', $role)
                      ->get()
                      ->getResultArray();

        foreach ($userIds as $row) {
            $this->sendToUser((int) $row['user_id'], $type, $context);
        }
    }

    /**
     * Ganti placeholder {key} dalam string template dengan nilai dari context.
     */
    private function interpolate(string $template, array $context): string
    {
        $search  = [];
        $replace = [];

        foreach ($context as $key => $value) {
            if (is_scalar($value)) {
                $search[]  = '{' . $key . '}';
                $replace[] = (string) $value;
            }
        }

        return str_replace($search, $replace, $template);
    }
}
