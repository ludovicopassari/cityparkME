CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,       -- Identificativo univoco per ogni utente
    first_name VARCHAR(50),               
    last_name VARCHAR(50),               
    username VARCHAR(50) UNIQUE NOT NULL,    -- Nome utente, deve essere unico
    email VARCHAR(50) UNIQUE NOT NULL,    -- email, deve essere unico
    password_hash VARCHAR(255) NOT NULL,     -- Hash della password per motivi di sicurezza
    salt VARCHAR(50) NOT NULL,                -- Salt per la generazione dell'hash della password
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Data di creazione del record
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Data di aggiornamento
    two_factor_enabled BOOLEAN DEFAULT FALSE -- Richiede autenticazione a due fattori
);


CREATE TABLE otp_transactions (
    transaction_id VARCHAR(12) PRIMARY KEY,       -- Identificativo univoco della transazione
    user_id INT NOT NULL,                         -- Riferimento all'utente
    otp_code VARCHAR(10) NOT NULL,               -- Codice OTP generato
    expiration_time TIMESTAMP NOT NULL,          -- Tempo di scadenza dell'OTP
    is_used TINYINT(1) NOT NULL DEFAULT 0,       -- Indica se l'OTP è stato utilizzato (0 = no, 1 = sì)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp di creazione della transazione
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE -- Collegamento alla tabella 'users'
);

CREATE TABLE credit_cards (
    card_id INT AUTO_INCREMENT PRIMARY KEY,  -- ID della carta
    user_id INT,                             -- ID utente che possiede la carta
    card_number VARCHAR(20) NOT NULL,        -- Numero della carta
    expiration_date DATE,                    -- Data di scadenza della carta
    card_type ENUM('VISA', 'MasterCard', 'AMEX') NOT NULL, -- Tipo di carta
    FOREIGN KEY (user_id) REFERENCES users(id)
);


CREATE TABLE veichle (
    id INT AUTO_INCREMENT PRIMARY KEY,       -- Identificativo univoco per ogni auto
    user_id INT NOT NULL,                    -- Riferimento all'utente nella tabella 'utenti'
    targa VARCHAR(20) UNIQUE NOT NULL,       -- Targa dell'auto, deve essere unica
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE -- Collegamento alla tabella 'utenti'
);


CREATE TABLE parking_slots (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,   -- ID dello stallo
    location VARCHAR(255),                     -- Posizione (ad esempio, "Zona A1")
    latitude DECIMAL(10, 6),                   -- Latitudine (per localizzazione sulla mappa)
    longitude DECIMAL(10, 6),                  -- Longitudine
    rate_per_hour DECIMAL(10, 2) NOT NULL,     -- Tariffa oraria
    is_occupied BOOLEAN DEFAULT FALSE,         -- Stato dello stallo (libero/occupato)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,  -- ID del pagamento
    reservation_id INT NOT NULL,                -- ID della prenotazione
    payment_method ENUM('credit_card', 'paypal', 'bank_transfer') NOT NULL, -- Metodo di pagamento
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Data del pagamento
    amount DECIMAL(10, 2) NOT NULL,             -- Importo pagato
    payment_status ENUM('successful', 'failed') DEFAULT 'successful',  -- Stato del pagamento
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id) -- Riferimento alla prenotazione
);

CREATE TABLE reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,   -- ID della prenotazione
    user_id INT NOT NULL,                            -- ID dell'utente
    slot_id INT NOT NULL,                            -- ID dello stallo
    start_time TIMESTAMP NOT NULL,                   -- Orario di inizio
    end_time TIMESTAMP NOT NULL,                     -- Orario di fine
    total_cost DECIMAL(10, 2) NOT NULL,              -- Costo totale della prenotazione
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending', -- Stato del pagamento
    plates_id INT NOT NULL,
    status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed', -- Stato della prenotazione
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Data di creazione della prenotazione
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Data di aggiornamento
    FOREIGN KEY (user_id) REFERENCES users(user_id), -- Riferimento all'utente
    FOREIGN KEY (slot_id) REFERENCES parking_slots(slot_id) -- Riferimento allo stallo
    FOREIGN KEY (plates_id) REFERENCES veichle(id)
);
