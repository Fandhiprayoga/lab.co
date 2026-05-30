<?php
$topItems      = $topItems ?? [];
$trend         = $trend ?? [];
$statusSummary = $statusSummary ?? [];
$lowStockItems = $lowStockItems ?? [];

$statusLabels = [
    'draft'            => 'Draft',
    'waiting_approval' => 'Menunggu Persetujuan',
    'approved'         => 'Disetujui',
    'rejected'         => 'Ditolak',
    'disbursed'        => 'Dikeluarkan',
    'completed'        => 'Selesai',
    'canceled'         => 'Dibatalkan',
    'problematic'      => 'Bermasalah',
];
?>

<div class="row">
  <!-- Top items chart -->
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header"><h6 class="mb-0"><i class="fas fa-trophy mr-1 text-warning"></i> Top 10 Bahan Paling Banyak Digunakan</h6></div>
      <div class="card-body">
        <?php if (empty($topItems)): ?>
          <p class="text-muted text-center">Belum ada data realisasi.</p>
        <?php else: ?>
          <canvas id="topItemsChart" height="280"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Trend permintaan per bulan -->
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header"><h6 class="mb-0"><i class="fas fa-chart-line mr-1 text-primary"></i> Tren Permintaan (6 Bulan Terakhir)</h6></div>
      <div class="card-body">
        <?php if (empty($trend)): ?>
          <p class="text-muted text-center">Belum ada data.</p>
        <?php else: ?>
          <canvas id="trendChart" height="280"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Status summary -->
  <div class="col-md-6 mb-4">
    <div class="card h-100">
      <div class="card-header"><h6 class="mb-0"><i class="fas fa-chart-pie mr-1 text-info"></i> Ringkasan Status Permintaan</h6></div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <?php foreach ($statusSummary as $row): ?>
          <tr>
            <td><?= esc($statusLabels[$row['status']] ?? $row['status']) ?></td>
            <td class="text-right font-weight-bold"><?= (int)$row['total'] ?></td>
          </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>
  </div>

  <!-- Bahan stok rendah -->
  <div class="col-md-6 mb-4">
    <div class="card h-100 <?= count($lowStockItems) > 0 ? 'border-warning' : '' ?>">
      <div class="card-header <?= count($lowStockItems) > 0 ? 'bg-warning text-dark' : '' ?>">
        <h6 class="mb-0"><i class="fas fa-exclamation-triangle mr-1"></i> Bahan di Bawah Stok Minimum (<?= count($lowStockItems) ?>)</h6>
      </div>
      <div class="card-body p-0">
        <?php if (empty($lowStockItems)): ?>
          <p class="text-muted text-center py-3">Semua bahan dalam kondisi stok aman.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="thead-light"><tr><th>Bahan</th><th>Lab</th><th class="text-right">Tersedia</th><th class="text-right">Min</th></tr></thead>
              <tbody>
                <?php foreach ($lowStockItems as $it): ?>
                <tr>
                  <td><?= esc($it['name']) ?></td>
                  <td><?= esc($it['lab_name'] ?? '—') ?></td>
                  <td class="text-right text-danger font-weight-bold"><?= number_format((float)$it['stock_available'], 2) ?> <?= esc($it['unit_symbol'] ?? '') ?></td>
                  <td class="text-right"><?= number_format((float)$it['min_stock'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if (! empty($topItems)): ?>
<script>
new Chart(document.getElementById('topItemsChart').getContext('2d'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_column($topItems, 'item_name')) ?>,
    datasets: [{
      label: 'Total Terpakai',
      data: <?= json_encode(array_column($topItems, 'total_used')) ?>,
      backgroundColor: 'rgba(54,162,235,0.7)',
      borderColor: 'rgba(54,162,235,1)',
      borderWidth: 1,
    }]
  },
  options: {indexAxis: 'y', plugins: {legend: {display: false}}, scales: {x: {beginAtZero: true}}}
});
</script>
<?php endif; ?>

<?php if (! empty($trend)): ?>
<script>
new Chart(document.getElementById('trendChart').getContext('2d'), {
  type: 'line',
  data: {
    labels: <?= json_encode(array_column($trend, 'month')) ?>,
    datasets: [{
      label: 'Jumlah Permintaan',
      data: <?= json_encode(array_column($trend, 'total')) ?>,
      fill: true,
      tension: 0.4,
      backgroundColor: 'rgba(75,192,192,0.2)',
      borderColor: 'rgba(75,192,192,1)',
    }]
  },
  options: {scales: {y: {beginAtZero: true, ticks: {precision: 0}}}}
});
</script>
<?php endif; ?>
