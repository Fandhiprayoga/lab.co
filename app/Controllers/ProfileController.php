<?php

namespace App\Controllers;

use App\Models\UserProfileModel;
use App\Models\StudyProgramModel;

class ProfileController extends BaseController
{
    protected UserProfileModel $profileModel;
    protected StudyProgramModel $studyProgramModel;

    public function __construct()
    {
        $this->profileModel      = new UserProfileModel();
        $this->studyProgramModel = new StudyProgramModel();
    }

    public function index()
    {
        $user    = auth()->user();
        $profile = $this->profileModel->getByUserId((int) $user->id);

        $studyPrograms = $this->studyProgramModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        $data = [
            'title'         => 'Profil Saya',
            'page_title'    => 'Profil',
            'user'          => $user,
            'userGroups'    => $user->getGroups(),
            'profile'       => $profile,
            'studyPrograms' => $studyPrograms,
        ];

        return $this->renderView('profile/index', $data);
    }

    public function update()
    {
        $user = auth()->user();

        $rules = [
            'username' => 'required|min_length[3]|max_length[30]',
            'prodi'    => 'permit_empty|max_length[150]',
            'nim_nik'  => 'permit_empty|max_length[50]',
            'phone'    => 'permit_empty|max_length[20]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user->username = $this->request->getPost('username');

        // Update password jika diisi
        $password = $this->request->getPost('password');
        if (! empty($password)) {
            $user->password = $password;
        }

        $users = auth()->getProvider();
        $users->save($user);

        // Simpan data profil tambahan
        $this->profileModel->upsert((int) $user->id, [
            'prodi'   => $this->request->getPost('prodi'),
            'nim_nik' => $this->request->getPost('nim_nik'),
            'phone'   => $this->request->getPost('phone'),
        ]);

        return redirect()->to('/profile')->with('success', 'Profil berhasil diperbarui.');
    }
}
