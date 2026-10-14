<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pageTitle = 'Generate Health Report';

$patients = $pdo->query("SELECT patient_id, patient_code, full_name FROM patients ORDER BY full_name")->fetchAll();
$doctors  = $pdo->query("SELECT doctor_id, full_name, specialization FROM doctors ORDER BY full_name")->fetchAll();
$refs     = $pdo->query("SELECT * FROM medical_references ORDER BY test_name")->fetchAll();

$selectedPatientId = (int) ($_GET['patient_id'] ?? 0);
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $old = $_POST;

    $patientId = (int) ($_POST['patient_id'] ?? 0);
    $doctorId  = (int) ($_POST['doctor_id'] ?? 0) ?: null;
    $reportDate = $_POST['report_date'] ?? date('Y-m-d');
    $doctorNotes = clean($_POST['doctor_notes'] ?? '');
    $recommendations = clean($_POST['recommendations'] ?? '');
    $followUp = clean($_POST['follow_up'] ?? '');
    $values = $_POST['test'] ?? [];

    if (!$patientId) {
        $errors[] = 'Please select a patient.';
    }
    $hasAnyValue = false;
    foreach ($values as $v) { if ($v !== '') $hasAnyValue = true; }
    if (!$hasAnyValue) {
        $errors[] = 'Please enter at least one test result.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            $reportCode = generate_report_code($pdo);
            $stmt = $pdo->prepare(
                "INSERT INTO health_reports (report_code, patient_id, doctor_id, created_by_admin, report_date, doctor_notes, recommendations, follow_up, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'final')"
            );
            $stmt->execute([$reportCode, $patientId, $doctorId, $_SESSION['admin_id'], $reportDate, $doctorNotes, $recommendations, $followUp]);
            $reportId = $pdo->lastInsertId();

            $insertResult = $pdo->prepare(
                "INSERT INTO test_results (report_id, test_key, test_name, value, unit, ref_low, ref_high, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            foreach ($refs as $ref) {
                $key = $ref['test_key'];
                if (!isset($values[$key]) || $values[$key] === '') continue;
                $val = (float) $values[$key];
                $status = evaluate_status($val, $ref['low_value'], $ref['high_value']);
                $insertResult->execute([
                    $reportId, $key, $ref['test_name'], $val, $ref['unit'], $ref['low_value'], $ref['high_value'], $status
                ]);
            }

            $pdo->commit();
            log_activity($pdo, 'admin', $_SESSION['admin_id'], "Generated report $reportCode");
            flash('success', "Report $reportCode generated successfully.");
            redirect(BASE_URL . '/admin/view_report.php?id=' . $reportId);
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Something went wrong while saving the report. Please try again.';
        }
    }
}

require __DIR__ . '/../includes/header_admin.php';
?>
<?php foreach ($errors as $err): ?><div class="alert alert-error" data-autohide><?= e($err) ?></div><?php endforeach; ?>

<form method="post" novalidate>
  <?= csrf_field() ?>

  <div class="card" style="margin-bottom:20px;">
    <h3 style="margin-top:0;">1. Patient &amp; visit details</h3>
    <div class="field-row">
      <div class="field">
        <label>Patient *</label>
        <select name="patient_id" required>
          <option value="">Select a patient…</option>
          <?php foreach ($patients as $p): ?>
            <option value="<?= (int)$p['patient_id'] ?>" <?= ($selectedPatientId === (int)$p['patient_id'] || ($old['patient_id'] ?? '') == $p['patient_id']) ? 'selected' : '' ?>>
              <?= e($p['full_name']) ?> (<?= e($p['patient_code']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Report date</label>
        <input type="date" name="report_date" value="<?= e($old['report_date'] ?? date('Y-m-d')) ?>">
      </div>
    </div>
  </div>

  <div class="card" style="margin-bottom:20px;">
    <h3 style="margin-top:0;">2. Diagnostic panel — enter available results</h3>
    <p class="hint">Leave a field blank to skip that test. Status previews automatically against the configured reference range.</p>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Test</th><th>Value</th><th>Unit</th><th>Reference range</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($refs as $ref): $key = $ref['test_key']; ?>
          <tr>
            <td><?= e($ref['test_name']) ?></td>
            <td style="max-width:140px;">
              <input type="number" step="0.01" name="test[<?= e($key) ?>]"
                     value="<?= e($old['test'][$key] ?? '') ?>"
                     data-test-input data-test-key="<?= e($key) ?>"
                     data-low="<?= e($ref['low_value']) ?>" data-high="<?= e($ref['high_value']) ?>"
                     placeholder="—">
            </td>
            <td class="hint"><?= e($ref['unit']) ?></td>
            <td class="mono hint"><?= e($ref['low_value']) ?> – <?= e($ref['high_value']) ?></td>
            <td><span id="status-<?= e($key) ?>" class="pill">—</span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card" style="margin-bottom:20px;">
    <h3 style="margin-top:0;">3. Doctor &amp; clinical notes</h3>
    <div class="field">
      <label>Reviewing doctor</label>
      <select name="doctor_id">
        <option value="">Select a doctor…</option>
        <?php foreach ($doctors as $d): ?>
          <option value="<?= (int)$d['doctor_id'] ?>" <?= ($old['doctor_id'] ?? '') == $d['doctor_id'] ? 'selected' : '' ?>>
            <?= e($d['full_name']) ?> — <?= e($d['specialization']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field"><label>Clinical notes / description</label><textarea name="doctor_notes"><?= e($old['doctor_notes'] ?? '') ?></textarea></div>
    <div class="field"><label>Recommendations</label><textarea name="recommendations"><?= e($old['recommendations'] ?? '') ?></textarea></div>
    <div class="field"><label>Follow-up instructions</label><textarea name="follow_up"><?= e($old['follow_up'] ?? '') ?></textarea></div>
  </div>

  <button type="submit" class="btn btn-primary">Generate Report</button>
  <a href="reports.php" class="btn btn-outline">Cancel</a>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>
