#!/usr/bin/env python3
"""Baut das Markenpaket: Logo als SVG und PNG, Signet, Favicons, Bilder fuer
soziale Netzwerke und das Stylesheet als PDF. Aufruf und Abhaengigkeiten im
README unter "Markenpaket".

    python bauen.py ~/Downloads/formateins-marke.zip
"""
import datetime
import json
import re
import shutil
import subprocess
import sys
import tempfile
import zipfile
from pathlib import Path

from PIL import Image, ImageCms

import umriss

HIER = Path(__file__).resolve().parent
REPO = HIER.parents[1]
SCHRIFTEN_CSS = (REPO / "schriften.css").as_uri()

# ---------------------------------------------------------------- Farben
# Werte aus :root in index.html. Wer dort eine Farbe aendert, aendert sie hier.
FARBEN = {
    "grund": "#1b2a33", "grund-tief": "#0e1820", "grund-hoch": "#263a47", "grund-licht": "#2e4557",
    "signal": "#ff8a4c", "signal-hell": "#ff9f6d",
    "auf-grund": "#e4eaee", "auf-grund-still": "#a8b8c4",
    "papier": "#f2ede2", "papier-still": "#ddd4c3",
    "tinte": "#15202a", "tinte-still": "#565f6a",
    "linie": "#2f4553",
}
F = FARBEN

# Name, CSS-Variable, Rolle. Die ersten fuenf sind die Hauptfarben.
PALETTE = [
    ("Schiefer", "grund", "Grundfläche. Die Marke steht auf Schiefer."),
    ("Kreide", "auf-grund", "Text auf Schiefer, „format“ im Logo."),
    ("Signal", "signal", "„eins“ im Logo und die eine Handlung pro Ansicht."),
    ("Papier", "papier", "Helle Flächen, Karten, Dokumente."),
    ("Tinte", "tinte", "Text auf Papier und auf Signal."),
    ("Schiefer tief", "grund-tief", "Verlauf, Fuss."),
    ("Schiefer hoch", "grund-hoch", "Lichtquelle im Verlauf."),
    ("Linie", "linie", "Linien und Rahmen auf Schiefer."),
    ("Kreide still", "auf-grund-still", "Leiser Text auf Schiefer."),
    ("Signal hell", "signal-hell", "Nur für Hover."),
    ("Papier still", "papier-still", "Bildgrund, Platzhalter."),
    ("Tinte still", "tinte-still", "Leiser Text auf Papier."),
]


def rgb(hexwert):
    h = hexwert.lstrip("#")
    return tuple(int(h[i:i + 2], 16) for i in (0, 2, 4))


_CMYK = ImageCms.buildTransform(
    ImageCms.createProfile("sRGB"),
    ImageCms.getOpenProfile("/System/Library/ColorSync/Profiles/Generic CMYK Profile.icc"),
    "RGB", "CMYK", renderingIntent=ImageCms.Intent.RELATIVE_COLORIMETRIC)


def cmyk(hexwert):
    px = ImageCms.applyTransform(Image.new("RGB", (1, 1), rgb(hexwert)), _CMYK).getpixel((0, 0))
    return tuple(round(v / 255 * 100) for v in px)


def leuchtdichte(hexwert):
    def kanal(c):
        c /= 255
        return c / 12.92 if c <= 0.03928 else ((c + 0.055) / 1.055) ** 2.4
    r, g, b = (kanal(c) for c in rgb(hexwert))
    return 0.2126 * r + 0.7152 * g + 0.0722 * b


def kontrast(a, b):
    la, lb = sorted((leuchtdichte(a), leuchtdichte(b)), reverse=True)
    return (la + 0.05) / (lb + 0.05)


# ---------------------------------------------------------------- Formen
WORT = umriss.wortmarke()
SIGNET = umriss.signet()
# Schutzraum: die Hoehe des o in "format"
_o = umriss.grenzen(umriss.setzen([("o", umriss.GROTESK, 0, "o")]))
SCHUTZ = _o[3] - _o[1]

# Kachel fuer Favicon und Profilbild: Verhaeltnis wie quellen/favicon.html
# (119 px Schrift auf 180 px Kachel)
KACHEL = 1000 * 180 / 119
# Im Browser-Tab ist die Kachel 16 bis 32 px gross. Dort fuellt das Zeichen sie
# staerker aus, sonst bleibt nur ein Fleck.
_s = umriss.grenzen(SIGNET)
KACHEL_KLEIN = max(_s[2] - _s[0], _s[3] - _s[1]) / 0.74

VARIANTEN = {
    # name: (format, eins)
    "auf-dunkel": (F["auf-grund"], F["signal"]),
    "auf-hell": (F["tinte"], F["signal"]),
    "einfarbig-weiss": ("#ffffff", "#ffffff"),
    "einfarbig-schwarz": ("#000000", "#000000"),
}


