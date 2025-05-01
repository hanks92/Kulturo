const TILE_WIDTH = 850;
const TILE_HEIGHT = 470;
const GRID_WIDTH = 6;
const GRID_HEIGHT = 6;

let game;
let selectedPlant = 'tree1';
let waterAmount = window.userWater ?? 0;
let unsavedWater = 0;
let watering = false;
let wateringInterval;
const plantStates = [];
let userInventory = {};

let currentWaterDrop = null;

const config = {
  type: Phaser.AUTO,
  width: window.innerWidth,
  height: window.innerHeight,
  backgroundColor: '#e6fce6',
  parent: 'game-container',
  scene: {
    preload,
    create,
    update
  },
  scale: {
    mode: Phaser.Scale.RESIZE,
    autoCenter: Phaser.Scale.CENTER_BOTH
  }
};

function preload() {
  this.load.image('grass', '/game/assets/ground/grass.png');
  this.load.image('tree1', '/game/assets/tree/treelvl1.png');
  this.load.image('tree2', '/game/assets/tree/treelvl2.png');
  this.load.image('tree3', '/game/assets/tree/treelvl3.png');
  this.load.image('tree4', '/game/assets/tree/treelvl4.png');
  this.load.image('tree5', '/game/assets/tree/treelvl5.png');
  this.load.image('waterDrop', '/game/assets/ground/waterDrop.png');
}

