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

Drei Karten sind erfasst, davon zwei vollständig:

- **Sambucus nigra / Schwarzer Holunder** — vollständig, Bild fehlt.
- **Viburnum lantana / Wolliger Schneeball** — vollständig, Bild fehlt.
- **Acer campestre / Feldahorn** — Rückseite steht, Untertitel und Bild fehlen.

Es fehlen also 47 Karten. Zum Erfassen braucht es pro Karte zwei Aufnahmen,
Vorder- und Rückseite, als Paar erkennbar.

Die Bilder der Knospen sind noch keine da: `gemmo/bilder/` ist leer. Sie
müssen als Dateien ins Projekt gelegt werden, aus einem Chatbild allein
entsteht keine.

## Geklärt

- **»Phytopharmas Pflanzenkinder«** steht auf jeder Rückseite gleich und
  liegt deshalb einmal in der App, nicht in jeder Karte.
- **Keine Numerierung** auf den Karten sichtbar. Die Liste sortiert
  alphabetisch nach dem botanischen Namen.
- **Mehrere Partner pro Kombination** kommen vor (»Ribes nigrum + Fagus
  sylvatica + Carpinus betulus bei Heuschnupfen«). `partner` ist deshalb
  immer eine Liste, jeder Name wird einzeln verlinkt.
