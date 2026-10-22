"""Wortmarke und Signet als Pfade, ohne dass eine Schrift installiert sein muss.

Gesetzt wird wie im Browser: HarfBuzz formt den Text mit Unterschneidung, die
Laufweite aus dem CSS (letter-spacing) kommt nach jedem Zeichen dazu, beide
Teile stehen auf derselben Grundlinie. Ueberlappende Konturen der variablen
Schriften werden zusammengefuehrt, sonst zeigen manche Programme Loecher.
Einheit: 1 em = 1000.
"""
import io
from pathlib import Path

import pathops
import uharfbuzz as hb
from fontTools.pens.svgPathPen import SVGPathPen
from fontTools.pens.transformPen import TransformPen
from fontTools.pens.boundsPen import BoundsPen
from fontTools.ttLib import TTFont

SCHRIFTEN = Path(__file__).resolve().parents[2] / "schriften"
EM = 1000


def _hb_font(datei, gewicht):
    ttf = TTFont(SCHRIFTEN / datei)
    ttf.flavor = None
    puffer = io.BytesIO()
    ttf.save(puffer)
    face = hb.Face(hb.Blob(puffer.getvalue()))
    font = hb.Font(face)
    font.set_variations({"wght": gewicht})
    return font, face.upem


GROTESK = ("schibsted-grotesk.woff2", 700)
SERIF = ("playfair-display.woff2", 900)


def setzen(teile):
    """teile: [(text, (datei, gewicht), laufweite_em, name)] -> {name: pathops.Path}, Breite."""
    x = 0.0
    pfade = {}
    for text, (datei, gewicht), laufweite, name in teile:
        font, upem = _hb_font(datei, gewicht)
        s = EM / upem
        buf = hb.Buffer()
        buf.add_str(text)
        buf.guess_segment_properties()
        hb.shape(font, buf, {"kern": True, "liga": False, "clig": False})
        pfad = pfade.setdefault(name, pathops.Path())
        for info, pos in zip(buf.glyph_infos, buf.glyph_positions):
            stift = TransformPen(pfad.getPen(), (s, 0, 0, s, x + pos.x_offset * s, pos.y_offset * s))
            font.draw_glyph_with_pen(info.codepoint, stift)
            x += pos.x_advance * s + laufweite * EM
    for p in pfade.values():
        p.simplify(fix_winding=True)
    return pfade


def grenzen(pfade):
    b = BoundsPen(None)
    for p in pfade.values():
        p.draw(b)
    return b.bounds  # xmin, ymin, xmax, ymax in Schriftkoordinaten (y nach oben)


def svg_d(pfad, dx, dy, faktor=1.0):
    """Pfad in SVG-Koordinaten (y nach unten), verschoben und skaliert."""
    pen = SVGPathPen(None, ntos=lambda v: f"{round(v, 1):g}")
    pfad.draw(TransformPen(pen, (faktor, 0, 0, -faktor, dx, dy)))
    return pen.getCommands()


def wortmarke():
    # Wie .logo .s / .logo .p in index.html
    return setzen([
        ("format", GROTESK, -0.035, "format"),
        ("eins", SERIF, -0.01, "eins"),
    ])


def signet():
    # Wie quellen/favicon.html
    return setzen([
        ("f", GROTESK, -0.05, "f"),
        ("e", SERIF, 0, "e"),
    ])
