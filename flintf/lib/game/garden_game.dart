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

    // 📍 Tuile en haut [0,0] et en bas [5,5]
    final topIso = gridToIso(0, 0, 0, 0);
    final bottomIso = gridToIso(gridWidth - 1, gridHeight - 1, 0, 0);

    // 📐 Dimensions en pixels du jardin (approximativement en Y)
    final isoHeight = bottomIso.y - topIso.y;

    // 🎯 Marges souhaitées
    const paddingTop = 150.0;
    const paddingBottom = 100.0;

    // 📏 Zoom calculé pour que le jardin tienne entre les deux marges verticales
    final availableHeight = size.y - paddingTop - paddingBottom;
    final zoom = availableHeight / isoHeight;

    // 🔍 Centre horizontalement (milieu de la grille)
    final centerTileX = gridWidth ~/ 2;
    final centerTileY = gridHeight ~/ 2;
    final isoCenterX = (centerTileX - centerTileY) * tileWidth / 2;

    // 🧮 Ajuste la position Y pour que la tuile du haut corresponde à paddingTop
    final screenCenterY = size.y / 2;
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
