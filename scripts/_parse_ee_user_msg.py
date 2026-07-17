#!/usr/bin/env python3
"""Parse TCF EE user message into structured combos."""
from __future__ import annotations

import json
import re
import sys
from pathlib import Path


def parse_message(text: str) -> dict[int, dict]:
    if "<user_query>" in text:
        text = text.split("<user_query>", 1)[1].split("</user_query>", 1)[0]

    parts = re.split(r"\nCombinaison (\d+)\n", text)
    out: dict[int, dict] = {}

    for i in range(1, len(parts), 2):
        n = int(parts[i])
        body = parts[i + 1]
        combo: dict = {"combo": n, "tasks": []}

        task_blocks = re.split(r"\n(\d)\nT[^\n]*\n", body)
        # first chunk before task 1 may be empty
        idx = 1
        while idx < len(task_blocks):
            task_num = int(task_blocks[idx])
            chunk = task_blocks[idx + 1]
            idx += 2

            prompt = ""
            correction = ""
            documents: list[dict] = []

            pm = re.search(
                r"(?:Message court|Narration|Argumentation)[^\n]*\n\n(.*?)\n\nMasquer la correction",
                chunk,
                re.S,
            )
            if not pm:
                pm = re.search(r"\n\n(.*?)\n\nMasquer la correction", chunk, re.S)
            if pm:
                prompt = pm.group(1).strip()

            cm = re.search(r"Correction\n(.*?)(?=\n\n\d\nT|\n\nCombinaison |\Z)", chunk, re.S)
            if cm:
                correction = cm.group(1).strip()

            if task_num == 3:
                doc_parts = re.split(r"\n\n(?=Les |Le |L'|La |De |Plus |Avec |Récemment|ASSOCIATION|Opinion|«|\"|Je |On |Des |Certaines|Malgré|Fabriquer|Grâce|Cette|D'après|Selon|En |Il |Dans |Beaucoup|Offrir|Bien que|Les |Un |Un(e)|Vous |Salut|Bonjour|Objet|COURRIER|Participez|Écrivez|Rédigez|Votre |Si l|Si l')", prompt, maxsplit=1)
                if len(doc_parts) == 2:
                    prompt = doc_parts[0].strip()
                    rest = doc_parts[1]
                    # split doc1/doc2 heuristically at blank line before second doc lead-in
                    split_m = re.search(
                        r"\n\n(Les |Le |L'|La |De |Plus |Avec |Récemment|ASSOCIATION|Opinion|«|\"|Je |On |Des |Certaines|Malgré|Fabriquer|Grâce|Cette|D'après|Selon|En |Il |Dans |Beaucoup|Offrir|Bien que|Un |Vous |Si l|Si l')",
                        rest,
                    )
                    if split_m:
                        d1 = rest[: split_m.start()].strip()
                        d2 = rest[split_m.start() :].strip()
                        if d1:
                            documents.append({"title": "Document 1", "content": d1})
                        if d2:
                            documents.append({"title": "Document 2", "content": d2})

            entry: dict = {"task": task_num, "prompt": prompt, "correction": correction}
            if documents:
                entry["documents"] = documents
            combo["tasks"].append(entry)

        out[n] = combo
    return out


def main() -> None:
    path = Path(sys.argv[1])
    text = path.read_text(encoding="utf-8")
    if path.suffix == ".jsonl":
        line_no = int(sys.argv[2]) if len(sys.argv) > 2 else 1
        lines = text.splitlines()
        text = json.loads(lines[line_no - 1])["message"]["content"][0]["text"]

    combos = parse_message(text)
    for n in sorted(combos):
        t1 = combos[n]["tasks"][0]["prompt"][:70] if combos[n]["tasks"] else ""
        print(f"{n}: {t1}...")


if __name__ == "__main__":
    main()