def svg(pfade, farben, rand=0.0, grund=None, kachel=None, titel="formateins", attrs=""):
    """Pfade als SVG. kachel: quadratische Flaeche, Inhalt mittig."""
    x0, y0, x1, y1 = umriss.grenzen(pfade)
    if kachel:
        b = h = kachel
        dx = (kachel - (x1 - x0)) / 2 - x0
        dy = (kachel - (y1 - y0)) / 2 + y1
    else:
        b, h = x1 - x0 + 2 * rand, y1 - y0 + 2 * rand
        dx, dy = rand - x0, rand + y1
    teile = []
    if grund:
        teile.append(f'<rect width="{b:g}" height="{h:g}" fill="{grund}"/>')
    for (name, pfad), farbe in zip(pfade.items(), farben):
        teile.append(f'<path fill="{farbe}" d="{umriss.svg_d(pfad, dx, dy)}"/>')
    b, h = round(b, 1), round(h, 1)
    return (f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {b:g} {h:g}" '
            f'width="{b / 10:g}" height="{h / 10:g}" role="img" aria-label="{titel}"{attrs}>'
            f'<title>{titel}</title>' + "".join(teile) + "</svg>")


def seitenverhaeltnis(pfade, rand=0.0):
    x0, y0, x1, y1 = umriss.grenzen(pfade)
    return (y1 - y0 + 2 * rand) / (x1 - x0 + 2 * rand)


# ---------------------------------------------------------------- Vorlagen
def html(koerper, stil=""):
    return (f'<!doctype html><html lang="de-CH"><meta charset="utf-8">'
            f'<link rel="stylesheet" href="{SCHRIFTEN_CSS}">'
            f'<style>*{{margin:0;padding:0;box-sizing:border-box}}'
            f'html,body{{background:transparent;-webkit-font-smoothing:antialiased}}{stil}</style>'
            f'{koerper}</html>')


def bild(svg_datei, breite, hoehe):
    return html(f'<img src="{svg_datei.as_uri()}" style="display:block;width:{breite}px;height:{hoehe}px">')


VOLL = ' style="display:block;width:100%;height:auto"'
HOCH = ' style="display:block;height:100%;width:auto"'
VERLAUF = f"radial-gradient(125% 95% at 10% 4%,{F['grund-licht']} 0%,{F['grund']} 50%,{F['grund-tief']} 100%)"
WORT_DUNKEL = svg(WORT, VARIANTEN["auf-dunkel"], attrs=VOLL)


def buehne(breite, hoehe, s, inhalt_links):
    """Bild im Stil von teilen.png. s: Skalierung gegenueber 1200x630."""
    logo_b = 34 * s * 4.906  # Breite der Wortmarke bei 34px Schrift
    stil = f"""
    html,body{{width:{breite}px;height:{hoehe}px;overflow:hidden}}
    body{{background:{VERLAUF};color:{F['auf-grund']};display:flex;flex-direction:column;
      padding:{78 * s}px {92 * s}px {78 * s}px {inhalt_links or 92 * s}px}}
    .logo{{width:{logo_b}px}}
    h1{{margin-top:auto;font-family:"Playfair Display";font-weight:900;font-size:{74 * s}px;line-height:1.06;letter-spacing:-.022em}}
    h1 span{{display:block;text-wrap:balance;font-family:"Schibsted Grotesk";font-weight:600;font-size:.98em;letter-spacing:-.04em;color:{F['signal']}}}
    .fuss{{margin-top:auto;display:flex;align-items:center;gap:{22 * s}px;font-family:"Schibsted Grotesk";font-weight:600;font-size:{24 * s}px;color:{F['auf-grund-still']}}}
    .fuss i{{width:{46 * s}px;height:{3 * s}px;background:{F['signal']}}}
    """
    return html(f'<div class="logo">{WORT_DUNKEL}</div>'
                f'<h1>Du machst gute Arbeit.<span>Man müsste sie online auch sehen.</span></h1>'
                f'<div class="fuss"><i></i>Webdesign aus Olten und Region</div>', stil)


def linkedin():
    # Das Profilbild deckt unten links einen Kreis ab, darum steht alles rechts.
    stil = f"""
    html,body{{width:1584px;height:396px;overflow:hidden}}
    body{{background:{VERLAUF};color:{F['auf-grund']};display:flex;align-items:center;justify-content:flex-end;padding:0 110px}}
    .block{{width:820px}}
    h1{{font-family:"Playfair Display";font-weight:900;font-size:54px;line-height:1.06;letter-spacing:-.022em}}
    h1 span{{display:block;font-family:"Schibsted Grotesk";font-weight:600;font-size:.98em;letter-spacing:-.04em;color:{F['signal']}}}
    .fuss{{margin-top:34px;display:flex;align-items:center;gap:20px;font-family:"Schibsted Grotesk";font-weight:600;font-size:21px;color:{F['auf-grund-still']}}}
    .fuss .logo{{width:150px}}
    .fuss i{{width:36px;height:2px;background:{F['signal']}}}
    """
    return html(f'<div class="block"><h1>Du machst gute Arbeit.<span>Man müsste sie online auch sehen.</span></h1>'
                f'<div class="fuss"><div class="logo">{WORT_DUNKEL}</div><i></i>Webdesign aus Olten und Region</div></div>', stil)


