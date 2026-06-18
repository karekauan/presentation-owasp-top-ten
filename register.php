<?php
/**
 * A07:2025 — Authentication Failures (sem política de senha)
 * A04:2025 — Cryptographic Failures (salva MD5 sem salt)
 *
 * Aceita qualquer senha, inclusive "1" -> nenhuma exigência de força.
 */
require_once __DIR__ . '/lib/layout.php';
boot_session();

$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $msg = ['danger', 'Preencha usuário e senha.'];
    } else {
        try {
            // Sem validação de força de senha (de propósito).
            $stmt = db()->prepare('INSERT INTO users (username, password_md5, email, cpf, saldo, role)
                                   VALUES (?, ?, ?, ?, ?, "user")');
            $stmt->execute([$username, md5($password), $username . '@demobank.test', '000.000.000-00', 0.0]);
            $msg = ['success', 'Conta criada! Senha aceita sem nenhuma exigência de força (A07).'];
        } catch (PDOException $e) {
            $msg = ['danger', 'Erro: ' . $e->getMessage()];
        }
    }
}

render_header('Cadastro');
vuln_banner('A07:2025', 'Authentication Failures', 'aceita senhas fracas e guarda como MD5 sem salt (A04).');
?>
<?php if ($msg): ?>
  <div class="alert alert-<?= $msg[0] ?>"><?= htmlspecialchars($msg[1]) ?></div>
<?php endif; ?>
<form method="post" class="card card-body" style="max-width:420px">
  <div class="mb-3">
    <label class="form-label">Usuário</label>
    <input name="username" class="form-control" autofocus>
  </div>
  <div class="mb-3">
    <label class="form-label">Senha (tente "1" — vai aceitar)</label>
    <input name="password" type="text" class="form-control">
  </div>
  <button class="btn btn-primary">Cadastrar</button>
</form>
<?php render_footer(); ?>
