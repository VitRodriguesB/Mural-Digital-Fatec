<?php
session_start();
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}
require_once 'config.php';

// --- 1. LÓGICA DE PROCESSAMENTO (CRUD) ---

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Salvar Professor
    if (isset($_POST['save_prof'])) {
        if (!empty($_POST['id'])) {
            $pdo->prepare("UPDATE professores SET nome=?, ano_inicio=? WHERE id=?")->execute([$_POST['nome'], $_POST['ano'], $_POST['id']]);
        } else {
            $pdo->prepare("INSERT INTO professores (nome, ano_inicio) VALUES (?, ?)")->execute([$_POST['nome'], $_POST['ano']]);
        }
    }
    // Salvar Curso
    if (isset($_POST['save_curso'])) {
        if (!empty($_POST['id'])) {
            $pdo->prepare("UPDATE cursos SET nome_curso=?, periodo=? WHERE id=?")->execute([$_POST['curso'], $_POST['periodo'], $_POST['id']]);
        } else {
            $pdo->prepare("INSERT INTO cursos (nome_curso, periodo) VALUES (?, ?)")->execute([$_POST['curso'], $_POST['periodo']]);
        }
    }
    // Salvar Matéria
    if (isset($_POST['save_materia'])) {
        if (!empty($_POST['id'])) {
            $pdo->prepare("UPDATE materias SET nome_materia=?, id_curso=? WHERE id=?")->execute([$_POST['materia'], $_POST['id_curso'], $_POST['id']]);
        } else {
            $pdo->prepare("INSERT INTO materias (nome_materia, id_curso) VALUES (?, ?)")->execute([$_POST['materia'], $_POST['id_curso']]);
        }
    }
    // Salvar Horário (Vínculo)
    if (isset($_POST['save_horario'])) {
        if (!empty($_POST['id'])) {
            $sql = "UPDATE horarios SET id_professor=?, id_materia=?, id_curso=?, dia_semana=?, horario_inicio=?, horario_fim=? WHERE id=?";
            $pdo->prepare($sql)->execute([$_POST['id_prof'], $_POST['id_mat'], $_POST['id_cur'], $_POST['dia'], $_POST['inicio'], $_POST['fim'], $_POST['id']]);
        } else {
            $sql = "INSERT INTO horarios (id_professor, id_materia, id_curso, dia_semana, horario_inicio, horario_fim) VALUES (?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$_POST['id_prof'], $_POST['id_mat'], $_POST['id_cur'], $_POST['dia'], $_POST['inicio'], $_POST['fim']]);
        }
    }
    header("Location: admin.php"); exit;
}

// Lógica de Exclusão
if (isset($_GET['del_table']) && isset($_GET['del_id'])) {
    $tabela = $_GET['del_table'];
    $id = $_GET['del_id'];
    $allowed = ['professores', 'cursos', 'materias', 'horarios'];
    if (in_array($tabela, $allowed)) {
        $pdo->prepare("DELETE FROM $tabela WHERE id = ?")->execute([$id]);
    }
    header("Location: admin.php"); exit;
}

// --- 2. BUSCA DE DADOS ---

