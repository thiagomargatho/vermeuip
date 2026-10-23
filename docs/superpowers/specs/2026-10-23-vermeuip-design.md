# vermeuip.com.br — design

## Intenção e porquê

Hoje o dono usa meuip.com.br para descobrir o próprio IP e acha a página
poluída (anúncios, excesso de elementos). A alternativa que conhece,
whatismyip.com, é limpa mas toda em inglês. O vermeuip.com.br é um site
**em português, limpo e rápido**, feito para **pessoas de TI** que precisam
de algumas ferramentas de rede no dia a dia, sem ruído.

Sucesso = abrir a página e ver o IP imediatamente, e conseguir usar qualquer
das ferramentas em poucos cliques, sem anúncio, cadastro ou distração.

## Escopo (decidido pelo dono)

Página única, layout **A** (protótipo aprovado: `.superpowers/brainstorm/2545-1792738510/content/prototipos.html`, aba "A · Abas").

1. **Topo**: IP do visitante grande, localização aproximada (cidade/UF/país),
   provedor (ISP + ASN), IP reverso (DNS PTR, ex.: ip-177-76-43-250.user.vivozap.com.br),
   botão "Copiar IP", e link discreto "Ver no mapa" ao lado da localização
   (abre o OpenStreetMap nas coordenadas; sem mapa embutido, por peso e
   privacidade). Mostrar IPv6 quando houver. (PTR adicionado após
   ver o meuip.com.br, que o exibe.)
2. **Abas** abaixo do topo, sem recarregar a página:
   - Teste de portas
   - Consulta DNS
   - Whois de domínio
   - Teste de velocidade

Aparência: clara, minimalista, muito espaço em branco, fonte do sistema,
responsiva (celular incluso). Idioma: português do Brasil.

## Não objetivos

Contas/login, histórico, anúncios, rastreadores, API pública, outros idiomas
(v1), ferramentas além das quatro acima.

## Anti-objetivos (seria fracasso mesmo funcionando)

- Poluição visual ou qualquer anúncio.
- Ferramenta que sirva para atacar terceiros (varredura de portas de IPs
  alheios).
- Guardar ou vazar IPs de visitantes (o Clarity, por ser terceiro, deve ser
  configurado para mascarar conteúdo, incluindo o IP exibido na página).
- Página lenta: o IP precisa aparecer na primeira renderização.

## Restrições

- Domínio `vermeuip.com.br` já registrado no **Registro.br** (o DNS será
  apontado à hospedagem; fazer logins/alterações no Registro.br é do dono).
- Repositório: github.com/thiagomargatho/vermeuip (privado), pasta
  `C:\Projetos\vermeuip`.
- Custo inicial próximo de zero.

## Decisões de "como" — todas escolhas do Claude (o dono delegou; podem ser revistas)

- **Hospedagem (decisão do dono)**: servidor próprio com **cPanel**
  (hospedagem compartilhada), não Vercel. Isso substitui a escolha anterior.
- **Stack** (minha escolha, por ser o que roda em qualquer cPanel): **PHP 8**
  para as rotas de API + HTML/CSS/JS puro no navegador, sem build obrigatório
  no servidor. O IP vem renderizado em PHP na primeira resposta. O domínio
  `vermeuip.com.br` e `www` apontam ao host via DNS no Registro.br (ou pelos
  nameservers do host).
- **IP**: lido no servidor pelos cabeçalhos da requisição; nunca por serviço
  externo chamado do navegador.
- **Localização/provedor**: consulta feita no servidor a uma base ou serviço
  gratuito de geolocalização de IP; mostrar sempre como "aproximada". Sem
  guardar nada.
- **Teste de portas**: só contra o **IP do próprio visitante**, nunca host
  digitado. Porta 1–65535, com atalhos para as comuns (22, 80, 443, 3389…).
  Limite de requisições por IP. Timeout curto.
