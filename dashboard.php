<?php
require_once __DIR__ . '/lib/layout.php';
$u = current_user();
if (!$u) {
    header('Location: index.php');
    exit;
}
render_header('Painel');
?>
<h2>Olá, <?= htmlspecialchars($u['username']) ?> 👋</h2>
<div class="row mt-3">
  <div class="col-md-4">
    <div class="card card-body">
      <h5>Saldo</h5>
      <p class="display-6">R$ <?= number_format((float)$u['saldo'], 2, ',', '.') ?></p>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card card-body">
      <h5>Atalhos</h5>
      <ul>
        <li><a href="profile.php?id=<?= (int)$u['id'] ?>">Meu perfil</a>
            — tente trocar o <code>id</code> na URL 👀</li>
        <li><a href="admin.php">Painel admin</a>
            — abre mesmo sem ser admin 👀</li>
        <li><a href="search.php">Buscar produtos</a></li>
      </ul>
    </div>
  </div>
</div>
<?php render_footer(); ?>
