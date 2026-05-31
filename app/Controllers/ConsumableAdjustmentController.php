<?php

namespace App\Controllers;

use App\Models\ConsumableItemModel;
use App\Models\ConsumableStockAdjustmentModel;
use CodeIgniter\I18n\Time;

class ConsumableAdjustmentController extends BaseController
{
    protected ConsumableItemModel $itemModel;
    protected ConsumableStockAdjustmentModel $adjustmentModel;

    public function __construct()
    {
        $this->itemModel       = new ConsumableItemModel();
        $this->adjustmentModel = new ConsumableStockAdjustmentModel();
    }

    public function create(int $consumableId)
    {
        if (! activeGroupCan('bhp.stock.adjust')) {
            return redirect()->to('/consumables')->with('error', 'Akses ditolak.');
        }

        $item = $this->itemModel
            ->select('consumable_items.*, consumable_categories.name AS category_name, units.symbol AS unit_symbol, labs.name AS lab_name')
            ->join('consumable_categories', 'consumable_categories.id = consumable_items.category_id', 'left')
            ->join('units', 'units.id = consumable_items.unit_id', 'left')
            ->join('labs', 'labs.id = consumable_items.lab_id', 'left')
            ->find($consumableId);

        if (! $item || ! $item['is_active']) {
            return redirect()->to('/consumables')->with('error', 'Bahan tidak ditemukan.');
        }

        return $this->renderView('consumables/adjustments/create', [
            'title'      => 'Penyesuaian Stok',
            'page_title' => 'Penyesuaian Stok: ' . $item['name'],
            'item'       => $item,
        ]);
    }

