<?php
$currentTab  = $tab ?? 'active';
$labQuery    = ($filterLabId ?? 0) > 0 ? '&lab_id=' . (int) $filterLabId : '';
?>
<style>
/* Oprek Browse — Modern Minimal Cards */
.card-oprek {
  border: 1px solid #edf2f7;
  border-radius: 14px;
  box-shadow: 0 1px 2px rgba(0,0,0,.04);
  transition: box-shadow .2s ease, transform .15s ease, border-color .2s ease;
  background: #fff;
  position: relative;
  overflow: hidden;
}
.card-oprek::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  height: 3px;
  width: 100%;
  background: linear-gradient(90deg, #4c6fff, #22c55e);
  opacity: .85;
}
.card-oprek:hover {
  box-shadow: 0 10px 24px rgba(0,0,0,.08);
  transform: translateY(-2px);
  border-color: #e2e8f0;
}
.card-oprek .card-body {
  padding: 1.35rem;
}
.oprek-title {
  font-size: 1.05rem;
  font-weight: 600;
  line-height: 1.4;
  color: #1f2937;
  letter-spacing: .2px;
}
.badge-sm {
  font-size: .7rem;
  padding: .2em .6em;
}
.oprek-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  padding: 2px 8px;
  font-size: .72rem;
  color: #6b7280;
  background: #f9fafb;
}
.oprek-meta {
  display: grid;
  grid-template-columns: 1fr;
  gap: 8px;
  font-size: .82rem;
  color: #6b7280;
}
.oprek-meta-item {
  display: flex;
  align-items: center;
  gap: 8px;
}
.oprek-meta-item i {
  width: 16px;
  text-align: center;
  flex-shrink: 0;
}
.oprek-actions .btn {
  border-radius: 10px;
}
</style>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($page_title) ?></h4>
        <?php if (! empty($labs)): ?>
        <div class="card-header-action">
          <form method="get" class="form-inline">
            <input type="hidden" name="tab" value="<?= esc($currentTab) ?>">
            <select name="lab_id" class="form-control form-control-sm" onchange="this.form.submit()">
              <option value="">Semua Lab</option>
              <?php foreach ($labs as $lab): ?>
              <option value="<?= (int) $lab['id'] ?>" <?= ($filterLabId ?? 0) === (int) $lab['id'] ? 'selected' : '' ?>>
                <?= esc($lab['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <!-- Tabs: Aktif | Arsip -->
        <ul class="nav nav-tabs mb-4">
          <li class="nav-item">
            <a class="nav-link <?= $currentTab === 'active' ? 'active' : '' ?>"
               href="<?= base_url('oprek/browse?tab=active' . $labQuery) ?>">
              <i class="fas fa-rocket"></i> Aktif
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $currentTab === 'archive' ? 'active' : '' ?>"
               href="<?= base_url('oprek/browse?tab=archive' . $labQuery) ?>">
              <i class="fas fa-archive"></i> Arsip
            </a>
          </li>
        </ul>

        <?php if (empty($campaigns)): ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-calendar-times fa-3x mb-3"></i>
            <p>
              <?= $currentTab === 'archive'
                ? 'Tidak ada open rekrutmen yang sudah diarsipkan.'
                : 'Tidak ada open rekrutmen yang sedang aktif saat ini.' ?>
            </p>
          </div>
        <?php else: ?>
          <div class="row">
            <?php foreach ($campaigns as $c): ?>
            <div class="col-md-6 col-lg-4 mb-4">
              <div class="card card-oprek h-100">
                <div class="card-body d-flex flex-column">
                  <!-- Top: status + application -->
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge badge-pill badge-<?= $c->status === 'published' ? 'success' : 'secondary' ?> badge-sm">
                      <?= $c->status === 'published' ? 'Aktif' : esc($c->status) ?>
                    </span>
                    <?php if ($c->has_applied): ?>
                    <span class="badge badge-pill badge-<?= $c->application_status === 'accepted' || $c->application_status === 'onboarding_complete' ? 'success' : 'warning' ?> badge-sm">
                      <?= esc($c->application_status) ?>
                    </span>
                    <?php endif; ?>
                  </div>

                  <!-- Title + lab chip -->
                  <h5 class="oprek-title mb-2"><?= esc($c->period_name) ?></h5>
                  <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="oprek-chip"><i class="fas fa-flask"></i><?= esc($c->lab_name) ?></span>
                    <span class="oprek-chip"><i class="fas fa-graduation-cap"></i><?= esc($c->nama_ta) ?></span>
                  </div>

                  <!-- Meta info -->
                  <div class="oprek-meta mb-3">
                    <div class="oprek-meta-item">
                      <i class="fas fa-calendar-alt fa-fw text-muted"></i>
                      <span>
                        <?php if ($c->registration_start_at && $c->registration_end_at): ?>
                          <?= date('d/m/Y', strtotime($c->registration_start_at)) ?> — <?= date('d/m/Y', strtotime($c->registration_end_at)) ?>
                        <?php else: ?>
                          <span class="text-muted">Menunggu jadwal</span>
                        <?php endif; ?>
                      </span>
                    </div>
                    <?php if ($c->quota): ?>
                    <div class="oprek-meta-item">
                      <i class="fas fa-users fa-fw text-muted"></i>
                      <span>Kuota <?= esc($c->quota) ?> peserta</span>
                    </div>
                    <?php endif; ?>
                  </div>

                  <!-- Action -->
                  <div class="mt-auto oprek-actions">
                    <?php if ($c->has_applied): ?>
                      <a href="<?= base_url('oprek/my-applications/' . $c->application_public_id) ?>" class="btn btn-outline-primary btn-block btn-sm">
                        <i class="fas fa-eye"></i> Lihat Status
                      </a>
                    <?php elseif ($currentTab === 'archive'): ?>
                      <a href="<?= base_url('oprek/detail/' . $c->public_id) ?>" class="btn btn-outline-secondary btn-block btn-sm">
                        <i class="fas fa-info-circle"></i> Lihat Detail
                      </a>
                    <?php else: ?>
                      <a href="<?= base_url('oprek/detail/' . $c->public_id) ?>" class="btn btn-primary btn-block btn-sm">
                        <i class="fas fa-info-circle"></i> Lihat Detail
                      </a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
