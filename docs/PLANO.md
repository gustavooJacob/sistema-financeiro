# Plano de Construção — FinControle

Este plano organiza a implementação do FinControle em fases incrementais, com base na organização sugerida do `docs/FSD.md` (Seção 25), nos módulos funcionais (Seção 6), nas entidades (Seção 10) e no modelo de dados (Seção 11).

Cada fase deve ser construída e validada isoladamente antes de avançar para a próxima. Uma fase por prompt/chat, conforme protocolo definido em `CLAUDE.md`.

---

## Fase 1 — Infraestrutura e base do projeto

**Status:** ✅ Concluída nesta etapa (preparação de terreno).

**Objetivo:** preparar a estrutura de pastas, o arquivo de entrada, a configuração técnica e as proteções básicas, sem implementar nenhuma funcionalidade de negócio.

**Checklist:**
- [x] Criar estrutura de diretórios conforme FSD Seção 5.2 (`config/`, `app/{controllers,models,views,services}/`, `database/migrations/`, `logs/erros/`, `assets/{css,js,vendor}/`).
- [x] Criar `index.php` como único ponto de entrada público.
- [x] Criar `config/config.example.php` (modelo sem segredos reais).
- [x] Proteger pastas internas (`config/`, `app/`, `database/`, `logs/`) com `.htaccess`.
- [x] Criar `.gitignore` (excluindo `config/config.php` real e logs).
- [x] Criar `docs/PLANO.md`, `CLAUDE.md`, `docs/STATUS.md`, `docs/ERROS.md`.

**Critérios de pronto:**
- Estrutura de pastas existe e reflete a Seção 5.2 do FSD.
- Nenhuma pasta interna é acessível por URL direta (validar manualmente via navegador na Fase de revisão de segurança).
- `config/config.php` real (com credenciais) não deve nunca ser commitado.

**Arquivos/pastas envolvidos:** raiz do projeto, `config/`, `app/`, `database/`, `logs/`, `assets/`.

**Observações de dependência:** nenhuma — é a base de todas as demais fases.

---

## Fase 2 — Banco de dados e persistência

**Objetivo:** criar o banco de dados MySQL e a arquitetura de migrations descrita na Seção 11 do FSD, incluindo a tabela de controle `migrations` e os dados iniciais (categorias e formas de pagamento padrão).

**Checklist:**
- [ ] Criar o banco de dados MySQL local (`fincontrole`, conforme `config.example.php`).
- [ ] Criar a tabela de controle `migrations`.
- [ ] Criar script `database/migrations/run.php` (execução via PHP CLI, nunca por rota pública).
- [ ] Criar migrations para as tabelas: `usuarios`, `categorias`, `formas_pagamento`, `lancamentos`, `historico_alteracoes`, `tokens_recuperacao_senha`, `logs_erros`, `logs_seguranca`.
- [ ] Criar índices sugeridos na Seção 11 (ex.: `usuario_id`+`status`+`data_prevista`, `usuario_id`+`data_efetiva`, etc.).
- [ ] Inserir dados iniciais: categorias e formas de pagamento padrão (`usuario_id` nulo, `padrao = 1`).
- [ ] Implementar classe de conexão PDO/mysqli lendo de `config/config.php`, com timezone `America/Sao_Paulo` fixado também na conexão MySQL.

**Critérios de pronto:**
- Todas as tabelas da Seção 11 existem, com campos de auditoria (`criado_em`, `atualizado_em`) e soft delete (`excluido_em`) onde aplicável.
- Executar a migration duas vezes não duplica tabelas nem dados (mecanismo de controle funcionando).
- Categorias e formas de pagamento padrão aparecem no banco após a primeira execução.

**Arquivos/pastas envolvidos:** `database/migrations/`, `config/config.php`, possivelmente `app/models/Conexao.php` (ou equivalente).

**Observações de dependência:** depende da Fase 1 (config e estrutura de pastas).

---

## Fase 3 — Autenticação, sessão e controle de acesso

**Objetivo:** implementar cadastro, login, logout, recuperação/redefinição de senha, bloqueio por tentativas e proteção de rotas (Módulo 1 do FSD, Seções 6, 15 e 16).

**Checklist:**
- [ ] Model `Usuario` (CRUD básico, hash de senha, contador de tentativas, bloqueio temporário).
- [ ] Controller de autenticação (cadastro, login, logout).
- [ ] Views: Cadastro, Login, Recuperação de Senha (solicitação), Redefinição de Senha.
- [ ] Fluxo de recuperação de senha com token de uso único e expiração (1 hora), envio via SMTP configurado em `config/config.php`.
- [ ] Bloqueio de login após 5 tentativas inválidas consecutivas, por 15 minutos.
- [ ] Sessão PHP nativa, expiração por inatividade de 30 minutos.
- [ ] Middleware/verificação de sessão ativa em todas as rotas protegidas.
- [ ] Registro de eventos no log de segurança (login inválido, bloqueio, redefinição de senha).

