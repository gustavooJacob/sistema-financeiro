# MANUTENCAO.md — Guia de Manutenção do FinControle

Este documento orienta qualquer pessoa ou IA que precise **alterar, corrigir ou evoluir** o FinControle após a entrega da primeira versão. Ele complementa (não substitui) `docs/FSD.md` (especificação funcional completa) e `docs/DESIGN.md` (referência visual).

## Visão geral

O FinControle é um sistema web de **gestão financeira pessoal**, de uso individual (sem hierarquia de perfis nem administrador). Cada usuário cadastra e-mail/senha e passa a registrar receitas e despesas, classificadas por categoria e forma de pagamento, acompanhando saldo realizado/previsto do mês e um histórico permanente de alterações.

**Para quem foi criado:** pessoas físicas que querem organizar as próprias finanças, sem exigir conhecimento contábil.

**Problemas que resolve:** consolidar lançamentos financeiros num único lugar, calcular saldo do mês (realizado e previsto) automaticamente, e manter rastreabilidade completa (histórico) de tudo que foi alterado.

**Módulos principais (ver `docs/FSD.md`, Seção 6):**
- **Autenticação e Conta** — cadastro, login, recuperação de senha, edição de e-mail/senha, exclusão de conta.
- **Categorias e Formas de Pagamento** — itens padrão do sistema + itens próprios do usuário.
- **Lançamentos Financeiros** — CRUD completo (criação, edição, conclusão de pendentes, exclusão), com filtros e paginação.
- **Painel Financeiro (Dashboard)** — tela inicial após login: saldo realizado/previsto, totais, gráfico de gastos por categoria, últimos lançamentos e próximos pendentes.
- **Histórico de Alterações** — consulta somente leitura de tudo que foi criado/editado/excluído.

## Stack e ambientes

- **Linguagem:** PHP puro (sem framework MVC pronto, sem Composer, sem autoloader — classes carregadas manualmente via `require` em `index.php`).
- **Banco de dados:** MySQL, acessado via PDO (prepared statements em 100% das consultas).
- **Front-end:** HTML + CSS + JavaScript puro + Bootstrap 5.3.3, fonte Inter e ícones Lucide — todos hospedados localmente em `assets/vendor/` (sem CDN).
- **Gráfico do painel:** Chart.js, também hospedado localmente em `assets/vendor/chartjs/`.
- **Padrão arquitetural:** MVC adaptado manualmente (Models sem HTML, Views sem SQL, Controllers sem HTML embutido).
- **Ambiente local:** XAMPP (Apache + PHP + MySQL), projeto dentro de uma subpasta de `htdocs/` (ex.: `htdocs/sistema_financeiro/`).
- **Ambiente de produção:** hospedagem PHP + MySQL (Hostnet), projeto dentro de uma subpasta do diretório público. **Nenhum caminho do sistema depende do nome da pasta** — a URL base é detectada dinamicamente a partir de `dirname($_SERVER['SCRIPT_NAME'])` em `index.php` (ver `Sessao::url()`), então mover o projeto de pasta não quebra links/redirects.
- **Sem gerenciador de dependências de back-end:** não há Composer nem `npm` neste projeto. Bibliotecas de front-end são baixadas manualmente e versionadas em `assets/vendor/`.

## Como rodar localmente

1. Copiar/posicionar a pasta do projeto dentro de `htdocs/` do XAMPP (ex.: `C:\xampp\htdocs\sistema_financeiro`).
2. Iniciar Apache e MySQL pelo painel de controle do XAMPP.
3. Criar o banco de dados MySQL local `fincontrole` (ou o nome usado em `config/config.php`), se ainda não existir.
4. Copiar `config/config.example.php` para `config/config.php` e preencher os valores reais do ambiente (usuário/senha do MySQL, SMTP local para testes de e-mail, `url_base`).
5. Rodar as migrations via linha de comando (nunca por rota HTTP pública):
   ```bash
   php database/migrations/run.php
   ```
   O script é idempotente — pode ser executado novamente sem duplicar tabelas ou dados (controle pela tabela `migrations`).
