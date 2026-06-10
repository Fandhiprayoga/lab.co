<?php

namespace App\Models;

use CodeIgniter\Model;

class OprekScoreModel extends Model
{
    protected $table         = 'oprek_scores';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'application_id',
        'component_id',
        'assessor_user_id',
        'score_value',
        'note',
        'scored_at',
    ];

    public function getByApplication(int $applicationId): array
    {
        return $this->select('oprek_scores.*, osc.component_name, osc.weight_percentage, osc.max_score')
            ->join('oprek_selection_components osc', 'osc.id = oprek_scores.component_id')
            ->where('oprek_scores.application_id', $applicationId)
            ->orderBy('osc.sort_order', 'ASC')
            ->findAll();
    }

    public function getFinalScore(int $applicationId): ?float
    {
        $scores = $this->select('oprek_scores.score_value, osc.weight_percentage, osc.id as component_id')
            ->join('oprek_selection_components osc', 'osc.id = oprek_scores.component_id')
            ->where('oprek_scores.application_id', $applicationId)
            ->where('osc.is_active', 1)
            ->findAll();

        if (empty($scores)) {
            return null;
        }

        // Group by component and average per component
        $componentScores = [];
        foreach ($scores as $s) {
            $componentScores[$s->component_id]['sum']   = ($componentScores[$s->component_id]['sum'] ?? 0) + $s->score_value;
            $componentScores[$s->component_id]['count'] = ($componentScores[$s->component_id]['count'] ?? 0) + 1;
            $componentScores[$s->component_id]['weight'] = (float) $s->weight_percentage;
        }

        $totalWeight = 0;
        $weightedSum = 0;
        foreach ($componentScores as $cid => $data) {
            $avg = $data['sum'] / $data['count'];
            $weightedSum += $avg * ($data['weight'] / 100);
            $totalWeight += $data['weight'];
        }

        if ($totalWeight <= 0) {
            return null;
        }

        return round($weightedSum, 2);
    }

    public function getRankingsByCampaign(int $campaignId): array
    {
        $applications = (new OprekApplicationModel())
            ->where('campaign_id', $campaignId)
            ->whereIn('application_status', ['doc_verified', 'in_selection', 'accepted'])
            ->findAll();

        $rankings = [];
        foreach ($applications as $app) {
            $finalScore = $this->getFinalScore($app->id);
            $rankings[] = [
                'application_id' => $app->id,
                'student_id'     => $app->student_id,
                'final_score'    => $finalScore,
            ];
        }

        usort($rankings, function ($a, $b) {
            $scoreA = $a['final_score'] ?? 0;
            $scoreB = $b['final_score'] ?? 0;
            return $scoreB <=> $scoreA;
        });

        return $rankings;
    }
}
