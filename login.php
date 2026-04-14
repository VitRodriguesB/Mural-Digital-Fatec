<?php
session_start();
require_once 'config.php';

// Se o usuário já estiver logado, redireciona direto para o admin
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: admin.php");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $senha = $_POST['senha'];

    // Busca o usuário no banco de dados
    $sql = "SELECT * FROM usuarios WHERE login = :login AND senha = :senha";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['login' => $login, 'senha' => $senha]);
    $user = $stmt->fetch();

    if ($user) {
        // Cria a sessão de segurança
        $_SESSION['logado'] = true;
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nome'] = $user['login'];
        
        header("Location: admin.php");
        exit;
    } else {
        $erro = "Usuário ou senha incorretos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mural Digital Fatec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { margin-top: 100px; max-width: 400px; }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="login-container w-100">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4>Mural Digital Fatec</h4>
                <small>Acesso Restrito</small>
            </div>
            <div class="card-body">
                
                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?= $erro ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label for="login" class="form-label">Usuário</label>
                        <input type="text" name="login" id="login" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" name="senha" id="senha" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </div>
                </form>
                
            </div>
            <div class="card-footer text-center text-muted">
                &copy; 2026 - Gestão de Horários
            </div>
        </div>
    </div>
</div>

</body>
</html>
