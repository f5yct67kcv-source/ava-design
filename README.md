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
| `gespraech.html`, `termine.php` | Terminbuchung, siehe „Gespräch vereinbaren“ unten |
| `kalender-zugang.php` | Platzhalter für den Kalenderzugang, der Upload füllt ihn aus den Secrets |
| `messung.js` | Banner und Messung mit Google, ausgeschaltet bis eine Kennung steht |
| `impressum.html`, `datenschutz.html` | Rechtsseiten, gemeinsame Gestaltung in `recht.css`, dauerhaft `noindex` |
| `foto.webp`, `portrait.webp` | Fotos, siehe unten |
| `arbeiten/` | Aufnahmen der Projekte für die Werkbank, siehe unten |
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
  ein unsichtbares Feld und eine Zeitfalle ab (unter acht Sekunden Seitenzeit gibt
  es ein falsches „ok“) und nimmt nur Anfragen von der eigenen Seite an.
- **Telefon ist Pflicht** im Anfrageformular (Schweizer Nummer mit zehn Ziffern
  oder +41, sonst Ausland mit Vorwahl; gleiche Regel in `kostenrechner.html`,
  `gespraech.html`, `anfrage.php` und `termine.php`). Grund: Angebote koennen im
  Spam landen, und vor dem Festpreis kommt ohnehin ein kurzes Gespraech, um die
  Vorstellung des Kunden zu verstehen. Der Preisrahmen bleibt ohne Nummer
  sichtbar, so steht es auch auf der Startseite.
- **Bestaetigung an den Kunden** schickt `anfrage.php` erst, wenn die Anfrage bei
  mir raus ist: HTML mit Textfassung, Preisrahmen und Antworten, Antworten gehen an
  adrian@formateins.ch. Gegen Missbrauch als Spam-Schleuder steht darin nichts
  frei Eingegebenes ausser einem geprueften Vornamen, keine Bemerkung, kein
  Betrieb, keine URL. Die Antworttexte kommen aus `ANTWORTEN` in `anfrage.php`
  (muss zu den `data-wert` im Rechner passen), nicht aus dem mitgeschickten Text.
  Hoechstens eine Bestaetigung pro Adresse und Tag, keine bei Links im Namen.
  Lokal testen: `php -d sendmail_path=… ` mit einem Skript, das die Mail in eine
  Datei schreibt.
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

