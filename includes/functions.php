<?php
/** Escape output safely */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Trim + strip tags for text inputs */
function clean($value) {
    return trim(strip_tags($value ?? ''));
}

/** Generate & verify CSRF tokens for every state-changing form */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}
function verify_csrf() {
    $sent = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        http_response_code(403);
        die('Invalid or expired form submission (CSRF check failed). Please go back and try again.');
    }
}

/** Flash messages between redirects */
function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

/** Unique patient code e.g. PT-2026-0007 */
function generate_patient_code(PDO $pdo) {
    $year = date('Y');
    $count = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn() + 1;
    return sprintf('PT-%s-%04d', $year, $count);
}

/** Unique report code e.g. RPT-20260811-0007 */
function generate_report_code(PDO $pdo) {
    $stamp = date('Ymd');
    $count = $pdo->query("SELECT COUNT(*) FROM health_reports")->fetchColumn() + 1;
    return sprintf('RPT-%s-%04d', $stamp, $count);
}

/**
 * Compare a numeric test value against a reference range
 * and return 'Low' | 'Normal' | 'High'
 */
function evaluate_status($value, $low, $high) {
    $value = (float) $value;
    if ($value < (float) $low) return 'Low';
    if ($value > (float) $high) return 'High';
    return 'Normal';
}

/** Small helper to build a CSS class for a status pill */
function status_class($status) {
    return match ($status) {
        'Low'    => 'pill pill-low',
        'High'   => 'pill pill-high',
        default  => 'pill pill-normal',
    };
}

/** Redirect helper */
function redirect($path) {
    header('Location: ' . $path);
    exit;
}

/** Log an action to the activity_log table */
function log_activity(PDO $pdo, string $actor_type, int $actor_id, string $action) {
    $stmt = $pdo->prepare("INSERT INTO activity_log (actor_type, actor_id, action) VALUES (?, ?, ?)");
    $stmt->execute([$actor_type, $actor_id, $action]);
}

/* ------------------------------------------------------------
 * Book My Care — appointment helpers
 * ------------------------------------------------------------ */
const BMC_TZ          = 'Asia/Kolkata';
const BMC_SLOT_MIN    = 15;                         // minutes per appointment
const BMC_SESSIONS    = [['09:00', '12:00'], ['14:00', '19:00']]; // morning 9 am–12 noon + afternoon 2–7 pm (break 12–2 pm)
const BMC_DAYS_AHEAD  = 14;                         // how far ahead a patient can book
const BMC_ARRIVE_EARLY = 15;                        // "please arrive 15 minutes early"

/** All slot start times (HH:MM) in a working day. */
function bmc_all_slots(): array {
    $out = [];
    foreach (BMC_SESSIONS as [$from, $to]) {
        for ($t = strtotime("2000-01-01 $from"); $t + BMC_SLOT_MIN * 60 <= strtotime("2000-01-01 $to"); $t += BMC_SLOT_MIN * 60) {
            $out[] = date('H:i', $t);
        }
    }
    return $out;
}

/** The hospital is closed on Sundays. */
function bmc_is_open_day(string $date): bool {
    return date('w', strtotime($date)) !== '0';
}

/** Dates (Y-m-d) a patient may book: today .. +BMC_DAYS_AHEAD, Sundays skipped. */
function bmc_bookable_dates(): array {
    $tz = new DateTimeZone(BMC_TZ);
    $d = new DateTime('today', $tz);
    $out = [];
    for ($i = 0; $i <= BMC_DAYS_AHEAD; $i++) {
        if ($d->format('w') !== '0') $out[] = $d->format('Y-m-d');
        $d->modify('+1 day');
    }
    return $out;
}

/** Free slots (HH:MM) for a doctor on a date; past times are dropped for today. */
function bmc_free_slots(PDO $pdo, int $doctorId, string $date): array {
    $st = $pdo->prepare("SELECT TIME_FORMAT(appointment_time,'%H:%i') FROM appointments
                         WHERE doctor_id = ? AND appointment_date = ? AND status = 'booked'");
    $st->execute([$doctorId, $date]);
    $taken = $st->fetchAll(PDO::FETCH_COLUMN);

    $now = new DateTime('now', new DateTimeZone(BMC_TZ));
    $isToday = $date === $now->format('Y-m-d');
    $nowHm = $now->format('H:i');

    return array_values(array_filter(bmc_all_slots(), function ($s) use ($taken, $isToday, $nowHm) {
        return !in_array($s, $taken, true) && (!$isToday || $s > $nowHm);
    }));
}

/** Unique appointment code e.g. BMC-20261012-0003 */
function generate_appointment_code(PDO $pdo, string $date): string {
    $st = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ?");
    $st->execute([$date]);
    return sprintf('BMC-%s-%04d', str_replace('-', '', $date), (int)$st->fetchColumn() + 1);
}