# ---------------------------------------------------------------- Stylesheet
def stylesheet(stand):
    def wort(var, breite, rand=0.0):
        return f'<div style="width:{breite}">{svg(WORT, VARIANTEN[var], rand=rand, attrs=VOLL)}</div>'

    def sig(farben, grund=None, groesse="100%"):
        return (f'<div style="width:{groesse}">' + svg(SIGNET, farben, grund=grund, kachel=KACHEL, titel="formateins Signet",
                attrs=VOLL) + '</div>')

    o_em = SCHUTZ / 1000
    breite_em = (umriss.grenzen(WORT)[2] - umriss.grenzen(WORT)[0]) / 1000

    def kopf(nr, titel):
        return (f'<div class="kopfzeile"><span>{nr} · {titel}</span>'
                f'<span class="mini">{svg(WORT, VARIANTEN["auf-hell"], attrs=HOCH)}</span></div>')

    # Farben
    gross = []
    for name, var, rolle in PALETTE[:5]:
        hx = F[var]
        c, m, y, k = cmyk(hx)
        r, g, b = rgb(hx)
        rand = ";box-shadow:inset 0 0 0 1px rgba(21,32,42,.14)" if var in ("papier", "auf-grund") else ""
        gross.append(f'''<div class="farbe"><div class="feld" style="background:{hx}{rand}"></div>
          <b>{name}</b><p>{rolle}</p>
          <dl><dt>HEX</dt><dd>{hx.upper()}</dd><dt>RGB</dt><dd>{r} {g} {b}</dd>
          <dt>CMYK</dt><dd>{c} {m} {y} {k}</dd><dt>CSS</dt><dd>--{var}</dd></dl></div>''')
    klein = []
    for name, var, rolle in PALETTE[5:]:
        hx = F[var]
        klein.append(f'<div class="neben"><i style="background:{hx}"></i><b>{name}</b><span>{hx.upper()}</span><p>{rolle}</p></div>')

    paare = [
        ("Kreide auf Schiefer", "auf-grund", "grund", "Fliesstext"),
        ("Kreide still auf Schiefer", "auf-grund-still", "grund", "Leiser Text"),
        ("Signal auf Schiefer", "signal", "grund", "Pointe, Logo"),
        ("Tinte auf Signal", "tinte", "signal", "Knopf"),
        ("Tinte auf Papier", "tinte", "papier", "Text auf hell"),
        ("Signal auf Papier", "signal", "papier", "nur Logo und Flächen"),
    ]
    kontraste = "".join(
        f'<tr><td><i style="background:{F[h]}"></i><i style="background:{F[v]}"></i>{n}</td>'
        f'<td>{kontrast(F[v], F[h]):.1f} : 1</td><td>{z}</td></tr>' for n, v, h, z in paare)

    s = f"""
@page{{size:297mm 210mm;margin:0}}
html{{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
body{{font-family:"Schibsted Grotesk";color:{F['tinte']};font-size:9.5pt;line-height:1.5}}
.seite{{width:297mm;height:210mm;position:relative;overflow:hidden;background:{F['papier']};padding:14mm 18mm 13mm;display:flex;flex-direction:column;break-after:page}}
.seite:last-child{{break-after:auto}}
.kopfzeile{{display:flex;justify-content:space-between;align-items:center;font-size:7.5pt;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:{F['tinte-still']}}}
.kopfzeile .mini{{height:12px}}
h2{{font-family:"Playfair Display";font-weight:900;font-size:27pt;line-height:1.06;letter-spacing:-.022em;color:{F['tinte-still']};margin:7mm 0 7mm;max-width:190mm}}
h2 span{{display:block;font-family:"Schibsted Grotesk";font-weight:600;font-size:.98em;letter-spacing:-.04em;color:{F['tinte']}}}
h3{{font-size:7.5pt;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:{F['tinte-still']};margin-bottom:2.5mm}}
.still{{color:{F['tinte-still']}}}
.flaeche{{background:{F['grund']};color:{F['auf-grund']};display:grid;place-items:center}}
.raster{{display:grid;gap:5mm}}
.notiz{{font-size:8.5pt;color:{F['tinte-still']};margin-top:2.5mm;line-height:1.45}}
.notiz b{{color:{F['tinte']};font-weight:700}}
code{{font-family:ui-monospace,Menlo,monospace;font-size:.9em}}

/* Titel */
.titel{{background:{VERLAUF};color:{F['auf-grund']};padding:22mm 24mm}}
.titel .wort{{width:150mm;margin-top:auto}}
.titel .unten{{margin-top:auto;display:flex;justify-content:space-between;align-items:flex-end;font-weight:600;font-size:11pt;color:{F['auf-grund-still']}}}
.titel .unten b{{display:block;font-family:"Playfair Display";font-weight:900;font-size:24pt;letter-spacing:-.02em;color:{F['auf-grund']};margin-bottom:2mm}}
.titel .strich{{display:flex;align-items:center;gap:5mm}}
.titel .strich i{{width:12mm;height:2px;background:{F['signal']}}}

/* Logo */
.buehne{{height:62mm}}
.aufbau{{grid-template-columns:1fr 1fr 1.25fr;margin-top:5mm;margin-bottom:7mm}}
.aufbau p{{font-size:8.5pt;color:{F['tinte-still']}}}
.aufbau .name{{font-size:10pt;font-weight:700;color:{F['tinte']}}}
.schutz{{position:relative;background:{F['papier-still']};height:44mm;display:grid;place-items:center}}
.schutz .rahmen{{position:relative;padding:calc(var(--o) * 1);outline:1px dashed {F['tinte-still']}}}
.schutz .rahmen::before{{content:"";position:absolute;left:0;top:0;width:var(--o);height:var(--o);background:{F['signal']};opacity:.35}}

/* Varianten */
.varianten{{grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr;flex:1;min-height:0}}
.var{{display:grid;grid-template-rows:1fr auto}}
.var .f{{display:grid;place-items:center}}
.var .t{{padding-top:2mm;font-size:8.5pt;color:{F['tinte-still']}}}
.var .t b{{color:{F['tinte']}}}
.signete{{display:grid;grid-template-columns:repeat(3,22mm) 1fr;gap:5mm;align-items:center;margin-top:5mm}}
.signete .bg{{width:22mm;height:22mm;display:grid;place-items:center}}
.nie{{display:grid;grid-template-columns:repeat(4,1fr);gap:5mm;margin-top:5mm;font-size:8.5pt;color:{F['tinte-still']}}}
.nie b{{display:block;color:{F['tinte']}}}

/* Farben */
.farben{{grid-template-columns:repeat(5,1fr)}}
.farbe .feld{{height:30mm}}
.farbe b{{display:block;margin-top:3mm;font-size:11pt}}
.farbe p{{font-size:8pt;color:{F['tinte-still']};min-height:8mm;line-height:1.35;margin-top:.5mm}}
.farbe dl{{display:grid;grid-template-columns:11mm 1fr;font-size:8pt;margin-top:2mm;row-gap:.6mm}}
.farbe dt{{color:{F['tinte-still']}}}
.farbe dd{{font-variant-numeric:tabular-nums;font-weight:600}}
.unterteil{{display:grid;grid-template-columns:1.5fr 1fr;gap:10mm;margin-top:auto}}
.nebenfarben{{display:grid;grid-template-columns:1fr 1fr;gap:2.2mm 6mm}}
.neben{{display:grid;grid-template-columns:7mm auto 1fr;gap:0 2.5mm;align-items:center;font-size:8pt}}
.neben i{{grid-row:span 2;width:7mm;height:7mm;box-shadow:inset 0 0 0 1px rgba(21,32,42,.1)}}
.neben span{{color:{F['tinte-still']};font-variant-numeric:tabular-nums}}
.neben p{{grid-column:2/4;color:{F['tinte-still']};font-size:7.5pt;line-height:1.3}}
table{{border-collapse:collapse;width:100%;font-size:8pt}}
td{{padding:1.3mm 0;border-top:1px solid {F['papier-still']}}}
td:nth-child(2){{font-weight:700;font-variant-numeric:tabular-nums;white-space:nowrap;padding:0 4mm}}
td:nth-child(3){{color:{F['tinte-still']}}}
td i{{display:inline-block;width:3.2mm;height:3.2mm;margin-right:1mm;vertical-align:-.5mm;box-shadow:inset 0 0 0 1px rgba(21,32,42,.12)}}
td i+i{{margin-right:2.5mm}}

/* Schriften */
.schriften{{grid-template-columns:1fr 1fr;gap:10mm;flex:1}}
.schrift{{display:flex;flex-direction:column}}
.schrift .aa{{font-size:92pt;line-height:1;letter-spacing:-.03em;margin:-2mm 0 3mm}}
.schrift .name{{font-size:15pt;font-weight:700}}
.schrift .alpha{{font-size:13pt;line-height:1.35;margin:3mm 0 4mm;color:{F['tinte-still']}}}
.schnitte{{border-top:1px solid {F['papier-still']}}}
.schnitte div{{display:grid;grid-template-columns:30mm 1fr;padding:1.6mm 0;border-bottom:1px solid {F['papier-still']};font-size:9pt}}
.schnitte span{{color:{F['tinte-still']}}}
.serif{{font-family:"Playfair Display"}}
.bezug{{margin-top:auto;font-size:8.5pt;color:{F['tinte-still']};max-width:170mm}}

/* Im Einsatz */
.einsatz{{grid-template-columns:1.35fr 1fr;gap:10mm;flex:1;min-height:0}}
.probe{{background:{VERLAUF};color:{F['auf-grund']};padding:11mm 12mm;display:flex;flex-direction:column}}
.probe .et{{font-size:7.5pt;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:{F['auf-grund-still']}}}
.probe h1{{font-family:"Playfair Display";font-weight:900;font-size:30pt;line-height:1.06;letter-spacing:-.022em;margin:5mm 0 5mm}}
.probe h1 span{{display:block;font-family:"Schibsted Grotesk";font-weight:600;font-size:.98em;letter-spacing:-.04em;color:{F['signal']}}}
.probe p{{color:{F['auf-grund-still']};max-width:105mm;font-size:10pt;line-height:1.6}}
.probe .knoepfe{{display:flex;gap:4mm;margin-top:auto}}
.knopf{{display:inline-flex;align-items:center;background:{F['signal']};color:{F['tinte']};font-weight:700;font-size:10pt;padding:3.6mm 5.6mm}}
.knopf.umriss{{background:none;color:{F['auf-grund']};border:1px solid color-mix(in srgb,{F['auf-grund']} 55%,transparent)}}
.regeln div{{display:grid;grid-template-columns:27mm 1fr;padding:2.1mm 0;border-bottom:1px solid {F['papier-still']};font-size:8.5pt;line-height:1.4}}
.regeln div:first-child{{border-top:1px solid {F['papier-still']}}}
.regeln b{{font-weight:700}}
.regeln span{{color:{F['tinte-still']}}}

/* Grundsaetze und Dateien */
.zwei{{grid-template-columns:1fr 1fr;gap:12mm;flex:1;align-content:start}}
.satz{{padding:2.6mm 0;border-bottom:1px solid {F['papier-still']};font-size:9pt}}
.satz:first-of-type{{border-top:1px solid {F['papier-still']}}}
.satz b{{display:block;font-size:10pt}}
.satz span{{color:{F['tinte-still']}}}
.dateien div{{display:grid;grid-template-columns:38mm 1fr;padding:2mm 0;border-bottom:1px solid {F['papier-still']};font-size:8.5pt;line-height:1.4}}
.dateien div:first-child{{border-top:1px solid {F['papier-still']}}}
.dateien code{{font-weight:600}}
.dateien span{{color:{F['tinte-still']}}}
.schluss{{display:flex;justify-content:space-between;font-size:8pt;color:{F['tinte-still']};border-top:1px solid {F['papier-still']};padding-top:3mm}}
"""
    min_px = 100
    min_mm = 25

    seiten = []
    # 1 Titel
    seiten.append(f'''<section class="seite titel">
  <div class="kopfzeile" style="color:{F['auf-grund-still']}"><span>Stylesheet</span><span>Stand {stand}</span></div>
  <div class="wort">{svg(WORT, VARIANTEN["auf-dunkel"], attrs=VOLL)}</div>
  <div class="unten"><div><b>Logo, Farben, Schriften.</b>Was formateins ausmacht, auf sieben Seiten.</div>
    <div class="strich"><i></i>Webdesign aus Olten und Region</div></div>
</section>''')

    # 2 Logo
    seiten.append(f'''<section class="seite">
  {kopf("01", "Logo")}
  <h2>Zwei Stimmen, ein Wort.<span>format in der Grotesk, eins in der Serif.</span></h2>
  <div class="flaeche buehne">{wort("auf-dunkel", "130mm")}</div>
  <div class="raster aufbau">
    <div><p class="name">format</p><p>Schibsted Grotesk Bold (700)<br>Laufweite −35<br>Kreide auf Schiefer, Tinte auf hell</p></div>
    <div><p class="name">eins</p><p>Playfair Display Black (900)<br>Laufweite −10<br>Immer Signal, ausser einfarbig</p></div>
    <div><p class="name">Schutzraum und Mindestgrösse</p><p>Rundum frei bleibt mindestens die Höhe des o, hier orange markiert.
      Kleiner als {min_px} px oder {min_mm} mm Breite nicht verwenden, darunter übernimmt das Signet.
      Das Logo ist eine Grafik, nie nachsetzen: immer die Dateien verwenden.</p></div>
  </div>
  <div class="raster" style="grid-template-columns:1.6fr 1fr;margin-top:auto">
    <div class="schutz" style="--o:{o_em / breite_em * 70:.3f}mm"><div class="rahmen">{wort("auf-hell", "70mm")}</div></div>
    <div class="schutz" style="background:{F['papier']};outline:1px solid {F['papier-still']};gap:3mm;align-content:center">
      <div style="display:flex;align-items:center;gap:6mm">{wort("auf-hell", f"{min_mm}mm")}{sig((F['auf-grund'], F['signal']), grund=F['grund'], groesse="6mm")}</div>
      <span class="notiz" style="margin:0">{min_mm} mm · darunter das Signet</span></div>
  </div>
</section>''')

    # 3 Varianten
    def var(feld, inhalt, datei, text, rahmen=False):
        r = f";box-shadow:inset 0 0 0 1px {F['papier-still']}" if rahmen else ""
        return (f'<div class="var"><div class="f" style="background:{feld}{r}">{inhalt}</div>'
                f'<div class="t"><b>{text}</b> · <code>{datei}</code></div></div>')
    seiten.append(f'''<section class="seite">
  {kopf("02", "Varianten")}
  <h2>Schiefer zuerst.<span>Die anderen Fassungen sind für Ausnahmen.</span></h2>
  <div class="raster varianten">
    {var(F['grund'], wort("auf-dunkel", "62%"), "logo-auf-dunkel", "Standard")}
    {var("#ffffff", wort("auf-hell", "62%"), "logo-auf-hell", "Auf Weiss und Papier", True)}
    {var(F['grund-tief'], wort("einfarbig-weiss", "62%"), "logo-einfarbig-weiss", "Auf Fotos und unruhigem Grund")}
    {var("#ffffff", wort("einfarbig-schwarz", "62%"), "logo-einfarbig-schwarz", "Stempel, Gravur, Schwarzweiss", True)}
  </div>
  <div class="signete">
    <div class="bg">{sig((F['auf-grund'], F['signal']), grund=F['grund'])}</div>
    <div class="bg" style="background:{F['grund']}">{sig((F['auf-grund'], F['signal']), groesse="100%")}</div>
    <div class="bg" style="background:#fff;box-shadow:inset 0 0 0 1px {F['papier-still']}">{sig((F['tinte'], F['signal']), groesse="100%")}</div>
    <div class="notiz" style="margin:0"><b>Signet fe</b> · für Favicon, App-Symbol und Profilbild, also nur dort, wo die Wortmarke zu klein würde. Als Kachel auf Schiefer oder frei gestellt.</div>
  </div>
  <div class="nie">
    <div><b>Nicht verzerren</b>Nur proportional skalieren, nicht drehen.</div>
    <div><b>Keine Effekte</b>Keine Schatten, Konturen, Verläufe im Logo.</div>
    <div><b>Keine dritte Farbe</b>eins ist Signal oder einfarbig wie der Rest.</div>
    <div><b>Ruhiger Grund</b>Auf Fotos nur einfarbig weiss, wo es ruhig ist.</div>
  </div>
</section>''')

    # 4 Farben
    seiten.append(f'''<section class="seite">
  {kopf("03", "Farben")}
  <h2>Kühler Grund, warmes Signal.<span>Orange ist eine Handlung, keine Dekoration.</span></h2>
  <div class="raster farben">{"".join(gross)}</div>
  <div class="unterteil">
    <div><h3>Ergänzungen</h3><div class="nebenfarben">{"".join(klein)}</div></div>
    <div><h3>Kontrast</h3><table>{kontraste}</table>
      <p class="notiz">Ab 4.5 : 1 lesbar für Fliesstext. CMYK sind Richtwerte, vor dem Druck mit der Druckerei abstimmen.</p></div>
  </div>
</section>''')

    # 5 Schriften
    alpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ<br>abcdefghijklmnopqrstuvwxyz äöü<br>0123456789 ’ „“ — CHF"
    seiten.append(f'''<section class="seite">
  {kopf("04", "Schriften")}
  <h2>Eine Serif für den Auftakt.<span>Eine Grotesk für alles, was arbeitet.</span></h2>
  <div class="raster schriften">
    <div class="schrift">
      <div class="aa serif" style="font-weight:900">Aa</div>
      <div class="name serif" style="font-weight:900;letter-spacing:-.01em">Playfair Display</div>
      <div class="alpha serif" style="font-weight:700">{alpha}</div>
      <div class="schnitte">
        <div><b>Black 900</b><span>Überschriften, eins im Logo</span></div>
        <div><b>Bold 700</b><span>Zwischentitel</span></div>
      </div>
    </div>
    <div class="schrift">
      <div class="aa" style="font-weight:700;letter-spacing:-.05em">Aa</div>
      <div class="name" style="letter-spacing:-.02em">Schibsted Grotesk</div>
      <div class="alpha">{alpha}</div>
      <div class="schnitte">
        <div><b>Regular 400</b><span>Fliesstext</span></div>
        <div><b>SemiBold 600</b><span>Pointe, die zweite Zeile einer Überschrift</span></div>
        <div><b>Bold 700</b><span>format im Logo, Knöpfe, Etiketten</span></div>
      </div>
    </div>
  </div>
  <p class="bezug">Beide Schriften sind frei unter der SIL Open Font License und kostenlos bei Google Fonts:
    <b>fonts.google.com/specimen/Playfair+Display</b> und <b>fonts.google.com/specimen/Schibsted+Grotesk</b>.
    Die Website liefert sie selbst aus, ohne Verbindung zu Google. Ersatz, wo sie fehlen: Georgia und Helvetica.</p>
</section>''')

    # 6 Im Einsatz
    seiten.append(f'''<section class="seite">
  {kopf("05", "Im Einsatz")}
  <h2>Die Serif setzt an,<span>die Grotesk sagt, worum es geht.</span></h2>
  <div class="raster einsatz">
    <div class="probe">
      <div class="et">Webdesign aus Olten und Region</div>
      <h1>Du machst gute Arbeit.<span>Man müsste sie online auch sehen.</span></h1>
      <p>Ich baue Websites für Selbstständige und kleine Unternehmen in Olten und der Region, die ihr Fach verstehen, online aber kaum stattfinden. Von Hand gebaut, zum Festpreis.</p>
      <div class="knoepfe"><span class="knopf">Was kostet meine Website?</span><span class="knopf umriss">Gespräch vereinbaren</span></div>
    </div>
    <div class="regeln">
      <div><b>Überschrift</b><span>Playfair Display Black, Laufweite −22, Zeilenabstand 1.06</span></div>
      <div><b>Pointe</b><span>Zweite Zeile: Schibsted Grotesk SemiBold, 98 % der Grösse, Laufweite −40. Im ersten Bild in Signal, sonst Tinte oder Kreide.</span></div>
      <div><b>Fliesstext</b><span>Schibsted Grotesk Regular, 16 bis 17.5 px, Zeilenabstand 1.6</span></div>
      <div><b>Etikett</b><span>Schibsted Grotesk Bold, Versalien, Laufweite +80, klein</span></div>
      <div><b>Knopf</b><span>Signal mit Tinte, Bold, eckig. Daneben höchstens ein Umriss-Knopf.</span></div>
      <div><b>Ecken</b><span>Flächen und Knöpfe eckig. Rund sind nur Punkte und Chips.</span></div>
    </div>
  </div>
</section>''')

    # 7 Grundsaetze und Dateien
    seiten.append(f'''<section class="seite">
  {kopf("06", "Grundsätze und Dateien")}
  <h2>Weniger, aber genau.<span>Und welche Datei wofür.</span></h2>
  <div class="raster zwei">
    <div>
      <h3>Grundsätze</h3>
      <div class="satz"><b>Schiefer trägt.</b><span>Dunkle Flächen sind der Normalfall, Papier ist die Abwechslung.</span></div>
      <div class="satz"><b>Ein Signal pro Ansicht.</b><span>Orange markiert die eine Handlung oder die eine Pointe. Kommt es viermal vor, ist es Dekor.</span></div>
      <div class="satz"><b>Du, kurz, konkret.</b><span>Ansprache per du. Kein Fachjargon, keine Floskeln.</span></div>
      <div class="satz"><b>Schweizer Schreibweise.</b><span>ss statt ß. Tausender mit Apostroph: CHF 2’400. Anführungszeichen „so“.</span></div>
    </div>
    <div>
      <h3>Dateien im Paket</h3>
      <div class="dateien">
        <div><code>logo/svg</code><span>Für Web, Druck und Weitergabe. Skaliert ohne Verlust.</span></div>
        <div><code>logo/png</code><span>Für Word, PowerPoint, Canva. Durchsichtig oder mit Grund.</span></div>
        <div><code>signet</code><span>fe als Kachel oder frei gestellt.</span></div>
        <div><code>favicon</code><span>Browser-Symbol, Apple- und Android-Symbol, mit Einbau-Anleitung.</span></div>
        <div><code>social</code><span>Profilbild, LinkedIn-Banner, Titelbild für das Google-Profil, Link-Vorschau.</span></div>
        <div><code>web/formateins.css</code><span>Farben und Schriften als CSS-Variablen.</span></div>
      </div>
    </div>
  </div>
  <div class="schluss"><span>formateins · eine Marke der pzu consulting gmbh</span><span>formateins.ch · adrian@formateins.ch</span></div>
</section>''')

    return html("".join(seiten), s)


