<?php

namespace App\Controllers;

use App\Models\LabModel;
use App\Models\LabVisitModel;

class LabVisitController extends BaseController
{
    protected LabVisitModel $visitModel;
    protected LabModel $labModel;

    public function __construct()
    {
        $this->visitModel = new LabVisitModel();
        $this->labModel   = new LabModel();
    }

    /**
     * Daftar semua kunjungan (seluruh lab).
     */
    public function index()
    {
        if (! activeGroupCan('visits.list')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke data kunjungan.');
        }

        $labs = $this->labModel->where('deleted_at IS NULL')->orderBy('name', 'ASC')->findAll();

        // Stat cards
        $today     = date('Y-m-d');
        $todayTotal = $this->visitModel->db->table('lab_visits')
            ->where('DATE(checked_in_at)', $today)->countAllResults();
        $nowInside = $this->visitModel->db->table('lab_visits')
            ->where('DATE(checked_in_at)', $today)
            ->where('checked_out_at IS NULL')->countAllResults();

        return $this->renderView('admin/visits/index', [
            'title'      => 'Buku Kunjungan',
            'page_title' => 'Buku Kunjungan Lab',
            'labs'       => $labs,
            'todayTotal' => $todayTotal,
            'nowInside'  => $nowInside,
        ]);
    }

