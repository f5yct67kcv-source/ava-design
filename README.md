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

  Der Platz ist vorbereitet. Foto als `foto.jpg` ins Projekt legen, dann in
  `index.html` diese Zeile

  ```html
  <div class="rund">[ Foto folgt ]</div>
  ```

  ersetzen durch

  ```html
  <div class="rund"><img src="foto.jpg" alt="Adrian von Arb"></div>
  ```

  Mehr braucht es nicht: der Kreis beschneidet das Bild selbst, hochkant wie
  quer wie quadratisch — alle drei Fälle geprüft. Mindestens 400 Pixel
  Kantenlänge, sonst wird es unscharf: der Kreis wird bis 190 Pixel breit, und
  auf Bildschirmen mit doppelter Pixeldichte sind das 380 echte Bildpunkte.

- **Zweites Foto** fehlt, im Abschnitt „Wer das macht". Hochformat 4:5, gleiche
  Mechanik: `[ Portrait folgt ]` in `<div class="portraetplatz">` ersetzen durch
  `<img src="portrait.jpg" alt="Adrian von Arb">`. Der Platz wird bis 340 Pixel
  breit, also mindestens 680 Pixel Breite liefern.

- **Referenzen** fehlen noch, bewusst. Der Hinweis auf die Hochzeitsfilme ist
  raus, damit steht aktuell kein Beleg auf der Seite. Mehrere Kundenprojekte
  sind kurz vor Abschluss; die Seite geht ohnehin erst danach online. Sobald
  drei Projekte live sind, kommt ein Referenzabschnitt zwischen „Nicht jede
  Website braucht alles" und den Preisabschnitt.

  **Währenddessen einsammeln, nicht nachher:** pro Projekt eine schriftliche
  Freigabe des Kunden, ein bis zwei Bildschirmaufnahmen, und einen Satz dazu,
  was die Seite für den Betrieb geändert hat. Danach fragen zu müssen kostet
  Wochen und liefert schlechtere Antworten.
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