- **DNS**: tipos A, AAAA, MX, TXT, NS, CNAME, SOA para qualquer domínio.
- **Whois**: via RDAP (rdap.registro.br para `.br`; bootstrap RDAP para os
  demais), exibindo titular quando público, datas, status e servidores DNS.
- **Velocidade (decidido com o dono)**: biblioteca open source
  `@cloudflare/speedtest` (MIT), medindo no navegador contra a rede da
  Cloudflare (download, upload, latência, jitter); deixar claro que é uma
  estimativa.
- **Velocidade, plano B (dono quer testar também)**: LibreSpeed (open source,
  backend PHP) hospedado no próprio cPanel. Construir os dois por trás de uma
  chave simples (ex.: constante de configuração), comparar os resultados no
  host real e manter o melhor como padrão. O plano B mede até o host, limitado
  pela banda/CPU dele; documentar essa diferença.
- **Analytics (pedido do dono)**: Microsoft Clarity, projeto `z23oq7eebz`,
  com o snippet oficial abaixo colado no `<head>` de todas as páginas. O dono
  decidiu usá-lo, o que substitui o "sem cookies / sem analytics de terceiros"
  anterior. **O dono decidiu que o Clarity carrega sempre, sem banner de
  consentimento** (risco LGPD avisado e aceito por ele). O rodapé deve
  mencionar o uso do Clarity e dos cookies. Sem logs próprios de IP.

```html
<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "z23oq7eebz");
</script>
```
- **Tema**: segue o sistema (claro/escuro); o padrão e o protótipo são claros.

## Decisões do Claude após a revisão do construtor (marcadas como escolha minha)

- **Teste de portas**: aceitar o limite. Em CGNAT/firewall o resultado
  será "fechada ou filtrada", com aviso claro na tela; sem servidor extra
  (VPS) na v1.
- **SSRF/abuso**: bloquear IPs privados, reservados e loopback em qualquer
  entrada (DNS, whois, portas). Teste de portas continua só no IP do visitante.
- **Limite de taxa**: contador efêmero por IP com validade de minutos, melhor
  esforço (memória, ou KV gratuito se necessário). Não é log; texto de
  privacidade diz isso.
- **DNS**: resolver pelo servidor (resolvedor padrão); mostrar TTL.
- **Velocidade**: pode depender de endpoint público de terceiro (ex.: Cloudflare).
  O rodapé informa que o teste de velocidade contata esse terceiro.
- **Geolocalização**: base local embutida (DB-IP Lite, com atribuição no
  rodapé) em vez de serviço externo, para não enviar o IP do visitante a
  terceiros. Se ISP/ASN vier vazio, o topo mostra só IP e localização.
- **Renderização**: IP aparece na primeira resposta do servidor (SSR dinâmico);
  localização/provedor podem chegar logo depois (streaming) sem atrasar o IP.
- **Rodapé**: texto curto de privacidade, atribuição da base de IP e contato
  `contato@vermeuip.com.br` (informado pelo dono; a caixa de e-mail ainda
  precisa existir); sem página de política separada na v1.
- **Deploy**: o Claude entrega o código pronto para subir no cPanel (pasta
  `public_html`, via Gerenciador de Arquivos, FTP ou Git do cPanel). O dono
  faz os logins no cPanel e no Registro.br e cria a caixa
  `contato@vermeuip.com.br`.
- **Verificar no host real (incógnitas)**: versão do PHP, se a hospedagem
  permite conexões de saída (`fsockopen`) para o teste de portas, e limites de
  banda/tempo para o teste de velocidade. O teste de portas deve falhar com
  mensagem clara se o host bloquear conexões de saída.

## Deixado ao construtor

Escolha da base/serviço de geolocalização, limites exatos de taxa, estrutura de
pastas, testes, textos de erro, SEO básico (título, descrição), favicon, e como
medir a velocidade dentro dos limites da hospedagem compartilhada.
