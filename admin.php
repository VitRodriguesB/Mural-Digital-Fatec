<?php
session_start();

// 1. TRAVA DE SEGURANÇA: Se não estiver logado, volta para o login
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// 2. LÓGICA PARA CADASTRAR PROFESSOR
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar'])) {
    $nome = $_POST['nome'];
    $materia = $_POST['materia'];
    $dia = $_POST['dia'];
    $inicio = $_POST['inicio'];
    $fim = $_POST['fim'];

    $sql = "INSERT INTO professores (nome, materia, dia_semana, horario_inicio, horario_fim) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nome, $materia, $dia, $inicio, $fim]);
    
    header("Location: admin.php"); // Recarrega a página para limpar o formulário
    exit;
}

// 3. LÓGICA PARA EXCLUIR PROFESSOR
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    $pdo->prepare("DELETE FROM professores WHERE id = ?")->execute([$id]);
    header("Location: admin.php");
    exit;
}

// 4. BUSCAR TODOS OS PROFESSORES PARA LISTAR NA TABELA
$professores = $pdo->query("SELECT * FROM professores ORDER BY dia_semana, horario_inicio ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin - Mural Fatec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <span class="navbar-brand">Mural Digital Fatec - Admin</span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Sair</a>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">Cadastrar Horário</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-2">
                            <label class="form-label small">Professor</label>
                            <input type="text" name="nome" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Matéria</label>
                            <input type="text" name="materia" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Dia da Semana</label>
                            <select name="dia" class="form-select form-control-sm">
                                <option>Segunda</option>
                                <option>Terça</option>
                                <option>Quarta</option>
                                <option>Quinta</option>
                                <option>Sexta</option>
                                <option>Sábado</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label small">Início</label>
                                <input type="time" name="inicio" class="form-control form-control-sm" required>
                            </div>
                            <div class="col">
                                <label class="form-label small">Fim</label>
                                <input type="time" name="fim" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <button type="submit" name="cadastrar" class="btn btn-success w-100">Salvar Horário</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Professores Cadastrados</span>
                        <a href="gerar_pdf.php" target="_blank" class="btn btn-primary btn-sm">Ver PDF do QR Code</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Dia</th>
                                <th>Horário</th>
                                <th class="text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($professores as $p): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($p['nome']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($p['materia']) ?></small>
                                </td>
                                <td><?= $p['dia_semana'] ?></td>
                                <td><?= substr($p['horario_inicio'], 0, 5) ?> - <?= substr($p['horario_fim'], 0, 5) ?></td>
                                <td class="text-center">
                                    <a href="?excluir=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza?')">Excluir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($professores)): ?>
                                <tr><td colspan="4" class="text-center text-muted">Nenhum professor cadastrado.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>