    public function store(int $consumableId)
    {
        if (! activeGroupCan('bhp.stock.adjust')) {
            return redirect()->to('/consumables')->with('error', 'Akses ditolak.');
        }

        $rules = [
            'adjustment_type' => 'required|in_list[susut,rusak,tumpah,koreksi,masuk]',
            'qty'             => 'required|numeric|greater_than[0]',
            'reason'          => 'permit_empty|max_length[500]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $item = $this->itemModel->find($consumableId);
        if (! $item || ! $item['is_active']) {
            return redirect()->to('/consumables')->with('error', 'Bahan tidak ditemukan.');
        }

        $type = $this->request->getPost('adjustment_type');
        $qty  = (float) $this->request->getPost('qty');

        // masuk → tambah stok; tipe lain → kurangi stok
        if ($type === 'masuk') {
            $newTotal     = (float) $item['stock_total'] + $qty;
            $newAvailable = (float) $item['stock_available'] + $qty;
        } else {
            $newTotal     = max(0, (float) $item['stock_total'] - $qty);
            $newAvailable = max(0, (float) $item['stock_available'] - $qty);
        }

        $db = db_connect();
        $db->transStart();

        $this->itemModel->update($consumableId, [
            'stock_total'     => $newTotal,
            'stock_available' => $newAvailable,
        ]);

        $this->adjustmentModel->insert([
            'consumable_id'   => $consumableId,
            'adjustment_type' => $type,
            'qty'             => $qty,
            'reason'          => trim((string) $this->request->getPost('reason')) ?: null,
            'adjusted_by'     => auth()->id(),
            'adjusted_at'     => Time::now()->toDateTimeString(),
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan penyesuaian stok. Coba lagi.');
        }

        return redirect()->to('/consumables')->with('success', 'Stok berhasil disesuaikan.');
    }

    // ------------------------------------------------------------------
    // Riwayat seluruh penyesuaian stok
    // ------------------------------------------------------------------

    public function history()
    {
        if (! activeGroupCan('bhp.stock.adjust')) {
            return redirect()->to('/consumables')->with('error', 'Akses ditolak.');
        }

        $labs = model('LabModel')->where('is_active', 1)->orderBy('name', 'ASC')->findAll();

        $typeLabels = [
            'masuk'   => 'Bahan Masuk',
            'susut'   => 'Susut',
            'rusak'   => 'Rusak',
            'tumpah'  => 'Tumpah',
            'koreksi' => 'Koreksi',
        ];

        return $this->renderView('consumables/adjustments/history', [
            'title'      => 'Riwayat Penyesuaian Stok',
            'page_title' => 'Riwayat Penyesuaian Stok BHP',
            'labs'       => $labs,
            'typeLabels' => $typeLabels,
        ]);
    }

    // ------------------------------------------------------------------
    // AJAX: DataTables server-side untuk riwayat penyesuaian
    // ------------------------------------------------------------------

    public function datatableHistory()
    {
        if (! activeGroupCan('bhp.stock.adjust')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $req    = $this->request;
        $draw   = (int) $req->getGet('draw');
        $start  = max(0, (int) $req->getGet('start'));
        $length = (int) $req->getGet('length');
        if ($length <= 0) { $length = 25; }

        $search      = trim((string) ($req->getGet('search')['value'] ?? ''));
        $orderCol    = (int) ($req->getGet('order')[0]['column'] ?? 1);
        $orderDir    = strtolower((string) ($req->getGet('order')[0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $filterLab   = (int) ($req->getGet('filter_lab')   ?? 0);
        $filterType  = (string) ($req->getGet('filter_type')  ?? '');
        $filterFrom  = (string) ($req->getGet('filter_from')  ?? '');
        $filterUntil = (string) ($req->getGet('filter_until') ?? '');

        $colMap = [
            1 => 'csa.adjusted_at',
            2 => 'ci.name',
            3 => 'labs.name',
            4 => 'csa.adjustment_type',
            5 => 'csa.qty',
        ];
        $orderField = $colMap[$orderCol] ?? 'csa.adjusted_at';

        $db = db_connect();

        $recordsTotal = (int) $db->table('consumable_stock_adjustments')->countAllResults();

        $applyFilters = function ($builder) use ($search, $filterLab, $filterType, $filterFrom, $filterUntil) {
            if ($search !== '') {
                $builder->groupStart()
                    ->like('ci.name', $search)
                    ->orLike('labs.name', $search)
                    ->orLike('u.username', $search)
                    ->orLike('csa.reason', $search)
                    ->groupEnd();
            }
            if ($filterLab > 0)     { $builder->where('ci.lab_id', $filterLab); }
            if ($filterType !== '')  { $builder->where('csa.adjustment_type', $filterType); }
            if ($filterFrom !== '')  { $builder->where('DATE(csa.adjusted_at) >=', $filterFrom); }
            if ($filterUntil !== '') { $builder->where('DATE(csa.adjusted_at) <=', $filterUntil); }
        };

        $joins = function ($builder) {
            $builder->join('consumable_items ci',  'ci.id = csa.consumable_id', 'left')
                    ->join('units',                'units.id = ci.unit_id',     'left')
                    ->join('labs',                 'labs.id = ci.lab_id',       'left')
                    ->join('users u',              'u.id = csa.adjusted_by',    'left');
        };

        // Count filtered
        $cnt = $db->table('consumable_stock_adjustments csa')
            ->select('COUNT(csa.id) AS cnt');
        $joins($cnt);
        $applyFilters($cnt);
        $recordsFiltered = (int) ($cnt->get()->getRow()->cnt ?? 0);

        // Data
        $qb = $db->table('consumable_stock_adjustments csa')
            ->select('csa.id, csa.adjusted_at, csa.adjustment_type, csa.qty, csa.reason,
                      ci.name AS item_name,
                      units.symbol AS unit_symbol,
                      labs.name AS lab_name,
                      u.username AS adjusted_by_name');
        $joins($qb);
        $applyFilters($qb);
        $rows = $qb->orderBy($orderField, $orderDir)->limit($length, $start)->get()->getResultArray();

        $typeBadge = [
            'masuk'   => '<span class="badge badge-success">Bahan Masuk</span>',
            'susut'   => '<span class="badge badge-warning">Susut</span>',
            'rusak'   => '<span class="badge badge-danger">Rusak</span>',
            'tumpah'  => '<span class="badge badge-danger">Tumpah</span>',
            'koreksi' => '<span class="badge badge-info">Koreksi</span>',
        ];

        $data = [];
        foreach ($rows as $i => $row) {
            $type = $row['adjustment_type'];
            $sym  = esc($row['unit_symbol'] ?? '');
            $qty  = rtrim(rtrim(number_format((float) $row['qty'], 4), '0'), '.');
            $sign = ($type === 'masuk') ? '<span class="text-success">+</span>' : '<span class="text-danger">−</span>';

            $data[] = [
                $start + $i + 1,
                esc($row['adjusted_at']),
                esc($row['item_name'] ?? '—'),
                esc($row['lab_name']  ?? '—'),
                $typeBadge[$type] ?? esc($type),
                $sign . ' ' . $qty . ' ' . $sym,
                esc($row['reason'] ?? '—'),
                esc($row['adjusted_by_name'] ?? '—'),
            ];
        }

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    // ------------------------------------------------------------------
    // Export riwayat penyesuaian → Excel (SpreadsheetML)
    // ------------------------------------------------------------------

    public function exportHistory()
    {
        if (! activeGroupCan('bhp.stock.adjust')) {
            return redirect()->to('/consumables/adjustments')->with('error', 'Akses ditolak.');
        }

        $req         = $this->request;
        $filterLab   = (int)    ($req->getGet('filter_lab')   ?? 0);
        $filterType  = (string) ($req->getGet('filter_type')  ?? '');
        $filterFrom  = (string) ($req->getGet('filter_from')  ?? '');
        $filterUntil = (string) ($req->getGet('filter_until') ?? '');

        $db = db_connect();

        $applyFilters = function ($builder) use ($filterLab, $filterType, $filterFrom, $filterUntil) {
            if ($filterLab > 0)     { $builder->where('ci.lab_id', $filterLab); }
            if ($filterType !== '')  { $builder->where('csa.adjustment_type', $filterType); }
            if ($filterFrom !== '')  { $builder->where('DATE(csa.adjusted_at) >=', $filterFrom); }
            if ($filterUntil !== '') { $builder->where('DATE(csa.adjusted_at) <=', $filterUntil); }
        };

        $qb = $db->table('consumable_stock_adjustments csa')
            ->select('csa.adjusted_at, csa.adjustment_type, csa.qty, csa.reason,
                      ci.name AS item_name,
                      units.symbol AS unit_symbol,
                      labs.name AS lab_name,
                      u.username AS adjusted_by_name')
            ->join('consumable_items ci',  'ci.id = csa.consumable_id', 'left')
            ->join('units',                'units.id = ci.unit_id',     'left')
            ->join('labs',                 'labs.id = ci.lab_id',       'left')
            ->join('users u',              'u.id = csa.adjusted_by',    'left');

        $applyFilters($qb);
        $rows = $qb->orderBy('csa.adjusted_at', 'DESC')->get()->getResultArray();

        $typeLabels = [
            'masuk'   => 'Bahan Masuk',
            'susut'   => 'Susut',
            'rusak'   => 'Rusak',
            'tumpah'  => 'Tumpah',
            'koreksi' => 'Koreksi',
        ];

        $headers = ['No', 'Tanggal', 'Nama Bahan', 'Lab', 'Tipe', 'Qty', 'Satuan', 'Alasan', 'Oleh'];

        $sheetXml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sheetXml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"';
        $sheetXml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $sheetXml .= '<Worksheet ss:Name="Riwayat Penyesuaian"><Table>' . "\n";

        // Header row
        $sheetXml .= '<Row>';
        foreach ($headers as $h) {
            $sheetXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($h, ENT_XML1, 'UTF-8') . '</Data></Cell>';
        }
        $sheetXml .= '</Row>' . "\n";

        // Data rows
        foreach ($rows as $no => $row) {
            $type    = $row['adjustment_type'] ?? '';
            $qty     = rtrim(rtrim(number_format((float) $row['qty'], 4), '0'), '.');
            $sign    = ($type === 'masuk') ? '+' : '-';
            $cells = [
                $no + 1,
                $row['adjusted_at']      ?? '',
                $row['item_name']        ?? '',
                $row['lab_name']         ?? '',
                $typeLabels[$type]       ?? $type,
                $sign . $qty,
                $row['unit_symbol']      ?? '',
                $row['reason']           ?? '',
                $row['adjusted_by_name'] ?? '',
            ];
            $sheetXml .= '<Row>';
            foreach ($cells as $cell) {
                $t = is_numeric($cell) ? 'Number' : 'String';
                $sheetXml .= '<Cell><Data ss:Type="' . $t . '">' . htmlspecialchars((string) $cell, ENT_XML1, 'UTF-8') . '</Data></Cell>';
            }
            $sheetXml .= '</Row>' . "\n";
        }

        $sheetXml .= '</Table></Worksheet></Workbook>';

        $filename = 'riwayat-penyesuaian-stok-' . date('Ymd-His');

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '.xls"')
            ->setHeader('Cache-Control', 'no-store, no-cache')
            ->setBody($sheetXml);
    }
}

