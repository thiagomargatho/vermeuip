<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../_lib/layout.php';

$caminho = '/localizar-ip/';
$descricao = 'Localize qualquer endereço IP: cidade, estado, país, provedor (ASN) e IP reverso. Consulta grátis, em português e sem anúncios.';

pagina_topo([
    'titulo' => 'Localizar IP: cidade, país e provedor de qualquer endereço IP',
    'descricao' => $descricao,
    'caminho' => $caminho,
    'logo_no_topo' => true,
    'jsonld' => jsonld_ferramenta('Localizar IP', $descricao, $caminho),
]);
?>
<article class="pagina">
    <h1>Localizar IP</h1>
    <p class="lead">Veja a cidade, o país e o provedor de qualquer endereço IP público, IPv4 ou IPv6.</p>

    <section class="panel" id="painel-ip" aria-labelledby="t-ip">
        <h2 id="t-ip" class="sr-only">Consultar um IP</h2>
        <form class="row" id="form-ip">
            <input name="ip" placeholder="8.8.8.8" aria-label="Endereço IP" autocapitalize="none" spellcheck="false">
            <button class="btn" type="submit">Localizar</button>
        </form>
        <div class="out" id="out-ip" aria-live="polite"></div>
    </section>

    <section class="texto">
        <h2>O que a consulta mostra</h2>
        <ul>
            <li>Cidade, estado e país aproximados do endereço.</li>
            <li>Provedor de internet e o número do sistema autônomo (ASN).</li>
            <li>IP reverso (PTR), quando o dono do endereço configurou um nome.</li>
        </ul>

        <h2>A localização é exata?</h2>
        <p>Não. A localização por IP indica a região onde o provedor registrou aquele bloco de endereços. É comum apontar a cidade vizinha ou a capital do estado. O IP não revela rua, casa nem o nome de quem usa a conexão. Só o provedor tem esse dado.</p>

        <h2>Para que serve</h2>
        <ul>
            <li>Conferir de onde veio um acesso que aparece em um log ou alerta de segurança.</li>
            <li>Descobrir qual provedor ou serviço de hospedagem está por trás de um IP.</li>
            <li>Verificar se uma VPN está mesmo saindo pelo país escolhido.</li>
        </ul>
        <p class="muted small">Quer ver o seu próprio endereço? Ele aparece na <a href="/">página inicial</a>. Dados de localização por <a href="https://db-ip.com" rel="noopener">IP Geolocation by DB-IP</a>.</p>
    </section>

    <?php pagina_relacionadas('localizar-ip'); ?>
</article>
<?php pagina_rodape(false);
