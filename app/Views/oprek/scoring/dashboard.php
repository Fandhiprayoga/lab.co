<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<style>
.ranking-1 { color: #ffc107; font-weight: bold; }
.ranking-2 { color: #6c757d; font-weight: bold; }
.ranking-3 { color: #cd7f32; font-weight: bold; }
.scoring-shell { background: #f6f8fc; border: 1px solid #e6ebf3; box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08); }
.scoring-header { background: linear-gradient(120deg, #f8fafc, #eef4ff); border-bottom: 1px solid #e3e9f3; }
.scoring-header h4 { margin-bottom: 0.2rem; }
.scoring-header .subtitle { color: #6b7280; font-size: 0.85rem; }
.scoring-rank-card { background: #ffffff; border: 1px solid #e8edf6; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06); }
.scoring-table thead th { background: #f9fbff; border-bottom: 1px solid #e8edf6; }
</style>
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12">
    <div class="card scoring-shell">
      <div class="card-header scoring-header d-flex align-items-center justify-content-between">
        <div>
          <h4><?= esc($page_title) ?></h4>
          <div class="subtitle">Ringkasan penilaian dan aksi scoring kandidat.</div>
        </div>
        <div class="card-header-action">
          <?php if ($isLaboran): ?>
          <a href="<?= base_url('oprek/' . $campaign->id . '/finalize') ?>" class="btn btn-success btn-sm">
            <i class="fas fa-check-double"></i> Verifikasi Akhir
          </a>
          <?php endif; ?>
          <a href="<?= base_url('oprek/' . $campaign->id) ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <?php if (empty($scoringData)): ?>
          <p class="text-muted text-center py-4">Belum ada kandidat yang lolos verifikasi berkas.</p>
        <?php else: ?>
          <!-- Rankings -->
          <div class="card mb-3 scoring-rank-card">
            <div class="card-body">
              <h5>Ranking Kandidat</h5>
              <div class="table-responsive">
                <table class="table table-sm table-bordered">
                  <thead><tr><th>Rank</th><th>Application ID</th><th>Nilai Akhir</th></tr></thead>
                  <tbody>
                    <?php foreach ($rankings as $r => $rank): ?>
                      <?php if ($r >= 10) break; ?>
                    <tr>
                      <td class="ranking-<?= $r + 1 <= 3 ? $r + 1 : '' ?>">#<?= $r + 1 ?></td>
                      <td>#<?= $rank['application_id'] ?></td>
                      <td><?= $rank['final_score'] !== null ? number_format($rank['final_score'], 2) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Scoring Grid -->
          <div class="table-responsive">
            <table class="table table-bordered table-sm scoring-table" id="table-scoring">
              <thead class="thead-light">
                <tr>
                  <th>Pendaftar</th>
                  <th>Status</th>
                  <?php foreach ($components as $comp): ?>
                    <th><?= esc($comp->component_name) ?><br><small>Bobot: <?= $comp->weight_percentage ?>%</small></th>
                  <?php endforeach; ?>
                  <th>Nilai Akhir</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($scoringData as $sd): ?>
                <tr>
                  <td>
                    <strong><?= esc($sd['application']->username) ?></strong><br>
                    <small><?= esc($sd['application']->nim_nik ?? '-') ?></small>
                  </td>
                  <td><span class="badge badge-info"><?= esc($sd['application']->application_status) ?></span></td>
                  <?php foreach ($sd['componentScores'] as $cs): ?>
                    <td class="text-center">
                      <?php if ($cs['my_score']): ?>
                        <span class="badge badge-success"><?= $cs['my_score']->score_value ?></span>
                      <?php else: ?>
                        <span class="badge badge-secondary">-</span>
                      <?php endif; ?>
                      <?php if (! empty($cs['all_scores'])): ?>
                        <br><small class="text-muted">
                          <?php foreach ($cs['all_scores'] as $s): ?>
                            <?= $s->score_value ?>,
                          <?php endforeach; ?>
                        </small>
                      <?php endif; ?>
                    </td>
                  <?php endforeach; ?>
                  <td class="text-center font-weight-bold">
                    <?= $sd['finalScore'] !== null ? number_format($sd['finalScore'], 2) : '-' ?>
                  </td>
                  <td>
                    <a href="<?= base_url('oprek/scoring/' . $sd['application']->public_id) ?>" class="btn btn-sm btn-primary">
                      <i class="fas fa-star"></i> Nilai
                    </a>
                  </td>
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

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
$(function() {
  $('#table-scoring').DataTable({ pageLength: 25, language: { url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' } });
});
</script>
<?= $this->endSection() ?>
