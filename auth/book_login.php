<?php
// Book My Care no longer needs a login - this old URL just opens the portal.
require_once __DIR__ . '/../config/config.php';
redirect(BASE_URL . '/book/index.php');
