<?php
// public/index.php
session_start();

// Gera token CSRF simples para o formulário (proteção básica)
if (empty($_SESSION['csrf_login'])) {
    $_SESSION['csrf_login'] = bin2hex(random_bytes(32));
}

$erro = $_GET['erro'] ?? null; // permite mostrar mensagens vindas do processa_login
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login • Clínica Veterinária</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <style>
        body { font-family: Arial, sans-serif; background:#f5f5f7; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
        .caixa { background:#fff; width:340px; border:1px solid #e6e6e6; border-radius:10px; padding:24px; box-shadow:0 6px 18px rgba(0,0,0,.06); }
        h1 { margin:0 0 14px; font-size:20px; color:#333; }
        label { display:block; margin:10px 0 6px; font-size:14px; color:#444; }
        input[type="email"], input[type="password"] { width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px; }
        .acao { margin-top:16px; }
        button { width:100%; padding:10px; border:none; border-radius:8px; font-size:15px; cursor:pointer; background:#303030; color:#fff; }
        .erro { background:#ffe9e9; color:#8b0000; border:1px solid #f3c2c2; padding:10px; border-radius:8px; margin-bottom:12px; font-size:13px; }
        .nota { margin-top:12px; font-size:12px; color:#666; }
    </style>
</head>
<body>
<div class="caixa">
    <h1>Iniciar sessão</h1>

    <?php if ($erro === 'credenciais'): ?>
        <div class="erro">Credenciais inválidas. Verifique o email e a palavra-passe.</div>
    <?php elseif ($erro === 'inativo'): ?>
        <div class="erro">A sua conta está inativa ou removida.</div>
    <?php elseif ($erro === 'csrf'): ?>
        <div class="erro">Sessão expirada. Por favor tente novamente.</div>
    <?php endif; ?>

    <form method="post" action="../scripts/processa_login.php" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_login']) ?>">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus>

        <label for="pw">Palavra-passe</label>
        <input type="password" id="pw" name="pw" required>

        <div class="acao">
            <button type="submit">Entrar</button>
        </div>
    </form>

    <p class="nota">
        <strong>Dica (apenas para testes iniciais):</strong><br>
        Email: <code>mendes@eprin.net</code><br>
        Password: <code>gde</code>
    </p>
</div>
</body>
</html>
