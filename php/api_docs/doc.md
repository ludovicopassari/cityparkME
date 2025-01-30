# Documentazione API: add_payment.php

## Descrizione
Il file `add_payment.php` è uno script PHP progettato per gestire le richieste HTTP relative all'aggiunta di pagamenti. Supporta CORS e gestisce diversi metodi HTTP, facilitando l'integrazione con applicazioni frontend o altri client.

---

## Configurazione iniziale
### Header HTTP
Lo script imposta i seguenti header:
- `Access-Control-Allow-Origin: *` — Permette richieste da tutte le origini.
- `Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE` — Consente vari metodi HTTP.

### Gestione delle richieste OPTIONS (preflight)
Se la richiesta è un preflight (`OPTIONS`):
- Risponde immediatamente con un codice di stato `200 OK`.
- Non esegue alcun altro codice.

---

## Endpoint: Aggiunta di un metodo di pagamento
### Metodo HTTP: `POST`

### URL
`/api/add_payment.php`

### Parametri richiesti
I parametri devono essere inviati nel corpo della richiesta in formato JSON. Di seguito sono elencati i parametri attesi:
- `id_user`
- `card_number` 


### Risposte
#### 200 OK
La richiesta è stata completata con successo.
- **Esempio di risposta**:
```json
{
  "status": "success",
  "message": "Payment added successfully."
}
```

#### 400 Bad Request
La richiesta è malformata o mancano parametri obbligatori.
- **Esempio di risposta**:
```json
{
  "status": "error",
  "message": "Invalid request: missing parameters."
}
```

#### 500 Internal Server Error
Errore generico sul server.
- **Esempio di risposta**:
```json
{
  "status": "error",
  "message": "An unexpected error occurred."
}
```



---

&nbsp;



# Documentazione API: add_reservation.php

## Descrizione
Lo script `add_reservation.php` gestisce le richieste HTTP per aggiungere prenotazioni in un sistema di gestione parcheggi. Include la verifica dei token JWT per garantire la sicurezza delle richieste e interagisce con un database per registrare le informazioni relative alle prenotazioni.

---

## Configurazione iniziale
### Header HTTP
Lo script imposta i seguenti header:
- `Access-Control-Allow-Origin: *` — Permette richieste da tutte le origini.
- `Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE` — Consente vari metodi HTTP.
- `Access-Control-Allow-Headers: Content-Type, Authorization` — Consente intestazioni personalizzate, come i token JWT.

### Gestione delle richieste OPTIONS (preflight)
Se la richiesta è un preflight (`OPTIONS`):
- Risponde con un codice di stato `200 OK` senza eseguire altro codice.

---

## Endpoint: Aggiunta di una prenotazione
### Metodo HTTP: `POST`

### URL
`/add_reservation.php`

### Autenticazione
L'API richiede un token JWT valido nell'header `Authorization`.
- Formato dell'header:
  ```http
  Authorization: Bearer <token>
  ```

### Parametri richiesti
I parametri devono essere inviati nel corpo della richiesta in formato JSON:
- `id_user` (int) — L'ID dell'utente.
- `slot_location` (string) — La posizione dello slot del parcheggio.
- `start_time` (string) — L'orario di inizio della prenotazione (formato: `YYYY-MM-DD HH:MM:SS`).
- `end_time` (string) — L'orario di fine della prenotazione (formato: `YYYY-MM-DD HH:MM:SS`).
- `total_cost` (float) — Il costo totale della prenotazione.
- `targa` (string) — La targa del veicolo.

### Risposte
#### 200 OK
La prenotazione è stata aggiunta con successo.
- **Esempio di risposta**:
```json
{
  "success": true,
  "message": "Reservation added successfully"
}
```

#### 400 Bad Request
Uno o più campi richiesti sono mancanti o malformati.
- **Esempio di risposta**:
```json
{
  "success": false,
  "message": "Campo mancante"
}
```

#### 401 Unauthorized
Il token JWT è mancante, non valido o scaduto.
- **Esempio di risposta**:
```json
{
  "success": false,
  "message": "Token mancante"
}
```
Oppure:
```json
{
  "success": false,
  "message": "Token non valido o scaduto"
}
```

