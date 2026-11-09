<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../_lib/layout.php';

$caminho = '/propagacao-dns/';
$descricao = 'Verifique a propagação de DNS: compare a resposta de vários resolvedores públicos (Google, Cloudflare e outros) para os registros A, AAAA, CNAME, MX, TXT e NS de um domínio.';

pagina_topo([
    'titulo' => 'Propagação de DNS: veja se o seu domínio já propagou',
    'descricao' => $descricao,
    'caminho' => $caminho,
    'logo_no_topo' => true,
    'jsonld' => jsonld_ferramenta('Propagação de DNS', $descricao, $caminho),
]);
?>
<article class="pagina">
    <h1>Propagação de DNS</h1>
    <p class="lead">Mudou o DNS de um domínio? Veja se os grandes resolvedores públicos já respondem com o valor novo.</p>

    <section class="panel" id="painel-prop" aria-labelledby="t-prop">
        <h2 id="t-prop" class="sr-only">Verificar propagação</h2>
        <form class="row" id="form-prop">
            <input name="dominio" placeholder="exemplo.com.br" aria-label="Domínio" autocapitalize="none" spellcheck="false">
            <select name="tipo" aria-label="Tipo de registro">
                <?php foreach (['A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS'] as $t): ?>
                    <option><?= $t ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn" type="submit">Verificar</button>
        </form>
        <div class="out" id="out-prop" aria-live="polite"></div>
    </section>

    <section class="texto">
        <h2>Como funciona</h2>
        <p>A consulta pergunta o mesmo registro a quatro resolvedores públicos: Google, Cloudflare, AdGuard e DNS.SB. Se todos devolvem o mesmo valor, a mudança já chegou a eles. Se as respostas diferem, parte da internet ainda enxerga o valor antigo.</p>

        <h2>Quanto tempo leva</h2>
        <p>Depende do TTL do registro antigo, que é o tempo que cada resolvedor guarda a resposta em cache. Com TTL de 3600, a troca pode levar até uma hora. Mudança de servidores de nomes (NS) costuma demorar mais, de algumas horas a um dia.</p>

        <h2>Limites desta consulta</h2>
        <ul>
            <li>Ela mostra o que os resolvedores respondem a partir do nosso servidor. O provedor da sua casa pode ter um cache próprio, ainda com o valor antigo.</li>
            <li>Para ver os registros direto no servidor do domínio, use a <a href="/consulta-dns/">consulta DNS</a>.</li>
        </ul>
    </section>

    <?php pagina_relacionadas('propagacao-dns'); ?>
</article>
<?php pagina_rodape(false);
