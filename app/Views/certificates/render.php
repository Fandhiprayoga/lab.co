<?php
$isPreview = $is_preview ?? false;
$canvasW   = $template->page_orientation === 'landscape' ? 1122 : 794;
$canvasH   = $template->page_orientation === 'landscape' ? 794 : 1122;
$pageSize  = $template->page_orientation === 'landscape' ? 'A4 landscape' : 'A4 portrait';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Sertifikat<?= ! $isPreview && isset($data['recipient_name']) ? ' - ' . esc($data['recipient_name']) : '' ?></title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #e0e0e0; font-family: Arial, sans-serif; }
    .cert-canvas {
      width: <?= $canvasW ?>px;
      height: <?= $canvasH ?>px;
      position: relative;
      overflow: hidden;
      <?php if ($template->background_path): ?>
      background-image: url('<?= base_url($template->background_path) ?>');
      background-size: 100% 100%;
      background-position: center;
      <?php endif; ?>
      background-color: #fff;
    }
    .cert-component {
      position: absolute;
      white-space: pre-wrap;
      word-wrap: break-word;
    }
    <?php if (! $isPreview): ?>
    .no-print {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 9999;
    }
    .no-print button {
      padding: 12px 24px;
      background: #6777ef;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .no-print button:hover { background: #5562d3; }

    @media print {
      @page { size: <?= $pageSize ?>; margin: 0; }
      body { background: #fff; }
      .no-print { display: none !important; }
      .cert-canvas {
        width: 100%;
        height: 100%;
        position: fixed;
        top: 0;
        left: 0;
      }
    }
    <?php endif; ?>
  </style>
</head>
<body>
  <div class="cert-canvas">
    <?php foreach ($components as $c): ?>
    <?php
      $text = '';
      switch ($c->component_type) {
        case 'recipient_name': $text = $data['recipient_name'] ?? 'Nama Penerima'; break;
        case 'cert_number':    $text = $data['cert_number'] ?? 'CERT-000000-XXXXXX'; break;
        case 'issued_date':    $text = $data['issued_date'] ?? date('d F Y'); break;
        case 'title':          $text = $c->content; break;
        case 'custom_text':    $text = $c->content; break;
        default:                $text = $c->content ?? ''; break;
      }
      $widthStyle = $c->width ? 'width:' . (int) $c->width . 'px;' : '';
    ?>
      <div class="cert-component" style="
        left:<?= (int) $c->x_position ?>px;
        top:<?= (int) $c->y_position ?>px;
        <?= $widthStyle ?>
        font-size:<?= (int) $c->font_size ?>px;
        color:<?= esc($c->font_color) ?>;
        font-family:<?= esc($c->font_family) ?>;
        font-weight:<?= esc($c->font_weight) ?>;
        text-align:<?= esc($c->text_align) ?>;
      "><?= esc($text) ?></div>
    <?php endforeach; ?>
  </div>

  <?php if (! $isPreview): ?>
  <div class="no-print">
    <button onclick="window.print()"><i class="fas fa-print"></i> Cetak Sertifikat</button>
  </div>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
  <?php endif; ?>
</body>
</html>
