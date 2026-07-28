# CLAUDE.md — Contexto para IA (FinControle)

Este arquivo orienta qualquer IA que trabalhe neste projeto, em qualquer chat ou ambiente. Leia-o por completo antes de iniciar qualquer tarefa.

## Idioma

Responder sempre em português do Brasil.

## O que é o projeto

FinControle é um sistema web de gestão financeira pessoal (receitas, despesas, categorias, formas de pagamento, painel financeiro e histórico de alterações), para uso individual, sem hierarquia de perfis. A especificação completa está em `docs/FSD.md` e a referência visual em `docs/DESIGN.md`.

## Stack, arquitetura e restrições (definidas no FSD — não alterar sem confirmação do usuário)

- **Linguagem:** PHP (procedural/orientado a objetos, sem framework MVC pronto).
- **Banco de dados:** MySQL.
- **Front-end:** HTML, CSS, JavaScript puro + Bootstrap, hospedado localmente (sem CDN).
- **Padrão arquitetural:** MVC adaptado manualmente (ver `docs/FSD.md`, Seção 5.1).
- **Restrições técnicas importantes:**
  - Não usar arquivo `.env`. Configuração sensível fica em `config/config.php` (PHP puro), carregado apenas via `require`/`include`.
  - Nenhuma biblioteca de front-end via CDN (Bootstrap, fonte Inter, ícones Lucide, Chart.js — tudo em `assets/vendor/`).
  - Sem APIs, integrações externas, uploads/anexos, exportações ou relatórios além do descrito no FSD.
  - Sem RBAC ou perfil de administrador — existe apenas um perfil de usuário, com isolamento estrito de dados por `usuario_id`.

## Ambientes

- **Desenvolvimento:** XAMPP (Apache + PHP + MySQL local), projeto em subpasta de `htdocs/`.
- **Testes/homologação:** não há ambiente dedicado; testes ocorrem localmente no XAMPP.
- **Produção:** hospedagem PHP + MySQL (Hostnet), projeto em subpasta do diretório público.
- O projeto não deve depender de nomes fixos como `htdocs`, `www` ou `public_html` na sua arquitetura.

## Estrutura de pastas

```
index.php                  # ponto de entrada único da aplicação
config/
  config.php                # configuração real (nunca versionada)
  config.example.php         # modelo sem segredos reais
app/
  controllers/
  models/
  views/                    # subpastas: auth, conta, lancamentos, categorias, formas_pagamento, painel, historico
  services/
database/
  migrations/                # scripts de migration, executados via PHP CLI
logs/
  erros/                     # log de contingência em arquivo
assets/
  css/
  js/
  vendor/                    # Bootstrap, fonte Inter, ícones Lucide, Chart.js (hospedados localmente)
docs/
  FSD.md
  DESIGN.md
  INSUMOS.md
  PLANO.md
  STATUS.md
  ERROS.md
```

As pastas `config/`, `app/`, `database/` e `logs/` são protegidas contra acesso direto por URL (`.htaccess` com `Deny/Require all denied`). O único ponto de entrada público é `index.php`.

## Comandos principais

- Nenhum gerenciador de dependências de back-end é usado (PHP puro, sem Composer previsto no FSD).
- Migrations: executadas via linha de comando PHP CLI (ex.: `php database/migrations/run.php`), nunca por rota HTTP pública.
- Não há comandos de build de front-end (Bootstrap/JS/CSS são arquivos estáticos baixados manualmente para `assets/vendor/`).
- Servidor local: Apache do XAMPP servindo a subpasta do projeto dentro de `htdocs/`.

## Protocolo dos arquivos vivos

Antes de iniciar qualquer trabalho:
1. Ler `docs/FSD.md`.
2. Ler `docs/DESIGN.md`.
3. Ler `docs/INSUMOS.md`.
4. Ler `docs/PLANO.md`.
5. Ler `docs/STATUS.md`.
6. Ler `docs/ERROS.md`.

Use sempre caminhos relativos à raiz do projeto.
Não transformar estes caminhos em links absolutos.
Não usar links `file:///`.
Não registrar caminhos locais da máquina atual dentro do `CLAUDE.md`.

Ao terminar qualquer trabalho:
1. Atualizar `docs/STATUS.md`.
2. Registrar erros e soluções em `docs/ERROS.md`, se houver.
3. Informar ao usuário o que foi feito.
4. Informar como testar ou validar a entrega.

## Boas práticas

- Código claro, funções pequenas, nomes descritivos.
- Comentários úteis em português do Brasil apenas quando esclarecem uma decisão não óbvia.
- Sem duplicação desnecessária de código.
- Sem funcionalidades fora do escopo definido em `docs/FSD.md` (ver Seção 7 — Fora de Escopo).
- Construir uma fase de cada vez, seguindo `docs/PLANO.md`, sem antecipar fases futuras.

## Interface

Seguir `docs/DESIGN.md` em todas as telas: paleta de cores, tipografia (Inter, hospedada localmente), espaçamentos em grade de 4px, componentes (cards, botões, badges, tabelas, sidebar de navegação por ícones), estados visuais (sucesso, erro, aviso, vazio, carregando) e ícones (Lucide, outline, hospedados localmente).

## Regras de segurança

Adequadas à stack PHP + MySQL sem framework, conforme `docs/FSD.md` (Seções 5.3, 5.4, 14–20, 24):

- **Injeção SQL:** toda consulta ao banco deve usar prepared statements (PDO ou mysqli com parâmetros). Nenhuma concatenação direta de entrada do usuário em SQL.
- **XSS:** toda saída de dados dinâmicos nas Views deve ser escapada (ex.: `htmlspecialchars`) antes de ser impressa em HTML.
- **CSRF:** formulários que alteram dados (criação, edição, exclusão) devem usar token CSRF por sessão, validado no Controller.
- **Senhas:** armazenadas sempre com hash seguro (`password_hash`/`password_verify` do PHP), nunca em texto puro. Mínimo de 8 caracteres (FSD, Seção 27).
- **Sessão:** sessão PHP nativa; expiração por inatividade de 30 minutos; toda rota protegida valida sessão ativa antes de processar a requisição.
- **Controle de acesso:** toda leitura, edição ou exclusão de lançamento, categoria, forma de pagamento ou histórico deve validar no backend que o registro pertence ao `usuario_id` autenticado, independentemente do que a interface envie.
- **Tokens de recuperação de senha:** armazenados com hash, uso único, expiração de 1 hora (FSD, Seção 27).
- **Bloqueio por tentativas:** 5 tentativas inválidas consecutivas bloqueiam login por 15 minutos (FSD, Seção 27).
- **Configuração sensível:** nunca em `.env`; sempre em `config/config.php`, fora de rota pública e protegido por `.htaccess`.
- **Mensagens de erro:** o usuário final nunca vê mensagem técnica, stack trace ou detalhe de SQL; mensagens de login inválido nunca revelam se o e-mail existe.
- **Logs:** log de erros técnicos (`logs_erros`, com contingência em arquivo em `logs/erros/`) e log de segurança (`logs_seguranca`) nunca expostos por rota pública.
- **Uploads:** não há upload nesta versão; não implementar.
- **Isolamento de dados:** cada usuário só acessa seus próprios dados; categorias/formas de pagamento padrão são somente leitura para todos os usuários.
- **Proteção de pastas internas:** `config/`, `app/`, `database/`, `logs/` nunca acessíveis diretamente por URL.
