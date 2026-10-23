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
   provedor (ISP + ASN), botão "Copiar IP". Mostrar IPv6 quando houver.
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
- Guardar ou vazar IPs de visitantes.
- Página lenta: o IP precisa aparecer na primeira renderização.

## Restrições

- Domínio `vermeuip.com.br` já registrado no **Registro.br** (o DNS será
  apontado à hospedagem; fazer logins/alterações no Registro.br é do dono).
- Repositório: github.com/thiagomargatho/vermeuip (privado), pasta
  `C:\Projetos\vermeuip`.
- Custo inicial próximo de zero.

## Decisões de "como" — todas escolhas do Claude (o dono delegou; podem ser revistas)

- **Stack**: Next.js (App Router) + TypeScript, hospedado na **Vercel**; rotas
  de API em funções Node. Domínio e `www` apontados à Vercel via registros
  DNS no Registro.br.
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
- **Velocidade**: medido no navegador (download, upload, latência), contra
  endpoints de teste públicos; deixar claro que é uma estimativa.
- **Privacidade**: sem cookies, sem analytics de terceiros, sem logs próprios
  de IP. Aviso curto no rodapé.
- **Tema**: segue o sistema (claro/escuro); o padrão e o protótipo são claros.

## Decisões do Claude após a revisão do construtor (marcadas como escolha minha)

- **Teste de portas na Vercel**: aceitar o limite. Em CGNAT/firewall o resultado
  será "fechada ou filtrada", com aviso claro na tela; sem servidor extra (VPS)
  na v1. Se isso incomodar no uso real, a v2 troca por uma VPS pequena.
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
- **Deploy**: o Claude entrega o código e faz o deploy usando o login do dono;
  o dono faz login na Vercel e altera o DNS no Registro.br.

## Deixado ao construtor

Escolha da base/serviço de geolocalização, limites exatos de taxa, estrutura de
pastas, testes, textos de erro, SEO básico (título, descrição), favicon, e como
medir a velocidade dentro dos limites da Vercel.
