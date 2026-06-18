<?php
/**
 * A01:2025 — Broken Access Control (falta de controle em nível de função)
 *
 * Qualquer usuário logado abre o painel admin via "forced browsing".
 * NÃO há checagem de papel (role). A página só deveria abrir para admins.
 *
 * Correção: if ($me['role'] !== 'admin') { http_response_code(403); exit; }
 */
require_once __DIR__ . '/lib/layout.php';
$me = current_user();
if (!$me) {
    header('Location: index.php');
    exit;
}

// VULNERÁVEL: deveria existir uma checagem de role aqui — e não existe.

$users = db()->query('SELECT id, username, email, cpf, saldo, role, password_md5 FROM users')->fetchAll();

render_header('Admin');
vuln_banner('A01:2025', 'Broken Access Control (função)', 'painel admin abre sem checar o papel do usuário.');
?>
<?php if ($me['role'] !== 'admin'): ?>
  <div class="alert alert-danger">⚠️ Você é <strong><?= htmlspecialchars($me['username']) ?></strong> (role=user),
      mas mesmo assim acessou o painel de administração.</div>
<?php endif; ?>

<h3>Todos os usuários</h3>
<table class="table table-striped">
  <thead><tr><th>ID</th><th>Usuário</th><th>E-mail</th><th>CPF</th><th>Saldo</th><th>Role</th><th>Hash (MD5)</th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <tr>
      <td><?= (int)$u['id'] ?></td>
      <td><?= htmlspecialchars($u['username']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><?= htmlspecialchars($u['cpf']) ?></td>
      <td>R$ <?= number_format((float)$u['saldo'], 2, ',', '.') ?></td>
      <td><?= htmlspecialchars($u['role']) ?></td>
      <td><code><?= htmlspecialchars($u['password_md5']) ?></code></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php render_footer(); ?>
