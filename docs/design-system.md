# Design system — vermeuip.com.br

Fonte única: `public/assets/app.css`. Sem framework; tema claro/escuro por tokens.

## Tokens

| Grupo | Tokens | Regra |
|---|---|---|
| Cor | `--bg --card --tx --mut --line --ac --acbg --ok --okbg --bad --badbg --btntx` | Cada cor é definida **uma vez por tema** (`--l-*` claro, `--d-*` escuro). Componentes só usam os alias sem prefixo |
| Espaçamento | `--sp-1`…`--sp-9` = 4, 8, 12, 16, 24, 32, 40, 48, 56 px | Nunca valores soltos em `padding`, `margin` ou `gap` |
| Fonte | `--fs-xs` 12, `-sm` 13, `-md` 14, `-lg` 16, `-xl` 18, `-display` 36 px | O IP grande usa `clamp(30px,8vw,64px)` |
| Raio | `--r-md` 8 px, `--r-lg` 12 px (cartões), `--r-pill` 99 px | |
| Sombra | `--sh-toast` | Só o aviso flutua |
| Movimento | `--t-fast` .15 s | Desligado com `prefers-reduced-motion: reduce` |
| Pontos de quebra | 860 px (tablet), 520 px (celular) | `@media` não aceita `var()`; valores fixos |

**Tema:** `color-scheme` + `light-dark()`. Segue o sistema; o botão do topo grava `data-theme="light|dark"` no `<html>` e `localStorage.tema`. Navegador sem `light-dark()` cai no tema claro.

**Contraste (WCAG AA ≥ 4,5:1):** todos os pares texto/fundo passam nos dois temas. O mais baixo é `--bad` sobre `--badbg` no claro (5,45:1). Ao mudar uma cor, rode a conta antes de publicar.

## Componentes

| Componente | Classe / marca | Quando usar | Estados |
|---|---|---|---|
| Botão | `.btn` | Ação principal (Testar, Consultar, Copiar IP, Iniciar teste) | hover, `:focus-visible`, `:disabled` (opacidade .6, cursor de espera) |
| Seletor de tema | `.tema` | Um por página, no cabeçalho | hover, foco, `aria-pressed` |
| Chip | `.chip` | Atalho de valor (portas 22, 80, 443…) | hover, foco |
| Ferramenta (cartão) | `.ferramentas` > `.ferramenta[role=tab]` com `.ferramenta-icone`, `-nome`, `-desc` | Escolher uma das 4 ferramentas, sem recarregar. Faz o papel de aba: o painel `[role=tabpanel]` correspondente aparece embaixo | hover (borda), `aria-selected` (fundo e borda no azul), foco, setas/Home/End. Grade 4 → 2 colunas (860 px) |
| Campo | `input`, `select` | Entrada de texto/escolha | foco (`:focus-visible`) |
| Tabela | `table` | Resultado de DNS, whois, portas | — |
| Valor copiável | `.copiavel`, `td.copiavel`, `.big.copiavel`, `#ip` | Um clique copia; `data-copia` sobrepõe o texto | hover, foco, Enter/Espaço |
| Etiqueta | `.tag` (verde) / `.tag.bad` (vermelha) | Estado curto: "Aberta", "Fechada ou filtrada" | — |
| Erro | `.erro` (`role=alert`) | Falha de consulta, em português e acionável | — |
| Métrica | `.metrics .big` | Números do teste de velocidade | hover quando copiável |
| Aviso | `.aviso` (`role=status`) | Confirmação curta ("Copiado: …"), some em 1,6 s | — |
| Rodapé | `.rodape` | Marca, ferramentas, privacidade, contato | 4 colunas → 2 (860 px) → 1 (520 px) |

## Acessibilidade

- Foco visível em todo controle (`outline: 2px solid var(--ac)`).
- Abas com `role=tablist/tab/tabpanel` e `aria-selected`; resultados em `aria-live=polite`.
- Valores copiáveis têm `tabindex=0`, `role=button` e `title`.
- Sem animação para quem pede menos movimento; rolagem do rodapé respeita isso também.

## Regras ao mexer

1. Cor, espaço, fonte, raio e sombra vêm de token. Sem hex nem `px` soltos nos componentes.
2. Nome de classe novo em português, curto, sem prefixo.
3. Componente novo entra nesta tabela com os estados que tiver.
4. Toda cor nova é testada nos dois temas (contraste ≥ 4,5:1).
