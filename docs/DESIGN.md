---
title: "Design System — SaaS Admin Dashboard"
version: "1.0.0"
type: "design-system"
source: "extraído de referência visual (dashboard de atendimento/call center)"
tags: ["saas", "dashboard", "admin", "light-theme", "minimal"]
target_audience: "IA codificadora / desenvolvedores front-end"
base_unit_px: 4
color_mode: "light"
---

# DESIGN.md

## 1. Identidade Visual Geral

Interface de **painel administrativo SaaS** (estilo CRM / central de atendimento), com estética **clean, minimalista e funcional**, voltada para produtividade e leitura densa de dados.

Características centrais:
- Fundo geral em cinza muito claro, criando contraste suave com cards brancos.
- Cards e painéis flutuantes com sombra sutil, sugerindo camadas (elevação leve, nunca dramática).
- Uso extensivo de **ícones monocromáticos finos** em barra lateral de navegação estreita.
- Hierarquia visual construída por **peso tipográfico e tamanho**, não por cor (cor é reservada para ação e status).
- Uma única cor de destaque (indigo/violeta) usada de forma econômica — apenas em botões primários, links ativos e ícones selecionados.
- Densidade de informação alta (tabelas, listas, formulários compactos) mas com respiro generoso entre blocos.
- Sensação geral: profissional, neutro, "enterprise software", sem elementos decorativos.

## 2. Paleta de Cores

```yaml
colors:
  background:
    page: "#F1F2F6"        # fundo geral da aplicação (cinza-azulado muito claro)
    canvas_secondary: "#F5F6FA"
    card: "#FFFFFF"          # fundo de cards, painéis e modais
    sidebar: "#FFFFFF"

  border:
    default: "#E7E8EF"
    subtle: "#EEEFF3"
    divider: "#ECEDF2"

  text:
    primary: "#1F2233"       # títulos, texto de alta ênfase
    secondary: "#6B6F80"     # texto de suporte, labels
    tertiary: "#A0A3B1"      # placeholders, texto desabilitado
    inverse: "#FFFFFF"       # texto sobre fundo colorido/escuro

  brand:
    primary: "#5B5FEF"       # indigo/violeta — cor de ação principal
    primary_hover: "#4A4EDB"
    primary_active: "#3E42C4"
    primary_soft: "#EDEDFC"  # fundo suave para estados ativos/selecionados

  status:
    success: "#2ECC71"       # dot de "Validation" / confirmações
    success_soft: "#E7F9EE"
    warning: "#F5A623"
    warning_soft: "#FEF3E2"
    error: "#EB5757"
    error_soft: "#FDEAEA"
    info: "#5B5FEF"
    info_soft: "#EDEDFC"

  icon:
    default: "#8A8DA0"
    active: "#5B5FEF"
    on_dark: "#FFFFFF"

  overlay:
    scrim: "rgba(20, 22, 40, 0.4)"
```

Regra de uso: a cor de marca (`brand.primary`) só aparece em **elementos interativos primários** (botão principal, ícone de navegação ativo, link ativo). Todo o restante da interface permanece em tons de cinza/branco.

## 3. Tipografia

```yaml
typography:
  font_family: "'Inter', 'Helvetica Neue', Arial, sans-serif"
  font_source: "arquivos .woff2 da fonte Inter baixados e hospedados localmente em assets/vendor/fonts/, sem uso de Google Fonts (CDN), conforme restrição técnica do FSD (Seção 3)"
  base_size_px: 13

  scale:
    h1: { size: 20, weight: 600, line_height: 1.3 }   # títulos de página (ex: "Service Types")
    h2: { size: 16, weight: 600, line_height: 1.3 }   # títulos de seção/card
    h3: { size: 13, weight: 600, line_height: 1.4 }   # subtítulos, cabeçalhos de tabela
    body: { size: 13, weight: 400, line_height: 1.5 }
    body_small: { size: 12, weight: 400, line_height: 1.4 }
    caption: { size: 11, weight: 500, line_height: 1.3, letter_spacing: "0.02em", transform: "uppercase" } # labels de seção tipo "CALLS STATISTICS"
    button: { size: 13, weight: 500, line_height: 1 }
    breadcrumb: { size: 12, weight: 400, color: "text.secondary" }

  color_default: "text.primary"
  color_muted: "text.secondary"
```

