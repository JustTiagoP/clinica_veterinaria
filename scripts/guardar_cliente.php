<?php
// scripts/guardar_cliente.php
session_start();
if (!isset($_SESSION['utilizador'])) { header('Location: ../public/index.php'); exit; }

// CSRF
if (!isset($_POST['csrf'], $_SESSION['csrf_cliente_form']) || !hash_equals($_SESSION['csrf_cliente_form'], $_POST['csrf'])) {
    header('Location: ../public/clientes.php'); exit;
}

require_once '../config/conexao.php';

$id = (int)($_POST['id'] ?? 0);

// Inputs
$nome_cliente   = trim($_POST['nome_cliente'] ?? '');
$nif            = trim($_POST['nif'] ?? '');
$email          = trim($_POST['email'] ?? '');
$telefone       = trim($_POST['telefone'] ?? '');
$telemovel      = trim($_POST['telemovel'] ?? '');
$morada         = trim($_POST['morada_cliente'] ?? '');
$codigo_postal  = trim($_POST['codigo_postal'] ?? '');
$localidade     = trim($_POST['localidade'] ?? '');

// validação mínima
if ($nome_cliente === '') {
    header('Location: ../public/clientes.php'); exit;
}

try {
    // Garantir clinica_id (assumimos a primeira para projeto simples)
    $clinicaId = (int)($ligacao->query("SELECT id FROM clinicas ORDER BY id ASC LIMIT 1")->fetch()['id'] ?? 0);
    if ($clinicaId === 0) { throw new Exception('Clinica não configurada.'); }

    if ($id > 0) {
        // Atualizar: impedir edição se apagado
        $chk = $ligacao->prepare("SELECT apagado_em FROM clientes WHERE id=:id");
        $chk->execute([':id'=>$id]);
        $r = $chk->fetch();
        if (!$r || $r['apagado_em'] !== null) {
            header('Location: ../public/clientes.php'); exit;
        }

        $sql = "UPDATE clientes
                SET nome_cliente=:nome, nif=:nif, email=:email, telefone=:tel, telemovel=:telem,
                    morada_cliente=:morada, codigo_postal=:cp, localidade=:loc
                WHERE id=:id";
        $stm = $ligacao->prepare($sql);
        $stm->execute([
            ':nome'=>$nome_cliente, ':nif'=>$nif, ':email'=>$email, ':tel'=>$telefone, ':telem'=>$telemovel,
            ':morada'=>$morada, ':cp'=>$codigo_postal, ':loc'=>$localidade, ':id'=>$id
        ]);
    } else {
        // Inserir
        $sql = "INSERT INTO clientes
                (clinica_id, nome_cliente, nif, email, telefone, telemovel, morada_cliente, codigo_postal, localidade, apagado_em, apagado_por)
                VALUES
                (:clinica, :nome, :nif, :email, :tel, :telem, :morada, :cp, :loc, NULL, NULL)";
        $stm = $ligacao->prepare($sql);
        $stm->execute([
            ':clinica'=>$clinicaId, ':nome'=>$nome_cliente, ':nif'=>$nif, ':email'=>$email,
            ':tel'=>$telefone, ':telem'=>$telemovel, ':morada'=>$morada, ':cp'=>$codigo_postal, ':loc'=>$localidade
        ]);
    }

} catch (Throwable $e) {
    // Poderias registar em logs
    // error_log($e->getMessage());
}

header('Location: ../public/clientes.php');
exit;