**Critérios de pronto:**
- Usuário consegue se cadastrar, fazer login e logout.
- Tentativas inválidas consecutivas bloqueiam a conta pelo tempo configurado.
- Rota protegida redireciona ao login quando não há sessão ativa.
- Recuperação de senha funciona de ponta a ponta em ambiente local (SMTP de teste).

**Arquivos/pastas envolvidos:** `app/controllers/`, `app/models/`, `app/views/auth/`, `app/services/`, `logs/`.

**Observações de dependência:** depende da Fase 2 (tabelas `usuarios` e `tokens_recuperacao_senha`).

---

## Fase 4 — Conta do usuário (perfil)

**Objetivo:** permitir edição de e-mail/senha e exclusão (soft delete) da própria conta (Módulo 1, telas "Conta/Perfil").

**Checklist:**
- [ ] Controller e View de Conta/Perfil.
- [ ] Edição de e-mail e senha mediante confirmação da senha atual.
- [ ] Exclusão da própria conta (soft delete), com confirmação em modal.
- [ ] Registro de alterações sensíveis no log de segurança.

**Critérios de pronto:**
- Usuário altera e-mail/senha somente confirmando a senha atual.
- Conta excluída impede novo login, mas preserva dados financeiros.

**Arquivos/pastas envolvidos:** `app/controllers/`, `app/views/conta/`, `app/services/`.

**Observações de dependência:** depende da Fase 3.

---

## Fase 5 — Categorias e Formas de Pagamento

**Objetivo:** implementar o Módulo 2 do FSD: listagem, criação e exclusão (soft delete) de categorias e formas de pagamento próprias, respeitando itens padrão do sistema.

**Checklist:**
- [ ] Models `Categoria` e `FormaPagamento`.
- [ ] Controllers e Views correspondentes.
- [ ] Validação de nome obrigatório e não duplicado (inclusive contra nomes padrão, case-insensitive).
- [ ] Exclusão apenas de itens próprios (soft delete), com bloqueio explícito para itens padrão.
- [ ] Geração de registro de histórico ao excluir item próprio.

**Critérios de pronto:**
- Usuário cria e exclui categorias/formas de pagamento próprias.
- Itens padrão nunca podem ser excluídos pela interface nem por manipulação direta de rota.
- Exclusão gera registro em `historico_alteracoes`.

**Arquivos/pastas envolvidos:** `app/controllers/`, `app/models/`, `app/views/categorias/`, `app/views/formas_pagamento/`.

**Observações de dependência:** depende da Fase 2 (tabelas e dados padrão já inseridos).

---

## Fase 6 — Lançamentos financeiros (CRUD)

**Objetivo:** implementar o Módulo 3 do FSD: criação, edição, atualização de status e exclusão de lançamentos, com todas as validações da Seção 14.

**Checklist:**
- [ ] Model `Lancamento` com validações (valor > 0, descrição ≤ 300 caracteres, datas, status).
- [ ] Controller e Views: Lista de Lançamentos (com filtros e paginação de 20/página) e Formulário de criação/edição.
- [ ] Ação "marcar como concluído" exigindo data efetiva.
- [ ] Exclusão (soft delete) com confirmação em modal.
- [ ] Geração de histórico: um registro por campo alterado em cada edição; um registro de criação; um registro de exclusão.
- [ ] Destaque visual de "atrasado" para pendentes com data prevista no passado (regra de apresentação).
- [ ] Isolamento de dados por `usuario_id` validado no backend em toda operação.

**Critérios de pronto:**
- CRUD completo funcionando com todas as validações da Seção 14.
- Histórico de edição gera um registro por campo alterado (não um único registro genérico).
- Tentar acessar/editar lançamento de outro usuário retorna acesso negado.

**Arquivos/pastas envolvidos:** `app/controllers/`, `app/models/`, `app/views/lancamentos/`, `app/services/` (cálculo/validação centralizados).

**Observações de dependência:** depende das Fases 2, 3 e 5 (usuário autenticado e categorias/formas de pagamento disponíveis).

---

## Fase 7 — Painel financeiro (Dashboard)

**Objetivo:** implementar o Módulo 4 do FSD: tela inicial após login, com saldo realizado, saldo previsto, totais, gráfico de gastos por categoria e listas de lançamentos recentes/pendentes.

