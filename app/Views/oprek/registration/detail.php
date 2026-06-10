<?php
$isOpen = $isRegistrationOpen ?? false;
?>
<style>
.oprek-hero {
  border: 1px solid #edf2f7;
  border-radius: 16px;
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  box-shadow: 0 4px 18px rgba(0,0,0,.05);
}
.oprek-hero .badge-lg {
  font-size: .78rem;
  border-radius: 999px;
  padding: .35rem .75rem;
}
.oprek-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  padding: 4px 10px;
  font-size: .78rem;
  color: #6b7280;
  background: #f9fafb;
}
.oprek-stat {
  border-radius: 14px;
  border: 1px solid #edf2f7;
  background: #fff;
  padding: 16px 16px 14px;
  box-shadow: 0 1px 2px rgba(0,0,0,.04);
  transition: box-shadow .2s ease, transform .15s ease;
}
.oprek-stat:hover {
  box-shadow: 0 10px 24px rgba(0,0,0,.06);
  transform: translateY(-1px);
}
.oprek-stat .oprek-stat-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
  color: #475569;
}
.oprek-stat .oprek-stat-label {
  font-size: .78rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: .08em;
}
.oprek-stat .oprek-stat-value {
  font-size: 1.05rem;
  font-weight: 600;
  color: #0f172a;
}
.badge-sm {
  font-size: .7rem;
  padding: .15em .55em;
}
.oprek-section {
  border: 1px solid #edf2f7;
  border-radius: 12px;
  overflow: hidden;
}
.oprek-section .card-header {
  background: #f8fafc;
  border-bottom: 1px solid #edf2f7;
}
.oprek-cta {
  border: 1px solid #edf2f7;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 6px 20px rgba(0,0,0,.06);
}
.oprek-cta .oprek-cta-icon {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
  color: #475569;
  margin: 0 auto 10px;
  font-size: 1.25rem;
}
.oprek-cta .btn {
  border-radius: 12px;
}
.oprek-cta hr {
  border-top: 1px solid #edf2f7;
}
.oprek-poster {
  border: 1px solid #edf2f7;
  border-radius: 14px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 2px 10px rgba(0,0,0,.04);
}
.oprek-poster img {
  width: 100%;
  display: block;
  object-fit: cover;
}
</style>
<div class="row">
  <div class="col-lg-8">

    <!-- Header -->
    <div class="card oprek-hero">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
          <div>
            <h4 class="mb-2"><?= esc($campaign->period_name) ?></h4>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="oprek-chip"><i class="fas fa-flask"></i><?= esc($campaign->lab_name) ?></span>
              <span class="oprek-chip"><i class="fas fa-graduation-cap"></i><?= esc($campaign->nama_ta) ?></span>
            </div>
          </div>
          <span class="badge badge-lg badge-<?= $isOpen ? 'success' : 'secondary' ?> px-3 py-2">
            <?= $isOpen ? 'Pendaftaran Dibuka' : 'Pendaftaran Ditutup' ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Info Grid -->
    <div class="row">
      <div class="col-sm-4 mb-3">
        <div class="oprek-stat h-100">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="oprek-stat-label mb-1">Periode</div>
              <div class="oprek-stat-value">
                <?php if ($campaign->registration_start_at || $campaign->registration_end_at): ?>
                  <?= $campaign->registration_start_at ? date('d M Y', strtotime($campaign->registration_start_at)) : '?' ?>
                  <span class="text-muted">—</span>
                  <?= $campaign->registration_end_at ? date('d M Y', strtotime($campaign->registration_end_at)) : '?' ?>
                <?php else: ?>
                  <span class="text-muted">Belum ditentukan</span>
                <?php endif; ?>
              </div>
            </div>
            <span class="oprek-stat-icon"><i class="fas fa-calendar-alt"></i></span>
          </div>
        </div>
      </div>
      <div class="col-sm-4 mb-3">
        <div class="oprek-stat h-100">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="oprek-stat-label mb-1">Kuota</div>
              <div class="oprek-stat-value">
                <?= esc($campaign->quota ?? '—') ?>
                <span class="text-muted" style="font-size:.8rem; font-weight:400;">peserta</span>
              </div>
            </div>
            <span class="oprek-stat-icon"><i class="fas fa-users"></i></span>
          </div>
        </div>
      </div>
      <div class="col-sm-4 mb-3">
        <div class="oprek-stat h-100">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="oprek-stat-label mb-1">Komponen</div>
              <div class="oprek-stat-value">
                <?= count($components) ?>
                <span class="text-muted" style="font-size:.8rem; font-weight:400;">tahapan</span>
              </div>
            </div>
            <span class="oprek-stat-icon"><i class="fas fa-clipboard-check"></i></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Poster -->
    <?php if (! empty($campaign->poster)): ?>
    <div class="card oprek-poster mb-3">
      <img src="<?= base_url($campaign->poster) ?>" alt="Poster <?= esc($campaign->period_name) ?>">
    </div>
    <?php endif; ?>

    <!-- Deskripsi -->
    <?php if (! empty($campaign->description)): ?>
    <div class="card oprek-section">
      <div class="card-header"><h6 class="mb-0"><i class="fas fa-align-left"></i> Deskripsi</h6></div>
      <div class="card-body">
        <div class="text-muted"><?= nl2br(esc($campaign->description)) ?></div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Persyaratan -->
    <?php if (! empty($campaign->requirements)): ?>
    <div class="card oprek-section">
      <div class="card-header"><h6 class="mb-0"><i class="fas fa-tasks"></i> Persyaratan</h6></div>
      <div class="card-body">
        <div class="text-muted"><?= nl2br(esc($campaign->requirements)) ?></div>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <!-- Sidebar: CTA -->
  <div class="col-lg-4">
    <div class="card oprek-cta sticky-top" style="top: 80px;">
      <div class="card-body text-center">
        <?php if ($application): ?>
          <div class="mb-3">
            <div class="oprek-cta-icon">
              <i class="fas fa-check"></i>
            </div>
            <h6 class="mb-1">Sudah Mendaftar</h6>
            <span class="badge badge-<?= $application->application_status === 'accepted' || $application->application_status === 'onboarding_complete' ? 'success' : 'warning' ?>">
              <?= esc($application->application_status) ?>
            </span>
            <p class="text-muted small mt-2 mb-0">
              Didaftarkan <?= date('d M Y', strtotime($application->submitted_at)) ?>
            </p>
          </div>
          <a href="<?= base_url('oprek/my-applications/' . $application->public_id) ?>" class="btn btn-outline-primary btn-block">
            <i class="fas fa-eye"></i> Lihat Status Pendaftaran
          </a>
        <?php elseif ($isOpen): ?>
          <div class="mb-3">
            <div class="oprek-cta-icon">
              <i class="fas fa-rocket"></i>
            </div>
            <h6 class="mb-1">Pendaftaran Dibuka</h6>
            <p class="text-muted small">Siap bergabung menjadi asisten lab?</p>
          </div>
          <a href="<?= base_url('oprek/register/' . $campaign->public_id) ?>" class="btn btn-primary btn-block btn-lg">
            <i class="fas fa-pen"></i> Daftar Sekarang
          </a>
        <?php else: ?>
          <div class="mb-3">
            <div class="oprek-cta-icon">
              <i class="fas fa-lock"></i>
            </div>
            <h6 class="mb-1">Pendaftaran Ditutup</h6>
            <p class="text-muted small">Periode pendaftaran telah berakhir.</p>
          </div>
        <?php endif; ?>

        <hr>
        <a href="<?= base_url('oprek/browse') ?>" class="btn btn-light btn-block btn-sm">
          <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
      </div>
    </div>
  </div>
</div>
