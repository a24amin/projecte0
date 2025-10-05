<?php
// Configuració de la base de dades 
$host = "localhost"; // Servidor de la base de dades
$db   = "a24amioulabi_projecte0"; // Nom de la base de dades
$user = "a24amioulabi_projecte0"; // Usuari de la base de dades
$pass = "#-bU#PC+g[2UOH)t"; // Contrasenya de la base de dades
$charset = "utf8mb4"; // Codificació de caràcters

// Cadena de connexió PDO 
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opcions per a PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Mostra excepcions en cas d'error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retorna arrays associatius per defecte
];

// Crear la connexió PDO 
try {
    $pdo = new PDO($dsn, $user, $pass, $options); // Intenta connectar amb la base de dades
} catch (\PDOException $e) {
    // Si falla, retorna un error en format JSON i atura l'script
    echo json_encode(["error" => "Error de conexión: " . $e->getMessage()]);
    exit;
}
