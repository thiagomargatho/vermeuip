# Design system — VerMeuIP.com.br

Fonte única: `public/assets/app.css`. Sem framework. Identidade visual aprovada em `docs/marca/identidade-visual.webp`.

## Princípios da página

1. Duas coisas em primeiro plano: **o IP** e **o botão "Testar velocidade"**. O resto fica recolhido ("Outras ferramentas").
2. Azul-marinho predominante. O **gradiente é reservado** ao botão de velocidade e a pequenos detalhes (faixa do topo).
3. Muito espaço em branco, poucos elementos, sem cartões, ícones decorativos nem animações.
4. Celular primeiro: o IP e o botão aparecem na primeira tela.

## Logo

| Arquivo | Uso |
|---|---|
| `assets/marca/logo.png` | Logo completo, tema **claro** |
| `assets/marca/logo-escuro.png` | Variação oficial para fundo **escuro** (aparece no tema escuro) |
| `assets/marca/marca.png` | Só o símbolo "IP" (base dos ícones) |
| `favicon-32.png`, `favicon-192.png`, `apple-touch-icon.png`, `og.png` | Ícones e imagem de compartilhamento |

Os arquivos são **recortes da arte aprovada**, gerados por `php scripts/gerar-logo.php` (fundo removido, sem redesenhar nem usar texto comum). Se houver o arquivo vetorial/PNG original em alta resolução, substitua `logo*.png` por ele: a variação escura é a mesma arte do logo claro com o azul-marinho trocado por branco (mesma resolução, 645 px).

## Tokens

| Grupo | Tokens | Regra |
|---|---|---|
| Marca | `--primary #0B1F44`, `--yellow #FFB800`, `--orange #FF7A00`, `--red #FF3B2E`, `--pink #E8006D`, `--brand-gradient`, `--on-brand #081429` | Gradiente: `linear-gradient(90deg, #FFB800 0%, #FF7A00 35%, #FF3B2E 70%, #E8006D 100%)`. Texto sobre ele: `--on-brand` |
| Cor (tema) | `--background --surface --text --muted --line --ac --btntx --ok --okbg --bad --badbg` | Cada cor é definida **uma vez por tema** (`--l-*` claro, `--d-*` escuro); componentes só usam os alias sem prefixo |
| Espaçamento | `--sp-1`…`--sp-9` = 4, 8, 12, 16, 24, 32, 40, 48, 64 px | Nunca valores soltos em `padding`, `margin` ou `gap` |
| Fonte | `--fs-xs` 12, `-sm` 13, `-md` 14, `-lg` 16, `-xl` 20, `-display` 36 px | Família: `Inter, system-ui…` (sem fonte externa, por privacidade e velocidade) |
| Raio | `--r-md` 8, `--r-lg` 12, `--r-pill` 99 px | |
| Sombra | `--sh-toast`, `--sh-cta` | Só o aviso e o botão de velocidade |
| Movimento | `--t-fast` .15 s | Desligado com `prefers-reduced-motion: reduce` |
| Ponto de quebra | 520 px (celular) | `@media` não aceita `var()`; valor fixo |

**Paleta**

| | Claro | Escuro |
|---|---|---|
| Fundo | `#FFFFFF` | `#081429` |
| Superfície | `#F5F7FA` | `#102344` |
| Texto | `#0B1F44` | `#FFFFFF` |
| Texto secundário | `#6B7280` | `#A8B3C5` |
| Acento (foco, botão `.btn`) | `#0B1F44` | `#FFB800` |

**Tema:** `color-scheme` + `light-dark()`. Segue o sistema; o botão do topo grava `data-theme="light|dark"` no `<html>` e `localStorage.tema`. Sem `light-dark()` (navegador antigo), cai no tema claro.

**Contraste (WCAG AA ≥ 4,5:1):** todos os pares texto/fundo passam nos dois temas; o mais baixo é o texto secundário sobre a superfície no claro (4,5:1), por isso ele só é usado sobre o fundo. Texto `--on-brand` sobre o gradiente: 10,6 / 7,0 / 5,2 / 4,1 (texto grande, mínimo 3:1). Ao mudar uma cor, refaça a conta.

## Componentes

| Componente | Classe / marca | Quando usar | Estados |
|---|---|---|---|
| IP | `.ip-address.copiavel#ip` | O IP do visitante, grande (`clamp(36px, 7vw, 72px)`); um clique copia | hover (cor), foco, Enter/Espaço |
| Copiar IP | `.btn-copiar` | Botão secundário (contorno) ao lado do IP | hover, foco |
| **Testar velocidade** | `.btn-speed` | Ação principal da página, gradiente oficial. Texto: "Testar velocidade" → "Medindo…" → "Testar novamente" | hover, `:active`, `:disabled`, foco |
| Métricas | `.metrics .big` | Download, upload, latência e jitter sob o botão; clique copia o número | hover, foco |
| Outras ferramentas | `.outras-nav [role=tab]` (pílulas) + `.panel[role=tabpanel]` | Teste de portas, consulta DNS e whois, **recolhidos** até o clique; clicar de novo recolhe | hover, `aria-selected` (pílula azul-marinho), foco, setas/Home/End |
| Botão de formulário | `.btn` | Testar, Consultar | hover, foco, `:disabled` |
| Chip | `.chip` | Atalho de porta (22, 80, 443…) | foco |
| Campo | `input`, `select` | Entrada de texto/escolha | foco |
| Tabela | `table` | Resultados de DNS, whois, portas; células de valor copiam ao clique | hover |
| Etiqueta | `.tag` / `.tag.bad` | Estado curto: "Aberta", "Fechada ou filtrada" | — |
| Erro | `.erro` (`role=alert`) | Falha de consulta, em português e acionável | — |
| Aviso | `.aviso` (`role=status`) | Confirmação curta ("Copiado: …") | — |
| Seletor de tema | `.tema` | Um por página, no topo | hover, foco, `aria-pressed` |
| Rodapé | `.rodape` | © e contato, mais a nota de privacidade e créditos, em duas linhas | — |

## Acessibilidade

- Foco visível em todo controle (`outline: 2px solid var(--ac)`).
- Outras ferramentas com `role=tablist/tab/tabpanel` e `aria-selected`; resultados em `aria-live=polite`.
- Valores copiáveis têm `tabindex=0`, `role=button` e `title`.
- Sem animação para quem pede menos movimento.
- Imagem do logo com `alt="VerMeuIP"`; só a variação do tema ativo é exibida.

## Regras ao mexer

1. Cor, espaço, fonte, raio e sombra vêm de token. Sem hex nem `px` soltos nos componentes.
2. O gradiente só vai em botão principal e pequenos detalhes. Nunca em texto corrido, fundos grandes ou cartões.
3. Componente novo entra nesta tabela com os estados que tiver.
4. Toda cor nova é testada nos dois temas (contraste ≥ 4,5:1).
5. Antes de acrescentar algo à página inicial, pergunte: ajuda a ver o IP ou a testar a velocidade? Se não, vai para "Outras ferramentas".
