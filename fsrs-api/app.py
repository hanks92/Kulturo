from flask import Flask, request, jsonify
from datetime import datetime
from fsrs.fsrs import Scheduler, Card, Rating, State
import logging

app = Flask(__name__)

# Configure le logger pour déboguer plus facilement
logging.basicConfig(level=logging.DEBUG)

# Initialise le scheduler FSRS
scheduler = Scheduler()

@app.route('/', methods=['GET'])
def index():
    """
    Route racine pour tester que l'API est en ligne et afficher les routes disponibles.
    """
    return jsonify({
        "message": "Welcome to the FSRS API",
        "routes": {
            "/review": "POST - Process a flashcard review",
            "/initialize_card": "POST - Initialize a new flashcard"
        }
    }), 200

@app.route('/review', methods=['POST'])
def review_card():
    """
    Route pour traiter une révision de carte.
    """
    try:
        # Récupération des données JSON de la requête
        data = request.get_json()
        if not data:
            app.logger.error("No JSON data received")
            return jsonify({"error": "No JSON data received"}), 400

        # Validation des données reçues
        if 'card' not in data or 'rating' not in data or 'review_datetime' not in data:
            app.logger.error("Invalid input: Missing 'card', 'rating', or 'review_datetime'")
            return jsonify({
                "error": "Invalid input: 'card', 'rating', and 'review_datetime' are required"
            }), 400

        # Extraction et transformation des données
        card_data = data['card']
        rating = Rating(data['rating'])
        review_datetime = datetime.fromisoformat(data['review_datetime'])

        # Recréer une carte à partir des données
        card = Card.from_dict(card_data)

        # Passer la carte à l'algorithme FSRS
        updated_card, review_log = scheduler.review_card(
            card=card, rating=rating, review_datetime=review_datetime
        )

        # Retourner les résultats au format JSON
        return jsonify({
            "card": updated_card.to_dict(),
            "review_log": review_log.to_dict()
        }), 200

    except KeyError as e:
        app.logger.error(f"KeyError: {str(e)}")
        return jsonify({"error": f"Missing key: {str(e)}"}), 422
    except ValueError as e:
        app.logger.error(f"ValueError: {str(e)}")
        return jsonify({"error": f"Invalid value: {str(e)}"}), 400
    except Exception as e:
        app.logger.error(f"An unexpected error occurred: {str(e)}")
        return jsonify({"error": f"An unexpected error occurred: {str(e)}"}), 500

@app.route('/initialize_card', methods=['POST'])
def initialize_card():
    """
    Route pour initialiser une nouvelle flashcard avec FSRS.
    """
    try:
        # Récupération des données JSON de la requête
        data = request.get_json()
        if not data:
            app.logger.error("No JSON data received")
            return jsonify({"error": "No JSON data received"}), 400

        # Validation des données reçues
        if 'id' not in data:
            app.logger.error("Invalid input: Missing 'id'")
            return jsonify({
                "error": "Invalid input: 'id' is required"
            }), 400

        # Création d'une nouvelle carte
        card_id = data['id']
        card = Card(
            card_id=card_id,
            state=State.Learning,
            step=0,  # Étape initiale pour les nouvelles cartes
        )

        # Retourner les paramètres initialisés au format JSON
        initialized_card = card.to_dict()
        return jsonify(initialized_card), 200

    except KeyError as e:
        app.logger.error(f"KeyError: {str(e)}")
        return jsonify({"error": f"Missing key: {str(e)}"}), 422
    except ValueError as e:
        app.logger.error(f"ValueError: {str(e)}")
        return jsonify({"error": f"Invalid value: {str(e)}"}), 400
    except Exception as e:
        app.logger.error(f"An unexpected error occurred: {str(e)}")
        return jsonify({"error": f"An unexpected error occurred: {str(e)}"}), 500

if __name__ == '__main__':
    # Ajoute le mode debug pour le développement
    app.run(host='0.0.0.0', port=5000, debug=True)
