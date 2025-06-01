import 'package:flame/components.dart';
import 'package:flame/game.dart';
import 'package:flame_svg/flame_svg.dart'; // ✅ Import pour SVG
import 'utils.dart';

class GardenGame extends FlameGame {
  static const int gridWidth = 6;
  static const int gridHeight = 6;

  @override
  Future<void> onLoad() async {
    super.onLoad();

    // Charge une instance SVG (une seule fois)
    final svgInstance = await Svg.load('images/svg/g1341.svg');

    final world = World();
    add(world);

    // Configuration de la caméra (centrage + zoom)
    const paddingTop = 150.0, paddingBottom = 100.0, paddingLeft = 100.0, paddingRight = 100.0;
    final topIso = gridToIso(0, 0, 0, 0);
    final bottomIso = gridToIso(gridWidth - 1, gridHeight - 1, 0, 0);
    final leftIso = gridToIso(0, gridHeight - 1, 0, 0);
    final rightIso = gridToIso(gridWidth - 1, 0, 0, 0);
    final isoHeight = bottomIso.y - topIso.y;
    final isoWidth = rightIso.x - leftIso.x;
    final availableHeight = size.y - paddingTop - paddingBottom;
    final availableWidth = size.x - paddingLeft - paddingRight;
    final zoomY = availableHeight / isoHeight;
    final zoomX = availableWidth / isoWidth;
    final zoom = zoomX < zoomY ? zoomX : zoomY;
    final centerTileX = gridWidth ~/ 2;
    final centerTileY = gridHeight ~/ 2;
    final isoCenterX = (centerTileX - centerTileY) * tileWidth / 2;
    final adjustedIsoCenterY = topIso.y + isoHeight / 2;

    add(CameraComponent.withFixedResolution(
      world: world,
      width: size.x,
      height: size.y,
    )
      ..viewfinder.zoom = zoom
      ..viewfinder.anchor = Anchor.center
      ..viewfinder.position = Vector2(isoCenterX, adjustedIsoCenterY),
    );

    // Ajoute une tuile SVG pour chaque case
    for (var y = 0; y < gridHeight; y++) {
      for (var x = 0; x < gridWidth; x++) {
        final positionIso = gridToIso(x, y, 0, 0);
        final svgTile = SvgComponent(
          svg: svgInstance,
          position: positionIso,
          size: Vector2(tileWidth, tileHeight),
          anchor: Anchor.center,
        );
        world.add(svgTile);
      }
    }
  }
}
