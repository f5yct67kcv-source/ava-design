# Gegenprobe zur Falle aus dem README: in index.html und in der gebauten
# Vorschau darf kein Zeichen aus U+0300..U+036F, U+0001 oder U+0002
# buchstaeblich vorkommen. Als Codepunkt-Vergleich geschrieben, damit in dieser
# Datei selbst keines davon steht.
import io, sys, pathlib

verboten = set(range(0x300, 0x370)) | {0x01, 0x02}

fehler = 0
for pfad in sys.argv[1:]:
    text = io.open(pfad, encoding='utf-8').read()
    treffer = {}
    for zeichen in text:
        n = ord(zeichen)
        if n in verboten:
            treffer[n] = treffer.get(n, 0) + 1
    if treffer:
        fehler += 1
        print("FEHLER", pfad, "->",
              ", ".join("U+%04X x%d" % (n, c) for n, c in sorted(treffer.items())))
    else:
        print("sauber:", pathlib.Path(pfad).name)

sys.exit(1 if fehler else 0)
