<?php
/*
 |--------------------------------------------------------------
 | Ficheiro de teste da ligação à Base de Dados
 |--------------------------------------------------------------
 | Objetivo:
 |   - Confirmar que o ficheiro conexao.php funciona.
 |   - Mostrar os registos da tabela "especies".
 |--------------------------------------------------------------
 | Este é o primeiro teste de comunicação entre PHP e MySQL.
 |--------------------------------------------------------------
*/

// Inclui o ficheiro de ligação (um nível acima)
require_once 'C:/trabalhos/clinica_veterinaria/config/conexao.php';

// Cria a consulta SQL (só queremos as espécies não apagadas)
$sql = "SELECT id, nome FROM especies WHERE apagado_em IS NULL ORDER BY nome ASC";

try {
    // Executa a consulta na base de dados
    $resultado = $ligacao->query($sql);

    // Guarda todos os registos num array
    $especies = $resultado->fetchAll();

} catch (PDOException $erro) {
    // Se algo correr mal, mostra uma mensagem simples
    echo "Erro ao obter dados: " . $erro->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Teste de Ligação à Base de Dados</title>
    <link rel="stylesheet" href="C:/trabalhos/clinica_veterinaria/assets/css/estilo.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f8f8;
            margin: 40px;
        }
        h1 {
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 400px;
            background: white;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #eaeaea;
        }
    </style>
</head>
<body>

<h1>Teste de Ligação à Base de Dados</h1>
<p>Esta página mostra os registos da tabela <strong>especies</strong>.</p>

<?php if (count($especies) > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Nome da Espécie</th>
        </tr>
        <?php foreach ($especies as $linha): ?>
            <tr>
                <td><?= htmlspecialchars($linha['id']) ?></td>
                <td><?= htmlspecialchars($linha['nome']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p><em>Não existem espécies registadas na base de dados.</em></p>
<?php endif; ?>

</body>
</html>