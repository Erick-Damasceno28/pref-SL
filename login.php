<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($login && $senha) {
        $pdo  = getDB();
        $stmt = $pdo->prepare('SELECT id, nome, senha FROM usuarios WHERE login = ? LIMIT 1');
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id']   = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            header('Location: ' . BASE_URL . '/pages/dashboard.php');
            exit;
        }
        $erro = 'Login ou senha incorretos. Tente novamente.';
    } else {
        $erro = 'Preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Cadastro Imobiliário · São Leopoldo</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="login-page">
  <div class="login-card">

    <div class="login-logo"><i class="bi bi-buildings-fill"></i></div>
    <h1 class="login-title">Cadastro Imobiliário</h1>
    <p class="login-sub">Prefeitura Municipal de São Leopoldo<br>Secretaria da Fazenda — IPTU</p>

    <?php if ($erro): ?>
    <div class="alert alert-error">
      <i class="bi bi-exclamation-triangle-fill"></i>
      <span><?= htmlspecialchars($erro) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="form-group mb-2">
        <label for="login"><i class="bi bi-person me-1"></i>Usuário</label>
        <input type="text" id="login" name="login" class="form-control"
               placeholder="Digite seu login" autocomplete="username"
               value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required>
      </div>

      <div class="form-group mb-2">
        <label for="senha"><i class="bi bi-lock me-1"></i>Senha</label>
        <div style="position:relative">
          <input type="password" id="senha" name="senha" class="form-control"
                 placeholder="Digite sua senha" autocomplete="current-password"
                 style="padding-right:2.5rem" required>
          <button type="button" id="toggle-senha"
                  style="position:absolute;right:.6rem;top:50%;transform:translateY(-50%);
                         background:none;border:none;cursor:pointer;color:var(--muted);font-size:.95rem">
            <i class="bi bi-eye" id="eye-icon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100"
              style="justify-content:center;padding:.55rem;font-size:.9rem;margin-top:.5rem">
        <i class="bi bi-box-arrow-in-right"></i> Entrar no Sistema
      </button>
    </form>

    <p class="login-hint">Acesso restrito a servidores autorizados</p>
  </div>
</div>

<script>
document.getElementById('toggle-senha').addEventListener('click', function () {
  const inp  = document.getElementById('senha');
  const icon = document.getElementById('eye-icon');
  if (inp.type === 'password') { inp.type = 'text';     icon.className = 'bi bi-eye-slash'; }
  else                         { inp.type = 'password'; icon.className = 'bi bi-eye'; }
});
</script>
</body>
</html>
