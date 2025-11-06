<?php
// scripts/criar_utilizador_inicial.php
// Objetivo: criar um utilizador inicial (admin) de forma simples.
// Uso: acede a esta página no browser, preenche o formulário e submete.
// IMPORTANTE: apaga este ficheiro após criar o utilizador!

session_start();

// Gera token CSRF
if (empty($_SESSION['csrf_setup'])) {
    $_SESSION['csrf_setup'] = bin2hex(random_bytes(32));
}

require_once '../config/conexao.php';

// Função simples para mensagens
function flash($msg, $tipo = 'info') {
    $_SESSION['flash'][] = ['msg' => $msg, 'tipo' => $tipo];
}
if (!isset($_SESSION['flash'])) $_SESSION['flash'] = [];

// Processamento do POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificação CSRF
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf_setup'], $_POST['csrf'])) {
        flash('Sessão expirada. Tenta novamente.', 'erro');
        header('Location: criar_utilizador_inicial.php');
        exit;
    }

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pw = $_POST['pw'] ?? '';
    $pw2 = $_POST['pw2'] ?? '';
    $ativo = isset($_POST['ativo']) ? 1 : 0;

    // Validações básicas
    if ($nome === '' || $email === '' || $pw === '' || $pw2 === '') {
        flash('Preenche todos os campos.', 'erro');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('Email inválido.', 'erro');
    } elseif ($pw !== $pw2) {
        flash('As palavras-passe não coincidem.', 'erro');
    } elseif (strlen($pw) < 10 || !preg_match('/[A-Z]/', $pw) || !preg_match('/[a-z]/', $pw) || !preg_match('/\d/', $pw) || !preg_match('/[^A-Za-z0-9]/', $pw)) {
        flash('A password deve ter pelo menos 10 caracteres, incluindo maiúsculas, minúsculas, números e símbolos.', 'erro');
    } else {
        try {
            // Verificar se já existe utilizador com este email (mesmo que apagado logicamente mantém-se reservado)
            $sql = "SELECT id, apagado_em FROM utilizadores WHERE email = :email LIMIT 1";
            $stm = $ligacao->prepare($sql);
            $stm->execute([':email' => $email]);
            $existe = $stm->fetch();

            if ($existe) {
                if ($existe['apagado_em'] !== null) {
                    flash('Já existe um registo com este email (mesmo apagado logicamente). Escolhe outro email ou pede ao professor para o restaurar.', 'erro');
                } else {
                    flash('Já existe um utilizador ativo com este email.', 'erro');
                }
            } else {
                // Criar utilizador
                $hash = password_hash($pw, PASSWORD_DEFAULT);

                $ins = $ligacao->prepare(
                    "INSERT INTO utilizadores (nome, email, palavra_passe_hash, telemovel, ativo, apagado_em, apagado_por)
                     VALUES (:nome, :email, :hash, NULL, :ativo, NULL, NULL)"
                );
                $ins->execute([
                    ':nome' => $nome,
                    ':email' => $email,
                    ':hash' => $hash,
                    ':ativo' => $ativo
                ]);

                flash('Utilizador criado com sucesso. Já podes fazer login.', 'sucesso');
                // Sugestão: apaga este ficheiro scripts/criar_utilizador_inicial.php
            }
        } catch (Throwable $e) {
            // Em desenvolvimento podes descomentar para ver o erro:
            // flash('Erro: ' . $e->getMessage(), 'erro');
            flash('Ocorreu um erro ao criar o utilizador.', 'erro');
        }
    }

    header('Location: criar_utilizador_inicial.php');
    exit;
}

// Apenas para conveniência: contar utilizadores ativos
$totalAtivos = 0;
try {
    $q = $ligacao->query("SELECT COUNT(*) AS t FROM utilizadores WHERE apagado_em IS NULL AND ativo = 1");
    $totalAtivos = (int)($q->fetch()['t'] ?? 0);
} catch (Throwable $e) {
    // ignorar
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Criar Utilizador Inicial</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <style>
        body { font-family: Arial, sans-serif; background:#f7f7f9; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
        .caixa { background:#fff; width:420px; border:1px solid #e6e6e6; border-radius:12px; padding:22px; box-shadow:0 8px 20px rgba(0,0,0,.06); }
        h1 { margin:0 0 12px; font-size:20px; color:#333; }
        .flash { margin-bottom:10px; padding:10px; border-radius:8px; font-size:14px; }
        .flash.info { background:#eef5ff; border:1px solid #cfe2ff; color:#0b5ed7; }
        .flash.sucesso { background:#eaf7ea; border:1px solid #c7e7c7; color:#1f7a1f; }
        .flash.erro { background:#ffe9e9; border:1px solid #f3c2c2; color:#8b0000; }
        label { display:block; margin:10px 0 6px; font-size:14px; color:#444; }
        input[type="text"], input[type="email"], input[type="password"] { width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; font-size:14px; }
        .acao { margin-top:16px; display:flex; gap:10px; }
        button { flex:1; padding:10px; border:none; border-radius:8px; font-size:15px; cursor:pointer; background:#303030; color:#fff; }
        .nota { margin-top:12px; font-size:12px; color:#666; }
        .badge { display:inline-block; background:#eee; border-radius:20px; padding:4px 10px; font-size:12px; margin-left:6px; }
    </style>
</head>
<body>
<div class="caixa">
    <h1>Criar Utilizador Inicial
        <span class="badge">Ativos: <?= (int)$totalAtivos ?></span>
    </h1>

    <?php foreach ($_SESSION['flash'] as $f): ?>
        <div class="flash <?= htmlspecialchars($f['tipo']) ?>"><?= htmlspecialchars($f['msg']) ?></div>
    <?php endforeach; $_SESSION['flash'] = []; ?>

    <form method="post" action="">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_setup']) ?>">

        <label for="nome">Nome completo</label>
        <input type="text" id="nome" name="nome" required placeholder="Ex.: Super Admin">

        <label for="email">Email (login)</label>
        <input type="email" id="email" name="email" required placeholder="admin@clinica-exemplo.pt">

        <label for="pw">Palavra-passe</label>
        <input type="password" id="pw" name="pw" required placeholder="mín. 10 caracteres">

        <label for="pw2">Confirmar palavra-passe</label>
        <input type="password" id="pw2" name="pw2" required>

        <label><input type="checkbox" name="ativo" checked> Conta ativa</label>

        <div class="acao">
            <button type="submit">Criar utilizador</button>
        </div>
    </form>

    <p class="nota">
        Após criar o utilizador, <strong>apaga este ficheiro</strong> (por segurança):<br>
        <code>/clinica_vet/scripts/criar_utilizador_inicial.php</code>
    </p>

    <p class="nota">
        Depois, faz login em: <code>/clinica_vet/public/index.php</code>
    </p>
</div>
</body>
</html>