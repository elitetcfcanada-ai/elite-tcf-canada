import json
import sys

path = r"C:\Users\MAKUI DJIBRILE\.cursor\projects\c-xampp-htdocs-tcf4\agent-transcripts\04082374-1753-4550-91d3-829a98c810cd\04082374-1753-4550-91d3-829a98c810cd.jsonl"
start = int(sys.argv[1]) if len(sys.argv) > 1 else 9
end = int(sys.argv[2]) if len(sys.argv) > 2 else 15

with open(path, encoding="utf-8") as f:
    for i, line in enumerate(f, 1):
        if i not in (82, 85):
            continue
        obj = json.loads(line)
        text = obj["message"]["content"][0]["text"]
        for n in range(start, end + 1):
            marker = f"Combinaison {n}"
            idx = text.find(marker)
            if idx < 0:
                print(f"=== COMBO {n} NOT FOUND ===")
                continue
            nxt = text.find("Combinaison ", idx + len(marker))
            chunk = text[idx:nxt] if nxt >= 0 else text[idx:]
            out = f"scripts/_mars_combo_{n}.txt"
            with open(out, "w", encoding="utf-8") as outf:
                outf.write(chunk)
            print(f"Wrote {out} ({len(chunk)} chars)")
