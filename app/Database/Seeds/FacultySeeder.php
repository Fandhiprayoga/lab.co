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
                'name'        => 'Fakultas Teknik Elektro',
                'code'        => 'FTE',
                'description' => 'Fakultas yang berfokus pada pengembangan ilmu dan teknologi di bidang teknik elektro, sistem tenaga, elektronika, dan telekomunikasi.',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Fakultas Informatika',
                'code'        => 'FIF',
                'description' => 'Fakultas yang mengembangkan ilmu komputer, rekayasa perangkat lunak, kecerdasan buatan, dan sistem informasi berbasis teknologi terkini.',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Fakultas Rekayasa Industri',
                'code'        => 'FRI',
                'description' => 'Fakultas yang berfokus pada perancangan, pengelolaan, dan optimasi sistem industri serta rantai pasok secara terintegrasi.',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Fakultas Industri Kreatif',
                'code'        => 'FIK',
                'description' => 'Fakultas yang mengintegrasikan kreativitas, seni, dan teknologi untuk menghasilkan karya inovatif di bidang desain, animasi, dan media digital.',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Fakultas Ekonomi dan Bisnis',
                'code'        => 'FEB',
                'description' => 'Fakultas yang mengkaji ilmu ekonomi, manajemen bisnis, akuntansi, dan kewirausahaan dalam konteks ekonomi digital dan global.',
                'is_active'   => 1,
            ],
            [
                'name'        => 'Fakultas Ilmu Terapan',
                'code'        => 'FIT',
                'description' => 'Fakultas yang menyelenggarakan pendidikan vokasi dan terapan di bidang teknologi informasi, komputasi, dan administrasi bisnis berbasis praktik.',
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
