# STATUS.md — Estado Atual do Projeto FinControle

**Última atualização:** 06/08/2026 (Deploy em produção no InfinityFree).

## Estado atual

O FinControle está com todas as fases do `docs/PLANO.md` concluídas, incluindo a Fase Final de revisão transversal de segurança e qualidade, e está **publicado em produção** no InfinityFree (`https://gustavo.freehosting.dev`). Módulo de autenticação e sessão, conta do usuário, categorias/formas de pagamento, CRUD completo de lançamentos financeiros, Painel Financeiro (Dashboard), Histórico de Alterações e a identidade visual completa (`docs/DESIGN.md`) estão implementados e testados ponta a ponta. Todas as telas internas usam a sidebar de navegação por ícones, cards, badges, tabelas e formulários do design system, com Bootstrap, fonte Inter e ícones Lucide hospedados localmente (sem CDN). A revisão de segurança (Seção 24 do FSD) e a conferência contra os Critérios de Aceitação (Seção 26 do FSD) foram concluídas sem pendências bloqueantes.

## Deploy em produção — InfinityFree (06/08/2026)

O sistema foi publicado em produção no **InfinityFree** (`https://gustavo.freehosting.dev`), em vez da Hostnet originalmente prevista no `CLAUDE.md`/`docs/MANUTENCAO.md` — hospedagem alternativa gratuita escolhida pelo usuário. `CLAUDE.md` e `docs/MANUTENCAO.md` ainda citam "Hostnet" como referência de ambiente de produção; manter em mente que o ambiente real atual é o InfinityFree até que esses documentos sejam atualizados.

**Particularidade do ambiente (diferente do fluxo documentado para Hostnet):** o plano gratuito do InfinityFree não oferece acesso SSH/CLI, apenas FTP e phpMyAdmin. Como as migrations só podem ser executadas via `php database/migrations/run.php` em linha de comando (nunca por rota HTTP pública — regra de segurança preservada), o schema foi aplicado da seguinte forma:
- Banco local (`fincontrole`, já com as 10 migrations aplicadas via CLI) exportado pelo phpMyAdmin do XAMPP — estrutura completa das 9 tabelas de negócio + tabela `migrations`, com dados apenas das categorias/formas de pagamento padrão (`usuario_id IS NULL AND padrao = 1`) e das 10 linhas de controle de `migrations`. Todos os dados de teste (usuários, lançamentos, histórico, logs) foram removidos antes da exportação.
- `.sql` resultante importado diretamente no banco já criado no painel do InfinityFree.
- **Consequência para o futuro:** qualquer nova migration precisará ser aplicada manualmente via phpMyAdmin no InfinityFree (não há CLI disponível lá), a menos que a hospedagem mude para uma com acesso SSH. Ao criar uma migration nova, gerar o SQL equivalente e aplicá-lo manualmente lá, além de registrar a linha correspondente na tabela `migrations` do servidor.

**Upload dos arquivos:** feito via FTP (FileZilla), para dentro da pasta `htdocs` do servidor (raiz do site). O File Manager web do InfinityFree não conseguiu criar/editar arquivos começados por ponto (`.htaccess`) — abordagem abandonada em favor do FTP, que não teve esse problema. Arquivos enviados: todo o conteúdo do projeto exceto `.git/`, `docs/` (não protegida por `.htaccess`, evitando expor documentação interna publicamente) e `CLAUDE.md` (removido do servidor após verificação, pelo mesmo motivo — descreve regras internas de segurança). Confirmado que os 5 `.htaccess` (raiz, `config/`, `app/`, `database/`, `logs/`) subiram corretamente.

**`config/config.php` de produção:** criado com os dados reais de conexão do MySQL do InfinityFree e `url_base` = `https://gustavo.freehosting.dev`.

**SMTP de produção:** configurado com o Brevo (plano gratuito, 300 e-mails/dia) — `smtp-relay.brevo.com:587`, TLS, remetente `gugalokeplays@gmail.com` validado no painel do Brevo. Na primeira chamada, o Brevo bloqueou o envio pedindo verificação do IP de saída do InfinityFree (comportamento normal de segurança do Brevo para IP nunca usado antes, não é bloqueio do InfinityFree); autorizado o IP pelo e-mail de verificação do Brevo, e o envio passou a funcionar normalmente.

**Testes realizados em produção:**
- `https://gustavo.freehosting.dev/` → carrega a tela de login corretamente.
- `https://gustavo.freehosting.dev/config/config.php` → HTTP 403 Forbidden (pasta protegida).
- Cadastro, login, criação de lançamento/categoria e painel financeiro → funcionando.
- Recuperação de senha (fluxo completo, incluindo recebimento do e-mail via Brevo e redefinição) → testada e funcionando.

**Pendências desta etapa:**
- Teste de isolamento entre dois usuários diferentes em produção ainda não foi reexecutado (já validado exaustivamente em ambiente local na Fase Final — ver detalhes mais abaixo nesta página).
- Remoção final dos dados de teste do banco de produção (usuário de teste usado para validar o fluxo) ainda pendente de confirmação.
- `CLAUDE.md` e `docs/MANUTENCAO.md` ainda não foram atualizados para refletir o InfinityFree como ambiente de produção real (em vez de Hostnet) nem o processo manual de migrations via phpMyAdmin — ajuste de documentação recomendado numa próxima sessão.

## Documentação final de manutenção (28/07/2026)

Com todas as fases funcionais e a revisão de segurança já concluídas, foi criada a documentação final para manutenção futura do sistema:

- **`docs/MANUTENCAO.md` criado** — guia completo para quem for alterar o sistema no futuro: visão geral, stack e ambientes, como rodar localmente, mapa de pastas, banco de dados/migrations, autenticação/autorização, como adicionar tela/campo/regra de negócio, como testar, cuidados de segurança, como registrar progresso e o que não fazer.
- **`docs/COMO-PEDIR-MUDANCAS.md` criado** — guia para uma pessoa leiga pedir alterações a uma IA, com checklist de aceitação e 8 modelos de prompt prontos (campo novo, tela nova, correção de erro, regra de negócio, ajuste visual, filtro/relatório, revisão de segurança, preparação de commit).
- **`CLAUDE.md` atualizado para modo manutenção** — passou a orientar sobre alterações pontuais num sistema já em produção (não mais construção de fases novas), reforçando o protocolo de leitura obrigatória (`MANUTENCAO.md`, `FSD.md`, `DESIGN.md`, `STATUS.md`, `ERROS.md`), as regras de segurança já implementadas e a orientação de testar/documentar/commitar a cada alteração.
- Nenhuma funcionalidade nova foi implementada nesta etapa; nenhum deploy foi realizado.

**Pendências:** nenhuma quanto a esta etapa de documentação — todas as fases funcionais e a revisão de segurança já estavam concluídas antes de iniciá-la.

**Próximo passo recomendado:** abrir um chat novo e usar o prompt de publicação/deploy (passo 7 do fluxo do usuário) quando desejar publicar o sistema na Hostnet.

