<?php
$statusStats = $statusStats ?? [];
$itemTypeStats = $itemTypeStats ?? [];
?>

<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><h4>Statistik Status Proposal</h4></div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Status</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <?php if (! empty($statusStats)): ?>
                <?php foreach ($statusStats as $row): ?>
                <tr>
                  <td><?= esc($row['status']) ?></td>
                  <td><?= (int) $row['total'] ?></td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="2" class="text-center">Belum ada data.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><h4>Komposisi Item Pengajuan</h4></div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Tipe Item</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <?php if (! empty($itemTypeStats)): ?>
                <?php foreach ($itemTypeStats as $row): ?>
                <tr>
                  <td><?= esc($row['item_type']) ?></td>
                  <td><?= (int) $row['total'] ?></td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="2" class="text-center">Belum ada data.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
