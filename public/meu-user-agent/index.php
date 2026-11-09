<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../_lib/layout.php';

use Vermeuip\RequestHeaders;

$caminho = '/meu-user-agent/';
$descricao = 'Veja o user agent e os cabeçalhos HTTP que o seu navegador envia aos sites: navegador, sistema, idioma e mais. Clique para copiar.';

$cabecalhos = RequestHeaders::visible($_SERVER);
$userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');

pagina_topo([
    'titulo' => 'Meu user agent: veja o user agent e os cabeçalhos HTTP do navegador',
    'descricao' => $descricao,
    'caminho' => $caminho,
    'logo_no_topo' => true,
    'jsonld' => jsonld_ferramenta('Meu user agent', $descricao, $caminho),
]);
?>
<article class="pagina">
    <h1>Meu user agent</h1>
    <p class="lead">Este é o user agent que o seu navegador enviou agora. Clique para copiar.</p>

    <p class="veredito copiavel" id="user-agent" data-clarity-mask="true" tabindex="0" role="button" title="Clique para copiar"><?= $userAgent !== '' ? e($userAgent) : '—' ?></p>

    <section class="panel" aria-labelledby="t-cab">
        <h2 id="t-cab">Cabeçalhos HTTP enviados</h2>
        <table data-clarity-mask="true">
            <thead><tr><th>Cabeçalho</th><th>Valor</th></tr></thead>
            <tbody>
<?php foreach ($cabecalhos as $c): ?>
                <tr><td><?= e($c['nome']) ?></td><td class="copiavel" tabindex="0" title="Clique para copiar"><?= e($c['valor']) ?></td></tr>
<?php endforeach; ?>
            </tbody>
        </table>
        <p class="muted small">Cookies e credenciais não são exibidos. Nada desta página é guardado.</p>
    </section>

    <section class="texto">
        <h2>O que é o user agent</h2>
        <p>É um texto que o navegador envia a cada site, dizendo qual é o navegador, a versão e o sistema operacional. Os sites usam isso para adaptar a página, por exemplo para mostrar a versão de celular.</p>

        <h2>Principais cabeçalhos</h2>
        <dl>
            <dt>User-Agent</dt>
            <dd>Navegador, versão e sistema operacional.</dd>
            <dt>Accept-Language</dt>
            <dd>Idiomas preferidos, em ordem. É por ele que um site abre em português.</dd>
            <dt>Accept-Encoding</dt>
            <dd>Formatos de compressão que o navegador aceita, como gzip e br.</dd>
            <dt>Sec-Ch-Ua</dt>
            <dd>Versão resumida do user agent, usada pelos navegadores baseados no Chromium.</dd>
            <dt>Referer</dt>
            <dd>Página de onde você veio, quando chegou por um link.</dd>
        </dl>

        <h2>Para que serve</h2>
        <ul>
            <li>Informar ao suporte técnico qual navegador e versão você usa.</li>
            <li>Testar se uma extensão ou configuração que troca o user agent está funcionando.</li>
            <li>Depurar um site ou uma API que se comporta de forma diferente conforme o navegador.</li>
        </ul>
    </section>

    <?php pagina_relacionadas('meu-user-agent'); ?>
</article>
<?php pagina_rodape(false);
