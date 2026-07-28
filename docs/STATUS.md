# STATUS.md — Estado Atual do Projeto FinControle

**Última atualização:** 28/07/2026 (Fase 4 concluída).

## Estado atual

Módulo de autenticação e sessão implementado e testado ponta a ponta: cadastro, login, logout, recuperação/redefinição de senha, bloqueio por tentativas e proteção de rotas por sessão. O sistema agora tem roteamento real (`index.php`) e a tela de Conta/Perfil permite ao usuário autenticado editar e-mail/senha e excluir a própria conta (soft delete). O painel financeiro (`/painel`) segue como placeholder — o painel completo é a Fase 7.

## Fase atual

**Fase 4 — Conta do usuário (perfil): ✅ Concluída.**

## Checklist por fase (ver detalhamento completo em `docs/PLANO.md`)

- [x] Fase 1 — Infraestrutura e base do projeto
- [x] Fase 2 — Banco de dados e persistência
- [x] Fase 3 — Autenticação, sessão e controle de acesso
- [x] Fase 4 — Conta do usuário (perfil)
- [ ] Fase 5 — Categorias e Formas de Pagamento
- [ ] Fase 6 — Lançamentos financeiros (CRUD)
- [ ] Fase 7 — Painel financeiro (Dashboard)
- [ ] Fase 8 — Histórico de alterações
- [ ] Fase 9 — Identidade visual (DESIGN.md) em todas as telas
- [ ] Fase Final — Itens transversais e revisão de entrega

## Próximo passo recomendado

Iniciar a **Fase 5 — Categorias e Formas de Pagamento**: models, controllers e views para listagem, criação e exclusão (soft delete) de categorias e formas de pagamento próprias, respeitando os itens padrão do sistema.

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
