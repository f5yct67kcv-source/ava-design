// Rendert eine Liste von Seiten mit Chrome zu PNG oder PDF.
// node raster.mjs auftraege.json
// Auftrag: {url, breite, hoehe, aus, durchsichtig?, pdf?}
import { spawn } from 'node:child_process';
import { writeFileSync, readFileSync, mkdtempSync } from 'node:fs';
import { tmpdir } from 'node:os'; import { join } from 'node:path';

const auftraege = JSON.parse(readFileSync(process.argv[2], 'utf8'));
const C = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const port = 9300 + Math.floor(Math.random() * 500);
const chrome = spawn(C, ['--headless=new', '--hide-scrollbars', `--remote-debugging-port=${port}`,
  `--user-data-dir=${mkdtempSync(join(tmpdir(), 'marke-'))}`, '--allow-file-access-from-files', 'about:blank'], { stdio: 'ignore' });
const warte = ms => new Promise(r => setTimeout(r, ms));
let ziel; for (let i = 0; i < 50 && !ziel; i++) { try { ziel = (await (await fetch(`http://127.0.0.1:${port}/json/list`)).json()).find(t => t.type === 'page') } catch {} if (!ziel) await warte(200) }
const ws = new WebSocket(ziel.webSocketDebuggerUrl); await new Promise(r => ws.onopen = r);
let id = 0; const offen = new Map();
ws.onmessage = e => { const m = JSON.parse(e.data); if (m.id && offen.has(m.id)) { offen.get(m.id)(m); offen.delete(m.id) } };
const cdp = (method, params = {}) => new Promise(r => { const i = ++id; offen.set(i, r); ws.send(JSON.stringify({ id: i, method, params })) });

await cdp('Page.enable');
for (const a of auftraege) {
  await cdp('Emulation.setDeviceMetricsOverride', { width: a.breite, height: a.hoehe, deviceScaleFactor: 1, mobile: false });
  await cdp('Emulation.setDefaultBackgroundColorOverride', a.durchsichtig ? { color: { r: 0, g: 0, b: 0, a: 0 } } : {});
  await cdp('Page.navigate', { url: a.url });
  await cdp('Runtime.evaluate', { expression: 'new Promise(r=>{const f=()=>document.readyState==="complete"?document.fonts.ready.then(()=>setTimeout(r,150)):setTimeout(f,50);f()})', awaitPromise: true });
  let daten;
  if (a.pdf) {
    daten = (await cdp('Page.printToPDF', { printBackground: true, preferCSSPageSize: true, displayHeaderFooter: false })).result.data;
  } else {
    daten = (await cdp('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, clip: { x: a.x || 0, y: a.y || 0, width: a.breite, height: a.hoehe, scale: 1 } })).result.data;
  }
  writeFileSync(a.aus, Buffer.from(daten, 'base64'));
}
ws.close(); chrome.kill();
