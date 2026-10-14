<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(BASE_URL . '/book/index.php');
verify_csrf();

$id = (int)($_POST['id'] ?? 0);
// Ownership check: only an appointment booked from this browser session, and still booked, can be cancelled
$mine = array_map('intval', $_SESSION['bmc_appts'] ?? []);
$ok = false;
if (in_array($id, $mine, true)) {
    $st = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ? AND status = 'booked'");
    $st->execute([$id]);
    $ok = $st->rowCount() > 0;
}

if ($ok) {
    log_activity($pdo, 'user', 0, 'Cancelled appointment #' . $id . ' (Book My Care guest)');
    flash('success', 'Appointment cancelled.');
} else {
    flash('error', 'That appointment could not be cancelled.');
}
redirect(BASE_URL . '/book/index.php');
