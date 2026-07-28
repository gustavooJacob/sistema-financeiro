# CLAUDE.md — Contexto para IA (FinControle) — MODO MANUTENÇÃO

Este arquivo orienta qualquer IA que trabalhe neste projeto, em qualquer chat ou ambiente. Leia-o por completo antes de iniciar qualquer tarefa.

A primeira versão do FinControle está **completa** (todas as fases do `docs/PLANO.md` concluídas, incluindo revisão de segurança e qualidade — ver `docs/STATUS.md`). A partir de agora, o projeto está em **modo manutenção**: toda tarefa é uma alteração pontual (novo campo, nova tela, correção de erro, ajuste de regra de negócio ou visual) sobre um sistema já em funcionamento — não uma nova fase de construção do zero.

## Idioma

Responder sempre em português do Brasil.

## O que é o projeto

FinControle é um sistema web de gestão financeira pessoal (receitas, despesas, categorias, formas de pagamento, painel financeiro e histórico de alterações), para uso individual, sem hierarquia de perfis. A especificação funcional completa está em `docs/FSD.md`, a referência visual em `docs/DESIGN.md`, e o guia de manutenção detalhado em `docs/MANUTENCAO.md`.

## Stack, arquitetura e restrições (definidas no FSD — não alterar sem confirmação explícita do usuário)

- **Linguagem:** PHP (procedural/orientado a objetos, sem framework MVC pronto, sem Composer/autoloader).
- **Banco de dados:** MySQL, acessado via PDO com prepared statements.
- **Front-end:** HTML, CSS, JavaScript puro + Bootstrap, fonte Inter e ícones Lucide, hospedados localmente em `assets/vendor/` (sem CDN).
- **Padrão arquitetural:** MVC adaptado manualmente (ver `docs/FSD.md`, Seção 5.1, e `docs/MANUTENCAO.md`).
- **Restrições técnicas importantes:**
  - Não usar arquivo `.env`. Configuração sensível fica em `config/config.php` (PHP puro), carregado apenas via `require`/`include`, nunca versionado.
  - Nenhuma biblioteca de front-end via CDN — tudo em `assets/vendor/`.
  - Sem APIs, integrações externas, uploads/anexos, exportações ou relatórios além do descrito no FSD (ver Seção 7 — Fora de Escopo).
  - Sem RBAC ou perfil de administrador — existe apenas um perfil de usuário, com isolamento estrito de dados por `usuario_id`.

## Ambientes

- **Desenvolvimento:** XAMPP (Apache + PHP + MySQL local), projeto em subpasta de `htdocs/`.
- **Testes/homologação:** não há ambiente dedicado; testes ocorrem localmente no XAMPP.
- **Produção:** hospedagem PHP + MySQL (Hostnet), projeto em subpasta do diretório público.
- O projeto não depende de nomes fixos como `htdocs`, `www` ou `public_html` — a URL base é detectada dinamicamente (ver `docs/MANUTENCAO.md`).

## Resumo da estrutura

```
index.php                  # ponto de entrada único da aplicação (roteamento)
config/config.php           # configuração real (nunca versionada)
app/{controllers,models,views,services}/
database/migrations/        # scripts de schema, executados via PHP CLI
logs/erros/                 # contingência em arquivo
assets/{css,js,vendor}/     # vendor hospedado localmente, versionado no Git
docs/                       # FSD, DESIGN, INSUMOS, PLANO, STATUS, ERROS, MANUTENCAO, COMO-PEDIR-MUDANCAS
```

`config/`, `app/`, `database/` e `logs/` são protegidas contra acesso direto por URL (`.htaccess`). O único ponto de entrada público é `index.php`. Detalhamento completo de cada pasta em `docs/MANUTENCAO.md`.

## Documentos obrigatórios para ler antes de alterar

Antes de qualquer alteração:
1. Ler `docs/MANUTENCAO.md` (guia de manutenção — como adicionar telas, campos, regras de negócio, e cuidados de segurança).
2. Ler `docs/FSD.md` (especificação funcional completa — regras de negócio, modelo de dados, escopo).
3. Ler `docs/DESIGN.md`, se a alteração envolver interface.
4. Ler `docs/STATUS.md` (estado atual do projeto).
5. Ler `docs/ERROS.md` (erros já conhecidos, para não repeti-los).
6. Entender o pedido do usuário.
7. Explicar o plano antes de alterar arquivos.

## Protocolo para mudanças futuras

Depois de qualquer alteração:
1. Testar o que foi alterado (não há suíte de testes automatizados — validação é manual; ver "Como testar alterações" em `docs/MANUTENCAO.md`).
2. Atualizar `docs/STATUS.md`.
3. Registrar erro e solução em `docs/ERROS.md`, se houver.
4. Fazer commit ou entregar os comandos para o usuário executar (nunca `git push` sem confirmação explícita).
5. Explicar ao usuário como validar a entrega.

## Boas práticas

- Código claro, funções pequenas, nomes descritivos.
- Comentários úteis em português do Brasil apenas quando esclarecem uma decisão não óbvia.
- Sem duplicação desnecessária de código.
- Sem funcionalidades fora do escopo definido em `docs/FSD.md` (Seção 7 — Fora de Escopo) — se o pedido do usuário expandir o escopo, confirmar explicitamente antes de implementar.
- Mudanças pequenas e isoladas, uma de cada vez; explicar o impacto antes de alterar múltiplas áreas do sistema.

