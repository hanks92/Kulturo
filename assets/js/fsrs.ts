import { generatorParameters, fsrs, createEmptyCard, Rating } from 'ts-fsrs';

// Configurer les paramètres FSRS
const params = generatorParameters({
    enable_fuzz: true,
    enable_short_term: true,
});

const f = fsrs(params);

// Créer une carte flash initiale
const card = createEmptyCard(new Date());

// Simuler une révision avec la réponse "Good"
const now = new Date();
const schedulingCards = f.repeat(card, now);

console.log("Prochaine révision :", schedulingCards[Rating.Good]);
