<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../_lib/layout.php';

$ctx = pagina_contexto();
$caminho = '/cgnat/';
$descricao = 'Descubra se o seu provedor de internet usa CGNAT: compare o IP WAN do roteador com o seu IP público e entenda por que as portas não abrem.';

pagina_topo([
    'titulo' => 'Teste de CGNAT: descubra se o seu provedor usa CGNAT',
    'descricao' => $descricao,
    'caminho' => $caminho,
    'logo_no_topo' => true,
    'jsonld' => jsonld_ferramenta('Teste de CGNAT', $descricao, $caminho),
]);
?>
<article class="pagina">
    <h1>Teste de CGNAT</h1>
    <p class="lead">Descubra se o seu provedor coloca a sua conexão atrás de CGNAT, o que impede abrir portas para a internet.</p>
    <p class="muted">Seu IP público: <span class="copiavel" id="ip" data-clarity-mask="true" tabindex="0" role="button" title="Clique para copiar"><?= $ctx['ip'] !== '' ? e($ctx['ip']) : '—' ?></span></p>

    <section class="panel" id="painel-cgnat" aria-labelledby="t-cgnat">
        <h2 id="t-cgnat" class="sr-only">Comparar o IP WAN</h2>
        <form class="row" id="form-cgnat">
            <input name="wan" placeholder="IP WAN do roteador, ex.: 100.72.10.5" aria-label="IP WAN do roteador" inputmode="decimal" autocapitalize="none" spellcheck="false" data-clarity-mask="true">
            <button class="btn" type="submit">Verificar</button>
        </form>
        <div class="out" id="out-cgnat" aria-live="polite"></div>
    </section>

    <section class="texto">
        <h2>Como fazer o teste</h2>
        <ol>
            <li>Abra a página de administração do roteador. O endereço costuma ser <code>192.168.0.1</code> ou <code>192.168.1.1</code>.</li>
            <li>Procure a tela de status da internet ou da WAN e copie o endereço IP que aparece ali.</li>
            <li>Cole o endereço no campo acima e clique em Verificar.</li>
        </ol>
        <p>O navegador não consegue ler o IP WAN do roteador sozinho, por isso o teste pede esse dado.</p>

        <h2>O que é CGNAT</h2>
        <p>CGNAT é um NAT feito pelo provedor: vários clientes saem para a internet com o mesmo IP público. Os provedores usam isso porque os endereços IPv4 acabaram. O sinal mais claro é o roteador receber um IP na faixa <code>100.64.0.0</code> a <code>100.127.255.255</code>, reservada para esse uso.</p>

        <h2>O que o CGNAT atrapalha</h2>
        <ul>
            <li>Abrir portas: câmeras, servidores, acesso remoto e DVR não ficam acessíveis de fora.</li>
            <li>Jogos online que pedem NAT aberto ou moderado.</li>
            <li>Hospedar qualquer serviço em casa.</li>
        </ul>

        <h2>Como sair do CGNAT</h2>
        <ul>
            <li>Peça ao provedor um IP público, dinâmico ou fixo. Alguns cobram uma taxa.</li>
            <li>Use IPv6, se o provedor oferecer: cada aparelho recebe um endereço público.</li>
            <li>Use um túnel ou VPN com encaminhamento de portas.</li>
        </ul>
    </section>

    <?php pagina_relacionadas('cgnat'); ?>
</article>
<?php pagina_rodape(false);