function create() {
  const gardenPixelWidth = (GRID_WIDTH + GRID_HEIGHT) * TILE_WIDTH / 2;
  const gardenPixelHeight = (GRID_WIDTH + GRID_HEIGHT) * TILE_HEIGHT / 2;
  const margin = 1000;
  const sceneWidth = gardenPixelWidth + margin;
  const sceneHeight = gardenPixelHeight + margin;

  const originX = sceneWidth / 2 - gardenPixelWidth / 2;
  const originY = sceneHeight / 2 - gardenPixelHeight / 2;

  const tileX = Math.floor(GRID_WIDTH / 2);
  const tileY = Math.floor(GRID_HEIGHT / 2);
  const centerX = (tileX - tileY) * TILE_WIDTH / 2 + originX;
  const centerY = (tileX + tileY) * TILE_HEIGHT / 2 + originY - TILE_HEIGHT * 1.5;

  this.centerX = centerX;
  this.centerY = centerY;

  const zoomX = window.innerWidth / sceneWidth;
  const zoomY = window.innerHeight / sceneHeight;
  const autoZoom = Math.min(zoomX, zoomY);

  const cam = this.cameras.main;
  cam.setZoom(autoZoom);
  cam.centerOn(centerX, centerY);
  cam.setBounds();

  // 👉 1. Charger les plantes existantes depuis le serveur
  fetch('/api/game/load-garden')
  .then(response => response.json())
  .then(savedPlants => {
    savedPlants.forEach(plantData => {
      const x = plantData.x;
      const y = plantData.y;
      const type = plantData.type;
      const level = plantData.level;
      const waterReceived = plantData.waterReceived;

      const isoX = (x - y) * TILE_WIDTH / 2 + originX;
      const isoY = (x + y) * TILE_HEIGHT / 2 + originY;

      const plantImage = this.add.image(isoX, isoY - TILE_HEIGHT / 2, type)
        .setOrigin(0.5, 1)
        .setDepth(isoY)
        .setInteractive();

      const barWidth = 300;
      const barHeight = 30;
      const barY = isoY - TILE_HEIGHT * 1.4;

      const progressBarBg = this.add.rectangle(isoX, barY, barWidth, barHeight, 0xaaaaaa)
        .setOrigin(0.5)
        .setDepth(isoY + 1)
        .setVisible(false);

      const progressBar = this.add.rectangle(isoX - barWidth / 2, barY, 0, barHeight, 0x00cc00)
        .setOrigin(0, 0.5)
        .setDepth(isoY + 2)
        .setVisible(false);

      plantStates.push({
        x,
        y,
        level,
        waterReceived,
        image: plantImage,
        progressBar,
        progressBarBg
      });
    });

    // 👉 Ensuite charger l'inventaire utilisateur
    return fetch('/api/game/inventory');
  })
  .then(response => response.json())
  .then(inventory => {
    console.log('✅ Inventaire reçu:', inventory); // <-- 🔥 ajoute ce log ici
    userInventory = inventory;
    updatePlantButtons(); // 🔄 mettre à jour les boutons avec le stock
  })
  .catch(error => {
    console.error('❌ Erreur lors du chargement du jardin ou de l\'inventaire:', error);
  });

  // 👉 2. Construire la grille de tuiles grass
  for (let y = 0; y < GRID_HEIGHT; y++) {
    for (let x = 0; x < GRID_WIDTH; x++) {
      const isoX = (x - y) * TILE_WIDTH / 2 + originX;
      const isoY = (x + y) * TILE_HEIGHT / 2 + originY;

      const tile = this.add.image(isoX, isoY, 'grass')
        .setOrigin(0.5, 1)
        .setInteractive({ pixelPerfect: true, useHandCursor: true })
        .setData({ x, y, planted: false });

      tile.setDepth(isoY);

      tile.on('pointerdown', () => {
        if (!tile.getData('planted')) {
          const quantity = userInventory[selectedPlant] ?? 0;

          if (quantity <= 0) {
            console.log('❌ Pas assez de stock pour planter', selectedPlant);
            return;
          }

          // ➡️ Créer l'image de la plante au bon endroit
          const plantImage = this.add.image(tile.x, tile.y - TILE_HEIGHT / 2, selectedPlant)
            .setOrigin(0.5, 1)
            .setDepth(tile.y)
            .setInteractive();

          const barWidth = 300;
          const barHeight = 30;
          const barY = tile.y - TILE_HEIGHT * 1.4;

          const progressBarBg = this.add.rectangle(tile.x, barY, barWidth, barHeight, 0xaaaaaa)
            .setOrigin(0.5)
            .setDepth(tile.y + 1)
            .setVisible(false);

          const progressBar = this.add.rectangle(tile.x - barWidth / 2, barY, 0, barHeight, 0x00cc00)
            .setOrigin(0, 0.5)
            .setDepth(tile.y + 2)
            .setVisible(false);

          // ➡️ Ajouter la nouvelle plante
          plantStates.push({
            x: tile.getData('x'),
            y: tile.getData('y'),
            level: 1,
            waterReceived: 0,
            image: plantImage,
            progressBar,
            progressBarBg
          });

          tile.setData('planted', true);

          // ➡️ Décrémenter l'inventaire
          if (userInventory[selectedPlant] !== undefined) {
            userInventory[selectedPlant]--;
            updatePlantButtons(); // 🔄 met à jour l'affichage du stock
          }
        }
      });

    }
  }

  // 👉 3. Sélection d'un type de plante en cliquant sur les boutons
  document.querySelectorAll('.plant-btn').forEach(button => {
    button.addEventListener('click', () => {
      selectedPlant = button.dataset.plant;
    });
  });

  // 👉 4. Affichage du compteur d'eau
  this.waterText = this.add.text(20, 20, `Eau: ${waterAmount}`, {
    fontSize: '24px',
    fill: '#000'
  }).setScrollFactor(0);

  // 👉 5. Gestion de l'arrosage
  this.input.on('pointerdown', (pointer) => {
    watering = true;

    if (!currentWaterDrop) {
      currentWaterDrop = this.add.image(pointer.worldX, pointer.worldY, 'waterDrop')
        .setOrigin(0.5)
        .setScale(0.2)
        .setDepth(9999);
    }

    wateringInterval = setInterval(() => {
      if (!watering || waterAmount <= 0) return;

      const worldPoint = pointer.positionToCamera(this.cameras.main);
      const plant = plantStates.find(p => {
        const bounds = p.image.getBounds();
        return Phaser.Geom.Rectangle.Contains(bounds, worldPoint.x, worldPoint.y);
      });

      if (plant) {
        waterAmount--;
        unsavedWater++;
        window.userWater = waterAmount;

        document.querySelectorAll('.hud-item').forEach(el => {
          if (el.textContent.includes('💧 Eau')) {
            el.textContent = `💧 Eau: ${waterAmount}`;
          }
        });

        plant.waterReceived++;
        const nextLevel = plant.level + 1;
        const needed = nextLevel * 5;
        const ratio = Math.min(plant.waterReceived / needed, 1);

        plant.progressBar.width = 300 * ratio;
        plant.progressBar.setVisible(true);
        plant.progressBarBg.setVisible(true);

        if (ratio === 1 && nextLevel <= 5) {
          plant.image.setTexture(`tree${nextLevel}`);
          plant.level = nextLevel;
          plant.waterReceived = 0;
          plant.progressBar.width = 0;
        }
      }
    }, 200);
  });

  this.input.on('pointerup', () => {
    watering = false;
    clearInterval(wateringInterval);
    if (currentWaterDrop) {
      currentWaterDrop.destroy();
      currentWaterDrop = null;
    }

    plantStates.forEach(plant => {
      if (plant.progressBar) plant.progressBar.setVisible(false);
      if (plant.progressBarBg) plant.progressBarBg.setVisible(false);
    });
  });
}