$profs = $pdo->query("SELECT * FROM professores ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nome_curso ASC")->fetchAll(PDO::FETCH_ASSOC);
$materias = $pdo->query("SELECT m.*, c.nome_curso, c.periodo FROM materias m JOIN cursos c ON m.id_curso = c.id ORDER BY m.nome_materia ASC")->fetchAll(PDO::FETCH_ASSOC);
$horarios = $pdo->query("SELECT h.*, p.nome as prof, m.nome_materia as mat, c.nome_curso as cur FROM horarios h 
                         JOIN professores p ON h.id_professor = p.id 
                         JOIN materias m ON h.id_materia = m.id 
                         JOIN cursos c ON h.id_curso = c.id ORDER BY h.id DESC")->fetchAll(PDO::FETCH_ASSOC);

$edit_data = null;
if (isset($_GET['edit_t']) && isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM {$_GET['edit_t']} WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gestão Mural Digital | Fatec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --fatec: #b00000; }
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .nav-tabs .nav-link.active { background: var(--fatec); color: #fff; border: none; }
        .nav-link { color: #333; font-weight: bold; }
        .card { border-radius: 12px; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .btn-fatec { background: var(--fatec); color: white; }
        .search-box { margin-bottom: 15px; border-radius: 20px; padding-left: 15px; border: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Gestão Mural Digital</h2>
        <a href="visualizar.php" target="_blank" class="btn btn-outline-dark">Ver Mural Público</a>
    </div>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><a class="nav-link <?= !isset($_GET['edit_t']) || $_GET['edit_t']=='horarios' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tab-horarios">Horários</a></li>
        <li class="nav-item"><a class="nav-link <?= @$_GET['edit_t']=='professores'?'active':'' ?>" data-bs-toggle="tab" href="#tab-profs">Professores</a></li>
        <li class="nav-item"><a class="nav-link <?= @$_GET['edit_t']=='cursos'?'active':'' ?>" data-bs-toggle="tab" href="#tab-cursos">Cursos</a></li>
        <li class="nav-item"><a class="nav-link <?= @$_GET['edit_t']=='materias'?'active':'' ?>" data-bs-toggle="tab" href="#tab-materias">Matérias</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade <?= !isset($_GET['edit_t']) || $_GET['edit_t']=='horarios' ? 'show active' : '' ?>" id="tab-horarios">
            <div class="row">
                <div class="col-md-4">
                    <div class="card p-4">
                        <h6 class="fw-bold mb-3"><?= $edit_data && $_GET['edit_t']=='horarios' ? 'Editar' : 'Vincular' ?> Horário</h6>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= @$edit_data['id'] ?>">
                            <select name="id_prof" class="form-select mb-2" required>
                                <option value="">Professor...</option>
                                <?php foreach($profs as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= @$edit_data['id_professor']==$p['id']?'selected':'' ?>><?= $p['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="id_cur" class="form-select mb-2" required>
                                <option value="">Curso...</option>
                                <?php foreach($cursos as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= @$edit_data['id_curso']==$c['id']?'selected':'' ?>><?= $c['nome_curso'] ?> (<?= $c['periodo'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <select name="id_mat" class="form-select mb-2" required>
                                <option value="">Matéria...</option>
                                <?php foreach($materias as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= @$edit_data['id_materia']==$m['id']?'selected':'' ?>><?= $m['nome_materia'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="dia" class="form-select mb-2">
                                <?php foreach(['Segunda','Terça','Quarta','Quinta','Sexta','Sábado'] as $d): ?>
                                    <option <?= @$edit_data['dia_semana']==$d?'selected':'' ?>><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="row mb-3">
                                <div class="col"><label class="small">Início</label><input type="time" name="inicio" class="form-control" value="<?= @$edit_data['horario_inicio'] ?>" required></div>
                                <div class="col"><label class="small">Fim</label><input type="time" name="fim" class="form-control" value="<?= @$edit_data['horario_fim'] ?>" required></div>
                            </div>
                            <button name="save_horario" class="btn btn-fatec w-100">Salvar na Grade</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card p-3">
                        <input type="text" class="form-control search-box" placeholder="Filtrar horários..." onkeyup="filterTable(this, 'table-horarios')">
                        <table class="table table-hover" id="table-horarios">
                            <thead><tr><th>Curso</th><th>Prof/Matéria</th><th>Dia/Horário</th><th class="text-center">Ação</th></tr></thead>
                            <tbody>
                                <?php foreach($horarios as $h): ?>
                                <tr>
                                    <td><small class="badge bg-secondary"><?= $h['cur'] ?></small></td>
                                    <td><strong><?= $h['prof'] ?></strong><br><small><?= $h['mat'] ?></small></td>
                                    <td><?= $h['dia_semana'] ?><br><small class="text-muted"><?= substr($h['horario_inicio'],0,5) ?> - <?= substr($h['horario_fim'],0,5) ?></small></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="?edit_t=horarios&edit_id=<?= $h['id'] ?>" class="btn btn-outline-primary">Editar</a>
                                            <a href="?del_table=horarios&del_id=<?= $h['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Excluir?')">Remover</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade <?= @$_GET['edit_t']=='professores'?'show active':'' ?>" id="tab-profs">
            <div class="row">
                <div class="col-md-4">
                    <div class="card p-4">
                        <h6 class="fw-bold mb-3"><?= $edit_data && $_GET['edit_t']=='professores' ? 'Editar' : 'Novo' ?> Professor</h6>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= @$edit_data['id'] ?>">
                            <input type="text" name="nome" class="form-control mb-2" placeholder="Nome" value="<?= @$edit_data['nome'] ?>" required>
                            <input type="number" name="ano" class="form-control mb-3" value="<?= @$edit_data['ano_inicio'] ?? date('Y') ?>">
                            <button name="save_prof" class="btn btn-fatec w-100">Salvar Professor</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card p-3">
                        <input type="text" class="form-control search-box" placeholder="Procurar professor..." onkeyup="filterTable(this, 'table-profs')">
                        <table class="table" id="table-profs">
                            <thead><tr><th>Nome</th><th>Ações</th></tr></thead>
                            <tbody>
                                <?php foreach($profs as $p): ?>
                                <tr>
                                    <td><?= $p['nome'] ?></td>
                                    <td>
                                        <a href="?edit_t=professores&edit_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <a href="?del_table=professores&del_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger">Excluir</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade <?= @$_GET['edit_t']=='cursos'?'show active':'' ?>" id="tab-cursos">
            <div class="row">
                <div class="col-md-4">
                    <div class="card p-4">
                        <h6 class="fw-bold mb-3"><?= $edit_data && $_GET['edit_t']=='cursos' ? 'Editar' : 'Novo' ?> Curso</h6>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= @$edit_data['id'] ?>">
                            <input type="text" name="curso" class="form-control mb-2" placeholder="Ex: ADS" value="<?= @$edit_data['nome_curso'] ?>" required>
                            <select name="periodo" class="form-select mb-3">
                                <option <?= @$edit_data['periodo']=='Manhã'?'selected':'' ?>>Manhã</option>
                                <option <?= @$edit_data['periodo']=='Noite'?'selected':'' ?>>Noite</option>
                                <option <?= @$edit_data['periodo']=='AMS'?'selected':'' ?>>AMS</option>
                            </select>
                            <button name="save_curso" class="btn btn-fatec w-100">Salvar Curso</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card p-3">
                        <input type="text" class="form-control search-box" placeholder="Procurar curso..." onkeyup="filterTable(this, 'table-cursos')">
                        <table class="table" id="table-cursos">
                            <thead><tr><th>Curso</th><th>Período</th><th>Ações</th></tr></thead>
                            <tbody>
                                <?php foreach($cursos as $c): ?>
                                <tr><td><?= $c['nome_curso'] ?></td><td><?= $c['periodo'] ?></td>
                                    <td>
                                        <a href="?edit_t=cursos&edit_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <a href="?del_table=cursos&del_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger">Excluir</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade <?= @$_GET['edit_t']=='materias'?'show active':'' ?>" id="tab-materias">
            <div class="row">
                <div class="col-md-4">
                    <div class="card p-4">
                        <h6 class="fw-bold mb-3"><?= $edit_data && $_GET['edit_t']=='materias' ? 'Editar' : 'Nova' ?> Matéria</h6>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= @$edit_data['id'] ?>">
                            <input type="text" name="materia" class="form-control mb-2" placeholder="Disciplina" value="<?= @$edit_data['nome_materia'] ?>" required>
                            <select name="id_curso" class="form-select mb-3" required>
                                <option value="">Vincular ao Curso...</option>
                                <?php foreach($cursos as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= @$edit_data['id_curso']==$c['id']?'selected':'' ?>><?= $c['nome_curso'] ?> (<?= $c['periodo'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <button name="save_materia" class="btn btn-fatec w-100">Salvar Matéria</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card p-3">
                        <input type="text" class="form-control search-box" placeholder="Procurar matéria..." onkeyup="filterTable(this, 'table-materias')">
                        <table class="table" id="table-materias">
                            <thead><tr><th>Matéria</th><th>Curso/Período</th><th>Ações</th></tr></thead>
                            <tbody>
                                <?php foreach($materias as $m): ?>
                                <tr>
                                    <td><strong><?= $m['nome_materia'] ?></strong></td>
                                    <td><?= $m['nome_curso'] ?> <span class="badge bg-light text-dark"><?= $m['periodo'] ?></span></td>
                                    <td>
                                        <a href="?edit_t=materias&edit_id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <a href="?del_table=materias&del_id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-danger">Excluir</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterTable(input, tableId) {
    let filter = input.value.toLowerCase();
    let rows = document.getElementById(tableId).getElementsByTagName("tr");
    for (let i = 1; i < rows.length; i++) {
        rows[i].style.display = rows[i].innerText.toLowerCase().includes(filter) ? "" : "none";
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>