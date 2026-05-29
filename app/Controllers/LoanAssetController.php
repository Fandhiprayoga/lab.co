<?php

namespace App\Controllers;

use App\Models\AssetCategoryModel;
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
    protected LabModel $labModel;
    protected AssetCategoryModel $categoryModel;
    protected UnitModel $unitModel;

    public function __construct()
    {
        $this->assetModel    = new LabAssetModel();
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
            'stock_total'      => 'required|is_natural_no_zero',
            'stock_available'  => 'required|is_natural',
            'condition_status' => 'required|in_list[baik,perlu_perbaikan,rusak]',
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
            'inventory_status'   => 'permit_empty|in_list[aktif,dipinjam,dalam_perbaikan,dihapuskan,hilang]',
            'responsible_user_id'=> 'permit_empty|is_natural_no_zero',
            'minimum_stock'      => 'permit_empty|is_natural',
            'notes'              => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $stockTotal = (int) $this->request->getPost('stock_total');
        $stockAvail = (int) $this->request->getPost('stock_available');
        $maxLoanHours = (int) $this->request->getPost('max_loan_hours');

        if ($stockAvail > $stockTotal) {
            return redirect()->back()->withInput()->with('error', 'Stok tersedia tidak boleh melebihi stok total.');
        }

        $labId = (int) $this->request->getPost('lab_id');
        if (! $this->labModel->find($labId)) {
            return redirect()->back()->withInput()->with('error', 'Lab tujuan tidak ditemukan.');
        }

        $categoryName = $this->resolveCategoryName((string) $this->request->getPost('category'));
        if ($categoryName === null) {
            return redirect()->back()->withInput()->with('error', 'Kategori alat wajib dipilih dari master kategori aktif.');
        }

        $conditionStatus = $this->resolveConditionStatus((string) $this->request->getPost('condition_status'));
        if ($conditionStatus === null) {
            return redirect()->back()->withInput()->with('error', 'Status kondisi alat tidak valid.');
        }

        $isLoanable = $this->request->getPost('is_loanable') ? 1 : 0;
        if ($conditionStatus === self::CONDITION_RUSAK) {
            $isLoanable = 0;
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
            'stock_total'     => $stockTotal,
            'stock_available' => $stockAvail,
            'is_active'       => $this->request->getPost('is_active') ? 1 : 0,
            'is_loanable'     => $isLoanable,
            'condition_status'=> $conditionStatus,
            'created_by'      => auth()->id(),
            'asset_code'      => $assetCode,
        ], $inventory));

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
            'stock_total'      => 'required|is_natural_no_zero',
            'stock_available'  => 'required|is_natural',
            'condition_status' => 'required|in_list[baik,perlu_perbaikan,rusak]',
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
            'inventory_status'   => 'permit_empty|in_list[aktif,dipinjam,dalam_perbaikan,dihapuskan,hilang]',
            'responsible_user_id'=> 'permit_empty|is_natural_no_zero',
            'minimum_stock'      => 'permit_empty|is_natural',
            'notes'              => 'permit_empty|max_length[2000]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $stockTotal = (int) $this->request->getPost('stock_total');
        $stockAvail = (int) $this->request->getPost('stock_available');
        $labId      = (int) $this->request->getPost('lab_id');
        $maxLoanHoursPost = $this->request->getPost('max_loan_hours');
        $maxLoanHours = ($maxLoanHoursPost === null || $maxLoanHoursPost === '')
            ? (int) $asset['max_loan_hours']
            : (int) $maxLoanHoursPost;

        if ($stockTotal < 1 || $stockAvail < 0 || $stockAvail > $stockTotal) {
            return redirect()->back()->withInput()->with('error', 'Nilai stok tidak valid.');
        }

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

        $conditionStatus = $this->resolveConditionStatus((string) $this->request->getPost('condition_status'));
        if ($conditionStatus === null) {
            return redirect()->back()->withInput()->with('error', 'Status kondisi alat tidak valid.');
        }

        $isLoanable = $this->request->getPost('is_loanable') ? 1 : 0;
        if ($conditionStatus === self::CONDITION_RUSAK) {
            $isLoanable = 0;
        }
        $photoPath = $this->handlePhotoUpload($asset['photo'] ?? null);

        $inventory = $this->collectInventoryPayload();
        $assetCode = trim((string) $this->request->getPost('asset_code'));
        if ($assetCode === '') {
            $assetCode = $asset['asset_code'] ?? $this->generateAssetCode($labId, $categoryName);
        } elseif ($this->isDuplicateAssetCode($assetCode, $id)) {
            return redirect()->back()->withInput()->with('error', 'Kode aset sudah digunakan, gunakan kode lain.');
        }

        $payload = array_merge([
            'name'            => trim((string) $this->request->getPost('name')),
            'lab_id'          => $labId,
            'asset_type'      => 'equipment',
            'category'        => $categoryName,
            'location'        => null,
            'specifications'  => trim((string) $this->request->getPost('specifications')) ?: null,
            'max_loan_hours'  => $maxLoanHours,
            'stock_total'     => $stockTotal,
            'stock_available' => $stockAvail,
            'is_active'       => $this->request->getPost('is_active') ? 1 : 0,
            'is_loanable'     => $isLoanable,
            'condition_status'=> $conditionStatus,
            'asset_code'      => $assetCode,
            'updated_by'      => auth()->id(),
        ], $inventory);

        if ($photoPath !== null) {
            $payload['photo'] = $photoPath;
        }

        $this->assetModel->update($id, $payload);

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
    private function collectInventoryPayload(): array
    {
        $req = $this->request;

        $acquisitionSource = (string) $req->getPost('acquisition_source');
        if (! in_array($acquisitionSource, self::ACQUISITION_SOURCES, true)) {
            $acquisitionSource = 'pembelian';
        }

        $inventoryStatus = (string) $req->getPost('inventory_status');
        if (! in_array($inventoryStatus, self::INVENTORY_STATUSES, true)) {
            $inventoryStatus = 'aktif';
        }

        $unitId = $req->getPost('unit_id');
        $unitId = ($unitId === null || $unitId === '') ? null : (int) $unitId;

        $responsibleUserId = $req->getPost('responsible_user_id');
        $responsibleUserId = ($responsibleUserId === null || $responsibleUserId === '') ? null : (int) $responsibleUserId;

        $purchasePrice = $req->getPost('purchase_price');
        $purchasePrice = ($purchasePrice === null || $purchasePrice === '') ? null : (float) $purchasePrice;

        $minimumStock = (int) $req->getPost('minimum_stock');

        $emptyToNull = static fn (?string $v): ?string => ($v === null || trim($v) === '') ? null : trim($v);

        return [
            'serial_number'       => $emptyToNull($req->getPost('serial_number')),
            'brand'               => $emptyToNull($req->getPost('brand')),
            'model'               => $emptyToNull($req->getPost('model')),
            'unit_id'             => $unitId,
            'acquisition_date'    => $emptyToNull($req->getPost('acquisition_date')),
            'acquisition_source'  => $acquisitionSource,
            'purchase_price'      => $purchasePrice,
            'supplier'            => $emptyToNull($req->getPost('supplier')),
            'funding_source'      => $emptyToNull($req->getPost('funding_source')),
            'warranty_until'      => $emptyToNull($req->getPost('warranty_until')),
            'inventory_status'    => $inventoryStatus,
            'responsible_user_id' => $responsibleUserId,
            'minimum_stock'       => max(0, $minimumStock),
            'notes'               => $emptyToNull($req->getPost('notes')),
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
}
