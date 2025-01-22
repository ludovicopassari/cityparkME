// Costanti principali
const BASE_API_URL = "http://localhost:8080/api";
const MAP_CENTER_COORDS = [41.9028, 12.4964]; // Centro mappa su Roma
const DEFAULT_ZOOM_LEVEL = 13;

// Inizializza la mappa
const map = L.map("map").setView(MAP_CENTER_COORDS, DEFAULT_ZOOM_LEVEL);
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
}).addTo(map);

// Utility per la gestione del token JWT
const TokenUtils = {
    getToken() {
        const token = localStorage.getItem("jwt_token");
        if (!token) {
            console.error("Token non trovato.");
            window.location.href = "/login.html";
        }
        return token;
    },

    decode(token) {
        const parts = token.split(".");
        if (parts.length !== 3) throw new Error("Token JWT non valido");
        return JSON.parse(atob(parts[1]));
    },

    isValid(decodedToken) {
        const currentTime = Math.floor(Date.now() / 1000);
        if (decodedToken.exp < currentTime) {
            localStorage.removeItem("jwt_token");
            window.location.href = "/login.html";
            return false;
        }
        return true;
    },
};

// Funzione generica per le richieste API
async function fetchData(url, method = "POST", body = null) {
    const token = TokenUtils.getToken();
    if (!token) return;

    const headers = {
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
    };

    try {
        const response = await fetch(url, {
            method,
            headers,
            body: JSON.stringify(body),
        });
        if (!response.ok) throw new Error("Errore nella richiesta API");
        return await response.json();
    } catch (error) {
        console.error("Errore:", error);
    }
}

// Funzione per recuperare gli slot di parcheggio
async function fetchSlots() {
    try {
        const response = await fetch(`${BASE_API_URL}/get_slots.php`);
        const slots = await response.json();

        // Per ogni slot, crea un marker sulla mappa
        slots.forEach((slot) => {
            if (slot.latitude && slot.longitude) {
                // Crea un'icona personalizzata per i marker
                const markerOptions = slot.is_occupied
                    ? {
                          icon: L.icon({
                              iconUrl: "http://localhost:8082/assets/red.png", // Usa un'icona rossa per i marker occupati
                              iconSize: [35, 35], // Dimensioni dell'icona
                              iconAnchor: [12, 25], // Punto di ancoraggio dell'icona
                              popupAnchor: [0, -25], // Posizionamento del popup
                          }),
                          clickable: false, // I marker occupati non sono cliccabili
                      }
                    : {
                          icon: L.icon({
                              iconUrl: "http://localhost:8082/assets/green.png", // Usa un'icona rossa per i marker occupati
                              iconSize: [35, 35], // Dimensioni dell'icona
                              iconAnchor: [12, 25], // Punto di ancoraggio dell'icona
                              popupAnchor: [0, -25], // Posizionamento del popup
                          }),
                          clickable: true,
                      };

                // Crea un marker con le opzioni precedenti
                const marker = L.marker(
                    [slot.latitude, slot.longitude],
                    markerOptions
                ).addTo(map);

                // Crea un popup con le informazioni dello slot
                marker.bindPopup(`
                    <strong>${slot.location}</strong><br>
                    Tariffa: €${slot.rate_per_hour}/h<br>
                    Stato: ${slot.is_occupied ? "Occupato" : "Libero"}
                `);

                // Se lo slot non è occupato, gestisci il click sul marker
                if (!slot.is_occupied) {
                    marker.on("click", () => {
                        document.getElementById("stallName").textContent =
                            slot.location;
                        document.getElementById("stallName2").textContent =
                            slot.location;
                        const parkingForm =
                            document.getElementById("parkingForm");

                        // Gestione della prenotazione
                        parkingForm.addEventListener("submit", (event) => {
                            event.preventDefault();
                            const startTime =
                                document.getElementById("startTime").value;
                            const endTime =
                                document.getElementById("endTime").value;

                            // Calcola la differenza tra orario di inizio e fine in ore
                            const start = new Date(
                                `1970-01-01T${startTime}:00`
                            );
                            const end = new Date(`1970-01-01T${endTime}:00`);
                            const hoursDiff = (end - start) / (1000 * 60 * 60);

                            if (hoursDiff > 0) {
                                const totalCost =
                                    slot.rate_per_hour * hoursDiff;
                                document.getElementById(
                                    "stallImport"
                                ).textContent = `€${totalCost.toFixed(2)}`;
                            } else {
                                alert(
                                    "Orario di fine deve essere successivo all'orario di inizio."
                                );
                            }
                        });
                    });
                }
            }
        });
    } catch (error) {
        console.error("Errore nel caricamento degli slot:", error);
    }
}

