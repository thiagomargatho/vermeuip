<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../_lib/layout.php';

$caminho = '/teste-de-ping/';
$descricao = 'Teste o ping da sua internet com um clique: latência, jitter, mínimo e máximo. Veja se a conexão está boa para jogos online e videochamadas. Grátis e sem anúncios.';

pagina_topo([
    'titulo' => 'Teste de ping: meça a latência e o jitter da sua internet',
    'descricao' => $descricao,
    'caminho' => $caminho,
    'logo_no_topo' => true,
    'jsonld' => jsonld_ferramenta('Teste de ping', $descricao, $caminho),
]);
?>
<article class="pagina">
    <h1>Teste de ping</h1>
    <p class="lead">Meça o ping e o jitter da sua internet com um clique. O teste leva poucos segundos.</p>

    <div class="velocidade" id="painel-ping">
        <button class="btn-speed" id="iniciar-ping" type="button">Testar ping</button>
        <div class="out" id="out-ping" aria-live="polite"></div>
    </div>

    <section class="texto">
        <h2>O que é ping</h2>
        <p>Ping, ou latência, é o tempo que um dado leva para ir do seu aparelho até um servidor e voltar. É medido em milissegundos (ms). Quanto menor, mais rápida é a resposta da conexão.</p>

        <h2>Qual ping é bom</h2>
        <table>
            <thead><tr><th>Ping</th><th>Como é na prática</th></tr></thead>
            <tbody>
                <tr><td>Até 20 ms</td><td>Excelente. Ideal para jogos competitivos.</td></tr>
                <tr><td>20 a 50 ms</td><td>Bom. Jogos online e videochamadas funcionam bem.</td></tr>
                <tr><td>50 a 100 ms</td><td>Aceitável. Jogos rápidos podem ter atraso.</td></tr>
                <tr><td>Acima de 100 ms</td><td>Alto. Atraso perceptível em jogos e chamadas.</td></tr>
            </tbody>
        </table>

        <h2>O que é jitter</h2>
        <p>Jitter é o quanto o ping varia de uma medição para a outra. Um ping baixo com jitter alto ainda causa travadas em chamadas de voz e em jogos. O ideal é ficar abaixo de 10 ms.</p>

        <h2>Como este teste mede</h2>
        <p>O navegador faz 20 requisições pequenas ao servidor da Cloudflare mais próximo de você e mede o tempo de cada uma. O resultado costuma ficar um pouco acima do comando <code>ping</code> do computador, porque o navegador usa HTTP e não ICMP. O ping até o servidor de um jogo específico pode ser diferente, pois depende de onde esse servidor fica.</p>

        <h2>Como baixar o ping</h2>
        <ul>
            <li>Use cabo de rede em vez de Wi-Fi.</li>
            <li>No Wi-Fi, chegue perto do roteador e prefira a rede de 5 GHz.</li>
            <li>Feche downloads, streaming e atualizações em outros aparelhos.</li>
            <li>Reinicie o roteador. Se o ping continuar alto em vários horários, fale com o provedor.</li>
        </ul>
        <p class="muted small">Quer medir também download e upload? Use o <a href="/teste-de-velocidade/">teste de velocidade</a>.</p>
    </section>

    <?php pagina_relacionadas('teste-de-ping'); ?>
</article>
<?php pagina_rodape(true);
