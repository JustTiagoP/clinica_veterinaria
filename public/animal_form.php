<?php
// public/animal_form.php
session_start();
if (!isset($_SESSION['utilizador'])) { header('Location: index.php'); exit; }
require_once '../config/conexao.php';

// CSRF do formulário
if (empty($_SESSION['csrf_animal_form'])) {
    $_SESSION['csrf_animal_form'] = bin2hex(random_bytes(32));
}

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$edit = $id > 0;

// Carregar listas base
$clientes = $ligacao->query("SELECT id, nome_cliente FROM clientes WHERE apagado_em IS NULL ORDER BY nome_cliente ASC")->fetchAll();
$especies = $ligacao->query("SELECT id, nome FROM especies WHERE apagado_em IS NULL ORDER BY nome ASC")->fetchAll();

$dados = [
  'cliente_id'=>null,'nome'=>'','especie_id'=>null,'raca_id'=>null,'sexo'=>'M','data_nascimento'=>null,
  'microchip'=>'','cor'=>'','esterilizado'=>0,'apagado_em'=>null
];

if ($edit) {
    $stm = $ligacao->prepare("SELECT * FROM animais WHERE id=:id LIMIT 1");
    $stm->execute([':id'=>$id]);
    $row = $stm->fetch();
    if (!$row) { header('Location: animais.php'); exit; }
    $dados = $row;
}

// Se já tivermos espécie selecionada, carregar raças dessa espécie
$racas = [];
if ($dados['especie_id']) {
    $stmR = $ligacao->prepare("SELECT id, nome FROM racas WHERE especie_id=:e ORDER BY nome ASC");
    $stmR->execute([':e'=>$dados['especie_id']]);
    $racas = $stmR->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title><?= $edit ? 'Editar' : 'Novo' ?> animal • Clínica Veterinária</title>
<link rel="stylesheet" href="../assets/css/estilo.css">
<style>
  body{font-family:Arial,sans-serif;background:#f7f7f9;margin:0}
  header{background:#303030;color:#fff;padding:14px 18px;display:flex;justify-content:space-between}
  main{max-width:920px;margin:20px auto;background:#fff;border:1px solid #eee;border-radius:10px;padding:18px}
  .linha{display:flex;gap:14px;margin-bottom:12px}.col{flex:1}
  label{display:block;font-size:13px;margin-bottom:6px;color:#333}
  input[type="text"],input[type="date"],select{width:100%;padding:10px;border:1px solid #ccc;border-radius:8px}
  .acoes{display:flex;gap:10px;margin-top:16px}
  .btn{padding:10px 14px;border:none;border-radius:8px;background:#303030;color:#fff;text-decoration:none;cursor:pointer}
  .btn-sec{background:#7a7a7a}
  .aviso{color:#a00}
</style>
</head>
<body>
<header>
  <div><strong>Clínica Veterinária</strong> — <?= $edit ? 'Editar' : 'Novo' ?> animal</div>
  <div><a class="btn-sec" href="animais.php">Voltar</a></div>
</header>

<main>
  <?php if ($edit && $dados['apagado_em'] !== null): ?>
    <p class="aviso"><strong>Atenção:</strong> este animal está <em>removido</em>. Restaura antes de editar.</p>
  <?php endif; ?>

  <form method="post" action="../scripts/guardar_animal.php" id="formAnimal">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_animal_form']) ?>">
    <input type="hidden" name="id" value="<?= (int)$id ?>">

    <div class="linha">
      <div class="col">
        <label for="cliente_id">Dono (cliente) <span style="color:#a00">*</span></label>
        <select id="cliente_id" name="cliente_id" required>
          <option value="">— selecione —</option>
          <?php foreach($clientes as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= $dados['cliente_id']==$c['id']?'selected':'' ?>>
              <?= htmlspecialchars($c['nome_cliente']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col">
        <label for="nome">Nome do animal <span style="color:#a00">*</span></label>
        <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($dados['nome']) ?>">
      </div>
      <div class="col">
        <label for="sexo">Sexo</label>
        <select id="sexo" name="sexo">
          <?php foreach(['M'=>'M','F'=>'F','Desconhecido'=>'Desconhecido'] as $k=>$v): ?>
            <option value="<?= $v ?>" <?= $dados['sexo']===$v?'selected':'' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="linha">
      <div class="col">
        <label for="especie_id">Espécie <span style="color:#a00">*</span></label>
        <select id="especie_id" name="especie_id" required>
          <option value="">— selecione —</option>
          <?php foreach($especies as $e): ?>
            <option value="<?= (int)$e['id'] ?>" <?= $dados['especie_id']==$e['id']?'selected':'' ?>>
              <?= htmlspecialchars($e['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col">
        <label for="raca_id">Raça</label>
        <select id="raca_id" name="raca_id">
          <option value="">— selecione —</option>
          <?php foreach($racas as $r): ?>
            <option value="<?= (int)$r['id'] ?>" <?= $dados['raca_id']==$r['id']?'selected':'' ?>>
              <?= htmlspecialchars($r['nome']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col">
        <label for="data_nascimento">Data de nascimento</label>
        <input type="date" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($dados['data_nascimento'] ?? '') ?>">
      </div>
    </div>

    <div class="linha">
      <div class="col">
        <label for="microchip">Microchip</label>
        <input type="text" id="microchip" name="microchip" value="<?= htmlspecialchars($dados['microchip'] ?? '') ?>">
      </div>
      <div class="col">
        <label for="cor">Cor</label>
        <input type="text" id="cor" name="cor" value="<?= htmlspecialchars($dados['cor'] ?? '') ?>">
      </div>
      <div class="col">
        <label for="esterilizado">Esterilizado</label>
        <select id="esterilizado" name="esterilizado">
          <option value="0" <?= (int)$dados['esterilizado']===0?'selected':'' ?>>Não</option>
          <option value="1" <?= (int)$dados['esterilizado']===1?'selected':'' ?>>Sim</option>
        </select>
      </div>
    </div>

    <div class="acoes">
      <button class="btn" type="submit"><?= $edit ? 'Guardar alterações' : 'Criar animal' ?></button>
      <a class="btn-sec" href="animais.php">Cancelar</a>
    </div>
  </form>
</main>

<script>
// Carregar raças ao mudar a espécie
document.getElementById('especie_id').addEventListener('change', async function(){
  const especieId = this.value;
  const selRaca = document.getElementById('raca_id');
  selRaca.innerHTML = '<option value="">— a carregar… —</option>';

  if (!especieId) { selRaca.innerHTML = '<option value="">— selecione —</option>'; return; }

  try {
    const resp = await fetch('../scripts/api_racas.php?especie_id=' + encodeURIComponent(especieId), {cache:'no-store'});
    const dados = await resp.json();
    selRaca.innerHTML = '<option value="">— selecione —</option>';
    dados.forEach(function(r){
      const op = document.createElement('option');
      op.value = r.id; op.textContent = r.nome;
      selRaca.appendChild(op);
    });
  } catch(e) {
    selRaca.innerHTML = '<option value="">(erro a carregar)</option>';
  }
});
</script>
</body>
</html>
