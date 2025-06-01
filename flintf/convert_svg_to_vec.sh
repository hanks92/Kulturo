#!/bin/bash

INPUT_DIR="assets/images/svg"
EXT=".svg"
OUTPUT_EXT=".svg.vec"

echo "🛠️ Compilation des SVG en fichiers .vec..."

for svg in "$INPUT_DIR"/*"$EXT"; do
  if [ -f "$svg" ]; then
    filename=$(basename -- "$svg")
    base="${filename%$EXT}"
    output="$INPUT_DIR/$base$OUTPUT_EXT"
    echo "📦 $filename -> $output"
    dart run vector_graphics_compiler -i "$svg" -o "$output"
  fi
done

echo "✅ Conversion terminée !"
