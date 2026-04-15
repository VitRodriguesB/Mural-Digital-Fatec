<?php
// Se estiver usando Dompdf, mantenha o autoload. 
// Caso esteja apenas gerando o HTML para o navegador, use o código abaixo:

require_once 'config.php';

// 1. BUSCA DE DADOS (Mesma lógica do visualizar.php)
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
    
    if (!in_array($row['prof_nome'], $resumo_profs)) $resumo_profs[] = $row['prof_nome'];
    $resumo_mats[$row['mat_nome']] = $row['mat_nome'];
}

$dias_fixos = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
sort($resumo_profs);
ksort($resumo_mats);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Impressão de Horários - FATEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 30px; background: #fff; font-family: Arial, sans-serif; color: #000; }
        .curso-header { background: #eee; padding: 5px 10px; border-left: 4px solid #b00000; font-weight: bold; margin-top: 20px; font-size: 13px; }
        .table-main { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; }
        .table-main th, .table-main td { border: 1px solid #000; text-align: center; padding: 4px; font-size: 10px; word-wrap: break-word; }
        .materia-name { font-weight: bold; color: #b00000; display: block; }
        .prof-name { font-size: 9px; }
        
        .resumo-container { margin-top: 30px; display: flex; gap: 20px; page-break-inside: avoid; }
        .resumo-box { flex: 1; }
        .table-resumo { width: 100%; border: 1px solid #000; border-collapse: collapse; }
        .table-resumo td { border: 1px solid #000; padding: 3px 6px; font-size: 10px; }
        .bg-stripe { background-color: #e6f7ff !important; -webkit-print-color-adjust: exact; }
    </style>
</head>
<body>

<h4 class="text-center fw-bold mb-4">MURAL DIGITAL DE HORÁRIOS - FATEC</h4>

<?php foreach ($cursos as $nome_exibicao => $conteudo): ?>
    <div class="curso-header"><?= htmlspecialchars($nome_exibicao) ?></div>
    <table class="table-main">
        <thead>
            <tr>
                <th style="width: 75px;">Horário</th>
                <?php foreach ($dias_fixos as $dia) echo "<th>$dia</th>"; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($conteudo['grade'] as $horario => $dias): ?>
            <tr>
                <td class="fw-bold"><?= $horario ?></td>
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
        <div class="fw-bold mb-1" style="font-size: 12px;">Professores:</div>
        <table class="table-resumo">
            <?php foreach ($resumo_profs as $idx => $prof): ?>
                <tr class="<?= $idx % 2 != 0 ? 'bg-stripe' : '' ?>"><td><?= htmlspecialchars($prof) ?></td></tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div class="resumo-box">
        <div class="fw-bold mb-1" style="font-size: 12px;">Disciplinas:</div>
        <table class="table-resumo">
            <?php $i=0; foreach ($resumo_mats as $mat): ?>
                <tr class="<?= $i % 2 != 0 ? 'bg-stripe' : '' ?>">
                    <td><?= htmlspecialchars($mat) ?></td>
                    <td style="width: 50px;"></td>
                </tr>
            <?php $i++; endforeach; ?>
        </table>
    </div>
</div>

<script>
    // Se você quer que o usuário decida quando imprimir, deixe esta parte vazia.
    // O PDF abrirá como uma página HTML pronta para ser salva como PDF (Ctrl + P).
</script>

</body>
</html>