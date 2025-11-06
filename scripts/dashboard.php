<?php
// public/dashboard.php
session_start();
if (!isset($_SESSION['utilizador'])) {
    header('Location: index.php');
    exit;
}

$u = $_SESSION['utilizador'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dashboard • Clínica Veterinária</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <style>
        body { font-family: Arial, sans-serif; margin:0; background:#f7f7f9; }
        header { background:#303030; color:#fff; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; }
        .wrap { display:flex; }
        nav { width:220px; background:#fff; border-right:1px solid #eee; min-height:calc(100vh - 56px); }
        nav a { display:block; padding:12px 14px; color:#333; text-decoration:none; border-bottom:1px solid #f3f3f3; }
        nav a:hover { background:#FD9308; color:#fff; }
        main { flex:1; padding:20px; }
        .bemvindo { background:#fff; border:1px solid #eee; border-radius:10px; padding:16px; }
        .logout { color:#fff; text-decoration:none; background:#7a7a7a; padding:8px 12px; border-radius:8px; }
        .logout:hover { background:#9a9a9a; }
    </style>
</head>
<body>

<header>
    <div><strong>Clínica Veterinária</strong> — Dashboard</div>
    <div>
        Olá, <strong><?= htmlspecialchars($u['nome']); ?></strong>
        &nbsp;|&nbsp;
        <a class="logout" href="logout.php">Terminar sessão</a>
    </div>
</header>

<div class="wrap">
    <nav>
        <!-- Menu simples (ligarão às páginas que vamos criar) -->
        <a href="dashboard.php">Início</a>
        <a href="clientes.php">Clientes</a>
        <a href="animais.php">Animais</a>
        <a href="consultas.php">Consultas</a>
        <a href="faturacao.php">Faturação</a>
        <a href="stock.php">Produtos & Stock</a>
    </nav>
    <main>
        <div class="bemvindo">
            <h2>Bem-vindo(a) ao painel</h2>
            <p>Use o menu à esquerda para aceder aos módulos. Nas aulas seguintes vamos criar as grelhas (listagens) e os formulários (CRUD) com <em>soft delete</em>.</p>
            <ul>
                <li>Listagens paginadas</li>
                <li>Criação/edição de registos</li>
                <li>Eliminação lógica (soft delete) e restauro</li>
                <li>Validação básica de formulários</li>
            </ul>
        </div>
    </main>
</div>

</body>
</html>