function centerMapOnUser() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                map.setView([latitude, longitude], 18);
                L.marker([latitude, longitude])
                    .addTo(map)
                    .bindPopup("La tua posizione")
                    .openPopup();
            },
            (error) => {
                console.error("Errore nella geolocalizzazione:", error);
                map.setView(MAP_CENTER_COORDS, DEFAULT_ZOOM_LEVEL);
            }
        );
    } else {
        console.error("Geolocalizzazione non supportata dal browser.");
        map.setView(MAP_CENTER_COORDS, DEFAULT_ZOOM_LEVEL);
    }
}

// Funzioni per la gestione delle prenotazioni
async function loadReservationInfo(userId, reservationId) {
    const data = await fetchData(
        `${BASE_API_URL}/load_reservation_info.php`,
        "POST",
        { id_user: userId, reservation_id: reservationId }
    );
    if (data && data.data.length > 0) {
        const reservation = data.data[0];
        document.getElementById("price").innerHTML = reservation.total_cost;
    }
}

async function loadReservation() {
    const token = TokenUtils.getToken();
    const decoded = TokenUtils.decode(token);
    if (!TokenUtils.isValid(decoded)) return;

    const data = await fetchData(
        `${BASE_API_URL}/load_reservation.php`,
        "POST",
        {
            id_user: decoded.user_id,
        }
    );
    if (!data || !data.data) return;

    const tableBody = document.getElementById("table-body-reservation");
    tableBody.innerHTML = "";

    data.data.forEach((item) => {
        const row = document.createElement("tr");
        row.innerHTML = `
                    <th scope="row">${item.reservation_id}</th>
                    <td>${item.slot_id}</td>
                    <td>${item.start_time || "N/A"}</td>
                    <td>${item.end_time || "N/A"}</td>
                `;

        if (item.payment_status === "pending" && item.status === "completed") {
            const payButton = document.createElement("button");
            payButton.id = item.reservation_id;
            payButton.classList.add("btn", "btn-primary");
            payButton.textContent = "Paga";
            payButton.setAttribute("data-bs-toggle", "modal");
            payButton.setAttribute("data-bs-target", "#paymentModal");
            payButton.addEventListener("click", () =>
                loadReservationInfo(decoded.user_id, item.reservation_id)
            );

            const buttonCell = document.createElement("td");
            buttonCell.appendChild(payButton);
            row.appendChild(buttonCell);
        } else {
            row.innerHTML += "<td></td>";
        }

        tableBody.appendChild(row);
    });
}

// Funzioni per la gestione dei pagamenti
async function loadPayments() {
    const token = TokenUtils.getToken();
    const decoded = TokenUtils.decode(token);
    if (!TokenUtils.isValid(decoded)) return;

    const data = await fetchData(`${BASE_API_URL}/load_payments.php`, "POST", {
        id_user: decoded.user_id,
    });
    if (!data || !data.data) return;

    const tableBody = document.getElementById("table-body");
    tableBody.innerHTML = "";

    data.data.forEach((item) => {
        const row = document.createElement("tr");
        row.innerHTML = `
                    <td>${item.reservation_id}</td>
                    <td>${item.payment_date || "N/A"}</td>
                    <td>${item.payment_status}</td>
                    <td>${item.total_cost}</td>
                `;
        tableBody.appendChild(row);
    });
}

