FROM ubuntu:20.04

# Mettre à jour le système et installer les dépendances
RUN apt update && apt install -y curl wget git

# Installer Ollama
RUN curl -fsSL https://ollama.ai/install.sh | sh

# Lancer Ollama au démarrage et télécharger le modèle après que Ollama soit prêt
CMD ollama serve & sleep 5 && ollama pull deepseek-r1:7b && tail -f /dev/null
