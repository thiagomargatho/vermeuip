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
<title>Ver meu IP — vermeuip.com.br</title>
<meta name="description" content="Veja seu IP, provedor e localização aproximada, e use teste de portas, consulta DNS, whois e teste de velocidade. Em português, sem anúncios.">
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
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
</header>

<main>
<section class="hero" aria-labelledby="ip-label">
    <p class="muted" id="ip-label">Seu endereço <?= $ipv6 ? 'IPv6' : 'IPv4' ?></p>
    <div class="ip" id="ip" data-clarity-mask="true"><?= $ip !== '' ? e($ip) : '—' ?></div>
    <p class="muted" id="local" data-clarity-mask="true">Carregando localização…</p>
    <p class="muted small" id="ptr-linha" data-clarity-mask="true" hidden>IP reverso: <span id="ptr"></span></p>
    <div class="actions">
        <button class="btn" id="copiar" type="button">Copiar IP</button>
        <a class="link" id="mapa" href="#" target="_blank" rel="noopener" hidden>Ver no mapa</a>
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
