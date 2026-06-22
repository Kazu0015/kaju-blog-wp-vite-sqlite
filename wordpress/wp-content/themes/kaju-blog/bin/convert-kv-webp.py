#!/usr/bin/env python3
"""
KV 用画像を WebP に変換し、PC 用の合成画像も生成する。

使い方（テーマ直下）:
  python3 bin/convert-kv-webp.py

入力: img/top/org/photo-*.jpg（または png）
出力: img/top/photo-*.webp, img/top/img-top-kv.webp
"""

from __future__ import annotations

from pathlib import Path

from PIL import Image

THEME_DIR = Path(__file__).resolve().parents[1]
IMG_TOP = THEME_DIR / "img" / "top"
ORG_DIR = IMG_TOP / "org"

KV_SLIDES = (
    "photo-peach",
    "photo-cherry",
    "photo-grape",
    "photo-plum",
    "photo-blueberry",
    "photo-prune",
)

WEBP_QUALITY = 85


def find_source(name: str) -> Path | None:
    for ext in (".jpg", ".jpeg", ".png"):
        path = ORG_DIR / f"{name}{ext}"
        if path.is_file():
            return path
    return None


def convert_slide(name: str) -> Image.Image:
    src = find_source(name)
    if src is None:
        raise SystemExit(f"source not found for {name} in {ORG_DIR}")

    out = IMG_TOP / f"{name}.webp"
    img = Image.open(src).convert("RGB")
    img.save(out, "WEBP", quality=WEBP_QUALITY, method=6)
    print(f"wrote {out.relative_to(THEME_DIR)} ({img.size[0]}x{img.size[1]})")
    return img


def build_combined(images: list[Image.Image]) -> None:
    w, h = images[0].size
    cols, rows = 3, 2
    canvas = Image.new("RGB", (w * cols, h * rows))
    for idx, img in enumerate(images):
        if img.size != (w, h):
            raise SystemExit(f"size mismatch at index {idx}: {img.size} vs {(w, h)}")
        col, row = idx % cols, idx // cols
        canvas.paste(img, (col * w, row * h))

    out = IMG_TOP / "img-top-kv.webp"
    canvas.save(out, "WEBP", quality=WEBP_QUALITY, method=6)
    print(f"wrote {out.relative_to(THEME_DIR)} ({canvas.size[0]}x{canvas.size[1]})")


def main() -> None:
    slides = [convert_slide(name) for name in KV_SLIDES]
    build_combined(slides)


if __name__ == "__main__":
    main()
