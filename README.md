# formateins — Website

Entwurf der eigenen Website. Webdesign für Selbstständige und kleine Unternehmen
in Olten und der Region.

Statisches HTML, kein Build-Schritt, keine externen Abhängigkeiten. Die zwei
Schriften liegen selbst im Projekt (`schriften/`, `schriften.css`).

## Dateien

| Datei | Inhalt |
|---|---|
| `index.html` | Startseite |
| `kostenrechner.html` | Kostenrechner, sieben Fragen, Preisrahmen vor dem Formular |
| `anfrage.php` | Nimmt die Anfrage aus dem Rechner an und schickt sie als Mail |
| `impressum.html`, `datenschutz.html` | Rechtsseiten, gemeinsame Gestaltung in `recht.css`, dauerhaft `noindex` |
| `foto.webp`, `portrait.webp` | Fotos, siehe unten |
| `schriften/`, `schriften.css` | Selbst ausgelieferte Schriften, siehe `schriften/LIZENZ.md` |
| `quellen/` | Vorlagen fuer Teilen-Bild und Favicon, siehe unten |
| `archiv/` | Frühere Entwurfsrichtungen, nicht mehr aktuell |

## Noch offen

- **Preise** sind gesetzt, im Objekt `preis` im Skript von `kostenrechner.html`.
  Grundpreis und Zuschlaege je Groesse (Texte, Bilder, Logo, Funktionen), daraus ein Punktwert, der Rahmen ist
  -10 % bis +15 % davon. Ueberarbeiten -20 %, Eilzuschlag +15 % nur bei Termin
  unter sechs Wochen. Lage: oberes Freelancer-Feld, rund halb so teuer wie eine
  Kleinagentur. Nach den ersten echten Projekten nachkalibrieren.
- **Laufende Kosten** stehen im Ergebnis als feste Zeile: rund CHF 200–250 im
  Jahr fuer Hosting und Domain (Hostpoint Standard ab CHF 15.90/Monat plus Domain), beim Anbieter auf den Namen des Kunden. Betrag
  steht im HTML (`class="laufend"`), pruefen wenn der Hoster die Preise aendert.
- **Fotos und Video vor Ort** bleiben ohne Zahl, weil Anfahrt und Umfang den
  Preis bestimmen; Video ist deutlich aufwendiger als Fotos. Der
  Rechner fragt dafuer freiwillig den Ort ab, er steht in der Anfrage.
- **Versand der Anfragen** laeuft ueber `anfrage.php` beim Hoster (Hostpoint).
  Das Skript prueft die Angaben, schickt sie als Mail und legt keine Anfrage ab. Es
  bremst nach fuenf Anfragen pro Stunde und Absender, faengt Spam-Programme ueber
  ein unsichtbares Feld ab und nimmt nur Anfragen von der eigenen Seite an.
  Auf GitHub Pages laeuft kein PHP, dort schlaegt der Versand bewusst fehl.