#### 405 Method Not Allowed
La richiesta non utilizza il metodo POST.
- **Esempio di risposta**:
```json
{
  "success": false,
  "message": "Metodo non consentito"
}
```

---


&nbsp;

# Documentazione API: add_targa.php

## Descrizione
Lo script `add_targa.php` consente di aggiungere un veicolo associato a un utente nel database. Include la verifica del token JWT per garantire la sicurezza delle richieste e gestisce i dati inviati nel corpo della richiesta in formato JSON.

---

## Configurazione iniziale
### Header HTTP
Lo script imposta i seguenti header:
- `Access-Control-Allow-Origin: *` — Permette richieste da tutte le origini.
- `Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE` — Consente vari metodi HTTP.
- `Access-Control-Allow-Headers: Content-Type, Authorization` — Consente intestazioni personalizzate, come i token JWT.

### Gestione delle richieste OPTIONS (preflight)
Se la richiesta è un preflight (`OPTIONS`):
- Risponde con un codice di stato `200 OK` senza eseguire altro codice.

---

## Endpoint: Aggiunta di un veicolo
### Metodo HTTP: `POST`

### URL
`/api/add_targa.php`



### Parametri richiesti
I parametri devono essere inviati nel corpo della richiesta in formato JSON:
- `id_user` (int) — L'ID dell'utente.
- `targa` (string) — La targa del veicolo.

### Risposte
#### 200 OK
Il veicolo è stato aggiunto con successo.
- **Esempio di risposta**:
```json
{
  "success": true,
  "message": "targa inserita correttamente"
}
```

#### 400 Bad Request
Uno o più campi richiesti sono mancanti o malformati.
- **Esempio di risposta**:
```json
{
  "success": false,
  "message": "Campo mancante"
}
```

#### 401 Unauthorized
Il token JWT è mancante, non valido o scaduto.
- **Esemppio di risposta**:
```json
{
  "success": false,
  "message": "Token non valido o scaduto"
}
```
Oppure:
```json
{
  "success": false,
  "message": "Token mancante"
}
```

#### 405 Method Not Allowed
La richiesta non utilizza il metodo POST.
- **Esempio di risposta**:
```json
{
  "success": false,
  "message": "Metodo non consentito"
}
```

---


&nbsp;

# API Endpoint: confirm_payment.php

## Descrizione
Questa API consente di aggiornare lo stato di una prenotazione a "paid" e di inserire una nuova transazione nella tabella dei pagamenti. L'endpoint accetta richieste POST con un token JWT nell'header per l'autenticazione.

---

## Metodo: POST

### Headers richiesti
| Nome             | Valore                  | Descrizione                               |
|------------------|-------------------------|-------------------------------------------|
| Authorization    | Bearer {token_jwt}     | Token JWT per autenticazione             |
| Content-Type     | application/json       | Tipo di contenuto richiesto              |

### Parametri richiesti (nel body JSON)
| Nome            | Tipo    | Descrizione                                                      |
|-----------------|---------|------------------------------------------------------------------|
| id_user         | Integer | L'ID dell'utente che sta effettuando il pagamento               |
| reservation_id  | Integer | L'ID della prenotazione da aggiornare                            |
| payment_method  | String  | Metodo di pagamento utilizzato (es. "carta di credito")         |
| amount          | Float   | Importo pagato                                                   |

---

## Risposte

### Risposta in caso di successo (200)
```json
{
  "success": true,
  "message": "Reservation and payment updated successfully"
}
```

### Risposta in caso di errore: Campo mancante (400)
```json
{
  "success": false,
  "message": "Campo mancante"
}
```

### Risposta in caso di errore: Token non valido/scaduto (401)
```json
{
  "success": false,
  "message": "Token non valido o scaduto"
}
```

### Risposta in caso di errore: Token mancante (401)
```json
{
  "success": false,
  "message": "Token mancante"
}
```

### Risposta in caso di metodo non consentito (405)
```json
{
  "success": false,
  "message": "Metodo non consentito"
}
```

---

&nbsp;


# Documentazione API: Rimozione Carta di Credito

Questa API permette di rimuovere un numero di carta di credito associato ad un utente, autenticando la richiesta tramite un token JWT.

## URL

`POST /api/delete_payment.php`

## Autenticazione

