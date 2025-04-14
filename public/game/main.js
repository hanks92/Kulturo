const TILE_WIDTH = 1024;
const TILE_HEIGHT = 512;
const GRID_WIDTH = 4;
const GRID_HEIGHT = 4;

let game;
let selectedPlant = 'tree1';

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
}

function create() {
  const gardenPixelWidth = (GRID_WIDTH + GRID_HEIGHT) * TILE_WIDTH / 2;
  const gardenPixelHeight = (GRID_WIDTH + GRID_HEIGHT) * TILE_HEIGHT / 2;

  const margin = 2000;
  const sceneWidth = gardenPixelWidth + margin;
  const sceneHeight = gardenPixelHeight + margin;

  const originX = sceneWidth / 2 - gardenPixelWidth / 2;
  const originY = sceneHeight / 2 - gardenPixelHeight / 2;

  // ✅ Centrage bas, caméra fixe
  const tileX = Math.floor(GRID_WIDTH / 2);
  const tileY = Math.floor(GRID_HEIGHT / 2);
  const centerX = (tileX - tileY) * TILE_WIDTH / 2 + originX;
  const centerY = (tileX + tileY) * TILE_HEIGHT / 2 + originY - TILE_HEIGHT * 1.5;

  this.centerX = centerX;
  this.centerY = centerY;

  const cam = this.cameras.main;
  cam.setZoom(0.3);
  cam.centerOn(centerX, centerY);
  cam.setBounds(); // sans effet ici mais laissé pour compatibilité

  // ❌ Drag désactivé
  // ❌ Zoom désactivé

  // 🌱 Grille
  for (let y = 0; y < GRID_HEIGHT; y++) {
    for (let x = 0; x < GRID_WIDTH; x++) {
      const isoX = (x - y) * TILE_WIDTH / 2 + originX;
      const isoY = (x + y) * TILE_HEIGHT / 2 + originY;

      const tile = this.add.image(isoX, isoY, 'grass')
        .setOrigin(0.5, 1)
        .setInteractive()
        .setData({ x, y, planted: false });

      tile.on('pointerdown', () => {
        if (!tile.getData('planted')) {
          this.add.image(isoX, isoY - TILE_HEIGHT / 2, selectedPlant)
            .setOrigin(0.5, 1);
          tile.setData('planted', true);
        }
      });
    }
  }

  // 🔄 Bouton recentrer (optionnel mais actif)
  const recenterBtn = document.getElementById('recenter-btn');
  if (recenterBtn) {
    recenterBtn.addEventListener('click', () => {
      cam.pan(this.centerX, this.centerY, 300, 'Power2');
    });
  }

  // 🌳 Choix de plante
  document.querySelectorAll('.plant-btn').forEach(button => {
    button.addEventListener('click', () => {
      selectedPlant = button.dataset.plant;
      console.log('Plante sélectionnée :', selectedPlant);
    });
  });
}

function update() {}

game = new Phaser.Game(config);

window.addEventListener('resize', () => {
  game.scale.resize(window.innerWidth, window.innerHeight);
});
