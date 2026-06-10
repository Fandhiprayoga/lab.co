<?php

namespace App\Controllers;

use App\Models\OprekCampaignModel;
use App\Models\OprekApplicationModel;
use App\Models\OprekApplicationDocumentModel;
use App\Models\OprekActivityLogModel;
use App\Models\LabModel;

class OprekRegistrationController extends BaseController
{
    protected OprekCampaignModel $campaignModel;
    protected OprekApplicationModel $applicationModel;
    protected OprekApplicationDocumentModel $documentModel;
    protected OprekActivityLogModel $logModel;
    protected LabModel $labModel;

    public function __construct()
    {
        $this->campaignModel    = new OprekCampaignModel();
        $this->applicationModel = new OprekApplicationModel();
        $this->documentModel    = new OprekApplicationDocumentModel();
        $this->logModel         = new OprekActivityLogModel();
        $this->labModel         = new LabModel();
    }

    // ---------------------------------------------------------------
    // Student: Browse active oprek
    // ---------------------------------------------------------------
    public function browse()
    {
        if (! activeGroupCan('oprek.apply')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak dapat mengakses halaman ini.');
        }

        $tab         = $this->request->getGet('tab') ?? 'active';
        $filterLabId = (int) $this->request->getGet('lab_id');
        $labs        = $this->labModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();

        if ($tab === 'archive') {
            $campaigns = $this->campaignModel->getArchived($filterLabId > 0 ? $filterLabId : null);
            $pageTitle = 'Daftar Open Rekrutmen Arsip';
        } else {
            $campaigns = $this->campaignModel->getActive($filterLabId > 0 ? $filterLabId : null);
            $pageTitle = 'Daftar Open Rekrutmen Aktif';
        }

        // Check which campaigns the student has already applied to
        $studentId = auth()->id();
        foreach ($campaigns as $c) {
            $existing = $this->applicationModel
                ->select('id, public_id, application_status')
                ->where('campaign_id', $c->id)
                ->where('student_id', $studentId)
                ->first();
            $c->has_applied = $existing !== null;
            $c->application_status = $existing->application_status ?? null;
            $c->application_id = $existing->id ?? null;
            $c->application_public_id = $existing->public_id ?? null;
        }

        return $this->renderView('oprek/registration/browse', [
            'title'        => 'Open Rekrutmen Asisten',
            'page_title'   => $pageTitle,
            'campaigns'    => $campaigns,
            'labs'         => $labs,
            'filterLabId'  => $filterLabId,
            'tab'          => $tab,
        ]);
    }

    // ---------------------------------------------------------------
    // Student: Detail campaign
    // ---------------------------------------------------------------
    public function detail($publicId)
    {
        if (! activeGroupCan('oprek.apply')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak dapat mengakses halaman ini.');
        }

        $campaign = $this->campaignModel->findByPublicId($publicId);
        if (! $campaign) {
            return redirect()->to('/oprek/browse')->with('error', 'Oprek tidak ditemukan.');
        }

        $components = model('App\Models\OprekSelectionComponentModel')
            ->getActiveByCampaign($campaign->id);

        // Check if already applied
        $studentId = auth()->id();
        $application = $this->applicationModel
            ->select('id, public_id, application_status, submitted_at')
            ->where('campaign_id', $campaign->id)
            ->where('student_id', $studentId)
            ->first();

        $isRegistrationOpen = $campaign->status === 'published'
            && (! $campaign->registration_end_at || $campaign->registration_end_at >= date('Y-m-d H:i:s'));

        return $this->renderView('oprek/registration/detail', [
            'title'             => 'Detail - ' . $campaign->period_name,
            'page_title'        => 'Detail Open Rekrutmen',
            'campaign'          => $campaign,
            'components'        => $components,
            'application'       => $application,
            'isRegistrationOpen' => $isRegistrationOpen,
        ]);
    }

    // ---------------------------------------------------------------
    // Student: Register for an oprek
    // ---------------------------------------------------------------
    public function register($publicId)
    {
        if (! activeGroupCan('oprek.apply')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak dapat mendaftar.');
        }

        $campaign = $this->campaignModel->findByPublicId($publicId);
        if (! $campaign) {
            return redirect()->to('/oprek/browse')->with('error', 'Oprek tidak ditemukan.');
        }

        if ($campaign->status !== 'published') {
            return redirect()->to('/oprek/browse')->with('error', 'Oprek tidak sedang aktif.');
        }

        // Check if already applied
        $studentId = auth()->id();
        $existing = $this->applicationModel
            ->where('campaign_id', $campaign->id)
            ->where('student_id', $studentId)
            ->first();

        if ($existing) {
            return redirect()->to('/oprek/my-applications')->with('info', 'Anda sudah mendaftar di oprek ini.');
        }

        return $this->renderView('oprek/registration/register', [
            'title'      => 'Daftar Oprek',
            'page_title' => 'Pendaftaran - ' . $campaign->period_name,
            'campaign'   => $campaign,
        ]);
    }

