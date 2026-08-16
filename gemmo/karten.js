/* Die Karten. Eine private Abschrift des Kartensets »Gemmotherapie« von
   Chrischta Ganz und Louis Hutter (AT Verlag), nur zum eigenen Nachschlagen
   und Lernen.

   FELDER — alle Textfelder wortgetreu von der Karte abschreiben, nicht
   zusammenfassen und nicht umformulieren. Ein Nachschlagewerk, dem man beim
   Nachschlagen nicht traut, ist keins.

     lat          Botanischer Name, Vorderseite gross.       "Alnus glutinosa"
     de           Deutscher Name, Vorderseite darunter, mit
                  Klammerzusatz falls gedruckt.              "Schwarzerle (Roterle)"
     untertitel   Die gruene kursive Zeile darunter, ohne
                  Anfuehrungszeichen.                        "Die Knospe, die frei atmen lässt"
     seelisch     Absatz unter SEELISCHE ASPEKTE, ganz. Auch
                  ein »Vorsicht:«-Satz gehoert hier hinein
                  und wird nicht ausgelagert — die App hebt
                  ihn beim Anzeigen selbst hervor.
     indikationen Liste unter INDIKATIONEN, siehe TRENNEN.
     kombinationen Liste unter BEWAEHRTE KOMBINATIONEN:
                  partner      die botanischen Namen, immer
                               als Liste, auch bei einem
                  verbindung   nur falls die Karte etwas
                               anderes als »+« zwischen die
                               Namen setzt ("im Wechsel mit")
                  anlass       der Rest der Zeile, wortgetreu
                               mitsamt seinem Bindewort —
                               "bei Gicht", "beim prämen-
                               struellen Syndrom", "zur
                               Lymphreinigung", ", um die
                               Cortisolproduktion anzuregen".
                               Das Bindewort wechselt von
                               Zeile zu Zeile, deshalb steht
                               es im Text und nicht im Code.
     bild         Dateiname des Knospenbildes in
                  gemmo/bilder/, oder "" solange keins da
                  ist.                                       "alnus-glutinosa.jpg"

   TRENNEN der Indikationen: an den Kommas, aber nur dort, wo wirklich eine
   neue Indikation beginnt. Zusammen bleibt, was der Satz zusammenhaelt:

     "Lungen-, Knochen- und Knochenhautentzündung"   eine Aufzaehlung mit
                                                     Ergaenzungsstrich
     "Gelenk-, Rückenschmerzen"                      dito
     "regt Leber, Milz und Pankreas an"              ein Satz mit Kommas

   Auseinandergerissen ergaebe das Eintraege wie »Lungen-« oder »Milz und
   Pankreas an«, und das Register waere Schrott. Die Suche findet trotzdem:
   sie sucht in Teilzeichenketten, »Lungen« steht in »Lungen-«.

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
      { partner: ["Olea europaea"],      anlass: "bei beginnender Arteriosklerose" },
      { partner: ["Ficus carica"],       anlass: "bei Nervosität und psychosomatischen Beschwerden sowie Bulimie" },
      { partner: ["Tilia tomentosa"],    anlass: "bei undefinierten Angststörungen (Phobien)" },
      { partner: ["Juniperus communis"], anlass: "bei erhöhten Blutfettwerten" }
    ],
    bild: ""
  },

  {
    lat: "Alnus glutinosa",
    de: "Schwarzerle (Roterle)",
    untertitel: "Die Gewebe durchlüftende, lichtende Knospe",
    seelisch: "Öffnet den Zugang zur Anderswelt. Hilft, verborgene, " +
      "verdrängte Persönlichkeitsanteile ins Bewusstsein zu heben und " +
      "Gefühle zu reintegrieren.",
    indikationen: [
      "Entzündungen der Arterien und Venen",
      "Harnsäureüberschuss",
      "rheumatische Beschwerden",
      "Lungen-, Knochen- und Knochenhautentzündung",
      "Nasennebenhöhlen-, Stirnhöhlenentzündung",
      "Morbus Crohn",
      "Colitis ulcerosa",
      "Blasen-, Harnröhren-, Scheiden- und Prostataentzündung"
    ],
    kombinationen: [
      { partner: ["Rosa canina", "Ribes nigrum"], verbindung: "im Wechsel mit",
        anlass: "bei Migräne" },
      { partner: ["Ribes nigrum"],                anlass: "bei allergischem Asthma und bei Nesselsucht" },
      { partner: ["Viburnum lantana"],            anlass: "bei spastischer Bronchitis/Asthma bronchiale" },
      { partner: ["Vitis vinifera", "Ficus carica"], anlass: "bei chronisch entzündlichen Darmerkrankungen" }
    ],
    bild: "alnus-glutinosa.jpg"
  },

  {
    lat: "Alnus incana",
    de: "Grauerle",
    untertitel: "Die Knospe bei persönlichen Niederlagen",
    seelisch: "Die Grauerle eignet sich zur allgemeinen Vitalisierung, vor " +
      "allem nach erfolgten Niederlagen.",
    indikationen: [
      "Gebärmuttermyome",
      "Eierstockzysten",
      "Gebärmutter- und Scheidensenkung",
      "Scheidenentzündung",
      "Scheidenausfluss",
      "Harnwegsentzündung",
      "Knoten in der Brust",
      "Krampfadern",
      "Arterienentzündungen",
      "Neigung zu Blutgerinnseln",
      "akute Thrombose",
      "Leberzirrhose",
      "chronisch entzündliche Darmerkrankungen",
      "Osteoporose",
      "stärkt die Beckenbodenmuskulatur"
    ],
    kombinationen: [
      { partner: ["Vaccinium vitis-idaea", "Ribes nigrum"], anlass: "bei Zysten und Myomen im weiblichen Unterleib" },
      { partner: ["Fraxinus excelsior"],                    anlass: "bei Senkungen der Unterleibsorgane (Blase, Scheide, Gebärmutter)" },
      { partner: ["Ficus carica", "Juglans regia"],         anlass: "bei Morbus Crohn" },
      { partner: ["Aesculus hippocastanum"],                anlass: "bei Hämorrhoiden" }
    ],
    bild: "alnus-incana.jpg"
  },

  {
    lat: "Betula pendula",
    de: "Hängebirke",
    untertitel: "Die reinigende, verjüngende Knospe",
    seelisch: "In Mythologie und Volksglaube ist die Birke Inbegriff des " +
      "Weiblichen.",
    indikationen: [
      "Gelenk-, Rückenschmerzen",
      "Osteoporose",
      "Rheumatismus",
      "Arthrose",
      "Übersäuerung",
      "Wachstumsverzögerung",
      "Frühjahrsmüdigkeit",
      "Nierenschwäche",
      "Harnwegs-, Blasen-, Leber-, Lymphknotenentzündungen",
      "Cellulite",
      "Nervenschwäche",
      "blutreinigend",
      "regt Leber, Milz und Pankreas an",
      "normalisiert Fettstoffwechsel"
    ],
    kombinationen: [
      { partner: ["Abies alba"],                          anlass: "bei Osteoporose" },
      { partner: ["Calluna vulgaris"],                    anlass: "bei Übersäuerung" },
      { partner: ["Fraxinus excelsior", "Ribes nigrum"],  anlass: "bei Gicht" },
      { partner: ["Pinus montana", "Ribes nigrum"],       anlass: "bei Kniearthrose, Hüftarthrose" },
      { partner: ["Rosmarinus officinalis"],              anlass: "zur Leberzellentgiftung" },
      { partner: ["Juniperus communis"],                  anlass: "zur Ausleitung über die Nieren" }
    ],
    bild: "betula-pendula.jpg"
  },

  {
    lat: "Ilex aquifolium",
    de: "Stechpalme",
    untertitel: "Die Reaktionsbereitschaft fördernde Knospe",
    seelisch: "Ist geeignet für Menschen, die ständig auf der Überholspur " +
      "leben mit dem Gefühl, unbedingt besser als alle anderen sein zu " +
      "müssen. Vorsicht: Nicht während der Schwangerschaft und Stillzeit " +
      "einnehmen!",
    indikationen: [
      "Augenheilmittel",
      "grauer Star",
      "Ohrgeräusche",
      "prämenstruelles Syndrom",
      "Brustentzündung",
      "gutartige Knoten in der Brust",
      "krampflösend",
      "Krämpfe des Verdauungstrakts",
      "Keuchhusten",
      "chronischer Husten",
      "Gicht"
    ],
    kombinationen: [
      { partner: ["Betula pendula", "Rubus idaeus"], anlass: "beim prämenstruellen Syndrom" },
      { partner: ["Juglans regia"],                  anlass: "zur Lymphreinigung" },
      { partner: ["Ribes nigrum"],                   anlass: ", um die Cortisolproduktion anzuregen" },
      { partner: ["Quercus robur"],                  anlass: "bei Erschöpfung" },
      { partner: ["Betula pendula", "Pinus mugo"],   anlass: "bei chronischem Rheumatismus" },
      { partner: ["Alnus glutinosa"],                anlass: "bei Allergien" }
    ],
    bild: "ilex-aquifolium.jpg"
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
      { partner: ["Ribes nigrum"],                                        anlass: "bei chronischen Ischiasbeschwerden" },
      { partner: ["Ribes nigrum", "Fagus sylvatica", "Carpinus betulus"], anlass: "bei Heuschnupfen" },
      { partner: ["Juniperus communis", "Ribes nigrum"],                  anlass: "bei Gicht" },
      { partner: ["Tilia tomentosa"],                                     anlass: "bei Schwarz-Weiß-Denken" }
    ],
    bild: "sambucus-nigra.jpg"
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
      { partner: ["Ribes nigrum", "Rosmarinus officinalis"], anlass: "bei Asthma" },
      { partner: ["Corylus avellana"],                       anlass: "bei chronischer Bronchitis, Lungenemphysem" },
      { partner: ["Cedrus libani"],                          anlass: "bei trockenen Formen des atopischen Ekzems" },
      { partner: ["Cornus sanguinea"],                       anlass: "bei Schilddrüsenüberfunktion" },
      { partner: ["Tilia tomentosa"],                        anlass: "bei Neigung zu Hyperventilation" }
    ],
    bild: "viburnum-lantana.jpg"
  }

];
