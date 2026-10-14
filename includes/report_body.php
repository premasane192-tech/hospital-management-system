<?php
require_once __DIR__ . '/lang.php';
$lang = current_report_lang();
// Preserve every existing query param (e.g. ?id=12) while swapping only "lang"
$baseQuery = $_GET; unset($baseQuery['lang']);
$qs = http_build_query($baseQuery);
?>

<div class="no-print" style="display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:18px; flex-wrap:wrap;">
  <div class="lang-switch">
    <span class="hint" style="margin-right:4px;"><?= e(t('language', $lang)) ?>:</span>
    <?php foreach (SUPPORTED_LANGS as $code => $label): ?>
      <a class="<?= $lang === $code ? 'active' : '' ?>" href="?<?= $qs ? $qs . '&' : '' ?>lang=<?= e($code) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>
  <button class="btn btn-primary" data-print>🖨️ <?= e(t('print_button', $lang)) ?></button>
</div>

<div class="report-doc" lang="<?= e($lang) ?>">
  <div class="report-head">
    <div>
      <div style="display:flex; align-items:center; gap:10px;">
        <svg class="pulse-mini" viewBox="0 0 64 22" xmlns="http://www.w3.org/2000/svg">
          <path d="M0 11 H14 L18 3 L24 19 L29 11 H36 L40 6 L44 16 L48 11 H64" stroke="#1D6FB8"/>
        </svg>
        <h2><?= e(HOSPITAL_NAME) ?></h2>
      </div>
      <p class="hint" style="margin:2px 0 0;"><?= e(t('report_subtitle', $lang)) ?></p>
    </div>
    <div style="text-align:right;">
      <div class="badge-code"><?= e(t('report_id', $lang)) ?>: <?= e($report['report_code']) ?></div>
      <p class="hint" style="margin:6px 0 0;"><?= e(t('date', $lang)) ?>: <?= e(date('d M Y', strtotime($report['report_date']))) ?></p>
    </div>
  </div>

  <div class="report-grid">
    <div><span><?= e(t('patient_id', $lang)) ?>:</span> <strong class="mono"><?= e($report['patient_code']) ?></strong></div>
    <div><span><?= e(t('patient_name', $lang)) ?>:</span> <strong><?= e($report['patient_name']) ?></strong></div>
    <div><span><?= e(t('age_gender', $lang)) ?>:</span> <strong><?= $age !== null ? e($age) . ' ' . e(t('yrs', $lang)) : '—' ?> / <?= e($report['gender']) ?></strong></div>
    <div><span><?= e(t('contact', $lang)) ?>:</span> <strong><?= e($report['patient_phone'] ?: '—') ?></strong></div>
  </div>

  <h3><?= e(t('test_results_heading', $lang)) ?></h3>
  <div class="table-wrap" style="margin-bottom:22px;">
    <table class="data-table">
      <thead><tr><th><?= e(t('test', $lang)) ?></th><th><?= e(t('value', $lang)) ?></th><th><?= e(t('unit', $lang)) ?></th><th><?= e(t('reference_range', $lang)) ?></th><th><?= e(t('status', $lang)) ?></th></tr></thead>
      <tbody>
      <?php if (!$results): ?>
        <tr><td colspan="5" class="empty-state"><?= e(t('no_results', $lang)) ?></td></tr>
      <?php endif; ?>
      <?php foreach ($results as $t): ?>
        <tr>
          <td><?= e($t['test_name']) ?></td>
          <td class="mono"><?= e($t['value']) ?></td>
          <td class="hint"><?= e($t['unit']) ?></td>
          <td class="mono hint"><?= e($t['ref_low']) ?> – <?= e($t['ref_high']) ?></td>
          <td><span class="<?= status_class($t['status']) ?>"><?= e(t_status($t['status'], $lang)) ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php
  // Pull medical reference interpretations for any test flagged Low/High
  $flagged = array_filter($results, fn($t) => $t['status'] !== 'Normal');
  if ($flagged):
      $keys = array_column($flagged, 'test_key');
      $in = implode(',', array_fill(0, count($keys), '?'));
      $refStmt = $pdo->prepare("SELECT test_key, test_name, interpretation FROM medical_references WHERE test_key IN ($in)");
      $refStmt->execute($keys);
      $refRows = $refStmt->fetchAll();
  ?>
  <h3><?= e(t('medical_reference_heading', $lang)) ?></h3>
  <ul style="font-size:.88rem; color:var(--ink-soft); padding-left:20px;">
    <?php foreach ($refRows as $rr): ?>
      <li style="margin-bottom:6px;"><strong style="color:var(--ink);"><?= e($rr['test_name']) ?>:</strong> <?= e($rr['interpretation']) ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>

  <?php if ($report['doctor_notes']): ?>
    <h3><?= e(t('doctor_notes_heading', $lang)) ?></h3>
    <p><?= nl2br(e($report['doctor_notes'])) ?></p>
  <?php endif; ?>

  <?php if ($report['recommendations']): ?>
    <h3><?= e(t('recommendations_heading', $lang)) ?></h3>
    <p><?= nl2br(e($report['recommendations'])) ?></p>
  <?php endif; ?>

  <?php if ($report['follow_up']): ?>
    <h3><?= e(t('follow_up_heading', $lang)) ?></h3>
    <p><?= nl2br(e($report['follow_up'])) ?></p>
  <?php endif; ?>

  <div class="signature-block">
    <div>
      <?php if ($report['doctor_name']): ?>
        <p style="margin:0; font-weight:600; color:var(--primary-dk);"><?= e($report['doctor_name']) ?></p>
        <p class="hint" style="margin:2px 0 0;"><?= e($report['qualification']) ?> · <?= e($report['specialization']) ?></p>
        <p class="hint" style="margin:0;"><?= e(t('reg_no', $lang)) ?>: <?= e($report['registration_no'] ?: '—') ?></p>
      <?php else: ?>
        <p class="hint"><?= e(t('no_doctor', $lang)) ?></p>
      <?php endif; ?>
    </div>
    <div class="sig-line"><?= e(t('doctor_signature', $lang)) ?></div>
  </div>

  <p class="disclaimer">
    <strong><?= e(t('disclaimer_label', $lang)) ?></strong> <?= e(t('disclaimer_text', $lang)) ?>
  </p>
</div>
