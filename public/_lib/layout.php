<?php

declare(strict_types=1);

/*
 * Layout comum das paginas (home e uma pagina por ferramenta).
 * Uso: pagina_topo([...meta]); ...conteudo...; pagina_rodape($meta);
 */

use Vermeuip\ClientIp;
use Vermeuip\Config;

const SITE_URL = 'https://vermeuip.com.br';

/** Ferramentas com pagina propria: slug => [rotulo, descricao curta]. */
const FERRAMENTAS = [
    'teste-de-velocidade' => ['Teste de velocidade', 'Download, upload e latência da sua internet'],
    'teste-de-ping' => ['Teste de ping', 'Latência e jitter da sua conexão'],
    'teste-de-portas' => ['Teste de portas', 'Veja se uma porta do seu IP está aberta'],
    'consulta-dns' => ['Consulta DNS', 'Registros A, MX, TXT e outros de qualquer domínio'],
    'whois' => ['Whois de domínio', 'Datas, status e servidores de nomes'],
    'localizar-ip' => ['Localizar IP', 'Cidade, país e provedor de qualquer IP'],
    'cgnat' => ['Teste de CGNAT', 'Descubra se o seu provedor usa CGNAT'],
    'propagacao-dns' => ['Propagação de DNS', 'Compare a resposta de vários resolvedores públicos'],
    'meu-user-agent' => ['Meu user agent', 'User agent e cabeçalhos HTTP do seu navegador'],
];

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL do arquivo com a data de modificacao: o navegador baixa de novo sempre que o arquivo muda. */
function v(string $arquivo): string
{
    return '/' . $arquivo . '?v=' . (@filemtime(dirname(__DIR__) . '/' . $arquivo) ?: 1);
}

/** @return array{ip:string,ipv6:bool,engine:string} */
function pagina_contexto(): array
{
    $config = Config::get();
    $ip = ClientIp::resolve($_SERVER, $config['TRUSTED_PROXIES']);
    return ['ip' => $ip, 'ipv6' => str_contains($ip, ':'), 'engine' => $config['SPEED_ENGINE']];
}

/**
 * Cabecalho HTML ate a abertura de <main>.
 *
 * @param array{titulo:string,descricao:string,caminho:string,og_titulo?:string,og_descricao?:string,jsonld:array<mixed>,logo_no_topo?:bool} $m
 */
