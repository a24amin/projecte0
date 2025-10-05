<?php
header('Content-Type: application/json'); // Indica que la resposta serà en format JSON
require_once "connexio.php"; // Inclou la connexió a la base de dades

// Llegir les respostes enviades per l'usuari 
$input = json_decode(file_get_contents('php://input'), true); // Agafa les dades JSON enviades amb fetch()
$respostesUsuari = $input['respostes'] ?? []; // Si no hi ha respostes, crea un array buit

$puntuacio = 0; // Inicialitza la puntuació

// Comptar el total de preguntes de la base de dades 
$stmtPreg = $pdo->query("SELECT COUNT(*) as total FROM preguntes");
$totalPreguntes = $stmtPreg->fetch(PDO::FETCH_ASSOC)['total'];

// Revisar cada resposta de l'usuari
foreach ($respostesUsuari as $r) {
    $pid = intval($r['pregunta_id']);  // ID de la pregunta
    $rid = intval($r['resposta_id']);  // ID de la resposta seleccionada

    // Consulta per saber si la resposta seleccionada és correcta
    $stmt = $pdo->prepare("SELECT correcta FROM respostes WHERE id = :rid AND pregunta_id = :pid");
    $stmt->execute(['rid' => $rid, 'pid' => $pid]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si existeix la resposta i és correcta, suma 1 a la puntuació
    if ($result && $result['correcta'] == 1) {
        $puntuacio++;
    }
}

// Retornar el resultat en format JSON
echo json_encode([
    "puntuacio" => $puntuacio, // Punts obtinguts per l'usuari
    "total" => $totalPreguntes // Total de preguntes del quiz
], JSON_PRETTY_PRINT);
