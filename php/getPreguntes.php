<?php
header('Content-Type: application/json');
require_once "connexio.php";

$n = isset($_GET['n']) ? (int)$_GET['n'] : 10;
$n = max(1, $n);

$stmt = $pdo->prepare("SELECT * FROM preguntes ORDER BY RAND() LIMIT :n");
$stmt->bindValue(':n', $n, PDO::PARAM_INT);
$stmt->execute();
$preguntes = $stmt->fetchAll();

foreach ($preguntes as &$p) {
    $stmt2 = $pdo->prepare("SELECT id, etiqueta, imatge FROM respostes WHERE pregunta_id = :pid");
    $stmt2->execute(['pid' => $p['id']]);
    $p['respostes'] = $stmt2->fetchAll();

    // Guardamos el ID real en otra propiedad para usarlo en JS
    $p['idReal'] = $p['id'];
    unset($p['id']); // opcional
}

echo json_encode(["preguntes" => $preguntes], JSON_PRETTY_PRINT);
