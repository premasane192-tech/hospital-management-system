<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pageTitle = 'Doctors';

$doctors = $pdo->query("SELECT * FROM doctors ORDER BY full_name")->fetchAll();

require __DIR__ . '/../includes/header_admin.php';
?>
<?php if ($msg = flash('success')): ?><div class="alert alert-success" data-autohide><?= e($msg) ?></div><?php endif; ?>
<div class="section-title">
  <h3>All doctors (<?= count($doctors) ?>)</h3>
  <a href="add_doctor.php" class="btn btn-primary btn-sm">➕ Add Doctor</a>
</div>

<div class="grid grid-2">
  <?php if (!$doctors): ?>
    <div class="empty-state"><div class="icon">🩺</div>No doctors added yet.</div>
  <?php endif; ?>
  <?php foreach ($doctors as $d): ?>
    <div class="card">
      <div class="section-title" style="margin-top:0;">
        <h3 style="margin:0;"><?= e($d['full_name']) ?></h3>
        <a href="edit_doctor.php?id=<?= (int)$d['doctor_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
      </div>
      <p class="hint mono">Reg. No: <?= e($d['registration_no'] ?: '—') ?> · <?= e($d['qualification']) ?></p>
      <p><strong><?= e($d['specialization']) ?></strong> — <?= e($d['clinic_name']) ?></p>
      <p style="font-size:.88rem;"><?= nl2br(e($d['description'])) ?></p>
      <p class="hint">📞 <?= e($d['phone'] ?: '—') ?><br>📍 <?= e($d['address'] ?: '—') ?></p>
    </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
