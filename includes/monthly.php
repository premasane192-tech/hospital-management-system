<?php
/**
 * Shared helpers for MONTHLY report downloads.
 * Used by admin/download_monthly.php (all patients) and
 * user/download_monthly.php (only the logged-in patient's own reports).
 */

/** Returns 'YYYY-MM', 'all', or null when the value is invalid. */
function valid_month_param($m): ?string {
    if ($m === 'all') return 'all';
    return (is_string($m) && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $m)) ? $m : null;
}

function month_label(string $month): string {
    return $month === 'all' ? 'All Months' : date('F Y', strtotime($month . '-01'));
}

/**
 * Fetch reports (with their test results attached) for a month.
 * $patientId = null  -> every patient (admin)
 * $patientId = 12    -> only that patient (user portal)
 */
function fetch_monthly_reports(PDO $pdo, string $month, ?int $patientId = null): array {
    $sql = "SELECT hr.report_id, hr.report_code, hr.report_date, hr.status,
                   hr.doctor_notes, hr.recommendations, hr.follow_up,
                   p.patient_code, p.full_name AS patient_name, p.gender,
                   p.date_of_birth, p.phone AS patient_phone,
                   d.full_name AS doctor_name
            FROM health_reports hr
            JOIN patients p ON p.patient_id = hr.patient_id
            LEFT JOIN doctors d ON d.doctor_id = hr.doctor_id
            WHERE 1=1";
    $params = [];
    if ($month !== 'all') {
        $start = $month . '-01';
        $sql .= " AND hr.report_date BETWEEN ? AND ?";
        $params[] = $start;
        $params[] = date('Y-m-t', strtotime($start));
    }
    if ($patientId !== null) {
        $sql .= " AND hr.patient_id = ?";
        $params[] = $patientId;
    }
    $sql .= " ORDER BY hr.report_date ASC, hr.report_id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reports = $stmt->fetchAll();
    if (!$reports) return [];

    $ids = array_column($reports, 'report_id');
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $t = $pdo->prepare("SELECT * FROM test_results WHERE report_id IN ($in) ORDER BY result_id ASC");
    $t->execute($ids);
    $byReport = [];
    foreach ($t->fetchAll() as $row) $byReport[$row['report_id']][] = $row;
    foreach ($reports as &$r) $r['results'] = $byReport[$r['report_id']] ?? [];
    unset($r);
    return $reports;
}

/** Neutralise spreadsheet formula injection (=, +, -, @) in text cells. */
function csv_safe($v): string {
    $v = (string) $v;
    return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
}

/** Stream an Excel-friendly CSV (one row per test result). */
function output_monthly_csv(array $reports, string $filename): void {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM so Excel shows symbols like °F correctly
    $out = fopen('php://output', 'w');
    $put = fn(array $row) => fputcsv($out, $row, ',', '"', '\\');

    $put(['Report Code','Report Date','Patient Code','Patient Name','Gender','Doctor','Report Status',
          'Test','Value','Unit','Ref Low','Ref High','Result',
          'Doctor Notes','Recommendations','Follow Up']);
    foreach ($reports as $r) {
        $base = [
            $r['report_code'], $r['report_date'], $r['patient_code'], csv_safe($r['patient_name']),
            $r['gender'], csv_safe($r['doctor_name'] ?? ''), ucfirst($r['status']),
        ];
        $tail = [csv_safe($r['doctor_notes']), csv_safe($r['recommendations']), csv_safe($r['follow_up'])];
        if (!$r['results']) {
            $put(array_merge($base, ['','','','','',''], $tail));
            continue;
        }
        foreach ($r['results'] as $t) {
            $put(array_merge($base, [
                csv_safe($t['test_name']), $t['value'], csv_safe($t['unit']), $t['ref_low'], $t['ref_high'], $t['status'],
            ], $tail));
        }
    }
    fclose($out);
}

