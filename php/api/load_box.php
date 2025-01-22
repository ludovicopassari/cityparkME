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
            if (isset($data['id_user'])) {
                $id_user = $data['id_user'];
                $confirmed="confirmed";
                $cancelled="cancelled";
                $completed="completed";
                $pending = "pending";
                $paid = "paid";

                // Prepara la query per recuperare i pagamenti in base a id_user
                $sql_count_active="SELECT COUNT(reservation_id) AS COUNT_A FROM reservations WHERE status=:confirmed AND payment_status=:pending AND user_id=:id_user";
                //payment status : pending   status : completed
                $sql_count_completed="SELECT COUNT(reservation_id) AS COUNT_S FROM reservations WHERE status=:completed AND payment_status=:pending AND user_id=:id_user";
                //payment status : paid status : completed
                $sql_count_close="SELECT COUNT(reservation_id) AS COUNT_C FROM reservations WHERE status=:completed  AND payment_status=:paid AND user_id=:id_user";

                $stmt_count_a= $pdo->prepare($sql_count_active);
                $stmt_count_a->bindParam(':id_user', $id_user, PDO::PARAM_INT);
                $stmt_count_a->bindParam(':confirmed',$confirmed ,PDO::PARAM_STR);
                $stmt_count_a->bindParam(':pending',$pending ,PDO::PARAM_STR);
                $stmt_count_a->execute();
                $result_a= $stmt_count_a->fetchColumn();

                $stmt_count_s= $pdo->prepare($sql_count_completed);
                $stmt_count_s->bindParam(':id_user', $id_user, PDO::PARAM_INT);
                $stmt_count_s->bindParam(':completed',$completed ,PDO::PARAM_STR);
                $stmt_count_s->bindParam(':pending',$pending ,PDO::PARAM_STR);
                $stmt_count_s->execute();
                $result_s= $stmt_count_s->fetchColumn();

                $stmt_count_c= $pdo->prepare($sql_count_close);
                $stmt_count_c->bindParam(':id_user', $id_user, PDO::PARAM_INT);
                $stmt_count_c->bindParam(':completed',$completed ,PDO::PARAM_STR);
                $stmt_count_c->bindParam(':paid',$paid ,PDO::PARAM_STR);
                
                $stmt_count_c->execute();
                $result_c= $stmt_count_c->fetchColumn();

                // Controlla se sono stati trovati risultati
                if (true) {
                    echo json_encode(['success' => true, 'count_a' => $result_a, 'count_s' => $result_s, 'count_c' => $result_c]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Nessun pagamento trovato']);
                }
            } else {
                // Se id_user non è presente nel body JSON
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