L'API richiede un token JWT valido per garantire che l'utente abbia i permessi necessari per eseguire l'operazione. Il token deve essere passato nell'intestazione `Authorization` come `Bearer Token`.

### Esempio di intestazione di autenticazione

```
Authorization: Bearer <JWT_Token>
```

## Parametri della richiesta

La richiesta deve essere inviata come `POST` con i seguenti parametri JSON nel corpo della richiesta:

| Campo        | Tipo   | Descrizione                                      | Esempio         |
|--------------|--------|--------------------------------------------------|-----------------|
| `id_user`    | int    | ID dell'utente per cui rimuovere il numero di carta. | 123             |
| `card_number`| string | Numero della carta di credito da rimuovere.      | "1234-5678-9876-5432" |

### Corpo della richiesta JSON

```json
{
  "id_user": 123,
  "card_number": "1234-5678-9876-5432"
}
```

## Risposta

La risposta dell'API sarà in formato JSON e conterrà un campo `success` per indicare se l'operazione è stata eseguita correttamente, insieme a un messaggio informativo.

### Risposte Possibili

#### Successo

```json
{
  "success": true,
  "message": "Numero carta rimosso con successo"
}
```

#### Nessun record trovato

```json
{
  "success": false,
  "message": "Nessuna carta trovata per l'utente specificato"
}
```

#### Mancano i parametri necessari

```json
{
  "success": false,
  "message": "Campo id_user o card_number mancante"
}
```

#### Token non valido o scaduto

```json
{
  "success": false,
  "message": "Token non valido o scaduto"
}
```



&nbsp;




# Documentazione API: Rimozione Veicolo (Targa)

Questa API consente di rimuovere un veicolo (identificato dalla targa) associato ad un utente, autenticando la richiesta tramite un token JWT.

## URL

`POST /api/delete_plates.php`

## Autenticazione

L'API richiede un token JWT valido per garantire che l'utente abbia i permessi necessari per eseguire l'operazione. Il token deve essere passato nell'intestazione `Authorization` come `Bearer Token`.

### Esempio di intestazione di autenticazione

```
Authorization: Bearer <JWT_Token>
```

## Parametri della richiesta

La richiesta deve essere inviata come `POST` con i seguenti parametri JSON nel corpo della richiesta:

| Campo        | Tipo   | Descrizione                                      | Esempio         |
|--------------|--------|--------------------------------------------------|-----------------|
| `id_user`    | int    | ID dell'utente per cui rimuovere il veicolo.     | 123             |
| `id_targa`   | int    | ID della targa del veicolo da rimuovere.         | 456             |

### Corpo della richiesta JSON

```json
{
  "id_user": 123,
  "id_targa": 456
}
```

## Risposta

La risposta dell'API sarà in formato JSON e conterrà un campo `success` per indicare se l'operazione è stata eseguita correttamente, insieme a un messaggio informativo.

### Risposte Possibili

#### Successo

```json
{
  "success": true,
  "message": "Targa rimossa con successo"
}
```

#### Nessun veicolo trovato

```json
{
  "success": false,
  "message": "Nessun veicolo trovato per l'utente specificato"
}
```

#### Mancano i parametri necessari

```json
{
  "success": false,
  "message": "Campo id_user o id_targa mancante"
}
```

#### Token non valido o scaduto

```json
{
  "success": false,
  "message": "Token non valido o scaduto"
}
```

&nbsp;




# Documentazione API: Ottenere Elenco Slot di Parcheggio

Questa API consente di ottenere l'elenco di tutti gli slot di parcheggio disponibili nel sistema, restituendo informazioni dettagliate su ciascuno slot.

## URL

`GET /api/get_slots.php`

## Autenticazione

Questa API non richiede autenticazione. È accessibile a chiunque faccia una richiesta GET.

## Parametri della richiesta

Questa API non richiede parametri nel corpo della richiesta. La richiesta è una semplice `GET`.

### Esempio di richiesta cURL

```bash
curl -X GET http://example.com/api/parking-slots
```

## Risposta

La risposta dell'API sarà in formato JSON e conterrà una lista di slot di parcheggio con i seguenti dettagli:

### Parametri nel corpo della risposta JSON