function update() {
  this.waterText.setText(`Eau: ${waterAmount}`);
  if (watering && currentWaterDrop) {
    const pointer = this.input.activePointer;
    currentWaterDrop.setPosition(pointer.worldX, pointer.worldY);
  }
}

// 🔄 Envoi Ajax pour persister l’eau
function syncWaterWithServer(newWaterValue) {
  fetch('/api/game/update-water', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({ water: newWaterValue })
  }).catch(err => console.error('Sync water error:', err));
}

// 🔄 Envoi Ajax pour persister l'état des plantes
function syncPlantsWithServer() {
  const plantsData = plantStates.map(plant => ({
    x: plant.x,
    y: plant.y,
    type: plant.image.texture.key, // très important d'envoyer le bon type d'arbre
    level: plant.level,
    waterReceived: plant.waterReceived,
  }));

  fetch('/api/game/update-plants', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({ plants: plantsData })
  }).catch(err => console.error('Sync plants error:', err));
}


// ⏲️ Sauvegarde périodique
setInterval(() => {
  if (unsavedWater > 0) {
    syncWaterWithServer(waterAmount);
    unsavedWater = 0;
  }
}, 5000);

// ⏲️ Sauvegarde périodique de l'état des plantes
setInterval(() => {
  syncPlantsWithServer();
}, 10000); // toutes les 10 secondes


// 🧹 Sauvegarde à la fermeture
window.addEventListener('beforeunload', () => {
  if (unsavedWater > 0) {
    navigator.sendBeacon('/api/game/update-water', JSON.stringify({ water: waterAmount }));
  }

  // 🚀 Sauvegarde aussi les plantes en quittant
  const plantsData = plantStates.map(plant => ({
    x: plant.x,
    y: plant.y,
    type: plant.image.texture.key,
    level: plant.level,
    waterReceived: plant.waterReceived,
  }));

  navigator.sendBeacon('/api/game/update-plants', JSON.stringify({ plants: plantsData }));
});

game = new Phaser.Game(config);

function updatePlantButtons() {
  document.querySelectorAll('.plant-btn').forEach(button => {
    const plantType = button.dataset.plant;
    const quantity = userInventory[plantType] ?? 0;
    const span = button.querySelector('.plant-qty');

    if (span) {
      if (quantity > 0) {
        span.textContent = `(${quantity})`;
        button.disabled = false;
      } else {
        span.textContent = `(Locked 🔒)`;
        button.disabled = true;
      }
    }
  });
}


window.addEventListener('resize', () => {
  game.scale.resize(window.innerWidth, window.innerHeight);
  const scene = game.scene.scenes[0];
  const gardenPixelWidth = (GRID_WIDTH + GRID_HEIGHT) * TILE_WIDTH / 2;
  const gardenPixelHeight = (GRID_WIDTH + GRID_HEIGHT) * TILE_HEIGHT / 2;
  const margin = 200;
  const sceneWidth = gardenPixelWidth + margin;
  const sceneHeight = gardenPixelHeight + margin;
  const zoomX = window.innerWidth / sceneWidth;
  const zoomY = window.innerHeight / sceneHeight;
  const autoZoom = Math.min(zoomX, zoomY);
  scene.cameras.main.setZoom(autoZoom);
  scene.cameras.main.centerOn(scene.centerX, scene.centerY);
});
