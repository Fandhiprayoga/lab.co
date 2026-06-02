<?php

namespace App\Controllers;

use App\Models\AssetCategoryModel;
use App\Models\AssetItemModel;
use App\Models\LabAssetModel;
use App\Models\LabModel;
use App\Models\UnitModel;

class LoanAssetController extends BaseController
{
    private const CONDITION_BAIK = 'baik';
    private const CONDITION_PERLU_PERBAIKAN = 'perlu_perbaikan';
    private const CONDITION_RUSAK = 'rusak';

    private const ACQUISITION_SOURCES = ['pembelian', 'hibah', 'pinjaman', 'produksi'];
    private const INVENTORY_STATUSES  = ['aktif', 'dipinjam', 'dalam_perbaikan', 'dihapuskan', 'hilang'];

    protected LabAssetModel $assetModel;
    protected AssetItemModel $assetItemModel;
    protected LabModel $labModel;
    protected AssetCategoryModel $categoryModel;
    protected UnitModel $unitModel;

    public function __construct()
    {
        $this->assetModel    = new LabAssetModel();
        $this->assetItemModel = new AssetItemModel();
        $this->labModel      = new LabModel();
        $this->categoryModel = new AssetCategoryModel();
        $this->unitModel     = new UnitModel();
    }

    public function index()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $assets = db_connect()->table('lab_assets a')
            ->select('a.*, l.name AS lab_name, u.symbol AS unit_symbol')
            ->join('labs l', 'l.id = a.lab_id', 'left')
            ->join('units u', 'u.id = a.unit_id', 'left')
            ->where('a.asset_type', 'equipment')
            ->orderBy('a.name', 'ASC')
            ->get()->getResultArray();

