<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pageTitle = 'Manage Users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $userId = (int) $_POST['user_id'];
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle') {
        $stmt = $pdo->prepare("SELECT status FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $current = $stmt->fetchColumn();
        $new = $current === 'active' ? 'disabled' : 'active';
        $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?")->execute([$new, $userId]);
        log_activity($pdo, 'admin', $_SESSION['admin_id'], "Set user #$userId status to $new");
        flash('success', 'User status updated.');
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$userId]);
        log_activity($pdo, 'admin', $_SESSION['admin_id'], "Deleted user #$userId");
        flash('success', 'User account deleted. Their patient record (if any) remains but is no longer linked to a login.');
    }
    redirect(BASE_URL . '/admin/users.php');
}

$users = $pdo->query(
    "SELECT u.*, p.patient_code FROM users u LEFT JOIN patients p ON p.user_id = u.user_id ORDER BY u.created_at DESC"
)->fetchAll();

require __DIR__ . '/../includes/header_admin.php';
?>
<?php if ($msg = flash('success')): ?><div class="alert alert-success" data-autohide><?= e($msg) ?></div><?php endif; ?>

<div class="section-title"><h3>All user accounts (<?= count($users) ?>)</h3></div>
<div class="search-bar"><input type="text" placeholder="Search by name, username, email…" data-search-input="#usersTable"></div>

<div class="table-wrap">
<table class="data-table" id="usersTable">
  <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Linked Patient</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
  <tbody>
  <?php if (!$users): ?>
    <tr><td colspan="7" class="empty-state">No registered users yet.</td></tr>
  <?php endif; ?>
  <?php foreach ($users as $u): ?>
    <tr>
      <td><?= e($u['full_name']) ?></td>
      <td class="mono"><?= e($u['username']) ?></td>
      <td><?= e($u['email']) ?></td>
      <td class="mono"><?= e($u['patient_code'] ?? '—') ?></td>
      <td><span class="pill <?= $u['status'] === 'active' ? 'pill-normal' : 'pill-high' ?>"><?= e(ucfirst($u['status'])) ?></span></td>
      <td><?= e(date('d M Y', strtotime($u['created_at']))) ?></td>
      <td style="white-space:nowrap;">
        <form method="post" style="display:inline;">
          <?= csrf_field() ?>
          <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
          <input type="hidden" name="action" value="toggle">
          <button type="submit" class="btn btn-outline btn-sm"><?= $u['status'] === 'active' ? 'Disable' : 'Enable' ?></button>
        </form>
        <form method="post" style="display:inline;" onsubmit="return confirm('Delete this user account?');">
          <?= csrf_field() ?>
          <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
          <input type="hidden" name="action" value="delete">
          <button type="submit" class="btn btn-danger btn-sm">Delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
