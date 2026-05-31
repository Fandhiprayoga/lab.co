<?= $this->section('css') ?>
<style>
/* ── Notification Page ─────────────────────────────────────────────── */
.np-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 18px;
}
.np-header-left {
  display: flex;
  align-items: center;
  gap: 10px;
}
.np-header-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}
.np-unread-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 20px;
  padding: 0 7px;
  background: #6366f1;
  color: #fff;
  font-size: 0.7rem;
  font-weight: 700;
  border-radius: 10px;
}
.np-header-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.np-tabs {
  display: flex;
  background: #f3f4f6;
  border-radius: 8px;
  padding: 3px;
  gap: 2px;
}
.np-tab {
  padding: 5px 14px;
  font-size: 0.77rem;
  font-weight: 500;
  border-radius: 6px;
  cursor: pointer;
  border: none;
  background: transparent;
  color: #6b7280;
  transition: all 0.15s;
  line-height: 1.4;
}
.np-tab.active {
  background: #fff;
  color: #1f2937;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
}
.np-mark-all-btn {
  padding: 5px 12px;
  font-size: 0.76rem;
  font-weight: 500;
  border: none;
  border-radius: 7px;
  background: #f3f4f6;
  color: #374151;
  cursor: pointer;
  transition: background 0.15s;
  white-space: nowrap;
}
.np-mark-all-btn:hover { background: #e9eaed; }
.np-card {
  background: #fff;
  border: 1px solid #e8eaed;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}
/* Skeleton */
.np-skeleton-row {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 15px 20px;
  border-bottom: 1px solid #f3f4f6;
}
.np-skeleton-row:last-child { border-bottom: none; }
.sk-box {
  background: linear-gradient(90deg, #f3f4f6 25%, #e9eaed 50%, #f3f4f6 75%);
  background-size: 200% 100%;
  animation: sk-shimmer 1.5s infinite;
  border-radius: 6px;
  flex-shrink: 0;
}
@keyframes sk-shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
/* Row */
.np-row {
  display: flex;
  align-items: flex-start;
  padding: 14px 20px;
  border-bottom: 1px solid #f3f4f6;
  cursor: pointer;
  transition: background 0.12s;
  position: relative;
}
.np-row:last-child { border-bottom: none; }
.np-row:hover { background: #f9fafb; }
.np-row.np-unread { background: #fafaff; }
.np-row.np-unread::before {
  content: '';
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 3px;
  background: #6366f1;
  border-radius: 0 2px 2px 0;
}
.np-row.np-unread:hover { background: #f0f3ff; }
.np-row-icon {
  flex-shrink: 0;
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 14px;
  margin-top: 1px;
  font-size: 14px;
}
.np-row-body { flex: 1; min-width: 0; }
.np-row-title {
  font-size: 0.83rem;
  font-weight: 500;
  color: #1f2937;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin-bottom: 3px;
}
.np-unread .np-row-title { font-weight: 600; }
.np-row-msg {
  font-size: 0.76rem;
  color: #6b7280;
  line-height: 1.45;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  margin-bottom: 5px;
}
.np-row-time { font-size: 0.69rem; color: #b0b7c3; }
.np-row-side {
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
  margin-left: 10px;
}
.np-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #6366f1;
  margin-top: 2px;
}
.np-del-btn {
  background: none;
  border: none;
  color: #d1d5db;
  cursor: pointer;
  padding: 3px 5px;
  border-radius: 5px;
  font-size: 11px;
  line-height: 1;
  transition: color 0.12s, background 0.12s;
  opacity: 0;
}
.np-row:hover .np-del-btn { opacity: 1; }
.np-del-btn:hover { color: #ef4444; background: #fee2e2; }
/* Empty / error */
.np-state {
  padding: 60px 20px;
  text-align: center;
  color: #c4cad4;
}
.np-state-icon { font-size: 2.2rem; margin-bottom: 10px; opacity: 0.5; }
.np-state-text { font-size: 0.83rem; }
/* Pagination */
.np-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 11px 20px;
  border-top: 1px solid #f0f2f5;
  flex-wrap: wrap;
  gap: 8px;
}
.np-page-info { font-size: 0.73rem; color: #9ca3af; }
.np-page-btns { display: flex; gap: 4px; }
.np-page-btn {
  min-width: 32px;
  height: 32px;
  padding: 0 8px;
  border: 1px solid #e5e7eb;
  border-radius: 7px;
  background: #fff;
  color: #374151;
  font-size: 0.77rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.12s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.np-page-btn:hover:not([disabled]) { background: #f3f4f6; border-color: #d1d5db; }
.np-page-btn.active { background: #6366f1; border-color: #6366f1; color: #fff; }
.np-page-btn[disabled] { opacity: 0.35; cursor: not-allowed; }
.np-page-sep {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  height: 32px;
  color: #9ca3af;
  font-size: 0.77rem;
}
</style>
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12 col-lg-8 offset-lg-2">

    <!-- Header -->
    <div class="np-header">
      <div class="np-header-left">
        <h5 class="np-header-title">Notifikasi</h5>
        <span class="np-unread-pill" id="np-unread-pill">–</span>
      </div>
      <div class="np-header-right">
        <div class="np-tabs">
          <button class="np-tab active" data-filter="all">Semua</button>
          <button class="np-tab" data-filter="unread">Belum Dibaca</button>
        </div>
        <button class="np-mark-all-btn" id="np-mark-all">
          <i class="fas fa-check-double mr-1"></i> Tandai Semua Dibaca
        </button>
      </div>
    </div>

    <!-- Card -->
    <div class="np-card">
      <div id="np-list">
        <!-- Initial skeleton -->
        <?php
        $skW = [[48,80],[55,88],[60,75],[50,92],[65,70],[52,85]];
        foreach ($skW as $w):
        ?>
        <div class="np-skeleton-row">
          <div class="sk-box" style="width:38px;height:38px;border-radius:10px;"></div>
          <div style="flex:1;">
            <div class="sk-box" style="width:<?= $w[0] ?>%;height:12px;margin-bottom:8px;"></div>
            <div class="sk-box" style="width:<?= $w[1] ?>%;height:10px;margin-bottom:6px;"></div>
            <div class="sk-box" style="width:28%;height:9px;"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div id="np-pager"></div>
    </div>

  </div>
</div>

<?= $this->section('js') ?>
<script>
(function () {
  'use strict';

  var baseUrl   = '<?= base_url() ?>';
  var csrfToken = '<?= csrf_hash() ?>';

  var npList    = document.getElementById('np-list');
  var npPager   = document.getElementById('np-pager');
  var npPill    = document.getElementById('np-unread-pill');
  var npMarkAll = document.getElementById('np-mark-all');
  var tabs      = document.querySelectorAll('.np-tab');

  var state = { page: 1, filter: 'all', loading: false };

  /* ── Helpers ───────────────────────────────────────────────────────── */
  function esc(str) {
    return String(str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function timeAgo(d) {
    if (!d) return '';
    var s = Math.floor((Date.now() - new Date(d).getTime()) / 1000);
    if (s < 60)    return s + 'd lalu';
    if (s < 3600)  return Math.floor(s / 60) + 'm lalu';
    if (s < 86400) return Math.floor(s / 3600) + 'j lalu';
    return Math.floor(s / 86400) + ' hari lalu';
  }

  var palette = {
    primary:   'background:#ede9fe;color:#7c3aed',
    success:   'background:#d1fae5;color:#059669',
    warning:   'background:#fef3c7;color:#d97706',
    danger:    'background:#fee2e2;color:#dc2626',
    info:      'background:#dbeafe;color:#2563eb',
    secondary: 'background:#f3f4f6;color:#6b7280'
  };
  function iconStyle(c) { return palette[c] || palette.secondary; }

  /* ── Skeleton ──────────────────────────────────────────────────────── */
  function showSkeleton() {
    var rows = [[48,80],[55,88],[60,75],[50,92],[65,70],[52,85]];
    var html = '';
    rows.forEach(function (w) {
      html += '<div class="np-skeleton-row">';
      html += '<div class="sk-box" style="width:38px;height:38px;border-radius:10px;"></div>';
      html += '<div style="flex:1;">';
      html += '<div class="sk-box" style="width:' + w[0] + '%;height:12px;margin-bottom:8px;"></div>';
      html += '<div class="sk-box" style="width:' + w[1] + '%;height:10px;margin-bottom:6px;"></div>';
      html += '<div class="sk-box" style="width:28%;height:9px;"></div>';
      html += '</div></div>';
    });
    npList.innerHTML  = html;
    npPager.innerHTML = '';
  }

  /* ── Render ────────────────────────────────────────────────────────── */
  function renderItems(data) {
    if (!data.items || data.items.length === 0) {
      var msg = state.filter === 'unread'
        ? 'Tidak ada notifikasi yang belum dibaca'
        : 'Belum ada notifikasi';
      npList.innerHTML  = '<div class="np-state"><div class="np-state-icon"><i class="far fa-bell"></i></div><div class="np-state-text">' + msg + '</div></div>';
      npPager.innerHTML = '';
      return;
    }

    var html = '';
    data.items.forEach(function (n) {
      var unread = !n.is_read;
      var url    = n.url || (baseUrl + 'notifications');
      html += '<div class="np-row' + (unread ? ' np-unread' : '') + '" data-id="' + esc(n.id) + '" data-read="' + (n.is_read ? '1' : '0') + '" data-url="' + esc(url) + '">';
      html += '  <div class="np-row-icon" style="' + iconStyle(n.color) + '"><i class="' + esc(n.icon) + '"></i></div>';
      html += '  <div class="np-row-body">';
      html += '    <div class="np-row-title">' + esc(n.title) + '</div>';
      html += '    <div class="np-row-msg">'   + esc(n.message) + '</div>';
      html += '    <div class="np-row-time">'  + timeAgo(n.created_at) + '</div>';
      html += '  </div>';
      html += '  <div class="np-row-side">';
      if (unread) html += '<span class="np-dot"></span>';
      html += '    <button class="np-del-btn" data-id="' + esc(n.id) + '" title="Hapus"><i class="fas fa-times"></i></button>';
      html += '  </div></div>';
    });
    npList.innerHTML = html;

    renderPager(data);
    refreshPill();
  }

  /* ── Pagination ────────────────────────────────────────────────────── */
  function renderPager(data) {
    if (data.total_pages <= 1) { npPager.innerHTML = ''; return; }

    var from = (data.page - 1) * data.per_page + 1;
    var to   = Math.min(data.page * data.per_page, data.total);
    var tp   = data.total_pages;
    var cur  = data.page;

    var html = '<div class="np-pagination">';
    html += '<span class="np-page-info">Menampilkan ' + from + '–' + to + ' dari ' + data.total + '</span>';
    html += '<div class="np-page-btns">';
    html += mkBtn(cur - 1, '<i class="fas fa-chevron-left" style="font-size:10px;"></i>', cur <= 1);

    var start = Math.max(1, cur - 2);
    var end   = Math.min(tp, cur + 2);
    if (start > 1) {
      html += mkBtn(1, '1');
      if (start > 2) html += '<span class="np-page-sep">&hellip;</span>';
    }
    for (var p = start; p <= end; p++) {
      html += mkBtn(p, p, false, p === cur);
    }
    if (end < tp) {
      if (end < tp - 1) html += '<span class="np-page-sep">&hellip;</span>';
      html += mkBtn(tp, tp);
    }
    html += mkBtn(cur + 1, '<i class="fas fa-chevron-right" style="font-size:10px;"></i>', cur >= tp);
    html += '</div></div>';
    npPager.innerHTML = html;
  }

  function mkBtn(page, label, disabled, active) {
    return '<button class="np-page-btn' + (active ? ' active' : '') + '" data-page="' + page + '"' + (disabled ? ' disabled' : '') + '>' + label + '</button>';
  }

  /* ── Fetch ─────────────────────────────────────────────────────────── */
  function load(page, filter) {
    if (state.loading) return;
    state.loading = true;
    state.page    = page;
    state.filter  = filter;
    showSkeleton();

    fetch(baseUrl + 'notifications/list?page=' + page + '&filter=' + filter, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (data) { state.loading = false; renderItems(data); })
      .catch(function () {
        state.loading = false;
        npList.innerHTML  = '<div class="np-state"><div class="np-state-icon"><i class="fas fa-exclamation-circle"></i></div><div class="np-state-text">Gagal memuat notifikasi</div></div>';
        npPager.innerHTML = '';
      });
  }

  function refreshPill() {
    fetch(baseUrl + 'notifications/unread-count', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        var n = parseInt(d.count, 10) || 0;
        if (npPill) npPill.textContent = n;
        var nb = document.getElementById('notif-badge');
        if (nb) { nb.textContent = n > 99 ? '99+' : (n || ''); nb.classList.toggle('d-none', n === 0); }
        var sb = document.getElementById('sidebar-notif-badge');
        if (sb) { sb.textContent = n > 99 ? '99+' : (n || ''); sb.classList.toggle('d-none', n === 0); }
      })
      .catch(function () {});
  }

  /* ── Events ────────────────────────────────────────────────────────── */
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(function (t) { t.classList.remove('active'); });
      tab.classList.add('active');
      load(1, tab.dataset.filter);
    });
  });

  npPager.addEventListener('click', function (e) {
    var b = e.target.closest('.np-page-btn[data-page]');
    if (!b || b.hasAttribute('disabled')) return;
    var p = parseInt(b.dataset.page, 10);
    if (p > 0) { load(p, state.filter); window.scrollTo({ top: 0, behavior: 'smooth' }); }
  });

  npList.addEventListener('click', function (e) {
    var del = e.target.closest('.np-del-btn');
    if (del) {
      e.preventDefault(); e.stopPropagation();
      fetch(baseUrl + 'notifications/' + del.dataset.id, {
        method: 'DELETE',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      }).then(function () { load(state.page, state.filter); }).catch(function () {});
      return;
    }

    var row = e.target.closest('.np-row');
    if (!row) return;

    if (row.dataset.read === '0') {
      fetch(baseUrl + 'notifications/' + row.dataset.id + '/read', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      }).catch(function () {});
    }

    var url = row.dataset.url;
    if (url && url !== baseUrl + 'notifications') window.location.href = url;
  });

  if (npMarkAll) {
    npMarkAll.addEventListener('click', function () {
      fetch(baseUrl + 'notifications/read-all', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
      }).then(function () { load(state.page, state.filter); }).catch(function () {});
    });
  }

  /* ── Init ──────────────────────────────────────────────────────────── */
  load(1, 'all');
  refreshPill();
})();
</script>
<?= $this->endSection() ?>
