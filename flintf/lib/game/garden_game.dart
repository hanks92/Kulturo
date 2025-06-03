import 'package:flame/components.dart';
import 'package:flame/game.dart';

const double tileWidth = 1024;
const double tileHeight = 666;
const double tileOverlap = 150;

class GardenGame extends FlameGame {
  static const int gridWidth = 6;
  static const int gridHeight = 6;

  late Map<String, int> userInventory;
  String selectedPlant = 'tree1';

  @override
  Future<void> onLoad() async {
    final grassSprite = await loadSprite('png/g1341.png');
    final world = World();
    add(world);

    final topIso = gridToIso(0, 0);
    final bottomIso = gridToIso(gridWidth - 1, gridHeight - 1);
    final leftIso = gridToIso(0, gridHeight - 1);
    final rightIso = gridToIso(gridWidth - 1, 0);

    final isoHeight = bottomIso.y - topIso.y;
    final isoWidth = rightIso.x - leftIso.x;

    const paddingTop = 150.0;
    const paddingBottom = 100.0;
    const paddingLeft = 100.0;
    const paddingRight = 100.0;

    final availableHeight = size.y - paddingTop - paddingBottom;
    final availableWidth = size.x - paddingLeft - paddingRight;

    final zoomY = availableHeight / isoHeight;
    final zoomX = availableWidth / isoWidth;
    final zoom = zoomX < zoomY ? zoomX : zoomY;

    final centerTileX = gridWidth ~/ 2;
    final centerTileY = gridHeight ~/ 2;
    final isoCenterX = (centerTileX - centerTileY) * tileWidth / 2;
    final adjustedIsoCenterY = topIso.y + isoHeight / 2;

    final camera = CameraComponent.withFixedResolution(
      world: world,
      width: size.x,
      height: size.y,
    )
      ..viewfinder.zoom = zoom
      ..viewfinder.anchor = Anchor.center
      ..viewfinder.position = Vector2(
        isoCenterX,
        adjustedIsoCenterY,
      );

    add(camera);

    for (int y = 0; y < gridHeight; y++) {
      for (int x = 0; x < gridWidth; x++) {
        final isoPos = gridToIso(x, y);
        world.add(SpriteComponent(
          sprite: grassSprite,
          position: isoPos,
          size: Vector2(tileWidth, tileHeight),
          anchor: Anchor.bottomCenter,
        ));
      }
    }
  }

  void loadInventory(Map<String, int> inventory) {
    userInventory = inventory;
  }

  void selectPlant(String plantType) {
    selectedPlant = plantType;
  }

  bool plantAt(int x, int y) {
    final stock = userInventory[selectedPlant] ?? 0;
    if (stock <= 0) return false;

    userInventory[selectedPlant] = stock - 1;
    final pos = gridToIso(x, y);

    loadSprite('png/$selectedPlant.png').then((sprite) {
      final comp = SpriteComponent(
        sprite: sprite,
        position: pos,
        size: Vector2(tileWidth, tileHeight),
        anchor: Anchor.bottomCenter,
      );
      add(comp);
    });

    return true;
  }

  List<Map<String, dynamic>> exportPlantedData() {
    return [
      {
        'x': gridWidth ~/ 2,
        'y': gridHeight ~/ 2,
        'type': selectedPlant,
        'level': 1,
        'waterReceived': 0,
      }
    ];
  }

  Vector2 gridToIso(int x, int y) {
    final isoX = (x - y) * tileWidth / 2;
    final isoY = (x + y) * (tileHeight - tileOverlap) / 2;
    return Vector2(isoX, isoY);
  }
}
