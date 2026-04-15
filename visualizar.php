<?php
require_once 'config.php';

// Query com JOIN para pegar os nomes das tabelas relacionadas
$sql = "SELECT h.*, p.nome as prof_nome, m.nome_materia as mat_nome, c.nome_curso, c.periodo 
        FROM horarios h
        JOIN professores p ON h.id_professor = p.id
        JOIN materias m ON h.id_materia = m.id
        JOIN cursos c ON h.id_curso = c.id
        ORDER BY c.nome_curso, h.horario_inicio, h.dia_semana";

$dados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$cursos = [];
$legenda = [];

foreach ($dados as $row) {
    $titulo = $row['nome_curso'] . " - " . $row['periodo'];
    $h = substr($row['horario_inicio'], 0, 5) . " - " . substr($row['horario_fim'], 0, 5);
    $d = $row['dia_semana'];
    
    $cursos[$titulo]['grade'][$h][$d] = $row;
    $legenda[$row['prof_nome']] = $row['mat_nome'];
}

$dias_fixos = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Visualização de Horários | Fatec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 40px; background: #fff; }
        .curso-header { background: #eee; padding: 10px; border-left: 5px solid #b00000; font-weight: bold; margin: 30px 0 15px 0; text-transform: uppercase; }
        .table { border: 1px solid #000; margin-bottom: 0; }
        .table th, .table td { border: 1px solid #000 !important; text-align: center; vertical-align: middle; font-size: 12px; }
        .materia { font-weight: bold; color: #b00000; display: block; }
        .resumo { margin-top: 50px; border-top: 2px solid #000; padding-top: 20px; }
    </style>
</head>
<body>

<h2 class="text-center fw-bold mb-5">MURAL DIGITAL DE HORÁRIOS - FATEC</h2>

<?php foreach ($cursos as $nome_exibicao => $conteudo): ?>
    <div class="curso-header"><?= $nome_exibicao ?></div>
    <table class="table table-sm">
        <thead>
            <tr>
                <th width="120">Horário</th>
                <?php foreach ($dias_fixos as $dia) echo "<th>$dia</th>"; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($conteudo['grade'] as $horario => $dias): ?>
            <tr>
                <td class="fw-bold bg-light"><?= $horario ?></td>
                <?php foreach ($dias_fixos as $dia_f): ?>
                    <td>
                        <?php if (isset($dias[$dia_f])): ?>
                            <span class="materia"><?= $dias[$dia_f]['mat_nome'] ?></span>
                            <?= $dias[$dia_f]['prof_nome'] ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

<div class="resumo">
    <h5><strong>Professores e Disciplinas</strong></h5>
    <div class="row mt-3">
        <?php ksort($legenda); foreach ($legenda as $p => $m): ?>
            <div class="col-md-6 mb-1 small"><strong><?= $p ?>:</strong> <?= $m ?></div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>