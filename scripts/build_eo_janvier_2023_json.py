# -*- coding: utf-8 -*-
import json
from pathlib import Path
from eo_jan_2023_t2 import TACHE2
from eo_jan_2023_t3 import TACHE3

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "database" / "seeds" / "exp_orale" / "janvier_2023.json"

payload = {
    "slug": "eo-expression-orale-janvier-2023",
    "title": "Expression Orale Janvier 2023",
    "subtitle": (
        "Découvrez les nouveaux sujets de l'expression orale qui se répètent. "
        "Pratiquez sur ces thèmes afin d'obtenir de bonnes notes."
    ),
    "parts": TACHE2 + TACHE3,
}
OUT.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
n = sum(len(p["subjects"]) for p in payload["parts"])
print(f"OK - {len(payload['parts'])} parties, {n} sujets -> {OUT}")
