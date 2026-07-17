# -*- coding: utf-8 -*-
"""Genere database/seeds/exp_orale/novembre_2025.json pour seed_eo_novembre_2025.php."""
import json
from pathlib import Path

from eo_nov_2025_t2 import TACHE2
from eo_nov_2025_t3 import TACHE3

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "database" / "seeds" / "exp_orale" / "novembre_2025.json"

SUBTITLE = (
    "Découvrez les nouveaux sujets de l'expression orale qui se répètent. "
    "Pratiquez sur ces thèmes afin d'obtenir de bonnes notes."
)

payload = {
    "slug": "eo-expression-orale-novembre-2025",
    "title": "Expression Orale Novembre 2025",
    "subtitle": SUBTITLE,
    "parts": TACHE2 + TACHE3,
}

OUT.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
n_sub = sum(len(p["subjects"]) for p in payload["parts"])
print(f"OK - {len(payload['parts'])} parties, {n_sub} sujets -> {OUT}")