**Ajustes de UI feitos após a Fase 9, a pedido do usuário (commit "Ajustes de UI: sidebar retratil, conteudo centralizado e correcao do grafico"):**
- Sidebar passou a ser retrátil: um botão "hambúrguer" no topo expande a sidebar de 56px para 220px, exibindo o rótulo de texto de cada item (além do ícone); o estado expandida/recolhida fica salvo em `localStorage` do navegador, já que cada navegação recarrega a página inteira (ver `app/views/partials/app_topo.php` e as classes `html.sidebar-expandida ...` em `assets/css/app.css`). Esta é uma extensão da sidebar 56px descrita no `docs/DESIGN.md` (Seção 8), que originalmente previa apenas ícones com tooltip — o comportamento retrátil foi um pedido explícito do usuário.
- O card de conteúdo de cada tela (`.cartao`) passou a ficar centralizado horizontalmente na área de conteúdo (`margin: 0 auto`), em vez de alinhado à esquerda.
- Corrigido um bug no gráfico de gastos por categoria do Painel (Chart.js): o `<canvas>` não tinha um contêiner de altura fixa, então o gráfico crescia descontroladamente ao alternar entre janela pequena e grande. Corrigido envolvendo o canvas em `<div class="grafico-container">` (altura fixa de 220px) e definindo `responsive: true`/`maintainAspectRatio: false` nas opções do Chart.js (`app/views/painel/index.php`).
- Testes: verificado via `curl` (HTML/CSS servidos pelo Apache local) que a marcação do botão hambúrguer, dos rótulos de cada item e do `grafico-container` está presente e correta; dados de teste removidos do banco ao final.

## Fase atual

**Fase Final — Itens transversais e revisão de entrega: ✅ Concluída.**

## Checklist por fase (ver detalhamento completo em `docs/PLANO.md`)

- [x] Fase 1 — Infraestrutura e base do projeto
- [x] Fase 2 — Banco de dados e persistência
- [x] Fase 3 — Autenticação, sessão e controle de acesso
- [x] Fase 4 — Conta do usuário (perfil)
- [x] Fase 5 — Categorias e Formas de Pagamento
- [x] Fase 6 — Lançamentos financeiros (CRUD)
- [x] Fase 7 — Painel financeiro (Dashboard)
- [x] Fase 8 — Histórico de alterações
- [x] Fase 9 — Identidade visual (DESIGN.md) em todas as telas
- [x] Fase Final — Itens transversais e revisão de entrega

## Próximo passo recomendado

Todas as fases do `docs/PLANO.md` estão concluídas. Não há próxima fase de codificação pendente. Recomenda-se abrir um chat novo e usar o prompt de **validação de segurança** (passo 5 do fluxo definido pelo usuário) para uma revisão independente antes da publicação em produção (Hostnet).

## Fase Final — Itens transversais e revisão de entrega (detalhes)

**O que foi feito:**

- **Auditoria de segurança e qualidade** (revisão manual + revisão dirigida de todo `app/controllers/`, `app/models/`, `app/services/`, `app/views/`, `index.php` e `.htaccess` de todas as pastas internas), cobrindo os itens da Seção 24 do FSD:
  - CSRF: confirmado que toda ação POST valida o token antes de processar, em todos os Controllers.
  - Isolamento de dados: confirmado que toda leitura/edição/exclusão de lançamento, categoria ou forma de pagamento valida `usuario_id` tanto no Controller quanto na cláusula `WHERE` da query (dupla camada), com mensagem genérica e registro de `acesso_negado` em `logs_seguranca` quando a posse falha.
  - SQL: confirmado que 100% das queries usam prepared statements (PDO); nenhuma concatenação de entrada do usuário em SQL. As poucas interpolações de string em SQL existentes (nome de tabela em `ItemClassificacao`, fragmentos de `WHERE` em `Lancamento::listar()`/`Historico::listar()`) vêm de whitelists fechadas no código ou de valores calculados internamente, nunca de entrada livre do usuário.
  - XSS: confirmado que toda saída dinâmica nas Views passa por `htmlspecialchars()`.
  - Sessão/rotas protegidas: confirmado que toda rota interna chama `Sessao::exigirAutenticacao()` antes de processar, além da checagem centralizada em `index.php`.
  - Mensagens de erro: confirmado que nenhuma tela expõe SQL, stack trace, nome de classe PHP ou caminho de arquivo ao usuário final; `index.php` tem `set_exception_handler` central e tratamento à parte para falha de conexão com o banco, ambos com mensagem genérica.
  - Senhas e tokens: confirmado uso de `password_hash`/`password_verify` e hash SHA-256 de uso único para token de recuperação de senha.
  - Pastas internas: confirmado (teste manual no navegador) que `config/`, `app/`, `database/` e `logs/` retornam HTTP 403 diretamente por URL.
- **Dois reforços defensivos aplicados como resultado da auditoria** (sem alterar comportamento funcional nem regra de negócio):
  - `app/views/painel/index.php`: os dados do gráfico de gastos por categoria (nomes de categoria definidos pelo próprio usuário) são impressos dentro de uma tag `<script>` via `json_encode`. Adicionadas as flags `JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT` (além da já existente `JSON_UNESCAPED_UNICODE`) para eliminar qualquer possibilidade futura de quebra de contexto HTML/JS caso o nome de uma categoria contenha caracteres como `<`, `>`, `&` ou aspas. Não havia exploração viável antes (o escape padrão de `/` do `json_encode` já impedia o vetor mais óbvio), mas o reforço deixa a proteção explícita e não dependente de um comportamento padrão implícito do PHP.
  - `app/controllers/ClassificacaoController.php` (`processarExcluir`): a ordem de validação foi invertida — agora a posse/existência do item é validada **antes** de verificar se ele é um item padrão do sistema (antes era o contrário). Não havia vazamento de dado sensível na ordem antiga (categorias padrão não pertencem a "outro usuário", são compartilhadas), mas a nova ordem segue a prática recomendada de sempre validar posse/existência como primeiro filtro de qualquer operação de escrita.
- **Testes executados (Chrome embutido, com Apache/MySQL do XAMPP em execução, `http://localhost:8080/sistema_financeiro`):**
  - `php -l` em todos os arquivos `.php` do projeto (exceto `assets/vendor/`) → nenhum erro de sintaxe.
  - Cadastro, login e painel (estado vazio) de um usuário novo → funcionando.
  - Acesso direto a `config/config.php` → HTTP 403 Forbidden.
  - Rota inexistente → página de erro genérica, sem detalhe técnico.
  - `GET /logout` (rota que só aceita POST) → tratada como rota inexistente, sem vazar detalhe técnico.
  - Criação de categoria própria, criação de lançamento (despesa concluída) → refletidos corretamente no painel (saldo realizado/previsto, total de despesas, gráfico, lista de últimos lançamentos), sem erro no console do navegador.
  - Tentativa de excluir uma categoria padrão do sistema (requisição forjada via `fetch`, contornando a interface que nem oferece essa opção) → bloqueada com a mensagem "Itens padrão do sistema não podem ser excluídos." (confirma que o reforço na ordem de validação não quebrou esse fluxo).
  - Segundo usuário de teste tentando **editar** (`GET /lancamentos/editar?id=...`) e **excluir** (`POST /lancamentos/excluir`, requisição forjada) um lançamento do primeiro usuário → ambos bloqueados com a mensagem genérica "Não foi possível localizar o lançamento informado.", e a lista de categorias do segundo usuário não mostra a categoria própria do primeiro (isolamento também nas categorias).
  - Envio de `/lancamentos/excluir` com token CSRF inválido (requisição forjada) → bloqueado com HTTP 400, sem texto técnico na resposta.
  - Consulta direta ao banco (`logs_seguranca`) após os testes acima → confirmados os registros `acesso_negado` (edição negada, exclusão negada, CSRF inválido) com o `usuario_id` correto do segundo usuário.
  - Consulta direta ao banco (`logs_erros`) após todos os testes → nenhum erro técnico inesperado registrado.

**Resultado dos testes:** todos passaram. Dados de teste (usuários `teste.fasefinal@example.com` e `teste.fasefinal.b@example.com`, categoria, lançamento e respectivos registros em `logs_seguranca`) foram removidos do banco ao final da validação.

**Conferência final contra a Seção 26 do FSD (Critérios de Aceitação):** todos os itens da lista foram revisados e confirmados atendidos — funcionalidades principais completas, arquitetura MVC respeitada, isolamento de dados validado no backend, validações da Seção 14 funcionando, indicadores do painel corretos, histórico por campo alterado, soft delete e campos de auditoria presentes, índices criados, logs de erro/segurança funcionando (incluindo contingência em arquivo), telas aderentes ao DESIGN.md, nenhuma funcionalidade fora de escopo, pastas internas protegidas, migrations não expostas e executadas apenas via CLI.