/** Printable "Save as PDF" page containing every report of the month. */
function output_monthly_print(array $reports, string $label, string $scopeLine, string $backUrl): void {
    $total = count($reports);
    $abnormal = 0;
    foreach ($reports as $r) foreach ($r['results'] as $t) if ($t['status'] !== 'Normal') $abnormal++;
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Monthly Reports — <?= e($label) ?> · <?= e(HOSPITAL_NAME) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="ambient-bg no-print"><span></span><span></span><span></span></div>
<div class="content fade-in" style="margin:0 auto;">
  <div class="no-print" style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; margin-bottom:18px;">
    <a class="btn btn-outline" href="<?= e($backUrl) ?>">← Back</a>
    <button class="btn btn-primary" data-print>🖨️ Print / Save as PDF</button>
  </div>

  <div class="report-doc" style="margin-bottom:22px;">
    <div class="report-head">
      <div>
        <h2><?= e(HOSPITAL_NAME) ?> — Monthly Report Summary</h2>
        <p class="hint" style="margin:2px 0 0;"><?= e($scopeLine) ?></p>
      </div>
      <div style="text-align:right;">
        <div class="badge-code"><?= e($label) ?></div>
        <p class="hint" style="margin:6px 0 0;">Generated: <?= e(date('d M Y, h:i A')) ?></p>
      </div>
    </div>
    <div class="report-grid">
      <div><span>Total reports:</span> <strong><?= (int)$total ?></strong></div>
      <div><span>Out-of-range results:</span> <strong><?= (int)$abnormal ?></strong></div>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Report</th><th>Date</th><th>Patient</th><th>Doctor</th></tr></thead>
        <tbody>
        <?php foreach ($reports as $r): ?>
          <tr>
            <td class="mono"><?= e($r['report_code']) ?></td>
            <td><?= e(date('d M Y', strtotime($r['report_date']))) ?></td>
            <td><?= e($r['patient_name']) ?> <span class="hint mono">(<?= e($r['patient_code']) ?>)</span></td>
            <td><?= e($r['doctor_name'] ?? '—') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php foreach ($reports as $r):
      $age = $r['date_of_birth'] ? floor((time() - strtotime($r['date_of_birth'])) / 31556926) : null; ?>
  <div class="report-doc month-report" style="margin-bottom:22px;">
    <div class="report-head">
      <div><h2><?= e($r['patient_name']) ?></h2>
        <p class="hint" style="margin:2px 0 0;"><?= e($r['patient_code']) ?> · <?= $age !== null ? e($age) . ' yrs / ' : '' ?><?= e($r['gender']) ?></p></div>
      <div style="text-align:right;">
        <div class="badge-code"><?= e($r['report_code']) ?></div>
        <p class="hint" style="margin:6px 0 0;"><?= e(date('d M Y', strtotime($r['report_date']))) ?> · <?= e($r['doctor_name'] ?? 'No doctor assigned') ?></p>
      </div>
    </div>
    <div class="table-wrap" style="margin-bottom:14px;">
      <table class="data-table">
        <thead><tr><th>Test</th><th>Value</th><th>Unit</th><th>Reference</th><th>Result</th></tr></thead>
        <tbody>
        <?php if (!$r['results']): ?><tr><td colspan="5" class="empty-state">No test results.</td></tr><?php endif; ?>
        <?php foreach ($r['results'] as $t): ?>
          <tr>
            <td><?= e($t['test_name']) ?></td>
            <td class="mono"><?= e($t['value']) ?></td>
            <td class="hint"><?= e($t['unit']) ?></td>
            <td class="mono hint"><?= e($t['ref_low']) ?> – <?= e($t['ref_high']) ?></td>
            <td><span class="<?= status_class($t['status']) ?>"><?= e($t['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if ($r['doctor_notes']): ?><p><strong>Doctor notes:</strong> <?= nl2br(e($r['doctor_notes'])) ?></p><?php endif; ?>
    <?php if ($r['recommendations']): ?><p><strong>Recommendations:</strong> <?= nl2br(e($r['recommendations'])) ?></p><?php endif; ?>
    <?php if ($r['follow_up']): ?><p><strong>Follow-up:</strong> <?= nl2br(e($r['follow_up'])) ?></p><?php endif; ?>
  </div>
  <?php endforeach; ?>

  <p class="disclaimer">This document is a digital summary for information only and is not a substitute for professional medical advice.</p>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
<?php
}