6. Acessar o sistema pelo navegador, por exemplo: `http://localhost:8080/sistema_financeiro/` (a porta e a subpasta dependem da configuração local do XAMPP).
7. Não há comando de build de front-end — os arquivos de `assets/` são estáticos e servidos diretamente pelo Apache.

## Mapa de pastas

```
index.php                  # ponto de entrada único (roteamento, carregamento de classes)
config/
  config.php                # configuração real (NUNCA versionada; contém credenciais)
  config.example.php         # modelo sem segredos — copiar para config.php
app/
  controllers/               # uma classe por área funcional (AuthController, LancamentoController, ...)
  models/                    # acesso a dados via PDO; sem HTML; sem lógica de apresentação
  views/                     # HTML das telas, organizadas por módulo (auth, conta, lancamentos, ...)
    partials/                 # cabeçalho/rodapé compartilhados (app_topo.php/app_fim.php com sidebar; topo.php/fim.php para telas de pré-autenticação)
  services/                  # regras de negócio e utilitários compartilhados (Sessao, Historico, LogErro, LogSeguranca, PainelFinanceiro, EmailService, Icone)
database/
  migrations/                 # scripts de criação/alteração de schema, numerados, executados via CLI
logs/
  erros/                      # contingência em arquivo quando o banco está indisponível (logs_erros)
assets/
  css/app.css                 # implementação dos tokens do DESIGN.md sobre o Bootstrap local
  js/
  vendor/                     # Bootstrap, fonte Inter, ícones Lucide, Chart.js — hospedados localmente, versionados no Git
docs/                        # FSD, DESIGN, INSUMOS, PLANO, STATUS, ERROS, este MANUTENCAO e COMO-PEDIR-MUDANCAS
```

**Cuidados por pasta:**
- `config/`, `app/`, `database/`, `logs/` têm `.htaccess` com `Deny/Require all denied` — nunca remover essa proteção nem criar rotas que exponham caminhos dessas pastas.
- `config/config.php` nunca deve ser commitado (já está no `.gitignore`); ao clonar o repositório em uma máquina nova, é preciso recriá-lo a partir de `config.example.php`.
- `assets/vendor/` é versionado propositalmente (não há CDN nem gerenciador de pacotes — as bibliotecas precisam existir fisicamente no repositório para funcionar em produção).
- Views nunca devem conter SQL; Models nunca devem gerar HTML; Controllers nunca devem ter HTML embutido em string (ver `docs/FSD.md`, Seção 5.1).

## Banco de dados e persistência

- **Onde ficam as migrations:** `database/migrations/`, numeradas sequencialmente (`001_...` a `010_...`) e executadas em ordem por `database/migrations/run.php`.
- **Como aplicar alterações de schema:** criar um novo arquivo de migration numerado (ex.: `011_...php`), seguindo o padrão dos arquivos existentes (`CREATE TABLE IF NOT EXISTS` ou `ALTER TABLE`), e rodar `php database/migrations/run.php` novamente. A tabela de controle `migrations` registra o nome de cada script já executado e evita reexecução.
- **Cuidado importante com transações:** comandos DDL (`CREATE TABLE`, `ALTER TABLE`) causam commit implícito no MySQL/InnoDB — **nunca envolver DDL em `beginTransaction()`/`commit()`** (ver `docs/ERROS.md`, 28/07/2026). Transações PDO só devem envolver comandos DML (`INSERT`/`UPDATE`/`DELETE`), como já é feito em `ClassificacaoController::processarExcluir()`.
- **Conexão:** `app/models/Conexao.php` (singleton PDO), com `PDO::ATTR_EMULATE_PREPARES = false`. Isso significa que **placeholders nomeados não podem se repetir na mesma query** — usar um nome distinto por ocorrência mesmo quando o valor é igual (ver `docs/ERROS.md`, "Invalid parameter number").
- **Dados iniciais (seed):** categorias e formas de pagamento padrão são inseridas pelas migrations `009` e `010` (`usuario_id` nulo, `padrao = 1`). Para adicionar um novo item padrão, criar uma nova migration de seed em vez de editar as existentes.
- **Antes de alterar a estrutura de uma tabela existente:** conferir o modelo de dados completo em `docs/FSD.md`, Seção 11 (campos, índices, chaves estrangeiras sugeridas) e o impacto em soft delete/histórico — praticamente toda tabela de negócio tem `excluido_em` e é referenciada por `historico_alteracoes`.

