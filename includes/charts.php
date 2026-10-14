<?php
/** Small dashboard chart helpers (pure HTML/CSS bars — no external libraries). */

/** Reports per month for the last 6 months. $patientId = null -> everyone. */
function month_series(PDO $pdo, ?int $patientId = null): array {
    $start = date('Y-m-01', strtotime('-5 months', strtotime(date('Y-m-01'))));
    $sql = "SELECT DATE_FORMAT(report_date, '%Y-%m') AS ym, COUNT(*) AS c FROM health_reports WHERE report_date >= ?";
    $params = [$start];
    if ($patientId !== null) { $sql .= " AND patient_id = ?"; $params[] = $patientId; }
    $sql .= " GROUP BY ym";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $found = $st->fetchAll(PDO::FETCH_KEY_PAIR);

    $series = [];
    for ($i = 5; $i >= 0; $i--) {
        $ts = strtotime("-$i months", strtotime(date('Y-m-01')));
        $series[] = ['label' => date('M', $ts), 'count' => (int)($found[date('Y-m', $ts)] ?? 0)];
    }
    return $series;
}

function render_bar_chart(array $series): void {
    $max = max(1, ...array_column($series, 'count'));
    echo '<div class="bars">';
    foreach ($series as $i => $s) {
        $h = $s['count'] ? max(8, round($s['count'] / $max * 100)) : 3;
        echo '<div class="bar-col" style="--i:' . (int)$i . '">'
           . '<span class="bar-val">' . (int)$s['count'] . '</span>'
           . '<div class="bar" style="--h:' . (int)$h . '%"></div>'
           . '<span class="bar-label">' . e($s['label']) . '</span></div>';
    }
    echo '</div>';
}

/** Normal / Low / High counts. $reportId = null -> across all reports. */
function status_mix(PDO $pdo, ?int $reportId = null): array {
    $sql = "SELECT status, COUNT(*) FROM test_results";
    $params = [];
    if ($reportId !== null) { $sql .= " WHERE report_id = ?"; $params[] = $reportId; }
    $sql .= " GROUP BY status";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll(PDO::FETCH_KEY_PAIR);
    return ['Normal' => (int)($rows['Normal'] ?? 0), 'Low' => (int)($rows['Low'] ?? 0), 'High' => (int)($rows['High'] ?? 0)];
}

function render_status_mix(array $mix): void {
    $total = array_sum($mix);
    if (!$total) { echo '<div class="empty-state" style="padding:24px;">No test results yet.</div>'; return; }
    $cls = ['Normal' => 'seg-normal', 'Low' => 'seg-low', 'High' => 'seg-high'];
    echo '<div class="mix-bar">';
    $i = 0;
    foreach ($mix as $k => $v) {
        if (!$v) continue;
        echo '<div class="mix-seg ' . $cls[$k] . '" style="--w:' . round($v / $total * 100, 2) . '%; --i:' . $i++ . '" title="' . e($k) . ': ' . $v . '"></div>';
    }
    echo '</div><div class="mix-legend">';
    foreach ($mix as $k => $v) {
        echo '<span><i class="dot ' . $cls[$k] . '"></i>' . e($k) . ' <strong data-count="' . $v . '">' . $v . '</strong>'
           . ' <small>(' . round($v / $total * 100) . '%)</small></span>';
    }
    echo '</div>';
}