        return $this->renderView('loans/assets/index', [
            'title'      => 'Master Aset Lab',
            'page_title' => 'Master Data Alat',
            'assets'     => $assets,
            'labs'       => $this->getActiveLabs(),
        ]);
    }

    public function create()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        return $this->renderView('loans/assets/create', [
            'title'      => 'Tambah Master Alat',
            'page_title' => 'Tambah Master Alat',
            'labs'       => $this->getActiveLabs(),
            'categories' => $this->getActiveCategories(),
            'units'      => $this->getActiveUnits(),
            'acquisitionSources' => self::ACQUISITION_SOURCES,
            'inventoryStatuses'  => self::INVENTORY_STATUSES,
        ]);
    }

    public function edit(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $asset = $this->assetModel->find($id);
        if (! $asset || ($asset['asset_type'] ?? null) !== 'equipment') {
            return redirect()->to('/admin/loans/assets')->with('error', 'Data aset tidak ditemukan.');
        }

        return $this->renderView('loans/assets/edit', [
            'title'      => 'Edit Master Alat',
            'page_title' => 'Edit Master Alat',
            'asset'      => $asset,
            'labs'       => $this->getActiveLabs(),
            'categories' => $this->getActiveCategories(),
            'units'      => $this->getActiveUnits(),
            'acquisitionSources' => self::ACQUISITION_SOURCES,
            'inventoryStatuses'  => self::INVENTORY_STATUSES,
        ]);
    }

    public function store()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $uploadedPhoto     = $this->request->getFile('asset_photo');
        $hasValidTempFile = $uploadedPhoto !== null
            && $uploadedPhoto->isValid()
            && ! $uploadedPhoto->hasMoved()
            && file_exists($uploadedPhoto->getTempName());

        $rules = [
            'name'             => 'required|min_length[3]',
            'lab_id'           => 'required|is_natural_no_zero',
            'max_loan_hours'   => 'required|is_natural',
            'asset_photo'      => $hasValidTempFile
                ? 'max_size[asset_photo,2048]|is_image[asset_photo]|mime_in[asset_photo,image/png,image/jpeg,image/webp,image/svg+xml]'
                : 'permit_empty',
            'asset_code'         => 'permit_empty|max_length[50]',
            'serial_number'      => 'permit_empty|max_length[100]',
            'brand'              => 'permit_empty|max_length[80]',
            'model'              => 'permit_empty|max_length[80]',
            'unit_id'            => 'permit_empty|is_natural_no_zero',
            'acquisition_date'   => 'permit_empty|valid_date[Y-m-d]',
            'acquisition_source' => 'permit_empty|in_list[pembelian,hibah,pinjaman,produksi]',
            'purchase_price'     => 'permit_empty|decimal',
            'supplier'           => 'permit_empty|max_length[150]',
            'funding_source'     => 'permit_empty|max_length[100]',
            'warranty_until'     => 'permit_empty|valid_date[Y-m-d]',
            'responsible_user_id'=> 'permit_empty|is_natural_no_zero',
            'minimum_stock'      => 'permit_empty|is_natural',
            'notes'              => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $maxLoanHours = (int) $this->request->getPost('max_loan_hours');

        $labId = (int) $this->request->getPost('lab_id');
        if (! $this->labModel->find($labId)) {
            return redirect()->back()->withInput()->with('error', 'Lab tujuan tidak ditemukan.');
        }

        $categoryName = $this->resolveCategoryName((string) $this->request->getPost('category'));
        if ($categoryName === null) {
            return redirect()->back()->withInput()->with('error', 'Kategori alat wajib dipilih dari master kategori aktif.');
        }

        $photoPath = $this->handlePhotoUpload();

        $inventory = $this->collectInventoryPayload();
        $assetCode = trim((string) $this->request->getPost('asset_code'));
        if ($assetCode === '') {
            $assetCode = $this->generateAssetCode($labId, $categoryName);
        } elseif ($this->isDuplicateAssetCode($assetCode)) {
            return redirect()->back()->withInput()->with('error', 'Kode aset sudah digunakan, gunakan kode lain.');
        }

        $this->assetModel->insert(array_merge([
            'name'            => trim((string) $this->request->getPost('name')),
            'lab_id'          => $labId,
            'asset_type'      => 'equipment',
            'category'        => $categoryName,
            'location'        => null,
            'specifications'  => trim((string) $this->request->getPost('specifications')) ?: null,
            'photo'           => $photoPath,
            'max_loan_hours'  => $maxLoanHours,
            'stock_total'     => 0,
            'stock_available' => 0,
            'is_active'       => $this->request->getPost('is_active') ? 1 : 0,
            'is_loanable'     => 0,
            'condition_status'=> self::CONDITION_BAIK,
            'inventory_status'=> 'aktif',
            'created_by'      => auth()->id(),
            'asset_code'      => $assetCode,
        ], $inventory));

        $assetId = (int) $this->assetModel->getInsertID();
        if ($assetId > 0) {
            $this->syncAssetStockAggregate($assetId);
        }

        return redirect()->to('/admin/loans/assets')->with('success', 'Master aset berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $asset = $this->assetModel->find($id);
        if (! $asset) {
            return redirect()->to('/admin/loans/assets')->with('error', 'Data aset tidak ditemukan.');
        }

        $uploadedPhoto     = $this->request->getFile('asset_photo');
        $hasValidTempFile = $uploadedPhoto !== null
            && $uploadedPhoto->isValid()
            && ! $uploadedPhoto->hasMoved()
            && file_exists($uploadedPhoto->getTempName());
        
        $rules = [
            'name'             => 'required|min_length[3]',
            'lab_id'           => 'required|is_natural_no_zero',
            'max_loan_hours'   => 'required|is_natural',
            'asset_photo'      => $hasValidTempFile
                ? 'max_size[asset_photo,2048]|is_image[asset_photo]|mime_in[asset_photo,image/png,image/jpeg,image/webp,image/svg+xml]'
                : 'permit_empty',
            'asset_code'         => 'permit_empty|max_length[50]',
            'serial_number'      => 'permit_empty|max_length[100]',
            'brand'              => 'permit_empty|max_length[80]',
            'model'              => 'permit_empty|max_length[80]',
            'unit_id'            => 'permit_empty|is_natural_no_zero',
            'acquisition_date'   => 'permit_empty|valid_date[Y-m-d]',
            'acquisition_source' => 'permit_empty|in_list[pembelian,hibah,pinjaman,produksi]',
            'purchase_price'     => 'permit_empty|decimal',
            'supplier'           => 'permit_empty|max_length[150]',
            'funding_source'     => 'permit_empty|max_length[100]',
            'warranty_until'     => 'permit_empty|valid_date[Y-m-d]',
            'responsible_user_id'=> 'permit_empty|is_natural_no_zero',
            'minimum_stock'      => 'permit_empty|is_natural',
            'notes'              => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $labId      = (int) $this->request->getPost('lab_id');
        $maxLoanHoursPost = $this->request->getPost('max_loan_hours');
        $maxLoanHours = ($maxLoanHoursPost === null || $maxLoanHoursPost === '')
            ? (int) $asset['max_loan_hours']
            : (int) $maxLoanHoursPost;

        if ($maxLoanHours < 0) {
            return redirect()->back()->withInput()->with('error', 'Maksimal jam peminjaman tidak valid.');
        }

        if (! $this->labModel->find($labId)) {
            return redirect()->back()->withInput()->with('error', 'Lab tujuan tidak ditemukan.');
        }

        $categoryName = $this->resolveCategoryName((string) $this->request->getPost('category'));
        if ($categoryName === null) {
            return redirect()->back()->withInput()->with('error', 'Kategori alat wajib dipilih dari master kategori aktif.');
        }

        $photoPath = $this->handlePhotoUpload($asset['photo'] ?? null);

        $inventory = $this->collectInventoryPayload($asset);
        $assetCode = trim((string) $this->request->getPost('asset_code'));
        if ($assetCode === '') {
            $assetCode = $asset['asset_code'] ?? $this->generateAssetCode($labId, $categoryName);
        } elseif ($this->isDuplicateAssetCode($assetCode, $id)) {
            return redirect()->back()->withInput()->with('error', 'Kode aset sudah digunakan, gunakan kode lain.');
        }

        $derivedState = $this->deriveAssetMasterState($id, $asset);

        $payload = array_merge([
            'name'            => trim((string) $this->request->getPost('name')),
            'lab_id'          => $labId,
            'asset_type'      => 'equipment',
            'category'        => $categoryName,
            'location'        => null,
            'specifications'  => trim((string) $this->request->getPost('specifications')) ?: null,
            'max_loan_hours'  => $maxLoanHours,
            'stock_total'     => (int) ($derivedState['stock_total'] ?? 0),
            'stock_available' => (int) ($derivedState['stock_available'] ?? 0),
            'is_active'       => $this->request->getPost('is_active') ? 1 : 0,
            'is_loanable'     => (int) ($derivedState['is_loanable'] ?? 0),
            'condition_status'=> (string) ($derivedState['condition_status'] ?? self::CONDITION_BAIK),
            'inventory_status'=> (string) ($derivedState['inventory_status'] ?? 'aktif'),
            'asset_code'      => $assetCode,
            'updated_by'      => auth()->id(),
        ], $inventory);

        if ($photoPath !== null) {
            $payload['photo'] = $photoPath;
        }

        $this->assetModel->update($id, $payload);
        $this->syncAssetStockAggregate($id);

        return redirect()->to('/admin/loans/assets')->with('success', 'Master aset berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $asset = $this->assetModel->find($id);
        if (! $asset) {
            return redirect()->to('/admin/loans/assets')->with('error', 'Data aset tidak ditemukan.');
        }

        $activeLoanExists = db_connect()->table('loan_requests')
            ->where('asset_id', $id)
            ->whereIn('status', ['waiting_l1', 'waiting_l2', 'approved_waiting_pickup', 'borrowed', 'late'])
            ->countAllResults() > 0;

        if ($activeLoanExists) {
            return redirect()->to('/admin/loans/assets')->with('error', 'Aset tidak bisa dihapus karena masih dipakai transaksi aktif.');
        }

        if (! empty($asset['photo']) && file_exists(FCPATH . $asset['photo'])) {
            unlink(FCPATH . $asset['photo']);
        }

        $this->assetModel->delete($id);

        return redirect()->to('/admin/loans/assets')->with('success', 'Master aset berhasil dihapus.');
    }

    public function qrIndex()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $filterLabId = (int) $this->request->getGet('lab_id');

        $builder = db_connect()->table('lab_assets a')
            ->select('a.id, a.name, a.asset_code, a.brand, a.model, a.lab_id, l.name AS lab_name')
            ->join('labs l', 'l.id = a.lab_id', 'left')
            ->where('a.asset_type', 'equipment')
            ->orderBy('l.name', 'ASC')
            ->orderBy('a.name', 'ASC');

        if ($filterLabId > 0) {
            $builder->where('a.lab_id', $filterLabId);
        }

        $assets = $builder->get()->getResultArray();

        return $this->renderView('loans/assets/qr_index', [
            'title'      => 'QR Code Alat',
            'page_title' => 'QR Code Alat',
            'assets'     => $assets,
            'labs'       => $this->getActiveLabs(),
            'filterLabId' => $filterLabId,
        ]);
    }

    public function qr(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $asset = $this->assetModel->find($id);
        if (! $asset || ($asset['asset_type'] ?? null) !== 'equipment') {
            return redirect()->to('/admin/loans/assets/qr')->with('error', 'Aset tidak ditemukan.');
        }

        return view('loans/assets/qr_show', [
            'asset'  => $asset,
            'qrUrl'  => base_url('admin/loans/assets/edit/' . $id),
        ]);
    }

    public function qrImage(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $asset = $this->assetModel->find($id);
        if (! $asset || ($asset['asset_type'] ?? null) !== 'equipment') {
            return $this->response->setStatusCode(404)->setBody('Aset tidak ditemukan.');
        }

        $url = base_url('admin/loans/assets/edit/' . $id);

        $builder = new \Endroid\QrCode\Builder\Builder(
            writer: new \Endroid\QrCode\Writer\PngWriter(),
            data: $url,
            encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
            errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::High,
            size: 400,
            margin: 16,
            roundBlockSizeMode: \Endroid\QrCode\RoundBlockSizeMode::Margin,
        );
        $result = $builder->build();

        return $this->response
            ->setHeader('Content-Type', $result->getMimeType())
            ->setBody($result->getString());
    }

    public function qrBulkPrint()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rawIds = $this->request->getGet('ids');
        $ids    = [];

        if (is_array($rawIds)) {
            $ids = array_map('intval', $rawIds);
            $ids = array_filter($ids, static fn ($v) => $v > 0);
            $ids = array_values($ids);
        }

        if (empty($ids)) {
            return redirect()->to('/admin/loans/assets/qr')->with('error', 'Pilih minimal satu alat untuk dicetak.');
        }

        $assets = db_connect()->table('lab_assets a')
            ->select('a.id, a.name, a.asset_code, a.brand, a.model, l.name AS lab_name')
            ->join('labs l', 'l.id = a.lab_id', 'left')
            ->whereIn('a.id', $ids)
            ->where('a.asset_type', 'equipment')
            ->orderBy('a.name', 'ASC')
            ->get()->getResultArray();

        return view('loans/assets/qr_bulk', [
            'assets' => $assets,
        ]);
    }

    public function itemIndex()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $filterAssetId = (int) $this->request->getGet('asset_id');

        $assets = db_connect()->table('lab_assets')
            ->select('id, name, asset_code')
            ->where('asset_type', 'equipment')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        return $this->renderView('loans/asset_items/index', [
            'title'         => 'Item Alat',
            'page_title'    => 'Manajemen Item Alat',
            'assets'        => $assets,
            'labs'          => $this->getActiveLabs(),
            'filterAssetId' => $filterAssetId,
        ]);
    }

    public function itemDatatable()
    {
        if (! activeGroupCan('lending.master.manage')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $req = $this->request;

        $draw   = (int) $req->getGet('draw');
        $start  = max(0, (int) $req->getGet('start'));
        $length = (int) $req->getGet('length');
        if ($length <= 0) {
            $length = 25;
        }

        $search = trim((string) ($req->getGet('search')['value'] ?? ''));
        $orderCol = (int) ($req->getGet('order')[0]['column'] ?? 1);
        $orderDir = strtolower((string) ($req->getGet('order')[0]['dir'] ?? 'asc')) === 'desc' ? 'DESC' : 'ASC';

        $filterAssetId = (int) ($req->getGet('filter_asset_id') ?? 0);
        $filterLabId = (int) ($req->getGet('filter_lab_id') ?? 0);
        $filterCondition = trim((string) ($req->getGet('filter_condition') ?? ''));
        $filterInventory = trim((string) ($req->getGet('filter_inventory') ?? ''));
        $filterLoanable = trim((string) ($req->getGet('filter_loanable') ?? ''));

        $colMap = [
            1 => 'ai.item_code',
            2 => 'a.name',
            3 => 'ai.serial_number',
            4 => 'l.name',
            5 => 'ai.condition_status',
            6 => 'ai.inventory_status',
            7 => 'ai.is_loanable',
        ];
        $orderField = $colMap[$orderCol] ?? 'ai.item_code';

        $db = db_connect();

        $totalBuilder = $db->table('asset_items ai')
            ->join('lab_assets a', 'a.id = ai.asset_id', 'inner')
            ->where('a.asset_type', 'equipment');
        $recordsTotal = (int) $totalBuilder->countAllResults();

        $countBuilder = $db->table('asset_items ai')
            ->select('COUNT(DISTINCT ai.id) AS cnt')
            ->join('lab_assets a', 'a.id = ai.asset_id', 'inner')
            ->join('labs l', 'l.id = ai.lab_id', 'left')
            ->where('a.asset_type', 'equipment');
        $this->applyItemDatatableFilters(
            $countBuilder,
            $search,
            $filterAssetId,
            $filterLabId,
            $filterCondition,
            $filterInventory,
            $filterLoanable
        );
        $recordsFiltered = (int) ($countBuilder->get()->getRow()->cnt ?? 0);

        $dataBuilder = $db->table('asset_items ai')
            ->select('ai.id, ai.item_code, ai.serial_number, ai.condition_status, ai.inventory_status, ai.is_loanable, a.id AS asset_id, a.name AS asset_name, a.asset_code AS master_asset_code, l.name AS lab_name')
            ->join('lab_assets a', 'a.id = ai.asset_id', 'inner')
            ->join('labs l', 'l.id = ai.lab_id', 'left')
            ->where('a.asset_type', 'equipment');
        $this->applyItemDatatableFilters(
            $dataBuilder,
            $search,
            $filterAssetId,
            $filterLabId,
            $filterCondition,
            $filterInventory,
            $filterLoanable
        );

        $rows = $dataBuilder
            ->orderBy($orderField, $orderDir)
            ->orderBy('ai.id', 'ASC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        $data = [];
        $csrfName = csrf_token();
        $csrfHash = csrf_hash();

        foreach ($rows as $i => $row) {
            $loanableBadge = (int) ($row['is_loanable'] ?? 0) === 1
                ? '<span class="badge badge-success">Ya</span>'
                : '<span class="badge badge-secondary">Tidak</span>';

            $conditionLabel = str_replace('_', ' ', (string) ($row['condition_status'] ?? '-'));
            $inventoryLabel = str_replace('_', ' ', (string) ($row['inventory_status'] ?? '-'));

            $actions = '<a href="' . base_url('admin/loans/asset-items/edit/' . (int) $row['id']) . '" class="btn btn-sm btn-info mr-1" title="Edit">'
                . '<i class="fas fa-edit"></i></a>';
            $actions .= '<form action="' . base_url('admin/loans/asset-items/delete/' . (int) $row['id']) . '" method="post" class="d-inline js-swal-delete-form" '
                . 'data-swal-title="Hapus item?" '
                . 'data-swal-text="Item ' . esc((string) ($row['item_code'] ?? '-')) . ' akan dihapus permanen." '
                . 'data-swal-confirm="Ya, hapus" '
                . 'data-swal-cancel="Batal">'
                . '<input type="hidden" name="' . esc($csrfName) . '" value="' . esc($csrfHash) . '">'
                . '<button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>'
                . '</form>';

            $assetText = esc((string) ($row['asset_name'] ?? '-'))
                . '<div><small class="text-muted"><code>' . esc((string) ($row['master_asset_code'] ?? '-')) . '</code></small></div>';

            $data[] = [
                $start + $i + 1,
                '<code>' . esc((string) ($row['item_code'] ?? '-')) . '</code>',
                $assetText,
                esc((string) ($row['serial_number'] ?? '-')),
                esc((string) ($row['lab_name'] ?? '-')),
                esc(ucfirst($conditionLabel)),
                esc(ucfirst($inventoryLabel)),
                $loanableBadge,
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

    public function itemCreate()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $selectedAssetId = (int) $this->request->getGet('asset_id');
        $assets = db_connect()->table('lab_assets')
            ->select('id, name, asset_code, lab_id, condition_status')
            ->where('asset_type', 'equipment')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        return $this->renderView('loans/asset_items/create', [
            'title'           => 'Tambah Item Alat',
            'page_title'      => 'Tambah Item Alat',
            'assets'          => $assets,
            'labs'            => $this->getActiveLabs(),
            'selectedAssetId' => $selectedAssetId,
            'inventoryStatuses' => self::INVENTORY_STATUSES,
        ]);
    }

    public function itemStore()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $rules = [
            'asset_id'          => 'required|is_natural_no_zero',
            'item_code'         => 'permit_empty|max_length[80]',
            'serial_number'     => 'permit_empty|max_length[100]',
            'lab_id'            => 'permit_empty|is_natural_no_zero',
            'location_detail'   => 'permit_empty|max_length[150]',
            'condition_status'  => 'required|in_list[baik,perlu_perbaikan,rusak,rusak_ringan,rusak_berat]',
            'inventory_status'  => 'required|in_list[aktif,dipinjam,dalam_perbaikan,dihapuskan,hilang]',
            'acquisition_date'  => 'permit_empty|valid_date[Y-m-d]',
            'warranty_until'    => 'permit_empty|valid_date[Y-m-d]',
            'responsible_user_id' => 'permit_empty|is_natural_no_zero',
            'notes'             => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $assetId = (int) $this->request->getPost('asset_id');
        $asset = $this->assetModel->find($assetId);
        if (! $asset || ($asset['asset_type'] ?? null) !== 'equipment') {
            return redirect()->back()->withInput()->with('error', 'Master alat tidak ditemukan.');
        }

        $labId = $this->request->getPost('lab_id');
        $labId = ($labId === null || $labId === '') ? ((int) ($asset['lab_id'] ?? 0) ?: null) : (int) $labId;
        if ($labId !== null && ! $this->labModel->find($labId)) {
            return redirect()->back()->withInput()->with('error', 'Lab item tidak ditemukan.');
        }

        $itemCode = trim((string) $this->request->getPost('item_code'));
        if ($itemCode === '') {
            $itemCode = $this->generateItemCode($asset);
        } elseif ($this->isDuplicateItemCode($itemCode)) {
            return redirect()->back()->withInput()->with('error', 'Item code sudah digunakan.');
        }

        $conditionStatus = strtolower(trim((string) $this->request->getPost('condition_status')));
        $inventoryStatus = strtolower(trim((string) $this->request->getPost('inventory_status')));
        $isLoanable      = $this->request->getPost('is_loanable') ? 1 : 0;
        if (in_array($conditionStatus, ['rusak', 'rusak_berat'], true)) {
            $isLoanable = 0;
        }
        if ($inventoryStatus !== 'aktif') {
            $isLoanable = 0;
        }

        $this->assetItemModel->insert([
            'asset_id'            => $assetId,
            'item_code'           => $itemCode,
            'serial_number'       => trim((string) $this->request->getPost('serial_number')) ?: null,
            'lab_id'              => $labId,
            'location_detail'     => trim((string) $this->request->getPost('location_detail')) ?: null,
            'condition_status'    => $conditionStatus,
            'inventory_status'    => $inventoryStatus,
            'is_loanable'         => $isLoanable,
            'acquisition_date'    => trim((string) $this->request->getPost('acquisition_date')) ?: null,
            'warranty_until'      => trim((string) $this->request->getPost('warranty_until')) ?: null,
            'notes'               => trim((string) $this->request->getPost('notes')) ?: null,
            'responsible_user_id' => ($this->request->getPost('responsible_user_id') ?: null),
            'created_by'          => auth()->id(),
            'updated_by'          => auth()->id(),
        ]);

        $this->syncAssetStockAggregate($assetId);

        return redirect()->to('/admin/loans/asset-items?asset_id=' . $assetId)->with('success', 'Item alat berhasil ditambahkan.');
    }

    public function itemEdit(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $item = $this->assetItemModel->find($id);
        if (! $item) {
            return redirect()->to('/admin/loans/asset-items')->with('error', 'Item alat tidak ditemukan.');
        }

        $assets = db_connect()->table('lab_assets')
            ->select('id, name, asset_code, lab_id, condition_status')
            ->where('asset_type', 'equipment')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        return $this->renderView('loans/asset_items/edit', [
            'title'           => 'Edit Item Alat',
            'page_title'      => 'Edit Item Alat',
            'item'            => $item,
            'assets'          => $assets,
            'labs'            => $this->getActiveLabs(),
            'inventoryStatuses' => self::INVENTORY_STATUSES,
        ]);
    }

    public function itemUpdate(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $item = $this->assetItemModel->find($id);
        if (! $item) {
            return redirect()->to('/admin/loans/asset-items')->with('error', 'Item alat tidak ditemukan.');
        }

        $rules = [
            'asset_id'          => 'required|is_natural_no_zero',
            'item_code'         => 'required|max_length[80]',
            'serial_number'     => 'permit_empty|max_length[100]',
            'lab_id'            => 'permit_empty|is_natural_no_zero',
            'location_detail'   => 'permit_empty|max_length[150]',
            'condition_status'  => 'required|in_list[baik,perlu_perbaikan,rusak,rusak_ringan,rusak_berat]',
            'inventory_status'  => 'required|in_list[aktif,dipinjam,dalam_perbaikan,dihapuskan,hilang]',
            'acquisition_date'  => 'permit_empty|valid_date[Y-m-d]',
            'warranty_until'    => 'permit_empty|valid_date[Y-m-d]',
            'responsible_user_id' => 'permit_empty|is_natural_no_zero',
            'notes'             => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $assetId = (int) $this->request->getPost('asset_id');
        $asset = $this->assetModel->find($assetId);
        if (! $asset || ($asset['asset_type'] ?? null) !== 'equipment') {
            return redirect()->back()->withInput()->with('error', 'Master alat tidak ditemukan.');
        }

        $itemCode = trim((string) $this->request->getPost('item_code'));
        if ($this->isDuplicateItemCode($itemCode, $id)) {
            return redirect()->back()->withInput()->with('error', 'Item code sudah digunakan.');
        }

        $labId = $this->request->getPost('lab_id');
        $labId = ($labId === null || $labId === '') ? ((int) ($asset['lab_id'] ?? 0) ?: null) : (int) $labId;
        if ($labId !== null && ! $this->labModel->find($labId)) {
            return redirect()->back()->withInput()->with('error', 'Lab item tidak ditemukan.');
        }

        $conditionStatus = strtolower(trim((string) $this->request->getPost('condition_status')));
        $inventoryStatus = strtolower(trim((string) $this->request->getPost('inventory_status')));
        $isLoanable      = $this->request->getPost('is_loanable') ? 1 : 0;
        if (in_array($conditionStatus, ['rusak', 'rusak_berat'], true)) {
            $isLoanable = 0;
        }
        if ($inventoryStatus !== 'aktif') {
            $isLoanable = 0;
        }

        $previousAssetId = (int) ($item['asset_id'] ?? 0);

        $this->assetItemModel->update($id, [
            'asset_id'            => $assetId,
            'item_code'           => $itemCode,
            'serial_number'       => trim((string) $this->request->getPost('serial_number')) ?: null,
            'lab_id'              => $labId,
            'location_detail'     => trim((string) $this->request->getPost('location_detail')) ?: null,
            'condition_status'    => $conditionStatus,
            'inventory_status'    => $inventoryStatus,
            'is_loanable'         => $isLoanable,
            'acquisition_date'    => trim((string) $this->request->getPost('acquisition_date')) ?: null,
            'warranty_until'      => trim((string) $this->request->getPost('warranty_until')) ?: null,
            'notes'               => trim((string) $this->request->getPost('notes')) ?: null,
            'responsible_user_id' => ($this->request->getPost('responsible_user_id') ?: null),
            'updated_by'          => auth()->id(),
        ]);

        if ($previousAssetId > 0 && $previousAssetId !== $assetId) {
            $this->syncAssetStockAggregate($previousAssetId);
        }
        $this->syncAssetStockAggregate($assetId);

        return redirect()->to('/admin/loans/asset-items?asset_id=' . $assetId)->with('success', 'Item alat berhasil diperbarui.');
    }

    public function itemDelete(int $id)
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $item = $this->assetItemModel->find($id);
        if (! $item) {
            return redirect()->to('/admin/loans/asset-items')->with('error', 'Item alat tidak ditemukan.');
        }

        $activeAssignmentExists = db_connect()->table('loan_proposal_item_assignments')
            ->where('asset_item_id', $id)
            ->where('returned_at IS NULL', null, false)
            ->countAllResults() > 0;

        if ($activeAssignmentExists) {
            return redirect()->to('/admin/loans/asset-items?asset_id=' . (int) $item['asset_id'])
                ->with('error', 'Item tidak bisa dihapus karena masih terikat peminjaman aktif.');
        }

        $assetId = (int) ($item['asset_id'] ?? 0);
        $this->assetItemModel->delete($id);
        if ($assetId > 0) {
            $this->syncAssetStockAggregate($assetId);
        }

        return redirect()->to('/admin/loans/asset-items?asset_id=' . $assetId)->with('success', 'Item alat berhasil dihapus.');
    }

    public function itemBulkGenerate()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $assetId = (int) $this->request->getPost('asset_id');
        $qty     = (int) $this->request->getPost('qty');

        if ($assetId < 1 || $qty < 1) {
            return redirect()->to('/admin/loans/asset-items')->with('error', 'Asset dan jumlah generate wajib valid.');
        }

        if ($qty > 500) {
            return redirect()->to('/admin/loans/asset-items?asset_id=' . $assetId)->with('error', 'Maksimal generate sekaligus adalah 500 item.');
        }

        $asset = $this->assetModel->find($assetId);
        if (! $asset || ($asset['asset_type'] ?? null) !== 'equipment') {
            return redirect()->to('/admin/loans/asset-items')->with('error', 'Master alat tidak ditemukan.');
        }

        $created = 0;
        for ($i = 0; $i < $qty; $i++) {
            $conditionStatus = (string) ($asset['condition_status'] ?? 'baik');
            if (! in_array($conditionStatus, ['baik', 'perlu_perbaikan', 'rusak', 'rusak_ringan', 'rusak_berat'], true)) {
                $conditionStatus = 'baik';
            }

            $isLoanable = 1;
            if (in_array($conditionStatus, ['rusak', 'rusak_berat'], true)) {
                $isLoanable = 0;
            }

            $this->assetItemModel->insert([
                'asset_id'            => $assetId,
                'item_code'           => $this->generateItemCode($asset),
                'serial_number'       => null,
                'lab_id'              => (int) ($asset['lab_id'] ?? 0) ?: null,
                'location_detail'     => null,
                'condition_status'    => $conditionStatus,
                'inventory_status'    => 'aktif',
                'is_loanable'         => $isLoanable,
                'acquisition_date'    => null,
                'warranty_until'      => null,
                'notes'               => 'Generated bulk from item management page',
                'responsible_user_id' => null,
                'created_by'          => auth()->id(),
                'updated_by'          => auth()->id(),
            ]);
            $created++;
        }

        $this->syncAssetStockAggregate($assetId);

        return redirect()->to('/admin/loans/asset-items?asset_id=' . $assetId)
            ->with('success', 'Berhasil generate ' . $created . ' item alat.');
    }

    private function applyItemDatatableFilters(
        $builder,
        string $search,
        int $filterAssetId,
        int $filterLabId,
        string $filterCondition,
        string $filterInventory,
        string $filterLoanable
    ): void {
        if ($filterAssetId > 0) {
            $builder->where('ai.asset_id', $filterAssetId);
        }

        if ($filterLabId > 0) {
            $builder->where('ai.lab_id', $filterLabId);
        }

        $allowedConditions = ['baik', 'perlu_perbaikan', 'rusak', 'rusak_ringan', 'rusak_berat'];
        if (in_array($filterCondition, $allowedConditions, true)) {
            $builder->where('ai.condition_status', $filterCondition);
        }

        $allowedInventory = ['aktif', 'dipinjam', 'dalam_perbaikan', 'dihapuskan', 'hilang'];
        if (in_array($filterInventory, $allowedInventory, true)) {
            $builder->where('ai.inventory_status', $filterInventory);
        }

        if ($filterLoanable === '1' || $filterLoanable === '0') {
            $builder->where('ai.is_loanable', (int) $filterLoanable);
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('ai.item_code', $search)
                ->orLike('ai.serial_number', $search)
                ->orLike('a.name', $search)
                ->orLike('a.asset_code', $search)
                ->orLike('l.name', $search)
                ->groupEnd();
        }
    }

    private function guardAccess()
    {
        if (! activeGroupCan('lending.master.manage')) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke master aset.');
        }

        return null;
    }

    private function getActiveLabs(): array
    {
        return $this->labModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    private function getActiveCategories(): array
    {
        return $this->categoryModel
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    private function resolveCategoryName(string $rawCategory): ?string
    {
        $categoryName = trim($rawCategory);
        if ($categoryName === '') {
            return null;
        }

        $category = $this->categoryModel
            ->where('name', $categoryName)
            ->where('is_active', 1)
            ->first();

        return $category ? (string) $category['name'] : null;
    }

    private function resolveConditionStatus(string $rawStatus): ?string
    {
        $status = trim($rawStatus);
        $allowed = [
            self::CONDITION_BAIK,
            self::CONDITION_PERLU_PERBAIKAN,
            self::CONDITION_RUSAK,
        ];

        return in_array($status, $allowed, true) ? $status : null;
    }

    private function getActiveUnits(): array
    {
        return $this->unitModel
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Kumpulkan field-field inventaris dari POST. Semua opsional & nullable.
     */
    private function collectInventoryPayload(array $currentAsset = []): array
    {
        $req = $this->request;

        $acquisitionSourcePost = $req->getPost('acquisition_source');
        $acquisitionSource = $acquisitionSourcePost === null
            ? (string) ($currentAsset['acquisition_source'] ?? 'pembelian')
            : (string) $acquisitionSourcePost;
        if (! in_array($acquisitionSource, self::ACQUISITION_SOURCES, true)) {
            $acquisitionSource = 'pembelian';
        }

        $inventoryStatusPost = $req->getPost('inventory_status');
        $inventoryStatus = $inventoryStatusPost === null
            ? (string) ($currentAsset['inventory_status'] ?? 'aktif')
            : (string) $inventoryStatusPost;
        if (! in_array($inventoryStatus, self::INVENTORY_STATUSES, true)) {
            $inventoryStatus = 'aktif';
        }

        $unitId = $req->getPost('unit_id');
        if ($unitId === null && array_key_exists('unit_id', $currentAsset)) {
            $unitId = $currentAsset['unit_id'];
        }
        $unitId = ($unitId === null || $unitId === '') ? null : (int) $unitId;

        $responsibleUserId = $req->getPost('responsible_user_id');
        if ($responsibleUserId === null && array_key_exists('responsible_user_id', $currentAsset)) {
            $responsibleUserId = $currentAsset['responsible_user_id'];
        }
        $responsibleUserId = ($responsibleUserId === null || $responsibleUserId === '') ? null : (int) $responsibleUserId;

        $purchasePrice = $req->getPost('purchase_price');
        if ($purchasePrice === null && array_key_exists('purchase_price', $currentAsset)) {
            $purchasePrice = $currentAsset['purchase_price'];
        }
        $purchasePrice = ($purchasePrice === null || $purchasePrice === '') ? null : (float) $purchasePrice;

        $minimumStockPost = $req->getPost('minimum_stock');
        $minimumStock = $minimumStockPost === null
            ? (int) ($currentAsset['minimum_stock'] ?? 0)
            : (int) $minimumStockPost;

        $emptyToNull = static fn (?string $v): ?string => ($v === null || trim($v) === '') ? null : trim($v);

        $textOrFallback = static function (string $key) use ($req, $currentAsset, $emptyToNull): ?string {
            $posted = $req->getPost($key);
            if ($posted === null && array_key_exists($key, $currentAsset)) {
                return $emptyToNull((string) $currentAsset[$key]);
            }

            return $emptyToNull(is_string($posted) ? $posted : null);
        };

        return [
            'serial_number'       => $textOrFallback('serial_number'),
            'brand'               => $textOrFallback('brand'),
            'model'               => $textOrFallback('model'),
            'unit_id'             => $unitId,
            'acquisition_date'    => $textOrFallback('acquisition_date'),
            'acquisition_source'  => $acquisitionSource,
            'purchase_price'      => $purchasePrice,
            'supplier'            => $textOrFallback('supplier'),
            'funding_source'      => $textOrFallback('funding_source'),
            'warranty_until'      => $textOrFallback('warranty_until'),
            'inventory_status'    => $inventoryStatus,
            'responsible_user_id' => $responsibleUserId,
            'minimum_stock'       => max(0, $minimumStock),
            'notes'               => $textOrFallback('notes'),
        ];
    }

    private function deriveAssetMasterState(int $assetId, array $fallbackAsset): array
    {
        $db = db_connect();

        $total = (int) $db->table('asset_items')
            ->where('asset_id', $assetId)
            ->countAllResults();

        $available = (int) $db->table('asset_items')
            ->where('asset_id', $assetId)
            ->where('inventory_status', 'aktif')
            ->where('is_loanable', 1)
            ->countAllResults();

        if ($total < 1) {
            return [
                'stock_total'      => (int) ($fallbackAsset['stock_total'] ?? 0),
                'stock_available'  => (int) ($fallbackAsset['stock_available'] ?? 0),
                'is_loanable'      => (int) ($fallbackAsset['is_loanable'] ?? 1),
                'condition_status' => (string) ($fallbackAsset['condition_status'] ?? self::CONDITION_BAIK),
                'inventory_status' => (string) ($fallbackAsset['inventory_status'] ?? 'aktif'),
            ];
        }

        $hasBroken = $db->table('asset_items')
            ->where('asset_id', $assetId)
            ->whereIn('condition_status', ['rusak', 'rusak_berat'])
            ->countAllResults() > 0;

        $hasNeedsRepair = $db->table('asset_items')
            ->where('asset_id', $assetId)
            ->whereIn('condition_status', ['perlu_perbaikan', 'rusak_ringan'])
            ->countAllResults() > 0;

        $conditionStatus = self::CONDITION_BAIK;
        if ($hasBroken) {
            $conditionStatus = self::CONDITION_RUSAK;
        } elseif ($hasNeedsRepair) {
            $conditionStatus = self::CONDITION_PERLU_PERBAIKAN;
        }

        return [
            'stock_total'      => $total,
            'stock_available'  => $available,
            'is_loanable'      => $available > 0 ? 1 : 0,
            'condition_status' => $conditionStatus,
            'inventory_status' => $available > 0 ? 'aktif' : 'dipinjam',
        ];
    }

    /**
     * Generate kode aset format: LAB{labId}-{KAT3}-{YY}-{seq4}
     * Contoh: LAB1-ALA-26-0001
     */
    private function generateAssetCode(int $labId, string $categoryName): string
    {
        $catSlug = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $categoryName) ?: 'GEN', 0, 3));
        $year    = date('y');
        $prefix  = sprintf('LAB%d-%s-%s-', $labId, $catSlug, $year);

        $lastCode = db_connect()->table('lab_assets')
            ->like('asset_code', $prefix, 'after')
            ->orderBy('asset_code', 'DESC')
            ->limit(1)
            ->get()
            ->getRow('asset_code');

        $nextSeq = 1;
        if ($lastCode && preg_match('/-(\d+)$/', $lastCode, $m)) {
            $nextSeq = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    private function isDuplicateAssetCode(string $code, ?int $ignoreId = null): bool
    {
        $builder = $this->assetModel->where('asset_code', $code);
        if ($ignoreId !== null) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    private function handlePhotoUpload(?string $oldPhoto = null): ?string
    {
        $photo = $this->request->getFile('asset_photo');
        if (! $photo || ! $photo->isValid() || $photo->hasMoved()) {
            return null;
        }

        $uploadPath = FCPATH . 'uploads/assets';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        if (! empty($oldPhoto) && file_exists(FCPATH . $oldPhoto)) {
            unlink(FCPATH . $oldPhoto);
        }

        $photoName = 'asset_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $photo->getClientExtension();
        $photo->move($uploadPath, $photoName);

        $savedPath = $uploadPath . DIRECTORY_SEPARATOR . $photoName;
        $this->normalizePhotoImage($savedPath, strtolower($photo->getClientExtension()));
        return 'uploads/assets/' . $photoName;
    }

    private function normalizePhotoImage(string $fullPath, string $extension): void
    {
        if ($extension === 'svg') {
            return;
        }

        if (! file_exists($fullPath)) {
            return;
        }

        try {
            \Config\Services::image('gd')
                ->withFile($fullPath)
                ->fit(500, 500, 'center')
                ->save($fullPath, 85);
        } catch (\Throwable $e) {
            // Keep original upload when image manipulation library is unavailable.
        }
    }

    // -------------------------------------------------------------------------
    // Download (CSV / Excel)
    // -------------------------------------------------------------------------

    public function download()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $format = $this->request->getGet('format') ?? 'csv';
        if (! in_array($format, ['csv', 'excel'], true)) {
            $format = 'csv';
        }

        $builder = db_connect()->table('lab_assets a')
            ->select(
                'a.asset_code, a.name, l.name AS lab_name, a.category, a.brand, a.model,' .
                ' a.serial_number, a.condition_status, a.inventory_status, a.is_loanable,' .
                ' a.stock_total, a.stock_available, u.symbol AS unit_symbol, a.max_loan_hours,' .
                ' a.acquisition_source, a.acquisition_date, a.purchase_price, a.supplier,' .
                ' a.funding_source, a.warranty_until, a.is_active, a.specifications, a.notes'
            )
            ->join('labs l', 'l.id = a.lab_id', 'left')
            ->join('units u', 'u.id = a.unit_id', 'left')
            ->where('a.asset_type', 'equipment')
            ->orderBy('l.name', 'ASC')
            ->orderBy('a.name', 'ASC');

        $labId = (int) $this->request->getGet('lab_id');
        if ($labId > 0) {
            $builder->where('a.lab_id', $labId);
        }

        $category = trim((string) ($this->request->getGet('category') ?? ''));
        if ($category !== '') {
            $builder->where('a.category', $category);
        }

        $conditionStatus = trim((string) ($this->request->getGet('condition_status') ?? ''));
        if (in_array($conditionStatus, [self::CONDITION_BAIK, self::CONDITION_PERLU_PERBAIKAN, self::CONDITION_RUSAK], true)) {
            $builder->where('a.condition_status', $conditionStatus);
        }

        $inventoryStatus = trim((string) ($this->request->getGet('inventory_status') ?? ''));
        if (in_array($inventoryStatus, self::INVENTORY_STATUSES, true)) {
            $builder->where('a.inventory_status', $inventoryStatus);
        }

        $isLoanable = $this->request->getGet('is_loanable');
        if ($isLoanable !== null && $isLoanable !== '') {
            $builder->where('a.is_loanable', (int) $isLoanable);
        }

        $isActive = $this->request->getGet('is_active');
        if ($isActive !== null && $isActive !== '') {
            $builder->where('a.is_active', (int) $isActive);
        }

        $assets   = $builder->get()->getResultArray();
        $filename = 'master-alat-' . date('Ymd-His');

        if ($format === 'excel') {
            return $this->outputExcel($assets, $filename);
        }

        return $this->outputCsv($assets, $filename);
    }

    private function buildExportHeaders(): array
    {
        return [
            'No',
            'Kode Aset',
            'Nama Alat',
            'Lab',
            'Kategori',
            'Merk',
            'Model',
            'No. Seri',
            'Kondisi',
            'Status Inventaris',
            'Boleh Dipinjam',
            'Stok Total',
            'Stok Tersedia',
            'Satuan',
            'Maks Jam Pinjam',
            'Sumber Perolehan',
            'Tanggal Perolehan',
            'Harga Beli (Rp)',
            'Supplier',
            'Sumber Dana',
            'Garansi Hingga',
            'Status Aktif',
            'Spesifikasi',
            'Catatan',
        ];
    }

    private function buildExportRow(int $no, array $asset): array
    {
        $conditionLabel = [
            'baik'             => 'Baik',
            'perlu_perbaikan'  => 'Perlu Perbaikan',
            'rusak'            => 'Rusak',
        ][$asset['condition_status'] ?? ''] ?? ($asset['condition_status'] ?? '');

        $invLabel = ucwords(str_replace('_', ' ', $asset['inventory_status'] ?? ''));

        $maxLoanHours = (int) ($asset['max_loan_hours'] ?? 0);

        return [
            $no,
            $asset['asset_code']       ?? '',
            $asset['name']             ?? '',
            $asset['lab_name']         ?? '',
            $asset['category']         ?? '',
            $asset['brand']            ?? '',
            $asset['model']            ?? '',
            $asset['serial_number']    ?? '',
            $conditionLabel,
            $invLabel,
            (int) ($asset['is_loanable'] ?? 0) === 1 ? 'Ya' : 'Tidak',
            (int) ($asset['stock_total']     ?? 0),
            (int) ($asset['stock_available'] ?? 0),
            $asset['unit_symbol']      ?? '',
            $maxLoanHours === 0 ? 'Unlimited' : $maxLoanHours . ' jam',
            $asset['acquisition_source'] ?? '',
            $asset['acquisition_date']   ?? '',
            $asset['purchase_price'] !== null ? (float) $asset['purchase_price'] : '',
            $asset['supplier']           ?? '',
            $asset['funding_source']     ?? '',
            $asset['warranty_until']     ?? '',
            (int) ($asset['is_active'] ?? 0) === 1 ? 'Aktif' : 'Nonaktif',
            $asset['specifications']     ?? '',
            $asset['notes']              ?? '',
        ];
    }

    private function outputCsv(array $assets, string $filename): \CodeIgniter\HTTP\ResponseInterface
    {
        $headers = $this->buildExportHeaders();

        ob_start();
        $out = fopen('php://output', 'w');

        // UTF-8 BOM so Excel on Windows interprets encoding correctly.
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);

        foreach ($assets as $no => $asset) {
            fputcsv($out, $this->buildExportRow($no + 1, $asset));
        }

        fclose($out);
        $csv = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.csv"')
            ->setHeader('Cache-Control', 'no-store, no-cache')
            ->setBody($csv);
    }

    private function outputExcel(array $assets, string $filename): \CodeIgniter\HTTP\ResponseInterface
    {
        $headers = $this->buildExportHeaders();

        $html  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $html .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"';
        $html .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $html .= '<Worksheet ss:Name="Master Alat"><Table>' . "\n";

        // Header row
        $html .= '<Row>';
        foreach ($headers as $h) {
            $html .= '<Cell><Data ss:Type="String">' . htmlspecialchars((string) $h, ENT_XML1, 'UTF-8') . '</Data></Cell>';
        }
        $html .= '</Row>' . "\n";

        // Data rows
        foreach ($assets as $no => $asset) {
            $row  = $this->buildExportRow($no + 1, $asset);
            $html .= '<Row>';
            foreach ($row as $cell) {
                $type  = is_numeric($cell) ? 'Number' : 'String';
                $html .= '<Cell><Data ss:Type="' . $type . '">' . htmlspecialchars((string) $cell, ENT_XML1, 'UTF-8') . '</Data></Cell>';
            }
            $html .= '</Row>' . "\n";
        }

        $html .= '</Table></Worksheet></Workbook>';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.xls"')
            ->setHeader('Cache-Control', 'no-store, no-cache')
            ->setBody($html);
    }

    // -------------------------------------------------------------------------
    // Import
    // -------------------------------------------------------------------------

    public function downloadImportTemplate()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $headers = [
            'Nama Alat*',
            'Nama Lab*',
            'Kategori*',
            'Jumlah Item*',
            'Maks Jam Pinjam* (0=Unlimited)',
            'Merk',
            'Model',
            'Kode Aset (kosong=otomatis)',
            'Satuan (simbol)',
            'Sumber Perolehan (pembelian/hibah/pinjaman/produksi)',
            'Tanggal Perolehan (YYYY-MM-DD)',
            'Harga Beli',
            'Supplier',
            'Sumber Dana',
            'Garansi Hingga (YYYY-MM-DD)',
            'Status Aktif (Ya/Tidak)',
            'Stok Minimum',
            'Spesifikasi',
            'Catatan',
        ];

        $sample = [
            'Mikroskop Binokuler',
            'Lab Biologi',
            'Alat Optik',
            '5',
            '8',
            'Olympus',
            'CX23',
            '',
            'unit',
            'pembelian',
            '2024-01-15',
            '15000000',
            'PT Optik Jaya',
            'APBN',
            '2027-01-15',
            'Ya',
            '1',
            'Pembesaran 40x-1000x',
            '',
        ];

        // Fetch reference data from DB for the reference section
        $db   = db_connect();
        $labRows = $db->table('labs')->select('name')->where('is_active', 1)->orderBy('name', 'ASC')->get()->getResultArray();
        $labs = array_column($labRows, 'name');
        $categories = $this->categoryModel->where('is_active', 1)->orderBy('name', 'ASC')->findColumn('name');
        $units = $db->table('units')->where('is_active', 1)->orderBy('name', 'ASC')->get()->getResultArray();

        ob_start();
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);
        fputcsv($out, $sample);

        // Reference section — rows starting with # are ignored by the parser
        fputcsv($out, ['']);
        fputcsv($out, ['# ===== REFERENSI (baris ini diabaikan saat import) =====']);
        fputcsv($out, ['# DAFTAR NAMA LAB AKTIF (gunakan persis untuk kolom "Nama Lab*"):']);
        foreach (($labs ?? []) as $labName) {
            fputcsv($out, ['# LAB', $labName]);
        }
        fputcsv($out, ['# DAFTAR KATEGORI AKTIF (gunakan persis untuk kolom "Kategori*"):']);
        foreach (($categories ?? []) as $catName) {
            fputcsv($out, ['# KATEGORI', $catName]);
        }
        fputcsv($out, ['# DAFTAR SATUAN AKTIF (gunakan simbol untuk kolom "Satuan"):']);
        foreach (($units ?? []) as $unit) {
            fputcsv($out, ['# SATUAN', $unit['symbol'], $unit['name']]);
        }
        fputcsv($out, ['# SUMBER PEROLEHAN: pembelian | hibah | pinjaman | produksi']);
        fputcsv($out, ['# STATUS AKTIF: Ya | Tidak']);
        fputcsv($out, ['# FORMAT TANGGAL: YYYY-MM-DD  (contoh: 2025-01-15)']);

        fclose($out);
        $csv = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="template-import-alat.csv"')
            ->setHeader('Cache-Control', 'no-store, no-cache')
            ->setBody($csv);
    }

    public function import()
    {
        return redirect()->to('/admin/loans/assets/import');
    }

    public function downloadSampleImport()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $db   = db_connect();
        $labRows = $db->table('labs')->select('name')->where('is_active', 1)->orderBy('name', 'ASC')->get()->getResultArray();
        $labs = array_column($labRows, 'name');
        $cats = $this->categoryModel->where('is_active', 1)->orderBy('name', 'ASC')->findColumn('name');

        if (empty($labs) || empty($cats)) {
            return redirect()->to('/admin/loans/assets/import')
                ->with('error', 'Tidak bisa generate sample: tidak ada lab atau kategori aktif di database.');
        }

        $headers = [
            'Nama Alat*', 'Nama Lab*', 'Kategori*',
            'Jumlah Item*', 'Maks Jam Pinjam* (0=Unlimited)', 'Merk', 'Model',
            'Kode Aset (kosong=otomatis)', 'Satuan (simbol)',
            'Sumber Perolehan (pembelian/hibah/pinjaman/produksi)',
            'Tanggal Perolehan (YYYY-MM-DD)', 'Harga Beli', 'Supplier', 'Sumber Dana',
            'Garansi Hingga (YYYY-MM-DD)',
            'Status Aktif (Ya/Tidak)',
            'Stok Minimum', 'Spesifikasi', 'Catatan',
        ];

        // Pick up to 3 labs & 2 categories from DB
        $lab0 = $labs[0];
        $lab1 = $labs[1] ?? $labs[0];
        $lab2 = $labs[2] ?? $labs[0];
        $cat0 = $cats[0];
        $cat1 = $cats[1] ?? $cats[0];

        $sampleRows = [
            [$lab0, $cat0, '10', '8',  'Dell',   'OptiPlex 3080', '',        'unit', 'pembelian', '2023-06-01', '12000000', 'PT Solusi Komputer', 'APBN',        '2026-06-01', 'Ya', '2', 'Intel Core i5, RAM 8GB, SSD 256GB', ''],
            [$lab0, $cat0, '5',  '4',  'Logitech','MK235',        '',        'set',  'pembelian', '2022-03-10', '350000',   'Tokopedia',          'APBN',        '',           'Ya', '1', 'Keyboard + mouse wireless',         'Perlu penggantian baterai'],
            [$lab1, $cat0, '3',  '0',  'Cisco',  'Catalyst 2960', '',        'unit', 'pembelian', '2021-01-15', '8500000',  'PT Jaringan Nusantara', 'Hibah',   '2024-01-15', 'Ya', '1', 'Switch 24 port managed',            'Aset lama untuk praktikum'],
            [$lab1, $cat1, '8',  '0',  '',       '',              '',        'unit', 'hibah',     '2020-07-17', '',         '',                   'Hibah Industri', '',      'Ya', '0', '',                                  'Hibah dari alumni'],
            [$lab2, $cat0, '2',  '6',  'Epson',  'EB-X51',        'PRJ-001', 'unit', 'pembelian', '2024-02-20', '7200000',  'PT Optik Prima',     'BOPTN',       '2027-02-20', 'Ya', '1', 'Projector XGA 3800 lumen',          ''],
            [$lab2, $cat1, '30', '0',  '',       'Lipat 3x1m',    '',        'pcs',  'pembelian', '2023-09-01', '150000',   'Toko Perlengkapan',  'APBN',        '',           'Ya', '5', 'Meja lipat serbaguna',              ''],
            [$lab0, $cat0, '1',  '0',  'HP',     'LaserJet M402n','',        'unit', 'pembelian', '2019-05-12', '3500000',  'Bhinneka',           'APBN',        '',           'Tidak', '0', 'Printer laser A4',               'Untuk pengadaan ulang'],
            [$lab1, $cat0, '15', '0',  'TP-Link','TL-WA901ND',    '',        'unit', 'pembelian', '2022-11-05', '450000',   'Shopee',             'APBN',        '2025-11-05', 'Ya', '3', 'Access point 450 Mbps dual band',   ''],
            [$lab2, $cat0, '4',  '2',  '',       'Prototipe Sensor','',       'pcs',  'produksi',  '2025-01-01', '',         '',                   'Dana Penelitian', '',      'Ya', '1', 'Sensor suhu & kelembaban buatan lab','Prototipe internal'],
            [$lab1, $cat1, '6',  '4',  'ASUS',   'VA24DQ',        '',        'unit', 'pinjaman',  '2024-08-01', '2100000',  'Peminjam Eksternal', 'Pinjaman',    '2025-08-01', 'Ya', '1', 'Monitor IPS 24 inch Full HD',       'Dipinjam dari institusi mitra'],
        ];

        ob_start();
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);

        foreach ($sampleRows as $i => $cols) {
            [$labName, $catName, $qty, $maxHour, $brand, $model, $code,
             $unitSymbol, $acqSrc, $acqDate, $price, $supplier, $fund, $warranty,
             $isActive, $minStock, $spec, $notes] = $cols;

            fputcsv($out, [
                "Alat Contoh " . ($i + 1),  // Nama Alat
                $labName, $catName, $qty, $maxHour,
                $brand, $model, $code, $unitSymbol, $acqSrc, $acqDate,
                $price, $supplier, $fund, $warranty,
                $isActive, $minStock, $spec, $notes,
            ]);
        }

        fclose($out);
        $csv = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="sample-import-alat.csv"')
            ->setHeader('Cache-Control', 'no-store, no-cache')
            ->setBody($csv);
    }

    public function importForm()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $db   = db_connect();
        $labs = $db->table('labs')->where('is_active', 1)->orderBy('name', 'ASC')->get()->getResultArray();
        $categories = $this->categoryModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        $units = $db->table('units')->where('is_active', 1)->orderBy('name', 'ASC')->get()->getResultArray();

        return $this->renderView('loans/assets/import_form', [
            'title'      => 'Import Master Alat',
            'page_title' => 'Import Data Master Alat',
            'labs'       => $labs,
            'categories' => $categories,
            'units'      => $units,
        ]);
    }

    public function importPreview()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $file = $this->request->getFile('import_file');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to('/admin/loans/assets/import')->with('error', 'File import tidak valid.');
        }

        if (strtolower($file->getClientExtension()) !== 'csv') {
            return redirect()->to('/admin/loans/assets/import')->with('error', 'Hanya file CSV yang didukung.');
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return redirect()->to('/admin/loans/assets/import')->with('error', 'Ukuran file terlalu besar (maks 2 MB).');
        }

        // Move uploaded file to a known writable location for the next step
        $tmpDir = WRITEPATH . 'uploads/import_tmp/';
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }
        $tmpName = 'import_assets_' . (int) auth()->id() . '_' . time() . '.csv';
        $file->move($tmpDir, $tmpName);
        $tmpPath = $tmpDir . $tmpName;

        // Store path in session for importProcess()
        session()->set('import_assets_tmp', $tmpPath);

        $rows = $this->parseImportFile($tmpPath);

        if (empty($rows)) {
            return redirect()->to('/admin/loans/assets/import')->with('error', 'File import kosong atau tidak dapat dibaca.');
        }

        $validCount = count(array_filter($rows, static fn ($r) => $r['valid']));
        $errorCount = count($rows) - $validCount;

        return $this->renderView('loans/assets/import_preview', [
            'title'      => 'Preview Import Alat',
            'page_title' => 'Preview Import Data Master Alat',
            'rows'       => $rows,
            'validCount' => $validCount,
            'errorCount' => $errorCount,
            'totalCount' => count($rows),
        ]);
    }

    public function importProcess()
    {
        if ($guard = $this->guardAccess()) {
            return $guard;
        }

        $tmpPath = session()->get('import_assets_tmp');
        if (! $tmpPath || ! file_exists($tmpPath)) {
            return redirect()->to('/admin/loans/assets/import')
                ->with('error', 'Sesi import tidak ditemukan atau sudah kadaluarsa. Silakan upload ulang file.');
        }

        $rows = $this->parseImportFile($tmpPath);

        // Clean up temp file
        @unlink($tmpPath);
        session()->remove('import_assets_tmp');

        $successRows = [];
        $errorRows   = [];

        foreach ($rows as $row) {
            if (! $row['valid']) {
                $errorRows[] = $row;
                continue;
            }

            $assetCode = $row['asset_code'];
            if ($assetCode === '') {
                $assetCode = $this->generateAssetCode($row['lab_id'], $row['resolved_category']);
            }

            $this->assetModel->insert(array_merge($row['payload'], [
                'asset_code' => $assetCode,
                'created_by' => auth()->id(),
            ]));

            $assetId = (int) $this->assetModel->getInsertID();
            if ($assetId > 0) {
                $this->ensureImportedAssetItems($assetId, $row['qty_items'], $row['lab_id'], $row['payload']);
                $this->syncAssetStockAggregate($assetId);
            }

            $successRows[] = array_merge($row, [
                'asset_code' => $assetCode,
                'created_items' => (int) $row['qty_items'],
            ]);
        }

        return $this->renderView('loans/assets/import_result', [
            'title'        => 'Hasil Import Alat',
            'page_title'   => 'Hasil Import Data Master Alat',
            'successRows'  => $successRows,
            'errorRows'    => $errorRows,
            'successCount' => count($successRows),
            'errorCount'   => count($errorRows),
        ]);
    }

    private function parseImportFile(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            return [];
        }

        // Strip UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Skip header row
        $headerRow = fgetcsv($handle);
        if (! $headerRow) {
            fclose($handle);
            return [];
        }

        // Preload lookups
        $db   = db_connect();
        $labs = $db->table('labs')->where('is_active', 1)->get()->getResultArray();
        $labMap = [];
        foreach ($labs as $lab) {
            $labMap[strtolower(trim($lab['name']))] = (int) $lab['id'];
        }

        $units = $db->table('units')->where('is_active', 1)->get()->getResultArray();
        $unitMap = [];
        foreach ($units as $unit) {
            $unitMap[strtolower(trim($unit['symbol']))] = (int) $unit['id'];
        }

        $categories = $this->categoryModel->where('is_active', 1)->findAll();
        $catMap = [];
        foreach ($categories as $cat) {
            $catMap[strtolower(trim($cat['name']))] = $cat['name'];
        }

        $rows       = [];
        $rowNum     = 1;
        $seenCodes  = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;

            // Skip empty rows
            if (count(array_filter($row, static fn ($v) => trim($v) !== '')) === 0) {
                continue;
            }

            // Skip reference/comment rows (start with #)
            if (isset($row[0]) && str_starts_with(ltrim($row[0]), '#')) {
                continue;
            }

            $row = array_pad($row, 19, '');

            [
                $name, $labName, $category, $qtyItems, $maxLoanHours,
                $brand, $model, $assetCode,
                $unitSymbol, $acquisitionSource, $acquisitionDate,
                $purchasePrice, $supplier, $fundingSource, $warrantyUntil,
                $isActive, $minimumStock, $specifications, $notes,
            ] = $row;

            $name            = trim($name);
            $labName         = trim($labName);
            $category        = trim($category);
            $assetCodeTrim   = trim($assetCode);

            $errors = [];

            if ($name === '') {
                $errors[] = 'Nama alat wajib diisi';
            }
            if ($labName === '') {
                $errors[] = 'Nama lab wajib diisi';
            }
            if ($category === '') {
                $errors[] = 'Kategori wajib diisi';
            }

            $labId = $labMap[strtolower($labName)] ?? null;
            if ($labName !== '' && $labId === null) {
                $errors[] = "Lab '{$labName}' tidak ditemukan atau tidak aktif";
            }

            $resolvedCategory = $catMap[strtolower($category)] ?? null;
            if ($category !== '' && $resolvedCategory === null) {
                $errors[] = "Kategori '{$category}' tidak ditemukan atau tidak aktif";
            }

            $qtyItemsInt = (int) trim($qtyItems);
            if ($qtyItemsInt < 1) {
                $errors[] = 'Jumlah item wajib diisi minimal 1';
            }

            // Asset code duplicate check
            if ($assetCodeTrim !== '') {
                if (isset($seenCodes[$assetCodeTrim])) {
                    $errors[] = "Kode aset '{$assetCodeTrim}' duplikat dalam file (sama dengan baris {$seenCodes[$assetCodeTrim]})";
                } elseif ($this->isDuplicateAssetCode($assetCodeTrim)) {
                    $errors[] = "Kode aset '{$assetCodeTrim}' sudah ada di database";
                } else {
                    $seenCodes[$assetCodeTrim] = $rowNum;
                }
            }

            $maxLoanHoursInt = max(0, (int) $maxLoanHours);

            $unitId = null;
            $unitSymbolTrim = strtolower(trim($unitSymbol));
            if ($unitSymbolTrim !== '') {
                $unitId = $unitMap[$unitSymbolTrim] ?? null;
            }

            $acquisitionSourceTrim = strtolower(trim($acquisitionSource));
            if (! in_array($acquisitionSourceTrim, self::ACQUISITION_SOURCES, true)) {
                $acquisitionSourceTrim = 'pembelian';
            }

            $isActiveInt      = (strtolower(trim($isActive)) !== 'tidak') ? 1 : 0;
            $purchasePriceVal = (trim($purchasePrice) !== '') ? (float) $purchasePrice : null;
            $minimumStockInt  = max(0, (int) $minimumStock);

            $emptyToNull = static fn (string $v): ?string => trim($v) === '' ? null : trim($v);

            $acquisitionDateVal = $emptyToNull($acquisitionDate);
            $warrantyUntilVal   = $emptyToNull($warrantyUntil);

            if ($acquisitionDateVal !== null && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $acquisitionDateVal)) {
                $acquisitionDateVal = null;
            }
            if ($warrantyUntilVal !== null && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $warrantyUntilVal)) {
                $warrantyUntilVal = null;
            }

            $valid   = empty($errors);
            $payload = null;

            if ($valid && $labId !== null && $resolvedCategory !== null) {
                $payload = [
                    'name'               => $name,
                    'lab_id'             => $labId,
                    'asset_type'         => 'equipment',
                    'category'           => $resolvedCategory,
                    'location'           => null,
                    'specifications'     => $emptyToNull($specifications),
                    'max_loan_hours'     => $maxLoanHoursInt,
                    'stock_total'        => 0,
                    'stock_available'    => 0,
                    'is_active'          => $isActiveInt,
                    'is_loanable'        => 0,
                    'condition_status'   => self::CONDITION_BAIK,
                    'brand'              => $emptyToNull($brand),
                    'model'              => $emptyToNull($model),
                    'unit_id'            => $unitId,
                    'acquisition_date'   => $acquisitionDateVal,
                    'acquisition_source' => $acquisitionSourceTrim,
                    'purchase_price'     => $purchasePriceVal,
                    'supplier'           => $emptyToNull($supplier),
                    'funding_source'     => $emptyToNull($fundingSource),
                    'warranty_until'     => $warrantyUntilVal,
                    'inventory_status'   => 'aktif',
                    'minimum_stock'      => $minimumStockInt,
                    'notes'              => $emptyToNull($notes),
                ];
            }

            $rows[] = [
                'row_num'          => $rowNum,
                'name'             => $name,
                'lab_name'         => $labName,
                'lab_id'           => $labId,
                'category'         => $category,
                'resolved_category'=> $resolvedCategory,
                'qty_items'        => $qtyItemsInt,
                'asset_code'       => $assetCodeTrim,
                'brand'            => trim($brand),
                'model'            => trim($model),
                'errors'           => $errors,
                'valid'            => $valid,
                'payload'          => $payload,
            ];
        }

        fclose($handle);
        return $rows;
    }

    private function ensureImportedAssetItems(int $assetId, int $qtyItems, int $labId, array $payload): void
    {
        $qtyItems = max(0, $qtyItems);
        if ($qtyItems < 1) {
            return;
        }

        $asset = $this->assetModel->find($assetId);
        if (! is_array($asset)) {
            return;
        }

        $acquisitionDate = $payload['acquisition_date'] ?? null;
        $warrantyUntil = $payload['warranty_until'] ?? null;

        for ($i = 0; $i < $qtyItems; $i++) {
            $itemCode = $this->generateItemCode($asset);
            $this->assetItemModel->insert([
                'asset_id'         => $assetId,
                'item_code'        => $itemCode,
                'serial_number'    => null,
                'lab_id'           => $labId,
                'location_detail'  => null,
                'condition_status' => 'baik',
                'inventory_status' => 'aktif',
                'is_loanable'      => 1,
                'acquisition_date' => $acquisitionDate,
                'warranty_until'   => $warrantyUntil,
                'notes'            => null,
                'created_by'       => auth()->id(),
                'updated_by'       => auth()->id(),
            ]);
        }
    }

    private function generateItemCode(array $asset): string
    {
        $assetId = (int) ($asset['id'] ?? 0);
        $base = trim((string) ($asset['asset_code'] ?? ''));
        if ($base === '') {
            $base = 'AST-' . str_pad((string) $assetId, 4, '0', STR_PAD_LEFT);
        }

        $lastItemCode = db_connect()->table('asset_items')
            ->select('item_code')
            ->where('asset_id', $assetId)
            ->like('item_code', $base . '-', 'after')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRow('item_code');

        $nextSeq = 1;
        if ($lastItemCode && preg_match('/-(\d{4})$/', (string) $lastItemCode, $match)) {
            $nextSeq = ((int) $match[1]) + 1;
        }

        $candidate = $base . '-' . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
        while ($this->isDuplicateItemCode($candidate)) {
            $nextSeq++;
            $candidate = $base . '-' . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    private function isDuplicateItemCode(string $itemCode, ?int $ignoreId = null): bool
    {
        $builder = $this->assetItemModel->where('item_code', $itemCode);
        if ($ignoreId !== null) {
            $builder->where('id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    private function syncAssetStockAggregate(int $assetId): void
    {
        if ($assetId < 1) {
            return;
        }

        $db = db_connect();

        $asset = $db->table('lab_assets')
            ->select('condition_status')
            ->where('id', $assetId)
            ->get()
            ->getRowArray();

        $fallbackCondition = (string) ($asset['condition_status'] ?? self::CONDITION_BAIK);
        if (! in_array($fallbackCondition, [self::CONDITION_BAIK, self::CONDITION_PERLU_PERBAIKAN, self::CONDITION_RUSAK], true)) {
            $fallbackCondition = self::CONDITION_BAIK;
        }

        $total = (int) $db->table('asset_items')
            ->where('asset_id', $assetId)
            ->countAllResults();

        $available = (int) $db->table('asset_items')
            ->where('asset_id', $assetId)
            ->where('inventory_status', 'aktif')
            ->where('is_loanable', 1)
            ->countAllResults();

        $loanableCount = (int) $db->table('asset_items')
            ->where('asset_id', $assetId)
            ->where('is_loanable', 1)
            ->where('inventory_status !=', 'hilang')
            ->countAllResults();

        $brokenCount = (int) $db->table('asset_items')
            ->where('asset_id', $assetId)
            ->whereIn('condition_status', ['rusak', 'rusak_berat'])
            ->countAllResults();

        $needsRepairCount = (int) $db->table('asset_items')
            ->where('asset_id', $assetId)
            ->whereIn('condition_status', ['perlu_perbaikan', 'rusak', 'rusak_ringan', 'rusak_berat'])
            ->countAllResults();

        $inventoryStatus = $total <= 0 ? 'hilang' : ($available > 0 ? 'aktif' : 'dipinjam');

        $conditionStatus = $fallbackCondition;
        if ($total > 0) {
            if ($brokenCount >= $total) {
                $conditionStatus = self::CONDITION_RUSAK;
            } elseif ($needsRepairCount > 0) {
                $conditionStatus = self::CONDITION_PERLU_PERBAIKAN;
            } else {
                $conditionStatus = self::CONDITION_BAIK;
            }
        }

        $db->table('lab_assets')
            ->where('id', $assetId)
            ->update([
                'stock_total'      => $total,
                'stock_available'  => $available,
                'inventory_status' => $inventoryStatus,
                'condition_status' => $conditionStatus,
                'is_loanable'      => $loanableCount > 0 ? 1 : 0,
                'updated_by'       => auth()->id(),
            ]);
    }
}
