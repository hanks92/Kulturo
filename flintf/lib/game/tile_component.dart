import 'package:flame/components.dart';
import 'utils.dart';

class TileComponent extends SpriteComponent {
  final int gridX;
  final int gridY;

  TileComponent({
    required this.gridX,
    required this.gridY,
    required Vector2 origin,
    required Sprite sprite,
  }) : super(
          sprite: sprite,
          size: Vector2(tileWidth, tileHeight),
          anchor: Anchor.bottomCenter,
          position: gridToIso(gridX, gridY, origin.x, origin.y),
        );
}