**Pendência conhecida:** nenhuma quanto aos critérios de pronto desta fase. Como já registrado na Fase 3, este ambiente local não tem um servidor SMTP de teste instalado — o envio real do e-mail de recuperação de senha não foi reexercitado nesta fase (o fluxo de geração/validação de token já foi validado na Fase 3 e não foi alterado aqui).

## Fase 9 — Identidade visual (DESIGN.md) em todas as telas (detalhes)

**O que foi implementado:**

- **Bibliotecas baixadas e hospedadas localmente em `assets/vendor/`** (sem CDN, conforme FSD Seção 3): Bootstrap 5.3.3 (`bootstrap.min.css` + `bootstrap.bundle.min.js`, apenas os arquivos finais minificados), fonte Inter (`InterVariable.woff2`, arquivo variável único cobrindo os pesos 400/500/600 usados no DESIGN.md) e os ícones Lucide usados no sistema (`assets/vendor/icons/*.svg` — navegação, ações de tabela, estados). Chart.js já havia sido baixado na Fase 7. Ver `assets/vendor/README.md` atualizado.
- **`app/services/Icone.php`**: helper novo que lê o SVG do ícone solicitado (lista restrita a `[a-z0-9-]`, sem entrada de usuário) e o imprime inline no HTML (permite colorir via CSS usando `stroke="currentColor"`, conforme DESIGN.md Seção 10).
- **`assets/css/app.css`**: substitui o `assets/css/auth.css` das fases anteriores. Implementa integralmente os tokens do `docs/DESIGN.md` (paleta de cores completa, tipografia Inter com `@font-face` local, espaçamentos em grade de 4px, raios `sm/md/lg/pill`, sombra de card sutil) e os componentes (cards, botões primário/secundário/ghost/perigo, badges, tabelas, formulários, paginação, estados vazio/erro/sucesso), reaproveitando os mesmos nomes de classe já usados nas Views desde a Fase 3 (para não exigir reescrita de HTML) e acrescentando as classes do novo shell de navegação (`app-shell`, `sidebar`, `sidebar-item`, `conteudo-principal`, `cartao`).
- **Sidebar de navegação por ícones** (FSD Seção 16; DESIGN.md Seção 8): nova estrutura de layout `app/views/partials/app_topo.php` + `app/views/partials/app_fim.php`, usada por todas as telas internas autenticadas (Painel, Lançamentos, Categorias, Formas de Pagamento, Histórico, Conta). Sidebar de 56px com os 6 ícones (`title` como tooltip, já que a sidebar não exibe texto), item ativo destacado com fundo `brand.primary_soft` (controlado pela variável `$paginaAtiva` que cada Controller/View define) e botão "Sair" no rodapé (com proteção CSRF, igual às demais ações de escrita).
- **Telas de pré-autenticação** (cadastro, login, recuperar/redefinir senha, erro genérico): continuam usando o parcial original `app/views/partials/topo.php`/`fim.php` (cartão único centralizado, sem sidebar, pois o usuário ainda não está autenticado), agora carregando `assets/css/app.css` em vez do antigo `auth.css`.
- Todas as Views internas (`conta/index.php`, `categorias/index.php`, `formas_pagamento/index.php`, `partials/lista_classificacao.php`, `lancamentos/index.php`, `lancamentos/formulario.php`, `painel/index.php`, `historico/index.php`) foram atualizadas para usar `app_topo.php`/`app_fim.php` e definir `$paginaAtiva`; os links de rodapé manuais duplicados (que existiam antes da sidebar) foram removidos, já que a navegação agora é feita pela sidebar.
- **`app/controllers/HistoricoController.php`**: passou a gerar `$csrfToken` (não gerava antes, pois a tela não tinha nenhum formulário de escrita) para que o botão "Sair" da sidebar funcione corretamente nessa tela.
- Nenhuma regra de negócio, validação ou lógica de Controller/Model foi alterada nesta fase — apenas a camada de apresentação (Views/CSS/assets).

**Testes executados (Chrome embutido, com Apache/MySQL do XAMPP em execução, `http://localhost:8080/sistema_financeiro`):**
- Todos os arquivos PHP alterados/criados passaram em `php -l` (sem erro de sintaxe).
- Cadastro e login de um usuário de teste (`teste.fase9@example.com`) → tela de login/cadastro renderiza corretamente com o novo `app.css` (cartão único, sem sidebar).
- Painel Financeiro (estado vazio e, em seguida, com 1 lançamento criado) → sidebar renderizada com os 6 ícones e o item "Painel" destacado; cards de indicadores, gráfico Chart.js (doughnut) e listas exibidos corretamente; nenhum erro no console do navegador.
- Criação de lançamento (`/lancamentos/novo`) → formulário com o item "Lançamentos" destacado na sidebar; após salvar, mensagem de sucesso e tabela de lançamentos (badges de tipo/status) renderizadas corretamente.
- Telas de Categorias e Histórico de Alterações → sidebar com o item correspondente destacado, badges "Padrão do sistema", tabela e filtros exibidos corretamente.
- Tela de Conta → item "Minha conta" destacado; botão "Sair" da **sidebar** (não o da própria tela) testado especificamente, pois usa um token CSRF gerado por um Controller (`HistoricoController`) que antes não gerava token nenhum — logout funcionou corretamente e redirecionou para `/login`.
- Rota inexistente → página de erro genérica (`erro_generico.php`) renderizada com o layout de pré-autenticação (cartão único, sem sidebar), sem mensagem técnica.
- Verificação de rede: `assets/vendor/bootstrap/css/bootstrap.min.css`, `assets/vendor/fonts/InterVariable.woff2` e `assets/vendor/icons/plus.svg` → HTTP 200 (assets públicos); `config/config.php`, `app/models/Conexao.php`, `database/migrations/run.php`, `logs/erros` → continuam HTTP 403 (proteção de pastas internas mantida).

**Resultado dos testes:** todos passaram. Dados de teste (usuário `teste.fase9@example.com`, lançamento e registro em `historico_alteracoes`) foram removidos do banco ao final da validação.

**Pendência conhecida:** nenhuma quanto aos critérios de pronto da fase. Um leiaute responsivo mais refinado para telas muito estreitas (menores que os breakpoints já cobertos no painel) não foi exigido pelo FSD (sistema é web, sem requisito explícito de app mobile) e não foi aprofundado além do que já existia.

## Fase 8 — Histórico de alterações (detalhes)

**O que foi implementado:**

- **`app/services/Historico.php`**: método novo `listar()` (mantendo no mesmo arquivo/classe que já grava os registros — módulo único responsável pela tabela `historico_alteracoes`, mesmo padrão de `ItemClassificacao` para categorias/formas de pagamento). Filtra por `usuario_id` (isolamento obrigatório), período (`data_alteracao` entre `data_inicio 00:00:00` e `data_fim 23:59:59`), categoria e forma de pagamento; paginação de 20 registros/página, ordenado por `data_alteracao` decrescente. Usa `LEFT JOIN` com `lancamentos` (mesmo já soft-deletados, pois a linha continua existindo na tabela) para permitir filtrar por categoria/forma de pagamento também quando a entidade do histórico é o próprio lançamento.
- **`app/models/ItemClassificacao.php`**: método novo `buscarPorIdIncluindoExcluidos()` — busca por id ignorando soft delete, usado apenas para resolver o nome de categorias/formas de pagamento já excluídas na exibição do histórico (FSD, Seção 17: histórico deve continuar acessível mesmo após a exclusão do item original).
- **`app/controllers/HistoricoController.php`**: rota protegida `/historico`, lê filtros e página da query string, chama `Historico::listar()`, calcula total de páginas e traduz os códigos internos (`entidade_tipo`, `acao`, `campo_alterado`) para rótulos legíveis em português. Quando o campo alterado é `categoria_id` ou `forma_pagamento_id`, resolve o nome correspondente (mesmo se o item já foi excluído) via `ItemClassificacao::buscarPorIdIncluindoExcluidos()`, com cache em memória por requisição para não repetir consultas.
- **View `app/views/historico/index.php`**: tela somente leitura (nenhuma ação de escrita), formulário de filtros por período/categoria/forma de pagamento (reaproveitando os mesmos componentes visuais da tela de Lançamentos), tabela com data/hora, entidade, ação (com badge colorido), campo alterado, valor anterior e valor novo, paginação e estado vazio quando não há alterações para os filtros aplicados.
- Rota `GET /historico` adicionada em `index.php`; links de navegação para "Histórico" adicionados ao painel, à listagem de lançamentos e ao template compartilhado de categorias/formas de pagamento.

