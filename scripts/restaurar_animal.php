<?php
// scripts/restaurar_animal.php
session_start();
if (!isset($_SESSION['utilizador'])) { header('Location: ../public/index.php'); exit; }

// CSRF
if (!isset($_POST['csrf'], $_SESSION['csrf_animais']) || !hash_equals($_SESSION['csrf_animais'], $_POST['csrf'])) {
    header('Location: ../public/animais.php'); exit;
}

require_once '../config/conexao.php';

$id = (int)($_POST['id'] ?? 0);
if ($id<=0) { header('Location: ../public/animais.php'); exit; }

try {
    $sql = "UPDATE animais SET apagado_em=NULL, apagado_por=NULL WHERE id=:id AND apagado_em IS NOT NULL";
    $stm = $ligacao->prepare($sql);
    $stm->execute([':id'=>$id]);
} catch(Throwable $e) { /* error_log($e->getMessage()); */ }

header('Location: ../public/animais.php?apagados=1');
exit;
