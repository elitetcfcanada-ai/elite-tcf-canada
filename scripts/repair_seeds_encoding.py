#!/usr/bin/env python3
"""Repair CP850 mojibake in database/seeds_ee_eo_data.sql (UTF-8 dump)."""
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
TARGET = ROOT / "database" / "seeds_ee_eo_data.sql"


def main() -> None:
    text = TARGET.read_text(encoding="utf-8")
    before = text.count("\u251c")
    if before == 0 and "Expression Écrite" in text:
        print("Already clean:", TARGET)
        return
    fixed = text.encode("cp850").decode("utf-8")
    if "\u251c" in fixed:
        raise SystemExit("Repair incomplete: box-drawing chars remain")
    if "Expression Écrite" not in fixed:
        raise SystemExit("Repair failed: expected titles not found")
    TARGET.write_text(fixed, encoding="utf-8", newline="\n")
    print(f"OK wrote {TARGET}")
    print(f"mojibake_before={before} after={fixed.count(chr(0x251C))}")
    print(f"Expression Écrite count={fixed.count('Expression Écrite')}")
    for needle in ("différentes", "Août", "Février", "Décembre", "accès"):
        print(f"  has {needle!r}: {needle in fixed}")


if __name__ == "__main__":
    main()
