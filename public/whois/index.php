<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../_lib/layout.php';

$caminho = '/whois/';
$descricao = 'Whois de domínio online: datas de registro e expiração, status e servidores de nomes de domínios .com.br, .com e outros. Grátis e em português.';

pagina_topo([
    'titulo' => 'Whois de domínio online: registro, expiração e servidores DNS',
    'descricao' => $descricao,
    'caminho' => $caminho,
    'logo_no_topo' => true,
    'jsonld' => jsonld_ferramenta('Whois de domínio online', $descricao, $caminho),
]);
?>
<article class="pagina">
    <h1>Whois de domínio online</h1>
    <p class="lead">Consulte a data de registro, a expiração, o status e os servidores de nomes de um domínio.</p>

    <section class="panel" id="painel-whois" aria-labelledby="t-whois">
        <h2 id="t-whois" class="sr-only">Consultar domínio</h2>
        <form class="row" id="form-whois">
            <input name="dominio" placeholder="exemplo.com.br" aria-label="Domínio" autocapitalize="none" spellcheck="false">
            <button class="btn" type="submit">Consultar</button>
        </form>
        <div class="out" id="out-whois" aria-live="polite"></div>
    </section>

    <section class="texto">
        <h2>O que o whois mostra</h2>
        <ul>
            <li>Data em que o domínio foi registrado e quando expira.</li>
            <li>Status do domínio, como ativo ou em período de renovação.</li>
            <li>Servidores de nomes (DNS) que respondem por ele.</li>
            <li>Titular, quando a informação é pública. Muitos registros ocultam dados pessoais por privacidade.</li>
        </ul>

        <h2>Como a consulta funciona</h2>
        <p>Usamos o RDAP, o protocolo que substituiu o whois tradicional. Os dados vêm direto do registro de cada domínio, como o Registro.br para domínios .br.</p>
        <p class="muted small">Digite só o domínio, por exemplo exemplo.com.br, sem http:// nem www.</p>
    </section>

    <?php pagina_relacionadas('whois'); ?>
</article>
<?php pagina_rodape(false);