| Campo           | Tipo   | Descrizione                                      | Esempio         |
|-----------------|--------|--------------------------------------------------|-----------------|
| `slot_id`       | int    | ID univoco dello slot di parcheggio.            | 1               |
| `location`      | string | Descrizione della posizione dello slot di parcheggio. | "Slot 11" |
| `latitude`      | float  | Latitudine della posizione dello slot.          | 45.4642         |
| `longitude`     | float  | Longitudine della posizione dello slot.         | 9.1900          |
| `rate_per_hour` | float  | Tariffa oraria per lo slot di parcheggio.       | 2.50            |
| `is_occupied`   | bool   | Indica se lo slot è occupato.                    | false           |

### Esempio di risposta

```json
[
  {
    "slot_id": 1,
    "location": "Centro Storico",
    "latitude": 45.4642,
    "longitude": 9.1900,
    "rate_per_hour": 2.50,
    "is_occupied": false
  },
  {
    "slot_id": 2,
    "location": "Piazza Garibaldi",
    "latitude": 45.4700,
    "longitude": 9.2000,
    "rate_per_hour": 3.00,
    "is_occupied": true
  }
]
```

&nbsp;

# Documentazione API: Recupero Carte di Credito dell'Utente

Questa API consente di recuperare tutte le carte di credito associate a un determinato utente, utilizzando un identificativo utente (`id_user`) e un token JWT per l'autenticazione.

## URL

`POST /api/get_user_card_number.php`

## Autenticazione

L'API richiede un token JWT valido nel header `Authorization` della richiesta. Il token JWT deve essere passato come un **Bearer Token**.


## Parametri della richiesta

La richiesta deve includere un corpo JSON con il parametro `id_user`, che rappresenta l'ID dell'utente di cui si desidera recuperare le carte di credito.

| Campo   | Tipo   | Descrizione                        | Esempio  |
|---------|--------|------------------------------------|---------|
| `id_user` | int    | ID univoco dell'utente per cui si vogliono ottenere le carte di credito. | 123     |

### Esempio di corpo della richiesta

```json
{
  "id_user": 123
}
```

## Risposta

L'API restituirà una risposta in formato JSON contenente i dati delle carte di credito associate all'utente specificato. In caso di errore, verrà restituito un messaggio di errore.

### Risposte di Successo

Se l'utente è stato trovato e le carte di credito associate sono disponibili, la risposta conterrà un array di oggetti, ognuno dei quali rappresenta una carta di credito.

#### Esempio di risposta di successo

```json
{
  "success": true,
  "data": [
    {
      "card_id": 1,
      "user_id": 123,
      "card_number": "1234 5678 9876 5432",
      "card_type": "Visa",
      "expiry_date": "12/24"
    },
    {
      "card_id": 2,
      "user_id": 123,
      "card_number": "2345 6789 8765 4321",
      "card_type": "Mastercard",
      "expiry_date": "08/25"
    }
  ]
}
```

### Risposta di Errore

Se non viene trovato l'utente o il parametro `id_user` è mancante, verrà restituito un messaggio di errore con un codice di stato HTTP 400 o 401.

#### Esempio di risposta con errore

```json
{
  "success": false,
  "message": "Utente non trovato"
}
```

```json
{
  "success": false,
  "message": "Campo id_user mancante"
}
```

### Risposta di errore per un token non valido

Se il token JWT è mancante o non valido, l'API restituirà un errore di autorizzazione (HTTP 401).

#### Esempio di risposta con errore di token

```json
{
  "success": false,
  "message": "Token non valido o scaduto"
}
```

### Risposta di errore per metodo non consentito

Se la richiesta non è una richiesta `POST`, l'API restituirà un errore con il messaggio "Metodo non consentito".

#### Esempio di risposta con errore di metodo non consentito

```json
{
  "success": false,
  "message": "Metodo non consentito"
}
```



&nbsp;


# API: Recupero Dati Utente

Questa API permette di recuperare i dati di un utente specificato tramite il parametro `id_user`, utilizzando un token JWT per l'autenticazione.

## URL

`POST /api/get_user_info.php`

## Autenticazione

L'API richiede un token JWT valido che deve essere incluso nell'intestazione `Authorization` della richiesta. Il token deve essere passato come **Bearer Token**.



## Parametri della richiesta

La richiesta deve includere un corpo JSON con il parametro `id_user`, che rappresenta l'ID dell'utente per cui si desiderano recuperare i dati.

