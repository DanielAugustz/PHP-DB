<?php
session_start();

$host = getenv('DB_HOST');
$db   = getenv('DB_DATABASE');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

$max_tentativas = 5;
$tentativa = 0;
$conexao = null;

while ($tentativa < $max_tentativas && $conexao === null) {
    try {
        $conexao = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    } catch (PDOException $e) {
        $tentativa++;
        if ($tentativa >= $max_tentativas) {
             $erro_conexao = "Erro ao conectar no banco após $max_tentativas tentativas: " . $e->getMessage();
        }
        sleep(2); 
    }
}

if ($conexao && $_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    
    $sql = "INSERT INTO usuarios (nome, email) VALUES (?, ?)";
    $inserir = $conexao->prepare($sql); 
    
    if ($inserir->execute([$nome, $email])) {
        $_SESSION['mensagem'] = ['texto' => "Usuário '".htmlspecialchars($nome)."' cadastrado!", 'tipo' => 'sucesso'];
    } else {
        $_SESSION['mensagem'] = ['texto' => "Erro ao cadastrar o usuário.", 'tipo' => 'erro'];
    }


    header("Location: index.php");
    exit; 
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro PHP & Docker</title>
    
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h2>Cadastrar Novo Usuário</h2>

    <?php
    if (isset($_SESSION['mensagem'])) {
        $msg = $_SESSION['mensagem'];
        echo "<div class='mensagem " . htmlspecialchars($msg['tipo']) . "'>" . htmlspecialchars($msg['texto']) . "</div>";
        
        unset($_SESSION['mensagem']);
    }

    if (isset($erro_conexao)) {
        echo "<div class='mensagem erro'>$erro_conexao</div>";
    }
    ?>

    <form action="index.php" method="POST" <?php echo !$conexao ? 'style="display:none;"' : ''; ?>>
        <div>
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" required>
        </div>
        <div>
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <button type="submit">Salvar</button>
    </form>
    
    <h2>Usuários Cadastrados</h2>

    <ul <?php echo !$conexao ? 'style="display:none;"' : ''; ?>>
    <?php
        if ($conexao) {
            $selecionar = $conexao->query("SELECT nome, email FROM usuarios ORDER BY id DESC");
            $contador = 0;
            while ($mostrar = $selecionar->fetch(PDO::FETCH_ASSOC)) {
                echo "<li>" . 
                       "<span class='nome'>" . htmlspecialchars($mostrar['nome']) . "</span>" . 
                       "<span class='email'>" . htmlspecialchars($mostrar['email']) . "</span>" . 
                     "</li>";
                $contador++;
            }
            
            if ($contador == 0) {
                 echo "<li>Nenhum usuário cadastrado ainda.</li>";
            }
        }
    ?>
    </ul>

</div> </body>
</html>