- **Arbeiten** (`#arbeiten`, zwischen Angebot und häufigen Fragen) zeigen
  Projekte nicht als Galerie, sondern auf einer Werkbank. Zuerst ein Leuchttisch
  mit allen Projekten verkleinert (Fenster und Telefon), ein Klick zoomt in eines
  hinein. Dort: Desktop-Fenster mit Bauplan unter dem Schnitt, Telefon,
  Inspektor mit drei Kennzahlen in Kundensprache: wievielmal leichter als
  der Durchschnitt, Cookies, Hosting in der Schweiz. Beide Geräte fahren die
  Seite von selbst ab, in gleichem Tempo für jede Seitenlänge (`--tempo`, Zeit
  pro Bildschirm voll, heute 4 s). Zwei gestrichelte Plätze „in Arbeit“ halten Raum für weitere
  Projekte. Projekte: GuardOpS (online), Craniosacral Praxis Johann von Arb
  (noch ohne Fotos aufgenommen).

  **Aufnehmen.** `quellen/aufnahme.mjs` steuert Chrome über das
  DevTools-Protokoll und nimmt die ganze Seite in fester Breite auf. Chrome ohne
  Kopf rendert sonst nicht schmaler als rund 500 px, Handy-Aufnahmen wären
  breiter als 390 und rechts abgeschnitten. Die Ausgabe nennt die Seitenhöhe.

  ```bash
  node quellen/aufnahme.mjs https://ADRESSE/ 1440 1 desk.png
  node quellen/aufnahme.mjs https://ADRESSE/ 1440 1 plan.png quellen/bauplan.js
  node quellen/aufnahme.mjs https://ADRESSE/ 390 2 mobil.png
  cwebp -metadata none -q 62 desk.png -o arbeiten/NAME-desktop.webp
  cwebp -metadata none -q 62 plan.png -o arbeiten/NAME-bauplan.webp
  cwebp -metadata none -q 62 -resize 440 0 mobil.png -o arbeiten/NAME-mobil.webp
  ```

  Der Bauplan ist dieselbe Seite mit eingeschobenem Stil: Hintergründe weg,
  Text in Balken, Abschnitte gestrichelt. Er muss mit denselben Massen wie
  `desk.png` aufgenommen werden, sonst springt die Seite am Schnitt.

  Für den Leuchttisch je zwei kleine Vorschauen, das obere Bildschirmende:
  `NAME-vorschau.webp` (1440 × 900 aus `desk.png`, verkleinert auf 960 × 600)
  und `NAME-vorschau-mobil.webp` (780 × 1688 aus `mobil.png`, verkleinert auf
  220 × 476). Die Desktop-Vorschau dient im Projekt auch als Platzhalter, bis
  die grosse Aufnahme geladen ist.

  Danach in `index.html` die Höhen `--h` und `data-mass` auf die neue
  Seitenlänge setzen, die Kennzahlen im Inspektor im Browser nachmessen
  (Netzwerk, übertragene Grösse, Hosts, Cookies) und die Zahl hinter `?v=` an
  allen Bildadressen der Werkbank hochzählen. Sonst zeigen Browser bis zu zehn
  Minuten lang das alte Bild aus dem Zwischenspeicher.

  **Kennzahlen im Inspektor.** Der Faktor ist 2652 KB geteilt durch das
  gemessene Gewicht der ganzen Seite, abgerundet. 2652 KB ist der Median aller
  Seiten am Desktop laut HTTP Archive, Web Almanac 2024; die Quelle steht als
  Fussnote unter der Werkbank. Gibt es einen neueren Almanac, Wert und
  Fussnote zusammen nachführen. „0 Cookies“ und „CH“ nur hinschreiben, wenn es
  für das Projekt stimmt: keine Cookies, keine fremden Server, Hosting bei
  einem Schweizer Anbieter. Johanns Seite trägt CH schon, gilt aber erst nach
  dem Umzug zu Hostpoint (`LIVEGANG`).

  **Neues Projekt:** eine Karte auf dem Tisch und einen Stand auf der Fläche
  nach dem Muster der bestehenden ergänzen, einen gestrichelten Platz
  entfernen, die Zahl „2 Projekte“ und „von 2“ in der Werkzeugleiste
  anpassen. Pro Kundenprojekt vorher eine schriftliche Freigabe einholen.

