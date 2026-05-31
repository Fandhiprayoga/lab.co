<?php

namespace App\Commands;

use App\Libraries\NotificationService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * Command CLI untuk testing notifikasi.
 *
 * Penggunaan:
 *   php spark notify:test                            → mode interaktif
 *   php spark notify:test --user=1                   → kirim ke user ID 1
 *   php spark notify:test --role=laboran             → blast ke semua laboran
 *   php spark notify:test --all                      → blast ke SEMUA user
 *   php spark notify:test --user=1 --type=bhp.approved
 *   php spark notify:test --list                     → tampilkan semua tipe tersedia
 */
class NotifyTest extends BaseCommand
{
    protected $group       = 'Notification';
    protected $name        = 'notify:test';
    protected $description = 'Kirim notifikasi test — ke user tertentu, role, atau semua user.';
    protected $usage       = 'notify:test [options]';
    protected $arguments   = [];
    protected $options     = [
        '--user'  => 'ID user penerima (integer). Contoh: --user=1',
        '--role'  => 'Nama role penerima. Contoh: --role=laboran',
        '--all'   => 'Kirim ke semua user terdaftar (flag tanpa nilai)',
        '--type'  => 'Tipe notifikasi. Default: loan.submitted. Lihat --list untuk semua tipe.',
        '--url'   => 'URL tujuan saat notif diklik. Opsional.',
        '--list'  => 'Tampilkan semua tipe notifikasi yang tersedia (flag tanpa nilai)',
    ];

    /** Default context placeholders untuk keperluan testing */
    private const TEST_CONTEXT = [
        'proposal_code'  => 'PROP-TEST-000',
        'request_code'   => 'BHP-TEST-000',
        'visitor_name'   => 'Budi Santoso (Test)',
        'lab_name'       => 'Lab Komputer',
        'asset_name'     => 'Mikroskop Binokuler XYZ',
        'scheduled_date' => '2026-06-01',
    ];

    public function run(array $params): void
    {
        $service = new NotificationService();
        $types   = NotificationService::TYPES;

        // CI4 CLI tidak memisahkan --key=value; normalkan terlebih dahulu
        $opts = $this->normalizeOptions();

        // ── --list : tampilkan semua tipe lalu keluar ────────────────────────
        if (isset($opts['list'])) {
            $this->showTypeList($types);
            return;
        }

        $userId  = $opts['user'] ?? null;
        $role    = $opts['role'] ?? null;
        $sendAll = isset($opts['all']);

        // ── Mode interaktif jika tidak ada opsi target ──────────────────────
        if ($userId === null && $role === null && ! $sendAll) {
            $this->runInteractive($service, $types);
            return;
        }

        // ── Validasi type ────────────────────────────────────────────────────
        $type = $opts['type'] ?? 'loan.submitted';
        if (! array_key_exists($type, $types)) {
            CLI::error("Tipe '{$type}' tidak dikenal.");
            $this->showTypeList($types);
            return;
        }

        $urlOverride = $opts['url'] ?? null;
        $context     = $this->buildContext($urlOverride);

        // ── Eksekusi berdasarkan target ──────────────────────────────────────
        if ($userId !== null) {
            $userId = (int) $userId;
            $service->sendToUser($userId, $type, $context);
            CLI::write(CLI::color('  ✓ ', 'green') . "Notifikasi [{$type}] dikirim ke user ID {$userId}");
            return;
        }

        if ($role !== null) {
            $count = $this->countUsersInRole((string) $role);
            $service->sendToRole((string) $role, $type, $context);
            CLI::write(CLI::color('  ✓ ', 'green') . "Notifikasi [{$type}] dikirim ke {$count} user dalam role '{$role}'");
            return;
        }

        if ($sendAll) {
            $count = $this->blastToAll($service, $type, $context);
            CLI::write(CLI::color('  ✓ ', 'green') . "Notifikasi [{$type}] dikirim ke {$count} user");
        }
    }