| Campo   | Tipo   | Descrizione                            | Esempio |
|---------|--------|----------------------------------------|--------|
| `id_user` | int    | ID univoco dell'utente di cui si vogliono ottenere i dati. | 123    |

### Esempio di corpo della richiesta

```json
{
  "id_user": 123
}
```

## Risposta

L'API restituirà una risposta in formato JSON contenente i dati dell'utente richiesto. In caso di errore, verrà restituito un messaggio di errore.

### Risposte di Successo

Se l'utente è stato trovato, la risposta conterrà i dettagli dell'utente.

#### Esempio di risposta di successo

```json
{
  "success": true,
  "data": {
    "id": 123,
    "first_name": "Mario",
    "last_name": "Rossi",
    "username": "Rossi",
    "email": "mario.rossi@example.com",
  }
}
```

### Risposta di Errore

Se non viene trovato l'utente o se il parametro `id_user` è mancante, verrà restituito un messaggio di errore.

#### Esempio di risposta con errore

```json
{
  "success": false,
  "message": "Utente non trovato"
}
```

```json
{
  "success": false,
  "message": "Campo mancante"
}
```

### Risposta di errore per token non valido

Se il token JWT è mancante o non valido, l'API restituirà un errore di autorizzazione (HTTP 401).

#### Esempio di risposta con errore di token

```json
{
  "success": false,
  "message": "Token non valido o scaduto"
}
```

### Risposta di errore per metodo non consentito

Se la richiesta non è una richiesta `POST`, l'API restituirà un errore con il messaggio "Metodo non consentito".

#### Esempio di risposta con errore di metodo non consentito

```json
{
  "success": false,
  "message": "Metodo non consentito"
}
```



# API: Recupero Veicoli per Utente

Questa API permette di recuperare i veicoli associati a un utente specificato tramite il parametro `id_user`, utilizzando un token JWT per l'autenticazione.

## URL

`POST /api/get_user_plates.php`

## Autenticazione

L'API richiede un token JWT valido che deve essere incluso nell'intestazione `Authorization` della richiesta. Il token deve essere passato come **Bearer Token**.



## Parametri della richiesta

La richiesta deve includere un corpo JSON con il parametro `id_user`, che rappresenta l'ID dell'utente per cui si desiderano recuperare i veicoli.

| Campo   | Tipo   | Descrizione                            | Esempio |
|---------|--------|----------------------------------------|--------|
| `id_user` | int    | ID univoco dell'utente di cui si vogliono ottenere i veicoli. | 123    |

### Esempio di corpo della richiesta

```json
{
  "id_user": 123
}
```

## Risposta

L'API restituirà una risposta in formato JSON contenente i dati dei veicoli associati all'utente. In caso di errore, verrà restituito un messaggio di errore.

### Risposte di Successo

Se i veicoli sono stati trovati per l'utente, la risposta conterrà i dettagli dei veicoli.

