<?php

namespace App\Models;

use CodeIgniter\Model;

class UserProfileModel extends Model
{
    protected $table         = 'user_profiles';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'user_id',
        'prodi',
        'nim_nik',
        'phone',
    ];

    protected $validationRules = [
        'prodi'   => 'permit_empty|max_length[150]',
        'nim_nik' => 'permit_empty|max_length[50]',
        'phone'   => 'permit_empty|max_length[20]',
    ];

    /**
     * Get profile by user_id, return null if not found.
     */
    public function getByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Insert or update profile for the given user.
     */
    public function upsert(int $userId, array $data): bool
    {
        $existing = $this->where('user_id', $userId)->first();

        if ($existing) {
            return $this->update($existing['id'], $data);
        }

        return $this->insert(array_merge($data, ['user_id' => $userId])) !== false;
    }
}
