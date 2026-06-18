<?php
/**
 * Conexão única com o SQLite (PDO).
 * Inclua este arquivo em qualquer página: require_once __DIR__ . '/lib/db.php';
 */

const DB_PATH = __DIR__ . '/../db/app.sqlite';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        // ATENÇÃO (demo): exibir erros do banco facilita a exploração de SQLi.
        // Em produção isto NUNCA deve ficar ligado (vaza estrutura interna).
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $pdo;
}

/** Inicia a sessão de forma idempotente. */
function boot_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/** Retorna o usuário logado (array) ou null. */
function current_user(): ?array
{
    boot_session();
    if (empty($_SESSION['uid'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['uid']]);
    $u = $stmt->fetch();
    return $u ?: null;
}
