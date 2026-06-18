<?php

namespace App\Controllers;

use App\Models\OprekCampaignModel;
use App\Models\OprekApplicationModel;
use App\Models\OprekApplicationDocumentModel;
use App\Models\OprekSelectionComponentModel;
use App\Models\OprekComponentAssessorModel;
use App\Models\OprekScoreModel;
use App\Models\OprekFinalDecisionModel;
use App\Models\OprekOnboardingProfileModel;
use App\Models\OprekActivityLogModel;
use App\Models\AcademicYearModel;
use App\Models\LabModel;
use App\Models\UserLabAssignmentModel;

class OprekController extends BaseController
{
    protected OprekCampaignModel $campaignModel;
    protected OprekApplicationModel $applicationModel;
    protected OprekApplicationDocumentModel $documentModel;
    protected OprekSelectionComponentModel $componentModel;
    protected OprekComponentAssessorModel $assessorModel;
    protected OprekScoreModel $scoreModel;
    protected OprekFinalDecisionModel $decisionModel;
    protected OprekOnboardingProfileModel $onboardingModel;
    protected OprekActivityLogModel $logModel;
    protected AcademicYearModel $academicYearModel;
    protected LabModel $labModel;
    protected UserLabAssignmentModel $assignmentModel;

    public function __construct()
    {
        $this->campaignModel    = new OprekCampaignModel();
        $this->applicationModel = new OprekApplicationModel();
        $this->documentModel    = new OprekApplicationDocumentModel();
        $this->componentModel   = new OprekSelectionComponentModel();
        $this->assessorModel    = new OprekComponentAssessorModel();
        $this->scoreModel       = new OprekScoreModel();
        $this->decisionModel    = new OprekFinalDecisionModel();
        $this->onboardingModel  = new OprekOnboardingProfileModel();
        $this->logModel         = new OprekActivityLogModel();
        $this->academicYearModel = new AcademicYearModel();
        $this->labModel         = new LabModel();
        $this->assignmentModel  = new UserLabAssignmentModel();
    }

    // ---------------------------------------------------------------
    // Common Guards
    // ---------------------------------------------------------------
    private function guardLaboran(): mixed
    {
        if (! activeGroupCan('oprek.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke menu ini.');
        }
        return null;
    }

    private function guardAssessor(): mixed
    {
        if (! activeGroupCan('oprek.manage') && ! activeGroupCan('oprek.scoring')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses.');
        }
        return null;
    }

    /** Get lab IDs assigned to current user (laboran/asisten) */
    private function getUserLabIds(): array
    {
        return $this->assignmentModel->getLabIdsByUser(auth()->id());
    }