# ---------------------------------------------------------------- Bauen
def main():
    ziel_zip = Path(sys.argv[1] if len(sys.argv) > 1 else "~/Downloads/formateins-marke.zip").expanduser()
    stand = datetime.date.today().strftime("%B %Y")
    monate = {"January": "Januar", "February": "Februar", "March": "März", "April": "April", "May": "Mai",
              "June": "Juni", "July": "Juli", "August": "August", "September": "September",
              "October": "Oktober", "November": "November", "December": "Dezember"}
    for en, de in monate.items():
        stand = stand.replace(en, de)

    arbeit = Path(tempfile.mkdtemp(prefix="marke-"))
    paket = arbeit / "formateins-marke"
    vorlagen = arbeit / "vorlagen"
    for p in ("logo/svg", "logo/png", "signet", "favicon", "social", "web"):
        (paket / p).mkdir(parents=True)
    vorlagen.mkdir()
    auftraege = []

    def raster(html_text, breite, hoehe, aus, durchsichtig=True, pdf=False, y=0):
        datei = vorlagen / f"v{len(auftraege)}.html"
        datei.write_text(html_text)
        auftraege.append({"url": datei.as_uri(), "breite": breite, "hoehe": hoehe, "aus": str(aus),
                          "durchsichtig": durchsichtig, "pdf": pdf, "y": y})

    # Wortmarke
    for name, farben in VARIANTEN.items():
        datei = paket / "logo/svg" / f"formateins-logo-{name}.svg"
        datei.write_text(svg(WORT, farben))
        verh = seitenverhaeltnis(WORT)
        breiten = (1000, 2000, 4000) if name in ("auf-dunkel", "auf-hell") else (2000,)
        for b in breiten:
            raster(bild(datei, b, round(b * verh)), b, round(b * verh),
                   paket / "logo/png" / f"formateins-logo-{name}-{b}.png")
    # mit Grund, Rand = Schutzraum
    for grundname, grund, var in (("schiefer", F["grund"], "auf-dunkel"), ("papier", F["papier"], "auf-hell"),
                                  ("weiss", "#ffffff", "auf-hell")):
        datei = vorlagen / f"grund-{grundname}.svg"
        datei.write_text(svg(WORT, VARIANTEN[var], rand=SCHUTZ, grund=grund))
        verh = seitenverhaeltnis(WORT, SCHUTZ)
        raster(bild(datei, 2000, round(2000 * verh)), 2000, round(2000 * verh),
               paket / "logo/png" / f"formateins-logo-mit-grund-{grundname}-2000.png", False)
    # E-Mail-Signatur: auf Weiss, damit es auch im Dunkelmodus von Outlook lesbar bleibt
    datei = vorlagen / "signatur.svg"
    datei.write_text(svg(WORT, VARIANTEN["auf-hell"], rand=SCHUTZ * 0.5, grund="#ffffff"))
    verh = seitenverhaeltnis(WORT, SCHUTZ * 0.5)
    raster(bild(datei, 400, round(400 * verh)), 400, round(400 * verh),
           paket / "logo/png" / "formateins-logo-email-signatur-400.png", False)

    # Signet
    kachel_farben = (F["auf-grund"], F["signal"])
    signete = {
        "formateins-signet": (kachel_farben, F["grund"]),
        "formateins-signet-auf-dunkel": (kachel_farben, None),
        "formateins-signet-auf-hell": ((F["tinte"], F["signal"]), None),
    }
    for name, (farben, grund) in signete.items():
        datei = paket / "signet" / f"{name}.svg"
        datei.write_text(svg(SIGNET, farben, grund=grund, kachel=KACHEL, titel="formateins Signet"))
        raster(bild(datei, 1024, 1024), 1024, 1024, paket / "signet" / f"{name}-1024.png", grund is None)

    # Favicon. App-Symbole mit dem Rand der grossen Kachel, der reicht auch fuer
    # maskierbare Symbole (Inhalt innerhalb von 80 % Durchmesser).
    profil = paket / "signet" / "formateins-signet.svg"
    fav = paket / "favicon" / "favicon.svg"
    fav.write_text(svg(SIGNET, kachel_farben, grund=F["grund"], kachel=KACHEL_KLEIN, titel="formateins"))
    for g in (16, 32, 48):
        raster(bild(fav, g, g), g, g, vorlagen / f"f{g}.png", False)
    for g, name in ((180, "apple-touch-icon.png"), (192, "icon-192.png"), (512, "icon-512.png")):
        raster(bild(profil, g, g), g, g, paket / "favicon" / name, False)

    # Social
    raster(bild(profil, 1080, 1080), 1080, 1080, paket / "social/profilbild-1080x1080.png", False)
    raster(linkedin(), 1584, 396, paket / "social/linkedin-banner-1584x396.png", False)
    raster(buehne(1920, 1080, 1.6, None), 1920, 1080, paket / "social/google-titelbild-1920x1080.png", False)
    shutil.copy(REPO / "teilen.png", paket / "social/link-vorschau-1200x630.png")

    # Stylesheet
    sg = stylesheet(stand)
    raster(sg, 1123, 794, paket / "formateins-stylesheet.pdf", False, pdf=True)
    for i in range(7):
        raster(sg, 1123, 794, arbeit / f"stylesheet-{i + 1}.png", False, y=round(i * 793.7))

    (arbeit / "auftraege.json").write_text(json.dumps(auftraege))
    subprocess.run(["node", str(HIER / "raster.mjs"), str(arbeit / "auftraege.json")], check=True)

    # favicon.ico aus den einzeln gerenderten Groessen
    ico = [Image.open(vorlagen / f"f{g}.png").convert("RGBA") for g in (16, 32, 48)]
    ico[2].save(paket / "favicon/favicon.ico", sizes=[(16, 16), (32, 32), (48, 48)], append_images=ico[:2])

    (paket / "favicon/site.webmanifest").write_text(json.dumps({
        "name": "formateins", "short_name": "formateins",
        "icons": [{"src": "icon-192.png", "sizes": "192x192", "type": "image/png"},
                  {"src": "icon-512.png", "sizes": "512x512", "type": "image/png"},
                  {"src": "icon-512.png", "sizes": "512x512", "type": "image/png", "purpose": "maskable"}],
        "theme_color": F["grund"], "background_color": F["grund"],
    }, indent=2, ensure_ascii=False) + "\n")
    (paket / "favicon/EINBAUEN.txt").write_text(
        "Alle Dateien dieses Ordners ins Hauptverzeichnis der Website legen und im <head> einfügen:\n\n"
        '<link rel="icon" href="/favicon.ico" sizes="32x32">\n'
        '<link rel="icon" href="/favicon.svg" type="image/svg+xml">\n'
        '<link rel="apple-touch-icon" href="/apple-touch-icon.png">\n'
        '<link rel="manifest" href="/site.webmanifest">\n'
        f'<meta name="theme-color" content="{F["grund"]}">\n\n'
        "Das Signet steht mit genug Rand in der Kachel, darum taugt icon-512.png auch als maskierbares Symbol.\n")

    # CSS-Variablen direkt aus der Website
    quelle = (REPO / "index.html").read_text()
    root = re.search(r":root\{.*?\n\}", quelle, re.S).group(0)
    (paket / "web/formateins.css").write_text(
        f"/* formateins · Farben, Schriften, Textskala. Stand {stand}.\n"
        "   Auszug aus der Website. Die Schriften sind kostenlos bei Google Fonts. */\n" + root + "\n")

    (paket / "LIESMICH.txt").write_text(f"""formateins · Markenpaket · Stand {stand}

formateins-stylesheet.pdf   Logo, Farben, Schriften, Regeln. Zuerst lesen.

logo/svg     Wortmarke als Vektor. Für Web, Druck, Weitergabe an Druckereien.
             auf-dunkel ist der Standard, auf-hell für weissen Grund.
logo/png     Dieselben als Bild in 1000, 2000 und 4000 px Breite, durchsichtig.
             mit-grund-…: mit Schutzraum auf farbiger Fläche.
             email-signatur-400: in der Signatur auf 200 px Breite setzen.
signet       fe, das Zeichen für kleine Flächen.
favicon      Browser- und App-Symbole, Anleitung in EINBAUEN.txt.
social       Profilbild, LinkedIn-Banner, Titelbild Google-Profil, Link-Vorschau.
web          Farben und Schriften als CSS-Variablen.

Schriften: Playfair Display und Schibsted Grotesk, beide kostenlos bei
Google Fonts (SIL Open Font License). Die Logo-Dateien brauchen keine
installierte Schrift, die Buchstaben sind Pfade.

formateins ist eine Marke der pzu consulting gmbh.
""")

    ziel_zip.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(ziel_zip, "w", zipfile.ZIP_DEFLATED) as z:
        for p in sorted(paket.rglob("*")):
            if p.is_file():
                z.write(p, p.relative_to(arbeit))
    print(f"{ziel_zip}\nVorschau: {arbeit}")


if __name__ == "__main__":
    main()
