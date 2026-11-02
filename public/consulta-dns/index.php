<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../_lib/layout.php';

$caminho = '/consulta-dns/';
$descricao = 'Consulta DNS online: veja os registros A, AAAA, MX, TXT, NS, CNAME e SOA de qualquer domínio. Grátis, em português e sem anúncios.';

pagina_topo([
    'titulo' => 'Consulta DNS online: registros A, MX, TXT, NS e mais',
    'descricao' => $descricao,
    'caminho' => $caminho,
    'logo_no_topo' => true,
    'jsonld' => jsonld_ferramenta('Consulta DNS online', $descricao, $caminho),
]);
?>
<article class="pagina">
    <h1>Consulta DNS online</h1>
    <p class="lead">Veja os registros DNS de qualquer domínio. Digite só o domínio, sem http://.</p>

    <section class="panel" id="painel-dns" aria-labelledby="t-dns">
        <h2 id="t-dns" class="sr-only">Consultar registros</h2>
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

    <section class="texto">
        <h2>Tipos de registro</h2>
        <dl>
            <dt>A</dt>
            <dd>Aponta o domínio para um endereço IPv4.</dd>
            <dt>AAAA</dt>
            <dd>Aponta o domínio para um endereço IPv6.</dd>
            <dt>MX</dt>
            <dd>Servidores que recebem o e-mail do domínio, com ordem de prioridade.</dd>
            <dt>TXT</dt>
            <dd>Texto livre. É onde ficam SPF, DKIM, DMARC e verificações de propriedade de serviços como o Google.</dd>
            <dt>NS</dt>
            <dd>Servidores de nomes que respondem pelo domínio.</dd>
            <dt>CNAME</dt>
            <dd>Apelido: faz um nome apontar para outro nome.</dd>
            <dt>SOA</dt>
            <dd>Dados administrativos da zona, como o servidor principal e os tempos de atualização.</dd>
        </dl>

        <h2>Quando usar</h2>
        <ul>
            <li>Conferir se o domínio já aponta para o servidor depois de mudar o DNS.</li>
            <li>Verificar se os registros de e-mail (MX, SPF, DKIM, DMARC) estão corretos.</li>
            <li>Descobrir quais servidores de nomes respondem por um domínio.</li>
        </ul>
        <p class="muted small">Mudanças de DNS podem levar de minutos a algumas horas para se propagar. O TTL indica por quanto tempo o resultado fica em cache.</p>
    </section>

    <?php pagina_relacionadas('consulta-dns'); ?>
</article>
<?php pagina_rodape(false);