Regra: títulos usam peso 600 sem variação de cor; ênfase é sempre por peso, nunca por cor de texto (exceto estados de erro/sucesso).

## 4. Espaçamentos

Sistema baseado em grade de **4px**.

```yaml
spacing:
  unit: 4
  scale:
    xs: 4
    sm: 8
    md: 12
    lg: 16
    xl: 24
    xxl: 32
    xxxl: 48

  usage:
    card_padding: 20            # padding interno de cards/painéis
    section_gap: 24             # espaço entre seções dentro de um card
    list_item_gap: 8            # espaço entre linhas de lista/tabela
    input_padding_x: 12
    input_padding_y: 8
    button_padding_x: 16
    button_padding_y: 8
    sidebar_icon_gap: 20
    page_margin: 24
```

## 5. Arredondamento de Bordas

```yaml
radius:
  none: 0
  sm: 4        # inputs, badges pequenos, dots
  md: 8        # botões, tabelas, itens de lista
  lg: 12       # cards, painéis, modais
  xl: 16       # painéis flutuantes de destaque (ex: preview de janela)
  pill: 999    # tags/badges de status arredondados totalmente
```

Regra: cards e painéis principais usam `lg` (12px); componentes internos (botões, inputs, linhas de tabela) usam `sm`–`md` (4–8px). Nunca misturar mais de 2 níveis de raio na mesma tela.

## 6. Estilo de Botões

```yaml
buttons:
  primary:
    background: "brand.primary"
    text_color: "#FFFFFF"
    border: "none"
    radius: "md"
    padding: "8px 16px"
    font_weight: 500
    hover: { background: "brand.primary_hover" }
    active: { background: "brand.primary_active" }
    disabled: { background: "#C9CAF0", text_color: "#FFFFFF" }

  secondary:
    background: "#FFFFFF"
    text_color: "text.primary"
    border: "1px solid border.default"
    radius: "md"
    padding: "8px 16px"
    hover: { background: "#F5F6FA" }

  ghost:
    background: "transparent"
    text_color: "text.secondary"
    border: "none"
    hover: { background: "#F1F2F6" }

  danger:
    background: "status.error"
    text_color: "#FFFFFF"
    hover: { background: "#D64545" }

  icon_button:
    size: 32
    radius: "sm"
    background_default: "transparent"
    background_hover: "#F1F2F6"
    icon_color: "icon.default"
    icon_color_active: "icon.active"
```

Regra: apenas **um botão primário por seção/tela** (ex: "Add Attribute"). Ações secundárias usam `secondary` ou `ghost`. Botões de call-to-action urgente (ex: "Edit" em linha de tabela) podem usar `primary` em miniatura contextual.

## 7. Estilo de Formulários

```yaml
forms:
  input:
    height: 36
    background: "#FFFFFF"
    border: "1px solid border.default"
    radius: "sm"
    padding: "8px 12px"
    font_size: 13
    placeholder_color: "text.tertiary"
    focus:
      border_color: "brand.primary"
      shadow: "0 0 0 3px rgba(91,95,239,0.15)"
    disabled:
      background: "#F5F6FA"
      text_color: "text.tertiary"

  select_dropdown:
    same_as: "input"
    chevron_color: "icon.default"

  checkbox:
    size: 16
    radius: 4
    border: "1px solid border.default"
    checked_background: "brand.primary"
    checked_icon_color: "#FFFFFF"

  toggle_switch:
    width: 36
    height: 20
    track_off: "#D9DBE4"
    track_on: "brand.primary"
    thumb: "#FFFFFF"

  search_input:
    icon_position: "right"
    icon_color: "icon.default"
    background: "#FFFFFF"
    border: "1px solid border.default"
    radius: "sm"

  label:
    font_size: 12
    font_weight: 500
    color: "text.secondary"
    margin_bottom: 6
```

## 8. Tabelas, Cards, Menus e Componentes Principais

