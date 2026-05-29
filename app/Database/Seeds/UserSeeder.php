<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Dapatkan user provider dari Shield
        $users = auth()->getProvider();

        /**
         * Daftar user default yang akan dibuat
         */
        $defaultUsers = [
            [
                'username' => 'superadmin',
                'email'    => 'superadmin@example.com',
                'password' => 'password123',
                'group'    => 'superadmin',
            ],
            [
                'username' => 'laboran',
                'email'    => 'laboran@example.com',
                'password' => 'password123',
                'group'    => 'laboran',
            ],
            [
                'username' => 'asisten',
                'email'    => 'asisten@example.com',
                'password' => 'password123',
                'group'    => 'asisten',
            ],
            [
                'username' => 'kepalab',
                'email'    => 'kepalab@example.com',
                'password' => 'password123',
                'group'    => 'kepala_lab',
            ],
            [
                'username' => 'dosen',
                'email'    => 'dosen@example.com',
                'password' => 'password123',
                'group'    => 'dosen',
            ],
            [
                'username' => 'mahasiswa',
                'email'    => 'mahasiswa@example.com',
                'password' => 'password123',
                'group'    => 'mahasiswa',
            ],
        ];

        foreach ($defaultUsers as $userData) {
            // Buat user entity
            $user = new User([
                'username' => $userData['username'],
                'email'    => $userData['email'],
                'password' => $userData['password'],
                'active'   => 1,
            ]);

            // Simpan user
            $users->save($user);

            // Ambil user yang baru dibuat
            $user = $users->findById($users->getInsertID());

            // Assign group/role
            $user->addGroup($userData['group']);

            echo "User '{$userData['username']}' created with role '{$userData['group']}'\n";
        }

        echo "\n=== Default Login Credentials ===\n";
        echo "Super Admin  : superadmin@example.com / password123\n";
        echo "Laboran      : laboran@example.com / password123\n";
        echo "Asisten Lab  : asisten@example.com / password123\n";
        echo "Kepala Lab   : kepalab@example.com / password123\n";
        echo "Dosen        : dosen@example.com / password123\n";
        echo "Mahasiswa    : mahasiswa@example.com / password123\n";
        echo "=================================\n";
    }
}
