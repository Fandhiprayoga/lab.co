<?php
$statusStats   = $statusStats ?? [];
$itemTypeStats = $itemTypeStats ?? [];
$monthlyStats  = $monthlyStats ?? [];
$durationStats = $durationStats ?? [];
$topProposers  = $topProposers ?? [];
$filters       = $filters ?? ['from' => '', 'until' => '', 'loan_type' => '', 'status' => ''];

$statusLabelMap = [
    'draft'      => 'Draft',
    'waiting_l1' => 'Menunggu Laboran',
    'waiting_l2' => 'Menunggu Ka.Lab',
    'approved'   => 'Disetujui',
    'borrowed'   => 'Dipinjam',
    'late'       => 'Terlambat',
    'returned'   => 'Dikembalikan',
    'problematic'=> 'Bermasalah',
    'in_use'     => 'Sedang Digunakan',
    'completed'  => 'Selesai',
    'rejected'   => 'Ditolak',
    'canceled'   => 'Dibatalkan',
];

$statusColorMap = [
    'draft'      => '#6c757d',
    'waiting_l1' => '#ffc107',
    'waiting_l2' => '#17a2b8',
    'approved'   => '#28a745',
    'borrowed'   => '#007bff',
    'late'       => '#fd7e14',
    'returned'   => '#20c997',
    'problematic'=> '#dc3545',
    'in_use'     => '#6610f2',
    'completed'  => '#198754',
    'rejected'   => '#e83e8c',
    'canceled'   => '#343a40',
];

$statusMap = [];
foreach ($statusStats as $row) {
    $statusMap[(string) ($row['status'] ?? '')] = (int) ($row['total'] ?? 0);
}

$totalProposals = array_sum($statusMap);
$waitingCount   = ($statusMap['waiting_l1'] ?? 0) + ($statusMap['waiting_l2'] ?? 0);
$operationalCount = ($statusMap['approved'] ?? 0) + ($statusMap['borrowed'] ?? 0) + ($statusMap['late'] ?? 0) + ($statusMap['in_use'] ?? 0);
$closedCount      = ($statusMap['returned'] ?? 0) + ($statusMap['completed'] ?? 0) + ($statusMap['rejected'] ?? 0) + ($statusMap['canceled'] ?? 0) + ($statusMap['problematic'] ?? 0);

$statusChartLabels = [];
$statusChartData   = [];
$statusChartColors = [];
foreach ($statusMap as $status => $count) {
    if ($count < 1) {
        continue;
    }
    $statusChartLabels[] = $statusLabelMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
    $statusChartData[]   = $count;
    $statusChartColors[] = $statusColorMap[$status] ?? '#6c757d';
}

$itemMap = [];
foreach ($itemTypeStats as $row) {
    $itemMap[(string) ($row['item_type'] ?? '')] = (int) ($row['total'] ?? 0);
}

$itemChartLabels = ['Alat', 'Ruangan'];
$itemChartData = [
    $itemMap['equipment'] ?? 0,
    $itemMap['lab'] ?? 0,
];

$periodMap = [];
foreach ($monthlyStats as $row) {
    $periodMap[(string) ($row['period'] ?? '')] = (int) ($row['total'] ?? 0);
}

$trendLabels = [];
$trendData   = [];
for ($i = 5; $i >= 0; $i--) {
    $period = date('Y-m', strtotime("-{$i} months"));
    $trendLabels[] = date('M Y', strtotime($period . '-01'));
    $trendData[]   = $periodMap[$period] ?? 0;
}

$avgHoursMap = ['equipment' => 0, 'lab' => 0];
foreach ($durationStats as $row) {
    $loanType = (string) ($row['loan_type'] ?? '');
    if (isset($avgHoursMap[$loanType])) {
        $avgHoursMap[$loanType] = round((float) ($row['avg_hours'] ?? 0), 1);
    }
}
?>

