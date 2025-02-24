// Funzione di decodifica JWT
function decodeJWT(token) {
    const parts = token.split(".");
    if (parts.length !== 3) throw new Error("Token JWT non valido");
    const payload = JSON.parse(atob(parts[1]));
    return { payload };
}

// Funzione per verificare la scadenza del token JWT
function isTokenValid(decoded) {
    const currentTime = Math.floor(Date.now() / 1000);
    if (decoded.payload.exp < currentTime) {
        sessionStorage.removeItem("jwt_token");
        window.location.href = "/login.html";
        return false;
    }
    return true;
}

try {
    // Recupera il token JWT dal localStorage
    const token = localStorage.getItem("jwt_token");

    // Verifica se il token esiste
    if (!token) {
        throw new Error("Token non trovato");
    }

    // Decodifica il token (se vuoi farlo)
    const decodedToken = decodeJWT(token);
    isTokenValid(decodedToken);
} catch (error) {
    // Gestione dell'errore
    sessionStorage.removeItem("jwt_token");
    window.location.href = "/login.html";
    console.error("Errore:", error.message);
}

// Funzione per il caricamento della navbar
async function loadNavbar() {
    try {
        const response = await fetch("navbar.html");
        const data = await response.text();
        document.getElementById("navbar").innerHTML = data;
    } catch (error) {
        console.error("Errore nel caricamento della navbar:", error);
    }
}

// Funzione per effettuare le richieste fetch con gestione dell'autenticazione
async function fetchData(url, body) {
    const token = localStorage.getItem("jwt_token");
    if (!token) {
        console.log("Token non trovato. Redireziona al login.");
        window.location.href = "/login.php";
        return;
    }

    const headers = {
        "Content-Type": "application/json",
        Authorization: "Bearer " + token,
    };

    try {
        const response = await fetch(url, {
            method: "POST",
            headers,
            body: JSON.stringify(body),
        });
        if (!response.ok) throw new Error("Errore nella richiesta HTTP");
        return await response.json();
    } catch (error) {
        console.error("Errore:", error);
    }
}

// Funzione per verificare la scadenza del token JWT
function isTokenValid(decoded) {
    const currentTime = Math.floor(Date.now() / 1000);
    if (decoded.payload.exp < currentTime) {
        window.location.href = "/login.html";
        sessionStorage.removeItem("jwt_token");
        return false;
    }
    return true;
}

// Funzione per gestire il caricamento delle informazioni utente
async function loadUserInfo() {
    const token = localStorage.getItem("jwt_token");
    const decoded = decodeJWT(token);
    if (!isTokenValid(decoded)) return;

    const data = await fetchData(
        "http://localhost:8080/api/get_user_info.php",
        { id_user: decoded.payload.user_id }
    );
    if (data) {
        document.getElementById("first_name").innerHTML = data.data.first_name;
        document.getElementById("last_name").innerHTML = data.data.last_name;
        document.getElementById("email").innerHTML = data.data.email;
    }
}

// Funzione per caricare le targhe utente
async function loadPlates() {
    const token = localStorage.getItem("jwt_token");
    const decoded = decodeJWT(token);
    if (!isTokenValid(decoded)) return;

    const data = await fetchData(
        "http://localhost:8080/api/get_user_plates.php",
        { id_user: decoded.payload.user_id }
    );
    const plates = data?.data || [];
    const list = document.getElementById("lista_targhe");
    list.innerHTML = ""; // Pulisci la lista esistente

    plates.forEach((item) => {
        const li = document.createElement("li");
        const deleteBtn = document.createElement("button");
        li.textContent = item.targa;
        li.className =
            "d-flex justify-content-between align-items-center m-2 border p-2";
        deleteBtn.className = "btn btn-danger btn-sm";
        deleteBtn.id = item.id;
        deleteBtn.textContent = "Rimuovi";
        li.appendChild(deleteBtn);
        list.appendChild(li);

        deleteBtn.addEventListener("click", async () => {
            await removePlate(deleteBtn.id, decoded.payload.user_id);
            li.remove(); // Rimuove la targa dalla lista subito dopo la cancellazione
        });
    });
}