async function loadCreditCards() {
    const token = TokenUtils.getToken();
    const decoded = TokenUtils.decode(token);
    if (!TokenUtils.isValid(decoded)) return;

    const data = await fetchData(
        `${BASE_API_URL}/get_user_card_number.php`,
        "POST",
        { id_user: decoded.user_id }
    );
    if (!data || !data.data) return;

    const creditCardList = document.getElementById("creditCardList");
    creditCardList.innerHTML =
        '<option value="default" disabled selected>Seleziona una carta</option>';

    data.data.forEach((card) => {
        const option = document.createElement("option");
        option.value = card.card_number;
        option.textContent = `${card.card_type} ****${card.card_number.slice(
            -4
        )}`;
        creditCardList.appendChild(option);
    });

    creditCardList.disabled = false;
}

async function loadPlates() {
    const token = TokenUtils.getToken();
    const decoded = TokenUtils.decode(token);
    if (!TokenUtils.isValid(decoded)) return;
    const data = await fetchData(
        `${BASE_API_URL}/get_user_plates.php`,
        "POST",
        {
            id_user: decoded.user_id,
        }
    );
    const platesList = document.getElementById("platesList");
    platesList.innerHTML =
        '<option value="default" disabled selected>Seleziona una targa</option>';
    data.data.forEach((plate) => {
        const option = document.createElement("option");
        option.value = plate.targa;
        option.textContent = `${plate.targa.slice(0, 3)}***${plate.targa.slice(
            -3
        )}`;
        platesList.appendChild(option);
    });

    platesList.disabled = false;
}

// Funzione per caricare la navbar
async function loadNavbar() {
    try {
        const response = await fetch("navbar.html");
        const data = await response.text();
        document.getElementById("navbar").innerHTML = data;
    } catch (error) {
        console.error("Errore nel caricamento della navbar:", error);
    }
}

async function confirmPayment() {
    const paymentMethod = document.getElementById("paymentMethod").value;
    const token = TokenUtils.getToken();
    const decoded = TokenUtils.decode(token);
    if (!TokenUtils.isValid(decoded)) return;

    const reservationId = document
        .querySelector("#paymentModal")
        .getAttribute("data-reservation-id");
    if (!reservationId || !paymentMethod) {
        console.error("Dati di pagamento non validi.");
        return;
    }

    const response = await fetchData(
        `${BASE_API_URL}/confirm_payment.php`,
        "POST",
        {
            id_user: decoded.user_id,
            reservation_id: reservationId,
            payment_method: paymentMethod,
            amount: document.getElementById("price").textContent,
        }
    );

    if (response && response.success) {
        alert("Pagamento effettuato con successo!");
        loadReservation(); // Aggiorna la lista delle prenotazioni
        loadPayments(); // Aggiorna la lista dei pagamenti
        document.getElementById("paymentModal").modal("hide"); // Chiudi il modal
    } else {
        alert("Errore nel pagamento. Riprova.");
    }
}

async function confirmReservation() {
    let total_cost = document.getElementById("stallImport").innerText;
    total_cost = total_cost.replace("€", "").trim();
    total_cost = parseFloat(total_cost);
    const slot_location = document.getElementById("stallName2").innerText;
    let start_time = document.getElementById("startTime").value;
    let end_time = document.getElementById("endTime").value;
    const targa = document.getElementById("platesList").value;
    let currentDate = new Date().toISOString().split("T")[0]; // Prende la data corrente in formato "YYYY-MM-DD"

    start_time = `${currentDate} ${start_time}:00`; // Aggiungi ore, minuti e secondi
    end_time = `${currentDate} ${end_time}:00`;
    // Validazione base dei campi
    if (!total_cost || !slot_location || !start_time || !end_time || !targa) {
        alert("Per favore, compila tutti i campi richiesti.");
        return;
    }

    const token = TokenUtils.getToken();
    const decoded = TokenUtils.decode(token);
    if (!TokenUtils.isValid(decoded)) return;
    console.log(
        JSON.stringify({
            id_user: decoded.user_id,
            total_cost: total_cost,
            slot_location: slot_location,
            start_time: start_time,
            end_time: end_time,
            targa: targa,
        })
    );

    try {
        const response = await fetchData(
            `${BASE_API_URL}/add_reservation.php`,
            "POST",
            {
                id_user: decoded.user_id,
                total_cost: total_cost,
                slot_location: slot_location,
                start_time: start_time,
                end_time: end_time,
                targa: targa,
            }
        );

        if (response && response.success) {
            //document.getElementById('paymentModal').modal('hide'); // Chiudi il modal
            fetchSlots(); // Aggiorna gli slot sulla mappa
            alert("Prenotazione effettuata con successo.");
        } else {
            alert("Errore nella prenotazione. Riprova.");
        }
    } catch (error) {
        alert("Si è verificato un errore nella richiesta. Riprova più tardi.");
    }
}

