# STATUS.md — Estado Atual do Projeto FinControle

**Última atualização:** 28/07/2026 (Fase 2 concluída).

## Estado atual

Banco de dados MySQL criado e estruturado por migrations controladas via CLI. Todas as tabelas do modelo de dados (FSD Seção 11) existem, com índices, chaves estrangeiras, soft delete e campos de auditoria. Categorias e formas de pagamento padrão inseridas. Ainda não há telas nem regras de negócio (autenticação, lançamentos etc.) implementadas.

## Fase atual

**Fase 2 — Banco de dados e persistência: ✅ Concluída.**

## Checklist por fase (ver detalhamento completo em `docs/PLANO.md`)

- [x] Fase 1 — Infraestrutura e base do projeto
- [x] Fase 2 — Banco de dados e persistência
- [ ] Fase 3 — Autenticação, sessão e controle de acesso
- [ ] Fase 4 — Conta do usuário (perfil)
- [ ] Fase 5 — Categorias e Formas de Pagamento
- [ ] Fase 6 — Lançamentos financeiros (CRUD)
- [ ] Fase 7 — Painel financeiro (Dashboard)
- [ ] Fase 8 — Histórico de alterações
- [ ] Fase 9 — Identidade visual (DESIGN.md) em todas as telas
- [ ] Fase Final — Itens transversais e revisão de entrega

## Próximo passo recomendado

Iniciar a **Fase 3 — Autenticação, sessão e controle de acesso**: model `Usuario`, controller de autenticação (cadastro/login/logout), views de auth, fluxo de recuperação de senha, bloqueio por tentativas e proteção de sessão.

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
- `config/config.php` real ainda não existe neste ambiente — apenas `config/config.example.php`. Deve ser criado localmente a partir do exemplo, sem versionar no Git.
