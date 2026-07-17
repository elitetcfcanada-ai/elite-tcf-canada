"""Regénère favicon.jpg : logo rempli, toque agrandie, 512×512 sans marges blanches."""
from __future__ import annotations

import math
from pathlib import Path

from PIL import Image, ImageDraw, ImageFilter

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "Assets" / "branding" / "favicon.jpg"
SVG = ROOT / "Assets" / "branding" / "favicon.svg"

SIZE = 512
RED = "#d30d0d"
WHITE = "#ffffff"


def draw_logo(size: int = SIZE) -> Image.Image:
    img = Image.new("RGB", (size, size), WHITE)
    draw = ImageDraw.Draw(img)
    cx = cy = size // 2

    # Anneau rouge plein bord à bord (pas de marge blanche autour)
    draw.ellipse((0, 0, size - 1, size - 1), fill=RED)

    # Bande blanche intermédiaire (~40px à 512)
    band_r = int(size * 0.403)  # ~206/512
    draw.ellipse(
        (cx - band_r, cy - band_r, cx + band_r, cy + band_r),
        fill=WHITE,
    )

    # Disque intérieur légèrement remonté (relief)
    inner_r = int(size * 0.316)  # ~162/512
    inner_cy = cy - int(size * 0.016)
    draw.ellipse(
        (cx - inner_r, inner_cy - inner_r, cx + inner_r, inner_cy + inner_r),
        fill=WHITE,
    )

    # Ombre légère sous le disque
    shadow = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    sdraw = ImageDraw.Draw(shadow)
    sdraw.ellipse(
        (cx - inner_r, inner_cy - inner_r + 4, cx + inner_r, inner_cy + inner_r + 4),
        fill=(0, 0, 0, 45),
    )
    shadow = shadow.filter(ImageFilter.GaussianBlur(radius=6))
    img = Image.alpha_composite(img.convert("RGBA"), shadow).convert("RGB")
    draw = ImageDraw.Draw(img)

    # Toque agrandie (~1.45× par rapport à l’ancienne version)
    scale = size / 512.0 * 1.45
    ox, oy = cx, inner_cy

    def pt(x: float, y: float) -> tuple[float, float]:
        return ox + x * scale, oy + y * scale

    # Plateau (losange)
    top = [
        pt(0, -52),
        pt(78, -22),
        pt(0, 8),
        pt(-78, -22),
    ]
    draw.polygon(top, fill=RED)

    # Base de la toque
    base = [
        pt(-46, 2),
        pt(-18, 28),
        pt(0, 28),
        pt(18, 28),
        pt(46, 2),
        pt(46, 34),
        pt(18, 52),
        pt(0, 52),
        pt(-18, 52),
        pt(-46, 34),
    ]
    draw.polygon(base, fill=RED)

    # Encoche gauche
    notch = [pt(-46, 8), pt(-38, 8), pt(-38, 30), pt(-46, 30)]
    draw.polygon(notch, fill=WHITE)

    return img


def write_svg() -> None:
    svg = """<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" role="img" aria-label="ELITE TCF CANADA">
  <defs>
    <filter id="inner-shadow" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="0" dy="6" stdDeviation="8" flood-color="#000000" flood-opacity="0.18"/>
    </filter>
    <filter id="ring-shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="-4" dy="2" stdDeviation="3" flood-color="#000000" flood-opacity="0.12"/>
    </filter>
  </defs>
  <circle cx="256" cy="256" r="256" fill="#d30d0d"/>
  <circle cx="256" cy="256" r="206" fill="#ffffff" filter="url(#ring-shadow)"/>
  <circle cx="256" cy="248" r="162" fill="#ffffff" filter="url(#inner-shadow)"/>
  <g transform="translate(256 248) scale(1.45)" fill="#d30d0d">
    <path d="M0 -52 L78 -22 L0 8 L-78 -22 Z"/>
    <path d="M-46 2 C-46 2 -18 28 0 28 C18 28 46 2 46 2 L46 34 C46 34 18 52 0 52 C-18 52 -46 34 -46 34 Z"/>
    <path d="M-46 8 L-38 8 L-38 30 L-46 30 Z" fill="#ffffff"/>
  </g>
</svg>
"""
    SVG.write_text(svg, encoding="utf-8")


def main() -> None:
    write_svg()
    logo = draw_logo(SIZE)
    logo.save(OUT, format="JPEG", quality=92, optimize=True)
    print(f"OK {OUT} -> {logo.size[0]}x{logo.size[1]}")


if __name__ == "__main__":
    main()
