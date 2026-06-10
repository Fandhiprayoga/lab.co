<?php

namespace App\Controllers;

use App\Models\OprekApplicationModel;
use App\Models\OprekCampaignModel;
use App\Models\OprekActivityLogModel;
use App\Models\UserLabAssignmentModel;
use App\Models\LabModel;

class OprekActivationController extends BaseController
{
    protected OprekApplicationModel $applicationModel;
    protected OprekCampaignModel $campaignModel;
    protected OprekActivityLogModel $logModel;
    protected UserLabAssignmentModel $assignmentModel;
    protected LabModel $labModel;

    public function __construct()
    {
        $this->applicationModel = new OprekApplicationModel();
        $this->campaignModel    = new OprekCampaignModel();
        $this->logModel         = new OprekActivityLogModel();
        $this->assignmentModel  = new UserLabAssignmentModel();
        $this->labModel         = new LabModel();
    }

    // ---------------------------------------------------------------
    // GET: Show activation form
    // ---------------------------------------------------------------
    public function activate($publicId)
    {
        if (! activeGroupCan('oprek.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $application = $this->applicationModel->findByPublicId($publicId);
        if (! $application) {
            return redirect()->back()->with('error', 'Pendaftaran tidak ditemukan.');
        }

        if ($application->application_status !== 'onboarding_complete') {
            return redirect()->back()->with('error', 'Pendaftar belum menyelesaikan onboarding.');
        }

        $campaign    = $this->campaignModel->getWithDetails($application->campaign_id);
        $userId      = (int) $application->student_id;

        // Check current roles
        $userProvider = auth()->getProvider();
        $user         = $userProvider->findById($userId);
        $userGroups   = $user ? $user->getGroups() : [];
        $hasAsisten   = in_array('asisten', $userGroups, true);

        // Check current lab assignments
        $assignedLabs    = $this->assignmentModel->getLabIdsByUser($userId);
        $campaignLabId   = (int) ($campaign->lab_id ?? 0);

        // All active labs for multi-select
        $allLabs = $this->labModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        // Pre-select campaign lab + existing assignments
        $preselectedIds = array_unique(array_merge([$campaignLabId], $assignedLabs));
        // Remove 0 if campaign lab_id is null
        $preselectedIds = array_filter($preselectedIds, fn($id) => $id > 0);

        return $this->renderView('oprek/activation/activate', [
            'title'          => 'Aktivasi Asisten',
            'page_title'     => 'Aktivasi Asisten Lab',
            'application'    => $application,
            'campaign'       => $campaign,
            'hasAsisten'     => $hasAsisten,
            'userGroups'     => $userGroups,
            'assignedLabs'   => $assignedLabs,
            'allLabs'        => $allLabs,
            'preselectedIds' => $preselectedIds,
            'campaignLabId'  => $campaignLabId,
        ]);
    }

    // ---------------------------------------------------------------
    // POST: Execute activation
    // ---------------------------------------------------------------
    public function storeActivation($publicId)
    {
        if (! activeGroupCan('oprek.manage')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $application = $this->applicationModel->findByPublicId($publicId);
        if (! $application) {
            return redirect()->back()->with('error', 'Pendaftaran tidak ditemukan.');
        }

        if ($application->application_status !== 'onboarding_complete') {
            return redirect()->back()->with('error', 'Pendaftar belum menyelesaikan onboarding.');
        }

        $labIds = $this->request->getPost('lab_ids') ?? [];
        $labIds = array_map('intval', $labIds);
        $labIds = array_filter($labIds, fn($id) => $id > 0);

        $applicationId = (int) $application->id;
        $campaignId    = (int) $application->campaign_id;
        $userId        = (int) $application->student_id;

        // --- Add role "asisten" ---
        $userProvider = auth()->getProvider();
        $user         = $userProvider->findById($userId);
        if ($user && ! in_array('asisten', $user->getGroups(), true)) {
            $user->addGroup('asisten');
        }

        // --- Assign labs ---
        if (! empty($labIds)) {
            $this->assignmentModel->assignLabs($userId, $labIds);
        }

        // --- Update application status ---
        $this->applicationModel->update($applicationId, ['application_status' => 'activated']);

        // --- Log ---
        $this->logModel->log(auth()->id(), 'activation.completed', $campaignId, $applicationId, [
            'lab_ids' => $labIds,
        ]);

        // --- Notify student ---
        $campaignObj = $this->campaignModel
            ->select('oprek_campaigns.period_name, labs.name as lab_name')
            ->join('labs', 'labs.id = oprek_campaigns.lab_id')
            ->where('oprek_campaigns.id', $campaignId)
            ->first();

        send_notification($userId, 'oprek.assistant_activated', [
            'application_id' => $applicationId,
            'period_name'    => $campaignObj->period_name ?? 'Oprek',
            'lab_name'       => $campaignObj->lab_name ?? '-',
        ]);

        return redirect()->to('/oprek/' . $campaignId)->with('success', 'Asisten berhasil diaktivasi. Role asisten dan lab telah ditetapkan.');
    }
}
