<?php
$currentUser  = auth()->user();
$currentUrl   = current_url();
$userGroups   = $currentUser->getGroups();
$active       = activeGroup();
$authGroups   = config('AuthGroups');

// Badge color per group
$badgeColors = [
  'superadmin' => 'danger',
  'laboran'    => 'warning',
  'asisten'    => 'info',
  'kepala_lab' => 'success',
  'dosen'      => 'primary',
  'mahasiswa'  => 'secondary',
  'alumni'     => 'dark',
];

// Role pill styles (bg / text color)
$roleStyle = [
  'superadmin' => 'background:#fee2e2;color:#dc2626',
  'laboran'    => 'background:#fef3c7;color:#b45309',
  'asisten'    => 'background:#dbeafe;color:#1d4ed8',
  'kepala_lab' => 'background:#d1fae5;color:#059669',
  'dosen'      => 'background:#ede9fe;color:#6d28d9',
  'mahasiswa'  => 'background:#f1f5f9;color:#475569',
  'alumni'     => 'background:#e0e7ff;color:#4338ca',
];
$roleCheckBg = [
  'superadmin' => '#dc2626',
  'laboran'    => '#b45309',
  'asisten'    => '#1d4ed8',
  'kepala_lab' => '#059669',
  'dosen'      => '#6d28d9',
  'mahasiswa'  => '#475569',
  'alumni'     => '#4338ca',
];

// Ambil lab assignment user login
$userLabs = [];
if ($currentUser) {
  $labAssignmentModel = new \App\Models\UserLabAssignmentModel();
  $userLabs = $labAssignmentModel->getLabsByUser((int) $currentUser->id);
}
$activeLabId = session()->get('active_lab_id');

