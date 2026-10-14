<?php
require_once __DIR__ . '/../config/config.php';
require_user();

// Monthly report downloads are an administrator-only feature.
flash('error', 'Monthly report download is available to administrators only.');
redirect(BASE_URL . '/user/reports.php');
