// Messung mit Google Analytics und Google Ads, und der Banner dazu.
// Google laedt erst nach "Akzeptieren". Vorher geht keine Anfrage an Google.
// Solange MESSUNG leer ist, passiert gar nichts: kein Banner, keine Messung,
// die Datenschutzerklaerung zeigt die Fassung ohne Messung. Siehe README, Messung.
(() => {
  const MESSUNG = {
    analytics: '',   // Mess-ID aus Google Analytics, G-…
    ads: '',         // Conversion-ID aus Google Ads, AW-…
    adsLabel: '',    // Label der Conversion "Anfrage gesendet" aus Google Ads, leer wenn aus Analytics importiert
  };
  const echt = !!(MESSUNG.analytics || MESSUNG.ads);
  // ?banner-test zeigt den Banner zum Ansehen, ohne dass etwas geladen wird.
  const test = /[?&]banner-test\b/.test(location.search);

  // Die Wahl liegt im Browser (localStorage, kein Cookie) und gilt zwoelf Monate.
  const SCHLUESSEL = 'zustimmung', GUELTIG = 365 * 864e5;
  const wahl = () => { try { const w = JSON.parse(localStorage.getItem(SCHLUESSEL)); return w && Date.now() - w.zeit < GUELTIG ? w.wahl : null; } catch { return null; } };
  const merken = (w) => { try { localStorage.setItem(SCHLUESSEL, JSON.stringify({ wahl: w, zeit: Date.now() })); } catch {} };

  window.dataLayer = window.dataLayer || [];
  function gtag(){ dataLayer.push(arguments); }
  let geladen = false;
  function laden(){
    if (geladen || !echt) return;
    geladen = true;
    gtag('consent', 'default', { ad_storage:'granted', analytics_storage:'granted', ad_user_data:'granted', ad_personalization:'granted' });
    gtag('js', new Date());
    if (MESSUNG.analytics) gtag('config', MESSUNG.analytics);
    if (MESSUNG.ads) gtag('config', MESSUNG.ads);
    const s = document.createElement('script');
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + (MESSUNG.analytics || MESSUNG.ads);
    document.head.appendChild(s);
  }

  // Ereignisse aus den Seiten. Ohne Zustimmung laufen sie ins Leere.
  // Nie Formularinhalte mitgeben: nur Namen und Nummern.
  window.messen = (name, werte = {}) => {
    if (!geladen) return;
    gtag('event', name, werte);
    if ((name === 'anfrage_gesendet' || name === 'termin_gebucht') && MESSUNG.ads && MESSUNG.adsLabel) gtag('event', 'conversion', { send_to: MESSUNG.ads + '/' + MESSUNG.adsLabel });
  };

  // Klicks auf Mail und Telefon, ueberall auf der Seite. Nur der Weg, nicht die Adresse.
  document.addEventListener('click', (e) => {
    const a = e.target.closest && e.target.closest('a[href^="mailto:"], a[href^="tel:"]');
    if (a) window.messen('kontakt_klick', { weg: a.href.startsWith('tel:') ? 'telefon' : 'mail' });
  });

  // Lehnt jemand nachtraeglich ab, kommen die Google-Cookies weg.
  function cookiesLoeschen(){
    const teile = location.hostname.split('.');
    const domains = ['', location.hostname, '.' + teile.slice(-2).join('.')];
    document.cookie.split(';').map(c => c.split('=')[0].trim()).filter(n => /^(_ga|_gid|_gat|_gcl)/.test(n)).forEach(n => {
      domains.forEach(d => { document.cookie = n + '=; Max-Age=0; path=/' + (d ? '; domain=' + d : ''); });
    });
  }

  const css = `
.zustimmung{position:fixed;left:0;right:0;bottom:0;z-index:300;display:flex;align-items:center;gap:28px;
  padding:16px max(24px,calc((100vw - 1180px) / 2));background:rgba(14,24,32,.97);border-top:1px solid #2f4553;
  color:#e4eaee;font-family:"Schibsted Grotesk",system-ui,-apple-system,sans-serif;font-size:.875rem;line-height:1.55;
  -webkit-backdrop-filter:blur(12px);backdrop-filter:blur(12px)}
.zustimmung[hidden]{display:none}
.zustimmung .text{flex:1;display:flex;align-items:baseline;gap:18px}
.zustimmung b{font-family:"Playfair Display",Georgia,serif;font-weight:900;font-size:1.05rem;white-space:nowrap}
.zustimmung p{margin:0;color:#a8b8c4}
.zustimmung a{color:inherit;text-underline-offset:3px}
.zustimmung .knoepfe{display:flex;gap:10px;flex:0 0 auto}
.zustimmung button{font:inherit;font-weight:700;min-width:140px;padding:11px 18px;border-radius:999px;cursor:pointer;white-space:nowrap}
.zustimmung .nein{background:transparent;color:inherit;border:1px solid rgba(228,234,238,.35)}
.zustimmung .ja{background:#ff8a4c;color:#14212a;border:1px solid #ff8a4c}
.zustimmung button:focus-visible{outline:2px solid #ff8a4c;outline-offset:3px}
@media(hover:hover){.zustimmung .nein:hover{border-color:#e4eaee}.zustimmung .ja:hover{background:#ff9f6d}}
@media(prefers-reduced-motion:no-preference){.zustimmung{animation:zustimmung-ein .45s cubic-bezier(.16,1,.3,1)}}
@keyframes zustimmung-ein{from{transform:translateY(100%)}}
@media(max-width:699px){
  .zustimmung{flex-direction:column;align-items:stretch;gap:14px;padding:16px}
  .zustimmung .text{flex-direction:column;gap:6px}
  .zustimmung button{flex:1;min-width:0}
}`;

  let leiste;
  function zeigen(fokus){
    if (!leiste) {
      const st = document.createElement('style'); st.textContent = css; document.head.appendChild(st);
      leiste = document.createElement('div');
      leiste.className = 'zustimmung';
      leiste.setAttribute('role', 'region');
      leiste.setAttribute('aria-label', 'Cookies');
      leiste.innerHTML = '<div class="text"><b>Cookies</b><p>Ich nutze Cookies, um zu sehen, ob meine Werbung wirkt. Nur wenn du zustimmst. <a href="datenschutz.html#messung">Datenschutz</a></p></div>'
        + '<div class="knoepfe"><button class="nein" type="button">Ablehnen</button><button class="ja" type="button">Akzeptieren</button></div>';
      leiste.querySelector('.ja').addEventListener('click', () => { merken('ja'); leiste.hidden = true; laden(); });
      leiste.querySelector('.nein').addEventListener('click', () => {
        merken('nein'); leiste.hidden = true;
        if (geladen) { cookiesLoeschen(); location.reload(); }
      });
      document.body.appendChild(leiste);
    }
    leiste.hidden = false;
    if (fokus) leiste.querySelector('button').focus();
  }

  function start(){
    if (!echt && !test) return;
    // Datenschutzerklaerung: Abschnitte fuer mit und ohne Messung umschalten.
    if (echt) {
      document.querySelectorAll('[data-messung="an"]').forEach(el => el.hidden = false);
      document.querySelectorAll('[data-messung="aus"]').forEach(el => el.hidden = true);
    }
    // Link im Fuss, um die Wahl jederzeit zu aendern.
    document.querySelectorAll('[data-cookies]').forEach(a => {
      a.hidden = false;
      a.addEventListener('click', (e) => { e.preventDefault(); zeigen(true); });
    });
    const w = test ? null : wahl();
    if (w === 'ja') laden();
    else if (w === null) zeigen(false);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
})();
