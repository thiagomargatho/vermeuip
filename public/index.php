<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Vermeuip\ClientIp;
use Vermeuip\Config;

$config = Config::get();
$ip = ClientIp::resolve($_SERVER, $config['TRUSTED_PROXIES']);
$ipv6 = str_contains($ip, ':');
$engine = $config['SPEED_ENGINE'];

header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Qual é o meu IP? Teste de portas, DNS, Whois e velocidade</title>
<meta name="description" content="Veja qual é o seu IP, provedor e localização. Ferramentas grátis de TI: teste de portas, consulta DNS, whois e velocidade. Em português, sem anúncios.">
<link rel="canonical" href="https://vermeuip.com.br/">
<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
<meta name="theme-color" content="#2563eb">
<meta property="og:type" content="website">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="vermeuip.com.br">
<meta property="og:url" content="https://vermeuip.com.br/">
<meta property="og:title" content="Qual é o meu IP? Teste de portas, DNS, Whois e velocidade">
<meta property="og:description" content="Seu IP, provedor e localização aproximada, mais teste de portas, consulta DNS, whois e teste de velocidade. Em português e sem anúncios.">
<meta property="og:image" content="https://vermeuip.com.br/assets/og.png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="vermeuip.com.br: qual é o meu IP e ferramentas de rede">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Qual é o meu IP? Teste de portas, DNS, Whois e velocidade">
<meta name="twitter:description" content="Seu IP, provedor e localização aproximada, mais teste de portas, consulta DNS, whois e teste de velocidade.">
<meta name="twitter:image" content="https://vermeuip.com.br/assets/og.png">
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "vermeuip.com.br",
  "url": "https://vermeuip.com.br/",
  "description": "Descubra qual é o seu IP, provedor e localização aproximada. Teste de portas, consulta DNS, whois e teste de velocidade.",
  "applicationCategory": "UtilitiesApplication",
  "operatingSystem": "Qualquer",
  "inLanguage": "pt-BR",
  "isAccessibleForFree": true,
  "offers": { "@type": "Offer", "price": "0", "priceCurrency": "BRL" },
  "featureList": ["Qual é o meu IP", "Teste de portas", "Consulta DNS", "Whois de domínio", "Teste de velocidade"]
}
</script>
<meta name="color-scheme" content="light dark">
<script>
    // aplica o tema salvo antes de pintar a pagina (evita piscar claro->escuro)
    try { var t = localStorage.getItem('tema'); if (t === 'light' || t === 'dark') document.documentElement.setAttribute('data-theme', t); } catch (e) {}
</script>
<link rel="stylesheet" href="assets/app.css">
<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "z23oq7eebz");
</script>
</head>
<body>
<header class="top">
    <a class="logo" href="/">ver<span>meu</span>ip</a>
    <button class="tema" id="tema" type="button" aria-label="Alternar entre tema claro e escuro">
        <span id="tema-icone" aria-hidden="true">☾</span> <span id="tema-texto">Tema escuro</span>
    </button>
</header>

<main>
<section class="hero" aria-labelledby="ip-label">
    <h1 class="rotulo" id="ip-label">Qual é o meu IP? <span>Seu endereço <?= $ipv6 ? 'IPv6' : 'IPv4' ?></span></h1>
    <div class="ip copiavel" id="ip" data-clarity-mask="true" tabindex="0" role="button" title="Clique para copiar"><?= $ip !== '' ? e($ip) : '—' ?></div>
    <p class="muted" id="local" data-clarity-mask="true">Carregando localização…</p>
    <p class="muted small" id="ptr-linha" data-clarity-mask="true" hidden>IP reverso: <span id="ptr" tabindex="0" role="button" title="Clique para copiar"></span></p>
    <div class="actions">
        <button class="btn" id="copiar" type="button">Copiar IP</button>
    </div>
</section>

<section class="ferramentas-secao" aria-labelledby="ferramentas-titulo">
    <h2 id="ferramentas-titulo" class="ferramentas-titulo">Ferramentas de rede</h2>
    <p class="muted ferramentas-sub">Escolha uma ferramenta. Tudo roda aqui mesmo, sem cadastro.</p>
    <nav class="ferramentas" role="tablist" aria-label="Ferramentas">
        <button class="ferramenta" role="tab" aria-selected="true" aria-controls="painel-portas" id="aba-portas" type="button">
            <svg class="ferramenta-icone" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="6" rx="1.5"/><rect x="3" y="14" width="18" height="6" rx="1.5"/><path d="M7 7h.01M7 17h.01M11 7h6M11 17h6"/></svg>
            <span class="ferramenta-nome">Teste de portas</span>
            <span class="ferramenta-desc">Veja se uma porta do seu IP está aberta.</span>
        </button>
        <button class="ferramenta" role="tab" aria-selected="false" aria-controls="painel-dns" id="aba-dns" type="button">
            <svg class="ferramenta-icone" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3z"/></svg>
            <span class="ferramenta-nome">Consulta DNS</span>
            <span class="ferramenta-desc">Registros A, MX, TXT, NS e outros.</span>
        </button>
        <button class="ferramenta" role="tab" aria-selected="false" aria-controls="painel-whois" id="aba-whois" type="button">
            <svg class="ferramenta-icone" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2.2"/><path d="M5.8 16c.6-1.6 1.8-2.4 3.2-2.4s2.6.8 3.2 2.4M15 10h3.5M15 13.5h3.5"/></svg>
            <span class="ferramenta-nome">Whois</span>
            <span class="ferramenta-desc">Titular, datas e servidores de um domínio.</span>
        </button>
        <button class="ferramenta" role="tab" aria-selected="false" aria-controls="painel-vel" id="aba-vel" type="button">
            <svg class="ferramenta-icone" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 17a8 8 0 1 1 16 0"/><path d="M12 17l4.5-6"/><circle cx="12" cy="17" r="1.2"/></svg>
            <span class="ferramenta-nome">Velocidade</span>
            <span class="ferramenta-desc">Download, upload e latência da sua conexão.</span>
        </button>
    </nav>