**Decisão de implementação (ambiguidade do FSD):** a tabela `historico_alteracoes` (Seção 11) não guarda a categoria/forma de pagamento vigente em cada momento passado de um lançamento — apenas o `entidade_id` do lançamento e, quando o campo alterado for `categoria_id`/`forma_pagamento_id`, o id anterior/novo como texto solto. Por isso, o filtro por categoria/forma de pagamento na tela de Histórico considera: (a) o próprio registro de histórico da categoria/forma (ex.: sua exclusão) e (b) os registros de histórico de lançamentos cuja categoria/forma **atual** (estado hoje salvo em `lancamentos`, mesmo soft-deletado) corresponda ao filtro. Um lançamento que teve sua categoria trocada de A para B não aparece, sob o filtro "A", no registro de criação/exclusão (que reflete o estado atual, B) — apenas o próprio registro de edição do campo `categoria_id` mostra ambos os nomes (anterior e novo) na coluna de valores. Essa é a leitura mais coerente com a estrutura de dados definida no FSD, já que não há uma segunda tabela de "estado histórico" do lançamento.

**Testes executados (via PowerShell `Invoke-WebRequest`, com Apache/MySQL do XAMPP em execução, `http://localhost:8080/sistema_financeiro`):**
- Usuário de teste criado, categoria própria criada, lançamento criado, editado (valor e categoria), marcado como concluído, categoria própria excluída e lançamento excluído — gerando 7 registros de histórico (1 criação, 4 edições — valor, categoria, status, data efetiva —, 1 exclusão de categoria, 1 exclusão de lançamento).
- `GET /historico` sem filtro → todos os 7 registros exibidos, ordenados por data/hora decrescente; nomes de categoria resolvidos corretamente mesmo para a categoria própria já excluída ("Pets Fase8") e para a categoria padrão ("Salário").
- Filtro por período sem correspondência (`data_inicio`/`data_fim` de anos antes) → estado vazio exibido corretamente ("Nenhuma alteração encontrada para os filtros aplicados.").
- Filtro por categoria própria já excluída (`categoria_id` da categoria excluída) → retornou corretamente apenas o registro de exclusão da própria categoria (o lançamento já não referencia mais essa categoria no estado atual, conforme decisão de implementação acima).
- Filtro pela categoria padrão para a qual o lançamento foi migrado (`categoria_id` da categoria atual do lançamento) → retornou corretamente todo o histórico do lançamento (criação, as 4 edições e a exclusão), incluindo a edição do campo categoria com os dois nomes resolvidos ("Pets Fase8" → "Salário").
- Segundo usuário de teste, sem nenhuma alteração própria → `GET /historico` exibe o estado vazio, confirmando isolamento por `usuario_id` (nenhum dado do primeiro usuário vazou).
- Um erro foi encontrado e corrigido durante os testes (ver `docs/ERROS.md`, "Invalid parameter number ao filtrar o histórico por categoria/forma de pagamento") — reexecutados os mesmos testes após a correção, sem novos erros em `logs_erros`.

**Resultado dos testes:** todos passaram. Dados de teste (usuários `teste.fase8@example.com` e `teste.fase8.b@example.com`, categoria "Pets Fase8", lançamento e respectivos registros em `historico_alteracoes`) foram removidos do banco ao final da validação.

**Pendência conhecida:** nenhuma quanto aos critérios de pronto da fase. A paginação (20 itens/página) foi implementada seguindo o mesmo padrão já testado na tela de Lançamentos, mas não foi exercitada com mais de 20 registros reais neste ambiente de teste. A tela ainda usa o estilo básico das fases anteriores (não o design system completo do `docs/DESIGN.md`) — tratado de forma consolidada na Fase 9.

## Fase 7 — Painel financeiro (Dashboard) (detalhes)

**O que foi implementado:**

- **`app/services/PainelFinanceiro.php`**: serviço de cálculo dos indicadores do painel (FSD, Seção 14, "Cálculo dos Indicadores do Painel Financeiro"), método `obterResumoMensal()`. Busca separadamente os lançamentos concluídos do mês (por `data_efetiva`) e os pendentes do mês (por `data_prevista`) e combina os dois conjuntos em memória (com `bcadd`/`bcsub`/`bccomp` para evitar erro de arredondamento em valores monetários): saldo realizado (só concluídos), saldo previsto e totais de receitas/despesas (concluídos + pendentes, mesma composição), gráfico de gastos por categoria (só despesas, mesma composição, agrupado por `categoria_id` com nomes resolvidos em uma segunda consulta). Retorna também as duas listas do painel (5 últimos lançamentos por `criado_em` decrescente, 5 próximos pendentes por `data_prevista` crescente) e uma flag `vazio` para o estado vazio do mês.
- **`app/controllers/PainelController.php`**: substituído o placeholder da Fase 3 por uma implementação real — passou a receber `PDO` no construtor, instancia `PainelFinanceiro` e passa o resumo calculado para a View.
- **View `app/views/painel/index.php`**: cards de indicadores (saldo realizado/previsto, receitas/despesas do mês), gráfico de gastos por categoria (Chart.js, tipo doughnut) e duas listas (últimos lançamentos, próximos pendentes com destaque "Atrasado"), com atalho para "Novo lançamento" e estado vazio quando não há nenhum lançamento no mês corrente.
- **Chart.js** baixado (`chart.umd.min.js`, v4.5.1) e hospedado em `assets/vendor/chartjs/` — carregado apenas quando há dados no gráfico, sem uso de CDN em tempo de execução.
- **`assets/css/auth.css`**: classes novas para o painel (`cartao-painel`, `grade-indicadores`, `cartao-indicador`, `grade-painel`, `cartao-secao`, `lista-painel`, `item-painel`, etc.), reaproveitando a paleta/tipografia/espaçamento já usados nas telas anteriores (aplicação definitiva do DESIGN.md em todas as telas continua sendo a Fase 9).
- `index.php`: `PainelController` passou a receber `$pdo`; `app/services/PainelFinanceiro.php` incluído no carregamento manual de classes.

**Decisão de implementação (ambiguidade do FSD):** a Seção 12 diz que o painel "sempre reflete o mês corrente" e o fluxo da Seção 13 lista "últimos lançamentos e próximos pendentes ... do mês corrente" — por isso as duas listas do painel (diferente da tela de Lançamentos, que não tem esse recorte) foram restritas ao mês corrente: "últimos lançamentos" filtra por `criado_em` dentro do mês; "próximos pendentes" filtra por `data_prevista` dentro do mês. Um lançamento pendente com vencimento no mês seguinte não aparece no painel (mas continua visível normalmente na tela de Lançamentos e entra no cálculo do mês em que sua `data_prevista` cair).

