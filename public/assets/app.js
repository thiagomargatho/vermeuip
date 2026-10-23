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
        return c instanceof Node ? el('td', {}, [c]) : el('td', { text: c == null || c === '' ? '—' : String(c) });
      }));
    }));
    return el('table', {}, [thead, tbody]);
  }

  async function chamar(url, opcoes) {
    var r, corpo;
    try {
      r = await fetch(url, opcoes);
      corpo = await r.json();
    } catch (e) {
      throw new Error('Não foi possível completar a consulta. Tente novamente.');
    }
    if (!r.ok) throw new Error(corpo && corpo.erro ? corpo.erro : 'Erro na consulta.');
    return corpo;
  }

  async function executar(botao, painel, fn) {
    botao.disabled = true;
    try { await fn(); } catch (e) { mostrarErro(painel, e.message); }
    botao.disabled = false;
  }

  /* ---- abas ---- */
  var abas = $$('.tabs [role=tab]');
  abas.forEach(function (aba) {
    aba.addEventListener('click', function () {
      abas.forEach(function (a) {
        var ativa = a === aba;
        a.setAttribute('aria-selected', ativa ? 'true' : 'false');
        $('#' + a.getAttribute('aria-controls')).hidden = !ativa;
      });
    });
  });

  /* ---- copiar IP ---- */
  var copiar = $('#copiar');
  copiar.addEventListener('click', function () {
    var ip = $('#ip').textContent.trim();
    var ok = function () { copiar.textContent = 'Copiado ✓'; setTimeout(function () { copiar.textContent = 'Copiar IP'; }, 1400); };
    if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(ip).then(ok);
  });

  /* ---- geolocalização (chega depois do IP) ---- */
  fetch('api/geo.php').then(function (r) { return r.json(); }).then(function (g) {
    var partes = [g.cidade, g.uf, g.pais].filter(Boolean).join(', ');
    var prov = g.isp ? g.isp + (g.asn ? ' (AS' + g.asn + ')' : '') : '';
    var texto = [partes, prov].filter(Boolean).join(' · ');
    $('#local').textContent = texto ? texto + ' · aproximado' : 'Localização indisponível';
    if (g.ptr) { $('#ptr').textContent = g.ptr; $('#ptr-linha').hidden = false; }
    if (g.lat != null && g.lon != null) {
      var m = $('#mapa');
      m.href = 'https://www.openstreetmap.org/?mlat=' + g.lat + '&mlon=' + g.lon + '#map=10/' + g.lat + '/' + g.lon;
      m.hidden = false;
    }
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
      var q = new URLSearchParams({ dominio: formDns.dominio.value, tipo: formDns.tipo.value });
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
      var d = await chamar('api/whois.php?' + new URLSearchParams({ dominio: formWhois.dominio.value }));
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
