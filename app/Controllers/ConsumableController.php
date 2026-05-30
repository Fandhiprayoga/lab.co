<?php

namespace App\Controllers;

use App\Models\ConsumableItemModel;
use App\Models\ConsumableRequestItemModel;
use App\Models\ConsumableRequestModel;
use App\Models\LabModel;
use CodeIgniter\I18n\Time;

class ConsumableController extends BaseController
{
    protected ConsumableRequestModel $requestModel;
    protected ConsumableRequestItemModel $requestItemModel;
    protected ConsumableItemModel $itemModel;
    protected LabModel $labModel;

    public function __construct()
    {
        $this->requestModel     = new ConsumableRequestModel();
        $this->requestItemModel = new ConsumableRequestItemModel();
        $this->itemModel        = new ConsumableItemModel();
        $this->labModel         = new LabModel();
    }

    // ------------------------------------------------------------------
    // Beranda BHP
    // ------------------------------------------------------------------

    public function beranda()
    {
        $lowStockItems = $this->itemModel->getLowStock();
        $totalItems    = $this->itemModel->where('is_active', 1)->countAllResults();
        $totalRequests = $this->requestModel->countAllResults();
        $pendingCount  = $this->requestModel
            ->whereIn('status', [
                ConsumableRequestModel::STATUS_WAITING_APPROVAL,
                ConsumableRequestModel::STATUS_APPROVED,
                ConsumableRequestModel::STATUS_DISBURSED,
            ])
            ->countAllResults();

        return $this->renderView('consumables/beranda', [
            'title'         => 'Beranda Bahan Habis Pakai',
            'totalItems'    => $totalItems,
            'totalRequests' => $totalRequests,
            'pendingCount'  => $pendingCount,
            'lowStockItems' => $lowStockItems,
        ]);
    }

    // ------------------------------------------------------------------
    // API: daftar bahan per lab (AJAX untuk form create)
    // ------------------------------------------------------------------

    public function itemsByLab()
    {
        $labId = (int) $this->request->getGet('lab_id');

        if ($labId < 1) {
            return $this->response->setJSON([]);
        }

        $items = $this->itemModel
            ->select('consumable_items.id, consumable_items.name, consumable_items.stock_available, units.symbol AS unit_symbol')
            ->join('units', 'units.id = consumable_items.unit_id', 'left')
            ->where('consumable_items.lab_id', $labId)
            ->where('consumable_items.is_active', 1)
            ->orderBy('consumable_items.name', 'ASC')
            ->findAll();

        return $this->response->setJSON($items);
    }

    // ------------------------------------------------------------------
    // AJAX: DataTables server-side untuk katalog bahan
    // ------------------------------------------------------------------

