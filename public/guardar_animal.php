<?php
// scripts/guardar_animal.php
session_start();
if (!isset($_SESSION['utilizador'])) { header('Location: ../public/index.php'); exit; }

// CSRF
if (!isset($_POST['csrf'], $_SESSION['csrf_animal_form']) || !hash_equals($_SESSION['csrf_animal_form'], $_POST['csrf'])) {
    header('Location: ../public/animais.php'); exit;
}

require_once '../config/conexao.php';

$id = (int)($_POST['id'] ?? 0);

$cliente_id = (int)($_POST['cliente_id'] ?? 0);
$nome       = trim($_POST['nome'] ?? '');
$especie_id = (int)($_POST['especie_id'] ?? 0);
$raca_id    = $_POST['raca_id'] !== '' ? (int)$_POST['raca_id'] : null;
$sexo       = ($_POST['sexo'] ?? 'M');
$data_nasc  = ($_POST['data_nascimento'] ?? null) ?: null;
$microchip  = trim($_POST['microchip'] ?? '');
$cor        = trim($_POST['cor'] ?? '');
$esterilizado = (int)($_POST['esterilizado'] ?? 0);

// validação mínima
if ($cliente_id<=0 || $nome==='' || $especie_id<=0) {
    header('Location: ../public/animais.php'); exit;
}

try {
    // clinica simples (primeira)
    $clinicaId = (int)($ligacao->query("SELECT id FROM clinicas ORDER BY id ASC LIMIT 1")->fetch()['id'] ?? 0);
    if ($clinicaId===0) { throw new Exception('Clinica não configurada.'); }

    if ($id>0) {
        // impedir edição se apagado
        $chk = $ligacao->prepare("SELECT apagado_em FROM animais WHERE id=:id");
        $chk->execute([':id'=>$id]);
        $r = $chk->fetch();
        if (!$r || $r['apagado_em']!==null) { header('Location: ../public/animais.php'); exit; }

        $sql = "UPDATE animais
                SET cliente_id=:cli, clinica_id=:cliid, nome=:nome, especie_id=:esp, raca_id=:raca,
                    sexo=:sexo, data_nascimento=:dn, microchip=:chip, cor=:cor, esterilizado=:est
                WHERE id=:id";
        $stm = $ligacao->prepare($sql);
        $stm->execute([
            ':cli'=>$cliente_id, ':cliid'=>$clinicaId, ':nome'=>$nome, ':esp'=>$especie_id, ':raca'=>$raca_id,
            ':sexo'=>$sexo, ':dn'=>$data_nasc, ':chip'=>$microchip, ':cor'=>$cor, ':est'=>$esterilizado, ':id'=>$id
        ]);
    } else {
        $sql = "INSERT INTO animais
                (cliente_id, clinica_id, nome, especie_id, raca_id, sexo, data_nascimento, microchip, cor, esterilizado, apagado_em, apagado_por)
                VALUES
                (:cli, :cliid, :nome, :esp, :raca, :sexo, :dn, :chip, :cor, :est, NULL, NULL)";
        $stm = $ligacao->prepare($sql);
        $stm->execute([
            ':cli'=>$cliente_id, ':cliid'=>$clinicaId, ':nome'=>$nome, ':esp'=>$especie_id, ':raca'=>$raca_id,
            ':sexo'=>$sexo, ':dn'=>$data_nasc, ':chip'=>$microchip, ':cor'=>$cor, ':est'=>$esterilizado
        ]);
    }
} catch(Throwable $e) {
    // error_log($e->getMessage());
}
header('Location: ../public/animais.php');
exit;
