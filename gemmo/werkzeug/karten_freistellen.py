# Stellt aus einem Foto einer Karte die Karte selbst frei: findet die helle
# Flaeche auf dem dunklen Granit, dreht sie gerade, schneidet sie aus und
# stellt sie hochkant.
#
# Kein OpenCV, nur Pillow und numpy. Die Karte ist in jedem dieser Fotos die
# einzige grosse helle Flaeche und liegt ueber der Bildmitte — deshalb reicht
# eine Schwelle mit anschliessendem Beschnitt der Ausreisser, es braucht keine
# Zusammenhangsanalyse.

import sys, pathlib
import numpy as np
from PIL import Image

ANALYSE_KANTE = 900          # auf dieser Groesse wird gesucht
WINKEL = np.arange(-46, 46, 0.4)


def kastenweich(a, r):
    """Kastenfilter ueber Summenbilder — ersetzt eine Weichzeichnung."""
    s = np.pad(a.astype(np.float64), r + 1, mode="edge").cumsum(0).cumsum(1)
    h, b = a.shape
    y, x = np.mgrid[0:h, 0:b]
    y0, y1 = y, y + 2 * r + 1
    x0, x1 = x, x + 2 * r + 1
    flaeche = (2 * r + 1) ** 2
    return (s[y1, x1] - s[y0, x1] - s[y1, x0] + s[y0, x0]) / flaeche


def kleinstfilter(m, r):
    """Erosion: raeumt die hellen Sprenkel des Granits weg."""
    aus = m.copy()
    for dy in range(-r, r + 1):
        for dx in range(-r, r + 1):
            aus &= np.roll(np.roll(m, dy, 0), dx, 1)
    return aus


def kartenlage(grau):
    """Winkel und Eckpunkte der Karte im Analysebild."""
    weich = kastenweich(grau, 4)
    # Die Schwelle liegt in der Mitte zwischen dunklem Grund und hellem Karton.
    dunkel, hell = np.percentile(weich, 20), np.percentile(weich, 92)
    maske = kleinstfilter(weich > (dunkel + hell) / 2, 3)

    ys, xs = np.nonzero(maske)
    if len(xs) < 500:
        raise SystemExit("keine Kartenflaeche gefunden")
    # Ausreisser (Reflexe auf dem Stein) unten wie oben abschneiden
    punkte = np.stack([xs, ys]).astype(np.float64)
    mitte = punkte.mean(1, keepdims=True)
    punkte -= mitte

    bester, bestflaeche = 0.0, None
    for w in WINKEL:
        b = np.radians(w)
        d = np.array([[np.cos(b), -np.sin(b)], [np.sin(b), np.cos(b)]]) @ punkte
        lo = np.percentile(d, 0.4, axis=1)
        hi = np.percentile(d, 99.6, axis=1)
        flaeche = (hi[0] - lo[0]) * (hi[1] - lo[1])
        if bestflaeche is None or flaeche < bestflaeche:
            bestflaeche, bester = flaeche, w

    b = np.radians(bester)
    d = np.array([[np.cos(b), -np.sin(b)], [np.sin(b), np.cos(b)]]) @ punkte
    lo = np.percentile(d, 0.4, axis=1)
    hi = np.percentile(d, 99.6, axis=1)
    return bester, mitte[:, 0], lo, hi


def freistellen(pfad, ziel):
    bild = Image.open(pfad).convert("RGB")
    faktor = ANALYSE_KANTE / max(bild.size)
    klein = bild.resize((round(bild.width * faktor), round(bild.height * faktor)))
    grau = np.asarray(klein.convert("L"), dtype=np.float64)

    winkel, mitte, lo, hi = kartenlage(grau)

    # Dieselbe Drehung auf dem grossen Bild, um dessen eigene Mitte.
    gross = bild.rotate(-winkel, resample=Image.BICUBIC, expand=True,
                        center=(bild.width / 2, bild.height / 2))
    # Der Ausschnitt wird im gedrehten Bezugssystem beschrieben, relativ zum
    # Schwerpunkt der Maske — der bleibt bei der Drehung um die Bildmitte nicht
    # stehen, also wird er mitgedreht.
    b = np.radians(-winkel)
    dreh = np.array([[np.cos(b), -np.sin(b)], [np.sin(b), np.cos(b)]])
    bildmitte = np.array([bild.width / 2, bild.height / 2])
    versatz = (np.array([gross.width, gross.height]) - np.array([bild.width, bild.height])) / 2
    schwer = dreh @ (mitte / faktor - bildmitte) + bildmitte + versatz

    links, oben = schwer + lo / faktor
    rechts, unten = schwer + hi / faktor
    karte = gross.crop((round(links), round(oben), round(rechts), round(unten)))

    # Hochkant stellen. Welche der beiden Drehungen richtig ist, entscheidet
    # der Aufruf — hier wird nur ins Hochformat gebracht.
    if karte.width > karte.height:
        karte = karte.transpose(Image.ROTATE_90)
    karte.save(ziel, quality=93)
    return karte.size, winkel


if __name__ == "__main__":
    quelle = pathlib.Path(sys.argv[1])
    ausgabe = pathlib.Path(sys.argv[2])
    ausgabe.mkdir(parents=True, exist_ok=True)
    for pfad in sorted(quelle.glob("*.JPG")):
        nummer = pfad.stem.split("-")[-1]
        groesse, winkel = freistellen(pfad, ausgabe / (nummer + ".jpg"))
        print(f"{nummer}: {groesse[0]}x{groesse[1]}  gedreht um {winkel:+.1f}°")
