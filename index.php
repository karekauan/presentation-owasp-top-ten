<?php
require_once __DIR__ . '/lib/layout.php';
$u = current_user();
render_header('Início');
?>

<div class="p-4 mb-4 bg-light rounded-3">
  <h1>🏦 DemoBank</h1>
  <p class="lead">App de demonstração para a palestra <strong>OWASP Top 10:2025</strong>.
     Cada página carrega de propósito uma vulnerabilidade clássica.</p>
</div>

<?php if ($u): ?>
  <div class="alert alert-success">Você está logado como <strong><?= htmlspecialchars($u['username']) ?></strong>.
    Vá para o <a href="dashboard.php">painel</a>.</div>
<?php else: ?>
<div class="row">
  <div class="col-md-6">
    <h3>Entrar</h3>
    <?php if (!empty($_GET['erro'])): ?>
      <div class="alert alert-danger">Usuário ou senha inválidos.</div>
    <?php endif; ?>
    <form method="post" action="login.php" class="card card-body">
      <div class="mb-3">
        <label class="form-label">Usuário</label>
        <input name="username" class="form-control" autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Senha</label>
        <input name="password" type="password" class="form-control">
      </div>
      <button class="btn btn-primary">Entrar</button>
    </form>
  </div>
  <div class="col-md-6">
    <h3>Demos disponíveis</h3>
    <ul class="list-group">
      <li class="list-group-item"><span class="badge bg-danger">A01</span> Broken Access Control — <code>profile.php?id=</code> e <code>admin.php</code></li>
      <li class="list-group-item"><span class="badge bg-danger">A04</span> Cryptographic Failures — hashes MD5 no login</li>
      <li class="list-group-item"><span class="badge bg-danger">A05</span> Injection — <a href="search.php">busca de produtos</a></li>
      <li class="list-group-item"><span class="badge bg-danger">A07</span> Authentication Failures — login sem rate limit</li>
    </ul>
    <p class="mt-3 text-muted">Dica: comece pela <a href="search.php">busca</a> (SQLi) para extrair os hashes.</p>
  </div>
</div>
<?php endif; ?>

<?php render_footer(); ?>