**Testes executados (via `curl` e navegador embutido, com Apache/MySQL do XAMPP em execução, usando `http://localhost:8080/sistema_financeiro`):**
- Usuário recém-cadastrado sem nenhum lançamento → painel exibe o estado vazio ("Nenhum lançamento registrado em Julho/2026 ainda.") com atalho para criar o primeiro lançamento.
- Criados 5 lançamentos no mês corrente (2 receitas — uma concluída R$ 5.000,00, uma pendente R$ 800,00 — e 3 despesas — duas concluídas R$ 300,00/R$ 1.200,00, uma pendente R$ 150,00 com `data_prevista` no passado) e 1 despesa pendente com `data_prevista` no mês seguinte (R$ 400,00, para validar exclusão do cálculo do mês):
  - Saldo realizado: R$ 3.500,00 (5.000 − 300 − 1.200) → correto.
  - Saldo previsto: R$ 4.150,00 (5.800 − 1.650) → correto.
  - Total de receitas do mês: R$ 5.800,00 (5.000 + 800) → correto.
  - Total de despesas do mês: R$ 1.650,00 (300 + 1.200 + 150, excluindo a de R$ 400,00 do mês seguinte) → correto.
  - Gráfico de gastos por categoria: Moradia R$ 1.200,00, Alimentação R$ 450,00 (300 + 150) — sem a despesa do mês seguinte → correto.
  - Lista "Próximos pendentes": exibiu apenas os 2 pendentes com `data_prevista` no mês corrente, com o vencido corretamente destacado como "Atrasado"; o pendente do mês seguinte não apareceu → correto.
  - Lista "Últimos lançamentos": exibiu os 5 lançamentos mais recentes por `criado_em` (incluindo a despesa do mês seguinte, criada no mês corrente) → correto pela regra adotada.
- Verificação no navegador (Chrome embutido): sem erros no console; requisição de rede confirmou `assets/vendor/chartjs/chart.umd.min.js` carregado do próprio domínio (HTTP 200), não de CDN; `typeof Chart !== 'undefined'` e canvas do gráfico presentes no DOM.
- Pastas internas após a fase: `config/config.php` e `app/models/Conexao.php` → HTTP 403 diretamente pelo navegador (proteção mantida); `assets/vendor/chartjs/chart.umd.min.js` → HTTP 200 (acessível como asset público, como esperado).

**Resultado dos testes:** todos passaram. Dados de teste (usuário `teste.fase7@example.com`, lançamentos e registros relacionados em `historico_alteracoes`/`logs_seguranca`) foram removidos do banco ao final da validação.

**Pendência conhecida:** nenhuma quanto aos critérios de pronto da fase. A tela do painel ainda usa o estilo básico das fases anteriores (não o design system completo do `docs/DESIGN.md`, com sidebar de ícones e demais componentes) — tratado de forma consolidada na Fase 9.

## Fase 6 — Lançamentos financeiros (CRUD) (detalhes)

**O que foi implementado:**

- **`app/models/Lancamento.php`**: model da entidade Lançamento (FSD, Seção 10/11/14). Métodos: `criar()`; `buscarPorId()` (já validando posse por `usuario_id`, retorna `null` para lançamento inexistente/excluído/de outro usuário); `atualizar()` (compara cada campo editável entre o valor atual e o novo — usando `bccomp` para o campo `valor`, evitando falso positivo por diferença de formatação decimal — e retorna somente os campos que realmente mudaram, para o histórico por campo); `marcarConcluido()` (só afeta lançamento próprio e ainda pendente; retorna os dois campos alterados — `status` e `data_efetiva` — conforme FSD Seção 13); `excluir()` (soft delete restrito ao próprio usuário); `listar()` (filtros opcionais de período por `data_prevista`, categoria e forma de pagamento, paginação de 20/página, `JOIN` com `categorias`/`formas_pagamento` para exibir os nomes já resolvidos na listagem).
- **`app/controllers/LancamentoController.php`**: `listar()` (lê filtros e página da query string, calcula total de páginas); `exibirCriar()`/`processarCriar()`; `exibirEditar()`/`processarEditar()` (ambos validam posse via `buscarComPermissao()`, que registra `acesso_negado` em `logs_seguranca` e redireciona com mensagem genérica quando o lançamento não existe ou pertence a outro usuário — mesmo padrão já usado em `ClassificacaoController`); `processarConcluir()` (exige `data_efetiva` válida antes de delegar ao model); `processarExcluir()`. Validações centralizadas em `validar()`, cobrindo exatamente a Seção 14 do FSD: valor numérico > 0, descrição ≤ 300 caracteres, tipo/status válidos, categoria/forma de pagamento obrigatórias e acessíveis ao usuário (própria ou padrão do sistema), data prevista válida, data efetiva obrigatória quando status = concluído. Geração de histórico: um registro `criacao` (sem campo específico, com uma descrição textual do lançamento em `valor_novo`); um registro `edicao` por campo alterado (na edição livre e também ao concluir, que gera dois registros — `status` e `data_efetiva` — conforme Seção 13); um registro `exclusao` (com a descrição do lançamento excluído em `valor_anterior`). Todas as ações protegidas por CSRF, seguindo o padrão dos Controllers anteriores.
- **Views**: `app/views/lancamentos/index.php` (listagem com formulário de filtros por período/categoria/forma de pagamento, tabela com destaque visual de "Atrasado" para pendentes com data prevista no passado, ação inline de "Concluir" com campo de data efetiva, exclusão com `confirm()` nativo, paginação) e `app/views/lancamentos/formulario.php` (criação/edição, com o campo "Data efetiva" show/hide via JavaScript puro conforme o status selecionado).
- **`assets/css/auth.css`**: classes novas para a tela de lançamentos (`cartao-tabela`, `formulario-filtros`, `tabela-lancamentos`, `badge-sucesso`/`badge-erro`, `botao-link`, `paginacao`, etc.), reaproveitando os tokens de cor/espaçamento/tipografia já usados nas telas anteriores (aplicação definitiva do DESIGN.md em todas as telas continua sendo a Fase 9).
- Links de navegação para "Lançamentos" adicionados ao painel e ao template de categorias/formas de pagamento.
- Rotas adicionadas em `index.php`: `GET /lancamentos`, `GET /lancamentos/novo`, `POST /lancamentos/criar`, `GET /lancamentos/editar`, `POST /lancamentos/editar`, `POST /lancamentos/concluir`, `POST /lancamentos/excluir`.

**Testes executados (via `curl`, com o Apache/MySQL do XAMPP em execução, usando `http://localhost:8080/sistema_financeiro`):**
- Criar lançamento válido (despesa, categoria/forma de pagamento padrão) → sucesso, aparece na listagem; registro `criacao` gravado em `historico_alteracoes`.
- Criar com valor zero → bloqueado com "Informe um valor maior que zero."; criar com status "concluído" sem data efetiva → bloqueado com "Informe a data efetiva para um lançamento concluído."
- Editar lançamento alterando `valor` e `categoria_id` na mesma ação → dois registros de histórico distintos gerados (um por campo, cada um com seu valor anterior/novo), não um registro genérico.
- Marcar lançamento pendente como concluído → status e data efetiva atualizados; dois registros de histórico gerados (`status` e `data_efetiva`).
- Excluir lançamento → `excluido_em` preenchido no banco, registro `exclusao` gravado em `historico_alteracoes`, item some da listagem ativa.
- Usuário B tentando editar (GET) e excluir (POST) um lançamento do usuário A → ambos bloqueados com mensagem genérica ("Não foi possível localizar o lançamento informado."), evento `acesso_negado` registrado em `logs_seguranca`, lançamento do usuário A permanece ativo e inalterado no banco.
- Envio de `/lancamentos/criar` e `/lancamentos/excluir` com token CSRF inválido → bloqueado com HTTP 400 e evento `acesso_negado` registrado.
- Filtro por categoria sem lançamentos correspondentes e filtro por período que exclui todos os registros → estado vazio exibido corretamente ("Nenhum lançamento encontrado para os filtros aplicados.").
- Lançamento pendente com data prevista no passado → exibido com badge "Atrasado" na listagem (regra de apresentação, sem alterar o status armazenado).

