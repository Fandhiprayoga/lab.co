<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class StudyProgramSeeder extends Seeder
{
    public function run()
    {
        if (ENVIRONMENT !== 'development') {
            throw new RuntimeException('StudyProgramSeeder hanya boleh dieksekusi pada environment development.');
        }

        if (! $this->db->tableExists('study_programs')) {
            throw new RuntimeException('Tabel study_programs belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        if (! $this->db->tableExists('faculties')) {
            throw new RuntimeException('Tabel faculties belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $facultyRows = $this->db->table('faculties')
            ->select('id, code')
            ->whereIn('code', ['FTE', 'FIF', 'FRI', 'FIK', 'FEB', 'FIT'])
            ->get()
            ->getResultArray();

        $facultyMap = [];
        foreach ($facultyRows as $facultyRow) {
            $facultyMap[$facultyRow['code']] = (int) $facultyRow['id'];
        }

        $missingFaculties = array_diff(['FTE', 'FIF', 'FRI', 'FIK', 'FEB', 'FIT'], array_keys($facultyMap));
        if (! empty($missingFaculties)) {
            throw new RuntimeException('Data fakultas belum tersedia: ' . implode(', ', $missingFaculties) . '. Jalankan FacultySeeder terlebih dahulu.');
        }

        $studyPrograms = [
            // Fakultas Teknik Elektro
            ['faculty_code' => 'FTE', 'code' => 'TTG', 'name' => 'S1 Teknik Telekomunikasi',           'description' => 'Sarjana (S1)', 'is_active' => 1],
            ['faculty_code' => 'FTE', 'code' => 'TE',  'name' => 'S1 Teknik Elektro',                  'description' => 'Sarjana (S1)', 'is_active' => 1],
            ['faculty_code' => 'FTE', 'code' => 'TBM', 'name' => 'S1 Teknik Biomedis',                 'description' => 'Sarjana (S1)', 'is_active' => 1],

            // Fakultas Informatika
            ['faculty_code' => 'FIF', 'code' => 'IF',  'name' => 'S1 Informatika',                     'description' => 'Sarjana (S1)', 'is_active' => 1],
            ['faculty_code' => 'FIF', 'code' => 'RPL', 'name' => 'S1 Rekayasa Perangkat Lunak',        'description' => 'Sarjana (S1)', 'is_active' => 1],
            ['faculty_code' => 'FIF', 'code' => 'SD',  'name' => 'S1 Sains Data',                      'description' => 'Sarjana (S1)', 'is_active' => 1],

            // Fakultas Rekayasa Industri
            ['faculty_code' => 'FRI', 'code' => 'TI',  'name' => 'S1 Teknik Industri',                 'description' => 'Sarjana (S1)', 'is_active' => 1],
            ['faculty_code' => 'FRI', 'code' => 'TL',  'name' => 'S1 Teknik Logistik',                 'description' => 'Sarjana (S1)', 'is_active' => 1],
            ['faculty_code' => 'FRI', 'code' => 'SI',  'name' => 'S1 Sistem Informasi',                'description' => 'Sarjana (S1)', 'is_active' => 1],
            ['faculty_code' => 'FRI', 'code' => 'TP',  'name' => 'S1 Teknologi Pangan',                'description' => 'Sarjana (S1)', 'is_active' => 1],

            // Fakultas Industri Kreatif
            ['faculty_code' => 'FIK', 'code' => 'DKV', 'name' => 'S1 Desain Komunikasi Visual (DKV)',  'description' => 'Sarjana (S1)', 'is_active' => 1],
            ['faculty_code' => 'FIK', 'code' => 'DP',  'name' => 'S1 Desain Produk',                   'description' => 'Sarjana (S1)', 'is_active' => 1],

            // Fakultas Ekonomi dan Bisnis
            ['faculty_code' => 'FEB', 'code' => 'BD',   'name' => 'S1 Bisnis Digital',                 'description' => 'Sarjana (S1)', 'is_active' => 1],

            // Fakultas Ilmu Terapan
            ['faculty_code' => 'FIT', 'code' => 'D3TT', 'name' => 'D3 Teknologi Telekomunikasi',       'description' => 'Diploma (D3)', 'is_active' => 1],
        ];

        $table = $this->db->table('study_programs');
        $now   = date('Y-m-d H:i:s');

        foreach ($studyPrograms as $studyProgram) {
            $facultyId = $facultyMap[$studyProgram['faculty_code']];

            $existing = $table
                ->where('faculty_id', $facultyId)
                ->where('name', $studyProgram['name'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $table
                    ->where('id', (int) $existing['id'])
                    ->update([
                        'code'        => $studyProgram['code'],
                        'description' => $studyProgram['description'],
                        'is_active'   => $studyProgram['is_active'],
                        'updated_at'  => $now,
                    ]);
                continue;
            }

            $table->insert([
                'faculty_id'  => $facultyId,
                'name'        => $studyProgram['name'],
                'code'        => $studyProgram['code'],
                'description' => $studyProgram['description'],
                'is_active'   => $studyProgram['is_active'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        echo "StudyProgramSeeder selesai. Data master program studi berhasil disiapkan.\n";
    }
}
