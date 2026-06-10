<?= $this->section('css') ?>
<style>
.score-shell { background: #f5f7fb; border: 1px solid #e6e9f2; box-shadow: 0 10px 30px rgba(23, 32, 52, 0.08); }
.score-hero { background: linear-gradient(120deg, #f8fafc, #eef5ff); border: 1px solid #e3e9f3; }
.score-hero .badge { font-weight: 600; }
.score-chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; background: #ffffff; border: 1px solid #e2e8f0; font-size: 0.82rem; }
.score-component { border: 1px solid #e8edf6; box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06); }
.score-component .card-header { background: #f9fbff; border-bottom: 1px solid #e8edf6; }
.score-meta { color: #6b7280; font-size: 0.85rem; }
.score-input .form-control { font-weight: 600; }
.score-save { letter-spacing: 0.2px; }
</style>
<?= $this->endSection() ?>

<div class="row">
  <div class="col-lg-8 offset-lg-2">
    <div class="card score-shell">
      <div class="card-header d-flex align-items-center justify-content-between">
        <div>
          <h4 class="mb-1"><?= esc($page_title) ?></h4>
          <div class="text-muted small">Form penilaian kandidat dengan komponen seleksi aktif.</div>
        </div>
        <div class="card-header-action">
          <a href="<?= base_url('oprek/' . $campaign->id . '/scoring') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <?php $form = json_decode($application->form_payload ?? '{}'); ?>
        <div class="card score-hero mb-4">
          <div class="card-body">
            <div class="d-flex flex-wrap align-items-start justify-content-between">
              <div class="mb-3 mb-md-0">
                <h5 class="mb-1"><?= esc($form->full_name ?? '-') ?></h5>
                <div class="text-muted mb-2"><?= esc($application->username) ?> (<?= esc($application->nim_nik ?? '-') ?>)</div>
                <div class="d-flex flex-wrap">
                  <span class="score-chip mr-2 mb-2"><i class="fas fa-id-card"></i> <?= esc($form->nim ?? '-') ?></span>
                  <span class="score-chip mr-2 mb-2"><i class="fas fa-book"></i> <?= esc($form->prodi ?? '-') ?></span>
                  <span class="score-chip mb-2"><i class="fas fa-layer-group"></i> Semester <?= esc($form->semester ?? '-') ?></span>
                </div>
              </div>
              <div class="text-md-right">
                <div class="score-meta mb-1">Status kandidat</div>
                <span class="badge badge-info px-3 py-2"><?= esc($application->application_status) ?></span>
              </div>
            </div>
          </div>
        </div>

        <form method="post" action="<?= base_url('oprek/scoring/' . $application->public_id . '/store') ?>">
          <?= csrf_field() ?>

          <?php if (empty($components)): ?>
            <div class="alert alert-warning">Tidak ada komponen seleksi yang aktif untuk oprek ini.</div>
          <?php else: ?>
            <?php foreach ($components as $comp): ?>
            <div class="card mb-3 score-component">
              <div class="card-header">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                  <div>
                    <div class="h6 mb-1"><?= esc($comp->component_name) ?></div>
                    <div class="score-meta">Bobot: <?= $comp->weight_percentage ?>%</div>
                  </div>
                  <span class="score-chip">Maks: <?= $comp->max_score ?></span>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group score-input">
                      <label>Nilai <span class="text-danger">*</span></label>
                      <input type="number" name="scores[<?= $comp->id ?>]" class="form-control"
                        value="<?= isset($existingScores[$comp->id]) ? esc($existingScores[$comp->id]->score_value) : '' ?>"
                        step="0.01" min="0" max="<?= $comp->max_score ?>">
                      <small class="text-muted">0 - <?= $comp->max_score ?></small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Catatan</label>
                      <textarea name="notes[<?= $comp->id ?>]" class="form-control" rows="2"><?= isset($existingScores[$comp->id]) ? esc($existingScores[$comp->id]->note) : '' ?></textarea>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <button type="submit" class="btn btn-primary btn-lg btn-block score-save">
            <i class="fas fa-save"></i> Simpan Semua Nilai
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