    /** Upload poster file. Returns relative path or null. */
    private function uploadPoster(): ?string
    {
        $file = $this->request->getFile('poster');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, $allowedExts)) {
            return null;
        }

        if ($file->getSize() > 2 * 1024 * 1024) { // 2MB max
            return null;
        }

        $newName = 'poster_' . time() . '.' . $ext;
        $dir = FCPATH . 'uploads/oprek/posters';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $file->move($dir, $newName);
        return 'uploads/oprek/posters/' . $newName;
    }

    // ---------------------------------------------------------------
    // Campaign CRUD
    // ---------------------------------------------------------------
    public function index()
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $activeLabId = (int) session()->get('active_lab_id');

        if ($activeLabId > 0) {
            // Filter by active lab session
            $userLabIds = $this->getUserLabIds();
            if (in_array($activeLabId, $userLabIds, true)) {
                $campaigns = $this->campaignModel->getByLab($activeLabId);
            } else {
                $campaigns = [];
            }
            $activeLab = $this->labModel->find($activeLabId);
        } else {
            // Show all user's assigned labs
            $labIds   = $this->getUserLabIds();
            $campaigns = [];
            if (! empty($labIds)) {
                foreach ($labIds as $labId) {
                    $list = $this->campaignModel->getByLab($labId);
                    $campaigns = array_merge($campaigns, $list);
                }
            }
            $activeLab = null;
        }

        return $this->renderView('oprek/campaigns/index', [
            'title'      => 'Open Rekrutmen Asisten',
            'page_title' => 'Daftar Oprek',
            'campaigns'  => $campaigns,
            'activeLab'  => $activeLab,
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $labIds       = $this->getUserLabIds();
        $labs         = ! empty($labIds) ? $this->labModel->whereIn('id', $labIds)->findAll() : [];
        $activeYear   = $this->academicYearModel->getActive();

        if (! $activeYear) {
            return redirect()->to('/oprek')->with('error', 'Tidak ada tahun akademik aktif. Silakan aktifkan tahun akademik terlebih dahulu.');
        }

        return $this->renderView('oprek/campaigns/create', [
            'title'       => 'Buka Oprek Baru',
            'page_title'  => 'Buka Open Rekrutmen Asisten',
            'labs'        => $labs,
            'activeYear'  => $activeYear,
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $activeYear = $this->academicYearModel->getActive();
        if (! $activeYear) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada tahun akademik aktif.');
        }

        $rules = [
            'lab_id'           => 'required|integer',
            'period_name'      => 'required|max_length[100]',
            'description'      => 'permit_empty|max_length[5000]',
            'requirements'     => 'permit_empty|max_length[5000]',
            'registration_start_at' => 'permit_empty|valid_date',
            'registration_end_at'   => 'permit_empty|valid_date',
            'quota'            => 'permit_empty|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Verify user is assigned to this lab
        $labIds = $this->getUserLabIds();
        if (! in_array((int) $this->request->getPost('lab_id'), $labIds)) {
            return redirect()->back()->withInput()->with('error', 'Anda tidak ditugaskan ke lab tersebut.');
        }

        $this->campaignModel->insert([
            'lab_id'                => $this->request->getPost('lab_id'),
            'academic_year_id'      => $activeYear->id,
            'period_name'           => $this->request->getPost('period_name'),
            'description'           => $this->request->getPost('description'),
            'requirements'          => $this->request->getPost('requirements'),
            'poster'                => $this->uploadPoster(),
            'registration_start_at' => $this->request->getPost('registration_start_at'),
            'registration_end_at'   => $this->request->getPost('registration_end_at'),
            'quota'                 => $this->request->getPost('quota') ?: null,
            'status'                => 'draft',
            'created_by'            => auth()->id(),
        ]);

        $campaignId = $this->campaignModel->getInsertID();

        $this->logModel->log(auth()->id(), 'campaign.created', (int) $campaignId);

        return redirect()->to('/oprek/' . $campaignId)->with('success', 'Oprek berhasil dibuat.');
    }

    public function show($id)
    {
        if ($guard = $this->guardAssessor()) return $guard;

        $campaign = $this->campaignModel->getWithDetails($id);
        if (! $campaign) {
            return redirect()->to('/oprek')->with('error', 'Oprek tidak ditemukan.');
        }

        $applications  = $this->applicationModel->getByCampaign($id);
        $components    = $this->componentModel->getByCampaign($id);
        $activityLogs  = $this->logModel->getByCampaign($id);

        return $this->renderView('oprek/campaigns/show', [
            'title'         => $campaign->period_name,
            'page_title'    => 'Detail Oprek: ' . $campaign->period_name,
            'campaign'      => $campaign,
            'applications'  => $applications,
            'components'    => $components,
            'activityLogs'  => $activityLogs,
        ]);
    }

    public function edit($id)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $campaign = $this->campaignModel->find($id);
        if (! $campaign) {
            return redirect()->to('/oprek')->with('error', 'Oprek tidak ditemukan.');
        }

        $labIds       = $this->getUserLabIds();
        $labs         = ! empty($labIds) ? $this->labModel->whereIn('id', $labIds)->findAll() : [];
        $academicYears = $this->academicYearModel->orderBy('kode_ta', 'DESC')->findAll();

        return $this->renderView('oprek/campaigns/edit', [
            'title'         => 'Edit Oprek',
            'page_title'    => 'Edit Open Rekrutmen Asisten',
            'campaign'      => $campaign,
            'labs'          => $labs,
            'academicYears' => $academicYears,
        ]);
    }

    public function update($id)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $campaign = $this->campaignModel->find($id);
        if (! $campaign) {
            return redirect()->to('/oprek')->with('error', 'Oprek tidak ditemukan.');
        }

        $rules = [
            'lab_id'               => 'required|integer',
            'academic_year_id'     => 'required|integer',
            'period_name'          => 'required|max_length[100]',
            'description'          => 'permit_empty|max_length[5000]',
            'requirements'         => 'permit_empty|max_length[5000]',
            'registration_start_at' => 'permit_empty|valid_date',
            'registration_end_at'   => 'permit_empty|valid_date',
            'quota'                => 'permit_empty|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'lab_id'                => $this->request->getPost('lab_id'),
            'academic_year_id'      => $this->request->getPost('academic_year_id'),
            'period_name'           => $this->request->getPost('period_name'),
            'description'           => $this->request->getPost('description'),
            'requirements'          => $this->request->getPost('requirements'),
            'registration_start_at' => $this->request->getPost('registration_start_at'),
            'registration_end_at'   => $this->request->getPost('registration_end_at'),
            'quota'                 => $this->request->getPost('quota') ?: null,
        ];

        $posterPath = $this->uploadPoster();
        if ($posterPath !== null) {
            // Delete old poster if exists
            if (! empty($campaign->poster)) {
                $oldPath = FCPATH . $campaign->poster;
                if (is_file($oldPath)) unlink($oldPath);
            }
            $updateData['poster'] = $posterPath;
        }

        $this->campaignModel->update($id, $updateData);

        $this->logModel->log(auth()->id(), 'campaign.updated', (int) $id);

        return redirect()->to('/oprek/' . $id)->with('success', 'Oprek berhasil diperbarui.');
    }

    public function publish($id)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $campaign = $this->campaignModel->find($id);
        if (! $campaign) {
            return redirect()->to('/oprek')->with('error', 'Oprek tidak ditemukan.');
        }

        $this->campaignModel->update($id, ['status' => 'published']);
        $this->logModel->log(auth()->id(), 'campaign.published', (int) $id);

        return redirect()->to('/oprek/' . $id)->with('success', 'Oprek berhasil dipublikasikan.');
    }

    public function close($id)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $campaign = $this->campaignModel->find($id);
        if (! $campaign) {
            return redirect()->to('/oprek')->with('error', 'Oprek tidak ditemukan.');
        }

        $this->campaignModel->update($id, ['status' => 'closed']);
        $this->logModel->log(auth()->id(), 'campaign.closed', (int) $id);

        return redirect()->to('/oprek/' . $id)->with('success', 'Oprek berhasil ditutup.');
    }

    public function archive($id)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $campaign = $this->campaignModel->find($id);
        if (! $campaign) {
            return redirect()->to('/oprek')->with('error', 'Oprek tidak ditemukan.');
        }

        $this->campaignModel->update($id, ['status' => 'archived']);
        $this->logModel->log(auth()->id(), 'campaign.archived', (int) $id);

        return redirect()->to('/oprek')->with('success', 'Oprek berhasil diarsipkan.');
    }

    // ---------------------------------------------------------------
    // Verification: Berkas Pendaftaran
    // ---------------------------------------------------------------
    public function verifyDocuments($publicId)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $application = $this->applicationModel->findByPublicId($publicId);
        if (! $application) {
            return redirect()->back()->with('error', 'Pendaftaran tidak ditemukan.');
        }

        $applicationId = $application->id;
        $documents = $this->documentModel->getByApplication($applicationId);

        return $this->renderView('oprek/verification/documents', [
            'title'       => 'Verifikasi Berkas',
            'page_title'  => 'Verifikasi Berkas Pendaftar',
            'application' => $application,
            'documents'   => $documents,
        ]);
    }

    public function verifyDocumentAction($documentId)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $document = $this->documentModel->find($documentId);
        if (! $document) {
            return redirect()->back()->with('error', 'Dokumen tidak ditemukan.');
        }

        $action = $this->request->getPost('action'); // approve / reject / revision
        $note   = $this->request->getPost('verification_note');

        $update = [
            'is_verified'       => $action === 'approve' ? 1 : 0,
            'verification_note' => $note,
            'verified_by'       => auth()->id(),
            'verified_at'       => date('Y-m-d H:i:s'),
        ];

        $this->documentModel->update($documentId, $update);

        // Check if all required docs are verified
        $applicationId = $document->application_id;
        $unverifiedCount = $this->documentModel->countUnverified($applicationId);

        if ($unverifiedCount === 0) {
            $this->applicationModel->update($applicationId, ['application_status' => 'doc_verified']);
            $this->logModel->log(auth()->id(), 'docs.all_verified', null, (int) $applicationId);
            // Notify student
            $application = $this->applicationModel
                ->select('oprek_applications.*, oc.period_name')
                ->join('oprek_campaigns oc', 'oc.id = oprek_applications.campaign_id')
                ->where('oprek_applications.id', $applicationId)
                ->first();
            send_notification($document->application->student_id ?? 0, 'oprek.doc_verified', [
                'application_id' => $applicationId,
                'period_name'    => $application->period_name ?? 'Oprek',
            ]);
        } elseif ($action === 'reject') {
            // If any doc rejected, whole application rejected
            $this->applicationModel->update($applicationId, ['application_status' => 'doc_rejected']);
            $this->logModel->log(auth()->id(), 'docs.rejected', null, (int) $applicationId);
        } elseif ($action === 'revision') {
            $this->applicationModel->update($applicationId, ['application_status' => 'doc_revision']);
            $this->logModel->log(auth()->id(), 'docs.revision_requested', null, (int) $applicationId);
        }

        $this->logModel->log(auth()->id(), "doc.{$action}", null, (int) $applicationId, [
            'document_id' => $documentId,
            'document_type' => $document->document_type,
        ]);

        return redirect()->back()->with('success', 'Verifikasi berhasil disimpan.');
    }

    // ---------------------------------------------------------------
    // Selection Components Builder
    // ---------------------------------------------------------------
    public function components($campaignId)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $campaign = $this->campaignModel->find($campaignId);
        if (! $campaign) {
            return redirect()->to('/oprek')->with('error', 'Oprek tidak ditemukan.');
        }

        $components = $this->componentModel->getByCampaign($campaignId);
        $totalWeight = $this->componentModel->getTotalWeight($campaignId);

        return $this->renderView('oprek/components/index', [
            'title'       => 'Komponen Seleksi',
            'page_title'  => 'Atur Komponen Seleksi - ' . $campaign->period_name,
            'campaign'    => $campaign,
            'components'  => $components,
            'totalWeight' => $totalWeight,
        ]);
    }

    public function storeComponent($campaignId)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $rules = [
            'component_name'    => 'required|max_length[100]',
            'component_key'     => 'required|max_length[50]|alpha_dash',
            'weight_percentage' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'max_score'         => 'required|decimal|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Check total weight doesn't exceed 100
        $currentTotal = $this->componentModel->getTotalWeight($campaignId);
        $newWeight = (float) $this->request->getPost('weight_percentage');
        if ($currentTotal + $newWeight > 100) {
            return redirect()->back()->withInput()->with('error', 'Total bobot tidak boleh melebihi 100%. Saat ini: ' . $currentTotal . '%');
        }

        $maxOrder = $this->componentModel
            ->where('campaign_id', $campaignId)
            ->selectMax('sort_order')
            ->first();

        $this->componentModel->insert([
            'campaign_id'       => $campaignId,
            'component_name'    => $this->request->getPost('component_name'),
            'component_key'     => $this->request->getPost('component_key'),
            'is_required'       => $this->request->getPost('is_required') ? 1 : 0,
            'is_active'         => 1,
            'weight_percentage' => $newWeight,
            'max_score'         => $this->request->getPost('max_score'),
            'sort_order'        => ($maxOrder->sort_order ?? 0) + 1,
        ]);

        $this->logModel->log(auth()->id(), 'component.created', (int) $campaignId);

        return redirect()->back()->with('success', 'Komponen seleksi berhasil ditambahkan.');
    }

    public function updateComponent($campaignId, $componentId)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $component = $this->componentModel->find($componentId);
        if (! $component) {
            return redirect()->back()->with('error', 'Komponen tidak ditemukan.');
        }

        $rules = [
            'component_name'    => 'required|max_length[100]',
            'weight_percentage' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'max_score'         => 'required|decimal|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Check total weight excluding current component
        $currentTotal = $this->componentModel->getTotalWeight($campaignId);
        $oldWeight = (float) $component->weight_percentage;
        $newWeight = (float) $this->request->getPost('weight_percentage');
        $adjustedTotal = $currentTotal - $oldWeight + $newWeight;
        if ($adjustedTotal > 100) {
            return redirect()->back()->withInput()->with('error', 'Total bobot tidak boleh melebihi 100%. Total setelah perubahan: ' . $adjustedTotal . '%');
        }

        $this->componentModel->update($componentId, [
            'component_name'    => $this->request->getPost('component_name'),
            'is_required'       => $this->request->getPost('is_required') ? 1 : 0,
            'is_active'         => $this->request->getPost('is_active') ? 1 : 0,
            'weight_percentage' => $newWeight,
            'max_score'         => $this->request->getPost('max_score'),
        ]);

        $this->logModel->log(auth()->id(), 'component.updated', (int) $campaignId);

        return redirect()->back()->with('success', 'Komponen seleksi berhasil diperbarui.');
    }

    public function deleteComponent($campaignId, $componentId)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $this->componentModel->delete($componentId);
        $this->logModel->log(auth()->id(), 'component.deleted', (int) $campaignId);

        return redirect()->back()->with('success', 'Komponen seleksi berhasil dihapus.');
    }

    public function toggleComponent($campaignId, $componentId)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $component = $this->componentModel->find($componentId);
        if (! $component) {
            return redirect()->back()->with('error', 'Komponen tidak ditemukan.');
        }

        $this->componentModel->update($componentId, [
            'is_active' => $component->is_active ? 0 : 1,
        ]);

        return redirect()->back()->with('success', 'Status komponen diperbarui.');
    }

    // ---------------------------------------------------------------
    // Assessors per Component
    // ---------------------------------------------------------------
    public function assessors($campaignId, $componentId)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $component  = $this->componentModel->find($componentId);
        $campaign   = $this->campaignModel->find($campaignId);
        $assessors  = $this->assessorModel->getByComponent($componentId);

        // Get lab_id from campaign to find laboran & asisten assigned to that lab
        $labAssignments = $this->assignmentModel
            ->select('user_lab_assignments.user_id, users.username, users.email')
            ->join('users', 'users.id = user_lab_assignments.user_id')
            ->where('lab_id', $campaign->lab_id)
            ->findAll();

        return $this->renderView('oprek/components/assessors', [
            'title'          => 'Atur Penilai',
            'page_title'     => 'Atur Penilai - ' . $component->component_name,
            'campaign'       => $campaign,
            'component'      => $component,
            'assessors'      => $assessors,
            'labAssignments' => $labAssignments,
        ]);
    }

    public function storeAssessors($campaignId, $componentId)
    {
        if ($guard = $this->guardLaboran()) return $guard;

        $assessorIds = $this->request->getPost('assessor_ids') ?? [];
        $roles       = $this->request->getPost('assessor_roles') ?? [];

        $assessors = [];
        foreach ($assessorIds as $i => $userId) {
            $assessors[] = [
                'user_id' => (int) $userId,
                'role'    => $roles[$i] ?? 'laboran',
            ];
        }

        $this->assessorModel->setAssessors($componentId, $assessors);
        $this->logModel->log(auth()->id(), 'assessors.updated', (int) $campaignId, null, [
            'component_id' => $componentId,
        ]);

        return redirect()->back()->with('success', 'Daftar penilai berhasil diperbarui.');
    }
}
