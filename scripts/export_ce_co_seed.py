#!/usr/bin/env python3
"""Export local CE/CO tables to database/seeds_ce_co_data.sql (UTF-8)."""
from __future__ import annotations

import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "database" / "seeds_ce_co_data.sql"
MYSQLDUMP = Path(r"C:\xampp\mysql\bin\mysqldump.exe")

HEADER = """-- Sujets Comprehension Ecrite + Comprehension Orale
-- Importer APRES database/tcf.sql
SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8mb4;
DELETE FROM tcf_ce_answers;
DELETE FROM tcf_ce_questions;
DELETE FROM tcf_ce_consignes;
DELETE FROM tcf_ce_exams;
DELETE FROM tcf_co_answers;
DELETE FROM tcf_co_questions;
DELETE FROM tcf_co_consignes;
DELETE FROM tcf_co_exams;

"""

TABLES = [
    "tcf_ce_exams",
    "tcf_ce_questions",
    "tcf_ce_answers",
    "tcf_ce_consignes",
    "tcf_co_exams",
    "tcf_co_questions",
    "tcf_co_answers",
    "tcf_co_consignes",
]


def main() -> None:
    args = [
        str(MYSQLDUMP),
        "-uroot",
        "--default-character-set=utf8mb4",
        "--skip-comments",
        "--no-create-info",
        "--complete-insert",
        "--skip-extended-insert",
        "--hex-blob",
        "tcf",
        *TABLES,
    ]
    raw = subprocess.check_output(args)
    body = raw.decode("utf-8")
    if "\u251c" in body:
        body = body.encode("cp850").decode("utf-8")
    OUT.write_text(HEADER + body + "\nSET FOREIGN_KEY_CHECKS=1;\n", encoding="utf-8", newline="\n")
    text = OUT.read_text(encoding="utf-8")
    print("wrote", OUT, "bytes", OUT.stat().st_size)
    print("mojibake_box", text.count("\u251c"))
    print("has_Comprehension_Ecrite", "Compréhension Écrite" in text)
    print("insert_count", text.count("INSERT INTO"))


if __name__ == "__main__":
    main()
