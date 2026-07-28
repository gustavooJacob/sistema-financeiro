# ERROS.md — Registro de Erros e Soluções

Este arquivo registra problemas técnicos encontrados durante a construção do FinControle e as soluções aplicadas, para evitar repetição do mesmo erro em fases futuras.

Ao registrar um novo erro, use o modelo abaixo:

```text
## <data> - <título curto do erro>

- Sintoma:
- Causa:
- Solução aplicada:
- Como evitar no futuro:
```

## 28/07/2026 - "There is no active transaction" ao rodar migrations

- Sintoma: `database/migrations/run.php` executava a primeira migration (`CREATE TABLE`) com sucesso, mas falhava na segunda com o erro `PDOException: There is no active transaction`, mascarando o erro real.
- Causa: o script envolvia cada migration em `beginTransaction()`/`commit()`/`rollBack()`, mas comandos DDL (`CREATE TABLE`) causam commit implícito no MySQL/InnoDB. Como a transação já havia sido finalizada implicitamente, qualquer chamada posterior a `commit()` ou `rollBack()` lançava uma nova exceção ("no active transaction"), substituindo a mensagem de erro original.
- Solução aplicada: removido o uso de transações PDO ao redor dos comandos de migration; cada migration passou a depender apenas de comandos idempotentes (`CREATE TABLE IF NOT EXISTS`) e do controle via tabela `migrations` para evitar execução duplicada.
- Como evitar no futuro: nunca envolver comandos DDL (CREATE/ALTER/DROP TABLE) em transações PDO no MySQL/InnoDB — eles sempre causam commit implícito. Transações PDO só devem ser usadas ao redor de comandos DML (INSERT/UPDATE/DELETE).

## 28/07/2026 - Bloqueio por tentativas não era liberado após redefinição de senha por token

- Sintoma: durante o teste da Fase 3, um usuário bloqueado por 5 tentativas de login inválidas consecutivas continuava bloqueado mesmo após redefinir a senha com sucesso através do link de recuperação por e-mail (token válido).
- Causa: `AuthController::processarRedefinirSenha` atualizava a senha e marcava o token como utilizado, mas não chamava `Usuario::resetarTentativasFalhas`, então `tentativas_login_falhas` e `bloqueado_ate` permaneciam com os valores antigos.
- Solução aplicada: adicionada a chamada a `Usuario::resetarTentativasFalhas` logo após `Usuario::atualizarSenha` em `AuthController::processarRedefinirSenha` (`app/controllers/AuthController.php`). O FSD não trata esse caso explicitamente, mas foi considerado o comportamento correto: o token por e-mail já comprova a posse da conta, então manter o bloqueio ativo após uma redefinição legítima só prejudicaria o usuário sem ganho de segurança.
- Como evitar no futuro: sempre que uma ação prova a posse da conta por um canal alternativo (e-mail, no caso), revisar se contadores de segurança relacionados (tentativas de login, bloqueio temporário) devem ser reiniciados como parte da mesma operação.

## 28/07/2026 - Coluna "usuario_id" ambígua na listagem de lançamentos

- Sintoma: `GET /lancamentos` retornava HTTP 500; o log técnico (`logs_erros`) registrava `PDOException: SQLSTATE[23000]... Column 'usuario_id' in where clause is ambiguous`.
- Causa: `Lancamento::listar()` fazia `JOIN` com `categorias` e `formas_pagamento` (para exibir os nomes na listagem), mas as condições de filtro (`WHERE usuario_id = ...`) não qualificavam a tabela de origem — e as três tabelas (`lancamentos`, `categorias`, `formas_pagamento`) têm uma coluna `usuario_id`, tornando a referência ambígua para o MySQL.
- Solução aplicada: todas as condições em `Lancamento::listar()` (`app/models/Lancamento.php`) passaram a qualificar explicitamente o alias da tabela (`l.usuario_id`, `l.data_prevista`, `l.categoria_id`, `l.forma_pagamento_id`, `l.excluido_em`).
- Como evitar no futuro: sempre qualificar as colunas com o alias da tabela em qualquer consulta que envolva `JOIN`, mesmo quando o nome da coluna parecer óbvio no contexto — várias tabelas deste projeto compartilham nomes de coluna (`usuario_id`, `nome`, `excluido_em`).

## 28/07/2026 - "Invalid parameter number" ao filtrar o histórico por categoria/forma de pagamento

- Sintoma: `GET /historico?categoria_id=17` (ou com `forma_pagamento_id`) retornava HTTP 500; `logs_erros` registrava `PDOException: SQLSTATE[HY093]: Invalid parameter number`.
- Causa: `Historico::listar()` (`app/services/Historico.php`) usava o mesmo placeholder nomeado (`:categoria_id` / `:forma_pagamento_id`) duas vezes na mesma consulta SQL (uma vez para comparar com `he.entidade_id`, outra com `l.categoria_id`/`l.forma_pagamento_id`). Como `Conexao` desativa `PDO::ATTR_EMULATE_PREPARES`, o driver MySQL nativo não aceita reutilizar o mesmo placeholder nomeado mais de uma vez na mesma query.
- Solução aplicada: cada ocorrência passou a usar um placeholder próprio (`:categoria_id_entidade` / `:categoria_id_lancamento`, `:forma_pagamento_id_entidade` / `:forma_pagamento_id_lancamento`), ambos recebendo o mesmo valor no array de parâmetros.
- Como evitar no futuro: com `PDO::ATTR_EMULATE_PREPARES = false` (usado neste projeto por `app/models/Conexao.php`), nunca repetir o mesmo nome de placeholder mais de uma vez numa consulta — usar um nome distinto por ocorrência, mesmo quando o valor é idêntico.

## 28/07/2026 - Redirects e links quebravam ao acessar o projeto pela subpasta do XAMPP

- Sintoma: ao acessar `http://localhost:8080/sistema_financeiro/`, o usuário era redirecionado para `http://localhost:8080/login` (sem o prefixo `sistema_financeiro`), resultando em "Not Found" do próprio Apache, fora da aplicação.
- Causa: `Sessao::redirecionar()` e os `action`/`href` das Views usavam caminhos absolutos a partir da raiz do domínio (ex.: `header('Location: /login')`, `action="/login"`), sem considerar que o projeto está instalado numa subpasta (`htdocs/sistema_financeiro/`), conforme previsto no FSD (Seção 4 e 25) para não depender de nomes fixos.
- Solução aplicada: adicionado `Sessao::url($caminho)`, que monta a URL prefixando a subpasta detectada dinamicamente a partir de `dirname($_SERVER['SCRIPT_NAME'])` em `index.php` (nunca um valor fixo no código). `Sessao::redirecionar()` e todas as Views de autenticação/erro/painel passaram a usar esse helper.
- Como evitar no futuro: nunca usar caminhos absolutos (`/rota`) em `header('Location: ...')`, `action` de formulário ou `href` de link — sempre passar pelo helper de URL da aplicação, especialmente em projetos que podem rodar dentro de uma subpasta em produção.
