const TILE_WIDTH = 1024;
const TILE_HEIGHT = 512;
const GRID_WIDTH = 4;
const GRID_HEIGHT = 4;

let game; // pour gérer resize

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

  // Point de centrage (ajusté selon taille réelle de l'écran)
  const visibleWidth = this.sys.game.config.width;
  const visibleHeight = this.sys.game.config.height;
  const centerX = sceneWidth / 2;
  const centerY = sceneHeight / 2;

  this.cameras.main.setBounds(0, 0, sceneWidth, sceneHeight);
  this.cameras.main.setZoom(0.3);
  this.cameras.main.centerOn(centerX, centerY);

  this.input.on('pointermove', function (pointer) {
    if (!pointer.isDown) return;
    this.cameras.main.scrollX -= (pointer.x - pointer.prevPosition.x) / this.cameras.main.zoom;
    this.cameras.main.scrollY -= (pointer.y - pointer.prevPosition.y) / this.cameras.main.zoom;
  }, this);

  this.input.on('wheel', (pointer, gameObjects, deltaX, deltaY) => {
    let zoom = this.cameras.main.zoom;
    zoom -= deltaY * 0.001;
    zoom = Phaser.Math.Clamp(zoom, 0.1, 1);
    this.cameras.main.setZoom(zoom);
  });

  // Placement des tuiles autour du centre
  for (let y = 0; y < GRID_HEIGHT; y++) {
    for (let x = 0; x < GRID_WIDTH; x++) {
      const isoX = (x - y) * TILE_WIDTH / 2 + centerX;
      const isoY = (x + y) * TILE_HEIGHT / 2 + centerY - gardenPixelHeight / 2;

      const tile = this.add.image(isoX, isoY, 'grass')
        .setOrigin(0.5, 1)
        .setInteractive()
        .setData({ x, y, planted: false });

      tile.on('pointerdown', () => {
        if (!tile.getData('planted')) {
          const treeTypes = ['tree1', 'tree2', 'tree3'];
          const chosenTree = Phaser.Math.RND.pick(treeTypes);
          this.add.image(isoX, isoY - TILE_HEIGHT / 2, chosenTree)
            .setOrigin(0.5, 1);
          tile.setData('planted', true);
        }
      });
    }
  }
}

function update() {}

game = new Phaser.Game(config);

// Ajout du resize automatique
window.addEventListener('resize', () => {
  game.scale.resize(window.innerWidth, window.innerHeight);
});