### Tabelas
```yaml
table:
  header:
    background: "transparent"
    text_color: "text.secondary"
    font_size: 11
    font_weight: 600
    text_transform: "uppercase"
    border_bottom: "1px solid border.default"
    padding: "8px 12px"
  row:
    background: "#FFFFFF"
    border_bottom: "1px solid border.subtle"
    padding: "10px 12px"
    hover_background: "#FAFAFC"
  cell_controls:
    inline_select_height: 32
    inline_toggle: true
    action_icon_color: "icon.default"
  status_dot:
    size: 8
    radius: "pill"
    success_color: "status.success"
```

### Cards / Painéis
```yaml
card:
  background: "card"
  border: "1px solid border.subtle"
  radius: "lg"
  shadow: "0 1px 2px rgba(20,22,40,0.04), 0 4px 12px rgba(20,22,40,0.04)"
  padding: 20
  header:
    font_size: 16
    font_weight: 600
    margin_bottom: 16
  section_label:
    icon_leading: true
    font_size: 11
    font_weight: 600
    text_transform: "uppercase"
    color: "text.secondary"
```

### Sidebar de Navegação (ícones)
```yaml
sidebar:
  width: 56
  background: "#FFFFFF"
  border_right: "1px solid border.default"
  icon_size: 20
  icon_gap: 20
  item_default_color: "icon.default"
  item_active_color: "icon.active"
  item_active_background: "brand.primary_soft"
  item_active_radius: "sm"
  item_tooltip: true   # cada ícone deve exibir tooltip/title com o nome do menu (Painel, Lançamentos, Categorias, Formas de Pagamento, Histórico, Conta) ao passar o mouse, já que a sidebar não exibe texto
```

### Cabeçalho de página / Breadcrumb
```yaml
page_header:
  breadcrumb_color: "text.secondary"
  breadcrumb_separator: ">"
  title_font_size: 20
  title_font_weight: 600
  right_slot: "user menu / seletor de contexto"
```

### Badges / Tags
```yaml
badge:
  radius: "pill"
  padding: "2px 10px"
  font_size: 11
  font_weight: 500
  variants:
    neutral: { background: "#F1F2F6", text: "text.secondary" }
    success: { background: "status.success_soft", text: "status.success" }
    warning: { background: "status.warning_soft", text: "status.warning" }
    error: { background: "status.error_soft", text: "status.error" }
    info: { background: "status.info_soft", text: "status.info" }
```

## 9. Estados Visuais

```yaml
states:
  success:
    color: "status.success"
    background: "status.success_soft"
    icon: "check-circle"
    usage: "confirmações, validações concluídas (ex: dot verde 'Validation')"

  error:
    color: "status.error"
    background: "status.error_soft"
    icon: "alert-circle"
    usage: "falhas de conexão, campos inválidos, ex: 'Connection crashed'"

  warning:
    color: "status.warning"
    background: "status.warning_soft"
    icon: "alert-triangle"
    usage: "avisos não bloqueantes"

  empty_state:
    icon_color: "text.tertiary"
    title_font_weight: 600
    description_color: "text.secondary"
    illustration: "opcional, monocromática, minimalista"
    cta_button: "secondary"

  loading_state:
    skeleton_background: "#EDEEF3"
    skeleton_shimmer: "#F5F6FA"
    spinner_color: "brand.primary"

  disabled_state:
    opacity: 0.5
    cursor: "not-allowed"

  focus_state:
    outline: "none"
    ring: "0 0 0 3px rgba(91,95,239,0.15)"
    ring_color_source: "brand.primary"
```

## 10. Ícones

Biblioteca sugerida: **Feather Icons** ou **Lucide Icons** (traço fino, cantos levemente arredondados, grid 24x24, `stroke-width: 1.5–2px`) — compatível com o estilo observado na imagem de referência.

```yaml
icons:
  library_adotada: "Lucide Icons — SVGs baixados individualmente e hospedados localmente em assets/vendor/icons/, sem uso de CDN, conforme restrição técnica do FSD (Seção 3)"
  style: "outline / stroke, sem preenchimento"
  stroke_width: 1.75
  sizes:
    sidebar_nav: 20
    table_row_action: 16
    inline_field: 14
    header_context: 18
  color_default: "icon.default"
  color_active: "icon.active"
  color_on_dark: "icon.on_dark"
```

