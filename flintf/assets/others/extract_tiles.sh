#!/bin/bash

SOURCE="roadTiles.svg"
OUTPUT_DIR="exported_tiles"
mkdir -p "$OUTPUT_DIR"

# Récupère tous les ids de type gXXXX présents dans le SVG
grep -o 'id="g[0-9]\{4\}"' "$SOURCE" | sed 's/id="//;s/"//' | sort -u | while read ID; do
  echo "Exporting $ID..."
  inkscape "$SOURCE" \
    --export-id="$ID" \
    --export-type=svg \
    --export-filename="$OUTPUT_DIR/$ID.svg"
done
