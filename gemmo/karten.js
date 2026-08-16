/* Die Karten. Eine private Abschrift des Kartensets »Gemmotherapie« von
   Chrischta Ganz und Louis Hutter (AT Verlag), nur zum eigenen Nachschlagen
   und Lernen.

   FELDER — alle Textfelder wortgetreu von der Karte abschreiben, nicht
   zusammenfassen und nicht umformulieren. Ein Nachschlagewerk, dem man beim
   Nachschlagen nicht traut, ist keins.

     lat          Botanischer Name, Vorderseite gross.       "Acer campestre"
     de           Deutscher Name, Vorderseite darunter.      "Feldahorn"
     untertitel   Die gruene kursive Zeile darunter, ohne
                  Anfuehrungszeichen.                        "Die Knospe, die frei atmen lässt"
     seelisch     Absatz unter SEELISCHE ASPEKTE, ganz.
     indikationen Liste unter INDIKATIONEN, an den Kommas
                  getrennt, jeder Eintrag wie gedruckt.      ["Asthma", ...]
     kombinationen Liste unter BEWAEHRTE KOMBINATIONEN. Jede
                  Zeile beginnt mit + und nennt eine oder
                  mehrere Partnerpflanzen, danach folgt der
                  Anlass. partner ist deshalb immer eine
                  Liste, auch bei nur einem Namen — die App
                  verlinkt jeden Namen einzeln auf seine
                  Karte.                                     {partner:["Ribes nigrum","Fagus sylvatica"], bei:"Heuschnupfen"}
     bild         Dateiname des Knospenbildes in
                  gemmo/bilder/, oder "" solange keins da
                  ist.                                       "sambucus-nigra.jpg"

   NICHT hier ablegen: die Zeile »Phytopharmas Pflanzenkinder« und der Hinweis
   aufs Buch. Beides steht auf jeder Rueckseite gleich und deshalb einmal in
   der App statt fuenfzigmal hier.

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
      { partner: ["Olea europaea"],      bei: "beginnender Arteriosklerose" },
      { partner: ["Ficus carica"],       bei: "Nervosität und psychosomatischen Beschwerden sowie Bulimie" },
      { partner: ["Tilia tomentosa"],    bei: "undefinierten Angststörungen (Phobien)" },
      { partner: ["Juniperus communis"], bei: "erhöhten Blutfettwerten" }
    ],
    bild: ""
  },

  {
    lat: "Sambucus nigra",
    de: "Schwarzer Holunder",
    untertitel: "Die zwischen Polaritäten vermittelnde Knospe",
    seelisch: "Die Schwarzer-Holunder-Knospe eignet sich für Menschen, die " +
      "in einem ausgeprägten Schwarz-Weiß-Denken »gefangen« sind. Sie hilft, " +
      "das Denken allmählich in ein »Sowohl-als-auch« zu überführen. Die " +
      "Holunderknospe ist breit einsetzbar!",
    indikationen: [
      "Verstopfung",
      "Muskelrheumatismus",
      "Bronchitis",
      "Husten",
      "Allergien",
      "Heuschnupfen",
      "Asthma",
      "Akne",
      "Psoriasis",
      "Neurodermitis",
      "immunstimulierend",
      "wundheilungsfördernd",
      "grippale Infekte",
      "Frühjahrs- und Herbstkuren"
    ],
    kombinationen: [
      { partner: ["Ribes nigrum"],                                        bei: "chronischen Ischiasbeschwerden" },
      { partner: ["Ribes nigrum", "Fagus sylvatica", "Carpinus betulus"], bei: "Heuschnupfen" },
      { partner: ["Juniperus communis", "Ribes nigrum"],                  bei: "Gicht" },
      { partner: ["Tilia tomentosa"],                                     bei: "Schwarz-Weiß-Denken" }
    ],
    bild: ""
  },

  {
    lat: "Viburnum lantana",
    de: "Wolliger Schneeball",
    untertitel: "Die Knospe, die frei atmen lässt",
    seelisch: "Hilft Spannungen zu lösen, vor allem, wenn diese mit " +
      "Symptomen auf der Haut und im Atemtrakt einhergehen.",
    indikationen: [
      "Krampfartige Störungen des Respirationstraktes",
      "Asthma",
      "Atemnot mit psychischem Hintergrund",
      "krampfartiger Husten",
      "atopische Ekzeme",
      "Neurodermitis",
      "Morbus Basedow",
      "Menstruationsbeschwerden durch seelische Konflikte",
      "alle stressbedingten Atembeschwerden",
      "stärkt die Lungenfunktion"
    ],
    kombinationen: [
      { partner: ["Ribes nigrum", "Rosmarinus officinalis"], bei: "Asthma" },
      { partner: ["Corylus avellana"],                       bei: "chronischer Bronchitis, Lungenemphysem" },
      { partner: ["Cedrus libani"],                          bei: "trockenen Formen des atopischen Ekzems" },
      { partner: ["Cornus sanguinea"],                       bei: "Schilddrüsenüberfunktion" },
      { partner: ["Tilia tomentosa"],                        bei: "Neigung zu Hyperventilation" }
    ],
    bild: ""
  }

];
