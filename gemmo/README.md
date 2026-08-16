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

## Die Karte ist das Objekt

Nicht eine Seite mit Abschnitten, sondern ein Stück Papier mit zwei Seiten,
das man umdreht. Tippen dreht, der Knopf darunter tut dasselbe und ist der Weg
für die Tastatur. Nachgebaut sind Papierton, die eingerückte olivgrüne Linie,
Knospe und Namen unten links auf der Vorderseite, grüne Versalien und
laufender Indikationstext auf der Rückseite.

Drei Abweichungen vom Druck, alle absichtlich:

- **Das Grün ist eine Spur dunkler.** Der Originalton (rund `#7f9f38`) kommt
  auf dem hellen Papier nur auf 3.4:1 und ist in den kleinen Versalien schwer
  zu lesen. `#64802d` liegt bei 4.9:1 und ist danebengehalten nicht zu
  unterscheiden.
- **Nachts wird das Papier zurückgenommen** von Weiss auf ein warmes
  Gebrochen-Weiss. Eine reinweisse Fläche auf fast schwarzem Grund blendet.
  Die Tinte wandert mit, der Kontrast innerhalb der Karte bleibt gleich.
- **Die Karte hält ihr Format**, solange die Rückseite kürzer ist als hoch
  (1:1.5) — sonst wäre eine Karte mit drei Indikationen ein Zettel. Wird der
  Text länger, wächst sie darüber hinaus, und die Vorderseite wächst mit.
  Auf echtem Karton ist die Vorderseite ja auch nicht kürzer, sie hat nur
  mehr Luft.

Die Karte hat ihre eigenen Farben, getrennt vom Rest der App: sie ist ein
Gegenstand aus Papier und behält ihr Aussehen, egal wie die App gerade
aussieht.

## Vier Wege

Vom Startbildschirm aus gewählt, weil es vier verschiedene Absichten gibt.

| Weg | Für die Absicht |
|---|---|
| **Karten** | Durchblättern, umdrehen, nichts wollen. Der Stapel liegt alphabetisch aus. |
| **Lernen** | Vollbild. Vorderseite raten, umdrehen, sich selbst bewerten. Karteikasten nach Leitner, drei Fächer. Leertaste dreht, 1 und 2 bewerten. |
| **Suchen** | Vom Symptom zur Knospe. Leeres Feld zeigt das ganze Register — das ist die blätterbare Form derselben Frage. |
| **Prüfung** | Abgefragt werden, drei Stufen, mit Punkten und Bestwert. |

**Lernen und Prüfung sind nicht dasselbe.** Beim Lernen bewertet man sich
selbst und darf grosszügig sein — das ist der Sinn eines Karteikastens. In der
Prüfung entscheidet die Antwort. Beides hat seinen Platz, aber nicht denselben.

Gesucht wird in Namen, Untertiteln, seelischen Aspekten, Indikationen und
Partnerpflanzen. Bei einem Treffer über die Indikation steht auf der Kachel,
welche es war — ohne das steht bei der Suche nach »Ekzem« eine Liste von
Pflanzennamen da, in der nichts das Wort Ekzem enthält, und man traut der
Suche nicht mehr.

Auf der Rückseite ist jede Indikation und jede Partnerpflanze anklickbar — die
Indikation führt in die Suche, die Partnerpflanze auf ihre eigene Karte, sobald
die abgeschrieben ist. Ein Klick darauf dreht die Karte *nicht* um.

## Die Prüfung

Zehn Fragen pro Durchgang, gebaut aus den Karten und nie aus dem Gedächtnis
der App. Gefragt wird nur aus vollständig erfassten Karten.

| Stufe | Form | Punkte |
|---|---|---|
| **Leicht** | Vier Antworten zur Wahl | 10 je Treffer |
| **Mittel** | Sechs Antworten, dazu Kombinationsfragen | 20 |
| **Schwer** | Kein Vorschlag, selber schreiben | 40 |

Dazu ein Serienbonus von 5 Punkten je Treffer in Folge, gedeckelt bei fünf —
sonst entscheidet eine einzige gute Strecke den ganzen Durchgang.

Sechs Fragetypen: lateinisch → deutsch, deutsch → lateinisch, Untertitel →
Knospe, Indikation → Knospe, Knospe → Indikation, und Kombination → Partner.
Jeder Typ sagt selbst, ob er mit dem vorhandenen Bestand möglich ist. Drei
Regeln halten die Fragen ehrlich:

- **Nur eindeutige Indikationen.** Steht »Asthma« auf drei Karten, taugt es
  nicht als Frage — die hätte drei richtige Antworten und die Punktzahl wäre
  Glückssache.
- **Ablenker dürfen nicht doch stimmen.** Bei »Welche Indikation steht auf
  Betula pendula?« werden die falschen Antworten gegen die echte Liste der
  Karte geprüft.
- **Keine doppelten Antworten.** *Ribes nigrum* steht auf fast jeder Karte als
  Partner; ohne Aussortieren stünde der Name mehrfach in derselben Auswahl.

Bei der Texteingabe entscheiden keine Tippfehler über die Punktzahl: verglichen
wird nach der Umlautfaltung, ein Buchstabe daneben zählt noch, ab acht Zeichen
zwei. »Sambucus nigre« ist gewusst, »Salbei« nicht.

Nach jeder Antwort steht da, worum es ging — sonst lernt man beim Prüfen
nichts, sondern erfährt nur, dass man es nicht wusste. Am Ende listet der
Rückblick alle zehn Fragen; jede Zeile führt auf ihre Karte.

Ein Durchgang wird nicht aufgehoben: wer die Prüfung verlässt, verliert ihn.
Ein halb gespielter Durchgang, den man Tage später wiederfindet, wäre kein
Ergebnis mehr.

## Karten erfassen

Alle Texte stehen in `karten.js`. Das Kopfkommentar dort beschreibt jedes Feld
und zeigt, wie eine Karte aussieht. Zwei Regeln:

1. **Wortgetreu abschreiben**, nicht zusammenfassen. Ein Nachschlagewerk, dem
   man beim Nachschlagen nicht traut, ist keins.
2. **Lücken leer lassen** (`""` oder `[]`), nichts erfinden. Die App schreibt
   dann »fehlt noch« an die Stelle und zählt oben mit, wie viele der 50 Karten
   vollständig sind.

## Die Knospenbilder

Sie werden aus den Fotos der Kartenvorderseiten geschnitten und liegen als JPG
in `gemmo/bilder/`, der Dateiname steht im Feld `bild`. Zwei Schritte, beide
in `gemmo/werkzeug/`:

```bash
python3 gemmo/werkzeug/karten_freistellen.py <fotos/> karten_gerade/
python3 gemmo/werkzeug/knospen_ausschneiden.py karten_gerade/ gemmo/bilder/
```

**Schritt 1** findet die Karte: sie ist auf jedem dieser Fotos die einzige
grosse helle Fläche auf dunklem Granit. Schwelle setzen, Sprenkel des Steins
wegerodieren, dann den Winkel suchen, unter dem das umschliessende Rechteck am
kleinsten wird — das ist die Drehung der Karte. Danach gerade drehen,
ausschneiden, hochkant stellen. Keine Zusammenhangsanalyse nötig; die Ausreisser
fallen über Perzentile heraus.

**Schritt 2** schneidet die Knospe aus. Sie ist auf der Karte das einzige grosse
warmfarbige Gebilde — die Namen sind fast schwarz, Untertitel und Randlinie
grün. Also: gesättigte warme Pixel suchen, Umriss nehmen, Papier ringsum stehen
lassen.

Zwei Feinheiten, die den Unterschied machen:

- **Die Namenszeile wird gesucht, nicht geschätzt.** Eine feste Grenze reicht
  nicht: bei der Grauerle läuft die Knospe tiefer als bei der Birke, und ein
  Wert, der beiden passt, schneidet der einen die Spitze ab oder holt der
  anderen die Kursivzeile ins Bild. Also wird die erste Zeile gesucht, in der
  gesetzter Text beginnt — dunkel und farblos, was die Knospe nie ist.
- **Der Papierton wird abgeglichen.** Im Foto kommt das Kartenpapier grau und
  leicht blaustichig an (rund 211,211,213). In der App liegt der Ausschnitt
  aber auf dem nachgebauten Papier (251,250,246) — ohne Abgleich sitzt ein
  grauer Kasten auf hellem Grund. Je Kanal ein eigener Faktor hebt die hellste
  Fläche auf den Papierton der App; das nimmt zugleich den Farbstich.