function pagina_topo(array $m): void
{
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    $url = SITE_URL . $m['caminho'];
    $ogTitulo = $m['og_titulo'] ?? $m['titulo'];
    $ogDescricao = $m['og_descricao'] ?? $m['descricao'];
    $json = json_encode($m['jsonld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($m['titulo']) ?></title>
<meta name="description" content="<?= e($m['descricao']) ?>">
<link rel="canonical" href="<?= e($url) ?>">
<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
<meta name="theme-color" content="#FFFFFF" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#081429" media="(prefers-color-scheme: dark)">
<meta property="og:type" content="website">
<meta property="og:locale" content="pt_BR">
<meta property="og:site_name" content="VerMeuIP">
<meta property="og:url" content="<?= e($url) ?>">
<meta property="og:title" content="<?= e($ogTitulo) ?>">
<meta property="og:description" content="<?= e($ogDescricao) ?>">
<meta property="og:image" content="<?= SITE_URL ?>/assets/marca/og.png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="VerMeuIP: qual é o meu IP e teste de velocidade">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($ogTitulo) ?>">
<meta name="twitter:description" content="<?= e($ogDescricao) ?>">
<meta name="twitter:image" content="<?= SITE_URL ?>/assets/marca/og.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/marca/favicon-32.png">
<link rel="icon" type="image/png" sizes="192x192" href="/assets/marca/favicon-192.png">
<link rel="apple-touch-icon" href="/assets/marca/apple-touch-icon.png">
<script type="application/ld+json">
<?= $json ?>

</script>
<meta name="color-scheme" content="light dark">
<script>
    // aplica o tema salvo antes de pintar a pagina (evita piscar claro->escuro)
    try { var t = localStorage.getItem('tema'); if (t === 'light' || t === 'dark') document.documentElement.setAttribute('data-theme', t); } catch (e) {}
</script>
<link rel="stylesheet" href="<?= v('assets/app.css') ?>">
<script type="text/javascript">
    // no ambiente local (testes) o Clarity nao carrega, para nao misturar com as visitas reais
    if (!/^(localhost|127\.0\.0\.1|\[::1\])$/.test(location.hostname))
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "z23oq7eebz");
</script>
</head>
<body>
<header class="topo<?= !empty($m['logo_no_topo']) ? ' topo-pagina' : '' ?>">
<?php if (!empty($m['logo_no_topo'])): ?>
    <a class="marca" href="/" aria-label="VerMeuIP, página inicial">
        <img class="logo logo-claro" src="/assets/marca/logo.webp" width="336" height="233" alt="VerMeuIP">
        <img class="logo logo-escuro" src="/assets/marca/logo-escuro.webp" width="336" height="233" alt="VerMeuIP">
    </a>
<?php endif; ?>
    <button class="tema" id="tema" type="button" aria-label="Alternar entre tema claro e escuro">
        <span id="tema-icone" aria-hidden="true">☾</span> <span id="tema-texto">Tema escuro</span>
    </button>
</header>

<main>
<?php
}

/** Rodape, aviso de copia e scripts. $comVelocidade carrega o motor do teste de velocidade. */
function pagina_rodape(bool $comVelocidade): void
{
    ?>
</main>

<footer class="rodape">
    <nav class="rodape-nav" aria-label="Ferramentas">
        <a href="/">Qual é o meu IP</a>
<?php foreach (FERRAMENTAS as $slug => [$rotulo]): ?>
        <a href="/<?= $slug ?>/"><?= e($rotulo) ?></a>
<?php endforeach; ?>
    </nav>
    <p>© <?= date('Y') ?> VerMeuIP.com.br · <a href="mailto:contato@vermeuip.com.br">contato@vermeuip.com.br</a></p>
    <p class="rodape-nota">Sem anúncios. Não guardamos o seu IP. Usamos o Microsoft Clarity (cookies e métricas de uso) e, no teste de velocidade, a Cloudflare. Localização aproximada por <a href="https://db-ip.com" rel="noopener">IP Geolocation by DB-IP</a>.</p>
</footer>

<div class="aviso" id="aviso" role="status" aria-live="polite" hidden></div>

<script src="<?= v('assets/app.js') ?>" defer></script>
<?php if ($comVelocidade): ?>
<script type="module" src="<?= v('assets/speed.js') ?>"></script>
<?php endif; ?>
</body>
</html>
<?php
}

/** Bloco do teste de velocidade (mesmo markup na home e na pagina propria). */
function bloco_velocidade(string $engine): void
{
    ?>
    <div class="velocidade" id="painel-vel" data-engine="<?= e($engine) ?>">
        <button class="btn-speed" id="iniciar-vel" type="button">Testar velocidade</button>
        <div class="out" id="out-vel" aria-live="polite"></div>
    </div>
<?php
}

/** Aplicacao web de uma ferramenta, para o JSON-LD. @return array<string,mixed> */
function jsonld_ferramenta(string $nome, string $descricao, string $caminho): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebApplication',
        'name' => $nome . ' — VerMeuIP',
        'url' => SITE_URL . $caminho,
        'description' => $descricao,
        'applicationCategory' => 'UtilitiesApplication',
        'operatingSystem' => 'Qualquer',
        'inLanguage' => 'pt-BR',
        'isAccessibleForFree' => true,
        'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'BRL'],
    ];
}

/** Links para as outras ferramentas, no fim de cada pagina de ferramenta. */
function pagina_relacionadas(string $atual): void
{
    ?>
    <section class="relacionadas" aria-labelledby="rel-titulo">
        <h2 id="rel-titulo">Outras ferramentas</h2>
        <ul>
            <li><a href="/">Qual é o meu IP</a> <span class="muted">Seu endereço e localização aproximada</span></li>
<?php foreach (FERRAMENTAS as $slug => [$rotulo, $desc]): ?>
<?php if ($slug === $atual) { continue; } ?>
            <li><a href="/<?= $slug ?>/"><?= e($rotulo) ?></a> <span class="muted"><?= e($desc) ?></span></li>
<?php endforeach; ?>
        </ul>
    </section>
<?php
}
