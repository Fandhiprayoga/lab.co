<?php

namespace App\Controllers;

use App\Models\OprekCampaignModel;
use App\Models\OprekApplicationModel;
use App\Models\OprekSelectionComponentModel;
use App\Models\OprekComponentAssessorModel;
use App\Models\OprekScoreModel;
use App\Models\OprekFinalDecisionModel;
use App\Models\OprekOnboardingProfileModel;
use App\Models\OprekActivityLogModel;
use App\Models\UserLabAssignmentModel;

class OprekSelectionController extends BaseController
{
    protected OprekCampaignModel $campaignModel;
    protected OprekApplicationModel $applicationModel;
    protected OprekSelectionComponentModel $componentModel;
    protected OprekComponentAssessorModel $assessorModel;
    protected OprekScoreModel $scoreModel;
    protected OprekFinalDecisionModel $decisionModel;
    protected OprekOnboardingProfileModel $onboardingModel;
    protected OprekActivityLogModel $logModel;
    protected UserLabAssignmentModel $assignmentModel;

    public function __construct()
    {
        $this->campaignModel    = new OprekCampaignModel();
        $this->applicationModel = new OprekApplicationModel();
        $this->componentModel   = new OprekSelectionComponentModel();
        $this->assessorModel    = new OprekComponentAssessorModel();
        $this->scoreModel       = new OprekScoreModel();
        $this->decisionModel    = new OprekFinalDecisionModel();
        $this->onboardingModel  = new OprekOnboardingProfileModel();
        $this->logModel         = new OprekActivityLogModel();
        $this->assignmentModel  = new UserLabAssignmentModel();
    }

    private function guardSelection(): mixed
    {
        if (! activeGroupCan('oprek.manage') && ! activeGroupCan('oprek.scoring')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }
        return null;
    }

    // ---------------------------------------------------------------
    // Scoring Dashboard per Campaign
    // ---------------------------------------------------------------
    public function scoringDashboard($campaignId)
    {
        if ($guard = $this->guardSelection()) return $guard;

        $campaign = $this->campaignModel->getWithDetails($campaignId);
        if (! $campaign) {
            return redirect()->to('/oprek')->with('error', 'Oprek tidak ditemukan.');
        }

        $components   = $this->componentModel->getActiveByCampaign($campaignId);
        $applications = $this->applicationModel->getByCampaign($campaignId);

        // Filter only verified / in_selection candidates
        $applications = array_filter($applications, fn($a) =>
            in_array($a->application_status, ['doc_verified', 'in_selection', 'accepted', 'onboarding_pending', 'onboarding_complete'])
        );

        $userId = auth()->id();
        $isLaboran = activeGroupCan('oprek.manage');

        // If asisten, only show candidates they can score
        if (! $isLaboran) {
            $assessorComponents = $this->assessorModel
                ->where('assessor_user_id', $userId)
                ->findAll();
            $assessorComponentIds = array_map(fn($a) => $a->component_id, $assessorComponents);
        }

        // Build scoring grid
        $scoringData = [];
        foreach ($applications as $app) {
            $scores = $this->scoreModel->getByApplication($app->id);
            $scoreMap = [];
            foreach ($scores as $s) {
                $key = $s->component_id . '_' . $s->assessor_user_id;
                $scoreMap[$key] = $s;
            }

            $componentScores = [];
            foreach ($components as $comp) {
                $componentScores[$comp->id] = [
                    'component' => $comp,
                    'my_score'  => $scoreMap[$comp->id . '_' . $userId] ?? null,
                    'all_scores' => array_filter($scores, fn($s) => $s->component_id == $comp->id),
                ];
            }

            $finalScore = $this->scoreModel->getFinalScore($app->id);

            $scoringData[] = [
                'application'     => $app,
                'componentScores' => $componentScores,
                'finalScore'      => $finalScore,
            ];
        }

        $rankings = $this->scoreModel->getRankingsByCampaign($campaignId);

        return $this->renderView('oprek/scoring/dashboard', [
            'title'       => 'Penilaian Seleksi',
            'page_title'  => 'Penilaian - ' . $campaign->period_name,
            'campaign'    => $campaign,
            'components'  => $components,
            'scoringData' => $scoringData,
            'rankings'    => $rankings,
            'isLaboran'   => $isLaboran,
        ]);
    }