### Ícones identificados na imagem de referência

```yaml
icons_used:
  navigation_sidebar:
    - name: "settings"
      symbol: "gear / cog"
      context: "item de navegação principal (Service Types)"
    - name: "file-text"
      symbol: "documento / lista"
      context: "seção de documentos/registros"
    - name: "alert-triangle"
      symbol: "triângulo de alerta"
      context: "seção de alertas/avisos"
    - name: "users"
      symbol: "grupo de pessoas"
      context: "seção de usuários/equipe"

  generic_ui:
    - name: "x / close"
      symbol: "X"
      context: "fechar painel, remover linha de tabela/lista"
    - name: "search"
      symbol: "lupa"
      context: "campo de busca (ex: 'Service Types 🔍')"
    - name: "chevron-down"
      symbol: "seta para baixo"
      context: "abrir dropdown/select (ex: 'TSR ▾', 'Group 1 ▾')"
    - name: "more-horizontal"
      symbol: "três pontos (...)"
      context: "menu de opções do usuário/contexto (ex: 'Charles Mullen ...')"
    - name: "check"
      symbol: "checkbox marcado"
      context: "colunas Visible / Required nas tabelas"
    - name: "circle (status dot)"
      symbol: "ponto colorido preenchido"
      context: "indicador de status ao lado de texto (ex: 'Validation' em verde)"
    - name: "chevron-right"
      symbol: "seta para direita"
      context: "separador de breadcrumb (ex: 'Service Types > Attributes')"
```

### Regras de uso de ícones

1. Sempre em **traço (outline)**, nunca preenchidos sólidos — exceção apenas para o *status dot* (círculo preenchido) e o checkbox marcado.
2. Cor padrão `icon.default` (cinza); muda para `icon.active` (`brand.primary`) apenas quando o item está selecionado/ativo.
3. Tamanho consistente por contexto: 20px na sidebar principal, 16px em ações de tabela, 14–18px em ícones inline de campo/cabeçalho.
4. Ícones nunca carregam cor de status por padrão — exceção: `alert-triangle` pode assumir `status.warning` quando representa um alerta real (não apenas navegação).
5. Nunca combinar dois estilos de ícone (ex: outline + filled) na mesma tela.

## 11. Regras de Aplicação para Consistência Visual

1. **Uma cor de destaque só.** `brand.primary` é a única cor "viva" da interface. Status (verde/vermelho/laranja) só aparece em badges, dots e mensagens — nunca em botões neutros.
2. **Hierarquia por peso e tamanho, não por cor.** Textos pretos/cinzas em variações de peso (400/500/600); cor reservada a interação e status.
3. **Grade de 4px em tudo.** Todo espaçamento, padding e altura de componente deve ser múltiplo de 4.
4. **Máximo 2 níveis de raio por tela.** Cards usam `lg`; componentes internos usam `sm`/`md`.
5. **Sombras sutis, nunca dramáticas.** Elevação é indicada por sombra leve (`0 1px 2px` + `0 4px 12px`), não por bordas fortes.
6. **Sidebar de ícones sempre monocromática**, com o item ativo destacado por fundo `primary_soft` + ícone `brand.primary`.
7. **Tabelas densas, mas com respiro vertical mínimo de 8px entre linhas** e linha divisória `border.subtle`.
8. **Um botão primário por contexto.** Ações secundárias/terciárias usam `secondary` ou `ghost`.
9. **Inputs sempre com altura fixa de 36px** e mesmo estilo de borda/foco em toda a aplicação (inputs, selects, search).
10. **Breadcrumbs e headers de página seguem padrão fixo**: breadcrumb em `text.secondary` acima do título, título em peso 600.
11. **Ícones em traço fino (stroke, não fill)**, tamanho padrão 20px na sidebar e 16px em contextos de tabela/lista.
12. **Nunca usar gradientes, texturas ou ilustrações coloridas** — a estética é flat e neutra por padrão; cor é sinal, não decoração.
