# -*- coding: utf-8 -*-
"""Extrait les corrections T3 novembre 2025 depuis le transcript utilisateur."""
import json
import re
from pathlib import Path

TRANSCRIPT = Path(
    r"C:\Users\MAKUI DJIBRILE\.cursor\projects\c-xampp-htdocs-tcf4\agent-transcripts"
    r"\04082374-1753-4550-91d3-829a98c810cd\04082374-1753-4550-91d3-829a98c810cd.jsonl"
)
OUT_DIR = Path(__file__).resolve().parent.parent / "database" / "seeds" / "exp_orale" / "novembre_t3"

EMOJI_RE = re.compile(
    r"[\U0001F300-\U0001FAFF\U00002700-\U000027BF\U00002600-\U000026FF"
    r"\U0001F1E0-\U0001F1FF\u2ff2\u2ff3]+"
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


def load_user_text() -> str:
    with TRANSCRIPT.open(encoding="utf-8") as f:
        for line in f:
            o = json.loads(line)
            if o.get("role") != "user":
                continue
            t = o.get("message", {}).get("content", [{}])[0].get("text", "")
            if "insert epression orale" in t and "novembre 2025" in t and "tache3[" in t:
                return t
    raise SystemExit("Message utilisateur introuvable")


def split_t3_block(text: str) -> list[str]:
    m = re.search(r"\] tache3\[(.*)$", text, re.S)
    if not m:
        raise SystemExit("Bloc tache3 introuvable")
    block = m.group(1)
    chunks = re.split(r"\n4 min 30 s\n4 essais\n\n", block)
    # skip header (Méthodologie…)
    return [c for c in chunks[1:] if c.strip()]


def parse_chunk(chunk: str) -> tuple[str, str]:
    chunk = chunk.strip()
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
        prompt = lines[0].strip()
        corr = "\n".join(lines[1:]).strip()
    return prompt, clean(corr)


def main() -> None:
    text = load_user_text()
    chunks = split_t3_block(text)
    if len(chunks) != 15:
        raise SystemExit(f"Attendu 15 sujets T3, trouvé {len(chunks)}")

    mapping = []
    for part in (1, 2, 3):
        for subj in range(1, 6):
            mapping.append((part, subj))

    OUT_DIR.mkdir(parents=True, exist_ok=True)
    for (part, subj), chunk in zip(mapping, chunks):
        prompt, corr = parse_chunk(chunk)
        fname = f"p{part}_s{subj}.txt"
        out = OUT_DIR / fname
        if corr:
            out.write_text(corr, encoding="utf-8")
            print(f"OK {fname} ({len(corr)} chars)")
        else:
            if out.is_file():
                out.unlink()
            print(f"SKIP {fname} (pas de correction)")


if __name__ == "__main__":
    main()
