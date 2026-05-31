<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GenerateConsumableRequestUuids extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Get all requests without UUID
        $requests = $db->table('consumable_requests')
            ->where('public_id IS NULL')
            ->orWhere('public_id', '')
            ->get()
            ->getResultArray();

        $count = 0;
        foreach ($requests as $request) {
            $uuid = $this->generateUuidV4();
            $db->table('consumable_requests')
                ->where('id', $request['id'])
                ->update(['public_id' => $uuid]);
            $count++;
        }

        echo "Generated {$count} UUIDs for existing consumable requests.\n";
    }

    private function generateUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
