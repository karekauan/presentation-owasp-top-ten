<?php
/**
 * A05:2025 — Injection (SQL Injection)
 *
 * O termo de busca é concatenado direto na SQL, sem prepared statement.
 * A query tem 3 colunas (id, name, price) -> permite UNION para extrair
 * outras tabelas (ex.: hashes de senha de users).
 *
 * Correção: usar prepared statements / parâmetros vinculados:
 *   $stmt = db()->prepare('... WHERE name LIKE ?'); $stmt->execute(["%$q%"]);
 */
require_once __DIR__ . '/lib/layout.php';

$q = $_GET['q'] ?? '';
$rows = [];
$error = null;
$sql = null;

if (isset($_GET['q'])) {
    // VULNERÁVEL: concatenação direta da entrada do usuário.
    $sql = "SELECT id, name, price FROM products WHERE name LIKE '%$q%'";
    try {
        $rows = db()->query($sql)->fetchAll();
    } catch (PDOException $e) {
        // Erro verboso de propósito: ajuda o atacante a ajustar o payload.
        $error = $e->getMessage();
    }
}

render_header('Buscar produtos');
vuln_banner('A05:2025', 'Injection (SQLi)', 'o termo de busca entra na SQL por concatenação.');
?>
<form method="get" class="row g-2 mb-3">
  <div class="col-auto" style="flex:1">
    <input name="q" class="form-control" value="<?= htmlspecialchars($q) ?>"
           placeholder="ex.: mouse   |   ataque: %' UNION SELECT id,username,password_md5 FROM users -- ">
  </div>
  <div class="col-auto"><button class="btn btn-primary">Buscar</button></div>
</form>

<?php if ($sql !== null): ?>
  <p class="text-muted">SQL executada:</p>
  <pre><?= htmlspecialchars($sql) ?></pre>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger"><strong>Erro SQL:</strong> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($rows): ?>
  <table class="table table-striped">
    <thead><tr><th>Coluna 1 (id)</th><th>Coluna 2 (name)</th><th>Coluna 3 (price)</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars((string)$r['id']) ?></td>
          <td><?= htmlspecialchars((string)$r['name']) ?></td>
          <td><?= htmlspecialchars((string)$r['price']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php elseif (isset($_GET['q']) && !$error): ?>
  <div class="alert alert-secondary">Nenhum resultado.</div>
<?php endif; ?>

<div class="card card-body mt-3">
  <h6>Payloads para testar ao vivo</h6>
  <ul class="mb-0">
    <li><code>mouse</code> — busca normal</li>
    <li><code>' OR '1'='1</code> — retorna todos os produtos (bypass do filtro)</li>
    <li><code>%' UNION SELECT id,username,password_md5 FROM users -- </code> — extrai os hashes de senha</li>
    <li><code>%' UNION SELECT id,username,cpf FROM users -- </code> — extrai CPFs</li>
  </ul>
</div>
<?php render_footer(); ?>
