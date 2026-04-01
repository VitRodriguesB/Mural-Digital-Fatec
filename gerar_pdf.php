<?php
require 'vendor/autoload.php'; // Carrega o Dompdf
include 'config.php';          // Carrega sua conexão $pdo

use Dompdf\Dompdf;
use Dompdf\Options;

// Configurações para permitir imagens e CSS externo
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// 1. Buscar dados do banco
$query = $pdo->query("SELECT * FROM professores ORDER BY dia_semana, horario_inicio");
$professores = $query->fetchAll(PDO::FETCH_ASSOC);

// 2. Montar o HTML (Pode usar CSS aqui dentro)
$html = '
<style>
    body { font-family: sans-serif; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
    th { background-color: #f2f2f2; }
    h2 { color: #b00000; text-align: center; }
</style>
<h2>Horário de Atendimento - Fatec</h2>
<table>
    <thead>
        <tr>
            <th>Professor</th>
            <th>Matéria</th>
            <th>Dia</th>
            <th>Horário</th>
        </tr>
    </thead>
    <tbody>';

foreach ($professores as $p) {
    $html .= "<tr>
                <td>{$p['nome']}</td>
                <td>{$p['materia']}</td>
                <td>{$p['dia_semana']}</td>
                <td>{$p['horario_inicio']} - {$p['horario_fim']}</td>
              </tr>";
}

$html .= '</tbody></table>';

// 3. Renderizar
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// 4. Saída para o navegador
$dompdf->stream("horarios_fatec.pdf", ["Attachment" => false]);