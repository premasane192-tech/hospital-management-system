<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pageTitle = 'Medical References';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $refId = (int) $_POST['reference_id'];
    $stmt = $pdo->prepare(
        "UPDATE medical_references SET low_value=?, high_value=?, unit=?, interpretation=? WHERE reference_id=?"
    );
    $stmt->execute([
        (float) $_POST['low_value'],
        (float) $_POST['high_value'],
        clean($_POST['unit']),
        clean($_POST['interpretation']),
        $refId,
    ]);
    log_activity($pdo, 'admin', $_SESSION['admin_id'], "Updated reference range #$refId");
    flash('success', 'Reference range updated.');
    redirect(BASE_URL . '/admin/references.php');
}

$refs = $pdo->query("SELECT * FROM medical_references ORDER BY test_name")->fetchAll();
require __DIR__ . '/../includes/header_admin.php';
?>
<?php if ($msg = flash('success')): ?><div class="alert alert-success" data-autohide><?= e($msg) ?></div><?php endif; ?>
<p class="hint" style="margin-bottom:18px;">These configurable ranges power the automatic Low / Normal / High analysis shown on every health report.</p>

<div class="grid grid-2">
<?php foreach ($refs as $r): ?>
  <div class="card">
    <h3 style="margin-top:0;"><?= e($r['test_name']) ?> <span class="mono hint">(<?= e($r['test_key']) ?>)</span></h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="reference_id" value="<?= (int)$r['reference_id'] ?>">
      <div class="field-row">
        <div class="field"><label>Low value</label><input type="number" step="0.01" name="low_value" value="<?= e($r['low_value']) ?>" required></div>
        <div class="field"><label>High value</label><input type="number" step="0.01" name="high_value" value="<?= e($r['high_value']) ?>" required></div>
      </div>
      <div class="field"><label>Unit</label><input type="text" name="unit" value="<?= e($r['unit']) ?>" required></div>
      <div class="field"><label>Educational interpretation</label><textarea name="interpretation"><?= e($r['interpretation']) ?></textarea></div>
      <button type="submit" class="btn btn-outline btn-sm">Save</button>
    </form>
  </div>
<?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
