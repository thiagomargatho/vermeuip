import CloudflareSpeedTest from './vendor/cloudflare-speedtest.js';

/*
 * window.vermeuip.speed.run(onProgress) -> Promise<{download_mbps, upload_mbps, latencia_ms, jitter_ms}>
 * Dois motores atras da mesma interface; o escolhido vem de data-engine no painel.
 */

// Medicoes padrao da Cloudflare sem "packetLoss": ele depende do servico TURN, que falha
// ("unable to get turn server credentials") e derrubava o teste no fim. Maior transferencia: 25 MB.
var MEDICOES = [
  { type: 'latency', numPackets: 2 },
  { type: 'download', bytes: 1e5, count: 1, bypassMinDuration: true },
  { type: 'latency', numPackets: 20 },
  { type: 'download', bytes: 1e5, count: 9 },
  { type: 'latency', numPackets: 2 },
  { type: 'download', bytes: 1e6, count: 8 },
  { type: 'latency', numPackets: 2 },
  { type: 'upload', bytes: 1e5, count: 8 },
  { type: 'latency', numPackets: 2 },
  { type: 'upload', bytes: 1e6, count: 6 },
  { type: 'latency', numPackets: 2 },
  { type: 'download', bytes: 1e7, count: 6 },
  { type: 'latency', numPackets: 2 },
  { type: 'upload', bytes: 1e7, count: 4 },
  { type: 'latency', numPackets: 2 },
  { type: 'download', bytes: 25e6, count: 4 },
  { type: 'latency', numPackets: 2 },
  { type: 'upload', bytes: 25e6, count: 4 },
  { type: 'latency', numPackets: 2 }
];

function runCloudflare(onProgress) {
  return new Promise(function (resolve, reject) {
    var engine = new CloudflareSpeedTest({ autoStart: false, measurements: MEDICOES });
    var snapshot = function (r) {
      var dl = r.getDownloadBandwidth(), ul = r.getUploadBandwidth();
      return {
        download_mbps: dl ? dl / 1e6 : null,
        upload_mbps: ul ? ul / 1e6 : null,
        latencia_ms: r.getUnloadedLatency() != null ? r.getUnloadedLatency() : null,
        jitter_ms: r.getUnloadedJitter() != null ? r.getUnloadedJitter() : null
      };
    };
    var last = null;
    engine.onResultsChange = function () { last = snapshot(engine.results); onProgress(last); };
    engine.onFinish = function (results) { resolve(snapshot(results)); };
    engine.onError = function () {
      // um erro no fim da medicao nao deve jogar fora download/upload ja medidos
      if (last && (last.download_mbps || last.upload_mbps)) resolve(last);
      else reject(new Error('O teste de velocidade falhou. Tente novamente.'));
    };
    engine.play();
  });
}

function loadScript(src) {
  return new Promise(function (resolve, reject) {
    if (window.Speedtest) return resolve();
    var s = document.createElement('script');
    s.src = src;
    s.onload = resolve;
    s.onerror = function () { reject(new Error('Não foi possível carregar o teste de velocidade.')); };
    document.head.appendChild(s);
  });
}

async function runLibreSpeed(onProgress) {
  await loadScript('speedtest/speedtest.js');
  return new Promise(function (resolve) {
    var st = new window.Speedtest();
    var num = function (v) { var n = parseFloat(v); return isNaN(n) || n <= 0 ? null : n; };
    var snap = function (d) {
      return { download_mbps: num(d.dlStatus), upload_mbps: num(d.ulStatus), latencia_ms: num(d.pingStatus), jitter_ms: num(d.jitterStatus) };
    };
    var last = {};
    st.setParameter('telemetry_level', 'off');
    st.setParameter('getIp_ispInfo', false);
    st.setSelectedServer({
      name: 'Este servidor', server: 'speedtest/backend/',
      dlURL: 'garbage.php', ulURL: 'empty.php', pingURL: 'empty.php', getIpURL: 'getIP.php'
    });
    st.onupdate = function (d) { last = snap(d); onProgress(last); };
    st.onend = function () { resolve(last); };
    st.start();
  });
}

function run(engine, onProgress) {
  return engine === 'librespeed' ? runLibreSpeed(onProgress) : runCloudflare(onProgress);
}

window.vermeuip = window.vermeuip || {};
window.vermeuip.speed = { run: function (onProgress) {
  var painel = document.getElementById('painel-vel');
  return run(painel.getAttribute('data-engine'), onProgress);
} };

(function ui() {
  var painel = document.getElementById('painel-vel');
  var botao = document.getElementById('iniciar-vel');
  var saida = document.getElementById('out-vel');
  var fmt = function (v, casas) { return v == null ? '—' : v.toFixed(casas); };

  function metrica(rotulo, valor, unidade) {
    var big = document.createElement('div');
    big.className = 'big';
    if (valor !== '—') {
      // clique copia so o numero (o app.js trata .copiavel); data-copia sobrepoe o texto com a unidade
      big.className = 'big copiavel';
      big.setAttribute('data-copia', valor);
      big.setAttribute('tabindex', '0');
      big.setAttribute('title', 'Clique para copiar');
    }
    big.textContent = valor + ' ';
    var u = document.createElement('small');
    u.textContent = unidade;
    big.appendChild(u);
    var r = document.createElement('div');
    r.className = 'muted';
    r.textContent = rotulo;
    var box = document.createElement('div');
    box.appendChild(r);
    box.appendChild(big);
    return box;
  }

  function mostrar(d, parcial) {
    var wrap = document.createElement('div');
    wrap.className = 'metrics';
    wrap.appendChild(metrica('Download', fmt(d.download_mbps, 0), 'Mbps'));
    wrap.appendChild(metrica('Upload', fmt(d.upload_mbps, 0), 'Mbps'));
    wrap.appendChild(metrica('Latência', fmt(d.latencia_ms, 0), 'ms'));
    wrap.appendChild(metrica('Jitter', fmt(d.jitter_ms, 1), 'ms'));
    var nota = document.createElement('p');
    nota.className = 'muted small';
    nota.textContent = parcial ? 'Medindo…' : 'Estimativa; o resultado varia com a rede e o servidor de teste (' +
      (painel.getAttribute('data-engine') === 'librespeed' ? 'este servidor' : 'Cloudflare') + ').';
    saida.replaceChildren(wrap, nota);
  }

  botao.addEventListener('click', async function () {
    botao.disabled = true;
    botao.textContent = 'Medindo…';
    mostrar({}, true);
    try {
      var final = await window.vermeuip.speed.run(function (d) { mostrar(d, true); });
      mostrar(final, false);
    } catch (e) {
      window.vermeuip.mostrarErro(saida, e.message);
    }
    botao.disabled = false;
    botao.textContent = 'Testar novamente';
  });
})();
