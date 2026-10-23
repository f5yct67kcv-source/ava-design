// Nimmt die Kommentare aus der Auslieferung. Sie sind fuer das Repository
// geschrieben, nicht fuer Besucher: wer den Quelltext der Seite oeffnet, soll
// den Bau sehen und nicht die Notizen dazu. Im Repository bleiben sie stehen,
// entfernt wird nur in dist/ — siehe .github/workflows/deploy-hostpoint.yml.
//
// Aufruf: node quellen/kommentare-entfernen.mjs dist
//
// Bewusst kein Minifizieren. Die Einrueckung, die kurzen zusammengezogenen
// Zeilen, die Reihenfolge der Eigenschaften: das ist die Handschrift der Seite,
// und ein Minifizierer schreibt sie um. Hier werden nur Zeichenbereiche
// herausgeschnitten, der Rest der Datei bleibt Zeichen fuer Zeichen gleich.
//
// Wo die Bereiche liegen, sagt bei JavaScript der Parser (acorn), nicht ein
// Suchmuster: ein "//" kann in einer Zeichenkette oder in einem regulaeren
// Ausdruck stehen, und dort darf nichts verschwinden.
//
// PHP bleibt unberuehrt — der Hoster fuehrt es aus, der Quelltext verlaesst den
// Server nie.

import { readdir, readFile, writeFile } from 'node:fs/promises';
import { join, extname } from 'node:path';
import { parse, tokenizer } from 'acorn';

// ---------------------------------------------------------------- ausschneiden

// Schneidet die gefundenen Bereiche heraus. Steht ein Kommentar allein auf der
// Zeile, geht die ganze Zeile mit, sonst bliebe eine Luecke aus Leerzeilen —
// genau die Spur, die wir nicht hinterlassen wollen. Steht er hinter Code, geht
// der Abstand davor mit, der Zeilenumbruch bleibt.
const LEER = (z) => z === ' ' || z === '\t';

function ausschneiden(text, bereiche) {
  const stuecke = [];
  let gelesen = 0;

  for (const [von, bis] of [...bereiche].sort((a, b) => a[0] - b[0])) {
    let a = von, b = bis;
    let zeilenanfang = a;
    while (zeilenanfang > 0 && LEER(text[zeilenanfang - 1])) zeilenanfang--;
    const alleinAufZeile = zeilenanfang === 0 || text[zeilenanfang - 1] === '\n';

    a = zeilenanfang;
    while (LEER(text[b])) b++;
    // Nur wenn der Kommentar die Zeile fuer sich hatte, darf der Umbruch mit.
    if (alleinAufZeile && text[b] === '\n') b++;
    else if (!alleinAufZeile) { /* Umbruch stehen lassen */ }

    if (a < gelesen) continue;            // ueberlappt, schon erledigt
    stuecke.push(text.slice(gelesen, a));
    gelesen = b;
  }
  stuecke.push(text.slice(gelesen));
  return stuecke.join('');
}

// ------------------------------------------------------------------------- CSS