#### Esempio di risposta di successo

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "targa": "AB123CD"
    }
  ]
}
```

### Risposta di Errore

Se non vengono trovati veicoli per l'utente o se il parametro `id_user` è mancante, verrà restituito un messaggio di errore.

#### Esempio di risposta con errore

```json
{
  "success": false,
  "message": "Utente non trovato"
}
```

```json
{
  "success": false,
  "message": "Campo id_user mancante"
}
```

### Risposta di errore per token non valido

Se il token JWT è mancante o non valido, l'API restituirà un errore di autorizzazione (HTTP 401).

#### Esempio di risposta con errore di token

```json
{
  "success": false,
  "message": "Token non valido o scaduto"
}
```

### Risposta di errore per metodo non consentito

Se la richiesta non è una richiesta `POST`, l'API restituirà un errore con il messaggio "Metodo non consentito".

#### Esempio di risposta con errore di metodo non consentito

```json
{
  "success": false,
  "message": "Metodo non consentito"
}
```




# API: Recupero Pagamenti per Utente

Questa API permette di recuperare i pagamenti effettuati da un utente specificato tramite il parametro `id_user`, utilizzando un token JWT per l'autenticazione.

## URL

`POST /api/load_payments.php`

## Autenticazione

L'API richiede un token JWT valido che deve essere incluso nell'intestazione `Authorization` della richiesta. Il token deve essere passato come **Bearer Token**.



## Parametri della richiesta

La richiesta deve includere un corpo JSON con il parametro `id_user`, che rappresenta l'ID dell'utente per cui si desiderano recuperare i pagamenti.

| Campo   | Tipo   | Descrizione                            | Esempio |
|---------|--------|----------------------------------------|--------|
| `id_user` | int    | ID univoco dell'utente di cui si vogliono ottenere i pagamenti. | 123    |

### Esempio di corpo della richiesta

```json
{
  "id_user": 123
}
```

## Risposta

L'API restituirà una risposta in formato JSON contenente i dettagli dei pagamenti effettuati. In caso di errore, verrà restituito un messaggio di errore.

### Risposte di Successo

Se i pagamenti sono stati trovati per l'utente, la risposta conterrà i dettagli dei pagamenti.

#### Esempio di risposta di successo

```json
{
  "success": true,
  "data": [
    {
      "payment_id": 1,
      "amount": 200.00,
      "payment_date": "2022-01-15",
      "payment_method": "2022-01-15",
      "reservation_id": 200.00,
      "payment_status" : "pending"
    }
  ]
}
```

### Risposta di Errore

Se non vengono trovati pagamenti per l'utente o se il parametro `id_user` è mancante, verrà restituito un messaggio di errore.

#### Esempio di risposta con errore

```json
{
  "success": false,
  "message": "Nessun pagamento trovato"
}
```

```json
{
  "success": false,
  "message": "Campo mancante"
}
```

### Risposta di errore per token non valido

Se il token JWT è mancante o non valido, l'API restituirà un errore di autorizzazione (HTTP 401).

#### Esempio di risposta con errore di token

```json
{
  "success": false,
  "message": "Token non valido o scaduto"
}
```

### Risposta di errore per metodo non consentito

Se la richiesta non è una richiesta `POST`, l'API restituirà un errore con il messaggio "Metodo non consentito".

#### Esempio di risposta con errore di metodo non consentito

```json
{
  "success": false,
  "message": "Metodo non consentito"
}
```



# API: Recupero Prenotazione per Utente

Questa API permette di recuperare i dettagli di una prenotazione effettuata da un utente specificato tramite i parametri `id_user` e `reservation_id`, utilizzando un token JWT per l'autenticazione.

## URL

`POST /api/load_reservation_info.php`

## Autenticazione

L'API richiede un token JWT valido che deve essere incluso nell'intestazione `Authorization` della richiesta. Il token deve essere passato come **Bearer Token**.


```

## Parametri della richiesta

La richiesta deve includere un corpo JSON con i seguenti parametri:

| Campo           | Tipo   | Descrizione                            | Esempio |
|-----------------|--------|----------------------------------------|--------|
| `id_user`       | int    | ID univoco dell'utente per cui si desidera recuperare la prenotazione. | 123    |
| `reservation_id`| int    | ID della prenotazione da recuperare.   | 456    |

### Esempio di corpo della richiesta

```json
{
  "id_user": 123,
  "reservation_id": 456
}
```

## Risposta

L'API restituirà una risposta in formato JSON contenente i dettagli della prenotazione. In caso di errore, verrà restituito un messaggio di errore.

### Risposte di Successo

Se la prenotazione è stata trovata per l'utente, la risposta conterrà i dettagli della prenotazione.

#### Esempio di risposta di successo

```json
{
  "success": true,
  "data": [
    {
      "reservation_id": 456,
      "user_id": 123,
      "slot_id" : 1,
      "start_time" : ,
      "end_time": ,
      "total_cost": ,
      "payment_date": ,
      "status": "completed",
      "created_at": ,
      "updated_at":,
      "plate_id":,
      "total_cost": 200.00
    }
  ]
}
```

### Risposta di Errore

Se non viene trovata la prenotazione per l'utente, oppure se un parametro necessario (`id_user` o `reservation_id`) è mancante, verrà restituito un messaggio di errore.

#### Esempio di risposta con errore

```json
{
  "success": false,
  "message": "Nessuna prenotazione trovata"
}
```

```json
{
  "success": false,
  "message": "Campo mancante"
}
```

### Risposta di errore per token non valido