- **Fotos** sind drin, aus einer Vorlage mit 1448 Pixel Breite: `foto.webp`
  (480 x 480, runder Platz im ersten Bild) und `portrait.webp` (680 x 850, 4:5,
  Abschnitt „Wer das macht"). Vor dem Livegang aus dem Original neu schneiden,
  damit sie auf Bildschirmen mit doppelter Pixeldichte scharf sind:

  ```bash
  cwebp -metadata none -q 80 -crop X Y B H -resize 480 480 original.jpg -o foto.webp
  cwebp -metadata none -q 80 -crop X Y B H -resize 680 850 original.jpg -o portrait.webp
  ```

  Der Kreis wird bis 190 Pixel breit, das Hochformat bis 340, gebraucht werden
  also mindestens 380 und 680 Pixel. `-metadata none` entfernt Kameradaten und Ort.

- **Referenzen** fehlen noch, bewusst. Der Hinweis auf die Hochzeitsfilme ist
  raus, damit steht aktuell kein Beleg auf der Seite. Mehrere Kundenprojekte
  sind kurz vor Abschluss; die Seite geht ohnehin erst danach online. Sobald
  drei Projekte live sind, kommt ein Referenzabschnitt zwischen „Nicht jede
  Website braucht alles" und die häufigen Fragen.

  **Währenddessen einsammeln, nicht nachher:** pro Projekt eine schriftliche
  Freigabe des Kunden, ein bis zwei Bildschirmaufnahmen, und einen Satz dazu,
  was die Seite für den Betrieb geändert hat. Danach fragen zu müssen kostet
  Wochen und liefert schlechtere Antworten.
- **Markenname** ist provisorisch AVA.
- **Kontaktweg** fehlt: keine E-Mail-Adresse, keine Telefonnummer hinterlegt.
- **Datenschutzerklaerung geht davon aus**, dass `EMPFAENGER` ein Postfach bei
  Hostpoint ist. Wird an Hotmail oder einen anderen Anbieter weitergeleitet,
  muss dieser dort genannt werden.

## Vor dem Livegang

Die SEO-Grundlage steht, aber sie ist bewusst stillgelegt. Alle Schalter sind
im Code mit `LIVEGANG` markiert — `grep -rn LIVEGANG .` findet sie alle.

1. **noindex entfernen** in `index.html` und `kostenrechner.html`. Solange die
   Zeile steht, bleibt die Seite aus dem Suchindex. Das ist Absicht: mit
   Platzhalterpreisen und toten Links waere eine Indexierung schaedlich, weil
   Google die Entwurfsfassung behaelt und sie spaeter gegen die echte Domain
   antreten laesst.
2. **Domain einsetzen** — `DOMAIN.ch` ersetzen in `robots.txt`, `sitemap.xml`
   und im JSON-LD am Ende von `index.html`. Dort auch `canonical` und `og:url`
   einkommentieren.
3. **Versand einrichten.** Bei Hostpoint ein Postfach auf der eigenen Domain
   anlegen, zum Beispiel `anfrage@DOMAIN.ch`. In `anfrage.php` oben
   `EMPFAENGER` (wo die Anfragen ankommen) und `ABSENDER` (dieses Postfach)
   eintragen. Der Absender muss auf der eigenen Domain liegen, sonst landen die
   Mails im Spam. In `kostenrechner.html` bei `VERSAND.mail` die eigene Adresse
   als Rueckfallweg eintragen. Danach eine Testanfrage abschicken und pruefen,
   ob sie ankommt, auch im Spam-Ordner nachsehen.
4. **Adresse und Telefon** ergaenzen: im JSON-LD, in `impressum.html` und in
   `datenschutz.html` — `grep -rn AUSFUELLEN .` findet alle Stellen.
   Ein LocalBusiness ohne Adresse wird von Google weitgehend ignoriert.
5. **Markenname bestaetigen.** Die Seite steht durchgehend auf formateins
   (Titel, Logo, Teilen-Bild, Favicon, JSON-LD). Vor dem Livegang Name bei
   nic.ch und swissreg.ch pruefen und kaufen. Faellt der Name weg, alle Stellen
   mit `grep -rni formateins .` finden.
6. **Google Business Profile anlegen.** Bei lokalen Suchen dominiert das
   Kartenpaket ueber den organischen Treffern. Das ist wirksamer als jede
   Meta-Optimierung und kostet nichts.

Was bereits liegt: `favicon.png`, `apple-touch-icon.png`, `teilen.png` (Open-Graph-Bild 1200x630),
`robots.txt`, `sitemap.xml`, JSON-LD-Geruest, `lang="de-CH"`, Open Graph und
Twitter Cards auf beiden Seiten.

## Logo

Wortmarke ohne Zeichen: „format“ in Schibsted Grotesk 700, „eins“ in Playfair
Display 900 und Signalorange — dieselben zwei Stimmen wie in den Ueberschriften.
Im HTML als `<span class="s">format</span><span class="p">eins</span>` in
`.logo`. Die verworfenen Varianten liegen in `archiv/logo-varianten.html`.

## Teilen-Bild und Favicon neu erzeugen

Die Bilder werden aus `quellen/` mit Chrome gerendert, damit sie die echten
Schriften tragen. Lokalen Server starten (siehe unten), dann:

```bash
C="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
"$C" --headless=new --hide-scrollbars --force-device-scale-factor=1 --virtual-time-budget=3000 --window-size=1200,630 --screenshot=teilen.png http://localhost:8000/quellen/teilen.html
"$C" --headless=new --hide-scrollbars --force-device-scale-factor=1 --virtual-time-budget=3000 --window-size=600,600 --screenshot=apple-touch-icon.png http://localhost:8000/quellen/favicon.html
sips -c 180 180 apple-touch-icon.png && sips -z 64 64 apple-touch-icon.png --out favicon.png
```

Chrome hat eine Mindest-Fenstergroesse, darum wird das Favicon gross gerendert
und danach ausgeschnitten.

## Lokal ansehen

```bash
python3 -m http.server 8000
```

Dann `http://localhost:8000` öffnen.
