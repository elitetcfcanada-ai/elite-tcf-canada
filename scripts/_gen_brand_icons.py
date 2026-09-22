"""Génère icon-192/512, apple-touch-icon et favicon.jpg depuis le design favicon.svg."""
from pathlib import Path
from PIL import Image, ImageDraw
import math

root = Path(r'c:\xampp\htdocs\elite-TCFCanada\Assets\branding')
RED = (211, 13, 13)
WHITE = (255, 255, 255)


def draw_logo(size: int, pad_ratio: float = 0.14) -> Image.Image:
    """Logo cercle rouge + feuille, avec marge (pad_ratio) pour install PWA."""
    img = Image.new('RGB', (size, size), WHITE)
    draw = ImageDraw.Draw(img)
    pad = int(size * pad_ratio)
    box = [pad, pad, size - pad - 1, size - pad - 1]
    # Cercle rouge plein
    draw.ellipse(box, fill=RED)
    # Anneau blanc
    inner1 = int(size * (pad_ratio + 0.10))
    box2 = [inner1, inner1, size - inner1 - 1, size - inner1 - 1]
    draw.ellipse(box2, fill=WHITE)
    # Disque blanc intérieur (zone feuille)
    inner2 = int(size * (pad_ratio + 0.18))
    box3 = [inner2, inner2, size - inner2 - 1, size - inner2 - 1]
    draw.ellipse(box3, fill=WHITE)

    # Feuille d'érable simplifiée (paths SVG approximés, scale réduit)
    cx = cy = size / 2
    s = size * 0.22  # plus petit que l'ancien scale 1.45
    cy -= size * 0.015

    def pt(x, y):
        return (cx + x * s, cy + y * s)

    # Losange supérieur
    top = [pt(0, -1.0), pt(1.5, -0.42), pt(0, 0.15), pt(-1.5, -0.42)]
    draw.polygon(top, fill=RED)
    # Base / tige
    base = [
        pt(-0.88, 0.04),
        pt(-0.35, 0.54),
        pt(0, 0.54),
        pt(0.35, 0.54),
        pt(0.88, 0.04),
        pt(0.88, 0.65),
        pt(0.35, 1.0),
        pt(0, 1.0),
        pt(-0.35, 1.0),
        pt(-0.88, 0.65),
    ]
    draw.polygon(base, fill=RED)
    # Petite découpe blanche (détail SVG)
    cut = [pt(-0.88, 0.15), pt(-0.73, 0.15), pt(-0.73, 0.58), pt(-0.88, 0.58)]
    draw.polygon(cut, fill=WHITE)
    return img


for name, size, pad in [
    ('icon-512.png', 512, 0.14),
    ('icon-192.png', 192, 0.14),
    ('apple-touch-icon.png', 180, 0.12),
    ('favicon.jpg', 512, 0.10),
]:
    im = draw_logo(size, pad)
    if name.endswith('.jpg'):
        im.save(root / name, 'JPEG', quality=92, optimize=True)
    else:
        im.save(root / name, 'PNG', optimize=True)
    print('wrote', name)
