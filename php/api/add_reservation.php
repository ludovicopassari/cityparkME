<?php

// Permetti tutte le origini (o specifica solo una particolare origine se preferisci)
header("Access-Control-Allow-Origin: *");

// Permetti i metodi che possono essere utilizzati (GET, POST, OPTIONS, PUT, DELETE, etc.)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");

// Permetti le intestazioni personalizzate
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Se la richiesta è un preflight (OPTIONS), ritorna una risposta vuota
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Includi la libreria per il JWT
require_once __DIR__ . '/../vendor/autoload.php'; // Assumendo che il file si trovi nella cartella superiore

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

// La chiave segreta che usi per firmare e verificare i JWT (deve essere la stessa usata per emettere i token)
$secret_key = 'la_tua_chiave_segreta';  // Cambia con la tua chiave segreta

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

            // Ora possiamo continuare con la logica della tua API

            // Leggi il corpo della richiesta JSON
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            // Controlla se il campo id_user esiste nel JSON
            if (isset($data['id_user']) &&  isset($data['slot_location']) && isset($data['start_time']) && isset($data['end_time'])  && isset($data['total_cost']) &&isset($data['targa']) ){
                // Estrai i dati dal payload
                $id_user = $data['id_user'];
                $slot_location = $data['slot_location'];
                $total_cost = $data['total_cost'];
                $start_time=$data['start_time'];
                $end_time=$data['end_time'];
                $targa=$data['targa'];
                
                
                $sql_select_targa = "SELECT id FROM veichle WHERE targa = :targa";
                // Preparare la query
                $stmt_targa = $pdo->prepare($sql_select_targa);
                // Bind del parametro
                $stmt_targa->bindParam(':targa', $targa);
                $stmt_targa->execute();

                $plates_id = $stmt_targa->fetch(PDO::FETCH_ASSOC);
                $plates_id = $plates_id['id'];

               
                $sql = "SELECT slot_id FROM parking_slots WHERE location = :slot_location;";
                // Preparare la query
                $stmt = $pdo->prepare($sql);
                // Bind del parametro
                $stmt->bindParam(':slot_location', $slot_location);
                $stmt->execute();

                $slot_id = $stmt->fetch(PDO::FETCH_ASSOC);
                $slot_id = $slot_id['slot_id'];
                
                $sql_insert_reservation = "INSERT INTO reservations (user_id, slot_id, start_time, end_time, total_cost, payment_status, status, created_at, updated_at, plates_id)
                                    VALUES (:id_user,:slot_id,:start_time,:end_time,:total_cost, :payment_status ,:status ,NOW(),NOW(),:plates_id);";
                
            

                $payment_status = 'pending';
                $status = 'confirmed';

                $stmt_insert = $pdo->prepare($sql_insert_reservation);
                $stmt_insert->bindParam(':status',$status); 
                $stmt_insert->bindParam(':payment_status',$payment_status); 
                $stmt_insert->bindParam(':id_user',$id_user); 
                $stmt_insert->bindParam(':slot_id',$slot_id); 
                $stmt_insert->bindParam(':plates_id',$plates_id); 
                $stmt_insert->bindParam(':start_time',$start_time); 
                $stmt_insert->bindParam(':end_time',$end_time); 
                $stmt_insert->bindParam(':total_cost',$total_cost); 

                // Esegui la query
                $stmt_insert->execute();
                // Restituisci una risposta di successo
                echo json_encode(['success' => true, 'message' => 'Reservation added successfully']);
            } else {
                // Se uno dei campi richiesti è mancante
                echo json_encode(['success' => false, 'message' => 'Campo mancante']);
            }

        } catch (Exception $e) {
            // Token non valido o scaduto
            http_response_code(401); // Non autorizzato
            echo json_encode(['success' => false, 'message' => 'Token non valido o scaduto']);
        }
    } else {
        // Se il token non è presente nell'header Authorization
        http_response_code(401); // Non autorizzato
        echo json_encode(['success' => false, 'message' => 'Token mancante']);
    }

} else {
    // Se la richiesta non è una POST
    echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
}
?>