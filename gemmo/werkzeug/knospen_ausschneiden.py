# Schneidet aus der freigestellten Kartenvorderseite die gemalte Knospe aus.
#
# Die Knospe ist auf der Karte das einzige grosse warmfarbige Gebilde: rot,
# braun, rostig, bei der Stechpalme auch gelb. Der Text ist fast schwarz, die
# Untertitelzeile und die Randlinie sind gruen. Also: warme, gesaettigte Pixel
# suchen, deren Umriss nehmen, ringsum etwas Papier stehen lassen.
#
# Das Papier bleibt weiss und wird nicht freigestellt — auf der Karte steht die
# Knospe ja auch auf Papier, und in der App wieder.

import sys, pathlib
import numpy as np
from PIL import Image

# karten_freistellen.py hat die Karte bereits ins Hochformat gedreht, dabei
# aber in die falsche Richtung — sie steht kopf. Hier wird nur noch dieser
# Fehler ausgeglichen, nicht erneut ins Hochformat gedreht.
AUFSTELLEN = Image.ROTATE_180

RAND = 0.055        # innerer Beschnitt, haelt die Randlinie draussen
LUFT = 0.07         # Papier ringsum die Knospe
ABSTAND = 0.022     # Luft zwischen Ausschnitt und Namenszeile
KANTE = 900         # Ausgabegroesse der langen Seite


def warmmaske(rgb):
    """Die gemalte Knospe: warm und gesaettigt. Die Namen sind fast schwarz,
    Untertitel und Randlinie gruen — beides faellt hier heraus."""
    r, g, b = rgb[..., 0], rgb[..., 1], rgb[..., 2]
    hoch = rgb.max(2)
    saettigung = hoch - rgb.min(2)
    return (saettigung > 26) & (r >= g) & (r > b) & (hoch > 55)


def tintenmaske(rgb):
    """Gesetzter Text: dunkel und farblos. Die Knospe hat auch dunkle Stellen,
    aber keine farblosen."""
    hoch = rgb.max(2)
    saettigung = hoch - rgb.min(2)
    return (hoch < 105) & (saettigung < 34)


def namenskante(feld):
    """Zeile, in der der Namenssatz beginnt. Eine feste Grenze reicht nicht:
    bei der Grauerle laeuft die Knospe tiefer als bei der Birke, und ein Wert,
    der beiden passt, schneidet der einen die Spitze ab oder holt der anderen
    die Kursivzeile ins Bild."""
    ho, br = feld.shape[:2]
    zeilen = tintenmaske(feld).sum(1)
    schwelle = max(6, br * 0.012)
    for y in range(int(ho * 0.5), ho):
        # Drei Zeilen am Stueck, damit ein Staubkorn den Schnitt nicht setzt.
        if (zeilen[y:y + 3] > schwelle).all():
            return y
    return ho


def papierton(bild):
    """Gleicht das Kuechenlicht aus. Das Papier der Karte kommt im Foto grau
    und leicht blaustichig an (rund 211,211,213); in der App liegt der
    Ausschnitt aber auf dem Papierton der nachgebauten Karte. Ohne Abgleich
    sitzt ein grauer Kasten auf hellem Grund.

    Gerechnet wird je Kanal ein Faktor, der die hellste Flaeche — also das
    Papier — auf den Papierton der App hebt. Das nimmt dem Bild zugleich den
    Farbstich, weil jeder Kanal seinen eigenen Faktor bekommt. Die Knospe
    faerbt das nicht um: sie wird um denselben Betrag mitgehoben, und der
    liegt bei rund einem Sechstel."""
    ZIEL = np.array([251.0, 250.0, 246.0])   # --k-papier der App
    a = np.asarray(bild, dtype=np.float64)
    papier = np.percentile(a.reshape(-1, 3), 95, axis=0)
    # Deckel bei 1.3: waere das Foto sehr dunkel, wuerde die Knospe ausbleichen.
    faktor = np.minimum(ZIEL / np.maximum(papier, 1), 1.3)
    return Image.fromarray(np.clip(a * faktor, 0, 255).astype(np.uint8))


def knospe(pfad, ziel):
    karte = Image.open(pfad).transpose(AUFSTELLEN)
    br, ho = karte.size

    x0, x1 = int(br * RAND), int(br * (1 - RAND))
    y0 = int(ho * RAND)
    innen = np.asarray(karte.crop((x0, y0, x1, int(ho * (1 - RAND)))), dtype=np.int16)

    grenze = namenskante(innen) - int(ho * ABSTAND)
    feld = innen[:grenze]

    maske = warmmaske(feld)
    ys, xs = np.nonzero(maske)
    if len(xs) < 200:
        raise SystemExit(f"{pfad.name}: keine Knospe gefunden ({len(xs)} Punkte)")

    # Randstreuung wegschneiden: einzelne warme Punkte im Papier soll der
    # Ausschnitt nicht mitziehen.
    lx, rx = np.percentile(xs, [0.6, 99.4])
    ly, ry = np.percentile(ys, [0.6, 99.4])

    luft = LUFT * max(rx - lx, ry - ly)
    kasten = (
        max(0, int(x0 + lx - luft)), max(0, int(y0 + ly - luft)),
        min(br, int(x0 + rx + luft)), min(y0 + grenze, int(y0 + ry + luft)),
    )
    aus = papierton(karte.crop(kasten))
    aus.thumbnail((KANTE, KANTE), Image.LANCZOS)
    aus.save(ziel, quality=88, optimize=True, progressive=True)
    return aus.size, ziel.stat().st_size


if __name__ == "__main__":
    quelle = pathlib.Path(sys.argv[1])
    ausgabe = pathlib.Path(sys.argv[2])
    ausgabe.mkdir(parents=True, exist_ok=True)
    # Vorderseite -> Dateiname der Karte
    ZUORDNUNG = {
        "IMG_2045": "sambucus-nigra",
        "IMG_2047": "viburnum-lantana",
        "IMG_2049": "ilex-aquifolium",
        "IMG_2051": "alnus-glutinosa",
        "IMG_2053": "alnus-incana",
        "IMG_2055": "betula-pendula",
    }
    for nummer, name in sorted(ZUORDNUNG.items()):
        ziel = ausgabe / (name + ".jpg")
        groesse, bytes_ = knospe(quelle / (nummer + ".jpg"), ziel)
        print(f"{nummer} -> {name}.jpg  {groesse[0]}x{groesse[1]}  {bytes_//1024} KB")
