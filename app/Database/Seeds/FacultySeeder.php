<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class FacultySeeder extends Seeder
{
    public function run()
    {
        if (ENVIRONMENT !== 'development') {
            throw new RuntimeException('FacultySeeder hanya boleh dieksekusi pada environment development.');
        }

        if (! $this->db->tableExists('faculties')) {
            throw new RuntimeException('Tabel faculties belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $faculties = [
            [
                'name'        => 'Fakultas Informatika',
                'code'        => 'FIF',
                'description' => null,
                'is_active'   => 1,
            ],
            [
                'name'        => 'Fakultas Teknik Telekomunikasi dan Elektro',
                'code'        => 'FTTE',
                'description' => null,
                'is_active'   => 1,
            ],
        ];

        $table = $this->db->table('faculties');
        $now   = date('Y-m-d H:i:s');

        foreach ($faculties as $faculty) {
            $existing = $table->where('code', $faculty['code'])->get()->getRowArray();

            if ($existing) {
                $table
                    ->where('id', (int) $existing['id'])
                    ->update([
                        'name'        => $faculty['name'],
                        'description' => $faculty['description'],
                        'is_active'   => $faculty['is_active'],
                        'updated_at'  => $now,
                    ]);
                continue;
            }

            $table->insert([
                'name'        => $faculty['name'],
                'code'        => $faculty['code'],
                'description' => $faculty['description'],
                'is_active'   => $faculty['is_active'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        echo "FacultySeeder selesai. Data master fakultas berhasil disiapkan.\n";
    }
}
