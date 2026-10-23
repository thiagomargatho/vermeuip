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
    <div class="ip" id="ip" data-clarity-mask="true"><?= $ip !== '' ? e($ip) : '—' ?></div>
    <p class="muted" id="local" data-clarity-mask="true">Carregando localização…</p>
    <p class="muted small" id="ptr-linha" data-clarity-mask="true" hidden>IP reverso: <span id="ptr"></span></p>
    <div class="actions">
        <button class="btn" id="copiar" type="button">Copiar IP</button>
    </div>
</section>

<nav class="tabs" role="tablist" aria-label="Ferramentas">
    <button role="tab" aria-selected="true" aria-controls="painel-portas" id="aba-portas">Teste de portas</button>
    <button role="tab" aria-selected="false" aria-controls="painel-dns" id="aba-dns">Consulta DNS</button>
    <button role="tab" aria-selected="false" aria-controls="painel-whois" id="aba-whois">Whois</button>
    <button role="tab" aria-selected="false" aria-controls="painel-vel" id="aba-vel">Velocidade</button>
</nav>

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

<footer class="foot">
    <p>Sem anúncios. Não guardamos o seu IP.</p>
    <p>Usamos o Microsoft Clarity (cookies e métricas de uso). O teste de velocidade contata a Cloudflare.
       Localização aproximada: <a href="https://db-ip.com" rel="noopener">IP Geolocation by DB-IP</a>.</p>
    <p>Contato: <a href="mailto:contato@vermeuip.com.br">contato@vermeuip.com.br</a></p>
</footer>

<script src="assets/app.js" defer></script>
<script type="module" src="assets/speed.js"></script>
</body>
</html>