    // ---------------------------------------------------------------
    // Score an individual application (assessor view)
    // ---------------------------------------------------------------
    public function scoreApplication($publicId)
    {
        if ($guard = $this->guardSelection()) return $guard;

        $application = $this->applicationModel->findByPublicId($publicId);
        if (! $application) {
            return redirect()->back()->with('error', 'Pendaftaran tidak ditemukan.');
        }

        $applicationId = $application->id;

        $campaign    = $this->campaignModel->getWithDetails($application->campaign_id);
        $components  = $this->componentModel->getActiveByCampaign($application->campaign_id);
        $userId      = auth()->id();
        $isLaboran   = activeGroupCan('oprek.manage');

        // Filter components assessor can score
        if (! $isLaboran) {
            $assessorComponents = $this->assessorModel
                ->where('assessor_user_id', $userId)
                ->findAll();
            $assessorComponentIds = array_map(fn($a) => $a->component_id, $assessorComponents);
            $components = array_filter($components, fn($c) => in_array($c->id, $assessorComponentIds));
        }

        $existingScores = [];
        foreach ($components as $comp) {
            $score = $this->scoreModel
                ->where('application_id', $applicationId)
                ->where('component_id', $comp->id)
                ->where('assessor_user_id', $userId)
                ->first();
            $existingScores[$comp->id] = $score;
        }

        return $this->renderView('oprek/scoring/score_form', [
            'title'           => 'Input Nilai',
            'page_title'      => 'Penilaian Kandidat',
            'application'     => $application,
            'campaign'        => $campaign,
            'components'      => $components,
            'existingScores'  => $existingScores,
        ]);
    }

    public function storeScore($publicId)
    {
        if ($guard = $this->guardSelection()) return $guard;

        $application = $this->applicationModel->findByPublicId($publicId);
        if (! $application) {
            return redirect()->back()->with('error', 'Pendaftaran tidak ditemukan.');
        }

        $applicationId = $application->id;

        $userId     = auth()->id();
        $scoresData = $this->request->getPost('scores') ?? [];
        $notesData  = $this->request->getPost('notes') ?? [];
        $now        = date('Y-m-d H:i:s');

        foreach ($scoresData as $componentId => $scoreValue) {
            if ($scoreValue === '' || $scoreValue === null) continue;

            $existing = $this->scoreModel
                ->where('application_id', $applicationId)
                ->where('component_id', $componentId)
                ->where('assessor_user_id', $userId)
                ->first();

            $data = [
                'application_id'   => $applicationId,
                'component_id'     => $componentId,
                'assessor_user_id' => $userId,
                'score_value'      => $scoreValue,
                'note'             => $notesData[$componentId] ?? null,
                'scored_at'        => $now,
            ];

            if ($existing) {
                $this->scoreModel->update($existing->id, $data);
            } else {
                $data['created_at'] = $now;
                $this->scoreModel->insert($data);
            }
        }

        // Mark application as in_selection
        if ($application->application_status === 'doc_verified') {
            $this->applicationModel->update($applicationId, ['application_status' => 'in_selection']);
        }

        $this->logModel->log($userId, 'score.submitted', (int) $application->campaign_id, (int) $applicationId);

        return redirect()->to('/oprek/' . $application->campaign_id . '/scoring')->with('success', 'Nilai berhasil disimpan.');
    }

