# Arbeitsweise in diesem Projekt

## Vor jedem Push

An `main` arbeiten mehrere Sitzungen parallel. Deshalb vor **jedem** Push:

```
git fetch origin main
git log --oneline main..origin/main    # leer = main steht noch, wo du sie verlassen hast
```

Hat sich `main` bewegt: eigene Commits mit `git rebase origin/main` daraufsetzen,
nicht mergen. Ein Merge-Commit fuer eine Handvoll Zeilen macht die Geschichte
unleserlich. Danach den Diff des fremden Commits anschauen (`git show --stat`) —
beruehrt er dieselben Dateien, die eigene Aenderung noch einmal pruefen.

Nie mit `--force` auf `main`. Auf dem eigenen Arbeitsbranch ist `--force` in
Ordnung, dort arbeitet sonst niemand.

## Entwuerfe bleiben auf der Testseite

Die Seite ist live. Entwuerfe gehen deshalb **nie** in `index.html` und die
anderen ausgelieferten Dateien, sondern in eigene Dateien mit dem Praefix
`entwurf`:

```
entwurf.html            Startseite im Entwurf
entwurf-auftritt.html   usw.
```

Der Workflow schliesst alles aus, was `entwurf` am Anfang hat, auch Bilder
(`entwurf-markenblatt.webp`). Selbst wenn
der Entwurf versehentlich auf `main` landet, steht er damit nicht auf
formateins.ch. Erst wenn Adrian den Entwurf abnimmt, wandert der Inhalt in die
echten Dateien und die Entwurfsdateien verschwinden.

Angeschaut wird der Entwurf ueber GitHub Pages (Vorschau, siehe README) oder
ueber Bilder aus dem lokalen Server. Aus der Cloud-Sitzung ist weder
formateins.ch noch github.io erreichbar, gepruefte Aussagen ueber die Vorschau
sind von hier aus also nicht moeglich.

## Push auf main heisst live

Jeder Push auf `main` startet die Action «Website zu Hostpoint» und laedt die
Seite auf den Webserver (siehe README, Auslieferung auf Hostpoint). Also nur auf
`main` pushen, wenn Adrian es ausdruecklich sagt; sonst auf den Arbeitsbranch.

Nach dem Push den Lauf pruefen, nicht einfach annehmen, dass er durchlief:
Actions → «Website zu Hostpoint», oder ueber die GitHub-Tools den letzten Lauf
auf `main` abfragen. Ein Lauf kann auch mal Minuten in der Warteschlange stehen.

## Was von hier aus nicht geprueft werden kann

`www.formateins.ch` ist aus der Cloud-Sitzung nicht erreichbar, der Egress-Proxy
blockt den Host. Ein `curl` darauf liefert nichts und beweist nichts — weder
dass die Seite alt ist noch dass sie neu ist. Der Beleg fuer einen erfolgreichen
Livegang sind die Logs der Action («replacing index.html», «Sync complete»).

## Aenderungen zuerst zeigen

Gestaltung erst vorfuehren, dann bauen: Entwuerfe als Bild rendern (Playwright,
die Seite lokal ueber `python3 -m http.server` ausliefern) und Adrian zur Wahl
stellen. Nicht ungefragt die bestehende Gestaltung umbauen.
