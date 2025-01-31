from flask import Flask, request, jsonify
from datetime import datetime, timezone
from fsrs.fsrs import Scheduler, Card, State, Rating
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

        # ✅ Correction minimale : conversion uniquement si c'est une chaîne
        review_datetime = datetime.fromisoformat(data['review_datetime'].replace("Z", "+00:00")) if isinstance(data['review_datetime'], str) else None
        due_datetime = datetime.fromisoformat(card_data['due'].replace("Z", "+00:00")) if isinstance(card_data.get('due'), str) else None
        last_review_datetime = datetime.fromisoformat(card_data['last_review'].replace("Z", "+00:00")) if isinstance(card_data.get('last_review'), str) else None

        # Mise à jour des valeurs dans card_data
        card_data['due'] = due_datetime
        card_data['last_review'] = last_review_datetime

        card = Card.from_dict(card_data)

        updated_card, review_log = scheduler.review_card(
            card=card, rating=rating, review_datetime=review_datetime
        )

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

if __name__ == '__main__':
    # Ajoute le mode debug pour le développement et écoute sur 0.0.0.0 pour Docker
    app.run(host='0.0.0.0', port=5000, debug=True)
