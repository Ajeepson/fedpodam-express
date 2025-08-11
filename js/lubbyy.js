// Lubbyy — small popup assistant. Sends messages to /api/chat
(function(){
  const btn = document.createElement('div'); btn.id='lubbyy-btn'; btn.textContent='💬';
  const chat = document.createElement('div'); chat.id='lubbyy-chat';
  chat.innerHTML = `
    <div id="lubbyy-header">Lubbyy Assistant</div>
    <div id="lubbyy-body"><div class="msg"><b>Lubbyy:</b> Hi! I can help you find products, orders, and answer FAQs.</div></div>
    <div id="lubbyy-input"><input id="lubbyyText" placeholder="Type a message and press Enter"></div>
  `;
  document.body.appendChild(chat); document.body.appendChild(btn);

  btn.addEventListener('click', ()=>{
    chat.style.display = chat.style.display === 'flex' ? 'none' : 'flex';
  });
  chat.style.display='none';
  chat.style.flexDirection='column';

  const body = () => document.getElementById('lubbyy-body');
  const input = () => document.getElementById('lubbyyText');

  async function sendMessage(text){
    if (!text) return;
    body().innerHTML += `<div class="msg"><b>You:</b> ${escapeHtml(text)}</div>`;
    input().value='';
    body().scrollTop = body().scrollHeight;
    try{
      const resp = await fetch('/api/chat', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ message: text }) });
      const j = await resp.json();
      const reply = j.reply || 'Sorry, Lubbyy could not respond right now.';
      body().innerHTML += `<div class="msg"><b>Lubbyy:</b> ${escapeHtml(reply)}</div>`;
      body().scrollTop = body().scrollHeight;
    }catch(e){
      body().innerHTML += `<div class="msg"><b>Lubbyy:</b> Error contacting server.</div>`;
    }
  }

  document.addEventListener('keypress', function(e){
    if (e.target && e.target.id === 'lubbyyText' && e.key === 'Enter'){
      sendMessage(e.target.value);
    }
  });

  function escapeHtml(s){ return s.replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;'); }
})();