// Funzione per aggiungere una nuova targa
async function addPlate() {
    const token = localStorage.getItem("jwt_token");
    const decoded = decodeJWT(token);
    const targaInput = document.getElementById("nuovaTarga");
    const targaValue = targaInput.value.trim();
    if (!targaValue) return;

    const data = await fetchData("http://localhost:8080/api/add_targa.php", {
        id_user: decoded.payload.user_id,
        targa: targaValue,
    });
    if (data) {
        alert("Targa inserita con successo.");
        loadPlates(); // Ricarica la lista delle targhe per aggiornare il contenuto del modale
    }
}

// Funzione per rimuovere una targa
async function removePlate(plateId, userId) {
    // Prevenire la chiusura automatica del modale quando clicchi su "Rimuovi"
    event.stopPropagation();
    const data = await fetchData(
        "http://localhost:8080/api/delete_plates.php",
        { id_user: userId, id_targa: plateId }
    );
    if (data) {
        alert("Targa Rimossa");
        loadPlates(); // Ricarica la lista delle targhe per aggiornare il contenuto del modale
    }
}

//Funzione per caricare le carte dell'utente
async function loadCard_number() {
    const token = localStorage.getItem("jwt_token");
    const decoded = decodeJWT(token);
    if (!isTokenValid(decoded)) return;
    const data = await fetchData(
        "http://localhost:8080/api/get_user_card_number.php",
        { id_user: decoded.payload.user_id }
    );
    const card_numbers = data?.data || [];
    const list = document.getElementById("lista_card_number");
    list.innerHTML = ""; // Pulisci la lista esistente

    card_numbers.forEach((item) => {
        console.log(item);
        const li = document.createElement("li");
        const deleteBtn = document.createElement("button");
        li.textContent = item.card_number;
        li.className =
            "d-flex justify-content-between align-items-center m-2 border p-2";
        deleteBtn.className = "btn btn-danger btn-sm";
        deleteBtn.id = item.card_number;
        console.log(item.card_number);
        deleteBtn.textContent = "Rimuovi";
        li.appendChild(deleteBtn);
        list.appendChild(li);

        deleteBtn.addEventListener("click", async () => {
            await removeCard_number(deleteBtn.id, decoded.payload.user_id);
            li.remove(); // Rimuove la carta dalla lista subito dopo la cancellazione
        });
    });
}

async function addCard_number() {
    const token = localStorage.getItem("jwt_token");
    const decoded = decodeJWT(token);
    const cardNumberInput = document.getElementById("nuovoMetodo");
    const cardNumberValue = cardNumberInput.value.trim();
    if (!cardNumberValue) return;
    const data = await fetchData("http://localhost:8080/api/add_payment.php", {
        id_user: decoded.payload.user_id,
        card_number: cardNumberValue,
    });
    if (data) {
        alert("Numero di carta inserita con successo.");
        loadCard_number(); // Ricarica la lista delle targhe per aggiornare il contenuto del modale
    }
}

async function removeCard_number(card_number, userId) {
    // Prevenire la chiusura automatica del modale quando clicchi su "Rimuovi"
    event.stopPropagation();
    const data = await fetchData(
        "http://localhost:8080/api/delete_payment.php",
        { id_user: userId, card_number: card_number }
    );
    if (data) {
        alert("Carta  Rimossa");
        loadCard_number(); // Ricarica la lista delle targhe per aggiornare il contenuto del modale
    }
}

// Funzione di inizializzazione della pagina
window.addEventListener("load", () => {
    loadNavbar();
    loadUserInfo();

    // Modal targhe
    const targheModal = document.getElementById("targheModal");
    targheModal.addEventListener("shown.bs.modal", loadPlates);

    // Gestione aggiunta targa
    document.getElementById("add-targa").addEventListener("click", addPlate);
    //Modal pagamento
    const pagamentoModal = document.getElementById("pagamentoModal");
    pagamentoModal.addEventListener("shown.bs.modal", loadCard_number);
    document
        .getElementById("add-paymentmethod")
        .addEventListener("click", addCard_number);
});
