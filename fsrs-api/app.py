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

        # ✅ Ajout du calcul de retrievability
        retrievability = card.get_retrievability(datetime.now(timezone.utc))
        initialized_card['retrievability'] = retrievability  # Ajout de retrievability dans la réponse

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

        # ✅ Log des données brutes reçues
        app.logger.info(f"Received data: {data}")

        # ✅ Vérifier et log les types avant conversion
        review_datetime = data['review_datetime']
        due_datetime = card_data.get('due')
        last_review_datetime = card_data.get('last_review')

        app.logger.info(f"BEFORE CONVERSION - review_datetime: {type(review_datetime)}, value: {review_datetime}")
        app.logger.info(f"BEFORE CONVERSION - due_datetime: {type(due_datetime)}, value: {due_datetime}")
        app.logger.info(f"BEFORE CONVERSION - last_review_datetime: {type(last_review_datetime)}, value: {last_review_datetime}")

        # ✅ Sécurisation de la conversion des dates avec logs détaillés
        def safe_convert(date_value, field_name):
            app.logger.info(f"Converting {field_name}: {date_value} (type: {type(date_value)})")
            if isinstance(date_value, str):
                try:
                    converted_date = datetime.fromisoformat(date_value.replace("Z", "+00:00"))
                    app.logger.info(f"SUCCESS: Converted {field_name} -> {converted_date}")
                    return converted_date
                except ValueError as e:
                    app.logger.error(f"ERROR: Invalid date format for {field_name}: {date_value} - {str(e)}")
                    return None
            elif date_value is None:
                app.logger.info(f"Skipping conversion for {field_name} because value is None")
            else:
                app.logger.error(f"Unexpected type for {field_name}: {type(date_value)} ({date_value})")
            return None

        review_datetime = safe_convert(review_datetime, "review_datetime")
        due_datetime = safe_convert(due_datetime, "due_datetime")
        last_review_datetime = safe_convert(last_review_datetime, "last_review_datetime")

        # ✅ Vérification après conversion
        app.logger.info(f"AFTER CONVERSION - review_datetime: {review_datetime}")
        app.logger.info(f"AFTER CONVERSION - due_datetime: {due_datetime}")
        app.logger.info(f"AFTER CONVERSION - last_review_datetime: {last_review_datetime}")

        # ✅ Vérifie et remplace None par une chaîne correcte pour `Card.from_dict()`
        card_data['due'] = due_datetime.isoformat() if due_datetime else "1970-01-01T00:00:00+00:00"
        card_data['last_review'] = last_review_datetime.isoformat() if last_review_datetime else "1970-01-01T00:00:00+00:00"

        # ✅ Log pour vérifier
        app.logger.info(f"BEFORE Card.from_dict() - corrected card_data: {card_data}")

        # ✅ Maintenant, Card.from_dict() reçoit toujours une chaîne et ne casse plus
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
