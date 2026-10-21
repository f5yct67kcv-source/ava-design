(() => {
  const st = document.createElement('style');
  st.textContent = `*,*::before,*::after{background:transparent!important;box-shadow:none!important;text-shadow:none!important;border-color:#2f4553!important;filter:none!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important}
html,body{background:#0e1820!important}
body *{outline:1px solid rgba(117,144,159,.28)}
header,section,footer{outline:1px dashed rgba(255,138,76,.55)!important;outline-offset:-1px}
.bar{background:#2a3e4b!important;color:transparent!important;-webkit-text-fill-color:transparent!important;border-radius:2px;-webkit-box-decoration-break:clone;box-decoration-break:clone;outline:0!important}
h1 .bar,h2 .bar,h3 .bar{background:#3f5e70!important}
svg *{stroke:#75909f!important;fill:none!important}
img,video,picture{opacity:0}
input,textarea,select,button{color:transparent!important}`;
  document.head.appendChild(st);
  const w = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT); const n = [];
  while (w.nextNode()) { const t = w.currentNode; if (t.nodeValue.trim() && !t.parentElement.closest('script,style,svg')) n.push(t); }
  n.forEach(t => { const s = document.createElement('span'); s.className = 'bar'; t.replaceWith(s); s.appendChild(t); });
})();
