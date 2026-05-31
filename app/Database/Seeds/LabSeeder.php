<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class LabSeeder extends Seeder
{
    public function run()
    {
        if (ENVIRONMENT !== 'development') {
            throw new RuntimeException('LabSeeder hanya boleh dieksekusi pada environment development.');
        }

        if (! $this->db->tableExists('labs')) {
            throw new RuntimeException('Tabel labs belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $labs = [
            [
                'name'             => 'Lab Jaringan Komputer',
                'code'             => 'LAB-JK',
                'description'      => 'Laboratorium untuk praktikum dan penelitian di bidang jaringan komputer.',
                'location'         => 'Telkom University Purwokerto',
                'capacity'         => 32,
                'logo'             => null,
                'is_active'        => 1,
                'is_loanable'      => 1,
                'condition_status' => 'baik',
            ],
            [
                'name'             => 'Lab Pemrograman & Aplikasi',
                'code'             => 'LAB-PA',
                'description'      => 'Laboratorium untuk praktikum pemrograman dan pengembangan aplikasi.',
                'location'         => 'Telkom University Purwokerto',
                'capacity'         => 40,
                'logo'             => null,
                'is_active'        => 1,
                'is_loanable'      => 1,
                'condition_status' => 'baik',
            ],
            [
                'name'             => 'Lab Multimedia',
                'code'             => 'LAB-MM',
                'description'      => 'Laboratorium untuk produksi dan pengolahan konten multimedia.',
                'location'         => 'Telkom University Purwokerto',
                'capacity'         => 24,
                'logo'             => null,
                'is_active'        => 1,
                'is_loanable'      => 1,
                'condition_status' => 'baik',
            ],
            [
                'name'             => 'Lab Data',
                'code'             => 'LAB-DATA',
                'description'      => 'Laboratorium untuk analisis data, machine learning, dan kecerdasan buatan.',
                'location'         => 'Telkom University Purwokerto',
                'capacity'         => 32,
                'logo'             => null,
                'is_active'        => 1,
                'is_loanable'      => 1,
                'condition_status' => 'baik',
            ],
            [
                'name'             => 'Lab High Performance',
                'code'             => 'LAB-HPC',
                'description'      => 'Laboratorium komputasi berkinerja tinggi untuk riset intensif.',
                'location'         => 'Telkom University Purwokerto',
                'capacity'         => 16,
                'logo'             => null,
                'is_active'        => 1,
                'is_loanable'      => 1,
                'condition_status' => 'baik',
            ],
            [
                'name'             => 'Lab E-Government',
                'code'             => 'LAB-EGOV',
                'description'      => 'Laboratorium penelitian dan pengembangan sistem e-government.',
                'location'         => 'Telkom University Purwokerto',
                'capacity'         => 30,
                'logo'             => null,
                'is_active'        => 1,
                'is_loanable'      => 1,
                'condition_status' => 'baik',
            ],
            [
                'name'             => 'Studio Fakultas Informatika',
                'code'             => 'STUDIO-FIF',
                'description'      => 'Studio kreatif untuk kegiatan produksi dan inovasi Fakultas Informatika.',
                'location'         => 'Telkom University Purwokerto',
                'capacity'         => 20,
                'logo'             => null,
                'is_active'        => 1,
                'is_loanable'      => 1,
                'condition_status' => 'baik',
            ],
            [
                'name'             => 'Lab Teknik Elektro & Teknik Digital (TE & TD)',
                'code'             => 'LAB-TETD',
                'description'      => 'Laboratorium praktikum teknik elektro dan teknik digital.',
                'location'         => 'Telkom University Purwokerto',
                'capacity'         => 30,
                'logo'             => null,
                'is_active'        => 1,
                'is_loanable'      => 1,
                'condition_status' => 'baik',
            ],
            [
                'name'             => 'Lab Sistem Komunikasi',
                'code'             => 'LAB-SISKOM',
                'description'      => 'Laboratorium untuk penelitian dan praktikum sistem komunikasi.',
                'location'         => 'Telkom University Purwokerto',
                'capacity'         => 24,
                'logo'             => null,
                'is_active'        => 1,
                'is_loanable'      => 1,
                'condition_status' => 'baik',
            ],
            [
                'name'             => 'Lab Switching',
                'code'             => 'LAB-SWT',
                'description'      => 'Laboratorium switching dan jaringan telekomunikasi.',
                'location'         => 'Telkom University Purwokerto',
                'capacity'         => 24,
                'logo'             => null,
                'is_active'        => 1,
                'is_loanable'      => 1,
                'condition_status' => 'baik',
            ],
            [
                'name'             => 'Lab Mikrobiologi & Biomedis',
                'code'             => 'LAB-MBIO',
                'description'      => 'Laboratorium mikrobiologi dan instrumentasi biomedis.',
                'location'         => 'Telkom University Purwokerto',
                'capacity'         => 20,
                'logo'             => null,
                'is_active'        => 1,
                'is_loanable'      => 1,
                'condition_status' => 'baik',
            ],
            [
                'name'             => 'Lab Physics and Instrumentation',
                'code'             => 'LAB-PI',
                'description'      => 'Laboratorium fisika terapan dan instrumentasi.',
                'location'         => 'Telkom University Purwokerto',
                'capacity'         => 24,
                'logo'             => null,
                'is_active'        => 1,
                'is_loanable'      => 1,
                'condition_status' => 'baik',
            ],
            [
                'name'             => 'Lab Internet of Everything (IoE)',
                'code'             => 'LAB-IOE',
                'description'      => 'Laboratorium riset dan pengembangan Internet of Everything.',
                'location'         => 'Telkom University Purwokerto',
                'capacity'         => 20,
                'logo'             => null,
                'is_active'        => 1,
                'is_loanable'      => 1,
                'condition_status' => 'baik',
            ],
        ];

        $table = $this->db->table('labs');
        $now   = date('Y-m-d H:i:s');

        foreach ($labs as $lab) {
            $existing = $table->where('code', $lab['code'])->get()->getRowArray();

            if ($existing) {
                $table
                    ->where('id', (int) $existing['id'])
                    ->update([
                        'name'             => $lab['name'],
                        'description'      => $lab['description'],
                        'location'         => $lab['location'],
                        'capacity'         => $lab['capacity'],
                        'logo'             => $lab['logo'],
                        'is_active'        => $lab['is_active'],
                        'is_loanable'      => $lab['is_loanable'],
                        'condition_status' => $lab['condition_status'],
                        'updated_at'       => $now,
                    ]);
                continue;
            }

            $table->insert([
                'name'             => $lab['name'],
                'code'             => $lab['code'],
                'description'      => $lab['description'],
                'location'         => $lab['location'],
                'capacity'         => $lab['capacity'],
                'logo'             => $lab['logo'],
                'qr_token'         => bin2hex(random_bytes(16)),
                'is_active'        => $lab['is_active'],
                'is_loanable'      => $lab['is_loanable'],
                'condition_status' => $lab['condition_status'],
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        echo "LabSeeder selesai. Data master lab berhasil disiapkan.\n";
    }
}
