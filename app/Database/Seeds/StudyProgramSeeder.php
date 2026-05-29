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

        $studyPrograms = [
            [
                'faculty_code' => 'FIF',
                'name'         => 'S1 Informatika',
                'description'  => 'Sarjana (S1)',
                'is_active'    => 1,
            ],
            [
                'faculty_code' => 'FIF',
                'name'         => 'S1 Sistem Informasi',
                'description'  => 'Sarjana (S1)',
                'is_active'    => 1,
            ],
            [
                'faculty_code' => 'FIF',
                'name'         => 'S1 Rekayasa Perangkat Lunak (Software Engineering)',
                'description'  => 'Sarjana (S1)',
                'is_active'    => 1,
            ],
            [
                'faculty_code' => 'FIF',
                'name'         => 'S1 Sains Data',
                'description'  => 'Sarjana (S1)',
                'is_active'    => 1,
            ],
            [
                'faculty_code' => 'FTTE',
                'name'         => 'S1 Teknik Telekomunikasi',
                'description'  => 'Sarjana (S1)',
                'is_active'    => 1,
            ],
            [
                'faculty_code' => 'FTTE',
                'name'         => 'S1 Teknik Elektro',
                'description'  => 'Sarjana (S1)',
                'is_active'    => 1,
            ],
            [
                'faculty_code' => 'FTTE',
                'name'         => 'S1 Teknoekonomi Komersial (Industrial Engineering)',
                'description'  => 'Sarjana (S1)',
                'is_active'    => 1,
            ],
            [
                'faculty_code' => 'FTTE',
                'name'         => 'S1 Teknik Biomedis',
                'description'  => 'Sarjana (S1)',
                'is_active'    => 1,
            ],
            [
                'faculty_code' => 'FTTE',
                'name'         => 'D3 Teknologi Telekomunikasi',
                'description'  => 'Diploma (D3)',
                'is_active'    => 1,
            ],
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
                        'description' => $studyProgram['description'],
                        'is_active'   => $studyProgram['is_active'],
                        'updated_at'  => $now,
                    ]);
                continue;
            }

            $table->insert([
                'faculty_id'  => $facultyId,
                'name'        => $studyProgram['name'],
                'code'        => null,
                'description' => $studyProgram['description'],
                'is_active'   => $studyProgram['is_active'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        echo "StudyProgramSeeder selesai. Data master program studi berhasil disiapkan.\n";
    }
}
