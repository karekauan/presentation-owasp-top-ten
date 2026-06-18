<?php
/**
 * A01:2025 — Broken Access Control (IDOR)
 *
 * O perfil é carregado pelo id da URL (?id=) SEM verificar se pertence
 * ao usuário logado. Basta trocar o número para ver CPF, saldo e mensagens
 * privadas de qualquer pessoa.
 *
 * Correção: usar SEMPRE o id da sessão ($_SESSION['uid']), ou checar
 * server-side se o recurso pertence ao usuário (ou se ele é admin).
 */
require_once __DIR__ . '/lib/layout.php';
$me = current_user();
if (!$me) {
    header('Location: index.php');
    exit;
}

// VULNERÁVEL: confia no id vindo do cliente, sem checar propriedade.
$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$me['id'];

$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$victim = $stmt->fetch();

$msgs = [];
if ($victim) {
    $m = db()->prepare('SELECT body FROM messages WHERE user_id = ?');
    $m->execute([$id]);
    $msgs = $m->fetchAll();
}

render_header('Perfil');
vuln_banner('A01:2025', 'Broken Access Control (IDOR)', 'o perfil vem do ?id= da URL, sem checar dono.');

if (!$victim) {
    echo '<div class="alert alert-secondary">Nenhum usuário com id ' . $id . '.</div>';
    render_footer();
    exit;
}

$isMine = ((int)$victim['id'] === (int)$me['id']);
?>
<?php if (!$isMine): ?>
  <div class="alert alert-danger">⚠️ Você está vendo o perfil de <strong><?= htmlspecialchars($victim['username']) ?></strong>,
      que NÃO é você. Isto é o IDOR acontecendo.</div>
<?php endif; ?>

<div class="card card-body">
  <h3><?= htmlspecialchars($victim['username']) ?>
      <?php if ($victim['role'] === 'admin'): ?><span class="badge bg-dark">admin</span><?php endif; ?>
  </h3>
  <table class="table">
    <tr><th>ID</th><td><?= (int)$victim['id'] ?></td></tr>
    <tr><th>E-mail</th><td><?= htmlspecialchars($victim['email']) ?></td></tr>
    <tr><th>CPF</th><td><?= htmlspecialchars($victim['cpf']) ?></td></tr>
    <tr><th>Saldo</th><td>R$ <?= number_format((float)$victim['saldo'], 2, ',', '.') ?></td></tr>
    <tr><th>Hash da senha (MD5)</th><td><code><?= htmlspecialchars($victim['password_md5']) ?></code></td></tr>
  </table>

  <h5>Mensagens privadas</h5>
  <?php if ($msgs): ?>
    <ul class="list-group">
      <?php foreach ($msgs as $row): ?>
        <li class="list-group-item"><?= htmlspecialchars($row['body']) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="text-muted">Sem mensagens.</p>
  <?php endif; ?>
</div>

<p class="mt-3 text-muted">Tente: <code>profile.php?id=1</code>, <code>?id=3</code>, <code>?id=4</code> …</p>
<?php render_footer(); ?>
