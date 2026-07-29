# FinControle

Sistema web de gestão financeira pessoal — registro de receitas e despesas, categorização, painel financeiro com indicadores do mês e histórico completo de alterações.

Projeto pessoal desenvolvido do zero em **PHP puro (sem framework)**, com arquitetura MVC construída manualmente, para praticar organização de código, segurança de aplicações web e modelagem de banco de dados sem depender de um framework pronto.

## Sobre o projeto

O FinControle permite que uma pessoa registre suas movimentações financeiras (receitas e despesas), classifique-as por categoria e forma de pagamento, e acompanhe em um painel único o saldo realizado, o saldo previsto e os gastos do mês por categoria. Cada lançamento tem status (pendente/concluído) e toda alteração relevante — criação, edição, exclusão — fica registrada em um histórico permanente, mesmo depois de o item original ser excluído.

O sistema é multiusuário, mas sem hierarquia de perfis: cada conta enxerga e manipula exclusivamente os próprios dados, com essa validação sempre feita no backend (nunca só na interface).

### Funcionalidades

- **Autenticação:** cadastro, login com bloqueio por tentativas inválidas, recuperação de senha por e-mail (token de uso único), edição de e-mail/senha e exclusão da própria conta.
- **Categorias e formas de pagamento:** itens padrão do sistema (somente leitura) + itens próprios de cada usuário, com criação e exclusão (soft delete).
- **Lançamentos financeiros:** CRUD completo (criar, editar, marcar como concluído, excluir), com filtros por período, categoria e forma de pagamento, e paginação.
- **Painel financeiro (dashboard):** saldo realizado, saldo previsto, total de receitas/despesas do mês corrente, gráfico de gastos por categoria (Chart.js), últimos lançamentos e próximos pendentes com destaque para atrasados.
- **Histórico de alterações:** log somente leitura de tudo que foi criado, editado ou excluído, com filtros, preservado mesmo após a exclusão do item original.

## Stack e decisões técnicas

| Camada | Tecnologia |
| --- | --- |
| Linguagem | PHP (orientado a objetos, sem framework, sem Composer/autoloader) |
| Banco de dados | MySQL, acesso via PDO com prepared statements |
| Front-end | HTML, CSS e JavaScript puro + Bootstrap, fonte Inter e ícones Lucide |
| Gráficos | Chart.js |
| Arquitetura | MVC adaptado manualmente (Model / View / Controller / Service) |

Algumas decisões propositais do projeto:

- **Sem framework e sem Composer** — todo o roteamento, a camada de acesso a dados e a estrutura MVC foram implementados manualmente, para entender o que um framework normalmente resolve por baixo dos panos.
- **Sem dependências via CDN** — Bootstrap, fonte e ícones ficam hospedados localmente em `assets/vendor/`, versionados no repositório.
- **Sem `.env`** — configuração sensível concentrada em `config/config.php` (PHP puro), fora de qualquer rota pública e nunca versionada.
- **Migrations próprias** — schema do banco versionado em `database/migrations/`, executado apenas via linha de comando (`php database/migrations/run.php`), nunca por rota HTTP.

### Segurança implementada

- Prepared statements (PDO) em 100% das consultas — sem concatenação de entrada do usuário em SQL.
- Escape de saída (`htmlspecialchars`) em toda View, e `JSON_HEX_*` nos dados impressos em `<script>`.
- Proteção CSRF em todo formulário que altera dados.
- Senhas com `password_hash`/`password_verify`; tokens de recuperação de senha armazenados com hash e uso único.
- Bloqueio de login após 5 tentativas inválidas consecutivas (15 minutos).
- Sessão com expiração por inatividade (30 minutos) e regeneração de ID no login.
- Isolamento de dados por usuário validado no backend, sempre na própria cláusula `WHERE` da query — nunca só na interface.
- Log de erros técnicos e log de segurança (tentativas de acesso negado, alterações sensíveis, etc.), com contingência em arquivo quando o banco está indisponível.
- Pastas internas (`config/`, `app/`, `database/`, `logs/`) bloqueadas contra acesso direto por URL via `.htaccess` — `index.php` é o único ponto de entrada público.

Mais detalhes de arquitetura, modelo de dados e regras de negócio em [`docs/FSD.md`](docs/FSD.md); referência visual completa em [`docs/DESIGN.md`](docs/DESIGN.md).

## Estrutura do projeto

```
index.php                  # ponto de entrada único da aplicação (roteamento)
config/                    # configuração sensível (não versionada)
app/
├── controllers/           # recebem a requisição, validam e decidem a resposta
├── models/                # entidades e acesso ao banco (PDO)
├── views/                 # HTML, organizado por módulo
└── services/               # regras de negócio compartilhadas (sessão, histórico, painel, etc.)
database/migrations/       # schema do banco, versionado e executado via CLI
assets/{css,js,vendor}/    # estilos, scripts e bibliotecas front-end hospedadas localmente
docs/                       # especificação funcional, design e documentação de manutenção
```

## Como rodar localmente

Pré-requisitos: [XAMPP](https://www.apachefriends.org/) (ou Apache + PHP 8+ + MySQL equivalentes).

1. Clone o repositório dentro da pasta `htdocs` do XAMPP:

```bash
git clone https://github.com/gustavooJacob/sistema-financeiro.git
```

2. Crie um banco de dados MySQL vazio, por exemplo `fincontrole`.

3. Copie o arquivo de configuração de exemplo e preencha com os dados do seu ambiente:

```bash
cp config/config.example.php config/config.php
```

Edite `config/config.php` com os dados de conexão do seu MySQL local (host, nome do banco, usuário e senha).

4. Rode as migrations para criar as tabelas e os dados padrão (categorias e formas de pagamento):

```bash
php database/migrations/run.php
```

5. Suba o Apache e o MySQL pelo painel do XAMPP e acesse no navegador:

```
http://localhost/sistema_financeiro
```

6. Crie uma conta na tela de cadastro e faça login para acessar o painel.

> O envio real do e-mail de recuperação de senha depende de um servidor SMTP configurado em `config/config.php` (host/porta/credenciais). Sem SMTP configurado, o fluxo de solicitação continua funcionando normalmente (o token é gerado e validado no banco), mas o e-mail não chega ao destinatário — útil para testar a interface sem depender de um SMTP real.

## Estado do projeto

Todas as fases funcionais da primeira versão estão concluídas e passaram por uma revisão dedicada de segurança e qualidade (CSRF, isolamento de dados por usuário, SQL injection, XSS, proteção de pastas internas, tratamento de erros). Detalhes de cada etapa de desenvolvimento e dos testes manuais realizados em [`docs/STATUS.md`](docs/STATUS.md).

Este é um projeto individual, sem fins comerciais, feito para fins de estudo e portfólio.