**Resultado dos testes:** todos passaram (um ajuste foi necessário durante os testes — ver `docs/ERROS.md`, "Coluna usuario_id ambígua na listagem de lançamentos"). Dados de teste (usuários `teste.fase6@example.com` e `teste.fase6.b@example.com`, lançamentos e respectivos registros em `historico_alteracoes`/`logs_seguranca`) foram removidos do banco ao final da validação.

**Pendência conhecida:** nenhuma quanto aos critérios de pronto da fase. A paginação (20 itens/página) foi implementada e revisada no código, mas não foi exercitada com mais de 20 registros reais neste ambiente de teste (validação feita com poucos lançamentos de teste); a lógica de contagem de páginas e navegação segue o mesmo padrão já testado em outras listagens do projeto. A tela ainda usa o estilo básico já usado desde a Fase 3 (não o design system completo do `docs/DESIGN.md`) — tratado de forma consolidada na Fase 9.

## Fase 5 — Categorias e Formas de Pagamento (detalhes)

**O que foi implementado:**

- **`app/models/ItemClassificacao.php`**: model único e parametrizado pela tabela (`categorias` ou `formas_pagamento`), já que as duas entidades têm exatamente a mesma estrutura e regras (FSD, Seção 10/12). Métodos: `listarAtivos()` (itens padrão + próprios do usuário, ativos, padrão primeiro), `nomeDuplicado()` (checa duplicidade case-insensitive contra padrão e próprios), `criar()`, `buscarPorId()` e `excluirProprio()` (soft delete que só afeta linhas do próprio usuário, não padrão e ainda não excluídas — a posse é validada na própria consulta SQL, não apenas no Controller).
- **`app/services/Historico.php`**: serviço genérico de gravação em `historico_alteracoes` (entidade, ação, campo alterado, valor anterior/novo), reutilizável nas próximas fases (lançamentos, Fase 6) além da exclusão de categorias/formas de pagamento usada aqui.
- **`app/controllers/ClassificacaoController.php`**: controller único, também parametrizado (tabela, tipo de entidade, rota base, pasta de view, rótulos), instanciado duas vezes em `index.php` — uma para categorias, outra para formas de pagamento — evitando duplicar toda a lógica de listagem/criação/exclusão. Ações:
  - `listar()` — rota protegida, lista itens padrão + próprios.
  - `processarCriar()` — valida nome obrigatório, limite de 100 caracteres e duplicidade (contra padrão e próprios, case-insensitive); protegido por CSRF.
  - `processarExcluir()` — bloqueia explicitamente a exclusão de itens padrão (mensagem própria), bloqueia e registra `acesso_negado` em `logs_seguranca` ao tentar excluir item de outro usuário ou inexistente; exclusão + registro de histórico (`exclusao`) executados dentro de uma transação PDO (DML, conforme já registrado em `docs/ERROS.md`); protegido por CSRF.
- **Views**: `app/views/categorias/index.php` e `app/views/formas_pagamento/index.php` — arquivos finos que apontam para o template compartilhado `app/views/partials/lista_classificacao.php` (mesma estrutura de tela, conforme FSD Seção 12). Exibe formulário de criação, lista de itens com badge "Padrão do sistema" ou "Própria", botão de exclusão apenas nos itens próprios (com `confirm()` nativo do navegador) e mensagem informativa quando o usuário ainda não tem itens próprios.
- **`assets/css/auth.css`**: classes novas `formulario-inline`, `lista-classificacao`, `item-classificacao`, `badge`/`badge-neutro`/`badge-info` e `botao-perigo-pequeno`, reaproveitando a paleta/tipografia já usada nas telas anteriores (aplicação definitiva do DESIGN.md em todas as telas continua sendo a Fase 9).
- **`app/views/painel/index.php`** e o template de classificação: adicionados links de navegação entre Categorias, Formas de Pagamento, Conta e Painel (menu completo da Seção 16 do FSD é construído na Fase 9).
- Rotas adicionadas em `index.php`: `GET /categorias`, `POST /categorias/criar`, `POST /categorias/excluir`, `GET /formas-pagamento`, `POST /formas-pagamento/criar`, `POST /formas-pagamento/excluir`.

**Testes executados (via PowerShell/`Invoke-WebRequest`, com o Apache/MySQL do XAMPP em execução, usando `http://localhost:8080/sistema_financeiro`):**
- `GET /categorias` e `GET /formas-pagamento` autenticado → exibem as 12 categorias e 6 formas de pagamento padrão corretamente.
- Criar categoria própria "Pets" → aparece na lista com badge "Própria" e mensagem de sucesso.
- Criar categoria com nome igual a um item padrão (case-insensitive, ex.: "salário" vs. "Salário") → bloqueada com mensagem de duplicidade.
- Criar categoria com nome igual a um item próprio já existente (case-insensitive, ex.: "PETS" vs. "Pets") → bloqueada com mensagem de duplicidade.
- Criar categoria com nome vazio → bloqueada com mensagem "Informe o nome da categoria."
- Tentar excluir uma categoria padrão do sistema → bloqueada com a mensagem "Itens padrão do sistema não podem ser excluídos."; `excluido_em` permanece `NULL` no banco.
- Excluir a categoria própria "Pets" → removida da listagem ativa, `excluido_em` preenchido no banco, registro `exclusao` gravado em `historico_alteracoes` (`valor_anterior = 'Pets'`), mensagem de sucesso exibida.
- Usuário B tentando excluir uma categoria criada pelo usuário A → bloqueada com mensagem genérica ("Não foi possível localizar o item informado."), evento `acesso_negado` registrado em `logs_seguranca`, categoria do usuário A permanece ativa no banco.
- Envio de `/categorias/criar` com token CSRF inválido → bloqueado com HTTP 400.

**Resultado dos testes:** todos passaram. Dados de teste (usuários `teste.fase5@example.com` e `teste.fase5.outro@example.com`, categoria "Pets" e respectivos registros em `historico_alteracoes`/`logs_seguranca`) foram removidos do banco ao final da validação.

**Pendência conhecida:** nenhuma. As telas de Categorias e Formas de Pagamento ainda usam o estilo básico já usado desde a Fase 3 (não o design system completo do `docs/DESIGN.md`) — tratado de forma consolidada na Fase 9. As categorias/formas de pagamento criadas nesta fase ainda não podem ser vinculadas a nenhum lançamento, pois o CRUD de lançamentos é a Fase 6.

## Fase 4 — Conta do usuário (perfil) (detalhes)

**O que foi implementado:**

- **`app/models/Usuario.php`**: dois métodos novos — `atualizarEmail()` (atualiza o e-mail do usuário autenticado) e `excluir()` (soft delete: apenas preenche `excluido_em`/`atualizado_em`, sem apagar a linha nem os dados financeiros vinculados).
- **`app/services/Sessao.php`**: método `atualizarEmail()`, que reflete na sessão ativa o novo e-mail assim que a alteração é salva (sem exigir novo login).
- **`app/controllers/ContaController.php`**: rota protegida `/conta` (exige sessão ativa, como todas as rotas internas):
  - `exibir()` — mostra o e-mail atual e o formulário de edição.
  - `processarEditar()` — exige a senha atual (via `password_verify`) para qualquer alteração; valida formato e unicidade do novo e-mail (ignorando o próprio usuário); troca de senha é opcional (só valida tamanho mínimo e confirmação se os campos de nova senha forem preenchidos); registra `email_alterado` e/ou `senha_alterada` no log de segurança apenas quando o respectivo dado muda de fato.
  - `processarExcluir()` — soft delete da conta, encerra a sessão imediatamente e registra `conta_excluida` no log de segurança.
  - Proteção CSRF em ambas as ações de escrita, seguindo o mesmo padrão do `AuthController`.
