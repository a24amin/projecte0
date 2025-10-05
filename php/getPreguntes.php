<?php
header('Content-Type: application/json'); // Indica que la resposta serà en format JSON
require_once "connexio.php"; // Inclou la connexió a la base de dades

// Obtenir el nombre de preguntes sol·licitades 
$n = isset($_GET['n']) ? (int)$_GET['n'] : 10; // Si no s'envia 'n', per defecte 10 preguntes
$n = max(1, $n); // Assegura que sempre hi hagi almenys 1 pregunta

// Consultar preguntes aleatòries 
$stmt = $pdo->prepare("SELECT * FROM preguntes ORDER BY RAND() LIMIT :n"); // Escull 'n' preguntes aleatòries
$stmt->bindValue(':n', $n, PDO::PARAM_INT); // Evita injeccions amb bindValue
$stmt->execute();
$preguntes = $stmt->fetchAll(); // Obté les preguntes

// Afegir les respostes corresponents a cada pregunta 
foreach ($preguntes as &$p) {
    // Consulta les respostes de la pregunta actual
    $stmt2 = $pdo->prepare("SELECT id, etiqueta, imatge, correcta, pregunta_id FROM respostes WHERE pregunta_id = :pid");
    $stmt2->execute(['pid' => $p['id']]);
    $respostes = $stmt2->fetchAll();

    // Afegeix explícitament l'ID de la pregunta a cada resposta
    foreach ($respostes as &$r) {
        $r['pregunta_id'] = $p['id'];
    }

    $p['respostes'] = $respostes; // Assigna les respostes a la pregunta
    $p['idReal'] = $p['id']; // Guarda l'ID real per si cal diferenciar-lo
}

// Retornar les preguntes amb les respostes en format JSON 
echo json_encode(["preguntes" => $preguntes], JSON_PRETTY_PRINT);