</section>

<div class="content">
    <section class="panel" id="painel-portas" role="tabpanel" aria-labelledby="aba-portas">
        <h2>Teste de portas</h2>
        <p class="muted">Verifica se uma porta do seu IP está aberta para a internet.</p>
        <form class="row" id="form-portas">
            <input name="porta" inputmode="numeric" value="443" aria-label="Porta" class="porta">
            <button class="btn" type="submit">Testar</button>
        </form>
        <p class="atalhos small">Atalhos:
            <?php foreach ([22, 25, 53, 80, 443, 3306, 3389] as $p): ?>
                <button type="button" class="chip" data-porta="<?= $p ?>"><?= $p ?></button>
            <?php endforeach; ?>
        </p>
        <p class="muted small">Se o seu provedor usa CGNAT ou há firewall, o resultado costuma ser "fechada ou filtrada".</p>
        <div class="out" id="out-portas" aria-live="polite"></div>
    </section>

    <section class="panel" id="painel-dns" role="tabpanel" aria-labelledby="aba-dns" hidden>
        <h2>Consulta DNS</h2>
        <p class="muted">Registros de qualquer domínio.</p>
        <form class="row" id="form-dns">
            <input name="dominio" placeholder="exemplo.com.br" aria-label="Domínio" autocapitalize="none" spellcheck="false">
            <select name="tipo" aria-label="Tipo de registro">
                <?php foreach (['A', 'AAAA', 'MX', 'TXT', 'NS', 'CNAME', 'SOA'] as $t): ?>
                    <option><?= $t ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn" type="submit">Consultar</button>
        </form>
        <div class="out" id="out-dns" aria-live="polite"></div>
    </section>

    <section class="panel" id="painel-whois" role="tabpanel" aria-labelledby="aba-whois" hidden>
        <h2>Whois de domínio</h2>
        <p class="muted">Datas, status e servidores de nomes.</p>
        <form class="row" id="form-whois">
            <input name="dominio" placeholder="exemplo.com.br" aria-label="Domínio" autocapitalize="none" spellcheck="false">
            <button class="btn" type="submit">Consultar</button>
        </form>
        <div class="out" id="out-whois" aria-live="polite"></div>
    </section>

    <section class="panel" id="painel-vel" role="tabpanel" aria-labelledby="aba-vel" data-engine="<?= e($engine) ?>" hidden>
        <h2>Teste de velocidade</h2>
        <p class="muted">Download, upload e latência. É uma estimativa.</p>
        <button class="btn" id="iniciar-vel" type="button">Iniciar teste</button>
        <div class="out" id="out-vel" aria-live="polite"></div>
    </section>
</div>
</main>

<footer class="rodape">
    <div class="rodape-grade">
        <div class="rodape-marca">
            <a class="logo" href="/">ver<span>meu</span>ip</a>
            <p>Ferramentas de rede em português, rápidas e sem anúncios. Feitas para quem trabalha com TI.</p>
        </div>
        <nav aria-label="Ferramentas">
            <h2>Ferramentas</h2>
            <ul>
                <li><a href="#portas" data-aba="portas">Teste de portas</a></li>
                <li><a href="#dns" data-aba="dns">Consulta DNS</a></li>
                <li><a href="#whois" data-aba="whois">Whois de domínio</a></li>
                <li><a href="#vel" data-aba="vel">Teste de velocidade</a></li>
            </ul>
        </nav>
        <div>
            <h2>Privacidade</h2>
            <p>Não guardamos o seu IP. Usamos o Microsoft Clarity (cookies e métricas de uso), e o teste de velocidade contata a Cloudflare.</p>
        </div>
        <div>
            <h2>Contato</h2>
            <p><a href="mailto:contato@vermeuip.com.br">contato@vermeuip.com.br</a></p>
            <p class="rodape-nota">Localização aproximada por <a href="https://db-ip.com" rel="noopener">IP Geolocation by DB-IP</a>.</p>
        </div>
    </div>
    <div class="rodape-base">
        <span>© <?= date('Y') ?> vermeuip.com.br. Todos os direitos reservados.</span>
        <span>Seu IP, DNS, whois, portas e velocidade em um só lugar.</span>
    </div>
</footer>

<div class="aviso" id="aviso" role="status" aria-live="polite" hidden></div>

<script src="assets/app.js" defer></script>
<script type="module" src="assets/speed.js"></script>
</body>
</html>