**Checklist:**
- [ ] Service de cálculo de saldo (realizado e previsto), seguindo exatamente as regras da Seção 14.
- [ ] Cálculo de total de receitas, total de despesas e dados do gráfico de gastos por categoria (mesma composição do saldo previsto).
- [ ] Integração do Chart.js (hospedado localmente em `assets/vendor/chartjs/`) para o gráfico de gastos por categoria.
- [ ] Lista dos 5 lançamentos mais recentes e dos 5 próximos pendentes, com destaque de atrasados.
- [ ] Estado vazio quando não há lançamentos no mês.

**Critérios de pronto:**
- Indicadores batem exatamente com as regras da Seção 14 ("Cálculo dos Indicadores do Painel Financeiro").
- Painel é a tela inicial exibida após login.
- Gráfico renderiza corretamente com Chart.js local (sem CDN).

**Arquivos/pastas envolvidos:** `app/controllers/`, `app/services/`, `app/views/painel/`, `assets/vendor/chartjs/`.

**Observações de dependência:** depende da Fase 6 (lançamentos existentes para calcular).

---

## Fase 8 — Histórico de alterações

**Objetivo:** implementar o Módulo 5 do FSD: tela somente leitura de consulta ao histórico, com filtros por período, categoria e forma de pagamento.

**Checklist:**
- [ ] Controller e View de Histórico, com paginação de 20 registros/página.
- [ ] Filtros por período, categoria e forma de pagamento.
- [ ] Exibição de alterações mesmo de itens já excluídos.
- [ ] Garantia de que a tela é somente leitura (sem nenhuma ação de escrita).

**Critérios de pronto:**
- Histórico exibe corretamente criação/edição/exclusão de lançamentos e exclusão de categorias/formas de pagamento, mesmo após exclusão do item original.
- Filtros funcionam corretamente e respeitam isolamento por usuário.

**Arquivos/pastas envolvidos:** `app/controllers/`, `app/views/historico/`.

**Observações de dependência:** depende das Fases 5 e 6 (que geram os registros de histórico).

---

## Fase 9 — Identidade visual (DESIGN.md) em todas as telas

**Objetivo:** aplicar consistentemente a paleta de cores, tipografia, espaçamentos, componentes e ícones definidos em `docs/DESIGN.md` a todas as telas construídas nas fases anteriores.

**Checklist:**
- [ ] Baixar e hospedar localmente: Bootstrap, fonte Inter (`.woff2`), ícones Lucide (SVGs individuais), Chart.js — todos em `assets/vendor/` (ver `assets/vendor/README.md`).
- [ ] Criar `assets/css/` com estilos que implementem a paleta, tipografia e espaçamentos do DESIGN.md sobre o Bootstrap local.
- [ ] Revisar cada tela already implementada quanto à aderência ao DESIGN.md (cards, botões, badges, tabelas, sidebar de navegação, estados vazio/erro/sucesso).

**Critérios de pronto:**
- Nenhuma biblioteca de front-end é carregada via CDN.
- Todas as telas seguem a paleta, tipografia, espaçamentos e componentes do DESIGN.md.

**Arquivos/pastas envolvidos:** `assets/vendor/`, `assets/css/`, `assets/js/`, todas as Views.

**Observações de dependência:** pode ser feita incrementalmente junto com cada fase funcional, mas deve ter uma revisão final consolidada antes da entrega.

---

## Fase Final — Itens transversais e revisão de entrega

**Status:** ✅ Concluída.

**Objetivo:** consolidar logs, segurança e qualidade antes da entrega, conforme Seções 19, 24 e 26 do FSD.

**Checklist:**
- [x] Log de erros funcionando (registro em `logs_erros` e estratégia de contingência em `logs/erros/*.log`).
- [x] Log de segurança funcionando para todos os eventos da Seção 19.
- [x] Revisão de segurança: nenhuma pasta interna acessível via navegador; nenhuma rota expõe dados de outro usuário; senhas/tokens sempre com hash; `config/config.php` não acessível publicamente.
- [x] Revisão de qualidade: validações, mensagens de erro, estados vazio/carregando/sucesso em todas as telas.
- [x] Conferência final contra os Critérios de Aceitação Técnica e Funcional (FSD, Seção 26).
- [x] Organização final do repositório para publicação em subpasta (XAMPP e, futuramente, Hostnet), sem depender de nomes fixos como `public_html`, `htdocs` ou `www`.

**Critérios de pronto:**
- Todos os itens da Seção 26 do FSD conferidos e marcados.
- Nenhuma funcionalidade fora de escopo (Seção 7) foi implementada.

**Arquivos/pastas envolvidos:** todo o projeto.

**Observações de dependência:** depende de todas as fases anteriores estarem concluídas.
