<?php
require_once 'config.php';

// Busca dados com JOIN para ter acesso aos nomes reais
$sql = "SELECT h.*, p.nome as prof_nome, m.nome_materia as mat_nome, c.nome_curso, c.periodo 
        FROM horarios h
        JOIN professores p ON h.id_professor = p.id
        JOIN materias m ON h.id_materia = m.id
        JOIN cursos c ON h.id_curso = c.id
        ORDER BY c.nome_curso, h.horario_inicio, h.dia_semana";

$dados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$cursos = [];
$resumo_profs = [];
$resumo_mats = [];

foreach ($dados as $row) {
    $titulo = $row['nome_curso'] . " - " . $row['periodo'];
    $h = substr($row['horario_inicio'], 0, 5) . " - " . substr($row['horario_fim'], 0, 5);
    $d = $row['dia_semana'];
    
    $cursos[$titulo]['grade'][$h][$d] = $row;
    
    // Coleta para as tabelas de resumo final
    if (!in_array($row['prof_nome'], $resumo_profs)) $resumo_profs[] = $row['prof_nome'];
    $resumo_mats[$row['mat_nome']] = $row['mat_nome']; // Exemplo simplificado, pode-se adicionar código da disciplina se houver no banco
}

$dias_fixos = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
sort($resumo_profs);
ksort($resumo_mats);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Mural Digital - FATEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #fff; font-family: Arial, sans-serif; }
        .curso-header { background: #f2f2f2; padding: 8px; border-left: 5px solid #b00000; font-weight: bold; margin: 25px 0 10px 0; font-size: 14px; }
        
        /* Otimização de espaço na tabela principal */
        .table-main { border: 1px solid #000; width: 100%; table-layout: fixed; }
        .table-main th, .table-main td { 
            border: 1px solid #000 !important; 
            text-align: center; 
            vertical-align: middle; 
            font-size: 11px; 
            padding: 4px !important;
            word-wrap: break-word; /* Força quebra de linha */
        }
        .materia-name { font-weight: bold; color: #b00000; display: block; margin-bottom: 2px; }
        .prof-name { font-size: 10px; color: #333; line-height: 1; }

        /* Tabelas de Resumo Final */
        .resumo-container { margin-top: 40px; display: flex; gap: 20px; }
        .resumo-box { flex: 1; }
        .resumo-title { font-weight: bold; font-size: 14px; margin-bottom: 5px; }
        .table-resumo { border: 1px solid #000; width: 100%; }
        .table-resumo td { 
            border: 1px solid #000; 
            padding: 3px 8px; 
            font-size: 11px; 
        }
        .bg-stripe { background-color: #e6f7ff; } /* Cor azul clara do exemplo */

        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<h3 class="text-center fw-bold">MURAL DIGITAL DE HORÁRIOS - FATEC</h3>

<?php foreach ($cursos as $nome_exibicao => $conteudo): ?>
    <div class="curso-header"><?= htmlspecialchars($nome_exibicao) ?></div>
    <table class="table-main">
        <thead>
            <tr>
                <th style="width: 80px;">Horário</th>
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
                            <span class="materia-name"><?= htmlspecialchars($dias[$dia_f]['mat_nome']) ?></span>
                            <span class="prof-name"><?= htmlspecialchars($dias[$dia_f]['prof_nome']) ?></span>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endforeach; ?>

<div class="resumo-container">
    <div class="resumo-box">
        <div class="resumo-title">Professores</div>
        <table class="table-resumo">
            <?php foreach ($resumo_profs as $index => $prof): ?>
                <tr class="<?= $index % 2 == 0 ? '' : 'bg-stripe' ?>">
                    <td><?= htmlspecialchars($prof) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="resumo-box">
        <div class="resumo-title">Disciplinas:</div>
        <table class="table-resumo">
            <?php $i = 0; foreach ($resumo_mats as $mat): ?>
                <tr class="<?= $i % 2 == 0 ? '' : 'bg-stripe' ?>">
                    <td><?= htmlspecialchars($mat) ?></td>
                    <td style="width: 80px; text-align: right;">---</td> </tr>
            <?php $i++; endforeach; ?>
        </table>
    </div>
</div>

<div class="text-center mt-4 no-print">
    <button onclick="window.print()" class="btn btn-danger">Gerar PDF / Imprimir</button>
</div>

</body>
</html>