    public function storeRegistration($publicId)
    {
        if (! activeGroupCan('oprek.apply')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $campaign = $this->campaignModel->findByPublicId($publicId);
        if (! $campaign || $campaign->status !== 'published') {
            return redirect()->to('/oprek/browse')->with('error', 'Oprek tidak aktif.');
        }

        $campaignId  = $campaign->id;
        $studentId   = auth()->id();

        $existing = $this->applicationModel
            ->where('campaign_id', $campaignId)
            ->where('student_id', $studentId)
            ->first();

        if ($existing) {
            return redirect()->to('/oprek/my-applications')->with('info', 'Anda sudah mendaftar.');
        }

        // Check quota
        if ($campaign->quota) {
            $verifiedCount = $this->applicationModel->countVerifiedByCampaign($campaignId);
            if ($verifiedCount >= $campaign->quota) {
                return redirect()->to('/oprek/browse')->with('error', 'Kuota pendaftaran sudah terpenuhi.');
            }
        }

        // Validate form data
        $rules = [
            'full_name' => 'required|max_length[100]',
            'nim'       => 'required|max_length[30]',
            'prodi'     => 'required|max_length[100]',
            'semester'  => 'required|integer|greater_than[0]',
            'phone'     => 'required|max_length[20]',
            'motivation' => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Build form payload
        $formPayload = [
            'full_name'  => $this->request->getPost('full_name'),
            'nim'        => $this->request->getPost('nim'),
            'prodi'      => $this->request->getPost('prodi'),
            'semester'   => $this->request->getPost('semester'),
            'phone'      => $this->request->getPost('phone'),
            'motivation' => $this->request->getPost('motivation'),
            'ipk'        => $this->request->getPost('ipk'),
        ];

        $applicationId = $this->applicationModel->insert([
            'campaign_id'        => $campaignId,
            'student_id'         => $studentId,
            'form_payload'       => json_encode($formPayload),
            'application_status' => 'submitted',
            'submitted_at'       => date('Y-m-d H:i:s'),
        ]);

        // Upload documents
        $docTypes = ['cv' => 'CV', 'ktm' => 'KTM', 'khs' => 'KHS', 'commitment_letter' => 'Surat Pernyataan Komitmen'];
        $hasError = false;

        foreach ($docTypes as $key => $label) {
            $file = $this->request->getFile($key);
            if (! $file || ! $file->isValid()) {
                $hasError = true;
                continue;
            }

            if (! $file->isValid() || $file->hasMoved()) {
                continue;
            }

            $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
            $ext = strtolower($file->getClientExtension());
            if (! in_array($ext, $allowedExts)) {
                $hasError = true;
                continue;
            }

            if ($file->getSize() > 5 * 1024 * 1024) { // 5MB max
                $hasError = true;
                continue;
            }

            $newName = $key . '_' . $studentId . '_' . time() . '.' . $ext;
            $subfolder = 'oprek/' . $campaignId . '/' . $applicationId;
            $file->move(WRITEPATH . 'uploads/' . $subfolder, $newName);

            $this->documentModel->insert([
                'application_id' => $applicationId,
                'document_type'  => $key,
                'file_path'      => 'uploads/' . $subfolder . '/' . $newName,
                'file_name'      => $file->getClientName(),
                'mime_type'      => $file->getClientMimeType(),
                'file_size'      => $file->getSize(),
            ]);
        }

        $this->logModel->log($studentId, 'application.submitted', (int) $campaignId, (int) $applicationId);

        // Notify laboran assigned to this lab
        $student = auth()->user();
        notify_role('laboran', 'oprek.new_application', [
            'student_name'   => $student->username ?? 'Mahasiswa',
            'period_name'    => $campaign->period_name ?? 'Oprek',
            'lab_name'       => $campaign->lab_name ?? '-',
            'application_id' => $applicationId,
            'url'            => base_url('oprek/' . $campaignId),
        ]);

        if ($hasError) {
            return redirect()->to('/oprek/my-applications')->with('warning', 'Pendaftaran berhasil, tetapi ada beberapa dokumen yang gagal diunggah. Silakan periksa kembali.');
        }

        return redirect()->to('/oprek/my-applications')->with('success', 'Pendaftaran berhasil! Menunggu verifikasi berkas.');
    }

    // ---------------------------------------------------------------
    // Student: My Applications
    // ---------------------------------------------------------------
    public function myApplications()
    {
        $studentId = auth()->id();

        $applications = $this->applicationModel->getByStudent($studentId);

        return $this->renderView('oprek/registration/my_applications', [
            'title'        => 'Pendaftaran Saya',
            'page_title'   => 'Status Pendaftaran Oprek',
            'applications' => $applications,
        ]);
    }

    public function showApplication($publicId)
    {
        $application = $this->applicationModel->findByPublicId($publicId);
        if (! $application) {
            return redirect()->to('/oprek/my-applications')->with('error', 'Pendaftaran tidak ditemukan.');
        }

        // Ensure student can only see their own
        if ((int) $application->student_id !== auth()->id()) {
            return redirect()->to('/oprek/my-applications')->with('error', 'Akses ditolak.');
        }

        $applicationId = $application->id;
        $documents = $this->documentModel->getByApplication($applicationId);
        $logs      = $this->logModel->getByApplication($applicationId);

        return $this->renderView('oprek/registration/show_application', [
            'title'       => 'Detail Pendaftaran',
            'page_title'  => 'Detail Status Pendaftaran',
            'application' => $application,
            'documents'   => $documents,
            'logs'        => $logs,
        ]);
    }

    // ---------------------------------------------------------------
    // Student: Revise documents (when requested by laboran)
    // ---------------------------------------------------------------
    public function reviseDocument($publicId, $documentType)
    {
        $application = $this->applicationModel->findByPublicId($publicId);
        if (! $application || (int) $application->student_id !== auth()->id()) {
            return redirect()->to('/oprek/my-applications')->with('error', 'Akses ditolak.');
        }

        if ($application->application_status !== 'doc_revision') {
            return redirect()->to('/oprek/my-applications')->with('error', 'Tidak dalam status revisi.');
        }

        $applicationId = $application->id;
        $document = $this->documentModel->getByApplicationAndType($applicationId, $documentType);
        if (! $document) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }

        return $this->renderView('oprek/registration/revise_document', [
            'title'        => 'Revisi Dokumen',
            'page_title'   => 'Upload Ulang ' . ucfirst($documentType),
            'application'  => $application,
            'document'     => $document,
            'documentType' => $documentType,
        ]);
    }

    public function storeRevisedDocument($publicId, $documentType)
    {
        $application = $this->applicationModel->findByPublicId($publicId);
        if (! $application || (int) $application->student_id !== auth()->id()) {
            return redirect()->to('/oprek/my-applications')->with('error', 'Akses ditolak.');
        }

        $applicationId = $application->id;

        $file = $this->request->getFile('document_file');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid.');
        }

        $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, $allowedExts)) {
            return redirect()->back()->with('error', 'Format file tidak diizinkan.');
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return redirect()->back()->with('error', 'Ukuran file maksimal 5MB.');
        }

        $campaignId = $application->campaign_id;
        $newName = $documentType . '_' . auth()->id() . '_' . time() . '.' . $ext;
        $subfolder = 'oprek/' . $campaignId . '/' . $applicationId;
        $file->move(WRITEPATH . 'uploads/' . $subfolder, $newName);

        $existingDoc = $this->documentModel->getByApplicationAndType($applicationId, $documentType);
        if ($existingDoc) {
            // Delete old file
            $oldPath = WRITEPATH . $existingDoc->file_path;
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
            $this->documentModel->update($existingDoc->id, [
                'file_path'         => 'uploads/' . $subfolder . '/' . $newName,
                'file_name'         => $file->getClientName(),
                'mime_type'         => $file->getClientMimeType(),
                'file_size'         => $file->getSize(),
                'is_verified'       => 0,
                'verification_note' => null,
                'verified_by'       => null,
                'verified_at'       => null,
            ]);
        } else {
            $this->documentModel->insert([
                'application_id' => $applicationId,
                'document_type'  => $documentType,
                'file_path'      => 'uploads/' . $subfolder . '/' . $newName,
                'file_name'      => $file->getClientName(),
                'mime_type'      => $file->getClientMimeType(),
                'file_size'      => $file->getSize(),
            ]);
        }

        // Put back to submitted for re-verification
        $this->applicationModel->update($applicationId, ['application_status' => 'submitted']);
        $this->logModel->log(auth()->id(), 'doc.revised', (int) $campaignId, (int) $applicationId, [
            'document_type' => $documentType,
        ]);

        return redirect()->to('/oprek/my-applications/' . $application->public_id)->with('success', 'Dokumen berhasil diunggah ulang. Menunggu verifikasi ulang.');
    }
}