    /**
     * CI4 < 4.5 tidak memisahkan --key=value, sehingga key yang tersimpan
     * adalah "key=value". Method ini menormalkan format tersebut sekaligus
     * mendukung format --key value (spasi) yang memang sudah ditangani CI4.
     *
     * @return array<string, string|true>
     */
    private function normalizeOptions(): array
    {
        $result = [];
        foreach (CLI::getOptions() as $rawKey => $rawVal) {
            if (str_contains($rawKey, '=')) {
                [$k, $v] = explode('=', $rawKey, 2);
                $result[$k] = $v;
            } else {
                // CI4 menyimpan null untuk flag tanpa nilai; kita pakai true
                $result[$rawKey] = ($rawVal === null) ? true : $rawVal;
            }
        }
        return $result;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Mode interaktif
    // ────────────────────────────────────────────────────────────────────────

    private function runInteractive(NotificationService $service, array $types): void
    {
        CLI::write('');
        CLI::write(CLI::color('=== Notify Test — Mode Interaktif ===', 'yellow'));
        CLI::write('');

        // Pilih target
        $targetChoice = CLI::prompt('Target pengiriman', ['user', 'role', 'all']);

        $userId = null;
        $role   = null;

        if ($targetChoice === 'user') {
            $userId = (int) CLI::prompt('Masukkan user ID');
        } elseif ($targetChoice === 'role') {
            $role = CLI::prompt('Masukkan nama role', ['superadmin', 'laboran', 'asisten', 'kepala_lab', 'dosen', 'mahasiswa']);
        }

        // Pilih tipe
        CLI::write('');
        $this->showTypeList($types);
        CLI::write('');
        $typeKeys = array_keys($types);
        $type     = CLI::prompt('Tipe notifikasi (ketik tipe dari daftar di atas)');

        if (! array_key_exists($type, $types)) {
            CLI::error("Tipe '{$type}' tidak dikenal. Batalkan.");
            return;
        }

        $context = $this->buildContext();

        CLI::write('');

        if ($targetChoice === 'user') {
            $service->sendToUser($userId, $type, $context);
            CLI::write(CLI::color('  ✓ ', 'green') . "Notifikasi [{$type}] dikirim ke user ID {$userId}");
        } elseif ($targetChoice === 'role') {
            $count = $this->countUsersInRole($role);
            $service->sendToRole($role, $type, $context);
            CLI::write(CLI::color('  ✓ ', 'green') . "Notifikasi [{$type}] dikirim ke {$count} user dalam role '{$role}'");
        } else {
            $count = $this->blastToAll($service, $type, $context);
            CLI::write(CLI::color('  ✓ ', 'green') . "Notifikasi [{$type}] dikirim ke {$count} user");
        }

        CLI::write('');
    }

    // ────────────────────────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────────────────────────

    private function buildContext(?string $url = null): array
    {
        $context = self::TEST_CONTEXT;

        if (! empty($url)) {
            $context['url'] = $url;
        }

        return $context;
    }

    private function blastToAll(NotificationService $service, string $type, array $context): int
    {
        $db    = Database::connect();
        $users = $db->table('users')->select('id')->get()->getResultArray();

        foreach ($users as $user) {
            $service->sendToUser((int) $user['id'], $type, $context);
        }

        return count($users);
    }

    private function countUsersInRole(string $role): int
    {
        $db = Database::connect();
        return (int) $db->table('auth_groups_users')
                        ->where('group', $role)
                        ->countAllResults();
    }

    private function showTypeList(array $types): void
    {
        CLI::write('');
        CLI::write(CLI::color('Tipe notifikasi tersedia:', 'yellow'));
        CLI::write(str_repeat('─', 72));

        $prevGroup = '';
        foreach ($types as $key => $def) {
            $group = explode('.', $key)[0];
            if ($group !== $prevGroup) {
                CLI::write('');
                CLI::write(CLI::color('  [' . strtoupper($group) . ']', 'cyan'));
                $prevGroup = $group;
            }
            $pad = str_pad($key, 26);
            CLI::write("  {$pad}  {$def['title']}");
        }

        CLI::write('');
    }
}
