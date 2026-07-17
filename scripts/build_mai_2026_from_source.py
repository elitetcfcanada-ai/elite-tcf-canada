#!/usr/bin/env python3
"""Parse mai_2026_source.txt and generate mai_2026_part*.php + mai_2026_data.php"""

from __future__ import annotations

import os
import re
import sys

ROOT = os.path.join(os.path.dirname(__file__), "..", "database", "seeds", "exp_ecrite")
SOURCE = os.path.join(ROOT, "mai_2026_source.txt")
COMBOS_PER_PART = 6


def php_str(s: str) -> str:
    return "'" + s.replace("\\", "\\\\").replace("'", "\\'") + "'"


def extract_prompt(block: str, task_num: int) -> str:
    # After "Tâche N" find prompt until "Masquer la correction" or next section
    m = re.search(
        rf"Tâche {task_num}\s*\n(?:Message court[^\n]*\n|Narration[^\n]*\n|Argumentation[^\n]*\n)?(.*?)(?:\nMasquer la correction|\n\d+\s*\nTâche |\Z)",
        block,
        re.DOTALL,
    )
    if not m:
        return ""
    text = m.group(1).strip()
    # Remove bullet lines like "60-120 mots •10 min"
    lines = []
    for line in text.split("\n"):
        line = line.strip()
        if re.match(r"^\d+-\d+ mots", line) or re.match(r"^\d+ min$", line) or "•" in line and "mots" in line:
            continue
        if line == "Simulateur":
            continue
        lines.append(line)
    return "\n".join(lines).strip()


def extract_correction(block: str, task_num: int) -> str:
    # Find all "Correction" sections in task block
    task_m = re.search(rf"Tâche {task_num}\s*\n(.*?)(?=\n\d+\s*\nTâche |\Z)", block, re.DOTALL)
    if not task_m:
        return ""
    task_block = task_m.group(1)
    m = re.search(
        r"Masquer la correction\s*\nCorrection\s*\n(.*?)(?=\n\d+\s*\nTâche |\nCombinaison |\Z)",
        task_block,
        re.DOTALL,
    )
    if not m:
        # combo 75 may lack correction for tasks 2-3
        return ""
    return m.group(1).strip()


def task3_raw_text(block: str) -> str:
    task_m = re.search(r"Tâche 3\s*\n(.*?)(?=\nMasquer la correction|\Z)", block, re.DOTALL)
    if not task_m:
        return ""
    out: list[str] = []
    for line in task_m.group(1).split("\n"):
        s = line.strip()
        if re.match(r"^Argumentation", s) or re.match(r"^\d+-\d+ mots", s) or ("•" in s and "mots" in s):
            continue
        if s == "":
            if out and out[-1] != "":
                out.append("")
        else:
            out.append(s)
    return "\n".join(out).strip()


def split_task3(full: str) -> tuple[str, list[dict]]:
    """Return (prompt, documents) for task 3."""
    if not full:
        return "", []

    if re.search(r"Document\s*1", full, re.I):
        prompt_m = re.match(r"^(.*?)(?=Document\s*1)", full, re.DOTALL | re.I)
        prompt = prompt_m.group(1).strip() if prompt_m else full.split("\n")[0].strip()
        parts = re.split(r"Document\s*2\s*", full, flags=re.I)
        p1 = re.split(r"Document\s*1\s*", parts[0], flags=re.I)[-1].strip()
        docs = [{"title": "Document 1", "content": p1}]
        if len(parts) > 1:
            docs.append({"title": "Document 2", "content": parts[1].strip()})
        return prompt, docs

    # Opinion 1 / Opinion 2 style
    if re.search(r"Opinion\s*1\s*:", full, re.I):
        prompt_m = re.match(r"^(.*?)(?=Opinion\s*1\s*:)", full, re.DOTALL | re.I)
        prompt = prompt_m.group(1).strip() if prompt_m else full.split("\n")[0].strip()
        parts = re.split(r"Opinion\s*2\s*:", full, flags=re.I)
        p1 = re.split(r"Opinion\s*1\s*:", parts[0], flags=re.I)[-1].strip()
        docs = [{"title": "Document 1", "content": ("Opinion 1 : " + p1).strip()}]
        if len(parts) > 1:
            docs.append({"title": "Document 2", "content": ("Opinion 2 : " + parts[1].strip()).strip()})
        return prompt, docs

    # Named speakers (Jean, Sara, Quentin, etc.)
    speaker_split = re.split(
        r"(?=\n(?:Jean|Sara|Quentin|Estelle|Marie|Paul \d|ASSOCIATION)[^\n]*\n)",
        "\n" + full,
    )
    speaker_split = [p.strip() for p in speaker_split if p.strip()]
    if len(speaker_split) >= 3:
        return speaker_split[0].strip(), [
            {"title": "Document 1", "content": speaker_split[1]},
            {"title": "Document 2", "content": speaker_split[2]},
        ]

    paras = [p.strip() for p in re.split(r"\n\s*\n", full) if p.strip()]
    if len(paras) >= 3:
        return paras[0], [
            {"title": "Document 1", "content": paras[1]},
            {"title": "Document 2", "content": paras[2]},
        ]
    if len(paras) == 2:
        first = paras[0]
        if "\n" in first:
            title, doc1 = first.split("\n", 1)
            return title.strip(), [
                {"title": "Document 1", "content": doc1.strip()},
                {"title": "Document 2", "content": paras[1]},
            ]
        return first, [{"title": "Document 1", "content": paras[1]}]

    if "\n" in full:
        lines = full.split("\n")
        return lines[0].strip(), [
            {"title": "Document 1", "content": "\n".join(lines[1:-1]).strip()},
            {"title": "Document 2", "content": lines[-1].strip()},
        ] if len(lines) >= 3 else []

    return full.split("\n")[0].strip(), []


