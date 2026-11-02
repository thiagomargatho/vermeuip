<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../_lib/layout.php';

$ctx = pagina_contexto();
$caminho = '/teste-de-velocidade/';
$descricao = 'Teste a velocidade da sua internet com um clique: download, upload, latência e jitter. Grátis, em português e sem anúncios.';

pagina_topo([
    'titulo' => 'Teste de velocidade da internet: download, upload e ping',
    'descricao' => $descricao,
    'caminho' => $caminho,
    'logo_no_topo' => true,
    'jsonld' => jsonld_ferramenta('Teste de velocidade da internet', $descricao, $caminho),
]);
?>
<article class="pagina">
    <h1>Teste de velocidade da internet</h1>
    <p class="lead">Meça o download, o upload, a latência e o jitter da sua conexão com um clique. O teste leva cerca de 30 segundos.</p>

    <?php bloco_velocidade($ctx['engine']); ?>

    <section class="texto">
        <h2>O que cada número significa</h2>
        <dl>
            <dt>Download</dt>
            <dd>Velocidade para receber dados, em megabits por segundo (Mbps). É o que pesa em vídeos, sites e downloads.</dd>
            <dt>Upload</dt>
            <dd>Velocidade para enviar dados. Importa em videochamadas, backups na nuvem e envio de arquivos.</dd>
            <dt>Latência</dt>
            <dd>Tempo de ida e volta até o servidor de teste, em milissegundos (ms). Quanto menor, mais rápida a resposta, o que ajuda em jogos online e chamadas.</dd>
            <dt>Jitter</dt>
            <dd>Variação da latência. Valores altos causam travadas em chamadas de voz e vídeo, mesmo com boa velocidade.</dd>
        </dl>

        <h2>Como ter um resultado mais confiável</h2>
        <ul>
            <li>Prefira cabo de rede. No Wi-Fi, chegue perto do roteador.</li>
            <li>Feche downloads, streaming e outros aparelhos que usem a rede.</li>
            <li>Repita o teste em horários diferentes: a velocidade varia ao longo do dia.</li>
            <li>Lembre que o plano é contratado em Mbps, mas a velocidade real depende também do servidor de destino.</li>
        </ul>

        <p class="muted small">O teste usa a infraestrutura da Cloudflare e o resultado é uma estimativa: varia com a rede, o aparelho e o servidor de teste.</p>
    </section>

    <?php pagina_relacionadas('teste-de-velocidade'); ?>
</article>
<?php pagina_rodape(true);
