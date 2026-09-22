"""Régénère icon-192/512, apple-touch-icon et favicon.jpg STRICTEMENT depuis favicon.svg."""
from __future__ import annotations

from pathlib import Path
from PIL import Image, ImageDraw

root = Path(__file__).resolve().parents[1] / 'Assets' / 'branding'
RED = (211, 13, 13)
WHITE = (255, 255, 255)


def _bezier(p0, p1, p2, p3, steps=24):
    pts = []
    for i in range(steps + 1):
        t = i / steps
        u = 1 - t
        x = u * u * u * p0[0] + 3 * u * u * t * p1[0] + 3 * u * t * t * p2[0] + t * t * t * p3[0]
        y = u * u * u * p0[1] + 3 * u * u * t * p1[1] + 3 * u * t * t * p2[1] + t * t * t * p3[1]
        pts.append((x, y))
    return pts


def draw_favicon_svg(size: int, *, pad_ratio: float = 0.0, bg=WHITE) -> Image.Image:
    """
    Reproduit Assets/branding/favicon.svg (viewBox 0 0 512 512).
    pad_ratio > 0 : réserve une marge blanche (icônes PWA / apple-touch).
    """
    img = Image.new('RGB', (size, size), bg)
    draw = ImageDraw.Draw(img)

    # Zone utile après padding (comme _icon_padded.svg scale ~0.72)
    usable = size * (1.0 - 2.0 * pad_ratio)
    ox = size * pad_ratio
    oy = size * pad_ratio
    s = usable / 512.0

    def X(x: float) -> float:
        return ox + x * s

    def Y(y: float) -> float:
        return oy + y * s

    def ellipse(cx, cy, r, fill):
        draw.ellipse([X(cx - r), Y(cy - r), X(cx + r), Y(cy + r)], fill=fill)

    # Cercles du SVG
    ellipse(256, 256, 256, RED)
    ellipse(256, 256, 206, WHITE)
    ellipse(256, 248, 162, WHITE)

    # Feuille : translate(256 248) scale(1.15)
    def L(x: float, y: float):
        return (X(256 + x * 1.15), Y(248 + y * 1.15))

    top = [L(0, -52), L(78, -22), L(0, 8), L(-78, -22)]
    draw.polygon(top, fill=RED)

    # path: M-46 2 C-46 2 -18 28 0 28 C18 28 46 2 46 2 L46 34 C46 34 18 52 0 52 C-18 52 -46 34 -46 34 Z
    base = []
    base += _bezier(L(-46, 2), L(-46, 2), L(-18, 28), L(0, 28))
    base += _bezier(L(0, 28), L(18, 28), L(46, 2), L(46, 2))[1:]
    base.append(L(46, 34))
    base += _bezier(L(46, 34), L(46, 34), L(18, 52), L(0, 52))[1:]
    base += _bezier(L(0, 52), L(-18, 52), L(-46, 34), L(-46, 34))[1:]
    draw.polygon(base, fill=RED)

    cut = [L(-46, 8), L(-38, 8), L(-38, 30), L(-46, 30)]
    draw.polygon(cut, fill=WHITE)
    return img


def main() -> None:
    jobs = [
        ('icon-512.png', 512, 0.14),
        ('icon-192.png', 192, 0.14),
        ('apple-touch-icon.png', 180, 0.12),
        ('favicon.jpg', 512, 0.0),  # = favicon.svg, sans marge
    ]
    for name, size, pad in jobs:
        im = draw_favicon_svg(size, pad_ratio=pad, bg=WHITE)
        out = root / name
        if name.endswith('.jpg'):
            im.save(out, 'JPEG', quality=94, optimize=True)
        else:
            im.save(out, 'PNG', optimize=True)
        print('wrote', out.name)


if __name__ == '__main__':
    main()
