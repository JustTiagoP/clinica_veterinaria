<?php
// scripts/api_racas.php
session_start();
if (!isset($_SESSION['utilizador'])) { http_response_code(401); echo '[]'; exit; }

require_once '../config/conexao.php';

$especie_id = isset($_GET['especie_id']) ? (int)$_GET['especie_id'] : 0;

header('Content-Type: application/json; charset=utf-8');

if ($especie_id<=0) { echo json_encode([]); exit; }

$stm = $ligacao->prepare("SELECT id, nome FROM racas WHERE especie_id=:e AND apagado_em IS NULL ORDER BY nome ASC");
$stm->execute([':e'=>$especie_id]);
echo json_encode($stm->fetchAll() ?: []);
