#!/usr/bin/env python3
"""Build mars_2026_part2.php from extracted combo text files."""

from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "database/seeds/exp_ecrite/mars_2026_part2.php"


def php_escape(s: str) -> str:
    return s.replace("\\", "\\\\").replace('"', '\\"').replace("$", "\\$")


def extract_block(text: str, start_label: str, end_labels: list[str]) -> str:
    idx = text.find(start_label)
    if idx < 0:
        raise ValueError(f"Missing {start_label!r}")
    idx += len(start_label)
    end = len(text)
    for label in end_labels:
        pos = text.find(label, idx)
        if pos >= 0:
            end = min(end, pos)
    return text[idx:end].strip()


def parse_combo(path: Path) -> dict:
    raw = path.read_text(encoding="utf-8")

    t1_prompt = extract_block(raw, "Tâche 1\nMessage court •\n60-120 mots •10 min\n\n", ["Masquer la correction"])
    t1_corr = extract_block(raw, "Correction\n", ["2\nTâche 2"])

    t2_prompt = extract_block(raw, "Tâche 2\nNarration •\n120-150 mots •20 min\n\n", ["Masquer la correction"])
    t2_corr = extract_block(raw.split("2\nTâche 2", 1)[1], "Correction\n", ["3\nTâche 3"])

    t3_section = raw.split("3\nTâche 3", 1)[1]
    t3_prompt = extract_block(
        t3_section,
        "Argumentation •\n120-180 mots •30 min\n\n",
        ["Masquer la correction"],
    )
    t3_corr = extract_block(t3_section, "Correction\n", [])

    # Split task 3 prompt into title + two document paragraphs.
    parts = re.split(r"\n\n+", t3_prompt.strip(), maxsplit=1)
    title = parts[0].strip()
    doc_text = parts[1].strip() if len(parts) > 1 else ""
    doc_paras = [p.strip() for p in re.split(r"\n\n+", doc_text) if p.strip()]
    if len(doc_paras) < 2:
        raise ValueError(f"Expected 2 documents in {path.name}, got {len(doc_paras)}")

    return {
        "t1_prompt": t1_prompt,
        "t1_corr": t1_corr,
        "t2_prompt": t2_prompt,
        "t2_corr": t2_corr,
        "t3_prompt": title,
        "doc1": doc_paras[0],
        "doc2": doc_paras[1],
        "t3_corr": t3_corr,
    }


def emit_task(task_num: int, prompt: str, correction: str, documents: list[tuple[str, str]] | None = None) -> str:
    lines = [
        "            [",
        f"                'task' => {task_num},",
        f"                'prompt' => '{php_escape(prompt)}',",
    ]
    if documents:
        lines.append("                'documents' => [")
        for title, content in documents:
            lines.append("                    [")
            lines.append(f"                        'title' => '{php_escape(title)}',")
            lines.append(f"                        'content' => '{php_escape(content)}',")
            lines.append("                    ],")
        lines.append("                ],")
    lines.append(f"                'correction' => \"{php_escape(correction)}\",")
    lines.append("            ],")
    return "\n".join(lines)


def main() -> None:
    chunks: list[str] = []
    for n in range(9, 16):
        data = parse_combo(ROOT / f"scripts/_mars_combo_{n}.txt")
        tasks = [
            emit_task(1, data["t1_prompt"], data["t1_corr"]),
            emit_task(2, data["t2_prompt"], data["t2_corr"]),
            emit_task(
                3,
                data["t3_prompt"],
                data["t3_corr"],
                [("Document 1", data["doc1"]), ("Document 2", data["doc2"])],
            ),
        ]
        chunks.append(
            "    [\n"
            f"        'combo' => {n},\n"
            "        'tasks' => [\n"
            + "\n".join(tasks)
            + "\n        ],\n"
            "    ],"
        )

    body = "\n".join(chunks)
    php = f"""<?php
declare(strict_types=1);

/** @return list<array{{combo:int,tasks:list<array{{task:int,prompt:string,correction:string,documents?:list<array{{title?:string,content:string}}>}}>}}> */
return [
{body}
];
"""
    OUT.write_text(php, encoding="utf-8")
    print(f"Wrote {OUT} ({OUT.stat().st_size} bytes)")


if __name__ == "__main__":
    main()
