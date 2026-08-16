/* Die Karten. Eine private Abschrift des Kartensets »Gemmotherapie« von
   Chrischta Ganz und Louis Hutter (AT Verlag), nur zum eigenen Nachschlagen
   und Lernen.

   FELDER — alle Textfelder wortgetreu von der Karte abschreiben, nicht
   zusammenfassen und nicht umformulieren. Ein Nachschlagewerk, dem man beim
   Nachschlagen nicht traut, ist keins.

     lat          Botanischer Name, Vorderseite gross.       "Acer campestre"
     de           Deutscher Name, Vorderseite darunter.      "Feldahorn"
     untertitel   Die kursive Zeile unter den Namen, ohne
                  Anfuehrungszeichen.                        "Die ... Knospe"
     seelisch     Absatz unter SEELISCHE ASPEKTE, ganz.
     indikationen Liste unter INDIKATIONEN, an den Kommas
                  getrennt, jeder Eintrag wie gedruckt.      ["Gallensteine", ...]
     kombinationen Liste unter BEWAEHRTE KOMBINATIONEN. Jede
                  Zeile beginnt mit + Partnerpflanze, danach
                  folgt der Anlass. Beides getrennt ablegen:
                  partner ist der botanische Name allein,
                  damit die App auf die Partnerkarte
                  verlinken kann.                            {partner:"Olea europaea", bei:"beginnender Arteriosklerose"}
     bild         Dateiname des freigestellten Knospenbildes
                  in gemmo/bilder/, oder "" solange keins da
                  ist.                                       "acer-campestre.jpg"

   FEHLENDES bleibt leer ("" oder []). Die App zeigt dann »fehlt noch« an
   dieser Stelle und zaehlt oben mit, wie viele Karten vollstaendig sind.
   Nichts erfinden — eine erfundene Indikation waere hier schlimmer als eine
   fehlende.

   REIHENFOLGE spielt keine Rolle, die App sortiert selbst nach dem
   botanischen Namen. */

window.KARTEN = [

  {
    lat: "Acer campestre",
    de: "Feldahorn",
    untertitel: "",
    seelisch: "Der Feldahorn eignet sich für Menschen, die Bescheidenheit " +
      "nach außen hin leben, aber im Verborgenen hohe Erwartungen pflegen. " +
      "Hier wirkt der Feldahorn lösend, entspannend und unterstützend für " +
      "den Menschen.",
    indikationen: [
      "Gallenblasenentzündung",
      "Gallensteine",
      "Leberinsuffizienz",
      "Gürtelrose",
      "trockene Haut",
      "Ekzeme",
      "Hypercholesterinämie",
      "Thrombosenneigung",
      "Arteriosklerose",
      "harntreibend",
      "Nierengrieß",
      "stärkt das Immunsystem",
      "wirkt gegen Pilze und Viren"
    ],
    kombinationen: [
      { partner: "Olea europaea",      bei: "beginnender Arteriosklerose" },
      { partner: "Ficus carica",       bei: "Nervosität und psychosomatischen Beschwerden sowie Bulimie" },
      { partner: "Tilia tomentosa",    bei: "undefinierten Angststörungen (Phobien)" },
      { partner: "Juniperus communis", bei: "erhöhten Blutfettwerten" }
    ],
    bild: ""
  },

  {
    lat: "Sambucus nigra",
    de: "Schwarzer Holunder",
    untertitel: "Die zwischen Polaritäten vermittelnde Knospe",
    seelisch: "",
    indikationen: [],
    kombinationen: [],
    bild: ""
  }

];