**Ohne Foto vom Rechner** geht es auch direkt in der App: Karte öffnen, auf das
leere Feld tippen, Kartenvorderseite fotografieren — auf dem Telefon öffnet das
die Kamera. Die Aufnahme wird auf 900 Pixel verkleinert und in der IndexedDB
des Browsers abgelegt. Nicht localStorage: fünfzig Aufnahmen sprengen dessen
fünf Megabyte, und zwar erst bei Karte vierzig, also genau dann, wenn die
Arbeit schon getan wäre.

Diese Aufnahmen bleiben auf dem Gerät — sie wandern nicht ins Repo und nicht auf
ein zweites Telefon. Beim Anzeigen gewinnt die eigene Aufnahme vor der Datei in
`bilder/`: sie ist die spätere, ausdrückliche Entscheidung.

## Stand

Sieben Karten liegen ab, sechs vollständig und mit Bild:

| Karte | fehlt noch |
|---|---|
| Alnus glutinosa / Schwarzerle (Roterle) | — |
| Alnus incana / Grauerle | — |
| Betula pendula / Hängebirke | — |
| Ilex aquifolium / Stechpalme | — |
| Sambucus nigra / Schwarzer Holunder | — |
| Viburnum lantana / Wolliger Schneeball | — |
| Acer campestre / Feldahorn | Untertitel, Bild |

Vom Feldahorn gibt es nur eine Aufnahme der Rückseite, aus dem allerersten
Foto. Solange die Vorderseite fehlt, fehlen Untertitel und Knospe.

Es fehlen 43 Karten. Pro Karte zwei Aufnahmen. **Reihenfolge: erst die
Vorderseite, dann die Rückseite** — daran werden die Paare erkannt, und daran
lässt sich die Zuordnung später auch belegen.

## Zwei Fallen, zweimal reingetappt

Beides sind unsichtbare Zeichen im Quelltext, und beide legen die App
vollständig lahm — sie zeigt dann keinen Fehler, sondern nichts.

- **Der Bereich der Kombinationszeichen** in der Umlautfaltung steht als
  `\u0300-\u036f` und nicht als die Zeichen selbst. Als Zeichen geschrieben
  sind sie im Editor unsichtbar, hängen sich beim Kopieren an den
  Nachbarbuchstaben — und wird die Datei einmal ohne Zeichensatzangabe
  ausgeliefert, liest der Browser den Bereich verkehrt herum ein. Dann wirft
  die Regex beim Laden, und der Bildschirm bleibt leer.
- **Die Klammern der Kursivstellen** im Fragetext stehen als `\u0001` und
  `\u0002`, aus demselben Grund.

Wer die Datei neu schreibt, prüft danach — dafür liegt die Gegenprobe in `gemmo/werkzeug/`:

```bash
python3 gemmo/werkzeug/gegenprobe.py gemmo/index.html gemmo/karten.js
```

Sie meldet jedes Zeichen aus U+0300–U+036F, U+0001 und U+0002, das buchstäblich
in der Datei steht, und endet mit einem Fehlercode. Zweimal ist das schon
passiert; beim dritten Mal soll es nicht wieder erst in der Vorschau auffallen.

## Eine Stelle zum Nachschauen

**Pinus mugo oder Pinus montana?** Die Ilex-Karte schreibt »Pinus mugo«, die
Birken-Karte »Pinus montana«. Botanisch dieselbe Bergkiefer, im Set aber zwei
Schreibweisen. Beide stehen so da, wie sie gedruckt sind; die App hält sie
deshalb für zwei Pflanzen. Sobald die Karte selbst auftaucht, zeigt sich,
welche Schreibweise das Set führt — dann kann die andere darauf zeigen.

## Geklärt

- **»Phytopharmas Pflanzenkinder«** steht auf jeder Rückseite gleich und
  liegt deshalb einmal in der App, nicht in jeder Karte.
- **Keine Numerierung** auf den Karten. Die Liste sortiert alphabetisch nach
  dem botanischen Namen.
- **Die Zuordnung der Rückseiten stimmt.** Zwei Paare belegten sich selbst,
  weil der Text die Pflanze nennt (»ist die Birke«, »Die Grauerle eignet
  sich«); bei *Alnus glutinosa* und *Ilex aquifolium* stützte sie sich nur auf
  die Reihenfolge im Chat. Die Dateinamen der Aufnahmen (IMG_2045 bis
  IMG_2056) geben die echte Aufnahmereihenfolge her: lückenlos Vorderseite,
  Rückseite, Vorderseite, Rückseite. Damit sind alle sechs Paare belegt.
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
