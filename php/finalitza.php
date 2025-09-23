<?php
header('Content-Type: application/json');
require_once "connexio.php";

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['respostes']) || !is_array($input['respostes'])) {
    echo json_encode(["error" => "No se enviaron respuestas válidas"]);
    exit;
}

$respostesUsuari = $input['respostes']; 

if (empty($respostesUsuari)) {
    echo json_encode(["puntuacio" => 0, "total" => 0]);
    exit;
}

$puntuacio = 0;

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
    "total" => count($respostesUsuari)
], JSON_PRETTY_PRINT);
