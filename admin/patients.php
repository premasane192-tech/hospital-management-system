<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pageTitle = 'Patients';

$patients = $pdo->query(
    "SELECT p.*, (SELECT COUNT(*) FROM health_reports hr WHERE hr.patient_id = p.patient_id) AS report_count
     FROM patients p ORDER BY p.created_at DESC"
)->fetchAll();

require __DIR__ . '/../includes/header_admin.php';
?>
<div class="section-title">
  <h3>All patients (<?= count($patients) ?>)</h3>
  <a href="add_patient.php" class="btn btn-primary btn-sm">➕ Register Patient</a>
</div>

<div class="search-bar">
  <input type="text" placeholder="Search by name, code, phone or email…" data-search-input="#patientsTable">
</div>

<div class="table-wrap">
<table class="data-table" id="patientsTable">
  <thead><tr><th>Code</th><th>Name</th><th>Gender</th><th>Phone</th><th>Email</th><th>Reports</th><th>Registered</th><th>Actions</th></tr></thead>
  <tbody>
  <?php if (!$patients): ?>
    <tr><td colspan="8" class="empty-state"><div class="icon">🗂️</div>No patients yet. Register your first patient to get started.</td></tr>
  <?php endif; ?>
  <?php foreach ($patients as $p): ?>
    <tr>
      <td class="mono"><?= e($p['patient_code']) ?></td>
      <td><?= e($p['full_name']) ?></td>
      <td><?= e($p['gender']) ?></td>
      <td><?= e($p['phone']) ?></td>
      <td><?= e($p['email']) ?></td>
      <td><?= (int)$p['report_count'] ?></td>
      <td><?= e(date('d M Y', strtotime($p['created_at']))) ?></td>
      <td style="white-space:nowrap;">
        <a href="patient_view.php?id=<?= (int)$p['patient_id'] ?>" class="btn btn-outline btn-sm">View</a>
        <a href="edit_patient.php?id=<?= (int)$p['patient_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
        <a href="delete_patient.php?id=<?= (int)$p['patient_id'] ?>" class="btn btn-danger btn-sm"
           data-confirm="Delete <?= e($p['full_name']) ?> and all their reports? This cannot be undone.">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
