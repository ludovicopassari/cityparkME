<?php

// Permetti tutte le origini (o specifica solo una particolare origine se preferisci)
header("Access-Control-Allow-Origin: *");

// Permetti i metodi che possono essere utilizzati (GET, POST, OPTIONS, PUT, DELETE)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");

// Permetti le intestazioni personalizzate
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Se la richiesta è un preflight (OPTIONS), ritorna una risposta vuota
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Includi la libreria per il JWT
// Usa il percorso corretto per il tuo autoload.php
require_once __DIR__ . '/../vendor/autoload.php'; // Assumendo che il file si trovi nella cartella superiore

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;
// La chiave segreta che usi per firmare e verificare i JWT (deve essere la stessa usata per emettere i token)
$secret_key = 'la_tua_chiave_segreta'; // Cambia con la tua chiave segreta

// Impostazioni per la connessione al database
$host = 'mysql';
$dbname = 'my_database';
$username = 'root';
$password = 'rootpassword';
$charset = 'utf8mb4';

// Connessione al database con PDO
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Impostiamo l'intestazione per il tipo di contenuto JSON
header('Content-Type: application/json');

// Verifica se la richiesta è una POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

     // Leggi il token JWT dall'header Authorization
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $bearer_token = null;

    // Estrai il token JWT dal header Authorization
    if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $bearer_token = $matches[1];
    }

    if ($bearer_token) {
        try {

            // Decodifica il token JWT
            $decoded = JWT::decode($bearer_token, new Key($secret_key, 'HS256'));

            // Leggi il corpo della richiesta JSON
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            // Controlla se il campo id_user e id_veicolo sono presenti nel JSON
            if (isset($data['id_user']) && isset($data['id_targa'])) {

                $id_user = $data['id_user'];
                $id_veicolo = $data['id_targa'];

                // Prepara la query per rimuovere il veicolo (targa) in base a id_user e id_veicolo
                $sql = "DELETE FROM veichle WHERE user_id = :id_user AND id = :id_targa";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
                $stmt->bindParam(':id_targa', $id_veicolo, PDO::PARAM_INT);

                // Esegui la query
                $stmt->execute();

                // Verifica se è stato rimosso un record
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Targa rimossa con successo']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Nessun veicolo trovato per l\'utente specificato']);
                }
            }else {
                // Se id_user o id_veicolo mancano nel body JSON
                echo json_encode(['success' => false, 'message' => 'Campo id_user o id_veicolo mancante']);
            }

        }catch (Exception $e) {
            // Token non valido o scaduto
            http_response_code(401); // Non autorizzato
            echo json_encode(['success' => false, 'message' => 'Token non valido o scaduto']);
        }
    }

} 

?>
