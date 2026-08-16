# Gemmotherapie — die 50 Knospen

Privates Nachschlagewerk zum Kartenset »Gemmotherapie. 50 Knospen in der
Naturheilkunde« von Chrischta Ganz und Louis Hutter (AT Verlag). Eine Abschrift
der eigenen Karten, zum Lernen und Nachschlagen auf dem Telefon. Nicht zur
Veröffentlichung, kein Ersatz für ärztlichen Rat.

## Ansehen

```bash
python3 -m http.server 8000
```

Dann `http://localhost:8000/gemmo/` öffnen. Auf dem Telefon: die Seite im
Browser öffnen und »Zum Home-Bildschirm«.

Keine Bibliothek, kein Build, keine Schrift von aussen — die App läuft ohne
Netz, sobald sie einmal geladen ist.

## Drei Wege

| Weg | Für die Frage |
|---|---|
| **Karten** | »Zeig mir Weissdorn.« Alphabetisch nach botanischem Namen. |
| **Register** | »Was hilft bei Ekzemen?« Fällt automatisch aus den Indikationen aller Karten heraus, wird nicht separat gepflegt. |
| **Lernen** | »Frag mich ab.« Karteikasten nach Leitner, drei Fächer, Stand im Browser gespeichert. |

Das Suchfeld sucht gleichzeitig in Namen, Untertiteln, seelischen Aspekten und
Indikationen. Bei einem Treffer über die Indikation steht in der Trefferzeile,
welche es war.

Die Partnerpflanzen unter »Bewährte Kombinationen« sind verlinkt: von *Acer
campestre* führt ein Klick direkt zu *Olea europaea*, sobald deren Karte
abgeschrieben ist.

## Karten erfassen

Alle Texte stehen in `karten.js`. Das Kopfkommentar dort beschreibt jedes Feld
und zeigt, wie eine Karte aussieht. Zwei Regeln:

1. **Wortgetreu abschreiben**, nicht zusammenfassen. Ein Nachschlagewerk, dem
   man beim Nachschlagen nicht traut, ist keins.
2. **Lücken leer lassen** (`""` oder `[]`), nichts erfinden. Die App schreibt
   dann »fehlt noch« an die Stelle und zählt oben mit, wie viele der 50 Karten
   vollständig sind.

Bilder der Knospen kommen als JPG nach `gemmo/bilder/`, der Dateiname ins Feld
`bild`. Freigestellt oder mit hellem Grund, mindestens 560 Pixel breit — das
Bild wird bis 280 Pixel breit gezeigt, doppelte Pixeldichte gerechnet.

## Stand

Zwei Karten sind angefangen, beide unvollständig — sie stammen aus dem einen
Foto, das bisher da ist:

- **Acer campestre / Feldahorn** — Rückseite vollständig, Untertitel und Bild fehlen.
- **Sambucus nigra / Schwarzer Holunder** — Vorderseite da, Rückseite fehlt ganz.

Es fehlen also 48 Karten und die Lücken der beiden. Zum Erfassen braucht es pro
Karte zwei Aufnahmen, Vorder- und Rückseite, als Paar erkennbar.

## Offen

- **Numerierung**: unklar, ob die Karten im Set eine Nummer tragen. Falls ja,
  kommt ein Feld `nr` dazu und die Liste sortiert danach statt alphabetisch.
- **»Phytopharmas Pflanzenkinder«** steht als Zeile über jeder Rückseite. Ob
  das auf allen Karten gleich ist, zeigt sich beim Abschreiben; falls ja, wandert
  es als feste Zeile in die App statt in jede Karte.