    public function datatableItems()
    {
        if (! activeGroupCan('bhp.catalog.view')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $req    = $this->request;
        $draw   = (int) $req->getGet('draw');
        $start  = max(0, (int) $req->getGet('start'));
        $length = (int) $req->getGet('length');
        if ($length <= 0) { $length = 25; }

        $search         = trim((string) ($req->getGet('search')['value'] ?? ''));
        $orderCol       = (int) ($req->getGet('order')[0]['column'] ?? 1);
        $orderDir       = strtolower((string) ($req->getGet('order')[0]['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $filterLab      = (int) ($req->getGet('filter_lab')      ?? 0);
        $filterCategory = (int) ($req->getGet('filter_category') ?? 0);
        $filterStatus   = (string) ($req->getGet('filter_status') ?? '');

        $colMap = [
            1 => 'ci.name',
            2 => 'consumable_categories.name',
            3 => 'labs.name',
            4 => 'ci.stock_available',
            7 => 'ci.expiry_date',
        ];
        $orderField = $colMap[$orderCol] ?? 'ci.name';

        $db    = db_connect();
        $today = date('Y-m-d');

        $recordsTotal = (int) $db->table('consumable_items')->where('is_active', 1)->countAllResults();

        // ---- Count filtered ----
        $cnt = $db->table('consumable_items ci')
            ->select('COUNT(DISTINCT ci.id) AS cnt')
            ->join('consumable_categories', 'consumable_categories.id = ci.category_id', 'left')
            ->join('labs', 'labs.id = ci.lab_id', 'left')
            ->where('ci.is_active', 1);
        $this->applyConsumableFilters($cnt, $search, $filterLab, $filterCategory, $filterStatus, $today);
        $recordsFiltered = (int) ($cnt->get()->getRow()->cnt ?? 0);

        // ---- Data ----
        $qb = $db->table('consumable_items ci')
            ->select('ci.id, ci.name, ci.stock_available, ci.min_stock, ci.location, ci.expiry_date, ci.requires_approval,
                      consumable_categories.name AS category_name,
                      units.symbol AS unit_symbol,
                      labs.name AS lab_name')
            ->join('consumable_categories', 'consumable_categories.id = ci.category_id', 'left')
            ->join('units', 'units.id = ci.unit_id', 'left')
            ->join('labs', 'labs.id = ci.lab_id', 'left')
            ->where('ci.is_active', 1);
        $this->applyConsumableFilters($qb, $search, $filterLab, $filterCategory, $filterStatus, $today);
        $rows = $qb->orderBy($orderField, $orderDir)->limit($length, $start)->get()->getResultArray();

        $canAdjust = activeGroupCan('bhp.stock.adjust');
        $data      = [];

        foreach ($rows as $i => $row) {
            $isLow     = (float) $row['stock_available'] <= (float) $row['min_stock'];
            $isExpired = ! empty($row['expiry_date']) && $row['expiry_date'] < $today;

            // Badge status
            if ($isLow && $isExpired) {
                $badge = '<span class="badge badge-danger">Kritis</span>';
            } elseif ($isLow) {
                $badge = '<span class="badge badge-warning">Stok Rendah</span>';
            } elseif ($isExpired) {
                $badge = '<span class="badge badge-danger">Kedaluwarsa</span>';
            } else {
                $badge = '<span class="badge badge-success">Tersedia</span>';
            }

            // Stok
            $sym     = esc($row['unit_symbol'] ?? '');
            $stockTxt = rtrim(rtrim(number_format((float) $row['stock_available'], 4), '0'), '.') . ' ' . $sym;
            $stockHtml = $isLow
                ? '<span class="text-danger font-weight-bold">' . $stockTxt . '</span>'
                : $stockTxt;

            // Kedaluwarsa
            if (empty($row['expiry_date'])) {
                $expiryHtml = '<span class="text-muted">—</span>';
            } elseif ($isExpired) {
                $expiryHtml = '<span class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>' . esc($row['expiry_date']) . '</span>';
            } else {
                $expiryHtml = esc($row['expiry_date']);
            }

            // Nama + badge approval
            $nameHtml = '<strong>' . esc($row['name']) . '</strong>';
            if ($row['requires_approval']) {
                $nameHtml .= ' <span class="badge badge-info" style="font-size:10px;">Approval</span>';
            }

            // Aksi
            $actions = '';
            if ($canAdjust) {
                $actions = '<a href="' . site_url('consumables/adjustments/' . $row['id'] . '/create') . '" '
                    . 'class="btn btn-xs btn-light" title="Penyesuaian Stok">'
                    . '<i class="fas fa-sliders-h"></i></a>';
            }

            $data[] = [
                $start + $i + 1,
                $nameHtml,
                esc($row['category_name'] ?? '—'),
                esc($row['lab_name'] ?? '—'),
                $stockHtml,
                rtrim(rtrim(number_format((float) $row['min_stock'], 4), '0'), '.') . ' ' . $sym,
                esc($row['location'] ?? '—'),
                $expiryHtml,
                $badge,
                $actions,
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    /** Terapkan filter ke query builder katalog bahan. */
    private function applyConsumableFilters(
        $builder,
        string $search,
        int $filterLab,
        int $filterCategory,
        string $filterStatus,
        string $today
    ): void {
        if ($search !== '') {
            $builder->groupStart()
                ->like('ci.name', $search)
                ->orLike('consumable_categories.name', $search)
                ->orLike('labs.name', $search)
                ->orLike('ci.location', $search)
                ->groupEnd();
        }

        if ($filterLab > 0)      { $builder->where('ci.lab_id', $filterLab); }
        if ($filterCategory > 0) { $builder->where('ci.category_id', $filterCategory); }

        if ($filterStatus === 'low_stock') {
            $builder->where('ci.stock_available <= ci.min_stock', null, false);
        } elseif ($filterStatus === 'expired') {
            $builder->where('ci.expiry_date IS NOT NULL', null, false)
                    ->where('ci.expiry_date <', $today);
        } elseif ($filterStatus === 'ok') {
            $builder->where('ci.stock_available > ci.min_stock', null, false)
                    ->groupStart()
                        ->where('ci.expiry_date IS NULL', null, false)
                        ->orWhere('ci.expiry_date >=', $today)
                    ->groupEnd();
        }
    }

    // ------------------------------------------------------------------
    // Katalog bahan
    // ------------------------------------------------------------------

    public function index()
    {
        if (! activeGroupCan('bhp.catalog.view')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $labs       = $this->labModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        $categories = db_connect()
            ->table('consumable_categories')
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        return $this->renderView('consumables/index', [
            'title'      => 'Katalog Bahan Habis Pakai',
            'page_title' => 'Katalog Bahan Habis Pakai',
            'labs'       => $labs,
            'categories' => $categories,
        ]);
    }

    // ------------------------------------------------------------------
    // Daftar permintaan
    // ------------------------------------------------------------------

    public function requests()
    {
        if (! activeGroupCan('bhp.request.track')) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $ownOnly = ! activeGroupCan('bhp.request.manage-all');
        $requests = $this->requestModel->getList($ownOnly, (int) auth()->id());

        return $this->renderView('consumables/requests/index', [
            'title'      => 'Permintaan BHP',
            'page_title' => 'Daftar Permintaan Bahan Habis Pakai',
            'requests'   => $requests,
        ]);
    }

    // ------------------------------------------------------------------
    // Buat permintaan
    // ------------------------------------------------------------------

    public function create()
    {
        if (! activeGroupCan('bhp.request.create')) {
            return redirect()->to('/consumables')->with('error', 'Akses ditolak.');
        }

        $labs = $this->labModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();

        return $this->renderView('consumables/requests/create', [
            'title'      => 'Buat Permintaan BHP',
            'page_title' => 'Buat Permintaan Bahan Habis Pakai',
            'labs'       => $labs,
        ]);
    }

    public function store()
    {
        if (! activeGroupCan('bhp.request.create')) {
            return redirect()->to('/consumables')->with('error', 'Akses ditolak.');
        }

        $rules = [
            'lab_id'  => 'required|is_natural_no_zero',
            'purpose' => 'required|min_length[5]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $items = $this->request->getPost('items') ?? [];
        if (empty($items)) {
            return redirect()->back()->withInput()->with('error', 'Tambahkan minimal 1 bahan sebelum menyimpan.');
        }

        $code = 'BHP-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $scheduledRaw = $this->request->getPost('scheduled_date');
        $scheduledDate = $scheduledRaw ? date('Y-m-d', strtotime((string) $scheduledRaw)) : null;

        $this->requestModel->insert([
            'request_code'   => $code,
            'requester_id'   => auth()->id(),
            'lab_id'         => (int) $this->request->getPost('lab_id'),
            'purpose'        => trim((string) $this->request->getPost('purpose')),
            'scheduled_date' => $scheduledDate,
            'status'         => ConsumableRequestModel::STATUS_DRAFT,
        ]);

        $requestId = (int) $this->requestModel->getInsertID();

        foreach ($items as $row) {
            $consumableId = (int) ($row['consumable_id'] ?? 0);
            $qty          = max(0.0001, (float) ($row['qty'] ?? 0));

            if ($consumableId < 1) {
                continue;
            }

            $this->requestItemModel->insert([
                'request_id'    => $requestId,
                'consumable_id' => $consumableId,
                'qty_requested' => $qty,
                'notes'         => trim((string) ($row['notes'] ?? '')) ?: null,
            ]);
        }

        return redirect()->to('/consumables/requests/' . $requestId)->with('success', 'Permintaan berhasil dibuat. Silakan kirim untuk approval.');
    }

    // ------------------------------------------------------------------
    // Detail permintaan
    // ------------------------------------------------------------------

    public function show(int $id)
    {
        if (! activeGroupCan('bhp.request.track')) {
            return redirect()->to('/consumables')->with('error', 'Akses ditolak.');
        }

        $request = $this->requestModel->getDetail($id);
        if (! $request) {
            return redirect()->to('/consumables/requests')->with('error', 'Permintaan tidak ditemukan.');
        }

        if (! $this->canAccessRequest($request)) {
            return redirect()->to('/consumables/requests')->with('error', 'Anda tidak memiliki akses ke permintaan ini.');
        }

        $requestItems = $this->requestItemModel->getByRequest($id);

        return $this->renderView('consumables/requests/show', [
            'title'        => 'Detail Permintaan BHP',
            'page_title'   => 'Permintaan: ' . $request['request_code'],
            'bhpRequest'   => $request,
            'requestItems' => $requestItems,
        ]);
    }

    // ------------------------------------------------------------------
    // Submit (draft → waiting_approval atau approved)
    // ------------------------------------------------------------------

    public function submit(int $id)
    {
        if (! activeGroupCan('bhp.request.submit')) {
            return redirect()->to('/consumables/requests')->with('error', 'Akses ditolak.');
        }

        $bhpRequest = $this->requestModel->find($id);
        if (! $bhpRequest || $bhpRequest['status'] !== ConsumableRequestModel::STATUS_DRAFT) {
            return redirect()->to('/consumables/requests')->with('error', 'Permintaan tidak dapat dikirim.');
        }

        if (! $this->canAccessRequest($bhpRequest)) {
            return redirect()->to('/consumables/requests')->with('error', 'Akses ditolak.');
        }

        $itemCount = $this->requestItemModel->where('request_id', $id)->countAllResults();
        if ($itemCount < 1) {
            return redirect()->to('/consumables/requests/' . $id)->with('error', 'Tambahkan minimal 1 bahan sebelum mengirim.');
        }

        // Cek apakah ada bahan yang requires_approval
        $needsApproval = db_connect()->table('consumable_request_items ri')
            ->join('consumable_items ci', 'ci.id = ri.consumable_id')
            ->where('ri.request_id', $id)
            ->where('ci.requires_approval', 1)
            ->countAllResults() > 0;

        $newStatus = $needsApproval
            ? ConsumableRequestModel::STATUS_WAITING_APPROVAL
            : ConsumableRequestModel::STATUS_APPROVED;

        $updateData = [
            'status'       => $newStatus,
            'submitted_at' => Time::now()->toDateTimeString(),
        ];

        // Jika tidak perlu approval, set qty_approved = qty_requested langsung
        if (! $needsApproval) {
            foreach ($this->requestItemModel->where('request_id', $id)->findAll() as $ri) {
                $this->requestItemModel->update($ri['id'], ['qty_approved' => $ri['qty_requested']]);
            }
        }

        $this->requestModel->update($id, $updateData);

        $msg = $needsApproval
            ? 'Permintaan dikirim ke Kepala Lab untuk persetujuan.'
            : 'Permintaan disetujui otomatis dan siap diproses.';

        return redirect()->to('/consumables/requests/' . $id)->with('success', $msg);
    }

    // ------------------------------------------------------------------
    // Approve / Reject (Kepala Lab)
    // ------------------------------------------------------------------

    public function approve(int $id)
    {
        if (! activeGroupCan('bhp.approval')) {
            return redirect()->to('/consumables/requests')->with('error', 'Akses ditolak.');
        }

        $bhpRequest = $this->requestModel->find($id);
        if (! $bhpRequest || $bhpRequest['status'] !== ConsumableRequestModel::STATUS_WAITING_APPROVAL) {
            return redirect()->to('/consumables/requests/' . $id)->with('error', 'Permintaan tidak valid untuk disetujui.');
        }

        $db = db_connect();
        $db->transStart();

        // Simpan qty_approved per item dari POST
        $approvedQtys = $this->request->getPost('qty_approved') ?? [];
        foreach ($approvedQtys as $itemId => $qty) {
            $qty = max(0, (float) $qty);
            $this->requestItemModel->update((int) $itemId, ['qty_approved' => $qty]);
        }

        $this->requestModel->update($id, [
            'status'        => ConsumableRequestModel::STATUS_APPROVED,
            'approval_by'   => auth()->id(),
            'approval_at'   => Time::now()->toDateTimeString(),
            'approval_note' => trim((string) $this->request->getPost('approval_note')) ?: 'Disetujui.',
        ]);

        $db->transComplete();

        return redirect()->to('/consumables/requests/' . $id)->with('success', 'Permintaan berhasil disetujui.');
    }

    public function reject(int $id)
    {
        if (! activeGroupCan('bhp.approval')) {
            return redirect()->to('/consumables/requests')->with('error', 'Akses ditolak.');
        }

        $bhpRequest = $this->requestModel->find($id);
        if (! $bhpRequest || $bhpRequest['status'] !== ConsumableRequestModel::STATUS_WAITING_APPROVAL) {
            return redirect()->to('/consumables/requests/' . $id)->with('error', 'Permintaan tidak valid untuk ditolak.');
        }

        $reason = trim((string) $this->request->getPost('approval_note')) ?: 'Ditolak oleh Kepala Lab.';

        $this->requestModel->update($id, [
            'status'        => ConsumableRequestModel::STATUS_REJECTED,
            'approval_by'   => auth()->id(),
            'approval_at'   => Time::now()->toDateTimeString(),
            'approval_note' => $reason,
        ]);

        return redirect()->to('/consumables/requests/' . $id)->with('success', 'Permintaan ditolak.');
    }

    // ------------------------------------------------------------------
    // Pengeluaran bahan (Laboran)
    // ------------------------------------------------------------------

    public function disburse(int $id)
    {
        if (! activeGroupCan('bhp.disburse')) {
            return redirect()->to('/consumables/requests')->with('error', 'Akses ditolak.');
        }

        $bhpRequest = $this->requestModel->find($id);
        if (! $bhpRequest || $bhpRequest['status'] !== ConsumableRequestModel::STATUS_APPROVED) {
            return redirect()->to('/consumables/requests/' . $id)->with('error', 'Permintaan harus berstatus Disetujui untuk dikeluarkan.');
        }

        $db = db_connect();
        $db->transStart();

        $requestItems = $this->requestItemModel->where('request_id', $id)->findAll();
        foreach ($requestItems as $ri) {
            $approved = (float) ($ri['qty_approved'] ?? $ri['qty_requested']);
            if ($approved <= 0) {
                continue;
            }

            $item = $this->itemModel->find((int) $ri['consumable_id']);
            if (! $item) {
                continue;
            }

            $newAvailable = max(0, (float) $item['stock_available'] - $approved);
            $newTotal     = max(0, (float) $item['stock_total'] - $approved);

            $this->itemModel->update((int) $item['id'], [
                'stock_available' => $newAvailable,
                'stock_total'     => $newTotal,
            ]);
        }

        $this->requestModel->update($id, [
            'status'       => ConsumableRequestModel::STATUS_DISBURSED,
            'disbursed_by' => auth()->id(),
            'disbursed_at' => Time::now()->toDateTimeString(),
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('/consumables/requests/' . $id)->with('error', 'Gagal memproses pengeluaran bahan. Coba lagi.');
        }

        return redirect()->to('/consumables/requests/' . $id)->with('success', 'Bahan berhasil dikeluarkan dari stok.');
    }

    // ------------------------------------------------------------------
    // Realisasi penggunaan
    // ------------------------------------------------------------------

    public function realize(int $id)
    {
        if (! activeGroupCan('bhp.realize')) {
            return redirect()->to('/consumables/requests')->with('error', 'Akses ditolak.');
        }

        $bhpRequest = $this->requestModel->getDetail($id);
        if (! $bhpRequest || $bhpRequest['status'] !== ConsumableRequestModel::STATUS_DISBURSED) {
            return redirect()->to('/consumables/requests/' . $id)->with('error', 'Permintaan harus berstatus Dikeluarkan untuk dicatat realisasinya.');
        }

        $requestItems = $this->requestItemModel->getByRequest($id);

        return $this->renderView('consumables/requests/realize', [
            'title'        => 'Catat Realisasi',
            'page_title'   => 'Realisasi Penggunaan: ' . $bhpRequest['request_code'],
            'bhpRequest'   => $bhpRequest,
            'requestItems' => $requestItems,
        ]);
    }

    public function storeRealization(int $id)
    {
        if (! activeGroupCan('bhp.realize')) {
            return redirect()->to('/consumables/requests')->with('error', 'Akses ditolak.');
        }

        $bhpRequest = $this->requestModel->find($id);
        if (! $bhpRequest || $bhpRequest['status'] !== ConsumableRequestModel::STATUS_DISBURSED) {
            return redirect()->to('/consumables/requests/' . $id)->with('error', 'Status permintaan tidak valid.');
        }

        $db = db_connect();
        $db->transStart();

        $actualQtys = $this->request->getPost('qty_actual') ?? [];
        foreach ($actualQtys as $itemId => $qty) {
            $this->requestItemModel->update((int) $itemId, ['qty_actual' => max(0, (float) $qty)]);
        }

        $this->requestModel->update($id, [
            'status'      => ConsumableRequestModel::STATUS_COMPLETED,
            'realized_by' => auth()->id(),
            'realized_at' => Time::now()->toDateTimeString(),
        ]);

        $db->transComplete();

        return redirect()->to('/consumables/requests/' . $id)->with('success', 'Realisasi penggunaan berhasil dicatat. Transaksi selesai.');
    }

    // ------------------------------------------------------------------
    // Batalkan permintaan
    // ------------------------------------------------------------------

    public function cancel(int $id)
    {
        $bhpRequest = $this->requestModel->find($id);
        if (! $bhpRequest) {
            return redirect()->to('/consumables/requests')->with('error', 'Permintaan tidak ditemukan.');
        }

        if (! $this->canAccessRequest($bhpRequest) && ! activeGroupCan('bhp.request.manage-all')) {
            return redirect()->to('/consumables/requests')->with('error', 'Akses ditolak.');
        }

        $cancelable = [
            ConsumableRequestModel::STATUS_DRAFT,
            ConsumableRequestModel::STATUS_WAITING_APPROVAL,
        ];

        if (! in_array($bhpRequest['status'], $cancelable, true)) {
            return redirect()->to('/consumables/requests/' . $id)->with('error', 'Permintaan tidak dapat dibatalkan pada status ini.');
        }

        $reason = trim((string) $this->request->getPost('cancel_reason')) ?: 'Dibatalkan oleh pemohon.';

        $this->requestModel->update($id, [
            'status'          => ConsumableRequestModel::STATUS_CANCELED,
            'canceled_reason' => $reason,
        ]);

        return redirect()->to('/consumables/requests/' . $id)->with('success', 'Permintaan berhasil dibatalkan.');
    }

    // ------------------------------------------------------------------
    // Analitik konsumsi
    // ------------------------------------------------------------------

    public function analytics()
    {
        if (! activeGroupCan('bhp.analytics.view')) {
            return redirect()->to('/consumables')->with('error', 'Akses ditolak.');
        }

        $db = db_connect();

        // Top 10 bahan paling banyak digunakan (berdasarkan qty_actual)
        $topItems = $db->table('consumable_request_items ri')
            ->select('ci.name AS item_name, units.symbol AS unit_symbol, SUM(ri.qty_actual) AS total_used')
            ->join('consumable_items ci', 'ci.id = ri.consumable_id', 'left')
            ->join('units', 'units.id = ci.unit_id', 'left')
            ->join('consumable_requests r', 'r.id = ri.request_id')
            ->where('r.status', ConsumableRequestModel::STATUS_COMPLETED)
            ->groupBy('ri.consumable_id')
            ->orderBy('total_used', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        // Tren permintaan per bulan (6 bulan terakhir)
        $trend = $db->table('consumable_requests')
            ->select("DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total")
            ->where('created_at >=', date('Y-m-d', strtotime('-6 months')))
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get()->getResultArray();

        // Ringkasan status
        $statusSummary = $db->table('consumable_requests')
            ->select('status, COUNT(*) AS total')
            ->groupBy('status')
            ->get()->getResultArray();

        // Bahan di bawah stok minimum
        $lowStockItems = $this->itemModel->getLowStock();

        return $this->renderView('consumables/analytics', [
            'title'          => 'Analitik BHP',
            'page_title'     => 'Analitik Konsumsi Bahan Habis Pakai',
            'topItems'       => $topItems,
            'trend'          => $trend,
            'statusSummary'  => $statusSummary,
            'lowStockItems'  => $lowStockItems,
        ]);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function canAccessRequest(array $request): bool
    {
        if (activeGroupCan('bhp.request.manage-all')) {
            return true;
        }

        return (int) $request['requester_id'] === (int) auth()->id();
    }
}