## Autenticação, autorização e usuários

- **Como o login funciona:** e-mail e senha, sessão PHP nativa (`app/services/Sessao.php`), com `session_regenerate_id` no login, expiração por inatividade de 30 minutos (configurável em `config/config.php`) e cookie `HttpOnly`/`SameSite=Lax`.
- **Perfis:** um único perfil de usuário — não há administrador nem RBAC. Todo controle de acesso se resume a isolamento de dados por `usuario_id` autenticado.
- **Onde as permissões são verificadas:** cada Controller de rota protegida chama `Sessao::exigirAutenticacao()` antes de processar qualquer coisa, **além** da checagem central em `index.php`. Toda consulta de leitura/edição/exclusão nos Models filtra explicitamente por `usuario_id` na cláusula `WHERE` (dupla camada: Controller + query) — nunca confiar apenas na interface.
- **Cuidados ao criar novas telas/rotas protegidas:**
  1. Chamar `Sessao::exigirAutenticacao()` no início do método do Controller.
  2. Toda consulta ao banco relacionada ao usuário deve incluir `usuario_id = :usuario_id` (ou equivalente) explicitamente.
  3. Ao negar acesso (registro de outro usuário ou inexistente), usar mensagem genérica e registrar o evento em `logs_seguranca` via `LogSeguranca::registrar()` (padrão já usado em `ClassificacaoController`/`LancamentoController`).
  4. Formulários que alteram dados precisam de token CSRF (`Sessao::tokenCsrf()` / `Sessao::validarCsrf()`), seguindo o padrão dos Controllers existentes.

## Como adicionar uma nova tela

1. Definir a rota (`GET`/`POST`) e adicioná-la ao array `$rotas` em `index.php`, apontando para um método de Controller.
2. Se for uma tela nova (não uma ação de um Controller existente), criar o Controller em `app/controllers/`, seguindo o padrão dos existentes (recebe `PDO` no construtor, chama `Sessao::exigirAutenticacao()`, prepara os dados e inclui a View).
3. Registrar o `require` do novo Controller (e de Models/Services novos, se houver) em `index.php`, na seção de carregamento manual de classes.
4. Criar a View em `app/views/<modulo>/`, usando `app_topo.php`/`app_fim.php` (telas internas autenticadas, com sidebar) ou `topo.php`/`fim.php` (telas de pré-autenticação), definindo `$paginaAtiva` para destacar o item correto na sidebar.
5. Escapar toda saída dinâmica com `htmlspecialchars()`; nunca montar SQL na View.
6. Se a tela tiver formulário de escrita, gerar e validar token CSRF, seguindo o padrão já usado nos demais Controllers.
7. Seguir `docs/DESIGN.md` para cores, tipografia, espaçamento e componentes (reaproveitar as classes já existentes em `assets/css/app.css` sempre que possível).
8. Testar manualmente o fluxo completo (ver seção "Como testar alterações" abaixo).

## Como adicionar um novo campo

