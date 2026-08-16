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

Sieben Karten liegen ab, sechs davon vollständig:

| Karte | fehlt noch |
|---|---|
| Alnus glutinosa / Schwarzerle (Roterle) | Bild |
| Alnus incana / Grauerle | Bild |
| Betula pendula / Hängebirke | Bild |
| Ilex aquifolium / Stechpalme | Bild |
| Sambucus nigra / Schwarzer Holunder | Bild |
| Viburnum lantana / Wolliger Schneeball | Bild |
| Acer campestre / Feldahorn | Untertitel, Bild |

Es fehlen 43 Karten. Pro Karte zwei Aufnahmen, Rückseite und Vorderseite,
in dieser Reihenfolge — daran werden die Paare erkannt.

`gemmo/bilder/` ist leer. Die Knospenbilder müssen als Dateien ins Projekt
gelegt werden; aus einem Chatbild entsteht keine.

## Zwei Stellen zum Nachschauen

- **Zuordnung ohne Selbstbeleg.** Die Rückseiten von *Betula pendula* und
  *Alnus incana* nennen ihre Pflanze im Text (»ist die Birke«, »Die Grauerle
  eignet sich«) und belegen ihre Zuordnung damit selbst. Die von *Alnus
  glutinosa* und *Ilex aquifolium* tun das nicht — dort stützt sich die
  Paarung allein auf die Reihenfolge der Fotos. Beim nächsten Durchblättern
  kurz prüfen.
- **Pinus mugo oder Pinus montana?** Die Ilex-Karte schreibt »Pinus mugo«, die
  Birken-Karte »Pinus montana«. Botanisch dieselbe Bergkiefer, im Set aber
  zwei Schreibweisen. Beide stehen so da, wie sie gedruckt sind; die App hält
  sie deshalb für zwei Pflanzen. Sobald die Karte selbst auftaucht, zeigt sich,
  welche Schreibweise das Set führt — dann kann die andere darauf zeigen.

## Geklärt

- **»Phytopharmas Pflanzenkinder«** steht auf jeder Rückseite gleich und
  liegt deshalb einmal in der App, nicht in jeder Karte.
- **Keine Numerierung** auf den Karten. Die Liste sortiert alphabetisch nach
  dem botanischen Namen.
- **Mehrere Partner pro Kombination** kommen vor (»Ribes nigrum + Fagus
  sylvatica + Carpinus betulus bei Heuschnupfen«). `partner` ist deshalb
  immer eine Liste, jeder Name wird einzeln verlinkt.
- **Nicht jede Verbindung ist ein Plus.** »Rosa canina im Wechsel mit Ribes
  nigrum« heisst abwechselnd, nicht zusammen. Dafür gibt es das Feld
  `verbindung`.
- **Das Bindewort vor dem Anlass wechselt** — »bei Gicht«, »beim
  prämenstruellen Syndrom«, »zur Lymphreinigung«, », um die Cortisolproduktion
  anzuregen«. Es steht deshalb im Text (`anlass`) und nicht im Code.
- **Warnsätze** stehen mitten in den seelischen Aspekten (»Vorsicht: Nicht
  während der Schwangerschaft und Stillzeit einnehmen!«). Sie bleiben im
  Wortlaut und an ihrer Stelle; die App setzt sie beim Anzeigen farbig ab.