- **View `app/views/conta/index.php`**: formulário único de e-mail/senha (com senha atual obrigatória), botão "Sair" e botão de perigo "Excluir minha conta" com confirmação via `confirm()` do próprio navegador (não há biblioteca de modal/JS de terceiros nesta fase — Bootstrap só é baixado na Fase 9; optou-se pelo `confirm()` nativo do JavaScript puro para atender ao requisito de "confirmação em modal" do FSD sem antecipar a Fase 9).
- **`app/views/partials/topo.php`**: passou a aceitar uma classe extra opcional (`$classeCartaoExtra`) para o cartão principal, usada pela tela de Conta (`cartao-largo`, 480px) sem alterar as demais telas, que continuam com a largura padrão de 380px.
- **`assets/css/auth.css`**: classes novas `cartao-largo`, `botao-secundario`, `botao-perigo`, `separador-secao`, `secao-titulo`, `secao-descricao` e estilo de campo somente leitura — reaproveitando a paleta/tipografia já usada nas telas de autenticação (aplicação definitiva do DESIGN.md em todas as telas é a Fase 9).
- **`app/views/painel/index.php`**: adicionado link "Minha conta" para a nova tela (o menu de navegação completo do FSD, Seção 16, é construído perto da Fase 9, junto da identidade visual final).
- Rotas adicionadas em `index.php`: `GET /conta`, `POST /conta/editar`, `POST /conta/excluir`.

**Decisão de implementação:** a exclusão da conta (soft delete) preenche apenas `excluido_em`, mantendo o `email` original na coluna (que tem índice único). Isso significa que o mesmo e-mail de uma conta excluída não pode ser reaproveitado em um novo cadastro nesta versão — o FSD não trata esse cenário explicitamente e a decisão foi não alterar o e-mail armazenado (ex.: anonimizá-lo), para preservar a rastreabilidade em `logs_seguranca` sem inventar comportamento fora do escopo definido.

**Testes executados (via `curl`, com o Apache/MySQL do XAMPP em execução, usando `http://localhost:8080/sistema_financeiro`):**
- Acesso a `/conta` sem sessão ativa → redireciona para `/login`; com sessão ativa → exibe a tela com o e-mail atual.
- Alteração de conta com senha atual incorreta → bloqueada com mensagem "Senha atual incorreta.", nenhuma alteração salva.
- Alteração de e-mail e senha simultaneamente, com senha atual correta → ambos atualizados; sessão passou a refletir o novo e-mail sem precisar de novo login; eventos `email_alterado` e `senha_alterada` registrados em `logs_seguranca`.
- Login com a senha antiga após a troca → rejeitado ("E-mail ou senha inválidos."); login com a nova senha → sucesso.
- Envio de `/conta/editar` com token CSRF inválido → bloqueado com HTTP 400 e evento `acesso_negado` registrado.
- Exclusão da própria conta → `excluido_em` preenchido no banco, sessão encerrada imediatamente; tentativa de login subsequente com as mesmas credenciais → rejeitada ("E-mail ou senha inválidos.", pois `buscarPorEmail`/`buscarPorId` sempre filtram `excluido_em IS NULL`); acesso a `/conta` após a exclusão → redireciona para `/login`.

**Resultado dos testes:** todos passaram. Dados de teste (usuário `teste.fase4(.novo)@example.com` e respectivos registros em `logs_seguranca`) foram removidos do banco ao final da validação.

**Pendência conhecida:** nenhuma. A tela de Conta ainda usa o estilo básico das telas de autenticação (não o design system completo do `docs/DESIGN.md`), assim como todas as telas construídas até aqui — isso é tratado de forma consolidada na Fase 9.

## Fase 3 — Autenticação, sessão e controle de acesso (detalhes)

**O que foi implementado:**

- **Roteamento real** em `index.php`: leitura da rota a partir de `REQUEST_URI` (relativa à subpasta do projeto, sem depender de nomes fixos como `htdocs`/`www`), despacho para Controllers, tratamento de erro genérico (`set_exception_handler`) e página 404 para rotas inexistentes.
- **`.htaccess` na raiz** do projeto: redireciona toda requisição que não seja um arquivo/pasta real para `index.php` (regra relativa à pasta, funciona em qualquer subpasta/ambiente).
- **`app/services/Sessao.php`**: início seguro de sessão (cookie `HttpOnly`, `SameSite=Lax`, `secure` quando HTTPS), expiração por inatividade (30 min, configurável), autenticação (com `session_regenerate_id`), logout, geração/validação de token CSRF por sessão e mensagens flash.
- **`app/services/LogSeguranca.php`** e **`app/services/LogErro.php`**: gravação dos eventos de segurança (Seção 19 do FSD) e erros técnicos na tabela correspondente, com contingência em arquivo (`logs/erros/erro_AAAA-MM-DD.log`) quando o banco está indisponível.
- **`app/services/EmailService.php`**: cliente SMTP próprio (sem biblioteca externa — o projeto não usa gerenciador de dependências de back-end), usado apenas no fluxo de recuperação de senha. Suporta `AUTH LOGIN` e `STARTTLS` opcional (nova chave `smtp.tls` em `config/config.php`, `false` por padrão).
- **`app/models/Usuario.php`**: criação, busca por e-mail/id, hash e verificação de senha (`password_hash`/`password_verify`), contador de tentativas de login falhas e bloqueio temporário.
- **`app/models/TokenRecuperacaoSenha.php`**: criação do token (armazenado apenas com hash SHA-256), busca de token válido (não utilizado e não expirado) e marcação de uso único.
- **`app/controllers/AuthController.php`**: cadastro, login (com bloqueio por tentativas e mensagens genéricas que não revelam se o e-mail existe), logout, solicitação de recuperação de senha (mensagem sempre genérica) e redefinição de senha via token.
- **`app/controllers/PainelController.php`**: rota protegida `/painel`, usada nesta fase apenas para validar a exigência de sessão ativa — o conteúdo real do painel financeiro é escopo da Fase 7.
- **Views** em `app/views/auth/` (cadastro, login, recuperar senha, redefinir senha), `app/views/painel/index.php` (placeholder) e `app/views/erros/erro_generico.php` (mensagem genérica ao usuário final, nunca detalhe técnico). Estilo básico em `assets/css/auth.css`, inspirado na paleta/tipografia/espaçamento do `docs/DESIGN.md` — a aplicação completa e definitiva do design system a todas as telas é a Fase 9.
- **Proteção CSRF** em todos os formulários que alteram dados (cadastro, login, logout, recuperar senha, redefinir senha), validada no Controller antes de qualquer processamento.

**Ajuste de configuração:** foram adicionadas duas chaves novas em `config/config.example.php` (e replicadas manualmente em `config/config.php`, que não é versionado):
- `smtp.tls` (`false` por padrão) — habilita `STARTTLS` quando o SMTP de produção exigir.
- `url_base` — usada para montar o link de redefinição de senha enviado por e-mail (ex.: `http://localhost/sistema_financeiro`).

**Decisão de implementação:** ao redefinir a senha com sucesso via token, o contador de tentativas de login falhas e o bloqueio temporário do usuário são zerados. O FSD não trata esse caso explicitamente, mas foi considerado o comportamento correto: o token por e-mail já comprova a posse da conta, então manter o bloqueio ativo após uma redefinição legítima só prejudicaria o usuário sem ganho de segurança. Identificado e corrigido durante os testes desta fase (ver `docs/ERROS.md`).

