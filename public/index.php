<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/_lib/layout.php';

$ctx = pagina_contexto();

// Perguntas frequentes: o mesmo texto vai para a pagina e para os dados estruturados.
$perguntas = [
    'O que é o meu IP?' => 'É o endereço que identifica a sua conexão na internet. Todo site que você visita enxerga esse endereço, e é ele que aparece no topo desta página.',
    'Qual a diferença entre IP público e IP privado?' => 'O IP público é o endereço da sua conexão na internet, dado pelo provedor. O IP privado (como 192.168.0.10) só vale dentro da sua rede e é dado pelo roteador a cada aparelho. Esta página mostra o IP público.',
    'Meu IP é fixo ou dinâmico?' => 'Na maioria dos planos residenciais o IP é dinâmico: muda quando o roteador reinicia ou de tempos em tempos. IP fixo costuma ser um serviço contratado à parte. Para saber, anote o seu IP, reinicie o roteador e confira se mudou.',
    'Qual a diferença entre IPv4 e IPv6?' => 'O IPv4 tem quatro números separados por pontos, como 200.160.2.3, e os endereços disponíveis acabaram. O IPv6 é o formato novo, mais longo, como 2001:12ff:0:2::3. Muitas conexões usam os dois ao mesmo tempo.',
    'Como mudar o meu IP?' => 'Com IP dinâmico, desligar o roteador por alguns minutos costuma trocar o endereço. Uma VPN também faz os sites enxergarem outro IP. Com IP fixo, só o provedor pode alterar.',
    'O que dá para descobrir pelo meu IP?' => 'Só a região aproximada (cidade ou estado) e o provedor de internet. O IP não revela o seu nome nem o seu endereço. Apenas o provedor sabe qual cliente usava um IP em certo momento.',
];
$faq = [];
foreach ($perguntas as $pergunta => $resposta) {
    $faq[] = ['@type' => 'Question', 'name' => $pergunta, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $resposta]];
}

pagina_topo([
    'titulo' => 'Meu IP: veja qual é o seu IP agora | VerMeuIP',
    'descricao' => 'Veja o seu IP na hora, com localização aproximada e provedor. Teste de velocidade com um clique, teste de portas, CGNAT, DNS e whois. Grátis, em português, sem anúncios.',
    'caminho' => '/',
    'og_titulo' => 'Meu IP: veja qual é o seu IP agora',
    'og_descricao' => 'Seu IP na hora e teste de velocidade com um clique. Também teste de portas, CGNAT, DNS e whois. Em português e sem anúncios.',
    'jsonld' => [[
        '@context' => 'https://schema.org',
        '@type' => 'WebApplication',
        'name' => 'VerMeuIP',
        'url' => 'https://vermeuip.com.br/',
        'description' => 'Descubra qual é o seu IP e teste a velocidade da internet com um clique. Também: teste de portas, consulta DNS e whois.',
        'applicationCategory' => 'UtilitiesApplication',
        'operatingSystem' => 'Qualquer',
        'inLanguage' => 'pt-BR',
        'isAccessibleForFree' => true,
        'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'BRL'],
        'featureList' => array_merge(['Qual é o meu IP'], array_column(FERRAMENTAS, 0)),
    ], [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faq,
    ]],
]);
?>
<section class="hero" aria-labelledby="ip-label">
    <a class="marca" href="/" aria-label="VerMeuIP, página inicial">
        <img class="logo logo-claro" src="/assets/marca/logo.webp" width="336" height="233" alt="VerMeuIP">
        <img class="logo logo-escuro" src="/assets/marca/logo-escuro.webp" width="336" height="233" alt="VerMeuIP">
    </a>

    <h1 class="rotulo" id="ip-label">Qual é o meu IP? <span>Seu endereço <?= $ctx['ipv6'] ? 'IPv6' : 'IPv4' ?></span></h1>
    <div class="ip-address copiavel" id="ip" data-clarity-mask="true" tabindex="0" role="button" title="Clique para copiar"><?= $ctx['ip'] !== '' ? e($ctx['ip']) : '—' ?></div>
    <button class="btn-copiar" id="copiar" type="button">Copiar IP</button>
    <p class="muted" id="local" data-clarity-mask="true">Carregando localização…</p>
    <p class="muted small" id="ptr-linha" data-clarity-mask="true" hidden>IP reverso: <span id="ptr" tabindex="0" role="button" title="Clique para copiar"></span></p>

    <?php bloco_velocidade($ctx['engine']); ?>
</section>

<section class="outras" aria-labelledby="outras-titulo">
    <h2 id="outras-titulo" class="outras-titulo">Outras ferramentas</h2>
    <nav class="outras-nav" role="tablist" aria-label="Outras ferramentas">
        <button role="tab" aria-selected="false" aria-controls="painel-portas" id="aba-portas" type="button">Teste de portas</button>
        <button role="tab" aria-selected="false" aria-controls="painel-dns" id="aba-dns" type="button">Consulta DNS</button>
        <button role="tab" aria-selected="false" aria-controls="painel-whois" id="aba-whois" type="button">Whois</button>
    </nav>

    <div class="content">
        <section class="panel" id="painel-portas" role="tabpanel" aria-labelledby="aba-portas" hidden>
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
    </div>

    <ul class="lista-ferramentas" aria-label="Todas as ferramentas">
<?php foreach (FERRAMENTAS as $slug => [$rotulo, $desc]): ?>
        <li><a href="/<?= $slug ?>/"><?= e($rotulo) ?></a> <span class="muted"><?= e($desc) ?></span></li>
<?php endforeach; ?>
    </ul>
</section>

<section class="perguntas" aria-labelledby="perguntas-titulo">
    <h2 id="perguntas-titulo" class="outras-titulo">Perguntas frequentes</h2>
<?php foreach ($perguntas as $pergunta => $resposta): ?>
    <details>
        <summary><?= e($pergunta) ?></summary>
        <p><?= e($resposta) ?></p>
    </details>
<?php endforeach; ?>
</section>
<?php pagina_rodape(true);
