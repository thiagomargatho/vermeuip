# Divulgação do VerMeuIP

Textos prontos para publicar. Links de outros sites para o `vermeuip.com.br` são o que mais pesa para o Google confiar em um domínio novo. Um ou dois posts bons valem mais que muitos posts repetidos: publique cada texto em um lugar só e responda os comentários.

Regras de cada comunidade mudam. Antes de postar, confira se ela aceita divulgação de projeto próprio.

## Onde publicar

| Lugar | O que funciona lá |
|---|---|
| TabNews (tabnews.com.br) | Post contando como o projeto foi feito. É o mais indicado para começar. |
| Reddit r/brdev | Post no tópico de projetos pessoais, quando houver. |
| Viva o Linux, fóruns de provedores (ISP) | A ferramenta de CGNAT e o teste de portas, que resolvem dúvida frequente. |
| Grupos de TI no WhatsApp, Telegram e LinkedIn | Texto curto com o link. |
| Perfil do GitHub e do LinkedIn | Link fixo para o site. |

## Texto longo (TabNews, r/brdev)

**Título:** Fiz um "qual é o meu IP" em português, sem anúncios e de código aberto

Eu usava sites de "meu IP" quase todo dia no trabalho e me incomodava com a quantidade de anúncio. Os bons que conheço estão em inglês. Então fiz o meu: https://vermeuip.com.br

O que ele faz:

- Mostra o IP na hora, com localização aproximada, provedor e IP reverso. Um clique copia.
- Teste de velocidade com um clique.
- Teste de portas no seu próprio IP.
- Teste de CGNAT: você informa o IP WAN do roteador e ele diz se o provedor usa CGNAT.
- Consulta DNS, propagação de DNS em vários resolvedores públicos e whois.
- Localizar qualquer IP e ver o user agent e os cabeçalhos do navegador.

Como foi feito: PHP 8 com HTML, CSS e JavaScript puros, sem framework e sem etapa de build. Roda em hospedagem compartilhada comum. Não guarda o IP de ninguém e tem tema claro e escuro.

O código está em https://github.com/thiagomargatho/vermeuip

Críticas e sugestões de ferramenta são bem-vindas.

## Texto curto (grupos e LinkedIn)

Fiz um site de "qual é o meu IP" em português e sem anúncios: https://vermeuip.com.br

Além do IP, tem teste de velocidade, teste de portas, teste de CGNAT, consulta e propagação de DNS e whois. É gratuito e de código aberto. Se faltar alguma ferramenta, me avise.

## Resposta para dúvidas de CGNAT e portas (fóruns)

Use só quando a pessoa perguntou algo que a ferramenta resolve.

Para saber se é CGNAT, compare o IP WAN que aparece no roteador com o IP que a internet enxerga. Se o do roteador estiver entre 100.64.0.0 e 100.127.255.255, é CGNAT e não adianta abrir porta no roteador. Este teste faz a comparação: https://vermeuip.com.br/cgnat/
