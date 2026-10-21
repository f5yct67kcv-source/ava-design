// Ganze Seite in fester Breite aufnehmen, ueber das DevTools-Protokoll.
// node aufnahme.mjs URL BREITE DICHTE AUSGABE.png
import { spawn } from 'node:child_process';
import { writeFileSync, mkdtempSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
const [url, breite, dichte, aus, einschub] = process.argv.slice(2);
import { readFileSync } from 'node:fs';
const C = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const profil = mkdtempSync(join(tmpdir(), 'cdp-'));
const port = 9300 + Math.floor(Math.random() * 500);
const chrome = spawn(C, ['--headless=new', '--hide-scrollbars', `--remote-debugging-port=${port}`, `--user-data-dir=${profil}`, 'about:blank'], { stdio: 'ignore' });
const warte = ms => new Promise(r => setTimeout(r, ms));
let ziel;
for (let i = 0; i < 50 && !ziel; i++) {
  try { const l = await (await fetch(`http://127.0.0.1:${port}/json/list`)).json(); ziel = l.find(t => t.type === 'page'); } catch {}
  if (!ziel) await warte(200);
}
const ws = new WebSocket(ziel.webSocketDebuggerUrl);
await new Promise(r => ws.onopen = r);
let id = 0; const offen = new Map();
ws.onmessage = e => { const m = JSON.parse(e.data); if (m.id && offen.has(m.id)) { offen.get(m.id)(m); offen.delete(m.id); } };
const cdp = (method, params = {}) => new Promise(r => { const i = ++id; offen.set(i, r); ws.send(JSON.stringify({ id: i, method, params })); });
const b = +breite, d = +dichte;
await cdp('Emulation.setDeviceMetricsOverride', { width: b, height: b < 600 ? 844 : 900, deviceScaleFactor: d, mobile: b < 600 });
if (b < 600) await cdp('Emulation.setUserAgentOverride', { userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1' });
await cdp('Page.enable');
await cdp('Page.navigate', { url });
await warte(3500);
// Alles einmal durchscrollen, damit faul geladene Bilder und Einblendungen ausgeloest werden.
await cdp('Runtime.evaluate', { expression: `(async()=>{for(let y=0;y<document.documentElement.scrollHeight;y+=400){scrollTo(0,y);await new Promise(r=>setTimeout(r,120))}scrollTo(0,0);document.querySelectorAll('details').forEach(x=>x.open=false)})()`, awaitPromise: true });
if (einschub) await cdp('Runtime.evaluate', { expression: readFileSync(einschub, 'utf8') });
await warte(1500);
const h = (await cdp('Runtime.evaluate', { expression: 'document.documentElement.scrollHeight', returnByValue: true })).result.result.value;
await cdp('Emulation.setDeviceMetricsOverride', { width: b, height: h, deviceScaleFactor: d, mobile: b < 600 });
await warte(1200);
const shot = await cdp('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true, clip: { x: 0, y: 0, width: b, height: h, scale: 1 } });
writeFileSync(aus, Buffer.from(shot.result.data, 'base64'));
console.log(JSON.stringify({ breite: b, hoehe: h }));
ws.close(); chrome.kill();