// Auto-pilih & simpan jika hanya 1 lab dan belum ada pilihan
if (empty($activeLabId) && count($userLabs) === 1) {
  $activeLabId = (int) $userLabs[0]->id;
  session()->set('active_lab_id', $activeLabId);
}
?>
<style>
  /* ── Navbar User Dropdown ── */
  .ud-menu {
    min-width: 240px;
    padding: 0;
    border: none;
    border-radius: 14px;
    box-shadow: 0 8px 32px rgba(15, 23, 42, .14);
    overflow: hidden;
  }

  .ud-header {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 14px 16px 12px;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
  }

  .ud-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 2px solid #e2e8f0;
  }

  .ud-name {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 160px;
  }

  .ud-email {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 160px;
  }

  .ud-role-section {
    padding: 10px 16px 10px;
    border-bottom: 1px solid #f1f5f9;
  }

  .ud-role-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #cbd5e1;
    margin-bottom: 6px;
  }

  .ud-role-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px 3px 7px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 600;
    line-height: 1.7;
  }

  .ud-role-pill .rp-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    opacity: .7;
    flex-shrink: 0;
  }

  .ud-role-switch-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #cbd5e1;
    padding: 2px 0 4px;
  }

  .ud-rs-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 5px 8px;
    margin-top: 2px;
    font-size: 12px;
    font-weight: 500;
    color: #475569;
    background: none;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    text-align: left;
    cursor: pointer;
    transition: background .12s, border-color .12s;
  }

  .ud-rs-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
  }

  .ud-rs-ring {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    border: 2px solid #cbd5e1;
    flex-shrink: 0;
    transition: border-color .12s;
  }

  .ud-rs-btn:hover .ud-rs-ring {
    border-color: #94a3b8;
  }

  .ud-menu-actions {
    padding: 6px 0;
  }

  .ud-menu-actions .dropdown-item {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 8px 16px;
    font-size: 13px;
    color: #334155;
    transition: background .12s;
  }

  .ud-menu-actions .dropdown-item:hover {
    background: #f8fafc;
    color: #0f172a;
  }

  .ud-menu-actions .dropdown-item i {
    width: 16px;
    text-align: center;
    color: #94a3b8;
    font-size: 13px;
  }

  .ud-menu-actions .dropdown-item.text-danger {
    color: #ef4444 !important;
  }

  .ud-menu-actions .dropdown-item.text-danger i {
    color: #ef4444;
  }

  .ud-menu-actions .dropdown-divider {
    margin: 4px 16px;
    border-color: #f1f5f9;
  }

  /* ── Group Switcher Nav Item ── */
  .rs-menu {
    min-width: 210px;
    padding: 0;
    border: none;
    border-radius: 14px;
    box-shadow: 0 8px 32px rgba(15, 23, 42, .14);
    overflow: hidden;
  }

  .rs-menu-head {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #94a3b8;
    padding: 12px 16px 6px;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
  }

  .rs-active-row {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    background: #f1f5f9;
    pointer-events: none;
  }

  .rs-check-circle {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    color: #fff;
    flex-shrink: 0;
  }

  .rs-switch-form .rs-switch-btn {
    display: flex;
    align-items: center;
    gap: 9px;
    width: 100%;
    padding: 9px 16px;
    font-size: 13px;
    color: #64748b;
    background: none;
    border: none;
    text-align: left;
    cursor: pointer;
    transition: background .12s, color .12s;
  }

  .rs-switch-form .rs-switch-btn:hover {
    background: #f8fafc;
    color: #0f172a;
  }

  .rs-ring {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    flex-shrink: 0;
    transition: border-color .12s;
  }

  .rs-switch-form .rs-switch-btn:hover .rs-ring {
    border-color: #94a3b8;
  }

  /* ── Lab Switcher ── */
  .lab-switcher {
    display: flex;
    align-items: center;
  }

  .lab-switcher-trigger {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 5px 12px 5px 10px;
    border: 1px solid rgba(255, 255, 255, .25);
    border-radius: 8px;
    background: rgba(255, 255, 255, .08);
    color: #fff;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all .18s ease;
    white-space: nowrap;
    max-width: 200px;
    backdrop-filter: blur(4px);
  }

  .lab-switcher-trigger:hover {
    background: rgba(255, 255, 255, .15);
    border-color: rgba(255, 255, 255, .4);
  }

  .lab-switcher-trigger .ls-icon {
    font-size: 13px;
    opacity: .85;
    flex-shrink: 0;
  }

  .lab-switcher-trigger .ls-label {
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
    min-width: 0;
  }

  .lab-switcher-trigger .ls-caret {
    font-size: 10px;
    opacity: .65;
    margin-left: auto;
    flex-shrink: 0;
    transition: transform .2s ease;
  }

  .lab-switcher-trigger[aria-expanded="true"] .ls-caret {
    transform: rotate(180deg);
  }

  .lab-switcher-trigger .ls-active-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #10b981;
    flex-shrink: 0;
    box-shadow: 0 0 6px rgba(16, 185, 129, .5);
  }

  /* ── Lab Switcher Dropdown Menu ── */
  .ls-menu {
    min-width: 220px;
    padding: 6px;
    border: none;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(15, 23, 42, .18);
    overflow: hidden;
    margin-top: 6px !important;
  }

  .ls-menu-head {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #94a3b8;
    padding: 8px 12px 4px;
  }

  .ls-menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #334155;
    cursor: pointer;
    transition: background .12s, color .12s;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
  }

  .ls-menu-item:hover {
    background: #f1f5f9;
    color: #0f172a;
  }

  .ls-menu-item.active {
    background: #eef2ff;
    color: #4f46e5;
    font-weight: 600;
  }

  .ls-menu-item .ls-item-icon {
    width: 28px;
    height: 28px;
    border-radius: 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    background: #f1f5f9;
    color: #64748b;
    transition: all .12s;
  }

  .ls-menu-item.active .ls-item-icon {
    background: #e0e7ff;
    color: #4f46e5;
  }

  .ls-menu-item .ls-item-info {
    flex: 1;
    min-width: 0;
    line-height: 1.3;
  }

  .ls-menu-item .ls-item-name {
    display: block;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .ls-menu-item .ls-item-code {
    display: block;
    font-size: 11px;
    color: #94a3b8;
    font-weight: 400;
  }

  .ls-menu-item .ls-check {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    color: #fff;
    background: transparent;
    flex-shrink: 0;
    opacity: 0;
    transform: scale(.5);
    transition: all .18s ease;
  }

  .ls-menu-item.active .ls-check {
    opacity: 1;
    transform: scale(1);
    background: #4f46e5;
  }

  .ls-menu-divider {
    margin: 4px 12px;
    border-top: 1px solid #f1f5f9;
  }
</style>
<nav class="navbar navbar-expand-lg main-navbar">
  <form class="form-inline mr-auto">
    <ul class="navbar-nav mr-3">
      <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
      <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li>
    </ul>
    <div class="search-element" style="position:relative;">
      <input
        id="navbar-menu-search"
        class="form-control"
        type="search"
        placeholder="Cari menu…"
        aria-label="Cari menu"
        autocomplete="off"
        data-width="250">
      <button class="btn" type="button" id="navbar-menu-search-btn"><i class="fas fa-search"></i></button>
      <div class="search-backdrop"></div>

      <!-- Dropdown hasil pencarian menu -->
      <div
        id="navbar-menu-search-dropdown"
        class="dropdown-menu shadow-sm"
        style="display:none; position:absolute; top:calc(100% + 4px); left:0; min-width:260px; z-index:9999; max-height:320px; overflow-y:auto;"></div>
    </div>
  </form>

  <script>
    (function() {
      var input = document.getElementById('navbar-menu-search');
      var dropdown = document.getElementById('navbar-menu-search-dropdown');
      var activeIdx = -1;
      var items = [];
      var debounce;
      var baseSearch = '<?= base_url('menu-search') ?>';

      if (!input || !dropdown) return;

      function renderItems(data) {
        items = data;
        activeIdx = -1;
        dropdown.innerHTML = '';

        if (data.length === 0) {
          dropdown.innerHTML = '<span class="dropdown-item text-muted small">Tidak ada menu ditemukan</span>';
          dropdown.style.display = 'block';
          return;
        }

        data.forEach(function(item, i) {
          var a = document.createElement('a');
          a.className = 'dropdown-item d-flex align-items-center';
          a.href = item.url;
          a.setAttribute('data-index', i);
          a.innerHTML =
            '<i class="' + escHtml(item.icon) + ' mr-2 text-muted" style="width:16px;text-align:center;"></i>' +
            '<span>' + escHtml(item.label) + '</span>';
          dropdown.appendChild(a);
        });

        dropdown.style.display = 'block';
      }

      function escHtml(str) {
        return str
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;');
      }

      function closeDropdown() {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        items = [];
        activeIdx = -1;
      }

      function setActive(idx) {
        var links = dropdown.querySelectorAll('a.dropdown-item');
        links.forEach(function(el) {
          el.classList.remove('active');
        });
        if (idx >= 0 && idx < links.length) {
          links[idx].classList.add('active');
          links[idx].scrollIntoView({
            block: 'nearest'
          });
          activeIdx = idx;
        }
      }

      input.addEventListener('input', function() {
        clearTimeout(debounce);
        var q = input.value.trim();
        if (q.length < 1) {
          closeDropdown();
          return;
        }

        debounce = setTimeout(function() {
          fetch(baseSearch + '?q=' + encodeURIComponent(q), {
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              }
            })
            .then(function(r) {
              return r.json();
            })
            .then(renderItems)
            .catch(function() {
              closeDropdown();
            });
        }, 200);
      });

      input.addEventListener('keydown', function(e) {
        var links = dropdown.querySelectorAll('a.dropdown-item');
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          setActive(Math.min(activeIdx + 1, links.length - 1));
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          setActive(Math.max(activeIdx - 1, 0));
        } else if (e.key === 'Enter') {
          if (activeIdx >= 0 && links[activeIdx]) {
            e.preventDefault();
            window.location.href = links[activeIdx].href;
          } else if (items.length === 1) {
            e.preventDefault();
            window.location.href = items[0].url;
          }
        } else if (e.key === 'Escape') {
          closeDropdown();
        }
      });

      document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
          closeDropdown();
        }
      });

      // Prevent form submit
      input.closest('form').addEventListener('submit', function(e) {
        e.preventDefault();
      });
    })();
  </script>
  <ul class="navbar-nav navbar-right">

    <!-- Group Switcher -->
    <?php if (count($userGroups) > 1): ?>
      <!-- <li class="dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg" title="Ganti Role">
        <span class="ud-role-pill" style="<?= $roleStyle[$active] ?? 'background:#f1f5f9;color:#475569' ?>; font-size:12px; padding:3px 10px 3px 7px;">
          <span class="rp-dot"></span>
          <?= esc($authGroups->groups[$active]['title'] ?? ucfirst($active)) ?>
        </span>
        <i class="fas fa-chevron-down ml-1" style="font-size:10px;opacity:.6;"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-right rs-menu">
        <div class="rs-menu-head">Ganti Role</div>
        <?php foreach ($userGroups as $grp): ?>
          <?php if ($grp === $active): ?>
            <div class="rs-active-row">
              <span class="rs-check-circle" style="background:<?= $roleCheckBg[$grp] ?? '#475569' ?>">
                <i class="fas fa-check"></i>
              </span>
              <?= esc($authGroups->groups[$grp]['title'] ?? ucfirst($grp)) ?>
            </div>
          <?php else: ?>
            <form action="<?= base_url('switch-group') ?>" method="post" class="rs-switch-form">
              <?= csrf_field() ?>
              <input type="hidden" name="group" value="<?= $grp ?>">
              <button type="submit" class="rs-switch-btn">
                <span class="rs-ring"></span>
                <?= esc($authGroups->groups[$grp]['title'] ?? ucfirst($grp)) ?>
              </button>
            </form>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </li> -->
    <?php endif; ?>

    <!-- Lab Switcher -->
    <?php if (!empty($userLabs)): ?>
      <?php
      $activeLabName = '';
      $activeLabCode = '';
      if ($activeLabId) {
        foreach ($userLabs as $lab) {
          if ((int) $lab->id === (int) $activeLabId) {
            $activeLabName = $lab->name;
            $activeLabCode = $lab->code;
            break;
          }
        }
      }
      ?>
      <li class="nav-item dropdown lab-switcher">
        <a href="#" class="nav-link lab-switcher-trigger" data-toggle="dropdown" id="lab-toggle" aria-haspopup="true" aria-expanded="false" title="Ganti Lab">
          <i class="fas fa-flask ls-icon"></i>
          <?php if ($activeLabId && $activeLabName): ?>
            <span class="ls-active-dot"></span>
            <span class="ls-label"><?= esc($activeLabName) ?></span>
          <?php else: ?>
            <span class="ls-label" style="opacity:.8;font-weight:400;">Pilih Lab</span>
          <?php endif; ?>
          <i class="fas fa-chevron-down ls-caret"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right ls-menu" aria-labelledby="lab-toggle">
          <div class="ls-menu-head">Pilih Laboratorium</div>

          <?php if ($activeLabId): ?>
            <form action="<?= base_url('switch-lab') ?>" method="post" style="margin:0;">
              <?= csrf_field() ?>
              <input type="hidden" name="lab_id" value="">
              <button type="submit" class="ls-menu-item">
                <span class="ls-item-icon"><i class="fas fa-th-large"></i></span>
                <span class="ls-item-info">
                  <span class="ls-item-name">Semua Lab</span>
                  <span class="ls-item-code">Tampilkan semua</span>
                </span>
              </button>
            </form>
            <div class="ls-menu-divider"></div>
          <?php endif; ?>

          <?php foreach ($userLabs as $lab): ?>
            <?php $isActive = $activeLabId && (int) $lab->id === (int) $activeLabId; ?>
            <form action="<?= base_url('switch-lab') ?>" method="post" style="margin:0;">
              <?= csrf_field() ?>
              <input type="hidden" name="lab_id" value="<?= (int) $lab->id ?>">
              <button type="submit" class="ls-menu-item <?= $isActive ? 'active' : '' ?>">
                <span class="ls-item-icon" style="<?= $isActive ? 'background:#e0e7ff;color:#4f46e5;' : '' ?>">
                  <i class="fas fa-flask"></i>
                </span>
                <span class="ls-item-info">
                  <span class="ls-item-name"><?= esc($lab->name) ?></span>
                  <span class="ls-item-code"><?= esc($lab->code) ?></span>
                </span>
                <span class="ls-check"><i class="fas fa-check"></i></span>
              </button>
            </form>
          <?php endforeach; ?>
        </div>
      </li>
    <?php endif; ?>

    <!-- Notification Bell -->
    <li class="nav-item dropdown">
      <a href="#" class="nav-link nav-link-lg notif-bell-btn" data-toggle="dropdown" id="notif-toggle" aria-haspopup="true" aria-expanded="false">
        <i class="far fa-bell"></i>
        <span class="notif-count-badge d-none" id="notif-badge"></span>
      </a>
      <div class="dropdown-menu dropdown-menu-right" id="notif-panel">
        <div class="notif-panel-head">
          <span class="notif-panel-title">Notifikasi</span>
          <a href="#" id="notif-mark-all-read" class="notif-mark-all-btn">Tandai semua dibaca</a>
        </div>
        <div id="notif-list" class="notif-scroll-body">
          <div class="notif-state-msg"><i class="fas fa-circle-notch fa-spin mr-1"></i> Memuat...</div>
        </div>
        <div class="notif-panel-foot">
          <a href="<?= base_url('notifications') ?>">Lihat semua notifikasi &rarr;</a>
        </div>
      </div>
    </li>

    <script>
      (function() {
        var notifToggle = document.getElementById('notif-toggle');
        var notifList = document.getElementById('notif-list');
        var notifBadge = document.getElementById('notif-badge');
        var markAllBtn = document.getElementById('notif-mark-all-read');
        var baseUrl = '<?= base_url() ?>';

        function escHtml(str) {
          return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function timeAgo(dateStr) {
          if (!dateStr) return '';
          var diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
          if (diff < 60) return diff + 'd lalu';
          if (diff < 3600) return Math.floor(diff / 60) + 'm lalu';
          if (diff < 86400) return Math.floor(diff / 3600) + 'j lalu';
          return Math.floor(diff / 86400) + ' hari lalu';
        }

        function iconStyle(color) {
          var map = {
            primary: 'background:#ede9fe;color:#7c3aed',
            success: 'background:#d1fae5;color:#059669',
            warning: 'background:#fef3c7;color:#d97706',
            danger: 'background:#fee2e2;color:#dc2626',
            info: 'background:#dbeafe;color:#2563eb',
            secondary: 'background:#f3f4f6;color:#6b7280'
          };
          return map[color] || map.secondary;
        }

        function renderNotifs(items) {
          if (!items || items.length === 0) {
            notifList.innerHTML = '<div class="notif-state-msg">Tidak ada notifikasi</div>';
            return;
          }

          var html = '';
          items.forEach(function(n) {
            var unreadCls = n.is_read ? '' : 'notif-unread';
            var itemUrl = n.url || (baseUrl + 'notifications');
            var dot = n.is_read ? '' : '<span class="notif-unread-pip"></span>';
            html += '<a href="' + escHtml(itemUrl) + '" class="notif-row ' + unreadCls + '" data-id="' + escHtml(n.id) + '" data-read="' + (n.is_read ? '1' : '0') + '">';
            html += '  <div class="notif-row-icon" style="' + iconStyle(n.color) + '"><i class="' + escHtml(n.icon) + '"></i></div>';
            html += '  <div class="notif-row-body">';
            html += '    <div class="notif-row-title">' + escHtml(n.title) + '</div>';
            html += '    <div class="notif-row-msg">' + escHtml(n.message) + '</div>';
            html += '    <div class="notif-row-time">' + timeAgo(n.created_at) + '</div>';
            html += '  </div>';
            html += dot;
            html += '</a>';
          });

          notifList.innerHTML = html;
        }

        function fetchRecent() {
          fetch(baseUrl + 'notifications/recent', {
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              }
            })
            .then(function(r) {
              return r.json();
            })
            .then(renderNotifs)
            .catch(function() {
              notifList.innerHTML = '<div class="notif-state-msg">Gagal memuat notifikasi</div>';
            });
        }

        // Klik pada item notifikasi → mark-read, lalu navigate
        if (notifList) {
          notifList.addEventListener('click', function(e) {
            var item = e.target.closest('.notif-row');
            if (!item) return;
            if (item.dataset.read === '0') {
              var id = item.dataset.id;
              fetch(baseUrl + 'notifications/' + id + '/read', {
                method: 'POST',
                headers: {
                  'X-Requested-With': 'XMLHttpRequest',
                  'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                }
              }).catch(function() {});
            }
          });
        }

        // Buka dropdown → muat notifikasi (native click, tanpa jQuery)
        if (notifToggle) {
          notifToggle.addEventListener('click', function() {
            // parentElement = <li class="nav-item dropdown">
            var menu = notifToggle.parentElement.querySelector('.dropdown-menu');
            if (menu && !menu.classList.contains('show')) {
              // Dropdown akan DIBUKA — reset & ambil data
              notifList.innerHTML = '<div class="notif-state-msg"><i class="fas fa-circle-notch fa-spin mr-1"></i> Memuat...</div>';
              fetchRecent();
            }
          });
        }

        // Tandai semua dibaca
        if (markAllBtn) {
          markAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            fetch(baseUrl + 'notifications/read-all', {
                method: 'POST',
                headers: {
                  'X-Requested-With': 'XMLHttpRequest',
                  'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                }
              })
              .then(function() {
                if (notifBadge) {
                  notifBadge.classList.add('d-none');
                  notifBadge.textContent = '0';
                }
                fetchRecent();
              })
              .catch(function() {});
          });
        }
      })();
    </script>

    <!-- User Menu -->
    <li class="dropdown">
      <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
        <img alt="avatar" src="<?= base_url('assets/img/avatar/avatar-1.png') ?>" class="rounded-circle mr-1">
        <div class="d-sm-none d-lg-inline-block">Hi, <?= esc($currentUser->username ?? 'User') ?></div>
      </a>
      <div class="dropdown-menu dropdown-menu-right ud-menu">

        <!-- Header: avatar + name + email -->
        <div class="ud-header">
          <img src="<?= base_url('assets/img/avatar/avatar-1.png') ?>" class="ud-avatar" alt="avatar">
          <div style="min-width:0;">
            <div class="ud-name"><?= esc($currentUser->username ?? 'User') ?></div>
            <div class="ud-email"><?= esc($currentUser->email ?? '') ?></div>
          </div>
        </div>

        <!-- Role section -->
        <div class="ud-role-section">
          <div class="ud-role-label">Role Aktif</div>
          <span class="ud-role-pill" style="<?= $roleStyle[$active] ?? 'background:#f1f5f9;color:#475569' ?>">
            <span class="rp-dot"></span>
            <?= esc($authGroups->groups[$active]['title'] ?? ucfirst($active)) ?>
          </span>
          <?php if (count($userGroups) > 1): ?>
            <div class="ud-role-switch-label mt-2">Ganti ke</div>
            <?php foreach ($userGroups as $grp): ?>
              <?php if ($grp !== $active): ?>
                <form action="<?= base_url('switch-group') ?>" method="post" style="margin:0;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="group" value="<?= $grp ?>">
                  <button type="submit" class="ud-rs-btn">
                    <span class="ud-rs-ring"></span>
                    <?= esc($authGroups->groups[$grp]['title'] ?? ucfirst($grp)) ?>
                  </button>
                </form>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Action links -->
        <div class="ud-menu-actions">
          <a href="<?= base_url('profile') ?>" class="dropdown-item">
            <i class="far fa-user"></i> Profil
          </a>
          <?php if (activeGroupCan('admin.settings')): ?>
            <a href="<?= base_url('admin/settings') ?>" class="dropdown-item">
              <i class="fas fa-cog"></i> Pengaturan
            </a>
          <?php endif; ?>
          <hr class="dropdown-divider">
          <a href="<?= base_url('logout') ?>" class="dropdown-item text-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>

      </div>
    </li>
  </ul>
</nav>