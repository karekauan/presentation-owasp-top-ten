<?php
require_once __DIR__ . '/lib/db.php';
boot_session();
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
