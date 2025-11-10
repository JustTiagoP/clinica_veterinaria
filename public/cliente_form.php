<?php
// public/cliente_form.php
session_start();
if (!isset($_SESSION['utilizador'])) { header('Location: index.php'); exit; }
require_once '../config/conexao.php';

// CSRF para guardar
if (empty($_SESSION['csrf_cliente_form'])) {
    $_SESSION['csrf_cliente_form'] = bin2hex(random_bytes(32));
}

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$edit = $id > 0;

$dados = [
    'nome_cliente' => '',
    'nif' => '',
    'email' => '',
    'telefone' => '',
    'telemovel' => '',
    'morada_cliente' => '',
    'codigo_postal' => '',
    'localidade' => ''
];

if ($edit) {
    $sql = "SELECT id, nome_cliente, nif, email, telefone, telemovel, morada_cliente, codigo_postal, localidade, apagado_em
            FROM clientes
            WHERE id = :id
            LIMIT 1";
    $stm = $ligacao->prepare($sql);
    $stm->execute([':id'=>$id]);
    $row = $stm->fetch();

    if (!$row) { header('Location: clientes.php'); exit; }
    $dados = $row;
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title><?= $edit ? 'Editar' : 'Novo' ?> cliente • Clínica Veterinária</title>
<link rel="stylesheet" href="../assets/css/estilo.css">
<style>
    body { font-family: Arial, sans-serif; background:#f7f7f9; margin:0; }
    header { background:#303030; color:#fff; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; }
    main { max-width:900px; margin:20px auto; background:#fff; border:1px solid #eee; border-radius:10px; padding:18px; }
    .linha { display:flex; gap:14px; margin-bottom:12px; }
    .linha .col { flex:1; }
    label { display:block; font-size:13px; margin-bottom:6px; color:#333; }
    input[type="text"], input[type="email"] { width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; }
    textarea { width:100%; padding:10px; border:1px solid #ccc; border-radius:8px; min-height:80px; }
    .acoes { display:flex; gap:10px; margin-top:16px; }
    .btn { padding:10px 14px; border:none; border-radius:8px; background:#303030; color:#fff; text-decoration:none; cursor:pointer; }
    .btn-sec { background:#7a7a7a; }
</style>
</head>
<body>

<header>
    <div><strong>Clínica Veterinária</strong> — <?= $edit ? 'Editar' : 'Novo' ?> cliente</div>
    <div><a class="btn-sec" href="clientes.php">Voltar</a></div>
</header>

<main>
    <?php if ($edit && $dados['apagado_em'] !== null): ?>
        <p style="color:#a00"><strong>Atenção:</strong> este cliente está <em>removido</em>. Precisas de o restaurar antes de editar.</p>
    <?php endif; ?>

    <form method="post" action="../scripts/guardar_cliente.php">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_cliente_form']) ?>">
        <input type="hidden" name="id" value="<?= (int)$id ?>">

        <div class="linha">
            <div class="col">
                <label for="nome_cliente">Nome <span style="color:#a00">*</span></label>
                <input type="text" id="nome_cliente" name="nome_cliente" required
                       value="<?= htmlspecialchars($dados['nome_cliente']) ?>">
            </div>
            <div class="col">
                <label for="nif">NIF</label>
                <input type="text" id="nif" name="nif" value="<?= htmlspecialchars($dados['nif']) ?>">
            </div>
        </div>

        <div class="linha">
            <div class="col">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($dados['email']) ?>">
            </div>
            <div class="col">
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($dados['telefone']) ?>">
            </div>
            <div class="col">
                <label for="telemovel">Telemóvel</label>
                <input type="text" id="telemovel" name="telemovel" value="<?= htmlspecialchars($dados['telemovel']) ?>">
            </div>
        </div>

        <div class="linha">
            <div class="col">
                <label for="morada_cliente">Morada</label>
                <textarea id="morada_cliente" name="morada_cliente"><?= htmlspecialchars($dados['morada_cliente']) ?></textarea>
            </div>
        </div>

        <div class="linha">
            <div class="col">
                <label for="codigo_postal">Código Postal</label>
                <input type="text" id="codigo_postal" name="codigo_postal" value="<?= htmlspecialchars($dados['codigo_postal']) ?>">
            </div>
            <div class="col">
                <label for="localidade">Localidade</label>
                <input type="text" id="localidade" name="localidade" value="<?= htmlspecialchars($dados['localidade']) ?>">
            </div>
        </div>

        <div class="acoes">
            <button class="btn" type="submit"><?= $edit ? 'Guardar alterações' : 'Criar cliente' ?></button>
            <a class="btn-sec" href="clientes.php">Cancelar</a>
        </div>
    </form>
</main>

</body>
</html>
