# AVA Design — Website

Entwurf der eigenen Website. Webdesign für Selbstständige und kleine Unternehmen
in Olten und der Region.

Statisches HTML, kein Build-Schritt, keine Abhängigkeiten ausser zwei Schriften
von Google Fonts.

## Dateien

| Datei | Inhalt |
|---|---|
| `index.html` | Startseite |
| `kostenrechner.html` | Kostenrechner, sechs Fragen, Preisrahmen vor dem Formular |
| `archiv/` | Frühere Entwurfsrichtungen, nicht mehr aktuell |

## Noch offen

- **Preise** sind Platzhalter. Die Werte stehen im Skript von `kostenrechner.html`
  in den Objekten `grund` und `zuschlag`. Solange sie nicht kalibriert sind,
  weist ein Hinweis neben der Zahl darauf hin.
- **Foto** fehlt. Quadratisch, Tageslicht, Blick in die Kamera. Kommt in den
  runden Platzhalter im ersten Bild.
- **Markenname** ist provisorisch AVA.
- **Kontaktweg** fehlt: keine E-Mail-Adresse, keine Telefonnummer hinterlegt.
- **Impressum und Datenschutz** sind leere Links. Die Datenschutzerklärung ist
  Pflicht, sobald der Rechner scharf geschaltet wird — er erhebt Name, E-Mail
  und Telefonnummer.

## Lokal ansehen

```bash
python3 -m http.server 8000
```

Dann `http://localhost:8000` öffnen.
