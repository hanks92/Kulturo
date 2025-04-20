const TILE_WIDTH = 850;
const TILE_HEIGHT = 470;
const GRID_WIDTH = 6;
const GRID_HEIGHT = 6;

let game;
let selectedPlant = 'tree1';
let waterAmount = 100;
let watering = false;
let wateringInterval;
const plantStates = [];

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

  const availableWidth = window.innerWidth;
  const availableHeight = window.innerHeight;

  const zoomX = availableWidth / sceneWidth;
  const zoomY = availableHeight / sceneHeight;
  const autoZoom = Math.min(zoomX, zoomY);

  const cam = this.cameras.main;
  cam.setZoom(autoZoom);
  cam.centerOn(centerX, centerY);
  cam.setBounds();

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
          const plantImage = this.add.image(isoX, isoY - TILE_HEIGHT / 2, selectedPlant)
            .setOrigin(0.5, 1)
            .setDepth(isoY)
            .setInteractive();

          plantStates.push({
            x,
            y,
            level: 1,
            waterReceived: 0,
            image: plantImage
          });

          tile.setData('planted', true);
        }
      });
    }
  }

  // 👇 Gestion du clic sur les boutons de plantation
  document.querySelectorAll('.plant-btn').forEach(button => {
    button.addEventListener('click', () => {
      selectedPlant = button.dataset.plant;
      console.log('Plante sélectionnée :', selectedPlant);
    });
  });

  // 👇 Affichage compteur d’eau
  this.waterText = this.add.text(20, 20, `Eau: ${waterAmount}`, {
    fontSize: '24px',
    fill: '#000'
  }).setScrollFactor(0);

  // 👇 Arrosage
  this.input.on('pointerdown', (pointer) => {
    watering = true;

    wateringInterval = setInterval(() => {
      if (!watering || waterAmount <= 0) return;

      const worldPoint = pointer.positionToCamera(this.cameras.main);

      const plant = plantStates.find(p => {
        const bounds = p.image.getBounds();
        return Phaser.Geom.Rectangle.Contains(bounds, worldPoint.x, worldPoint.y);
      });

      if (plant) {
        waterAmount--;
        plant.waterReceived++;

        const nextLevel = plant.level + 1;
        if (plant.waterReceived >= nextLevel * 5 && nextLevel <= 5) {
          plant.image.setTexture(`tree${nextLevel}`);
          plant.level = nextLevel;
        }
      }
    }, 200);
  });

  this.input.on('pointerup', () => {
    watering = false;
    clearInterval(wateringInterval);
  });
}

function update() {
  this.waterText.setText(`Eau: ${waterAmount}`);
}

game = new Phaser.Game(config);

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
