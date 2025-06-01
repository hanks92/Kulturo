import 'package:flame/components.dart';
import 'package:flame/game.dart';
import 'tile_component.dart';
import 'utils.dart';

class GardenGame extends FlameGame {
  static const int gridWidth = 6;
  static const int gridHeight = 6;

  @override
  Future<void> onLoad() async {
    final grassSprite = await loadSprite('ground/grass.png');
    final world = World();
    add(world);

    final origin = Vector2.zero();

    // 🎯 Marges souhaitées
    const paddingTop = 150.0;
    const paddingBottom = 100.0;
    const paddingLeft = 100.0;
    const paddingRight = 100.0;

    // 📍 Coordonnées isométriques des coins du jardin
    final topIso = gridToIso(0, 0, 0, 0);
    final bottomIso = gridToIso(gridWidth - 1, gridHeight - 1, 0, 0);
    final leftIso = gridToIso(0, gridHeight - 1, 0, 0);
    final rightIso = gridToIso(gridWidth - 1, 0, 0, 0);

    // 📐 Dimensions du jardin en pixels
    final isoHeight = bottomIso.y - topIso.y;
    final isoWidth = rightIso.x - leftIso.x;

    // 📏 Dimensions disponibles à l'écran
    final availableHeight = size.y - paddingTop - paddingBottom;
    final availableWidth = size.x - paddingLeft - paddingRight;

    // 🔍 Calcul du zoom optimal
    final zoomY = availableHeight / isoHeight;
    final zoomX = availableWidth / isoWidth;
    final zoom = zoomX < zoomY ? zoomX : zoomY;

    // 📌 Calcul du centrage horizontal et vertical
    final centerTileX = gridWidth ~/ 2;
    final centerTileY = gridHeight ~/ 2;
    final isoCenterX = (centerTileX - centerTileY) * tileWidth / 2;
    final adjustedIsoCenterY = topIso.y + isoHeight / 2;

    // 🎥 Caméra avec zoom et centrage
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

    // 🧱 Affiche les tuiles
    for (int y = 0; y < gridHeight; y++) {
      for (int x = 0; x < gridWidth; x++) {
        world.add(TileComponent(
          gridX: x,
          gridY: y,
          origin: origin,
          sprite: grassSprite,
        ));
      }
    }
  }
}