    // ---------------------------------------------------------------
    // Final Decision
    // ---------------------------------------------------------------
    public function finalize($campaignId)
    {
        if ($guard = $this->guardSelection()) return $guard;

        if (! activeGroupCan('oprek.manage')) {
            return redirect()->back()->with('error', 'Hanya laboran yang dapat melakukan verifikasi akhir.');
        }

        $campaign = $this->campaignModel->getWithDetails($campaignId);
        if (! $campaign) {
            return redirect()->to('/oprek')->with('error', 'Oprek tidak ditemukan.');
        }

        $applications = array_filter(
            $this->applicationModel->getByCampaign($campaignId),
            fn($a) => in_array($a->application_status, ['doc_verified', 'in_selection'])
        );

        // Get scores & rankings
        $rankings = $this->scoreModel->getRankingsByCampaign($campaignId);

        $appScores = [];
        foreach ($applications as $app) {
            $finalScore = $this->scoreModel->getFinalScore($app->id);
            $appScores[$app->id] = $finalScore;
        }

        return $this->renderView('oprek/scoring/finalize', [
            'title'        => 'Verifikasi Akhir',
            'page_title'   => 'Verifikasi Akhir - ' . $campaign->period_name,
            'campaign'     => $campaign,
            'applications' => $applications,
            'appScores'    => $appScores,
            'rankings'     => $rankings,
        ]);
    }

    public function storeFinalDecision($campaignId)
    {
        if (! activeGroupCan('oprek.manage')) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $decisions = $this->request->getPost('decisions') ?? [];
        $notes     = $this->request->getPost('decision_notes') ?? [];
        $now       = date('Y-m-d H:i:s');
        $userId    = auth()->id();

        foreach ($decisions as $applicationId => $status) {
            if (! in_array($status, ['accepted', 'rejected', 'waitlist'])) continue;

            $application = $this->applicationModel->find($applicationId);
            if (! $application) continue;

            $finalScore = $this->scoreModel->getFinalScore($applicationId);

            $existing = $this->decisionModel->getByApplication($applicationId);
            if ($existing) {
                $this->decisionModel->update($existing->id, [
                    'decision_status' => $status,
                    'final_score'     => $finalScore,
                    'decided_by'      => $userId,
                    'decision_note'   => $notes[$applicationId] ?? null,
                    'decided_at'      => $now,
                ]);
            } else {
                $this->decisionModel->insert([
                    'application_id'  => $applicationId,
                    'decision_status' => $status,
                    'final_score'     => $finalScore,
                    'decided_by'      => $userId,
                    'decision_note'   => $notes[$applicationId] ?? null,
                    'decided_at'      => $now,
                ]);
            }

            // Update application status
            $newStatus = match ($status) {
                'accepted' => 'accepted',
                'rejected' => 'failed',
                'waitlist' => 'failed',
                default    => $application->application_status,
            };

            $this->applicationModel->update($applicationId, ['application_status' => $newStatus]);

            // Create onboarding profile for accepted
            if ($status === 'accepted') {
                $existingProfile = $this->onboardingModel->getByApplication($applicationId);
                if (! $existingProfile) {
                    $this->onboardingModel->insert([
                        'application_id'    => $applicationId,
                        'onboarding_status' => 'pending',
                    ]);
                    $this->applicationModel->update($applicationId, ['application_status' => 'accepted']);
                }
            }

            // Notify student
            $notifType = $status === 'accepted' ? 'oprek.application_accepted' : 'oprek.application_rejected';
            $campaignObj = $this->campaignModel
                ->select('oprek_campaigns.period_name, labs.name as lab_name')
                ->join('labs', 'labs.id = oprek_campaigns.lab_id')
                ->where('oprek_campaigns.id', $campaignId)
                ->first();
            send_notification($application->student_id, $notifType, [
                'application_id' => $applicationId,
                'period_name'    => $campaignObj->period_name ?? 'Oprek',
                'lab_name'       => $campaignObj->lab_name ?? '-',
            ]);
        }

        $this->logModel->log($userId, 'final_decision.published', (int) $campaignId);
        $this->campaignModel->update($campaignId, ['status' => 'closed']);

        return redirect()->to('/oprek/' . $campaignId)->with('success', 'Verifikasi akhir berhasil disimpan. Hasil telah dipublikasikan.');
    }
}
