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

// Includi il file di configurazione
$config = include './config.php';

// Usa i parametri di configurazione
$host = $config['host'];
$dbname = $config['dbname'];
$username = $config['username'];
$password = $config['password'];
$charset = 'utf8mb4';
$secret_key = $config['secret_key'];  // Cambia con la tua chiave segreta
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
            if (isset($data['id_user']) && isset($data['reservation_id']) && isset($data['payment_method']) && isset($data['amount']) ) {
                // Estrai i dati dal payload
                $id_user = $data['id_user'];
                $reservation_id = $data['reservation_id'];
                $payment_method = $data['payment_method'];
                $amount = $data['amount'];
                

                // 1. Aggiorna la tabella reservations (status = 'paid')
                $sql_update_reservation = "UPDATE reservations
                                           SET payment_status = 'paid'
                                           WHERE reservation_id = :reservation_id AND user_id = :id_user";

                $stmt_update = $pdo->prepare($sql_update_reservation);
                $stmt_update->bindParam(':reservation_id', $reservation_id, PDO::PARAM_INT);
                $stmt_update->bindParam(':id_user', $id_user, PDO::PARAM_INT);
                $stmt_update->execute();

                // 2. Inserisci una nuova riga nella tabella payments
                $sql_insert_payment = "INSERT INTO payments (reservation_id, payment_method,payment_date, amount, payment_status)
                                    VALUES (:reservation_id, :payment_method, NOW(),:amount, :payment_status);";
                $payment_status = 'successful'; // Stato del pagamento
                $stmt_insert = $pdo->prepare($sql_insert_payment);
                $stmt_insert->bindParam(':reservation_id', $reservation_id, PDO::PARAM_INT); // Parametro intero (int)
                $stmt_insert->bindParam(':payment_method', $payment_method); // Parametro stringa (str)
                $stmt_insert->bindParam(':amount', $amount, PDO::PARAM_STR); // Parametro numerico come stringa (decimal o float, per esempio "100.50")
                $stmt_insert->bindParam(':payment_status', $payment_status, PDO::PARAM_STR); // Parametro stringa (str)

                // Esegui la query
                $stmt_insert->execute();
                // Restituisci una risposta di successo
                echo json_encode(['success' => true, 'message' => 'Reservation and payment updated successfully']);
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
