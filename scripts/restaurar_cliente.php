<?php
// scripts/restaurar_cliente.php
session_start();
if (!isset($_SESSION['utilizador'])) { header('Location: ../public/index.php'); exit; }

// CSRF
if (!isset($_POST['csrf'], $_SESSION['csrf_clientes']) || !hash_equals($_SESSION['csrf_clientes'], $_POST['csrf'])) {
    header('Location: ../public/clientes.php'); exit;
}

require_once '../config/conexao.php';

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { header('Location: ../public/clientes.php'); exit; }

try {
    $sql = "UPDATE clientes
            SET apagado_em = NULL, apagado_por = NULL
            WHERE id = :id AND apagado_em IS NOT NULL";
    $stm = $ligacao->prepare($sql);
    $stm->execute([':id'=>$id]);
} catch (Throwable $e) {
    // error_log($e->getMessage());
}

header('Location: ../public/clientes.php?apagados=1');
exit;
