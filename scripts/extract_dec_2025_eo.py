# -*- coding: utf-8 -*-
"""Parse le message source Expression orale Decembre 2025 et genere les fichiers seed."""
import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
SOURCE = ROOT / "database" / "seeds" / "exp_orale" / "decembre_2025_source.txt"
T3_DIR = ROOT / "database" / "seeds" / "exp_orale" / "decembre_t3"
SCRIPTS = Path(__file__).resolve().parent

EMOJI_RE = re.compile(
    r"[\U0001F300-\U0001FAFF\U00002700-\U000027BF\U00002600-\U000026FF"
    r"\U0001F1E0-\U0001F1FF\u2ff2\u2ff3\u2705\U0001f94f\U0001f535]+"
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


def parse_chunks(block: str, duration_pat: str) -> list[str]:
    chunks = re.split(duration_pat, block)
    return [c.strip() for c in chunks if c.strip()]


def parse_subject_chunk(chunk: str) -> tuple[str, str]:
    chunk = chunk.strip()
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
        lines = [ln for ln in chunk.splitlines() if ln.strip()]
        prompt = lines[0].strip() if lines else ""
        corr = "\n".join(lines[1:]).strip()
    # retirer en-tetes UI residuels dans le prompt
    prompt_lines = []
    for ln in prompt.splitlines():
        ln = ln.strip()
        if not ln or ln.startswith("Tâche ") or ln == "Interaction orale" or ln == "Argumentation":
            continue
        if ln.startswith("Prép.") or ln.startswith("Méthodologie"):
            continue
        if re.match(r"^\d+ sujets$", ln):
            continue
        if ln in ("Présentation", "Interaction orale", "Argumentation"):
            continue
        if ln.startswith("Tâche 2 :") or ln.startswith("Tâche 3 :"):
            continue
        if ln.startswith("Partie "):
            break
        prompt_lines.append(ln)
    prompt = "\n".join(prompt_lines).strip()
    return prompt, clean(corr)


def group_by_parties(chunks: list[str], expected_per_part: int) -> list[list[tuple[str, str]]]:
    """Decoupe une liste de sujets en parties de N sujets."""
    subjects = [parse_subject_chunk(c) for c in chunks]
    subjects = [(p, c) for p, c in subjects if p]
    parts = []
    i = 0
    while i < len(subjects):
        parts.append(subjects[i : i + expected_per_part])
        i += expected_per_part
    return parts


def py_str(s: str) -> str:
    return json.dumps(s, ensure_ascii=False)


def write_t2(parts: list[list[tuple[str, str]]]) -> None:
    lines = [
        "# -*- coding: utf-8 -*-",
        '"""Contenu Tache 2 — Expression orale Decembre 2025 (texte source exact)."""',
        "",
        "",
        "def sub(title, prompt, correction, icon=\"bx bx-message-detail\"):",
        "    return {",
        '        "title": title,',
        '        "prompt": prompt.strip(),',
        '        "correction": correction.strip(),',
        '        "icon_class": icon,',
        "    }",
        "",
        "",
        "def part(task_key, part_num, subjects):",
        "    return {",
        '        "task_key": task_key,',
        '        "part_number": part_num,',
        '        "part_title": f"Partie {part_num}",',
        '        "subjects": subjects,',
        "    }",
        "",
        "",
        "TACHE2 = [",
    ]
    for pnum, part_subjects in enumerate(parts, 1):
        lines.append(f'    part("tache2", {pnum}, [')
        for snum, (prompt, corr) in enumerate(part_subjects, 1):
            lines.append(f'        sub("Sujet {snum}", {py_str(prompt)}, {py_str(corr)}),')
        lines.append("    ]),")
    lines.append("]")
    out = SCRIPTS / "eo_dec_2025_t2.py"
    out.write_text("\n".join(lines) + "\n", encoding="utf-8")
    n = sum(len(p) for p in parts)
    print(f"OK eo_dec_2025_t2.py — {len(parts)} parties, {n} sujets")


def write_t3(parts: list[list[tuple[str, str]]]) -> None:
    T3_DIR.mkdir(parents=True, exist_ok=True)
    lines = [
        "# -*- coding: utf-8 -*-",
        '"""Contenu Tache 3 — Expression orale Decembre 2025 (texte source exact)."""',
        "from pathlib import Path",
        "",
        "_CORR_DIR = Path(__file__).resolve().parent.parent / \"database\" / \"seeds\" / \"exp_orale\" / \"decembre_t3\"",
        "",
        "",
        "def sub(title, prompt, correction_file=None, icon=\"bx bx-message-detail\"):",
        "    correction = \"\"",
        "    if correction_file:",
        "        path = _CORR_DIR / correction_file",
        "        if path.is_file():",
        "            correction = path.read_text(encoding=\"utf-8\").strip()",
        "    return {",
        '        "title": title,',
        '        "prompt": prompt.strip(),',
        '        "correction": correction,',
        '        "icon_class": icon,',
        "    }",
        "",
        "",
        "def part(task_key, part_num, subjects):",
        "    return {",
        '        "task_key": task_key,',
        '        "part_number": part_num,',
        '        "part_title": f"Partie {part_num}",',
        '        "subjects": subjects,',
        "    }",
        "",
        "",
        "TACHE3 = [",
    ]
    total = 0
    for pnum, part_subjects in enumerate(parts, 1):
        lines.append(f'    part("tache3", {pnum}, [')
        for snum, (prompt, corr) in enumerate(part_subjects, 1):
            fname = f"p{pnum}_s{snum}.txt"
            if corr:
                (T3_DIR / fname).write_text(corr, encoding="utf-8")
            else:
                fpath = T3_DIR / fname
                if fpath.is_file():
                    fpath.unlink()
            cf = py_str(fname) if corr else "None"
            lines.append(f'        sub("Sujet {snum}", {py_str(prompt)}, {cf}),')
            total += 1
        lines.append("    ]),")
    lines.append("]")
    out = SCRIPTS / "eo_dec_2025_t3.py"
    out.write_text("\n".join(lines) + "\n", encoding="utf-8")
    print(f"OK eo_dec_2025_t3.py — {len(parts)} parties, {total} sujets")


def main() -> None:
    text = load_text()
    t2_block = split_block(text, r"tache\s*2\s*\[", r"\]\s*tache3\s*\[")
    t3_block = split_block(text, r"\]\s*tache3\s*\[", None)

    t2_chunks = parse_chunks(
        t2_block,
        r"\n3 min 30 s\n4 essais\n\n",
    )
    # skip header before first real prompt
    t2_chunks = [c for c in t2_chunks if "Masquer" in c or "Voir la correction" in c or c.startswith("Je ") or c.startswith("Vous ") or c.startswith("Dans le")]
    t2_parts = group_by_parties(t2_chunks, 5)
    if len(t2_parts) != 10:
        print(f"WARN T2: {len(t2_parts)} parties (attendu 10), {len(t2_chunks)} chunks")
    write_t2(t2_parts)

    t3_chunks = parse_chunks(
        t3_block,
        r"\n4 min 30 s\n4 essais\n\n",
    )
    t3_chunks = [
        c
        for c in t3_chunks
        if "Aucun sujet disponible" not in c and ("Masquer" in c or c.strip().startswith("Aujourd") or c.strip().startswith("De nos") or c.strip().startswith("Il est") or c.strip().startswith("Pensez") or c.strip().startswith("Pour ") or c.strip().startswith("Selon") or c.strip().startswith("Quel") or c.strip().startswith("Le fait") or c.strip().startswith("Les ") or c.strip().startswith("L'") or c.strip().startswith("La ") or c.strip().startswith("Si vous") or c.strip().startswith("Certains") or c.strip().startswith("Faut-il") or c.strip().startswith("Dans le"))
    ]
    t3_parts = group_by_parties(t3_chunks, 5)
    if len(t3_parts) != 7:
        print(f"WARN T3: {len(t3_parts)} parties (attendu 7), {len(t3_chunks)} chunks")
    write_t3(t3_parts)


if __name__ == "__main__":
    main()