1. Conferir se o campo já está previsto (mesmo que informalmente) em `docs/FSD.md`; se não estiver, avaliar se está dentro do escopo do sistema (ver FSD, Seção 7 — Fora de Escopo) antes de prosseguir.
2. Criar uma nova migration em `database/migrations/` (`ALTER TABLE ... ADD COLUMN ...`), nunca editar uma migration já executada em produção.
3. Atualizar o Model correspondente (métodos de criação/atualização/listagem) para ler/gravar o novo campo.
4. Atualizar o formulário na View de criação/edição, incluindo o `label` (ver padrão do DESIGN.md, Seção 7) e o valor pré-preenchido na edição.
5. Atualizar a validação no Controller (mesmo padrão do método `validar()` de `LancamentoController`, por exemplo).
6. Atualizar a listagem/tabela relacionada, se o campo precisar aparecer nela.
7. Se o campo pertencer a uma entidade com histórico (lançamentos, categorias, formas de pagamento), verificar se a edição desse campo deve gerar um registro em `historico_alteracoes` (um registro por campo alterado, ver FSD Seção 13).
8. Atualizar `docs/FSD.md` se o campo alterar uma regra de negócio ou o modelo de dados documentado (opcional, mas recomendado para manter o FSD como fonte de verdade).

## Como adicionar uma nova regra de negócio

1. Ler `docs/FSD.md` por completo antes de alterar — a regra pode já estar coberta, ou pode conflitar com uma regra existente (ex.: cálculo de saldo, Seção 14).
2. Localizar onde a regra equivalente já é aplicada hoje (normalmente em `app/services/` para regras centrais como cálculo de saldo/histórico, ou no método `validar()` de um Controller para validações de formulário).
3. Alterar com cuidado, mantendo a separação MVC: regra de negócio não deve morar na View, e idealmente não deve morar apenas no Controller (preferir Model/Service).
4. Testar o cenário principal (regra aplicada corretamente) e os cenários de erro/borda (campo ausente, valor inválido, usuário sem permissão).
5. Se a regra afetar cálculo de saldo ou indicadores do painel, testar com múltiplos lançamentos (concluído/pendente, dentro/fora do mês) para garantir que a composição continua batendo com a Seção 14 do FSD.
6. Atualizar `docs/FSD.md` se a regra mudar um comportamento documentado.

## Como testar alterações

- **Não há suíte de testes automatizados neste projeto** (não previsto no FSD). Toda validação é manual.
- **Verificação de sintaxe:** `php -l caminho/do/arquivo.php` em todo arquivo PHP alterado.
- **Fluxos principais a testar após qualquer alteração relevante:**
  - Cadastro, login, logout.
  - Criar/editar/excluir um lançamento; marcar como concluído.
  - Criar/excluir uma categoria ou forma de pagamento própria; confirmar que itens padrão não podem ser excluídos.
  - Conferir o Painel Financeiro após alterações em lançamentos (saldo realizado/previsto, totais, gráfico).
  - Conferir a tela de Histórico após qualquer alteração/exclusão.
  - Testar isolamento entre dois usuários diferentes (usuário B não deve enxergar/alterar dados do usuário A).
  - Testar acesso a pastas internas diretamente pela URL (`config/config.php`, `app/`, `database/`, `logs/`) — deve retornar HTTP 403.
  - Testar envio de formulário com token CSRF ausente/inválido — deve ser bloqueado.
- **Como verificar erros:** consultar a tabela `logs_erros` (ou os arquivos em `logs/erros/erro_AAAA-MM-DD.log`, usados apenas quando o banco está indisponível) após os testes, para confirmar que nenhum erro técnico inesperado foi gerado.
- **Como verificar eventos de segurança:** consultar a tabela `logs_seguranca` para confirmar que eventos como `acesso_negado`, `login_invalido`, `email_alterado` etc. foram registrados corretamente quando esperado.
- **Quando atualizar `docs/ERROS.md`:** sempre que um erro técnico não óbvio for encontrado e corrigido durante o desenvolvimento ou os testes — registrar sintoma, causa, solução e como evitar no futuro, seguindo o modelo já usado no arquivo.
- **Dados de teste:** remover usuários/lançamentos/categorias criados apenas para teste ao final da validação, para não poluir o banco de desenvolvimento.

