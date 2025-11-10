<?php
// public/clientes.php
session_start();
if (!isset($_SESSION['utilizador'])) {
    header('Location: index.php'); exit;
}

require_once '../config/conexao.php';

// CSRF para ações nesta página (apagar/restaurar via POST)
if (empty($_SESSION['csrf_clientes'])) {
    $_SESSION['csrf_clientes'] = bin2hex(random_bytes(32));
}

$u = $_SESSION['utilizador'];

// --- Filtros e paginação ---
$q = trim($_GET['q'] ?? '');
$mostrarApagados = isset($_GET['apagados']) ? 1 : 0;

$pag = max(1, (int)($_GET['p'] ?? 1));
$pp  = 10; // por página
$off = ($pag - 1) * $pp;

// WHERE base
$where = [];
$params = [];

if ($q !== '') {
    $where[] = "(c.nome_cliente LIKE :q OR c.nif LIKE :q OR c.email LIKE :q OR c.telefone LIKE :q OR c.telemovel LIKE :q)";
    $params[':q'] = "%{$q}%";
}

if ($mostrarApagados) {
    // mostrar todos (inclui apagados)
    // nada a acrescentar aqui
} else {
    $where[] = "c.apagado_em IS NULL";
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// total
$sqlTotal = "SELECT COUNT(*) AS t FROM clientes c {$whereSql}";
$stmT = $ligacao->prepare($sqlTotal);
$stmT->execute($params);
$total = (int)($stmT->fetch()['t'] ?? 0);
$tp = max(1, (int)ceil($total / $pp));

// registos
$sql = "SELECT c.id, c.nome_cliente, c.nif, c.email, c.telefone, c.telemovel, c.localidade, c.apagado_em
        FROM clientes c
        {$whereSql}
        ORDER BY c.nome_cliente ASC
        LIMIT :pp OFFSET :off";
$stm = $ligacao->prepare($sql);
foreach ($params as $k => $v) { $stm->bindValue($k, $v, PDO::PARAM_STR); }
$stm->bindValue(':pp',  $pp, PDO::PARAM_INT);
$stm->bindValue(':off', $off, PDO::PARAM_INT);
$stm->execute();
$linhas = $stm->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Clientes • Clínica Veterinária</title>
<link rel="stylesheet" href="../assets/css/estilo.css">
<style>
    body { font-family: Arial, sans-serif; margin:0; background:#f7f7f9; }
    header { background:#303030; color:#fff; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; }
    .wrap { display:flex; }
    nav { width:220px; background:#fff; border-right:1px solid #eee; min-height:calc(100vh - 56px); }
    nav a { display:block; padding:12px 14px; color:#333; text-decoration:none; border-bottom:1px solid #f3f3f3; }
    nav a:hover { background:#FD9308; color:#fff; }
    main { flex:1; padding:20px; }
    .topo { display:flex; gap:10px; align-items:center; margin-bottom:14px; flex-wrap:wrap; }
    .topo form { display:flex; gap:10px; align-items:center; }
    input[type="text"] { padding:8px; border:1px solid #ccc; border-radius:8px; min-width:260px; }
    button, .btn { padding:8px 12px; border:none; border-radius:8px; background:#303030; color:#fff; text-decoration:none; cursor:pointer; }
    .btn-sec { background:#7a7a7a; }
    .btn-warn { background:#c0392b; }
    .btn-plain { background:#d0d0d0; color:#222; }
    table { border-collapse: collapse; width:100%; background:#fff; border:1px solid #eee; }
    th, td { border-bottom:1px solid #eee; padding:10px; text-align:left; }
    th { background:#fafafa; }
    .apagado { color:#a00; font-weight:bold; }
    .paginacao { margin-top:12px; display:flex; gap:6px; flex-wrap:wrap; }
    .paginacao a, .paginacao span { padding:6px 10px; border:1px solid #ddd; border-radius:6px; text-decoration:none; color:#333; background:#fff; }
    .paginacao .atual { background:#303030; color:#fff; border-color:#303030; }
    .tag { font-size:12px; background:#eee; border-radius:999px; padding:3px 8px; }
</style>
</head>
<body>

<header>
    <div><strong>Clínica Veterinária</strong> — Clientes</div>
    <div>
        Olá, <strong><?= htmlspecialchars($u['nome']); ?></strong>
        &nbsp;|&nbsp;
        <a class="btn-plain" href="dashboard.php">Dashboard</a>
        &nbsp;|&nbsp;
        <a class="btn-sec" href="logout.php">Terminar sessão</a>
    </div>
</header>

<div class="wrap">
    <nav>
        <a href="dashboard.php">Início</a>
        <a href="clientes.php"><strong>Clientes</strong> <span class="tag"><?= $total ?></span></a>
        <a href="animais.php">Animais</a>
        <a href="consultas.php">Consultas</a>
        <a href="faturacao.php">Faturação</a>
        <a href="stock.php">Produtos & Stock</a>
    </nav>

    <main>
        <div class="topo">
            <a class="btn" href="cliente_form.php">+ Novo cliente</a>

            <form method="get" action="">
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Pesquisar por nome, NIF, email ou telefone">
                <label><input type="checkbox" name="apagados" value="1" <?= $mostrarApagados ? 'checked' : '' ?>> Mostrar removidos</label>
                <button type="submit">Procurar</button>
                <?php if ($q !== '' || $mostrarApagados): ?>
                    <a class="btn-plain" href="clientes.php">Limpar</a>
                <?php endif; ?>
            </form>
        </div>

        <table>
            <tr>
                <th>Nome</th>
                <th>NIF</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Telemóvel</th>
                <th>Localidade</th>
                <th style="width:220px;">Ações</th>
            </tr>
            <?php if (!$linhas): ?>
                <tr><td colspan="7"><em>Sem registos.</em></td></tr>
            <?php else: foreach ($linhas as $r): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($r['nome_cliente']) ?>
                        <?php if ($r['apagado_em'] !== null): ?>
                            <span class="apagado">(removido)</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['nif']) ?></td>
                    <td><?= htmlspecialchars($r['email']) ?></td>
                    <td><?= htmlspecialchars($r['telefone']) ?></td>
                    <td><?= htmlspecialchars($r['telemovel']) ?></td>
                    <td><?= htmlspecialchars($r['localidade']) ?></td>
                    <td>
                        <a class="btn-sec" href="cliente_form.php?id=<?= (int)$r['id'] ?>">Editar</a>

                        <?php if ($r['apagado_em'] === null): ?>
                            <form style="display:inline" method="post" action="../scripts/eliminar_cliente.php" onsubmit="return confirm('Remover este cliente? Poderá ser restaurado mais tarde.');">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_clientes']) ?>">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button class="btn-warn" type="submit">Remover</button>
                            </form>
                        <?php else: ?>
                            <form style="display:inline" method="post" action="../scripts/restaurar_cliente.php" onsubmit="return confirm('Restaurar este cliente?');">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_clientes']) ?>">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button class="btn" type="submit">Restaurar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </table>

        <?php if ($tp > 1): ?>
            <div class="paginacao">
                <?php for ($i=1; $i<=$tp; $i++): 
                    $qs = http_build_query(array_filter(['q'=>$q ?: null, 'apagados'=>$mostrarApagados ?: null, 'p'=>$i]));
                    $cls = $i === $pag ? 'atual' : '';
                ?>
                    <a class="<?= $cls ?>" href="?<?= $qs ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
