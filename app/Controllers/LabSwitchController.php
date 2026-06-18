<?php

namespace App\Controllers;

use App\Models\UserLabAssignmentModel;

class LabSwitchController extends BaseController
{
    /**
     * Switch active lab untuk user yang sedang login.
     * User hanya bisa pilih lab yang memang di-assign ke dirinya.
     * Support both form POST (redirect) and AJAX (JSON) requests.
     */
    public function switch()
    {
        $labId = $this->request->getPost('lab_id');
        $user  = auth()->user();

        if (empty($labId)) {
            // Reset — clear active lab
            session()->remove('active_lab_id');
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => true, 'message' => 'Lab aktif direset.']);
            }
            return redirect()->back()->with('success', 'Lab aktif direset ke semua lab.');
        }

        $labIds = (new UserLabAssignmentModel())->getLabIdsByUser((int) $user->id);

        if (! in_array((int) $labId, $labIds)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Anda tidak memiliki akses ke lab tersebut.']);
            }
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke lab tersebut.');
        }

        session()->set('active_lab_id', (int) $labId);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Lab aktif diubah.']);
        }

        return redirect()->back()->with('success', 'Lab aktif diubah.');
    }
}
