from flask import Flask, request, jsonify
from datetime import datetime, timezone
from fsrs.fsrs import Scheduler, Card, State, Rating, ReviewLog
import logging

app = Flask(__name__)

# Configure le logger pour le débogage
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
            "/initialize_card": "POST - Initialize a new flashcard",
            "/review": "POST - Process a flashcard review"
        }
    }), 200

@app.route('/initialize_card', methods=['POST'])
def initialize_card():
    """
    Route pour initialiser une nouvelle flashcard avec FSRS.
    """
    try:
        data = request.get_json()
        if not data or 'id' not in data:
            app.logger.error("Invalid input: Missing 'id'")
            return jsonify({"error": "Invalid input: 'id' is required"}), 400

        card_id = data['id']
        card = Card(card_id=card_id)  # Création de la carte en mode Learning

        # Convertir la carte en dictionnaire pour la réponse
        initialized_card = card.to_dict()

        app.logger.info(f"✅ Card initialized: {initialized_card}")
        return jsonify(initialized_card), 200

    except Exception as e:
        app.logger.error(f"❌ An unexpected error occurred: {str(e)}")
        return jsonify({"error": f"An unexpected error occurred: {str(e)}"}), 500

@app.route('/review', methods=['POST'])
def review_card():
    """
    Route pour traiter une révision de carte.
    """
    try:
        data = request.get_json()
        if not data:
            app.logger.error("No JSON data received")
            return jsonify({"error": "No JSON data received"}), 400

        if 'card' not in data or 'rating' not in data or 'review_datetime' not in data:
            app.logger.error("Invalid input: Missing 'card', 'rating', or 'review_datetime'")
            return jsonify({
                "error": "Invalid input: 'card', 'rating', and 'review_datetime' are required"
            }), 400

        card_data = data['card']
        rating = Rating(data['rating'])

        # Conversion sécurisée des dates
        def safe_convert(date_value, field_name):
            if isinstance(date_value, str):
                try:
                    return datetime.fromisoformat(date_value.replace("Z", "+00:00"))
                except ValueError as e:
                    app.logger.error(f"Invalid date format for {field_name}: {date_value} - {str(e)}")
                    return None
            return None

        review_datetime = safe_convert(data['review_datetime'], "review_datetime")
        due_datetime = safe_convert(card_data.get('due'), "due_datetime")
        last_review_datetime = safe_convert(card_data.get('last_review'), "last_review_datetime")

        # Vérification et correction des valeurs NULL
        card_data['due'] = due_datetime.isoformat() if due_datetime else "1970-01-01T00:00:00+00:00"
        card_data['last_review'] = last_review_datetime.isoformat() if last_review_datetime else "1970-01-01T00:00:00+00:00"

        # Création de l'objet `Card`
        card = Card.from_dict(card_data)

        # Passage dans l'algorithme FSRS
        updated_card, review_log = scheduler.review_card(
            card=card, rating=rating, review_datetime=review_datetime
        )

        # Ajout de logs pour voir si `review_log` est bien généré
        app.logger.info(f"🟢 Updated card: {updated_card.to_dict()}")
        if review_log:
            app.logger.info(f"🟢 ReviewLog generated: {review_log.to_dict()}")
        else:
            app.logger.warning(f"⚠️ No ReviewLog generated for card_id {card.card_id}")

        # Construction de la réponse JSON
        response_data = {
            "card": updated_card.to_dict(),
            "review_log": review_log.to_dict() if review_log else None  # Évite une erreur si `review_log` est None
        }

        app.logger.info(f"✅ Review processed successfully: {response_data}")
        return jsonify(response_data), 200

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
    # Mode debug activé et écoute sur 0.0.0.0 pour Docker
    app.run(host='0.0.0.0', port=5000, debug=True)