## Cuidados de segurança

Toda alteração futura deve preservar (ver `docs/FSD.md`, Seções 5.3, 5.4, 14–20, 24):

- **Injeção SQL:** toda consulta nova deve usar PDO com prepared statements/parâmetros nomeados. Nunca concatenar entrada do usuário em SQL.
- **XSS:** toda saída dinâmica em View deve passar por `htmlspecialchars()`. Dados impressos dentro de `<script>` (ex.: JSON para o Chart.js) devem usar `json_encode()` com as flags `JSON_HEX_*` já usadas em `app/views/painel/index.php`.
- **CSRF:** todo formulário que altera dados precisa de token CSRF gerado por `Sessao::tokenCsrf()` e validado no Controller antes de qualquer processamento.
- **Sessão:** rotas protegidas sempre chamam `Sessao::exigirAutenticacao()`; nunca remover essa checagem nem confiar apenas na ausência de link na interface.
- **Isolamento de dados:** toda leitura/edição/exclusão de lançamento, categoria, forma de pagamento ou histórico deve validar `usuario_id` no backend, na query, independentemente do que a interface enviar.
- **Senhas e tokens:** sempre `password_hash`/`password_verify` para senha; tokens de recuperação sempre armazenados com hash (nunca em texto puro), uso único e expiração.
- **Proteção de pastas internas:** nunca remover os `.htaccess` de `config/`, `app/`, `database/`, `logs/`; nunca criar rota pública que exponha caminho de arquivo interno.
- **Mensagens de erro:** usuário final nunca vê SQL, stack trace, nome de classe PHP ou caminho de arquivo — sempre mensagem genérica; erros técnicos vão para `logs_erros`.
- **Segredos:** nunca usar `.env`; toda configuração sensível fica em `config/config.php` (não versionado). Nunca commitar esse arquivo nem embutir credenciais em código.
- **Migrations:** nunca criar rota HTTP para executá-las; execução sempre via `php database/migrations/run.php`.
- **Uploads e APIs externas:** não fazem parte do escopo (FSD, Seção 7/21/23) — não implementar, mesmo que solicitado, sem antes confirmar explicitamente com o usuário uma mudança de escopo do FSD.

## Como registrar progresso

Toda alteração futura relevante deve:
1. Atualizar `docs/STATUS.md` com o que foi feito, quando e o resultado dos testes.
2. Registrar em `docs/ERROS.md` qualquer erro técnico não óbvio encontrado e corrigido, seguindo o modelo do arquivo.

## O que não fazer

- Não reescrever partes do sistema que já funcionam apenas por preferência estética — alterar somente o necessário para a tarefa pedida.
- Não alterar a stack (linguagem, banco de dados, ausência de framework MVC/Composer) sem confirmação explícita do usuário — isso está definido no FSD e é uma decisão consciente do projeto.
- Não remover proteção de segurança (CSRF, isolamento por `usuario_id`, `.htaccess` de pastas internas, hash de senha/token) para "resolver rápido" um bug ou destravar um teste.
- Não versionar segredos (`config/config.php` real, credenciais de SMTP/banco).
- Não pular a etapa de teste manual antes de considerar uma tarefa concluída.
- Não implementar funcionalidades fora do escopo do FSD (uploads, exportações, APIs externas, RBAC, lançamentos recorrentes, múltiplas moedas etc. — ver FSD, Seção 7) sem antes confirmar explicitamente com o usuário que o escopo do FSD está sendo ampliado.
- Não alterar várias áreas do sistema numa única mudança sem explicar o impacto de cada uma — preferir mudanças pequenas e testáveis isoladamente.