// Zeichenketten und url(...) ueberspringen, der Rest ist Kommentar oder Code.
function cssBereiche(text) {
  const bereiche = [];
  for (let i = 0; i < text.length; i++) {
    const z = text[i];
    if (z === '"' || z === "'") {
      for (i++; i < text.length && text[i] !== z; i++) if (text[i] === '\\') i++;
      continue;
    }
    if (z === '/' && text[i + 1] === '*') {
      const ende = text.indexOf('*/', i + 2);
      const bis = ende === -1 ? text.length : ende + 2;
      bereiche.push([i, bis]);
      i = bis - 1;
      continue;
    }
    if (/^url\(/i.test(text.slice(i, i + 4))) {
      const ende = text.indexOf(')', i);
      i = ende === -1 ? text.length : ende;
    }
  }
  return bereiche;
}

const css = (text) => ausschneiden(text, cssBereiche(text));

// ------------------------------------------------------------------ JavaScript

// acorn liefert die Kommentare mit genauer Lage. Erst als Skript versuchen,
// dann als Modul — die Seite laedt beides.
function jsBereiche(text) {
  let fehler;
  for (const sourceType of ['script', 'module']) {
    const bereiche = [];
    try {
      parse(text, {
        ecmaVersion: 'latest', sourceType, allowHashBang: true,
        onComment: (block, inhalt, von, bis) => bereiche.push([von, bis]),
      });
      return bereiche;
    } catch (e) { fehler = e; }
  }
  throw fehler;
}

// Beweis, dass nur Kommentare weg sind: die Folge der Token muss vorher und
// nachher Zeichen fuer Zeichen dieselbe sein. Ist sie es nicht, war mehr im
// Schnitt als ein Kommentar, und dann liefern wir die Datei nicht aus.
function tokenGleich(vorher, nachher) {
  const lesen = (t) => {
    for (const sourceType of ['script', 'module']) {
      try {
        return [...tokenizer(t, { ecmaVersion: 'latest', sourceType })]
          .map((tk) => `${tk.type.label}\u0000${tk.value}`).join('\u0001');
      } catch {}
    }
    return null;
  };
  const a = lesen(vorher), b = lesen(nachher);
  return a !== null && a === b;
}

function js(text) {
  const ergebnis = ausschneiden(text, jsBereiche(text));
  if (!tokenGleich(text, ergebnis)) throw new Error('die Token haben sich veraendert');
  return ergebnis;
}

// ------------------------------------------------------------------------ HTML

// In <style> und <script> steht kein HTML-Kommentar, dort gelten die Regeln der
// jeweiligen Sprache. Also zuerst diese Bereiche abstecken, dazwischen die
// <!-- --> herausnehmen. JSON-LD bleibt wie es ist: es ist Daten, kein Code.
const BEREICH = /<(style|script)\b([^>]*)>([\s\S]*?)<\/\1\s*>/gi;

function html(text) {
  let ergebnis = '';
  let gelesen = 0;

  for (const treffer of text.matchAll(BEREICH)) {
    const [ganz, tag, attribute, inhalt] = treffer;
    const start = treffer.index;
    const inhaltStart = start + ganz.indexOf(inhalt, tag.length + 1 + attribute.length);

    ergebnis += htmlKommentare(text.slice(gelesen, inhaltStart));

    const json = /type\s*=\s*["']?application\/(ld\+)?json/i.test(attribute);
    if (!inhalt.trim() || json) ergebnis += inhalt;
    else if (tag.toLowerCase() === 'style') ergebnis += css(inhalt);
    else ergebnis += js(inhalt);

    gelesen = inhaltStart + inhalt.length;
  }
  ergebnis += htmlKommentare(text.slice(gelesen));
  return ergebnis;
}

function htmlKommentare(text) {
  const bereiche = [];
  for (let i = text.indexOf('<!--'); i !== -1; i = text.indexOf('<!--', i + 1)) {
    const ende = text.indexOf('-->', i + 4);
    if (ende === -1) break;
    bereiche.push([i, ende + 3]);
    i = ende;
  }
  // Wo ein Kommentar zwischen zwei Leerzeilen stand, bleibt sonst eine doppelte
  // Luecke stehen — auch eine Spur. In HTML spielt der Umbruch keine Rolle, die
  // Seite hat kein <pre> und kein white-space:pre.
  return ausschneiden(text, bereiche).replace(/\n{3,}/g, '\n\n');
}

// ------------------------------------------------------------------- Durchgang

const wurzel = process.argv[2];
if (!wurzel) {
  console.error('Ordner fehlt: node quellen/kommentare-entfernen.mjs dist');
  process.exit(1);
}

async function durchgehen(ordner) {
  for (const eintrag of await readdir(ordner, { withFileTypes: true })) {
    const pfad = join(ordner, eintrag.name);
    if (eintrag.isDirectory()) { await durchgehen(pfad); continue; }

    const behandeln = { '.html': html, '.css': css, '.js': js }[extname(eintrag.name).toLowerCase()];
    if (!behandeln) continue;

    const vorher = await readFile(pfad, 'utf8');
    let nachher;
    try {
      nachher = behandeln(vorher);
    } catch (fehler) {
      // Lieber mit Kommentaren ausliefern als mit halber Datei: Abbruch, der
      // Lauf wird rot, und auf dem Server bleibt die letzte gute Fassung.
      console.error(`::error::${pfad}: ${fehler.message}`);
      process.exit(1);
    }
    await writeFile(pfad, nachher);
    console.log(`${pfad}: ${vorher.length} → ${nachher.length} Zeichen (-${vorher.length - nachher.length})`);
  }
}

await durchgehen(wurzel);