def parse_combo(body: str, num: int) -> dict:
    combo = {"combo": num, "tasks": []}
    for t in (1, 2, 3):
        prompt = extract_prompt(body, t)
        correction = extract_correction(body, t)
        task: dict = {"task": t, "prompt": prompt, "correction": correction}
        if t == 3:
            raw3 = task3_raw_text(body)
            p3, docs = split_task3(raw3)
            if p3:
                task["prompt"] = p3
            if docs:
                task["documents"] = docs
        combo["tasks"].append(task)
    return combo


def parse_source(text: str) -> list[dict]:
    parts = re.split(r"Combinaison (\d+)\s*\n", text)
    combos = []
    for i in range(1, len(parts), 2):
        num = int(parts[i])
        if num == 66:
            continue  # skip missing combo
        body = parts[i + 1]
        combos.append(parse_combo(body, num))
    combos.sort(key=lambda c: c["combo"])
    return combos


def emit_combo(combo: dict, indent: int = 4) -> str:
    sp = " " * indent
    lines = [
        f"{sp}[",
        f"{sp}    'combo' => {combo['combo']},",
        f"{sp}    'tasks' => [",
    ]
    for task in combo["tasks"]:
        lines.append(f"{sp}        [")
        lines.append(f"{sp}            'task' => {task['task']},")
        lines.append(f"{sp}            'prompt' => {php_str(task['prompt'])},")
        lines.append(f"{sp}            'correction' => {php_str(task['correction'])},")
        if "documents" in task:
            lines.append(f"{sp}            'documents' => [")
            for doc in task["documents"]:
                lines.append(f"{sp}                [")
                lines.append(f"{sp}                    'title' => {php_str(doc['title'])},")
                lines.append(f"{sp}                    'content' => {php_str(doc['content'])},")
                lines.append(f"{sp}                ],")
            lines.append(f"{sp}            ],")
        lines.append(f"{sp}        ],")
    lines.append(f"{sp}    ],")
    lines.append(f"{sp}],")
    return "\n".join(lines)


def write_parts(combos: list[dict]) -> list[str]:
    files = []
    for i in range(0, len(combos), COMBOS_PER_PART):
        chunk = combos[i : i + COMBOS_PER_PART]
        part_num = i // COMBOS_PER_PART + 1
        start = chunk[0]["combo"]
        end = chunk[-1]["combo"]
        filename = f"mai_2026_part{part_num}.php"
        header = f"""<?php
declare(strict_types=1);

/** Combinaisons {start} à {end} — Mai 2026 */
return [
"""
        body = "\n".join(emit_combo(c) for c in chunk)
        path = os.path.join(ROOT, filename)
        with open(path, "w", encoding="utf-8", newline="\n") as f:
            f.write(header + body + "\n];\n")
        files.append(filename)
        print(f"Wrote {filename}: combos {start}-{end} ({len(chunk)} combos)")
    return files


def write_data(files: list[str]) -> None:
    paths = [f"__DIR__ . '/{f}'" for f in files]
    paths_joined = ",\n    ".join(paths)
    content = f"""<?php
declare(strict_types=1);

/** @return list<array{{combo:int,tasks:list<array{{task:int,prompt:string,correction:string,documents?:list<array{{title?:string,content:string}}>}}>}}> */
$parts = [
    {paths_joined},
];
$merged = [];
foreach ($parts as $file) {{
    if (!is_file($file)) {{
        throw new RuntimeException('Fichier données manquant: ' . $file);
    }}
    $merged = array_merge($merged, require $file);
}}
return $merged;
"""
    path = os.path.join(ROOT, "mai_2026_data.php")
    with open(path, "w", encoding="utf-8", newline="\n") as f:
        f.write(content)
    print(f"Wrote mai_2026_data.php ({len(files)} parts)")


def main() -> int:
    if not os.path.isfile(SOURCE):
        print(f"Missing {SOURCE}", file=sys.stderr)
        return 1
    text = open(SOURCE, encoding="utf-8").read()
    combos = parse_source(text)
    print(f"Parsed {len(combos)} combinations")
    missing_prompt = []
    for c in combos:
        for t in c["tasks"]:
            if not t["prompt"].strip():
                missing_prompt.append((c["combo"], t["task"]))
    if missing_prompt:
        print("WARN empty prompts:", missing_prompt[:20], "...", len(missing_prompt))
    files = write_parts(combos)
    write_data(files)
    return 0


if __name__ == "__main__":
    sys.exit(main())
