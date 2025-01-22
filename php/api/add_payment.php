<?php
// Permetti tutte le origini (o specifica solo una particolare origine se preferisci)
header("Access-Control-Allow-Origin: *");

// Permetti i metodi che possono essere utilizzati (GET, POST, PUT, DELETE, etc.)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");

// Se la richiesta è un preflight (OPTIONS), ritorna una risposta vuota
/*
Una preflight è una richiesta che i browser moderni effettuano automaticamente prima di
inviare una richiesta CORS che utilizza metodi o header che potrebbero non essere sicuri o 
standard. Lo scopo del prefight è verificare che il server accetti effettivamente la richiesta CORS
Quando il browser invia una richiesta preflight utilizzando il metodo OPTIONS, il server risponde con 200 OK,
permettendo al browser di sapere che le richieste successive con i metodi e header specificati sono consentite.
Se la richiesta è un OPTIONS, il server risponde immediatamente con un codice di stato 200 OK. Questo indica al client
 (tipicamente un browser) che le richieste CORS possono procedere.

*/
// Permetti le intestazioni personalizzate
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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
$charset = 'utf8mb4'; //unicode



// Connessione al database con PDO
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [ //Array associativo 
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //Configura PDO per lanciare eccezioni in caso di errori. Questo è utile per la gestione degli errori, poiché permette di catturare gli errori con try/catch
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //imposta il metodo di fetch predefinito su PDO::FETCH_ASSOC, che fa sì che i risultati delle query siano restituiti come array associativi 
    PDO::ATTR_EMULATE_PREPARES   => false, //Disabilita la simulazione delle query preparate. Forza l'uso delle query preparate native, migliorando la sicurezza contro attacchi SQL injection
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Impostiamo l'intestazione per il tipo di contenuto JSON
header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD']==='POST'){
/*
     Controlla se la richiesta HTTP è di tipo POST. Se la richiesta non è una POST, viene restituito un messaggio di errore indicando
      che il metodo non è consentito.
*/
 // Leggi il token JWT dall'header Authorization
 $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? ''; //In caso non esiste assegna una stringa vuota 
 $bearer_token = null; 
 if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
    $bearer_token = $matches[1];
}
/* APPUNTI UTILI 
preg_match(): È una funzione PHP che esegue una ricerca utilizzando un'espressione regolare. 
Restituisce true se la stringa $auth_header corrisponde all'espressione regolare fornita.
Espressione Regolare /Bearer\s(\S+)/:
Bearer: Cerca la parola "Bearer" che è una convenzione standard per indicare che l'header contiene un token.
\s: Cerca uno spazio dopo la parola "Bearer".
(\S+): Cerca una sequenza di caratteri non bianchi (\S indica un carattere non bianco, e + indica uno o più caratteri).
Parentesi: Racchiudono \S+ per catturare il token che segue "Bearer ".
$matches: Se viene trovata una corrispondenza, preg_match riempie l'array $matches con le parti della stringa che corrispondono 
all'espressione regolare. L'intero match è memorizzato in $matches[0], mentre il token stesso è in $matches[1] grazie alle parentesi di cattura.
*/

if($bearer_token){
    try{
        // Leggi il corpo della richiesta JSON
        $input = file_get_contents('php://input'); //Legge il corpo della richiesta, che si presume sia in formato JSON, e lo decodifica in un array associativo PHP.
        $data = json_decode($input, true);

        // Controlla se il campo id_user esiste nel JSON
        if (isset($data['id_user']) && isset($data['card_number'])) {
            $id_user = $data['id_user'];
            $card_number= $data['card_number'];
            // Prepara la query per recuperare i dati in base a id_user
            $sql = "SELECT * FROM users WHERE id= :id_user";
            $sql = "INSERT INTO credit_cards(user_id, card_number) VALUES (:id_user,:card_number)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
            $stmt->bindParam(':card_number', $card_number,PDO::PARAM_INT);

            // Esegui la query
            $stmt->execute();
            
            // Se id_user  è presente nel body JSON
            echo json_encode(['success' => true, 'message' => 'numero carta inserita inserita correttamente']);
        } else {
            // Se id_user non è presente nel body JSON
            echo json_encode(['success' => false, 'message' => 'Campo mancante']);
        }
    }catch(Exception $e) {
        // Token non valido o scaduto
        http_response_code(401); // Non autorizzato
        echo json_encode(['success' => false, 'message' => 'Token non valido o scaduto']);
    }
}else {
    // Se il token non è presente nell'header Authorization
    http_response_code(401); // Non autorizzato
    echo json_encode(['success' => false, 'message' => 'Token mancante']);
}


} else {
// Se la richiesta non è una POST
echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
}
?>