- **Anbieterin** ist die pzu consulting gmbh (UID CHE-411.716.010), formateins
  ihre Marke. Die eingetragene Firma muss auf Seite, Offerten und Rechnungen
  sichtbar sein (Art. 954a OR), darum steht sie im Fuss und im Impressum.
  Nicht MWST-pflichtig (Umsatz unter CHF 100'000), darum steht im Rechner
  „Mehrwertsteuer kommt keine dazu“. Wird die GmbH pflichtig: Rechner, FAQ
  und Impressum anpassen. Der Zweck im Handelsregister wird noch um Webdesign
  erweitert.
- **Markenname** formateins. Auf swissreg.ch am 22.10.2026 geprüft, kein
  Konflikt. `formateins.ch` ist registriert. `format1.ch` als Umleitung
  darauf ist noch offen (fängt „eins“ als Ziffer ab).
- **Kontaktweg:** `adrian@formateins.ch` (Postfach bei Hostpoint, seit
  22.10.2026) steht im Fuss, im Abschnitt „Wer das macht“, in Impressum,
  Datenschutz und JSON-LD. Telefon 079 686 84 86 in Fuss, Impressum und JSON-LD. Bewusst nicht an Hotmail
  weiterleiten: Weiterleitungen landen dort gern im Spam, und die
  Datenschutzerklärung geht davon aus, dass Anfragen bei Hostpoint bleiben.
- **Datenschutzerklaerung geht davon aus**, dass `EMPFAENGER` ein Postfach bei
  Hostpoint ist. Wird an Hotmail oder einen anderen Anbieter weitergeleitet,
  muss dieser dort genannt werden.

## Auslieferung auf Hostpoint

Jeder Push auf `main` lädt die Seite per GitHub Action auf Hostpoint
(`.github/workflows/deploy-hostpoint.yml`), über FTPS mit
`SamKirkland/FTP-Deploy-Action`. Vorher prüft die Action die Syntax von
`anfrage.php` und `messung.js`, bei einem Fehler wird nichts hochgeladen.

Hochgeladen wird alles ausser `archiv/`, `quellen/`, `README.md`, `.github/`,
`.claude/` und `.gitignore`. Neue Dateien kommen also von selbst mit, nur neue
Ausnahmen gehören in den Workflow. `htaccess-hostpoint` wird als `.htaccess`
ausgeliefert: immer https und `www.formateins.ch`, Zwischenspeicher-Regeln,
Sicherheitskopfzeilen.

Secrets im Repository (Settings → Secrets and variables → Actions):

| Secret | Inhalt |
|---|---|
| `HOSTPOINT_FTP_HOST` | `sl250.web.hostpoint.ch` (Webhosting itufeden), nicht `ftp.formateins.ch`, sonst passt das Zertifikat nicht |
| `FORMATEINS_FTP_USER` | `deploy@formateins.ch`, Pfad `/home/itufeden/www/formateins.ch` |
| `FORMATEINS_FTP_PASSWORD` | dessen Passwort |
| `KALENDER_BENUTZER` | `adrian@formateins.ch`, Anmeldung bei Hostpoint Cloud Office |
| `KALENDER_PASSWORT` | dessen Passwort. Ändert es sich, hier nachführen, sonst geht jede Buchung nur als Mail ein. Alte Absage-Links werden dann ungültig |

Der FTP-Benutzer ist bewusst auf den einen Ordner beschränkt: Gelangt das
Passwort je nach aussen, kommt man damit nur an diese Seite.

Der Pfad muss genau der Document Root der Website sein (Hostpoint → Websites →
formateins.ch, Grundeinstellung «Apache mit PHP»). Beim ersten Versuch fehlte
`www/`: Der Upload lief fehlerfrei durch, landete aber in einem Ordner, den
niemand ausliefert. Die Abgleichsdatei `.ftp-deploy-sync-state.json` liegt im
Zielordner, die `.htaccess` sperrt sie für Besucher.

Den Workflow kann der normale GitHub-Zugang nicht ändern (fehlender Scope
`workflow`). Für Änderungen daran vorher `gh auth refresh -h github.com -s workflow`,
oder die Datei direkt auf github.com bearbeiten. Neu starten ohne Push:
Actions → «Website zu Hostpoint» → Run workflow.

GitHub Pages läuft parallel weiter, als Vorschau. Nach dem Livegang
abschalten (Settings → Pages), sonst steht die Seite doppelt im Netz.

## Gespräch vereinbaren

`gespraech.html` zeigt freie Zeiten und bucht sie, `termine.php` holt sie per
CalDAV aus dem Kalender «Gespräche» in Hostpoint Cloud Office
(`https://caldav.hostpoint.ch/caldav/Y2FsOi8vMC8xMDE/`). Kein fremder Dienst,
nichts eingebettet: «0 Cookies» und «Gehostet in der Schweiz» bleiben wahr.

**Bedienung, nur im Kalender:**
- Termin mit Titel **frei** = in diesem Fenster ist buchbar. Feste Zeiten als
  wiederkehrender Termin (wöchentlich, auch mit Ausnahmen).
- **Alles andere** im Kalender «Gespräche» blockiert, auch ganztägig
  («gesperrt») und die gebuchten Gespräche selbst.
- Ohne «frei» ist nichts buchbar. TimeTree (Familienkalender) hat keine
  Schnittstelle, darum gibt Adrian nur frei, was dort frei ist.

**Regeln** zuoberst in `termine.php`: 30 Minuten, 15 Minuten Puffer, ab morgen,
vier Wochen voraus. Ein Fenster 14–17 Uhr ergibt 14:00, 14:45, 15:30, 16:15.
Arten: Telefon oder Video über kMeet (Infomaniak, Schweiz): jede Buchung bekommt
den eigenen Raum `VIDEO_RAUM` + 16 Zufallszeichen, ohne Konto. Der Link steht in
der Bestätigung, im Kalendereintrag (Ort) und in Adrians Mail.

**Ablauf einer Buchung:** Zeit unter Sperre nochmals prüfen, Termin per PUT in
den Kalender, Mail an Adrian, Bestätigung an den Kunden (HTML, `termin.ics`,
Absage-Link mit HMAC). Ist der Kalender nicht erreichbar, geht der Wunsch als
Mail an Adrian („NICHT eingetragen“) und der Kunde sieht „ich bestätige per
Mail“. Absagen über `gespraech.html?absagen=…&t=…` löscht den Termin.

**Zugang:** `KALENDER_BENUTZER`/`KALENDER_PASSWORT` als Secrets, der Workflow
schreibt sie beim Upload in `kalender-zugang.php` (per `.htaccess` gesperrt).
Ohne Secrets zeigt die Seite Telefon und Mail statt Zeiten, ebenso auf GitHub
Pages und lokal.

**Lokal testen:** `termine.php` mit geänderter `KALENDER`-Adresse gegen einen
nachgebauten CalDAV-Server (`php -S` mit Router, der REPORT/PUT/GET/DELETE auf
Dateien abbildet), Mails über `sendmail_path` in Dateien.

## Messung und Banner (Google)

Gebaut, aber ausgeschaltet. `messung.js` liegt auf allen vier Seiten und tut
nichts, solange oben in `MESSUNG` keine Kennung steht: kein Banner, keine
Verbindung zu Google, die Datenschutzerklärung zeigt die Fassung ohne Messung.
Einschalten, wenn die Google-Kampagne startet.

**Entschieden (22.10.2026):**

- Google zuerst, Meta später.
- Google laedt erst nach „Akzeptieren“. Vorher geht keine einzige Anfrage an
  Google. Bewusst nicht der erweiterte Einwilligungsmodus, in dem Google auch
  ohne Zustimmung Signale ohne Cookies schickt und fehlende Conversions schätzt.
- Banner als Leiste unten. Titel „Cookies“, Text „Ich nutze Cookies, um zu
  sehen, ob meine Werbung wirkt. Nur wenn du zustimmst.“, Link zum Datenschutz.
  Google steht nicht im Banner, aber in der Datenschutzerklärung.
- Knöpfe „Ablehnen“ und „Akzeptieren“, gleich gross.
- Die Wahl liegt zwölf Monate im Browser (localStorage, kein Cookie). Link
  „Cookies“ im Fuss jeder Seite öffnet den Banner wieder. Wer nachträglich
  ablehnt, dessen Google-Cookies werden gelöscht.

**Gezählt wird** (im Kostenrechner, über `messen()` aus `messung.js`):

| Ereignis | Wann | Rolle |
|---|---|---|
| `page_view` | jeder Seitenaufruf, kommt vom Google-Tag | automatisch |
| `rechner_start` | erste Antwort im Rechner | Nebenziel |
| `rechner_frage` mit `nr` | jede Frage beim ersten Beantworten | Trichter: wo steigen Leute aus |
| `anfrage_gesendet` | erst wenn `anfrage.php` den Eingang bestätigt | Hauptziel, Conversion |
| `kontakt_klick` mit `weg` | Klick auf Mail- oder Telefonlink, auf allen Seiten | Nebenziel |
| `termin_gebucht` mit `art` | erst wenn `termine.php` die Buchung bestätigt | Hauptziel, Conversion wie `anfrage_gesendet` |

Formularinhalte gehen nie an Google, auch nicht die Antworten im Rechner,
nur die Nummer der Frage. Die Mail-Rückfalllösung zählt nicht als Anfrage,
weil niemand weiss, ob die Mail abgeschickt wurde.

**Einschalten:**

1. In Google Analytics eine Property anlegen, Mess-ID `G-…` in
   `MESSUNG.analytics` eintragen. Unter Datenaufbewahrung 14 Monate
   einstellen, so steht es in der Datenschutzerklärung.
2. `anfrage_gesendet` in Analytics als Schlüsselereignis markieren.
3. In Google Ads die Conversion-ID `AW-…` in `MESSUNG.ads` eintragen. Die
   Conversion „Anfrage gesendet“ entweder aus Analytics importieren (dann
   `adsLabel` leer lassen) oder in Ads anlegen und das Label in
   `MESSUNG.adsLabel` eintragen. Nicht beides, sonst zählt sie doppelt.
4. Testen: Seite öffnen, akzeptieren, Rechner durchspielen, eine Anfrage
   schicken und in Analytics unter Echtzeit nachsehen.

Zum Ansehen ohne Kennung: `?banner-test` an die Adresse hängen. Dann
erscheint der Banner, geladen wird nichts.

Die Datenschutzerklärung hat dafür zwei Fassungen (`data-messung="aus"` und
`"an"`). `messung.js` schaltet um, sobald eine Kennung steht. Ändert Google
etwas an Cookies, Anbieter oder Datenübermittlung, dort nachführen.

## Vor dem Livegang

Die SEO-Grundlage steht, aber sie ist bewusst stillgelegt. Alle Schalter sind
im Code mit `LIVEGANG` markiert — `grep -rn LIVEGANG .` findet sie alle.

1. **noindex entfernen** in `index.html`, `kostenrechner.html` und `gespraech.html`. Solange die
   Zeile steht, bleibt die Seite aus dem Suchindex. Das ist Absicht: mit
   Platzhalterpreisen und toten Links waere eine Indexierung schaedlich, weil
   Google die Entwurfsfassung behaelt und sie spaeter gegen die echte Domain
   antreten laesst.
2. **Domain einschalten.** `formateins.ch` ist registriert (22.10.2026, bei
   Hostpoint). Adresse der Seite ist `https://www.formateins.ch`; sie steht
   schon in `sitemap.xml`, im auskommentierten Block in `robots.txt`, im
   JSON-LD und in `canonical`/`og:url`. Beim Livegang: bei Hostpoint ein
   Webhosting mit der Domain verknüpfen, die Adresse ohne `www` auf die mit
   `www` umleiten, `canonical` und `og:url` einkommentieren und `og:image`
   auf die volle Adresse setzen (`https://www.formateins.ch/teilen.png`).
   Offen: `format1.ch` als Umleitung.
3. **Versand testen.** `EMPFAENGER` und `ABSENDER` in `anfrage.php` sowie
   `VERSAND.mail` in `kostenrechner.html` stehen auf `adrian@formateins.ch`.
   Nach dem Hochladen auf Hostpoint eine Testanfrage abschicken und prüfen,
   ob sie ankommt, auch im Spam-Ordner. Falls ein eigener Absender gewünscht
   ist (`anfrage@formateins.ch`, zum Filtern), muss er bei Hostpoint als
   Postfach existieren.
4. **Kontaktangaben** stehen: pzu consulting gmbh, Hochgasse 7, 4632
   Trimbach (Sitz laut Handelsregister), `adrian@formateins.ch`,
   079 686 84 86. So in Impressum, Fuss und JSON-LD. Genau dieselben Angaben im
   Google-Unternehmensprofil und in den Verzeichnissen verwenden, im Profil
   als Einzugsgebiet ohne sichtbare Adresse.
5. **Google-Unternehmensprofil anlegen.** Bei lokalen Suchen dominiert das
   Kartenpaket ueber den organischen Treffern, und auch KI-Assistenten stützen
   Empfehlungen wie „Webdesigner in Olten“ stark auf Profile und Bewertungen.
   Die ersten Kunden um eine Bewertung bitten. Kostet nichts.
6. **Bei Google und Bing anmelden.** Google Search Console und Bing Webmaster
   Tools, jeweils die Sitemap einreichen. Bing geht oft vergessen, dabei stützt
   sich die Websuche von ChatGPT unter anderem darauf.
7. **Branchenverzeichnisse:** local.ch und search.ch. Überall genau derselbe
   Name, dieselbe Adresse, dieselbe Telefonnummer wie auf der Seite. Danach
   die Einträge und das Google-Profil im JSON-LD unter `sameAs` eintragen
   (Hinweis steht über dem JSON-LD in `index.html`).
8. **Vermerk auf Kundenseiten**, zum Beispiel „Website: formateins“ mit Link im
   Fuss, nur mit Einverständnis des Kunden. Jede Kundenseite wird so zu einer
   Erwähnung, für Suchmaschinen wie für KI. Bei Johann und GuardOpS zuerst.

Bewusst weggelassen: `llms.txt`. Kein grosser KI-Anbieter nutzt die Datei
nachweislich (Stand Oktober 2026).

Was bereits liegt: `favicon.png`, `apple-touch-icon.png`, `teilen.png` (Open-Graph-Bild 1200x630),
`robots.txt`, `sitemap.xml`, JSON-LD-Geruest, `lang="de-CH"`, Open Graph und
Twitter Cards auf beiden Seiten.

**FAQ als strukturierte Daten.** Die Fragen auf beiden Seiten stehen
zusätzlich als `FAQPage` im Code, damit Suchmaschinen und KI-Assistenten sie
als Frage und Antwort lesen. Nach jeder Änderung an den Fragen ausführen:

```bash
python3 quellen/faq-daten.py
```

## Logo

Wortmarke ohne Zeichen: „format“ in Schibsted Grotesk 700, „eins“ in Playfair
Display 900 und Signalorange — dieselben zwei Stimmen wie in den Ueberschriften.
Im HTML als `<span class="s">format</span><span class="p">eins</span>` in
`.logo`. Die verworfenen Varianten liegen in `archiv/logo-varianten.html`.

## Markenpaket

`quellen/marke/` baut ein ZIP mit allem, was jemand ausserhalb der Website
braucht: Wortmarke als SVG (Buchstaben als Pfade, keine Schrift noetig) und PNG,
Signet „fe“, Favicons mit Manifest, Profilbild, LinkedIn-Banner, Titelbild fuer
das Google-Profil, die CSS-Variablen und das Stylesheet als PDF (Logo, Farben mit
HEX/RGB/CMYK und Kontrast, Schriften, Regeln).

- `umriss.py` setzt Wortmarke und Signet mit HarfBuzz aus den Schriften in
  `schriften/`, mit derselben Laufweite wie `.logo` im CSS. Nachgeprueft:
  deckungsgleich mit der Darstellung im Browser.
- `bauen.py` erzeugt SVGs und Vorlagen, `raster.mjs` rendert sie mit Chrome zu
  PNG und PDF. Farben stehen in `bauen.py` oben und muessen zu `:root` in
  `index.html` passen. `web/formateins.css` wird direkt aus `index.html` gezogen.
- CMYK sind Richtwerte ueber das macOS-Profil „Generic CMYK“.
- Die Schriftdateien liegen bewusst nicht im Paket: Playfair Display hat einen
  reservierten Namen, eine beschnittene Fassung duerfte so nicht weitergegeben
  werden. Das Stylesheet verweist auf Google Fonts.

```bash
python3 -m venv /tmp/marke-venv
/tmp/marke-venv/bin/pip install "fonttools[woff]" uharfbuzz skia-pathops pillow
/tmp/marke-venv/bin/python quellen/marke/bauen.py ~/Downloads/formateins-marke.zip
```

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