Se il token JWT è mancante o non valido, l'API restituirà un errore di autorizzazione (HTTP 401).

#### Esempio di risposta con errore di token

```json
{
  "success": false,
  "message": "Token non valido o scaduto"
}
```

### Risposta di errore per metodo non consentito

Se la richiesta non è una richiesta `POST`, l'API restituirà un errore con il messaggio "Metodo non consentito".

#### Esempio di risposta con errore di metodo non consentito

```json
{
  "success": false,
  "message": "Metodo non consentito"
}
```

#

# API: Recupero Prenotazioni in Attesa o Completate

Questa API permette di recuperare le prenotazioni effettuate da un utente specificato tramite il parametro `id_user`, filtrando per lo stato della prenotazione (attesa o completata) e il pagamento in sospeso.

## URL

`POST /api/load_reservation.php`

## Autenticazione

L'API richiede un token JWT valido che deve essere incluso nell'intestazione `Authorization` della richiesta. Il token deve essere passato come **Bearer Token**.



## Parametri della richiesta

La richiesta deve includere un corpo JSON con i seguenti parametri:

| Campo           | Tipo   | Descrizione                            | Esempio |
|-----------------|--------|----------------------------------------|--------|
| `id_user`       | int    | ID univoco dell'utente per cui si desidera recuperare le prenotazioni. | 123    |

### Esempio di corpo della richiesta

```json
{
  "id_user": 123
}
```

## Risposta

L'API restituirà una risposta in formato JSON contenente i dettagli delle prenotazioni che soddisfano i criteri di stato e pagamento. In caso di errore, verrà restituito un messaggio di errore.

### Risposte di Successo

Se vengono trovate prenotazioni corrispondenti ai criteri (pagamenti in sospeso e prenotazioni confermate o completate), la risposta conterrà i dettagli delle prenotazioni.



### Risposta di Errore

Se non vengono trovate prenotazioni che soddisfano i criteri o se il campo `id_user` è mancante, verrà restituito un messaggio di errore.

#### Esempio di risposta con errore

```json
{
  "success": false,
  "message": "Nessun pagamento trovato"
}
```

```json
{
  "success": false,
  "message": "Campo mancante"
}
```

### Risposta di errore per token non valido

Se il token JWT è mancante o non valido, l'API restituirà un errore di autorizzazione (HTTP 401).

#### Esempio di risposta con errore di token

```json
{
  "success": false,
  "message": "Token non valido o scaduto"
}
```

### Risposta di errore per metodo non consentito

Se la richiesta non è una richiesta `POST`, l'API restituirà un errore con il messaggio "Metodo non consentito".

#### Esempio di risposta con errore di metodo non consentito

```json
{
  "success": false,
  "message": "Metodo non consentito"
}
```



## Documentazione API: Creazione Pagamento per Prenotazione

Questa API consente di registrare un pagamento per una prenotazione specifica. Il pagamento è associato a una prenotazione tramite il suo `reservation_id` e al `user_id`. L'API richiede un token JWT per l'autenticazione e accetta una richiesta `POST` con dettagli del pagamento.

### URL
`POST /api/pay_reservation.php`


### Parametri della richiesta

La richiesta deve includere un corpo JSON con i seguenti parametri:

| Campo            | Tipo   | Descrizione                                    | Esempio |
|------------------|--------|------------------------------------------------|--------|
| `id_user`        | int    | ID dell'utente per cui si effettua il pagamento | 123    |
| `reservation_id` | int    | ID della prenotazione per cui si effettua il pagamento | 456    |

#### Esempio di corpo della richiesta

```json
{
  "id_user": 123,
  "reservation_id": 456
}
```


### Risposta di Errore

Se manca un parametro obbligatorio nel corpo della richiesta o se si verificano altri errori, l'API restituirà un messaggio di errore.

#### Esempio di risposta con errore (campo mancante)

```json
{
  "success": false,
  "message": "Campo mancante"
}
```

#### Esempio di risposta con errore (token non valido o mancante)

```json
{
  "success": false,
  "message": "Token non valido o scaduto"
}
```

### Risposta di errore per metodo non consentito

Se la richiesta non è un `POST`, l'API restituirà un errore.

```json
{
  "success": false,
  "message": "Metodo non consentito"
}
```
