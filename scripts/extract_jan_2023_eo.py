# -*- coding: utf-8 -*-
"""Parse Expression orale Janvier 2023 et genere les fichiers seed."""
import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SOURCE = ROOT / "database" / "seeds" / "exp_orale" / "janvier_2023_source.txt"
T3_DIR = ROOT / "database" / "seeds" / "exp_orale" / "janvier_2023_t3"
SCRIPTS = Path(__file__).resolve().parent

EMOJI_RE = re.compile(
    r"[\U0001F300-\U0001FAFF\U00002700-\U000027BF\U00002600-\U000026FF"
    r"\U0001F1E0-\U0001F1FF\u2ff2\u2ff3\u2705\uFE0F\u20E3]+"
)


def clean(text: str) -> str:
    lines = []
    for line in text.splitlines():
        line = EMOJI_RE.sub("", line).strip()
        if line in ("Masquer", "Exemple de réponse", "Voir la correction"):
            continue
        if line:
            lines.append(line)
    return "\n".join(lines).strip()


def load_text() -> str:
    if len(sys.argv) > 1:
        return Path(sys.argv[1]).read_text(encoding="utf-8")
    if not SOURCE.is_file():
        raise SystemExit(f"Source introuvable: {SOURCE}")
    return SOURCE.read_text(encoding="utf-8")


def split_block(text: str, start_pat: str, end_pat: str | None) -> str:
    m = re.search(start_pat, text, re.I | re.S)
    if not m:
        raise SystemExit(f"Bloc introuvable: {start_pat}")
    start = m.end()
    if end_pat:
        m2 = re.search(end_pat, text[start:], re.I | re.S)
        block = text[start : start + m2.start()] if m2 else text[start:]
    else:
        block = text[start:]
    return block


def parse_chunks(block: str, pat: str) -> list[str]:
    return [c.strip() for c in re.split(pat, block) if c.strip()]


def parse_subject(chunk: str) -> tuple[str, str]:
    if "Aucun sujet disponible" in chunk:
        return "", ""
    if "Masquer" in chunk:
        prompt_part, rest = chunk.split("Masquer", 1)
        prompt = prompt_part.strip()
        if "Exemple de réponse" in rest:
            _, corr = rest.split("Exemple de réponse", 1)
        else:
            corr = rest
    elif "Voir la correction" in chunk:
        prompt_part, _ = chunk.split("Voir la correction", 1)
        prompt = prompt_part.strip()
        corr = ""
    else:
        lines = chunk.splitlines()
        prompt = lines[0].strip() if lines else ""
        corr = "\n".join(lines[1:]).strip()
    plines = []
    for ln in prompt.splitlines():
        ln = ln.strip()
        if not ln:
            continue
        if re.match(r"^(Tâche|Présentation|Interaction|Argumentation|Méthodologie|Prép\.|\d+ sujets)", ln):
            continue
        if ln.startswith("Partie "):
            break
        plines.append(ln)
    return "\n".join(plines).strip(), clean(corr)


def py_str(s: str) -> str:
    return json.dumps(s, ensure_ascii=False)


def write_t2(parts: list[list[tuple[str, str]]]) -> None:
    lines = [
        "# -*- coding: utf-8 -*-",
        '"""Contenu Tache 2 — Expression orale Janvier 2023 (texte source exact)."""',
        "",
        "def sub(title, prompt, correction, icon=\"bx bx-message-detail\"):",
        "    return {\"title\": title, \"prompt\": prompt.strip(), \"correction\": correction.strip(), \"icon_class\": icon}",
        "",
        "def part(task_key, part_num, subjects):",
        "    return {\"task_key\": task_key, \"part_number\": part_num, \"part_title\": f\"Partie {part_num}\", \"subjects\": subjects}",
        "",
        "TACHE2 = [",
    ]
    for pnum, subs in enumerate(parts, 1):
        lines.append(f'    part("tache2", {pnum}, [')
        for snum, (p, c) in enumerate(subs, 1):
            lines.append(f'        sub("Sujet {snum}", {py_str(p)}, {py_str(c)}),')
        lines.append("    ]),")
    lines.append("]")
    (SCRIPTS / "eo_jan_2023_t2.py").write_text("\n".join(lines) + "\n", encoding="utf-8")
    print(f"OK eo_jan_2023_t2.py — {sum(len(x) for x in parts)} sujets")


