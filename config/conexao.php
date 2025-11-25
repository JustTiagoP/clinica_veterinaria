<?php
/*
 |--------------------------------------------------------------
 | Ficheiro de ligação à Base de Dados (conexao.php)
 |--------------------------------------------------------------
 | Este ficheiro cria a ligação entre o PHP e o MySQL usando PDO.
 | Deve ser incluído em todas as páginas que precisem de aceder à BD.
 |--------------------------------------------------------------
 | Boas práticas seguidas:
 | - Ligação única e segura (PDO)
 | - Charset UTF-8
 | - Modo de erros por exceção
 | - Mensagem de erro simples (sem mostrar credenciais)
 |--------------------------------------------------------------

*/

$host = '157.173.115.230';          // Servidor da base de dados em formação.net
$bdname = 'clinica_veterinaria';      // Nome da base de dados
$utilizador = 'clinica_sa';         // Nome do utilizador MySQL
$senha = '@Amm100%';   

// Tenta criar a ligação
try {
    // DSN = Data Source Name
    $ligacao = new PDO("mysql:host=$host;dbname=$bdname;charset=utf8mb4", $utilizador, $senha);
    
    // Define o modo de erro: lança exceção em caso de falha
    $ligacao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // (Opcional) Define o modo de recuperação de resultados como associativo
    $ligacao->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Teste de ligação (pode ser removido após confirmar que funciona)
     echo "Ligação bem-sucedida à base de dados!";
    
} catch (PDOException $erro) {
    // Em caso de erro, mostra uma mensagem simples (não revelar detalhes!)
    echo "Erro: Não foi possível ligar à base de dados. <br>";
    // Para ver o erro durante o desenvolvimento:
    echo "Detalhes: " . $erro->getMessage();
    exit;
}
?>
