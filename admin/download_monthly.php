<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/monthly.php';
require_admin();

$back = BASE_URL . '/admin/reports.php';
$month = !empty($_GET['all']) ? 'all' : valid_month_param($_GET['month'] ?? date('Y-m'));
if (!$month) { flash('error', 'Please choose a valid month.'); redirect($back); }
$format = ($_GET['format'] ?? 'csv') === 'print' ? 'print' : 'csv';

$reports = fetch_monthly_reports($pdo, $month);
if (!$reports) { flash('error', 'No reports found for ' . month_label($month) . '.'); redirect($back); }

log_activity($pdo, 'admin', (int)$_SESSION['admin_id'], "Downloaded monthly reports ($month, $format)");

if ($format === 'print') {
    output_monthly_print($reports, month_label($month), 'All patients · ' . count($reports) . ' report(s)', $back);
} else {
    output_monthly_csv($reports, 'careplus-monthly-reports-' . $month . '.csv');
}
exit;
