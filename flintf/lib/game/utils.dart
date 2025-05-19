import 'package:flame/components.dart';

const double tileWidth = 128;
const double tileHeight = 64;

Vector2 gridToIso(int x, int y, double originX, double originY) {
  final isoX = (x - y) * tileWidth / 2 + originX;
  final isoY = (x + y) * tileHeight / 2 + originY;
  return Vector2(isoX, isoY);
}
