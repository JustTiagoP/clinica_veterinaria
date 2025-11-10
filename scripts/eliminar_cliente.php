<?php
// scripts/eliminar_cliente.php
session_start();
if (!isset($_SESSION['utilizador'])) { header('Location: ../public/index.php'); exit; }

// CSRF
if (!isset($_POST['csrf'], $_SESSION['csrf_clientes']) || !hash_equals($_SESSION['csrf_clientes'], $_POST['csrf'])) {
    header('Location: ../public/clientes.php'); exit;
}

require_once '../config/conexao.php';

$id = (int)($_POST['id'] ?? 0);
$uid = (int)$_SESSION['utilizador']['id'];

if ($id <= 0) { header('Location: ../public/clientes.php'); exit; }

try {
    $sql = "UPDATE clientes
            SET apagado_em = NOW(), apagado_por = :uid
            WHERE id = :id AND apagado_em IS NULL";
    $stm = $ligacao->prepare($sql);
    $stm->execute([':uid'=>$uid, ':id'=>$id]);
} catch (Throwable $e) {
    // error_log($e->getMessage());
}

header('Location: ../public/clientes.php');
exit;
