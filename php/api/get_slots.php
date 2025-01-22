<?php


// Permetti tutte le origini (o specifica solo una particolare origine se preferisci)
header("Access-Control-Allow-Origin: *");

// Permetti i metodi che possono essere utilizzati (GET, POST, PUT, DELETE, etc.)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");

// Permetti le intestazioni personalizzate
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Se la richiesta è un preflight (OPTIONS), ritorna una risposta vuota
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configurazione del database
$host = 'mysql';
$db = 'my_database';
$user = 'root';
$password = 'rootpassword';
$port = 3306;

try {
    // Connessione al database
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query per ottenere tutti gli slot
    $query = "SELECT slot_id, location, latitude, longitude, rate_per_hour, is_occupied FROM parking_slots";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // Recupero dei dati
    $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Restituzione dei dati in formato JSON
    header('Content-Type: application/json');
    echo json_encode($slots);
} catch (PDOException $e) {
    // Gestione degli errori
    http_response_code(500);
    echo json_encode(['error' => 'Errore nella connessione al database: ' . $e->getMessage()]);
}
?>

