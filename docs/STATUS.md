# STATUS.md — Estado Atual do Projeto FinControle

**Última atualização:** 28/07/2026 (preparação de terreno).

## Estado atual

Terreno preparado: estrutura inicial de pastas, arquivo de entrada, configuração de exemplo, proteções de acesso, plano de construção e arquivos vivos criados. Nenhuma funcionalidade de negócio foi implementada ainda.

## Fase atual

**Fase 1 — Infraestrutura e base do projeto: ✅ Concluída.**

## Checklist por fase (ver detalhamento completo em `docs/PLANO.md`)

- [x] Fase 1 — Infraestrutura e base do projeto
- [ ] Fase 2 — Banco de dados e persistência
- [ ] Fase 3 — Autenticação, sessão e controle de acesso
- [ ] Fase 4 — Conta do usuário (perfil)
- [ ] Fase 5 — Categorias e Formas de Pagamento
- [ ] Fase 6 — Lançamentos financeiros (CRUD)
- [ ] Fase 7 — Painel financeiro (Dashboard)
- [ ] Fase 8 — Histórico de alterações
- [ ] Fase 9 — Identidade visual (DESIGN.md) em todas as telas
- [ ] Fase Final — Itens transversais e revisão de entrega

## Próximo passo recomendado

Iniciar a **Fase 2 — Banco de dados e persistência**: criar o banco MySQL local, a tabela de controle `migrations`, o script de execução via CLI e as migrations de todas as tabelas descritas em `docs/FSD.md` (Seção 11), incluindo a inserção das categorias e formas de pagamento padrão.

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