def write_t3(parts: list[list[tuple[str, str]]]) -> None:
    T3_DIR.mkdir(parents=True, exist_ok=True)
    lines = [
        "# -*- coding: utf-8 -*-",
        '"""Contenu Tache 3 — Expression orale Janvier 2023 (texte source exact)."""',
        "from pathlib import Path",
        "_CORR_DIR = Path(__file__).resolve().parent.parent / \"database\" / \"seeds\" / \"exp_orale\" / \"janvier_2023_t3\"",
        "",
        "def sub(title, prompt, correction_file=None, icon=\"bx bx-message-detail\"):",
        "    correction = \"\"",
        "    if correction_file:",
        "        p = _CORR_DIR / correction_file",
        "        if p.is_file(): correction = p.read_text(encoding=\"utf-8\").strip()",
        "    return {\"title\": title, \"prompt\": prompt.strip(), \"correction\": correction, \"icon_class\": icon}",
        "",
        "def part(task_key, part_num, subjects):",
        "    return {\"task_key\": task_key, \"part_number\": part_num, \"part_title\": f\"Partie {part_num}\", \"subjects\": subjects}",
        "",
        "TACHE3 = [",
    ]
    for pnum, subs in enumerate(parts, 1):
        lines.append(f'    part("tache3", {pnum}, [')
        for snum, (p, c) in enumerate(subs, 1):
            fname = f"p{pnum}_s{snum}.txt"
            if c:
                (T3_DIR / fname).write_text(c, encoding="utf-8")
            elif (T3_DIR / fname).is_file():
                (T3_DIR / fname).unlink()
            cf = py_str(fname) if c else "None"
            lines.append(f'        sub("Sujet {snum}", {py_str(p)}, {cf}),')
        lines.append("    ]),")
    lines.append("]")
    (SCRIPTS / "eo_jan_2023_t3.py").write_text("\n".join(lines) + "\n", encoding="utf-8")
    print(f"OK eo_jan_2023_t3.py — {sum(len(x) for x in parts)} sujets")


def main() -> None:
    text = load_text()
    t2 = split_block(text, r"tache\s*2\s*\[", r"\]\s*tache3\s*\[")
    t3 = split_block(text, r"\]\s*tache3\s*\[", None)

    t2_chunks = [
        c
        for c in parse_chunks(t2, r"\n3 min 30 s\n4 essais\n\n")
        if "Masquer" in c or "Voir la correction" in c or re.match(r"^(Je |Nous |Vous )", c.strip())
    ]
    t3_chunks = [
        c
        for c in parse_chunks(t3, r"\n4 min 30 s\n4 essais\n\n")
        if "Aucun sujet disponible" not in c
        and (
            "Masquer" in c
            or "Voir la correction" in c
            or re.match(r"^(Il |Les |Pensez|De nos|Pour |Comment|Quel|À votre|Analysez|Certaines|Aujourd|Est-ce|On peut|Si vous|Consommer|Faut-il|La |Quand )", c.strip())
        )
    ]

    t2_parts = [t2_chunks[i : i + 5] for i in range(0, len(t2_chunks), 5)]
    t3_parts = [t3_chunks[i : i + 5] for i in range(0, len(t3_chunks), 5)]

    t2_parts = [[parse_subject(c) for c in p] for p in t2_parts]
    t3_parts = [[parse_subject(c) for c in p] for p in t3_parts]

    print(f"T2: {len(t2_parts)} parties, {sum(len(p) for p in t2_parts)} sujets")
    print(f"T3: {len(t3_parts)} parties, {sum(len(p) for p in t3_parts)} sujets")
    write_t2(t2_parts)
    write_t3(t3_parts)


if __name__ == "__main__":
    main()
