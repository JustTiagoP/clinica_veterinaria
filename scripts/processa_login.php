<?php
// scripts/processa_login.php
session_start();

// Verificação CSRF simples
if (!isset($_POST['csrf'], $_SESSION['csrf_login']) || !hash_equals($_SESSION['csrf_login'], $_POST['csrf'])) {
    header('Location: ../public/index.php?erro=csrf');
    exit;
}

require_once '../config/conexao.php';

// Ler e validar inputs básicos
$email = trim($_POST['email'] ?? '');
$pw    = $_POST['pw'] ?? '';

// Pequena validação
if ($email === '' || $pw === '') {
    header('Location: ../public/index.php?erro=credenciais');
    exit;
}

try {
    // Procurar utilizador ativo e não apagado logicamente
    $sql = "SELECT id, nome, email, palavra_passe_hash, ativo, apagado_em
            FROM utilizadores
            WHERE email = :email
            LIMIT 1";

    $stm = $ligacao->prepare($sql);
    $stm->execute([':email' => $email]);
    $user = $stm->fetch();

    // Verificações de estado
    if (!$user || $user['apagado_em'] !== null || (int)$user['ativo'] !== 1) {
        header('Location: ../public/index.php?erro=inativo');
        exit;
    }

    // Verificar password
    if (!password_verify($pw, $user['palavra_passe_hash'])) {
        header('Location: ../public/index.php?erro=credenciais');
        exit;
    }

    // Autenticação OK → criar sessão
    session_regenerate_id(true); // mitiga fixação de sessão

    $_SESSION['utilizador'] = [
        'id'    => (int)$user['id'],
        'nome'  => $user['nome'],
        'email' => $user['email']
    ];

    // Limpa token CSRF de login (opcional)
    unset($_SESSION['csrf_login']);

    // Redireciona para o dashboard
    header('Location: ../public/dashboard.php');
    exit;

} catch (Throwable $e) {
    // Em produção registar em logs; aqui redirecionamos com erro genérico
    header('Location: ../public/index.php?erro=credenciais');
    exit;
}
