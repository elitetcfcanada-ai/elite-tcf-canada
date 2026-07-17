# -*- coding: utf-8 -*-
"""Transcript -> JSON EO (rapide)."""
import json
import re
from pathlib import Path

OUT = Path(__file__).resolve().parent.parent / "database" / "seeds" / "exp_orale"
TRANSCRIPT = Path(
    r"C:\Users\MAKUI DJIBRILE\.cursor\projects\c-xampp-htdocs-tcf4\agent-transcripts"
    r"\04082374-1753-4550-91d3-829a98c810cd\04082374-1753-4550-91d3-829a98c810cd.jsonl"
)
SUBTITLE = (
    "Découvrez les nouveaux sujets de l'expression orale qui se répètent. "
    "Pratiquez sur ces thèmes afin d'obtenir de bonnes notes."
)
EMOJI = re.compile(
    r"[\U0001F300-\U0001FAFF\U00002700-\U000027BF\U00002600-\U000026FF"
    r"\U0001F1E0-\U0001F1FF\u2ff2\u2ff3\u2705\uFE0F\u20E3\u2060]+"
)
T3_SPLIT = re.compile(r"\]?\s*tache\s*3\s*\[", re.I | re.S)
PARTIE_RE = re.compile(r"Partie (\d+)\n\d+ sujets", re.M)

EXAMS = [
    {
        "needle": ("decembre 2025", "canadien"),
        "file": "decembre_2025.json",
        "slug": "eo-expression-orale-decembre-2025",
        "title": "Expression Orale Décembre 2025",
    },
    {
        "needle": ("janvier 2023", "restaurant"),
        "file": "janvier_2026.json",
        "slug": "eo-expression-orale-janvier-2026",
        "title": "Expression Orale Janvier 2026",
    },
]


def clean(t: str) -> str:
    lines = []
    for ln in t.splitlines():
        ln = EMOJI.sub("", ln).strip()
        if ln in ("Masquer", "Exemple de réponse", "Voir la correction"):
            continue
        if ln:
            lines.append(ln)
    return "\n".join(lines).strip()


def load_msg(needles: tuple[str, str]) -> str:
    with TRANSCRIPT.open(encoding="utf-8") as f:
        for line in f:
            o = json.loads(line)
            if o.get("role") != "user":
                continue
            t = o["message"]["content"][0].get("text", "")
            if all(n in t.lower() for n in needles):
                return t
    raise SystemExit(f"Message introuvable: {needles}")


def _strip_headers(chunk: str) -> str:
    lines = chunk.strip().splitlines()
    out = []
    for ln in lines:
        s = ln.strip()
        if not s:
            continue
        if re.match(r"^Partie \d+", s):
            continue
        if re.match(r"^\d+ sujets$", s):
            continue
        if s in ("Présentation", "Interaction orale", "Argumentation"):
            continue
        if re.match(r"^Tâche \d", s):
            continue
        out.append(ln)
    return "\n".join(out).strip()


def parse_chunk(chunk: str) -> tuple[str, str]:
    chunk = _strip_headers(chunk)
    if not chunk:
        return "", ""
    if "Aucun sujet disponible" in chunk and "Masquer" not in chunk and "Voir la correction" not in chunk:
        return "", ""
    if "Masquer" in chunk:
        p, rest = chunk.split("Masquer", 1)
        c = rest.split("Exemple de réponse", 1)[1] if "Exemple de réponse" in rest else ""
    elif "Voir la correction" in chunk:
        p, _ = chunk.split("Voir la correction", 1)
        c = ""
    else:
        lines = [x.strip() for x in chunk.splitlines() if x.strip()]
        p = lines[0] if lines else ""
        c = "\n".join(lines[1:])
    pl = []
    for ln in p.splitlines():
        ln = ln.strip()
        if not ln or re.match(r"^(Tâche|Présentation|Interaction|Argumentation|Méthodologie|Prép\.|\d+ sujets)", ln):
            continue
        if ln.startswith("Partie "):
            break
        pl.append(ln)
    return "\n".join(pl).strip(), clean(c)


def _renumber_parts(parts: list[dict]) -> list[dict]:
    by_task: dict[str, list[dict]] = {}
    for p in parts:
        by_task.setdefault(p["task_key"], []).append(p)
    out = []
    for task_key in sorted(by_task.keys()):
        group = sorted(by_task[task_key], key=lambda x: x["part_number"])
        for i, p in enumerate(group, 1):
            out.append({**p, "part_number": i, "part_title": f"Partie {i}"})
    return out


def extract_parts(block: str, sep: str, task_key: str) -> list[dict]:
    matches = list(PARTIE_RE.finditer(block))
    out = []
    for i, m in enumerate(matches):
        start = m.end()
        end = matches[i + 1].start() if i + 1 < len(matches) else len(block)
        section = block[start:end]
        if "Aucun sujet disponible" in section and sep not in section:
            continue
        chunks = [c for c in re.split(sep, section)[1:] if parse_chunk(c)[0]]
        if not chunks:
            continue
        subs = []
        for j, ch in enumerate(chunks, 1):
            p, c = parse_chunk(ch)
            subs.append({
                "title": f"Sujet {j}",
                "prompt": p,
                "correction": c,
                "icon_class": "bx bx-message-detail",
            })
        out.append({
            "task_key": task_key,
            "part_number": int(m.group(1)),
            "part_title": f"Partie {m.group(1)}",
            "subjects": subs,
        })
    return out


def parts_struct(parts: list[dict]) -> str:
    return ",".join(f"{p['task_key']}:{p['part_number']}:{len(p['subjects'])}" for p in parts)


def build_exam(text: str) -> tuple[list[dict], int, int]:
    blocks = T3_SPLIT.split(text, maxsplit=1)
    if len(blocks) < 2:
        raise SystemExit("Separateur tache 3 introuvable")
    t2_parts = extract_parts(blocks[0], r"\n3 min 30 s\n4 essais\n\n", "tache2")
    t3_parts = extract_parts(blocks[1], r"\n4 min 30 s\n4 essais\n\n", "tache3")
    parts = _renumber_parts(t2_parts + t3_parts)
    t2_n = sum(len(p["subjects"]) for p in parts if p["task_key"] == "tache2")
    t3_n = sum(len(p["subjects"]) for p in parts if p["task_key"] == "tache3")
    return parts, t2_n, t3_n


def main() -> None:
    for ex in EXAMS:
        text = load_msg(ex["needle"])
        parts, t2_n, t3_n = build_exam(text)
        n = sum(len(p["subjects"]) for p in parts)
        payload = {
            "slug": ex["slug"],
            "title": ex["title"],
            "subtitle": SUBTITLE,
            "parts": parts,
            "_expected": n,
            "_struct": parts_struct(parts),
            "_seed_rev": 2,
        }
        path = OUT / ex["file"]
        path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
        print(f"OK {ex['title']}: T2={t2_n} T3={t3_n} -> {n} sujets ({path.name})")


if __name__ == "__main__":
    main()