<div class="card mb-3">
  <div class="card-header d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="fas fa-filter mr-1"></i>Filter Analitik</h4>
    <button type="button" id="btn-export-analytics" class="btn btn-success btn-sm">
      <i class="fas fa-file-csv mr-1"></i>Export Ringkas CSV
    </button>
  </div>
  <div class="card-body">
    <form method="get" action="<?= base_url('loans/analytics') ?>">
      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label for="filter-from">Dari Tanggal</label>
            <input type="date" id="filter-from" name="from" class="form-control" value="<?= esc((string) ($filters['from'] ?? '')) ?>">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="filter-until">Sampai Tanggal</label>
            <input type="date" id="filter-until" name="until" class="form-control" value="<?= esc((string) ($filters['until'] ?? '')) ?>">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="filter-loan-type">Tipe Peminjaman</label>
            <select id="filter-loan-type" name="loan_type" class="form-control">
              <option value="">Semua Tipe</option>
              <option value="equipment" <?= ($filters['loan_type'] ?? '') === 'equipment' ? 'selected' : '' ?>>Alat</option>
              <option value="lab" <?= ($filters['loan_type'] ?? '') === 'lab' ? 'selected' : '' ?>>Ruangan</option>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="filter-status">Status</label>
            <select id="filter-status" name="status" class="form-control">
              <option value="">Semua Status</option>
              <?php foreach ($statusLabelMap as $key => $label): ?>
              <option value="<?= esc($key) ?>" <?= ($filters['status'] ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="d-flex" style="gap:.5rem">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="fas fa-check mr-1"></i>Terapkan Filter
        </button>
        <a href="<?= base_url('loans/analytics') ?>" class="btn btn-light btn-sm">
          <i class="fas fa-undo mr-1"></i>Reset
        </a>
      </div>
    </form>
  </div>
</div>

<div class="row mb-3">
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary"><i class="fas fa-file-alt"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Total Proposal</h4></div>
        <div class="card-body"><?= (int) $totalProposals ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-warning"><i class="fas fa-hourglass-half"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Menunggu Approval</h4></div>
        <div class="card-body"><?= (int) $waitingCount ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-cogs"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Operasional Aktif</h4></div>
        <div class="card-body"><?= (int) $operationalCount ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-dark"><i class="fas fa-flag-checkered"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Selesai / Closed</h4></div>
        <div class="card-body"><?= (int) $closedCount ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header"><h4>Tren Proposal 6 Bulan Terakhir</h4></div>
      <div class="card-body">
        <canvas id="proposalTrendChart" height="110"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h4>Komposisi Item</h4></div>
      <div class="card-body">
        <canvas id="itemCompositionChart" height="220"></canvas>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><h4>Distribusi Status Proposal</h4></div>
      <div class="card-body">
        <canvas id="statusDistributionChart" height="170"></canvas>
      </div>
      <div class="table-responsive px-3 pb-3">
        <table class="table table-sm table-striped mb-0">
          <thead>
            <tr>
              <th>Status</th>
              <th class="text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <?php if (! empty($statusStats)): ?>
              <?php foreach ($statusStats as $row):
                $status = (string) ($row['status'] ?? '');
              ?>
              <tr>
                <td><?= esc($statusLabelMap[$status] ?? ucfirst(str_replace('_', ' ', $status))) ?></td>
                <td class="text-right"><?= (int) ($row['total'] ?? 0) ?></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="2" class="text-center text-muted">Belum ada data.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><h4>Rata-rata Durasi Pemakaian</h4></div>
      <div class="card-body">
        <div class="row">
          <div class="col-6">
            <div class="border rounded p-3 text-center h-100">
              <div class="text-muted small mb-1">Alat</div>
              <div class="h4 mb-0 text-primary"><?= esc(number_format((float) $avgHoursMap['equipment'], 1)) ?> jam</div>
            </div>
          </div>
          <div class="col-6">
            <div class="border rounded p-3 text-center h-100">
              <div class="text-muted small mb-1">Ruangan</div>
              <div class="h4 mb-0 text-success"><?= esc(number_format((float) $avgHoursMap['lab'], 1)) ?> jam</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h4>Top 5 Pengusul</h4></div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-striped mb-0">
            <thead>
              <tr>
                <th class="pl-3">Pengusul</th>
                <th class="text-right pr-3">Jumlah Proposal</th>
              </tr>
            </thead>
            <tbody>
              <?php if (! empty($topProposers)): ?>
                <?php foreach ($topProposers as $row): ?>
                <tr>
                  <td class="pl-3"><?= esc($row['proposer_name'] ?? '-') ?></td>
                  <td class="text-right pr-3"><?= (int) ($row['total'] ?? 0) ?></td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="2" class="text-center text-muted py-3">Belum ada data.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var trendCtx = document.getElementById('proposalTrendChart');
  var itemCtx = document.getElementById('itemCompositionChart');
  var statusCtx = document.getElementById('statusDistributionChart');

  if (trendCtx) {
    new Chart(trendCtx, {
      type: 'line',
      data: {
        labels: <?= json_encode($trendLabels) ?>,
        datasets: [{
          label: 'Proposal',
          data: <?= json_encode($trendData) ?>,
          borderColor: '#6777ef',
          backgroundColor: 'rgba(103, 119, 239, 0.15)',
          tension: 0.35,
          fill: true,
          pointRadius: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        plugins: { legend: { display: false } }
      }
    });
  }

  if (itemCtx) {
    new Chart(itemCtx, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($itemChartLabels) ?>,
        datasets: [{
          data: <?= json_encode($itemChartData) ?>,
          backgroundColor: ['#0288d1', '#388e3c']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
      }
    });
  }

  if (statusCtx) {
    new Chart(statusCtx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($statusChartLabels) ?>,
        datasets: [{
          data: <?= json_encode($statusChartData) ?>,
          backgroundColor: <?= json_encode($statusChartColors) ?>,
          borderRadius: 6,
          maxBarThickness: 28
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
        plugins: { legend: { display: false } }
      }
    });
  }

  var exportBtn = document.getElementById('btn-export-analytics');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      var rows = [];
      rows.push(['Section', 'Label', 'Value']);

      rows.push(['Summary', 'Total Proposal', '<?= (int) $totalProposals ?>']);
      rows.push(['Summary', 'Menunggu Approval', '<?= (int) $waitingCount ?>']);
      rows.push(['Summary', 'Operasional Aktif', '<?= (int) $operationalCount ?>']);
      rows.push(['Summary', 'Selesai/Closed', '<?= (int) $closedCount ?>']);

      <?php foreach ($statusStats as $row):
        $status = (string) ($row['status'] ?? '');
        $label = $statusLabelMap[$status] ?? ucfirst(str_replace('_', ' ', $status));
      ?>
      rows.push(['Status', '<?= esc($label, 'js') ?>', '<?= (int) ($row['total'] ?? 0) ?>']);
      <?php endforeach; ?>

      <?php foreach ($itemChartLabels as $index => $label): ?>
      rows.push(['Item', '<?= esc((string) $label, 'js') ?>', '<?= (int) ($itemChartData[$index] ?? 0) ?>']);
      <?php endforeach; ?>

      rows.push(['Durasi', 'Rata-rata Alat (jam)', '<?= esc((string) number_format((float) $avgHoursMap['equipment'], 1), 'js') ?>']);
      rows.push(['Durasi', 'Rata-rata Ruangan (jam)', '<?= esc((string) number_format((float) $avgHoursMap['lab'], 1), 'js') ?>']);

      <?php foreach ($topProposers as $row): ?>
      rows.push(['Top Pengusul', '<?= esc((string) ($row['proposer_name'] ?? '-'), 'js') ?>', '<?= (int) ($row['total'] ?? 0) ?>']);
      <?php endforeach; ?>

      var csv = rows.map(function (row) {
        return row.map(function (cell) {
          var value = String(cell).replace(/"/g, '""');
          return '"' + value + '"';
        }).join(',');
      }).join('\n');

      var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      var url = URL.createObjectURL(blob);
      var link = document.createElement('a');
      link.href = url;
      link.download = 'analytics-peminjaman-' + new Date().toISOString().slice(0, 10) + '.csv';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    });
  }
});
</script>
