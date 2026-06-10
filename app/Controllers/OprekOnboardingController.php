<?php

namespace App\Controllers;

use App\Models\OprekApplicationModel;
use App\Models\OprekApplicationDocumentModel;
use App\Models\OprekOnboardingProfileModel;
use App\Models\OprekActivityLogModel;

class OprekOnboardingController extends BaseController
{
    protected OprekApplicationModel $applicationModel;
    protected OprekApplicationDocumentModel $documentModel;
    protected OprekOnboardingProfileModel $onboardingModel;
    protected OprekActivityLogModel $logModel;

    public function __construct()
    {
        $this->applicationModel = new OprekApplicationModel();
        $this->documentModel    = new OprekApplicationDocumentModel();
        $this->onboardingModel  = new OprekOnboardingProfileModel();
        $this->logModel         = new OprekActivityLogModel();
    }

    // ---------------------------------------------------------------
    // Student: View and submit onboarding data
    // ---------------------------------------------------------------
    public function index($publicId)
    {
        $application = $this->applicationModel->findByPublicId($publicId);
        if (! $application || (int) $application->student_id !== auth()->id()) {
            return redirect()->to('/oprek/my-applications')->with('error', 'Akses ditolak.');
        }

        if (! in_array($application->application_status, ['accepted', 'onboarding_pending', 'onboarding_complete'])) {
            return redirect()->to('/oprek/my-applications')->with('error', 'Anda belum dinyatakan diterima.');
        }

        $applicationId = $application->id;
        $profile  = $this->onboardingModel->getByApplication($applicationId);
        $documents = $this->documentModel->getByApplication($applicationId);

        return $this->renderView('oprek/onboarding/student_form', [
            'title'       => 'Kelengkapan Berkas',
            'page_title'  => 'Upload Kelengkapan Data Asisten',
            'application' => $application,
            'profile'     => $profile,
            'documents'   => $documents,
        ]);
    }

    public function storeOnboarding($publicId)
    {
        $application = $this->applicationModel->findByPublicId($publicId);
        if (! $application || (int) $application->student_id !== auth()->id()) {
            return redirect()->to('/oprek/my-applications')->with('error', 'Akses ditolak.');
        }

        $applicationId = $application->id;

        $rules = [
            'bank_account_number' => 'required|max_length[30]',
            'bank_name'           => 'required|max_length[100]',
            'bank_account_name'   => 'required|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $campaignId = $application->campaign_id;

        // Update profile fields
        $profile = $this->onboardingModel->getByApplication($applicationId);
        $profileData = [
            'bank_account_number' => $this->request->getPost('bank_account_number'),
            'bank_name'           => $this->request->getPost('bank_name'),
            'bank_account_name'   => $this->request->getPost('bank_account_name'),
            'onboarding_status'   => 'submitted',
        ];

        // Upload signature
        $sigFile = $this->request->getFile('signature_file');
        if ($sigFile && $sigFile->isValid()) {
            $sigDocId = $this->uploadOnboardingDoc($applicationId, $campaignId, 'signature', $sigFile);
            if ($sigDocId) {
                $profileData['signature_document_id'] = $sigDocId;
            }
        }

        // Upload passbook front page
        $passbookFile = $this->request->getFile('passbook_file');
        if ($passbookFile && $passbookFile->isValid()) {
            $passbookDocId = $this->uploadOnboardingDoc($applicationId, $campaignId, 'passbook_front', $passbookFile);
            if ($passbookDocId) {
                $profileData['passbook_document_id'] = $passbookDocId;
            }
        }

        if ($profile) {
            $this->onboardingModel->update($profile->id, $profileData);
        } else {
            $profileData['application_id'] = $applicationId;
            $this->onboardingModel->insert($profileData);
        }

        // Update application status
        $this->applicationModel->update($applicationId, ['application_status' => 'onboarding_pending']);

        $this->logModel->log(auth()->id(), 'onboarding.submitted', (int) $campaignId, (int) $applicationId);

        return redirect()->to('/oprek/my-applications/' . $application->public_id)->with('success', 'Data kelengkapan berhasil dikirim. Menunggu verifikasi laboran.');
    }

    private function uploadOnboardingDoc(int $applicationId, int $campaignId, string $type, $file): ?int
    {
        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, $allowedExts)) return null;