// Event listeners per i modali
document.getElementById("dataModal").addEventListener("shown.bs.modal", () => {
    loadPayments();
    loadReservation();
});

document
    .getElementById("reservationModal")
    .addEventListener("shown.bs.modal", () => {
        loadPlates();
    });

document
    .getElementById("paymentModal")
    .addEventListener("shown.bs.modal", (event) => {
        const button = event.relatedTarget; // Bottone che ha aperto il modal
        const reservationId = button.getAttribute("id"); // L'ID della prenotazione è nell'attributo ID del bottone
        const modal = document.querySelector("#paymentModal");
        modal.setAttribute("data-reservation-id", reservationId); // Imposta l'attributo data-reservation-id
        loadCreditCards(); // Carica le carte di credito
    });

// Inizializzazione
loadNavbar();
centerMapOnUser();
fetchSlots();

/* // Costanti principali
const BASE_API_URL = 'http://localhost:8080/api'; // URL di base per le API
const MAP_CENTER_COORDS = [41.9028, 12.4964]; // Coordinate per centrare la mappa su Roma
const DEFAULT_ZOOM_LEVEL = 30; // Livello di zoom predefinito della mappa

// Inizializza la mappa con il centro e il livello di zoom predefiniti
const map = L.map('map').setView(MAP_CENTER_COORDS, DEFAULT_ZOOM_LEVEL);

// Aggiungi il layer di OpenStreetMap alla mappa
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, // Imposta il massimo livello di zoom
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' // Attributo per OpenStreetMap
}).addTo(map);

// Utility per la gestione del token JWT
const TokenUtils = {
    // Recupera il token JWT dal localStorage
    getToken() {
        const token = localStorage.getItem('jwt_token');
        if (!token) {
            console.error("Token non trovato.");
            window.location.href = '/login.html'; // Reindirizza alla pagina di login se il token non è presente
        }
        return token;
    },

    // Decodifica il token JWT
    decode(token) {
        const parts = token.split('.');
        if (parts.length !== 3) throw new Error('Token JWT non valido');
        return JSON.parse(atob(parts[1])); // Decodifica la parte centrale del token
    },

    // Verifica se il token è valido (non scaduto)
    isValid(decodedToken) {
        const currentTime = Math.floor(Date.now() / 1000); // Ottieni il tempo corrente in secondi
        if (decodedToken.exp < currentTime) {
            localStorage.removeItem('jwt_token');
            window.location.href = '/login.html'; // Reindirizza alla pagina di login se il token è scaduto
            return false;
        }
        return true;
    }
};

// Funzione generica per effettuare richieste API
async function fetchData(url, method = 'POST', body = null) {
    const token = TokenUtils.getToken(); // Ottieni il token
    if (!token) return;

    const headers = {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}` // Aggiungi il token nell'header della richiesta
    };

    try {
        const response = await fetch(url, { method, headers, body: JSON.stringify(body) });
        if (!response.ok) throw new Error('Errore nella richiesta API');
        return await response.json(); // Restituisci la risposta in formato JSON
    } catch (error) {
        console.error('Errore:', error);
    }
}

// Funzione per recuperare gli slot di parcheggio
async function fetchSlots() {
    try {
        const response = await fetch(`${BASE_API_URL}/get_slots.php`);
        const slots = await response.json();

        // Per ogni slot, crea un marker sulla mappa
        slots.forEach(slot => {
            if (slot.latitude && slot.longitude) {
                // Crea un'icona personalizzata per i marker
                const markerOptions = slot.is_occupied ? {
                    icon: L.icon({
                        iconUrl: 'http://localhost:8082/js/red.png', // Usa un'icona rossa per i marker occupati
                        iconSize: [35, 35], // Dimensioni dell'icona
                        iconAnchor: [12, 25], // Punto di ancoraggio dell'icona
                        popupAnchor: [0, -25] // Posizionamento del popup
                    }),
                    clickable: false // I marker occupati non sono cliccabili
                } : { 
                    icon: L.icon({
                        iconUrl: 'http://localhost:8082/js/green.png', // Usa un'icona rossa per i marker occupati
                        iconSize: [35, 35], // Dimensioni dell'icona
                        iconAnchor: [12, 25], // Punto di ancoraggio dell'icona
                        popupAnchor: [0, -25] // Posizionamento del popup
                    }),
                    clickable : true
                };

                // Crea un marker con le opzioni precedenti
                const marker = L.marker([slot.latitude, slot.longitude], markerOptions).addTo(map);

                // Crea un popup con le informazioni dello slot
                marker.bindPopup(`
                    <strong>${slot.location}</strong><br>
                    Tariffa: €${slot.rate_per_hour}/h<br>
                    Stato: ${slot.is_occupied ? 'Occupato' : 'Libero'}
                `);

                // Se lo slot non è occupato, gestisci il click sul marker
                if (!slot.is_occupied) {
                    marker.on('click', () => {
                        document.getElementById('stallName').textContent = slot.location;
                        document.getElementById('stallName2').textContent = slot.location;
                        const parkingForm = document.getElementById('parkingForm');

                        // Gestione della prenotazione
                        parkingForm.addEventListener('submit', (event) => {
                            event.preventDefault();
                            const startTime = document.getElementById("startTime").value;
                            const endTime = document.getElementById('endTime').value;

                            // Calcola la differenza tra orario di inizio e fine in ore
                            const start = new Date(`1970-01-01T${startTime}:00`);
                            const end = new Date(`1970-01-01T${endTime}:00`);
                            const hoursDiff = (end - start) / (1000 * 60 * 60);

                            if (hoursDiff > 0) {
                                const totalCost = slot.rate_per_hour * hoursDiff;
                                document.getElementById('stallImport').textContent = `€${totalCost.toFixed(2)}`;
                            } else {
                                alert('Orario di fine deve essere successivo all\'orario di inizio.');
                            }
                        });
                    });
                }
            }
        });
    } catch (error) {
        console.error('Errore nel caricamento degli slot:', error);
    }
}

// Funzione per centrare la mappa sulla posizione dell'utente
function centerMapOnUser() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                const { latitude, longitude } = position.coords;
                map.setView([latitude, longitude], 15); // Imposta la vista della mappa sulla posizione dell'utente
                L.marker([latitude, longitude]).addTo(map).bindPopup('La tua posizione').openPopup(); // Aggiungi un marker per la posizione dell'utente
            },
            error => {
                console.error('Errore nella geolocalizzazione:', error);
                map.setView(MAP_CENTER_COORDS, DEFAULT_ZOOM_LEVEL); // Torna alla vista di Roma in caso di errore
            }
        );
    } else {
        console.error('Geolocalizzazione non supportata dal browser.');
        map.setView(MAP_CENTER_COORDS, DEFAULT_ZOOM_LEVEL); // Torna alla vista di Roma se la geolocalizzazione non è supportata
    }
}

// Funzione per caricare la navbar
async function loadNavbar() {
    try {
        const response = await fetch('navbar.html');
        const data = await response.text();
        document.getElementById('navbar').innerHTML = data;
    } catch (error) {
        console.error('Errore nel caricamento della navbar:', error);
    }
}

 // Funzioni per la gestione dei pagamenti
 async function loadPayments() {
    const token = TokenUtils.getToken();
    const decoded = TokenUtils.decode(token);
    if (!TokenUtils.isValid(decoded)) return;

    const data = await fetchData(`${BASE_API_URL}/load_payments.php`, 'POST', { id_user: decoded.user_id });
    if (!data || !data.data) return;

    const tableBody = document.getElementById("table-body");
    tableBody.innerHTML = '';

    data.data.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.reservation_id}</td>
            <td>${item.payment_date || "N/A"}</td>
            <td>${item.payment_status}</td>
            <td>${item.total_cost}</td>
        `;
        tableBody.appendChild(row);
    });
}

// Inizializzazione
loadNavbar(); // Carica la navbar
centerMapOnUser(); // Centra la mappa sulla posizione dell'utente
fetchSlots(); // Carica gli slot di parcheggio sulla mappa
 */