**Testes executados (via `curl`, com o Apache/MySQL do XAMPP em execução):**
- Cadastro com senha fraca (< 8 caracteres) → erro de validação exibido corretamente; cadastro válido → redireciona para `/login`.
- Login com senha incorreta → mensagem genérica "E-mail ou senha inválidos.", evento `login_invalido` registrado em `logs_seguranca`.
- 5 tentativas de login inválidas consecutivas → conta bloqueada; 6ª tentativa, mesmo com a senha correta, continua bloqueada; evento `bloqueio_tentativas` registrado.
- Acesso a `/painel` sem sessão ativa → redireciona para `/login`; com sessão ativa → exibe a página protegida.
- Envio de formulário com token CSRF inválido → bloqueado com HTTP 400 e evento `acesso_negado` registrado.
- Logout → encerra a sessão; acesso posterior a `/painel` volta a redirecionar para `/login`.
- Solicitação de recuperação de senha → mensagem de confirmação sempre genérica; token gerado e armazenado apenas com hash; falha de envio de e-mail (sem SMTP de teste local instalado) registrada em `logs_erros` sem quebrar o fluxo do usuário.
- Redefinição de senha com token válido → senha alterada, token marcado como utilizado, evento `redefinicao_senha_concluida` registrado; reutilização do mesmo token → rejeitada (uso único); token inválido/expirado → mensagem de erro apropriada.
- Login com a nova senha após redefinição (incluindo o caso em que a conta estava bloqueada por tentativas) → sucesso, redireciona para `/painel`.
- Rota inexistente → HTTP 404 com página genérica.
- Pastas internas (`config/`, `app/`, `database/`, `logs/`) → continuam retornando HTTP 403 diretamente pelo navegador; `assets/css/auth.css` → acessível normalmente (HTTP 200).

**Resultado dos testes:** todos passaram (um ajuste foi necessário durante os testes — ver acima e `docs/ERROS.md`). Dados de teste criados durante a validação foram removidos do banco ao final.

**Pendência conhecida (ambiente, não impede a fase):** não há um servidor SMTP de teste local (ex.: Mailpit) instalado neste ambiente XAMPP. O envio do e-mail de recuperação de senha falha silenciosamente para o usuário final (que sempre vê a mensagem de confirmação genérica) e a falha é registrada em `logs_erros`. Para testar o envio real do e-mail, instale um SMTP de teste local e ajuste `config/config.php` → `smtp` (host/porta), ou aponte para um SMTP real. Isso não bloqueia a Fase 3, pois o token é criado e validado corretamente no banco independentemente do envio do e-mail.

## Fase 2 — Banco de dados e persistência (detalhes)

- Banco MySQL local `fincontrole` criado (charset `utf8mb4`, collation `utf8mb4_unicode_ci`).
- Tabela de controle `migrations` criada; script `database/migrations/run.php` executa migrations pendentes via PHP CLI (nunca por rota pública) e não duplica execução (testado rodando duas vezes seguidas).
- 10 migrations criadas em `database/migrations/`, na ordem: `usuarios`, `categorias`, `formas_pagamento`, `lancamentos`, `historico_alteracoes`, `tokens_recuperacao_senha`, `logs_erros`, `logs_seguranca`, seed de categorias padrão, seed de formas de pagamento padrão.
- Todas as tabelas incluem campos de auditoria (`criado_em`, `atualizado_em`) e soft delete (`excluido_em`) onde aplicável, além das chaves estrangeiras e índices sugeridos na Seção 11 do FSD (ex.: `usuario_id`+`status`+`data_prevista` em `lancamentos`).
- Classe de conexão `app/models/Conexao.php` criada: conexão PDO única (singleton), lendo `config/config.php`, com `PDO::ATTR_ERRMODE_EXCEPTION`, `PDO::ATTR_EMULATE_PREPARES = false` e fuso horário da sessão MySQL fixado em `-03:00` (offset fixo de America/Sao_Paulo, que não usa mais horário de verão desde 2019 — evita depender das tabelas de fuso horário do MySQL, nem sempre carregadas no XAMPP).
- `config/config.php` real criado localmente (root/senha vazia, `fincontrole`), a partir de `config/config.example.php`; não versionado (confirmado no `.gitignore`).
- Dados padrão inseridos (decisão de implementação, já que o FSD não define os nomes exatos — ver observação abaixo): 12 categorias e 6 formas de pagamento padrão (`usuario_id` nulo, `padrao = 1`).

**Testes executados:**
- `php database/migrations/run.php` rodado duas vezes: primeira execução criou as 10 migrations; segunda execução apenas as ignorou como já executadas (nenhuma duplicação).
- Conferência das tabelas criadas via `mysql` CLI (`SHOW TABLES`, `SHOW INDEX`) — todas as 9 tabelas de negócio + `migrations` presentes, com os índices esperados.
- Conferência dos dados padrão inseridos e da acentuação correta (UTF-8) via `mysql --default-character-set=utf8mb4` e via `Conexao::obterInstancia()`.
- Teste da classe `Conexao`: leitura de categorias via PDO e confirmação de `@@session.time_zone = -03:00`.
- Teste de integridade referencial: tentativa de inserir um lançamento com `usuario_id` inexistente foi corretamente bloqueada pela FK (`SQLSTATE[23000]`, erro 1452).

**Resultado dos testes:** todos passaram.

**Observações:**
- O FSD (Seção 11) exige a inserção de categorias e formas de pagamento padrão, mas não especifica os nomes exatos. Foi feita uma escolha razoável de nomes comuns em finanças pessoais (ver lista completa em `database/migrations/009_inserir_categorias_padrao.php` e `010_inserir_formas_pagamento_padrao.php`). Se o usuário quiser uma lista diferente, os nomes podem ser ajustados antes da Fase 5 (Categorias e Formas de Pagamento) sem impacto estrutural.
- Um erro de execução foi encontrado e corrigido durante esta fase (transações PDO ao redor de DDL) — ver `docs/ERROS.md`.

## Controle de versão (Git/GitHub)

- Repositório Git local inicializado (branch `main`).
- `.gitignore` criado: protege `config/config.php` real, logs de contingência (`logs/erros/*.log`) e arquivos de SO/editor. `assets/vendor/**` propositalmente **não** é ignorado (dependências hospedadas localmente, sem CDN e sem gerenciador de pacotes — precisam ser versionadas para existir em produção).
- `.gitattributes` criado para padronizar final de linha (LF) em arquivos de texto e marcar binários (imagens, fontes) corretamente.
- Conferido que nenhum segredo foi versionado (apenas `config/config.example.php`, sem credenciais reais).
- Commit inicial criado: "Estrutura inicial do projeto".
- Repositório remoto criado no GitHub como **público**, conectado via SSH: `git@github.com:gustavooJacob/sistema-financeiro.git`.
- Chave SSH gerada nesta máquina e cadastrada na conta do GitHub para autenticação.
- Push do commit inicial realizado com sucesso (`main` → `origin/main`).

## Observações

- Nenhum arquivo de `docs/INSUMOS.md` precisou ser copiado nesta etapa (inventário indicou que não há logos, ícones ou imagens de referência disponíveis além dos próprios documentos).
- Bibliotecas de front-end (Bootstrap, fonte Inter, ícones Lucide, Chart.js) ainda **não foram baixadas** — isso deve ocorrer na Fase 9 (ou antecipadamente, se necessário para telas de fases anteriores). Ver `assets/vendor/README.md`.
- `config/config.php` real existe apenas localmente neste ambiente (root/senha vazia, `fincontrole`) — nunca versionado no Git. Após clonar o repositório em outro ambiente, é preciso copiá-lo novamente a partir de `config/config.example.php` e preencher os valores (incluindo as chaves novas `smtp.tls` e `url_base` adicionadas na Fase 3).
