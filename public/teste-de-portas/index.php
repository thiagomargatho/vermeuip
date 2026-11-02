<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../_lib/layout.php';

$ctx = pagina_contexto();
$caminho = '/teste-de-portas/';
$descricao = 'Teste de portas online: veja se uma porta do seu IP (22, 80, 443, 3389 e outras) está aberta para a internet. Grátis e em português.';

pagina_topo([
    'titulo' => 'Teste de portas online: veja se uma porta está aberta',
    'descricao' => $descricao,
    'caminho' => $caminho,
    'logo_no_topo' => true,
    'jsonld' => jsonld_ferramenta('Teste de portas online', $descricao, $caminho),
]);
?>
<article class="pagina">
    <h1>Teste de portas online</h1>
    <p class="lead">Verifique se uma porta TCP do seu endereço IP está aberta para a internet.</p>
    <p class="muted">Seu IP: <span class="copiavel" id="ip" data-clarity-mask="true" tabindex="0" role="button" title="Clique para copiar"><?= $ctx['ip'] !== '' ? e($ctx['ip']) : '—' ?></span></p>

    <section class="panel" id="painel-portas" aria-labelledby="t-portas">
        <h2 id="t-portas" class="sr-only">Testar uma porta</h2>
        <form class="row" id="form-portas">
            <input name="porta" inputmode="numeric" value="443" aria-label="Porta" class="porta">
            <button class="btn" type="submit">Testar</button>
        </form>
        <p class="atalhos small">Atalhos:
            <?php foreach ([22, 25, 53, 80, 443, 3306, 3389] as $p): ?>
                <button type="button" class="chip" data-porta="<?= $p ?>"><?= $p ?></button>
            <?php endforeach; ?>
        </p>
        <div class="out" id="out-portas" aria-live="polite"></div>
    </section>

    <section class="texto">
        <h2>Para que serve</h2>
        <p>O teste tenta uma conexão TCP com a porta escolhida no seu IP público, vinda dos nossos servidores. Serve para conferir se um servidor, câmera, jogo ou acesso remoto está acessível de fora da sua rede. Por segurança, só testamos o IP de quem está visitando a página.</p>

        <h2>Portas mais comuns</h2>
        <table>
            <thead><tr><th>Porta</th><th>Serviço</th></tr></thead>
            <tbody>
                <tr><td>22</td><td>SSH (acesso remoto seguro)</td></tr>
                <tr><td>25</td><td>SMTP (envio de e-mail)</td></tr>
                <tr><td>53</td><td>DNS</td></tr>
                <tr><td>80</td><td>HTTP (sites sem criptografia)</td></tr>
                <tr><td>443</td><td>HTTPS (sites seguros)</td></tr>
                <tr><td>3306</td><td>MySQL</td></tr>
                <tr><td>3389</td><td>Área de trabalho remota do Windows (RDP)</td></tr>
            </tbody>
        </table>

        <h2>Por que a porta aparece como fechada</h2>
        <ul>
            <li>Nenhum programa está escutando naquela porta.</li>
            <li>O firewall do computador ou do roteador bloqueia a conexão.</li>
            <li>A porta não foi redirecionada (port forwarding) no roteador.</li>
            <li>Seu provedor usa CGNAT: vários clientes compartilham o mesmo IP público e não é possível abrir portas de fora. Nesse caso, peça ao provedor um IP público.</li>
        </ul>
        <p class="muted small">Quando o resultado é "fechada ou filtrada", não dá para distinguir entre as duas situações.</p>
    </section>

    <?php pagina_relacionadas('teste-de-portas'); ?>
</article>
<?php pagina_rodape(false);
