from flask import Flask, request, jsonify
from authentication.authenticator import check_first_factor, check_second_factor,register_user
from datetime import datetime
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

@app.route('/authenticate', methods=['POST'])
def authenticate():

    response_error = {
        "status": "error",
        "message": "Invalid request"
    }
    
    # Ottieni il JSON dalla richiesta
    message = request.get_json()

    # input validation
    action = message.get('action')
    data = message.get('data')

    if action is None or data is None:
        return response_error
    
    if action == 'login_request':
        email = data.get('email')
        passwd = data.get('password')

        if email is  None or passwd is None:
            return response_error
        
        auth_result = check_first_factor(email,passwd)
        
        return auth_result
    elif action =='verify_otp':
        otp_prop = data.get('otp')
        transaction_id_prop =data.get('transaction_id') 
        otp_verification_result = check_second_factor(otp_prop,transaction_id_prop)
        return jsonify(otp_verification_result)
    return jsonify(response_error)


@app.route('/register', methods=['POST'])
def register():

    # Messaggio di errore generico
    response_error = {
        "status": "error",
        "message": "Invalid request"
    }

    try:
        # Ottieni il JSON dalla richiesta
        message = request.get_json()
        
        if not message:
            response_error['message'] = "Request body must be JSON."
            return jsonify(response_error), 400
        
        # Validazione dell'azione
        action = message.get('action')
        if action != "register_request":
            response_error['message'] = "Invalid action."
            return jsonify(response_error), 400

        # Ottieni i dati
        data = message.get('data', {})
        if not data:
            response_error['message'] = "Data is missing."
            return jsonify(response_error), 400

        # Validazione dei campi
        first_name = data.get('first_name')
        last_name = data.get('last_name')
        email = data.get('email')
        username = data.get('username')
        password = data.get('password')

        if not first_name or not last_name or  not email or not password:
            response_error['message'] = "Incomplete data..."
            return jsonify(response_error), 400

        # Logica di registrazione (es. salvataggio nel database)
        response_success = register_user(first_name, last_name,username,email,password)

        return jsonify(response_success)

    except Exception as e:
        # Gestione degli errori generici
        response_error['message'] = f"An error occurred: {str(e)}"
        return jsonify(response_error), 500

    # Aggiungi un return di fallback, anche se non dovrebbe mai essere raggiunto
    return jsonify(response_error), 400

# Avvia il server
if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
