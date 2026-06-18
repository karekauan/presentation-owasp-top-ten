<?php
/**
 * A07:2025 — Authentication Failures
 * A04:2025 — Cryptographic Failures
 *
 * Problemas de propósito:
 *  - Sem rate limit / lockout / CAPTCHA  -> brute force livre (ver attack/bruteforce.sh)
 *  - Compara hash MD5 sem salt           -> hashes triviais de quebrar
 */
require_once __DIR__ . '/lib/db.php';
boot_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// A04: hash fraco. A07: nenhuma proteção contra tentativas repetidas.
$stmt = db()->prepare('SELECT * FROM users WHERE username = ? AND password_md5 = ?');
$stmt->execute([$username, md5($password)]);
$user = $stmt->fetch();

if ($user) {
    $_SESSION['uid'] = $user['id'];
    header('Location: dashboard.php');
    exit;
}

// (didático) Não há contagem de falhas, atraso, nem bloqueio.
header('Location: index.php?erro=1');
exit;
