(function () {
  'use strict';

  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  function el(tag, props, children) {
    var n = document.createElement(tag);
    Object.keys(props || {}).forEach(function (k) {
      if (k === 'text') n.textContent = props[k]; else n.setAttribute(k, props[k]);
    });
    (children || []).forEach(function (c) { n.appendChild(c); });
    return n;
  }

  function mostrarErro(painel, msg) {
    painel.replaceChildren(el('div', { 'class': 'erro', role: 'alert', text: msg }));
  }

  function tabela(cabecalho, linhas) {
    var thead = el('thead', {}, [el('tr', {}, cabecalho.map(function (h) { return el('th', { text: h }); }))]);
    var tbody = el('tbody', {}, linhas.map(function (l) {
      return el('tr', {}, l.map(function (c) {
        if (c instanceof Node) return el('td', {}, [c]);
        var vazio = c == null || c === '';
        return el('td', vazio ? { text: '—' } : { text: String(c), 'class': 'copiavel', tabindex: '0', title: 'Clique para copiar' });
      }));
    }));
    return el('table', {}, [thead, tbody]);
  }

  async function chamar(url, opcoes) {
    var r, corpo;
    try {
      r = await fetch(url, opcoes);
    } catch (e) {
      throw new Error('Não foi possível completar a consulta. Tente novamente.');
    }
    try { corpo = await r.json(); } catch (e) { corpo = null; }
    if (!r.ok) {
      if (corpo && corpo.erro) throw new Error(corpo.erro);
      // o firewall do servidor responde 403 em HTML a entradas com cara de URL
      throw new Error(r.status === 403
        ? 'A consulta foi bloqueada. Digite só o domínio, por exemplo exemplo.com.br.'
        : 'Erro na consulta. Tente novamente.');
    }
    if (!corpo) throw new Error('Resposta inesperada do servidor. Tente novamente.');
    return corpo;
  }

  /* "https://Exemplo.com/caminho?x" -> "Exemplo.com": evita enviar "://" ao servidor */
  function limparDominio(v) {
    return String(v).trim().replace(/^[a-z][a-z0-9+.\-]*:\/\//i, '').split(/[\/?#]/)[0];
  }

  async function executar(botao, painel, fn) {
    botao.disabled = true;
    try { await fn(); } catch (e) { mostrarErro(painel, e.message); }
    botao.disabled = false;
  }

  /* ---- abas ---- */
  var abas = $$('.tabs [role=tab]');
  function ativarAba(aba) {
    abas.forEach(function (a) {
      var ativa = a === aba;
      a.setAttribute('aria-selected', ativa ? 'true' : 'false');
      $('#' + a.getAttribute('aria-controls')).hidden = !ativa;
    });
  }
  abas.forEach(function (aba) { aba.addEventListener('click', function () { ativarAba(aba); }); });
  // links do rodape levam direto para a ferramenta
  $$('[data-aba]').forEach(function (link) {
    link.addEventListener('click', function (ev) {
      ev.preventDefault();
      var aba = $('#aba-' + link.getAttribute('data-aba'));
      ativarAba(aba);
      $('.tabs').scrollIntoView({ behavior: 'smooth', block: 'start' });
      aba.focus({ preventScroll: true });
    });
  });

  /* ---- tema claro/escuro: comeca no do sistema; o clique grava a escolha ---- */
  var raiz = document.documentElement;
  var botaoTema = $('#tema');
  var escuroSistema = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

  function temaAtual() {
    var t = raiz.getAttribute('data-theme');
    if (t === 'light' || t === 'dark') return t;
    return escuroSistema && escuroSistema.matches ? 'dark' : 'light';
  }
  function mostrarTema() {
    var escuro = temaAtual() === 'dark';
    // o botao mostra o tema para o qual ele vai trocar
    $('#tema-icone').textContent = escuro ? '☀' : '☾';
    $('#tema-texto').textContent = escuro ? 'Tema claro' : 'Tema escuro';
    botaoTema.setAttribute('aria-pressed', escuro ? 'true' : 'false');
  }
  botaoTema.addEventListener('click', function () {
    var novo = temaAtual() === 'dark' ? 'light' : 'dark';
    raiz.setAttribute('data-theme', novo);
    try { localStorage.setItem('tema', novo); } catch (e) {}
    mostrarTema();
  });
  if (escuroSistema && escuroSistema.addEventListener) escuroSistema.addEventListener('change', mostrarTema);
  mostrarTema();

  /* ---- copiar: um clique em qualquer valor (IP, resultados, numeros) ja copia (o "Ctrl+C") ---- */
  var aviso = $('#aviso'), avisoTimer = null;
  function mostrarAviso(msg) {
    aviso.textContent = msg;
    aviso.hidden = false;
    clearTimeout(avisoTimer);
    avisoTimer = setTimeout(function () { aviso.hidden = true; }, 1600);
  }
  function copiarTexto(texto) {
    if (navigator.clipboard && navigator.clipboard.writeText && window.isSecureContext) {
      return navigator.clipboard.writeText(texto);
    }
    return new Promise(function (ok, falha) {
      var t = document.createElement('textarea');
      t.value = texto; t.setAttribute('readonly', ''); t.style.position = 'fixed'; t.style.opacity = '0';
      document.body.appendChild(t); t.select();
      try { document.execCommand('copy') ? ok() : falha(); } catch (e) { falha(e); }
      document.body.removeChild(t);
    });
  }
  function copiarValor(texto) {
    texto = String(texto).trim();
    if (!texto || texto === '—') return;
    copiarTexto(texto).then(
      function () { mostrarAviso('Copiado: ' + texto); },
      function () { mostrarAviso('Não foi possível copiar. Selecione e use Ctrl+C.'); }
    );
  }
  // elementos copiaveis: .copiavel, e a celula/valor marcados no JS (data-copia sobrepoe o texto)
  document.addEventListener('click', function (ev) {
    var alvo = ev.target.closest('.copiavel, #ptr');
    if (alvo) copiarValor(alvo.getAttribute('data-copia') || alvo.textContent);
  });
  document.addEventListener('keydown', function (ev) {
    if (ev.key !== 'Enter' && ev.key !== ' ') return;
    var alvo = ev.target.closest && ev.target.closest('.copiavel, #ptr');
    if (alvo) { ev.preventDefault(); copiarValor(alvo.getAttribute('data-copia') || alvo.textContent); }
  });
  $('#copiar').addEventListener('click', function () { copiarValor($('#ip').textContent); });

  /* ---- geolocalização (chega depois do IP) ---- */
  fetch('api/geo.php').then(function (r) { return r.json(); }).then(function (g) {
    var partes = [g.cidade, g.uf, g.pais].filter(Boolean).join(', ');
    var prov = g.isp ? g.isp + (g.asn ? ' (AS' + g.asn + ')' : '') : '';
    var texto = [partes, prov].filter(Boolean).join(' · ');
    $('#local').textContent = texto ? texto + ' · aproximado' : 'Localização indisponível';
    if (g.ptr) { $('#ptr').textContent = g.ptr; $('#ptr-linha').hidden = false; }
  }).catch(function () { $('#local').textContent = 'Localização indisponível'; });

  /* ---- teste de portas ---- */
  var formPortas = $('#form-portas'), outPortas = $('#out-portas');
  $$('.chip[data-porta]').forEach(function (c) {
    c.addEventListener('click', function () { formPortas.porta.value = c.getAttribute('data-porta'); formPortas.requestSubmit(); });
  });
  formPortas.addEventListener('submit', function (ev) {
    ev.preventDefault();
    executar($('button[type=submit]', formPortas), outPortas, async function () {
      var d = await chamar('api/ports.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ porta: formPortas.porta.value.trim() })
      });
      var aberta = d.estado === 'aberta';
      var tag = el('span', { 'class': 'tag' + (aberta ? '' : ' bad'), text: aberta ? 'Aberta' : 'Fechada ou filtrada' });
      outPortas.replaceChildren(tabela(['Host', 'Porta', 'Estado'], [[d.ip, d.porta, tag]]));
    });
  });

  /* ---- DNS ---- */
  var formDns = $('#form-dns'), outDns = $('#out-dns');
  formDns.addEventListener('submit', function (ev) {
    ev.preventDefault();
    executar($('button[type=submit]', formDns), outDns, async function () {
      var q = new URLSearchParams({ dominio: limparDominio(formDns.dominio.value), tipo: formDns.tipo.value });
      var d = await chamar('api/dns.php?' + q);
      if (!d.registros.length) return mostrarErro(outDns, 'Nenhum registro encontrado.');
      outDns.replaceChildren(tabela(['Tipo', 'Valor', 'TTL'], d.registros.map(function (r) { return [r.tipo, r.valor, r.ttl]; })));
    });
  });

  /* ---- Whois ---- */
  var formWhois = $('#form-whois'), outWhois = $('#out-whois');
  formWhois.addEventListener('submit', function (ev) {
    ev.preventDefault();
    executar($('button[type=submit]', formWhois), outWhois, async function () {
      var d = await chamar('api/whois.php?' + new URLSearchParams({ dominio: limparDominio(formWhois.dominio.value) }));
      outWhois.replaceChildren(tabela(['Campo', 'Valor'], [
        ['Domínio', d.dominio], ['Titular', d.titular], ['Registrado em', d.registrado_em],
        ['Expira em', d.expira_em], ['Status', (d.status || []).join(', ')],
        ['Servidores DNS', (d.nameservers || []).join(', ')]
      ]));
    });
  });

  window.vermeuip = window.vermeuip || {};
  window.vermeuip.mostrarErro = mostrarErro;
  window.vermeuip.tabela = tabela;
  window.vermeuip.el = el;
})();