        if ($file->getSize() > 5 * 1024 * 1024) return null;

        $newName = $type . '_' . auth()->id() . '_' . time() . '.' . $ext;
        $subfolder = 'oprek/' . $campaignId . '/' . $applicationId;
        $file->move(WRITEPATH . 'uploads/' . $subfolder, $newName);

        // Check if existing doc of this type
        $existingDoc = $this->documentModel->getByApplicationAndType($applicationId, $type);
        if ($existingDoc) {
            $oldPath = WRITEPATH . $existingDoc->file_path;
            if (is_file($oldPath)) unlink($oldPath);
            $this->documentModel->update($existingDoc->id, [
                'file_path' => 'uploads/' . $subfolder . '/' . $newName,
                'file_name' => $file->getClientName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
            return (int) $existingDoc->id;
        }

        return $this->documentModel->insert([
            'application_id' => $applicationId,
            'document_type'  => $type,
            'file_path'      => 'uploads/' . $subfolder . '/' . $newName,
            'file_name'      => $file->getClientName(),
            'mime_type'      => $file->getClientMimeType(),
            'file_size'      => $file->getSize(),
        ]);
    }

    // ---------------------------------------------------------------
    // Laboran: Verify onboarding
    // ---------------------------------------------------------------
    public function verify($publicId)
    {
        if (! activeGroupCan('oprek.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $application = $this->applicationModel->findByPublicId($publicId);
        if (! $application) {
            return redirect()->back()->with('error', 'Pendaftaran tidak ditemukan.');
        }

        $applicationId = $application->id;
        $profile  = $this->onboardingModel->getByApplication($applicationId);
        $documents = $this->documentModel->getByApplication($applicationId);

        return $this->renderView('oprek/onboarding/verify', [
            'title'       => 'Verifikasi Onboarding',
            'page_title'  => 'Verifikasi Kelengkapan Data Asisten',
            'application' => $application,
            'profile'     => $profile,
            'documents'   => $documents,
        ]);
    }

    public function storeVerification($publicId)
    {
        if (! activeGroupCan('oprek.manage')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $action = $this->request->getPost('action'); // verified / revision
        $note   = $this->request->getPost('note');

        $application = $this->applicationModel->findByPublicId($publicId);
        if (! $application) {
            return redirect()->back()->with('error', 'Pendaftaran tidak ditemukan.');
        }

        $applicationId = $application->id;
        $profile = $this->onboardingModel->getByApplication($applicationId);
        if (! $profile) {
            return redirect()->back()->with('error', 'Data onboarding tidak ditemukan.');
        }

        $update = [
            'onboarding_status' => $action === 'verified' ? 'verified' : 'revision',
            'verified_by'       => $action === 'verified' ? auth()->id() : null,
            'verified_at'       => $action === 'verified' ? date('Y-m-d H:i:s') : null,
        ];

        $this->onboardingModel->update($profile->id, $update);

        $appUpdate = ['application_status' => $action === 'verified' ? 'onboarding_complete' : 'onboarding_pending'];
        $this->applicationModel->update($applicationId, $appUpdate);

        $application = $this->applicationModel->find($applicationId);
        $this->logModel->log(auth()->id(), "onboarding.{$action}", (int) $application->campaign_id, (int) $applicationId);

        $notifType = $action === 'verified' ? 'oprek.onboarding_verified' : 'oprek.onboarding_revision';
        $campaignObj = $this->applicationModel
            ->select('oc.period_name')
            ->join('oprek_campaigns oc', 'oc.id = oprek_applications.campaign_id')
            ->where('oprek_applications.id', $applicationId)
            ->first();
        send_notification($application->student_id, $notifType, [
            'application_id' => $applicationId,
            'period_name'    => $campaignObj->period_name ?? 'Oprek',
        ]);

        return redirect()->to('/oprek/' . $application->campaign_id)->with('success', 'Verifikasi onboarding berhasil.');
    }
}
