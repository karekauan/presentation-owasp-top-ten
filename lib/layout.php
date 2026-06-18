<?php
/**
 * Cabeçalho/rodapé compartilhados. Uso:
 *   render_header('Título'); ... html ...; render_footer();
 */
require_once __DIR__ . '/db.php';

function render_header(string $title): void
{
    $u = current_user();
    $userLabel = $u ? htmlspecialchars($u['username']) . ($u['role'] === 'admin' ? ' (admin)' : '') : null;
    ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> — DemoBank (INSEGURO)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { padding-bottom: 4rem; }
        .vuln-badge { font-family: monospace; }
        pre { background:#0d1117; color:#c9d1d9; padding:1rem; border-radius:.5rem; overflow:auto; }
        .danger-banner { background:#7f1d1d; color:#fff; text-align:center; padding:.35rem; font-size:.85rem; }
    </style>
</head>
<body>
<div class="danger-banner">⚠️ APLICAÇÃO PROPOSITALMENTE VULNERÁVEL — uso didático, rode apenas localmente/offline</div>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">🏦 DemoBank</a>
    <div class="navbar-nav me-auto">
      <a class="nav-link" href="search.php">Buscar produtos</a>
      <?php if ($u): ?>
        <a class="nav-link" href="dashboard.php">Painel</a>
        <a class="nav-link" href="profile.php?id=<?= (int)$u['id'] ?>">Meu perfil</a>
        <a class="nav-link" href="admin.php">Admin</a>
      <?php endif; ?>
    </div>
    <div class="navbar-nav">
      <?php if ($u): ?>
        <span class="navbar-text me-3">👤 <?= $userLabel ?></span>
        <a class="nav-link" href="logout.php">Sair</a>
      <?php else: ?>
        <a class="nav-link" href="index.php">Entrar</a>
        <a class="nav-link" href="register.php">Cadastrar</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main class="container mt-4">
    <?php
}

function render_footer(): void
{
    ?>
</main>
<footer class="container text-muted mt-5">
  <hr>
  <small>DemoBank — projeto educacional OWASP Top 10:2025. Cada página indica a categoria de risco demonstrada.</small>
</footer>
</body>
</html>
    <?php
}

/** Mostra um aviso de qual vulnerabilidade a página demonstra. */
function vuln_banner(string $code, string $name, string $desc): void
{
    ?>
    <div class="alert alert-warning">
      <span class="badge bg-danger vuln-badge"><?= htmlspecialchars($code) ?></span>
      <strong><?= htmlspecialchars($name) ?></strong> — <?= htmlspecialchars($desc) ?>
    </div>
    <?php
}