## Interface

Seguir `docs/DESIGN.md` em todas as telas: paleta de cores, tipografia (Inter, hospedada localmente), espaçamentos em grade de 4px, componentes (cards, botões, badges, tabelas, sidebar de navegação por ícones), estados visuais (sucesso, erro, aviso, vazio, carregando) e ícones (Lucide, outline, hospedados localmente). Reaproveitar as classes já existentes em `assets/css/app.css` sempre que possível, em vez de criar CSS novo duplicado.

## Regras de segurança (preservar em toda alteração)

Adequadas à stack PHP + MySQL sem framework, conforme `docs/FSD.md` (Seções 5.3, 5.4, 14–20, 24) e `docs/MANUTENCAO.md`:

- **Injeção SQL:** toda consulta ao banco deve usar prepared statements (PDO). Nenhuma concatenação direta de entrada do usuário em SQL. Atenção: `PDO::ATTR_EMULATE_PREPARES = false` está ativo — não repetir o mesmo placeholder nomeado na mesma query (ver `docs/ERROS.md`).
- **XSS:** toda saída de dados dinâmicos nas Views deve ser escapada (`htmlspecialchars`) antes de ser impressa em HTML; dados impressos em `<script>` (JSON) devem usar as flags `JSON_HEX_*`.
- **CSRF:** formulários que alteram dados (criação, edição, exclusão) devem usar token CSRF por sessão, validado no Controller.
- **Senhas:** armazenadas sempre com hash seguro (`password_hash`/`password_verify`), nunca em texto puro. Mínimo de 8 caracteres.
- **Sessão:** sessão PHP nativa; expiração por inatividade de 30 minutos; toda rota protegida chama `Sessao::exigirAutenticacao()` antes de processar a requisição.
- **Controle de acesso:** toda leitura, edição ou exclusão de lançamento, categoria, forma de pagamento ou histórico deve validar no backend, na própria query, que o registro pertence ao `usuario_id` autenticado — nunca confiar apenas na interface.
- **Tokens de recuperação de senha:** armazenados com hash, uso único, expiração de 1 hora.
- **Bloqueio por tentativas:** 5 tentativas inválidas consecutivas bloqueiam login por 15 minutos.
- **Configuração sensível:** nunca em `.env`; sempre em `config/config.php`, fora de rota pública e protegido por `.htaccess`; nunca versionar esse arquivo.
- **Mensagens de erro:** o usuário final nunca vê mensagem técnica, stack trace ou detalhe de SQL; mensagens de login inválido nunca revelam se o e-mail existe.
- **Logs:** log de erros técnicos (`logs_erros`, com contingência em arquivo em `logs/erros/`) e log de segurança (`logs_seguranca`) nunca expostos por rota pública.
- **Uploads:** não há upload nesta versão; não implementar sem confirmação explícita de mudança de escopo.
- **Isolamento de dados:** cada usuário só acessa seus próprios dados; categorias/formas de pagamento padrão são somente leitura para todos os usuários.
- **Proteção de pastas internas:** `config/`, `app/`, `database/`, `logs/` nunca acessíveis diretamente por URL — nunca remover os `.htaccess`.
- **Migrations:** nunca criar rota HTTP pública para executá-las; sempre via `php database/migrations/run.php`.

## Cuidados para não quebrar funcionalidades existentes

- Não reescrever partes do sistema que já funcionam sem necessidade — alterar apenas o que a tarefa exige.
- Não alterar a stack (linguagem, banco de dados, ausência de framework/Composer) sem confirmação explícita do usuário.
- Não remover proteção de segurança para "resolver rápido" um bug ou destravar um teste.
- Ao adicionar campo/tela/regra, verificar impacto em: validação, histórico de alterações, soft delete, cálculo de saldo/painel (se envolver lançamentos) e isolamento por usuário.
- Consultar `docs/ERROS.md` antes de mexer em migrations (DDL nunca em transação), consultas com `JOIN` (qualificar colunas ambíguas) ou queries com placeholders repetidos.

## Orientação para testar antes de concluir

Não há suíte de testes automatizados. Validação é sempre manual, no XAMPP local:
- `php -l` em todo arquivo PHP alterado.
- Testar o fluxo principal da alteração e ao menos um cenário de erro/borda.
- Testar isolamento entre dois usuários diferentes quando a alteração envolver dados de usuário.
- Verificar `logs_erros` e `logs_seguranca` após os testes.
- Remover dados de teste do banco ao final.

Detalhamento completo em `docs/MANUTENCAO.md`, seção "Como testar alterações".

## Orientação para atualizar STATUS e ERROS

- Sempre atualizar `docs/STATUS.md` ao final de qualquer alteração relevante, descrevendo o que foi feito e o resultado dos testes.
- Sempre registrar em `docs/ERROS.md` qualquer erro técnico não óbvio encontrado e corrigido, seguindo o modelo já usado no arquivo (Sintoma / Causa / Solução aplicada / Como evitar no futuro).

## Orientação para commit

Após qualquer alteração relevante e testada, oferecer ao usuário um commit com mensagem clara descrevendo a mudança (ou entregar os comandos `git add`/`git commit` para o usuário executar, se não puder rodar diretamente). Nunca fazer `git push` sem confirmação explícita do usuário. Nunca commitar `config/config.php` real nem qualquer credencial.
