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

        if (! $this->db->tableExists('faculties')) {
            throw new RuntimeException('Tabel faculties belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $facultyRows = $this->db->table('faculties')
            ->select('id, code')
            ->whereIn('code', ['FIF', 'FTTE'])
            ->get()
            ->getResultArray();

        $facultyMap = [];
        foreach ($facultyRows as $facultyRow) {
            $facultyMap[$facultyRow['code']] = (int) $facultyRow['id'];
        }

        if (! isset($facultyMap['FIF']) || ! isset($facultyMap['FTTE'])) {
            throw new RuntimeException('Data fakultas FIF/FTTE belum tersedia. Jalankan FacultySeeder terlebih dahulu.');
        }

        $labs = [
            [
                'name'      => 'Lab Jaringan Komputer',
                'code'      => 'LAB-JK',
                'faculty_code' => 'FIF',
                'location'  => 'Telkom University Purwokerto',
                'capacity'  => null,
                'is_active' => 1,
            ],
            [
                'name'      => 'Lab Pemrograman & Aplikasi',
                'code'      => 'LAB-PA',
                'faculty_code' => 'FIF',
                'location'  => 'Telkom University Purwokerto',
                'capacity'  => null,
                'is_active' => 1,
            ],
            [
                'name'      => 'Lab Multimedia',
                'code'      => 'LAB-MM',
                'faculty_code' => 'FIF',
                'location'  => 'Telkom University Purwokerto',
                'capacity'  => null,
                'is_active' => 1,
            ],
            [
                'name'      => 'Lab Data',
                'code'      => 'LAB-DATA',
                'faculty_code' => 'FIF',
                'location'  => 'Telkom University Purwokerto',
                'capacity'  => null,
                'is_active' => 1,
            ],
            [
                'name'      => 'Lab High Performance',
                'code'      => 'LAB-HPC',
                'faculty_code' => 'FIF',
                'location'  => 'Telkom University Purwokerto',
                'capacity'  => null,
                'is_active' => 1,
            ],
            [
                'name'      => 'Lab E-Government',
                'code'      => 'LAB-EGOV',
                'faculty_code' => 'FIF',
                'location'  => 'Telkom University Purwokerto',
                'capacity'  => null,
                'is_active' => 1,
            ],
            [
                'name'      => 'Studio Fakultas Informatika',
                'code'      => 'STUDIO-FIF',
                'faculty_code' => 'FIF',
                'location'  => 'Telkom University Purwokerto',
                'capacity'  => null,
                'is_active' => 1,
            ],
            [
                'name'      => 'Lab Teknik Elektro & Teknik Digital (TE & TD)',
                'code'      => 'LAB-TETD',
                'faculty_code' => 'FTTE',
                'location'  => 'Telkom University Purwokerto',
                'capacity'  => null,
                'is_active' => 1,
            ],
            [
                'name'      => 'Lab Sistem Komunikasi',
                'code'      => 'LAB-SISKOM',
                'faculty_code' => 'FTTE',
                'location'  => 'Telkom University Purwokerto',
                'capacity'  => null,
                'is_active' => 1,
            ],
            [
                'name'      => 'Lab Switching',
                'code'      => 'LAB-SWT',
                'faculty_code' => 'FTTE',
                'location'  => 'Telkom University Purwokerto',
                'capacity'  => null,
                'is_active' => 1,
            ],
            [
                'name'      => 'Lab Mikrobiologi & Biomedis',
                'code'      => 'LAB-MBIO',
                'faculty_code' => 'FTTE',
                'location'  => 'Telkom University Purwokerto',
                'capacity'  => null,
                'is_active' => 1,
            ],
            [
                'name'      => 'Lab Physics and Instrumentation',
                'code'      => 'LAB-PI',
                'faculty_code' => 'FTTE',
                'location'  => 'Telkom University Purwokerto',
                'capacity'  => null,
                'is_active' => 1,
            ],
            [
                'name'      => 'Lab Internet of Everything (IoE)',
                'code'      => 'LAB-IOE',
                'faculty_code' => 'FTTE',
                'location'  => 'Telkom University Purwokerto',
                'capacity'  => null,
                'is_active' => 1,
            ],
        ];

        $table = $this->db->table('labs');
        $now   = date('Y-m-d H:i:s');

        foreach ($labs as $lab) {
            $facultyId = $facultyMap[$lab['faculty_code']] ?? null;
            if ($facultyId === null) {
                throw new RuntimeException('Mapping fakultas tidak ditemukan untuk lab: ' . $lab['name']);
            }

            $existing = $table->where('code', $lab['code'])->get()->getRowArray();

            if ($existing) {
                $table
                    ->where('id', (int) $existing['id'])
                    ->update([
                        'name'       => $lab['name'],
                        'faculty_id' => $facultyId,
                        'location'   => $lab['location'],
                        'capacity'   => $lab['capacity'],
                        'is_active'  => $lab['is_active'],
                        'updated_at' => $now,
                    ]);
                continue;
            }

            $table->insert([
                'name'       => $lab['name'],
                'code'       => $lab['code'],
                'faculty_id' => $facultyId,
                'location'   => $lab['location'],
                'capacity'   => $lab['capacity'],
                'is_active'  => $lab['is_active'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        echo "LabSeeder selesai. Data master lab berhasil disiapkan.\n";
    }
}