    /**
     * Server-side DataTables endpoint: GET /admin/visits/datatable
     */
    public function datatable()
    {
        if (! activeGroupCan('visits.list')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $req    = $this->request;
        $draw   = (int) $req->getGet('draw');
        $start  = max(0, (int) $req->getGet('start'));
        $length = (int) $req->getGet('length');
        if ($length <= 0) { $length = 10; }

        $search  = (string) ($req->getGet('search')['value'] ?? '');
        $orderCol = (int) ($req->getGet('order')[0]['column'] ?? 5);
        $orderDir = strtolower((string) ($req->getGet('order')[0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

        // Custom filters
        $filterLabId    = $req->getGet('filter_lab_id');
        $filterDateFrom = $req->getGet('filter_date_from');
        $filterDateTo   = $req->getGet('filter_date_to');
        $filterStatus   = $req->getGet('filter_status');

        // Orderable columns map (index => sql expression)
        $colMap = [
            1 => 'l.name',
            2 => 'lv.visitor_name',
            3 => 'lv.visitor_institution',
            4 => 'lv.purpose',
            5 => 'lv.checked_in_at',
            6 => 'lv.checked_out_at',
        ];
        $orderField = $colMap[$orderCol] ?? 'lv.checked_in_at';

        $db = db_connect();

        $base = $db->table('lab_visits lv')
            ->select('lv.*, l.name AS lab_name, l.code AS lab_code')
            ->join('labs l', 'l.id = lv.lab_id', 'left');

        $recordsTotal = (clone $base)->countAllResults(false);

        if ($search !== '') {
            $base->groupStart()
                ->like('lv.visitor_name', $search)
                ->orLike('lv.visitor_institution', $search)
                ->orLike('l.name', $search)
                ->groupEnd();
        }
        if (! empty($filterLabId)) {
            $base->where('lv.lab_id', (int) $filterLabId);
        }
        if (! empty($filterDateFrom)) {
            $base->where('DATE(lv.checked_in_at) >=', $filterDateFrom);
        }
        if (! empty($filterDateTo)) {
            $base->where('DATE(lv.checked_in_at) <=', $filterDateTo);
        }
        if ($filterStatus === 'checkedin') {
            $base->where('lv.checked_out_at IS NULL');
        } elseif ($filterStatus === 'checkedout') {
            $base->where('lv.checked_out_at IS NOT NULL');
        }

        $recordsFiltered = (clone $base)->countAllResults(false);

        $rows = $base->orderBy($orderField, $orderDir)->limit($length, $start)->get()->getResultArray();

        $data = [];
        foreach ($rows as $v) {
            $isIn = empty($v['checked_out_at']);
            if (! $isIn) {
                $s = strtotime($v['checked_out_at']) - strtotime($v['checked_in_at']);
                $h = floor($s / 3600); $m = floor(($s % 3600) / 60);
                $dur = $h > 0 ? "{$h}j {$m}m" : "{$m}m";
            } else {
                $s = time() - strtotime($v['checked_in_at']);
                $h = floor($s / 3600); $m = floor(($s % 3600) / 60);
                $dur = ($h > 0 ? "{$h}j " : '') . "{$m}m";
            }

            $purposeLabels = $this->visitModel->purposeLabels;
            $purposeLabel  = $purposeLabels[$v['purpose']] ?? $v['purpose'];
            $purposeNote   = ! empty($v['purpose_note']) ? '<br><small class="text-muted">' . esc($v['purpose_note']) . '</small>' : '';

            $labHtml = '<span class="font-weight-medium">' . esc($v['lab_name'] ?? '-') . '</span>'
                . (! empty($v['lab_code']) ? '<br><small class="text-muted">' . esc($v['lab_code']) . '</small>' : '');

            $statusBadge = $isIn
                ? '<span class="badge badge-success">Di Dalam</span>'
                : '<span class="badge badge-secondary">Selesai</span>';

            $durHtml = '<span class="small ' . ($isIn ? 'text-success font-weight-medium' : 'text-muted') . '">' . esc($dur)
                . ($isIn ? ' <i class="fas fa-circle text-success" style="font-size:7px;vertical-align:middle;"></i>' : '') . '</span>';

            $checkinFmt  = date('d/m/Y H:i', strtotime($v['checked_in_at']));
            $checkoutFmt = $isIn ? '<span class="text-muted">—</span>' : date('d/m/Y H:i', strtotime($v['checked_out_at']));

            $actionBtn = $isIn
                ? '<button class="btn btn-sm btn-warning force-checkout-btn" data-id="' . (int) $v['id'] . '" data-name="' . esc($v['visitor_name'], 'attr') . '">'
                  . '<i class="fas fa-sign-out-alt mr-1"></i>Checkout</button>'
                : '<span class="text-muted">—</span>';

            $data[] = [
                '',   // row number (client-side)
                $labHtml,
                esc($v['visitor_name']),
                esc($v['visitor_institution'] ?? '—'),
                '<span class="badge badge-light">' . esc($purposeLabel) . '</span>' . $purposeNote,
                '<span class="small">' . esc($checkinFmt) . '</span>',
                '<span class="small">' . $checkoutFmt . '</span>',
                $durHtml,
                $statusBadge,
                $actionBtn,
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    /**
     * Force checkout: isi checked_out_at = now() untuk kunjungan yang belum checkout.
     * POST admin/visits/(:num)/force-checkout
     */
    public function forceCheckout(int $id)
    {
        if (! activeGroupCan('visits.list')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $visit = $this->visitModel->find($id);
        if (! $visit) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Data kunjungan tidak ditemukan.']);
        }
        if (! empty($visit['checked_out_at'])) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Pengunjung sudah checkout.']);
        }

        $now = date('Y-m-d H:i:s');
        $this->visitModel->update($id, ['checked_out_at' => $now]);

        return $this->response->setJSON(['success' => true, 'checked_out_at' => $now]);
    }

    /**
     * Daftar kunjungan per lab.
     */
    public function labVisits(int $labId)
    {
        if (! activeGroupCan('visits.list')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke data kunjungan.');
        }

        $lab = $this->labModel->find($labId);
        if (! $lab) {
            return redirect()->to('/admin/visits')->with('error', 'Lab tidak ditemukan.');
        }

        $filters = [
            'date_from' => $this->request->getGet('date_from'),
            'date_to'   => $this->request->getGet('date_to'),
            'status'    => $this->request->getGet('status'),
        ];

        $visits = $this->visitModel->getVisitsForLab($labId, $filters);
        $stats  = $this->visitModel->todayStats($labId);

        return $this->renderView('admin/visits/lab', [
            'title'         => 'Kunjungan - ' . $lab['name'],
            'page_title'    => 'Kunjungan Lab: ' . $lab['name'],
            'lab'           => $lab,
            'visits'        => $visits,
            'filters'       => $filters,
            'todayStats'    => $stats,
            'purposeLabels' => $this->visitModel->purposeLabels,
        ]);
    }
}
