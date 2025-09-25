<?php
header('Content-Type: application/json');
require_once "connexio.php";

$input = json_decode(file_get_contents('php://input'), true);

$respostesUsuari = $input['respostes'] ?? [];

$puntuacio = 0;

// Contar todas las preguntas de la BD
$stmtPreg = $pdo->query("SELECT COUNT(*) as total FROM preguntes");
$totalPreguntes = $stmtPreg->fetch(PDO::FETCH_ASSOC)['total'];

foreach ($respostesUsuari as $r) {
    $pid = intval($r['pregunta_id']);
    $rid = intval($r['resposta_id']);

    $stmt = $pdo->prepare("SELECT correcta FROM respostes WHERE id = :rid AND pregunta_id = :pid");
    $stmt->execute(['rid' => $rid, 'pid' => $pid]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['correcta'] == 1) {
        $puntuacio++;
    }
}

echo json_encode([
    "puntuacio" => $puntuacio,
    "total" => $totalPreguntes
], JSON_PRETTY_PRINT);
