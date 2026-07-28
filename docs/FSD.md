# DOCUMENTO DE ESPECIFICAÇÃO FUNCIONAL (FSD)

## 1. Visão Geral

**Nome do sistema:** FinControle (nome provisório).

**Objetivo principal:** o FinControle é um sistema web de gestão financeira pessoal que permite a uma pessoa registrar receitas e despesas, classificá-las por categoria e forma de pagamento, acompanhar o saldo do mês (realizado e previsto) e identificar rapidamente padrões e pendências de gastos.

**Resumo do funcionamento:** cada usuário cria uma conta com e-mail e senha e passa a registrar seus lançamentos financeiros (receitas e despesas), classificando-os por categoria e forma de pagamento. Cada lançamento tem um status (pendente ou concluído) e duas datas (prevista e efetiva). O sistema calcula automaticamente o saldo realizado (somente lançamentos concluídos) e o saldo previsto (concluídos + pendentes) do mês corrente, seguindo o calendário civil. Um painel financeiro central resume a situação do mês, e toda alteração relevante feita em lançamentos, categorias e formas de pagamento fica registrada em um histórico permanente, mesmo após exclusões.

**Público usuário:** pessoas físicas que desejam organizar suas finanças pessoais no dia a dia, sem exigir conhecimento contábil.

**Contexto de uso:** uso individual. Cada usuário acessa e controla exclusivamente seus próprios dados; não há compartilhamento de dados entre contas, nem qualquer hierarquia de perfis ou administrador nesta versão.

**Observações relevantes para implementação:**
- O sistema deve ser implementado seguindo organização inspirada em MVC (Model-View-Controller), mesmo sem uso de framework MVC pronto.
- O sistema não deve conter APIs, integrações externas, uploads/anexos, exportações ou relatórios além do que está explicitamente descrito neste documento.
- O sistema será criado inicialmente em ambiente local (XAMPP) e futuramente publicado em hospedagem com PHP e MySQL (Hostnet), podendo ocupar uma subpasta do ambiente público em ambos os casos.

## 2. Documentos do Projeto para Implementação

A IA codificadora deverá utilizar, para implementar o sistema:

- `docs/FSD.md` — este documento, que consolida todas as decisões funcionais e técnicas necessárias.
- `docs/DESIGN.md` — documento de referência visual (paleta de cores, tipografia, espaçamentos, componentes de UI, ícones), a ser aplicado às telas descritas neste FSD.

Este FSD já consolida todas as decisões funcionais e técnicas necessárias para a implementação do sistema. Nenhum outro documento é necessário para compreender ou codificar o sistema.

## 3. Stack Definida

- **Linguagem de programação:** PHP.
- **Banco de dados:** MySQL.
- **Tecnologias de interface:** HTML, CSS, JavaScript puro.
- **Bibliotecas ou frameworks:** Bootstrap, utilizado localmente (arquivos baixados no projeto, sem uso de CDN).
- **Dependências importantes:** nenhum framework MVC pronto (como Laravel ou Symfony) será utilizado; a organização MVC será construída manualmente seguindo a estrutura de diretórios definida na Seção 5.
- **Padrão arquitetural:** MVC (Model-View-Controller), adaptado a PHP procedural/orientado a objetos sem framework.
- **Restrições técnicas:**
  - Não deve ser utilizado arquivo `.env` para armazenar credenciais ou configurações sensíveis.
  - Não devem ser usadas bibliotecas externas via CDN; toda dependência de front-end (Bootstrap, ícones, etc.) deve estar hospedada localmente dentro do projeto.
- **Observações sobre uso local de bibliotecas:** o Bootstrap e qualquer outra biblioteca de front-end devem ser baixados e mantidos dentro do `[Diretório do Projeto - Repositório]` (por exemplo, em `assets/vendor/`), sem dependência de conexão externa em tempo de execução.

## 4. Ambientes do Projeto

- **Desenvolvimento local:** XAMPP (Apache + PHP + MySQL rodando localmente). O projeto ficará dentro de uma subpasta de `htdocs/`, por exemplo `htdocs/fincontrole/`.
- **Testes/homologação:** não haverá ambiente dedicado nesta versão. Os testes ocorrerão localmente, no próprio ambiente XAMPP, antes da publicação.
- **Produção:** hospedagem com suporte a PHP e MySQL (Hostnet). O projeto ficará dentro de uma subpasta do diretório público da hospedagem, por exemplo `www/fincontrole/`.
- **Observações sobre deploy:** o processo detalhado de publicação (deploy) para o ambiente de produção não faz parte do escopo deste FSD e deverá ser tratado em uma etapa própria, fora deste documento. O FSD apenas garante que a estrutura do projeto seja compatível com publicação em subpasta, sem depender de o projeto ocupar sozinho a pasta pública principal da hospedagem.

## 5. Arquitetura do Sistema

O sistema deverá ser organizado a partir do `[Diretório do Projeto - Repositório]`, que representa a pasta do projeto versionada no repositório. Essa pasta poderá ser posicionada dentro da pasta pública adequada a cada ambiente:

- no XAMPP, normalmente dentro de `htdocs/fincontrole/`;
- na Hostnet, normalmente dentro de `www/fincontrole/`;
- em outras hospedagens, dentro de `public_html/fincontrole/` ou pasta pública equivalente.

O usuário poderá utilizar subpastas para manter vários sistemas no mesmo XAMPP ou na mesma hospedagem, portanto o FSD não assume que o projeto ocupará sozinho a pasta pública principal.

### 5.1 Aplicação do padrão MVC

- **Model:** classes/arquivos responsáveis por representar as entidades do sistema (usuário, lançamento, categoria, forma de pagamento, histórico de alterações, token de recuperação de senha) e por toda a comunicação com o banco de dados (consultas, inserções, atualizações, exclusões lógicas). Os Models não devem conter HTML nem lógica de apresentação.
- **Controller:** arquivos/classes responsáveis por receber as requisições do usuário (via formulários ou ações de tela), validar entradas, acionar as regras de negócio (diretamente ou por meio de classes de serviço) e decidir qual View será exibida ou qual redirecionamento ocorrerá. Os Controllers não devem conter consultas SQL diretas nem HTML embutido.
- **View:** arquivos responsáveis por renderizar a interface (HTML + Bootstrap local + JavaScript de apoio), recebendo apenas os dados já processados pelo Controller. As Views não devem conter lógica de negócio nem acesso direto ao banco de dados.
- **Fluxo das requisições:** toda requisição do navegador deve passar pelo arquivo de entrada da aplicação (`index.php`), que direciona a requisição para o Controller apropriado com base na rota solicitada. O Controller consulta/atualiza os Models necessários e, ao final, seleciona a View a ser renderizada, passando os dados já preparados.
- **Organização das regras de negócio:** regras de negócio centrais (cálculo de saldo, identificação de atrasados, geração de histórico, validações de valor/descrição) devem ficar isoladas em classes de serviço ou nos próprios Models, nunca nas Views e, preferencialmente, não diretamente nos Controllers, para manter a separação de responsabilidades.
- **Separação entre banco de dados, lógica e interface:** nenhuma View deve executar consultas SQL; nenhum Model deve gerar HTML; nenhum Controller deve conter marcação HTML embutida em strings.
- **Arquivos auxiliares, configurações e assets:** devem ficar em pastas próprias (`config/`, `assets/`), nunca misturados com Models, Views ou Controllers.

### 5.2 Estrutura de diretórios sugerida

```
[Diretório do Projeto - Repositório]/
├── index.php                  # arquivo de entrada da aplicação (único ponto de acesso público às rotas)
├── config/
│   └── config.php             # configurações técnicas (banco de dados, flags de log) — não acessível via navegador
├── app/
│   ├── controllers/           # Controllers do sistema
│   ├── models/                # Models do sistema
│   ├── views/                 # Views organizadas por módulo (auth, conta, lancamentos, categorias, formas_pagamento, painel, historico)
│   └── services/              # Regras de negócio compartilhadas (cálculo de saldo, geração de histórico, etc.)
├── database/
│   └── migrations/            # Scripts de migration versionados — não acessível via navegador
├── logs/
│   └── erros/                 # Log de contingência em arquivo — não acessível via navegador
├── assets/
│   ├── css/
│   ├── js/
│   └── vendor/                 # Bootstrap e demais bibliotecas front-end hospedadas localmente
└── public/assets/ (opcional)   # apenas se a stack de hospedagem exigir separação de assets públicos
```

Pastas internas como `config/`, `app/`, `database/migrations/` e `logs/` **não devem ser acessadas diretamente pelo navegador**. O único ponto de entrada público da aplicação é o `index.php`.

### 5.3 Proteção de pastas internas

A IA codificadora deve proteger as pastas internas contra acesso direto por URL, usando a estratégia adequada à stack e ao servidor:

- Em ambiente Apache (XAMPP e, presumivelmente, Hostnet), deve ser usado um arquivo `.htaccess` em cada pasta interna (`config/`, `database/`, `logs/`, `app/`) negando acesso direto (`Deny from all` ou equivalente em sintaxe moderna do Apache).
- A aplicação **não deve depender apenas do `.htaccess`** como única proteção: sempre que possível, essas pastas devem ficar fora do diretório publicamente servido pelo Apache, ou o próprio código deve validar que arquivos internos não sejam referenciados por links diretos, rotas públicas ou includes indevidos.
- Nenhuma rota pública deve expor caminhos de arquivos internos (por exemplo, nunca montar uma URL que aponte diretamente para um arquivo de `app/models/` ou `database/migrations/`).

### 5.4 Arquivo de configuração

- O projeto **não deve utilizar arquivo `.env`** para armazenar credenciais ou parâmetros sensíveis.
- Deve ser utilizado um arquivo de configuração em código PHP: `config/config.php`.
- Esse arquivo armazenará, quando aplicável: dados de conexão com o banco de dados (host, nome do banco, usuário, senha), flags de ativação de log de erros/segurança, e parâmetros internos da aplicação (por exemplo, tempo de expiração de sessão e de token de recuperação de senha).
- O arquivo deve ser carregado apenas internamente pelo código PHP, via `require`/`include`, nunca exposto por rota pública.
- A pasta `config/` deve estar protegida contra acesso direto por URL, conforme a Seção 5.3.

## 6. Escopo Funcional da Primeira Versão

### Módulo 1 — Autenticação e Conta

**Cadastro de usuário**
- Objetivo: permitir que a pessoa crie uma conta para acessar o sistema.
- Usuários envolvidos: usuário não autenticado (visitante).
- Ações permitidas: informar e-mail e senha, confirmar senha, enviar o formulário de cadastro.
- Resultado esperado: conta criada e usuário direcionado à tela de login, onde deverá autenticar-se normalmente com as credenciais recém-cadastradas. O sistema não realiza login automático após o cadastro.
- Dependências: nenhuma.
- Regras relacionadas: e-mail deve ser único no sistema; senha deve seguir regras mínimas de segurança (ver Seção 14).

**Login**
- Objetivo: autenticar o usuário para acesso ao sistema.
- Usuários envolvidos: usuário cadastrado.
- Ações permitidas: informar e-mail e senha, enviar formulário de login.
- Resultado esperado: sessão criada e usuário redirecionado ao painel financeiro.
- Dependências: cadastro de usuário.
- Regras relacionadas: bloqueio temporário após tentativas inválidas consecutivas (ver Seção 15).

**Recuperação de senha**
- Objetivo: permitir que o usuário recupere o acesso caso esqueça a senha.
- Usuários envolvidos: usuário cadastrado.
- Ações permitidas: solicitar recuperação informando e-mail; receber e-mail com link/token de redefinição; definir nova senha através do link.
- Resultado esperado: senha redefinida e usuário capaz de fazer login novamente.
- Dependências: cadastro de usuário, envio de e-mail (SMTP configurado em `config/config.php`).
- Regras relacionadas: token de redefinição deve ter validade limitada e uso único (ver Seção 15).

**Edição de e-mail e senha (conta/perfil)**
- Objetivo: permitir que o usuário altere seus próprios dados de acesso.
- Usuários envolvidos: usuário autenticado.
- Ações permitidas: alterar e-mail; alterar senha.
- Resultado esperado: dado atualizado e evento registrado no log de segurança.
- Dependências: login.
- Regras relacionadas: qualquer alteração sensível (e-mail ou senha) exige confirmação da senha atual.

**Exclusão da própria conta**
- Objetivo: permitir que o usuário encerre sua conta no sistema.
- Usuários envolvidos: usuário autenticado.
- Ações permitidas: solicitar exclusão da conta, confirmar a ação.
- Resultado esperado: conta marcada como excluída (soft delete); usuário deslogado e impedido de autenticar novamente; dados financeiros permanecem preservados no banco para fins de integridade e histórico.
- Dependências: login.
- Regras relacionadas: exclusão é lógica (soft delete), nunca física.

### Módulo 2 — Categorias e Formas de Pagamento

**Listagem de categorias e formas de pagamento**
- Objetivo: exibir as opções padrão do sistema e as criadas pelo próprio usuário.
- Usuários envolvidos: usuário autenticado.
- Ações permitidas: visualizar lista; criar novo item; excluir item próprio (soft delete).
- Resultado esperado: usuário consegue escolher/gerenciar as categorias e formas de pagamento disponíveis para seus lançamentos.
- Dependências: login.
- Regras relacionadas: categorias e formas de pagamento padrão do sistema não podem ser excluídas pelo usuário; apenas itens criados pelo próprio usuário podem ser excluídos.

**Criação de categoria/forma de pagamento própria**
- Objetivo: permitir personalizar a classificação de lançamentos.
- Usuários envolvidos: usuário autenticado.
- Ações permitidas: informar nome do novo item e confirmar.
- Resultado esperado: novo item disponível para uso em lançamentos, visível apenas para o usuário que o criou.
- Dependências: login.
- Regras relacionadas: nome obrigatório; não permitir duplicidade de nome para o mesmo usuário no mesmo tipo (categoria ou forma de pagamento).

**Exclusão de categoria/forma de pagamento própria**
- Objetivo: remover da lista de opções disponíveis um item que o usuário não deseja mais usar.
- Usuários envolvidos: usuário autenticado.
- Ações permitidas: excluir (soft delete) item próprio.
- Resultado esperado: item deixa de aparecer como opção para novos lançamentos; lançamentos antigos que já usam esse item continuam exibindo a informação normalmente; a exclusão gera um registro no histórico de alterações.
- Dependências: existência de ao menos um item próprio criado.
- Regras relacionadas: itens padrão do sistema não podem ser excluídos.

### Módulo 3 — Lançamentos Financeiros

**Registro de lançamento (receita ou despesa)**
- Objetivo: registrar uma movimentação financeira.
- Usuários envolvidos: usuário autenticado.
- Ações permitidas: informar tipo (receita/despesa), valor, categoria, forma de pagamento, descrição, data prevista, status inicial (pendente ou concluído; se concluído, também a data efetiva).
- Resultado esperado: lançamento criado, saldo do mês recalculado, lançamento visível na listagem e no painel.
- Dependências: login; existência de ao menos uma categoria e uma forma de pagamento disponíveis (padrão ou próprias).
- Regras relacionadas: ver Seção 14 (validações e regras de negócio).

**Edição de lançamento**
- Objetivo: corrigir informações de um lançamento já existente.
- Usuários envolvidos: usuário autenticado, dono do lançamento.
- Ações permitidas: alterar qualquer campo do lançamento (tipo, valor, categoria, forma de pagamento, descrição, datas, status).
- Resultado esperado: lançamento atualizado, saldo recalculado, alteração registrada no histórico.
- Dependências: existência do lançamento.
- Regras relacionadas: edição livre, sem restrição de prazo; toda edição gera registro de histórico.

**Atualização de status (marcar como concluído)**
- Objetivo: registrar que um lançamento pendente efetivamente aconteceu.
- Usuários envolvidos: usuário autenticado, dono do lançamento.
- Ações permitidas: alterar status de "pendente" para "concluído", informando a data efetiva.
- Resultado esperado: status atualizado; saldo realizado e saldo previsto recalculados; alteração registrada no histórico.
- Dependências: lançamento existente com status "pendente".
- Regras relacionadas: data efetiva é obrigatória ao concluir um lançamento.

**Exclusão de lançamento**
- Objetivo: remover um lançamento indevido ou incorreto.
- Usuários envolvidos: usuário autenticado, dono do lançamento.
- Ações permitidas: excluir (soft delete) lançamento.
- Resultado esperado: lançamento removido da listagem ativa e do cálculo de saldo; saldo recalculado imediatamente; histórico de alterações relacionado ao lançamento permanece disponível mesmo após a exclusão.
- Dependências: existência do lançamento.
- Regras relacionadas: exclusão livre, sem restrição de prazo; exclusão é lógica (soft delete).

**Listagem de lançamentos com filtros**
- Objetivo: permitir consulta e busca de lançamentos.
- Usuários envolvidos: usuário autenticado.
- Ações permitidas: filtrar por período, categoria e forma de pagamento; visualizar detalhes de um lançamento.
- Resultado esperado: lista de lançamentos do próprio usuário, respeitando os filtros aplicados.
- Dependências: login.
- Regras relacionadas: apenas lançamentos do próprio usuário autenticado são exibidos.

### Módulo 4 — Painel Financeiro (Dashboard)

**Consulta do painel financeiro**
- Objetivo: apresentar, em uma única tela, o resumo da situação financeira do mês corrente.
- Usuários envolvidos: usuário autenticado.
- Ações permitidas: visualizar indicadores; navegar para outras áreas do sistema (lançamentos, histórico, categorias, formas de pagamento).
- Resultado esperado: exibição de saldo realizado, saldo previsto, total de receitas, total de despesas, gráfico de gastos por categoria, lista dos últimos lançamentos e lista dos próximos pendentes (com destaque para atrasados).
- Dependências: login.
- Regras relacionadas: o mês financeiro segue o calendário civil (dia 1 ao último dia do mês); tela inicial do sistema após login.

### Módulo 5 — Histórico de Alterações

**Consulta de histórico**
- Objetivo: permitir ao usuário rever mudanças feitas em seus lançamentos, categorias e formas de pagamento ao longo do tempo.
- Usuários envolvidos: usuário autenticado.
- Ações permitidas: visualizar lista de alterações; filtrar por período, categoria e forma de pagamento; visualizar detalhes de uma alteração específica (o que mudou e quando).
- Resultado esperado: histórico completo, incluindo alterações relacionadas a lançamentos, categorias e formas de pagamento já excluídos.
- Dependências: login.
- Regras relacionadas: histórico é somente leitura; nunca pode ser editado ou excluído pelo usuário.

## 7. Fora de Escopo

Os itens abaixo foram considerados e **não fazem parte da primeira versão** do FinControle:

- Lançamentos recorrentes automáticos — adiado para manter a primeira versão simples; lançamentos devem ser cadastrados manualmente.
- Metas de economia ou orçamento com limite de gasto por categoria — considerado incremento futuro.
- Notificações ou alertas ativos (e-mail, push, SMS) — apenas destaque visual de atrasados na tela.
- Controle de saldo separado por conta ou carteira — apenas o campo simples de forma de pagamento.
- Compartilhamento de dados entre múltiplos usuários (ex.: casal, família) — sistema definido para uso individual.
- Gráficos adicionais além do gráfico de gastos por categoria do painel.
- Integração bancária automática (open finance, importação de extrato).
- Categorização automática por inteligência artificial.
- Controle de investimentos (ações, Tesouro Direto, criptomoedas).
- Emissão de notas fiscais ou conciliação contábil.
- Simuladores de financiamento, projeção de saldo futuro, cálculo de imposto de renda.
- Suporte a múltiplas moedas — o sistema opera exclusivamente em Real (R$).
- Aplicativo mobile — o sistema é exclusivamente web.
- Perfil de administrador ou qualquer hierarquia de permissões.
- RBAC (controle de acesso baseado em papéis) formal — há apenas isolamento de dados por usuário autenticado.
- Uploads, anexos ou arquivos.
- Exportações (CSV, PDF, Excel) e relatórios avançados.
- APIs internas ou externas e integrações externas de qualquer natureza.

## 8. Perfis de Usuário e Permissões

Existe um único perfil de usuário no sistema.

### Perfil: Usuário

- **Descrição:** pessoa física que utiliza o sistema para controlar suas próprias finanças pessoais.
- **Permissões:**
  - Cadastrar-se e fazer login.
  - Recuperar senha e editar e-mail/senha da própria conta.
  - Registrar, editar, marcar status e excluir (soft delete) seus próprios lançamentos.
  - Criar e excluir (soft delete) suas próprias categorias e formas de pagamento.
  - Consultar o painel financeiro, com todos os indicadores.
  - Consultar o histórico de alterações, com filtros.
  - Excluir (soft delete) a própria conta.
- **Restrições:**
  - Não pode visualizar, alterar ou excluir dados financeiros de outros usuários.
  - Não pode acessar nenhuma área do sistema sem estar autenticado.
  - Não pode excluir categorias ou formas de pagamento padrão do sistema.
- **Áreas acessíveis:** todas as áreas do sistema, restritas aos próprios dados.
- **Ações bloqueadas:** qualquer ação de escrita ou leitura sobre registros pertencentes a outro usuário deve ser recusada pelo backend, independentemente de manipulação da interface.

Não há hierarquia de perfis nem administrador nesta versão. Toda validação de posse (o registro pertence ao usuário autenticado) deve ser feita no backend em toda operação de leitura, edição e exclusão.

## 9. Recursos Estruturais do Sistema

### Autenticação
- Objetivo: garantir que apenas o próprio usuário acesse seus dados.
- Onde é aplicado: em todas as rotas do sistema, exceto cadastro, login e recuperação de senha.
- Comportamento esperado: sessão criada após login válido; toda requisição a áreas protegidas verifica se há sessão ativa.
- Permissões envolvidas: apenas o próprio usuário autenticado acessa seus dados.
- Cuidados de segurança: senha armazenada com hash seguro (nunca em texto puro); proteção contra força bruta (bloqueio por tentativas); expiração de sessão por inatividade.
- Critérios de validação: usuário não autenticado é redirecionado ao login ao tentar acessar qualquer área protegida.

### RBAC
- Não haverá RBAC formal nesta versão. O único controle de acesso existente é o isolamento de dados por usuário autenticado: cada usuário só enxerga e manipula seus próprios registros.

### Auditoria (Histórico de Alterações)
- Objetivo: registrar mudanças relevantes feitas em lançamentos, categorias e formas de pagamento, preservando rastreabilidade mesmo após exclusões.
- Onde é aplicado: criação, edição e exclusão de lançamentos; exclusão de categorias e formas de pagamento.
- Comportamento esperado: cada alteração relevante gera um registro de histórico com valor anterior, valor novo (quando aplicável) e data/hora.
- Permissões envolvidas: apenas o próprio usuário consulta seu histórico.
- Cuidados de segurança: histórico nunca pode ser editado ou apagado pelo usuário.
- Critérios de validação: histórico permanece acessível mesmo após exclusão do lançamento/categoria/forma de pagamento original.

### Soft Delete
- Objetivo: preservar integridade referencial e histórico, evitando remoção física de dados.
- Onde é aplicado: lançamentos financeiros, categorias, formas de pagamento e conta do usuário.
- Comportamento esperado: registro excluído recebe uma marca de exclusão (ex.: campo de data de exclusão) e deixa de aparecer nas listagens ativas e nos cálculos de saldo, mas permanece no banco de dados.
- Permissões envolvidas: apenas o dono do registro pode excluí-lo.
- Cuidados de segurança: todas as consultas de listagem/cálculo devem filtrar explicitamente registros não excluídos.
- Critérios de validação: registros excluídos não aparecem em listagens padrão, mas continuam referenciados corretamente em lançamentos e históricos antigos.

### Log de Erros
- Ver detalhamento completo na Seção 19.

### Log de Segurança
- Ver detalhamento completo na Seção 19.

### Configurações Globais
- Não há configurações globais editáveis pela interface nesta primeira versão. Os únicos parâmetros técnicos existentes ficam no arquivo de configuração em código (`config/config.php`), descritos na Seção 20.

### Uploads e Anexos
- Não fazem parte do escopo desta versão (ver Seção 21).

### Exportações
- Não fazem parte do escopo desta versão (ver Seção 22).

### APIs e Integrações Externas
- Não fazem parte do escopo desta versão (ver Seção 23).

## 10. Entidades do Sistema

### Usuário
- **Finalidade:** representar a pessoa que utiliza o sistema e é dona de todos os dados financeiros vinculados.
- **Principais informações:** e-mail, senha (armazenada com hash), data de criação, contador de tentativas de login falhas, data/hora de bloqueio temporário (quando aplicável).
- **Relacionamentos funcionais:** possui muitos lançamentos, categorias próprias, formas de pagamento próprias, registros de histórico e tokens de recuperação de senha.
- **Regras de criação/edição/exclusão/visualização:** criado no cadastro; e-mail e senha editáveis pelo próprio usuário mediante confirmação da senha atual; exclusão é sempre soft delete; usuário só visualiza os próprios dados.
- **Soft delete:** sim.
- **Auditoria:** alterações sensíveis de conta (e-mail, senha) e exclusão/restauração de conta são registradas no log de segurança (não no histórico de alterações financeiro).
- **Permissões de acesso:** o próprio usuário, apenas para seus dados.

### Lançamento Financeiro
- **Finalidade:** registrar uma movimentação financeira (receita ou despesa).
- **Principais informações:** tipo (receita/despesa), valor, categoria, forma de pagamento, descrição, data prevista, data efetiva, status (pendente/concluído).
- **Relacionamentos funcionais:** pertence a um usuário; relaciona-se a uma categoria e a uma forma de pagamento; gera registros de histórico.
- **Regras de criação/edição/exclusão/visualização:** criação e edição livres, sem restrição de prazo; exclusão é soft delete; apenas o dono visualiza/edita/exclui.
- **Soft delete:** sim.
- **Auditoria:** sim — toda criação, edição e exclusão gera registro no histórico de alterações.
- **Permissões de acesso:** apenas o usuário dono do lançamento.
- **Observações:** valor deve ser sempre positivo e maior que zero; descrição limitada a 300 caracteres.

### Categoria
- **Finalidade:** classificar lançamentos por tipo de gasto ou receita.
- **Principais informações:** nome, indicação se é padrão do sistema ou criada pelo usuário.
- **Relacionamentos funcionais:** pode pertencer a um usuário (categoria própria) ou ser padrão do sistema (sem usuário associado); relaciona-se a muitos lançamentos.
- **Regras de criação/edição/exclusão/visualização:** usuário pode criar categorias próprias; apenas categorias próprias podem ser excluídas (soft delete) pelo usuário; categorias padrão não podem ser excluídas por usuários.
- **Soft delete:** sim, para categorias próprias do usuário.
- **Auditoria:** sim — exclusão de categoria própria gera registro no histórico de alterações.
- **Permissões de acesso:** categorias padrão são visíveis a todos os usuários; categorias próprias são visíveis apenas ao usuário que as criou.

### Forma de Pagamento
- **Finalidade:** indicar como o pagamento ou recebimento foi feito.
- **Principais informações:** nome, indicação se é padrão do sistema ou criada pelo usuário.
- **Relacionamentos funcionais:** análogo à entidade Categoria.
- **Regras de criação/edição/exclusão/visualização:** idênticas às da entidade Categoria.
- **Soft delete:** sim, para itens próprios do usuário.
- **Auditoria:** sim — exclusão de item próprio gera registro no histórico de alterações.
- **Permissões de acesso:** itens padrão visíveis a todos; itens próprios visíveis apenas ao criador.

### Histórico de Alterações
- **Finalidade:** registrar de forma permanente as mudanças ocorridas em lançamentos, categorias e formas de pagamento.
- **Principais informações:** tipo de entidade alterada, identificador da entidade, tipo de ação (criação/edição/exclusão), campo alterado, valor anterior, valor novo, data/hora da alteração, usuário responsável.
- **Relacionamentos funcionais:** relaciona-se a um usuário, e referencia (sem depender fisicamente) um lançamento, categoria ou forma de pagamento, mesmo que estes tenham sido excluídos.
- **Regras de criação/edição/exclusão/visualização:** criado automaticamente pelo sistema a cada alteração relevante; nunca editável ou excluível pelo usuário; visível apenas ao usuário dono dos dados.
- **Soft delete:** não aplicável (histórico nunca é excluído).
- **Auditoria:** o próprio histórico é o mecanismo de auditoria do sistema.
- **Permissões de acesso:** apenas o usuário dono dos dados relacionados.

### Token de Recuperação de Senha
- **Finalidade:** viabilizar a redefinição segura de senha via e-mail.
- **Principais informações:** valor do token (armazenado com hash), data de expiração, indicação de uso.
- **Relacionamentos funcionais:** pertence a um usuário.
- **Regras de criação/edição/exclusão/visualização:** criado ao solicitar recuperação de senha; expira após período limitado (ver Seção 15); marcado como utilizado após uso; não editável pelo usuário.
- **Soft delete:** não aplicável (controle por expiração e flag de uso).
- **Auditoria:** solicitação de recuperação e redefinição efetiva de senha são registradas no log de segurança.
- **Permissões de acesso:** uso restrito ao fluxo de recuperação de senha; não exposto em nenhuma tela de consulta.

## 11. Modelo de Dados Proposto

O banco de dados deverá ser criado em MySQL, coerente com as entidades acima. A estrutura sugerida é a seguinte (nomes de tabelas e campos ilustrativos; tipos de dados sugeridos):

### `usuarios`
| Campo | Tipo sugerido | Observações |
| --- | --- | --- |
| id | INT, PK, AUTO_INCREMENT | |
| email | VARCHAR(190), UNIQUE | índice único |
| senha_hash | VARCHAR(255) | hash seguro (ex.: bcrypt/argon2 via função nativa do PHP) |
| tentativas_login_falhas | TINYINT UNSIGNED, default 0 | usado no bloqueio por tentativas |
| bloqueado_ate | DATETIME, NULL | data/hora até quando o login está bloqueado |
| criado_em | DATETIME | auditoria |
| atualizado_em | DATETIME | auditoria |
| excluido_em | DATETIME, NULL | soft delete |

### `categorias`
| Campo | Tipo sugerido | Observações |
| --- | --- | --- |
| id | INT, PK, AUTO_INCREMENT | |
| usuario_id | INT, FK → usuarios.id, NULL | NULL indica categoria padrão do sistema |
| nome | VARCHAR(100) | |
| padrao | TINYINT(1), default 0 | indica item padrão não excluível |
| criado_em | DATETIME | |
| atualizado_em | DATETIME | |
| excluido_em | DATETIME, NULL | soft delete (apenas itens próprios) |

Índice: (`usuario_id`, `nome`) para evitar duplicidade lógica por usuário.

### `formas_pagamento`
Estrutura idêntica à tabela `categorias`, com os mesmos campos e regras.

### `lancamentos`
| Campo | Tipo sugerido | Observações |
| --- | --- | --- |
| id | INT, PK, AUTO_INCREMENT | |
| usuario_id | INT, FK → usuarios.id, NOT NULL | |
| tipo | ENUM('receita','despesa'), NOT NULL | |
| valor | DECIMAL(12,2), NOT NULL | constraint de aplicação: valor > 0 |
| categoria_id | INT, FK → categorias.id, NOT NULL | |
| forma_pagamento_id | INT, FK → formas_pagamento.id, NOT NULL | |
| descricao | VARCHAR(300), NULL | |
| data_prevista | DATE, NOT NULL | |
| data_efetiva | DATE, NULL | obrigatória quando status = concluído |
| status | ENUM('pendente','concluido'), NOT NULL | |
| criado_em | DATETIME | |
| atualizado_em | DATETIME | |
| excluido_em | DATETIME, NULL | soft delete |

Índices sugeridos: (`usuario_id`, `status`, `data_prevista`) para consultas de painel e atrasados; (`usuario_id`, `data_efetiva`) para cálculo de saldo realizado por mês; (`usuario_id`, `categoria_id`) para o gráfico de gastos por categoria; (`usuario_id`, `forma_pagamento_id`) para filtros por forma de pagamento; (`excluido_em`) para acelerar filtragem de registros ativos.

### `historico_alteracoes`
| Campo | Tipo sugerido | Observações |
| --- | --- | --- |
| id | INT, PK, AUTO_INCREMENT | |
| usuario_id | INT, FK → usuarios.id, NOT NULL | |
| entidade_tipo | ENUM('lancamento','categoria','forma_pagamento'), NOT NULL | |
| entidade_id | INT, NOT NULL | referência lógica (sem FK obrigatória, pois a entidade pode ter sido excluída) |
| acao | ENUM('criacao','edicao','exclusao'), NOT NULL | |
| campo_alterado | VARCHAR(100), NULL | preenchido em edições |
| valor_anterior | TEXT, NULL | |
| valor_novo | TEXT, NULL | |
| data_alteracao | DATETIME, NOT NULL | |

Índices sugeridos: (`usuario_id`, `data_alteracao`), (`usuario_id`, `entidade_tipo`, `entidade_id`) para consulta rápida do histórico de um item específico e para os filtros da tela de histórico.

### `tokens_recuperacao_senha`
| Campo | Tipo sugerido | Observações |
| --- | --- | --- |
| id | INT, PK, AUTO_INCREMENT | |
| usuario_id | INT, FK → usuarios.id, NOT NULL | |
| token_hash | VARCHAR(255), NOT NULL | token armazenado com hash, nunca em texto puro |
| expira_em | DATETIME, NOT NULL | |
| utilizado | TINYINT(1), default 0 | |
| criado_em | DATETIME | |

Índice: (`usuario_id`, `utilizado`, `expira_em`).

### `logs_erros` (quando gravado em banco)
| Campo | Tipo sugerido | Observações |
| --- | --- | --- |
| id | INT, PK, AUTO_INCREMENT | |
| data_hora | DATETIME, NOT NULL | |
| tipo_erro | VARCHAR(100) | |
| mensagem_tecnica | TEXT | |
| contexto | VARCHAR(255), NULL | rota/ação onde ocorreu |
| usuario_id | INT, NULL | quando aplicável |
| ip | VARCHAR(45), NULL | |

### `logs_seguranca`
| Campo | Tipo sugerido | Observações |
| --- | --- | --- |
| id | INT, PK, AUTO_INCREMENT | |
| data_hora | DATETIME, NOT NULL | |
| tipo_evento | VARCHAR(100), NOT NULL | ex.: login_invalido, acesso_negado, bloqueio_tentativas, alteracao_dados_sensiveis, exclusao_registro, restauracao_registro |
| usuario_id | INT, NULL | quando identificável |
| email_tentativa | VARCHAR(190), NULL | para tentativas de login com e-mail não autenticado |
| ip | VARCHAR(45), NULL | |
| detalhes | TEXT, NULL | |

Índice: (`usuario_id`, `data_hora`), (`tipo_evento`, `data_hora`).

### `migrations` (tabela de controle)
| Campo | Tipo sugerido | Observações |
| --- | --- | --- |
| id | INT, PK, AUTO_INCREMENT | |
| nome_arquivo | VARCHAR(255), UNIQUE | nome do script de migration já executado |
| executada_em | DATETIME | |

### Observações sobre integridade dos dados

- Todas as chaves estrangeiras devem garantir integridade referencial (`categoria_id`, `forma_pagamento_id`, `usuario_id`) nas tabelas que dependem de outra entidade ativa.
- A exclusão lógica (soft delete) deve ser respeitada em toda consulta de listagem e de cálculo de saldo: registros com `excluido_em` preenchido não entram em somatórios nem aparecem em listas padrão.
- O campo `valor` de `lancamentos` deve ser validado tanto no backend quanto, preferencialmente, com constraint de aplicação (validação em código), garantindo que seja sempre maior que zero.
- Campos de auditoria (`criado_em`, `atualizado_em`) devem existir em todas as tabelas de entidades principais (`usuarios`, `categorias`, `formas_pagamento`, `lancamentos`).

### Estratégia de Migrations

O projeto deverá utilizar uma arquitetura de migrations para criação e atualização da estrutura do banco de dados, evitando que o usuário precise criar tabelas, campos e índices manualmente em um gerenciador como o phpMyAdmin.

- As migrations devem contemplar, quando aplicável: criação das tabelas listadas acima, criação dos campos, definição de chaves primárias e estrangeiras, criação de índices, criação de constraints, campos de auditoria e campos de soft delete.
- As migrations devem incluir, como dado inicial obrigatório, a inserção das categorias e formas de pagamento padrão do sistema (com `usuario_id` nulo e `padrao = 1`).
- As migrations devem ficar em uma pasta interna do projeto: `database/migrations/`.
- As migrations **não devem ser acessíveis diretamente pelo navegador**; a pasta `database/` deve ser protegida contra acesso direto por URL, conforme a Seção 5.3.
- O projeto deve prever um mecanismo de controle para evitar execução duplicada de migrations, por meio da tabela `migrations` descrita acima: cada script de migration, ao ser executado, registra seu nome nessa tabela; antes de executar uma migration, o sistema verifica se ela já foi registrada.
- A execução das migrations deve ocorrer apenas por um meio controlado, como um script de linha de comando (executado localmente via PHP CLI, por exemplo `php database/migrations/run.php`) ou uma rotina interna protegida. **Não deve haver rota pública aberta no navegador para execução de migrations.** Caso, no futuro, seja necessária uma tela para esse fim, ela deverá ser protegida por autenticação e bloqueios de segurança adicionais — mas a abordagem preferencial nesta versão é a execução via linha de comando/script controlado, fora de qualquer rota HTTP pública.

## 12. Módulos e Telas

Todas as telas devem seguir a identidade visual, paleta de cores, tipografia, espaçamentos e componentes definidos em `docs/DESIGN.md`. Este FSD não repete detalhamento visual já coberto por aquele documento; descreve apenas o conteúdo funcional de cada tela.

### Tela: Cadastro de Usuário
- **Objetivo:** permitir criação de nova conta.
- **Usuários que acessam:** visitante (não autenticado).
- **Principais ações:** preencher e-mail, senha e confirmação de senha; enviar cadastro.
- **Principais campos:** e-mail, senha, confirmação de senha.
- **Botões e ações:** "Criar conta" (botão primário); link para tela de login.
- **Mensagens esperadas:** erro de e-mail já cadastrado; erro de senha fraca ou divergente na confirmação; sucesso ao criar conta.
- **Estados importantes:** erro de validação, carregando (envio do formulário), sucesso.

### Tela: Login
- **Objetivo:** autenticar o usuário.
- **Usuários que acessam:** visitante.
- **Principais ações:** informar e-mail e senha; acessar recuperação de senha.
- **Principais campos:** e-mail, senha.
- **Botões e ações:** "Entrar" (botão primário); link "Esqueci minha senha"; link para cadastro.
- **Mensagens esperadas:** credenciais inválidas; conta temporariamente bloqueada por tentativas; sucesso (redirecionamento ao painel).
- **Estados importantes:** erro de autenticação, bloqueado por tentativas, carregando, sucesso.

### Tela: Recuperação de Senha (solicitação)
- **Objetivo:** solicitar redefinição de senha.
- **Usuários que acessam:** visitante.
- **Principais ações:** informar e-mail cadastrado.
- **Principais campos:** e-mail.
- **Botões e ações:** "Enviar link de recuperação".
- **Mensagens esperadas:** confirmação genérica de envio (sem revelar se o e-mail existe ou não, por segurança).
- **Estados importantes:** carregando, sucesso.

### Tela: Redefinição de Senha
- **Objetivo:** permitir definir nova senha a partir do link recebido por e-mail.
- **Usuários que acessam:** visitante portando token válido.
- **Principais ações:** informar nova senha e confirmação.
- **Principais campos:** nova senha, confirmação de nova senha.
- **Botões e ações:** "Redefinir senha".
- **Mensagens esperadas:** token inválido ou expirado; senha redefinida com sucesso.
- **Estados importantes:** erro (token inválido/expirado), sucesso.

### Tela: Conta / Perfil
- **Objetivo:** permitir editar e-mail/senha e excluir a própria conta.
- **Usuários que acessam:** usuário autenticado.
- **Principais ações:** alterar e-mail; alterar senha; excluir conta.
- **Principais campos:** e-mail atual, novo e-mail, senha atual (obrigatória para qualquer alteração sensível), nova senha, confirmação de nova senha.
- **Botões e ações:** "Salvar alterações" (primário); "Excluir minha conta" (botão de perigo, com confirmação em modal).
- **Mensagens esperadas:** senha atual incorreta; e-mail já em uso; alteração salva com sucesso; confirmação antes de excluir conta.
- **Estados importantes:** erro de validação, sucesso, confirmação de exclusão.

### Tela: Painel Financeiro (Dashboard) — tela inicial após login
- **Objetivo:** apresentar resumo financeiro do mês corrente.
- **Usuários que acessam:** usuário autenticado.
- **Principais ações:** navegar para lançamentos, histórico, categorias, formas de pagamento.
- **Principais campos/informações exibidas:** saldo realizado, saldo previsto, total de receitas do mês, total de despesas do mês, gráfico de gastos por categoria (regras de cálculo de cada indicador definidas na Seção 14, "Cálculo dos Indicadores do Painel Financeiro"), lista dos últimos lançamentos ordenados por data de criação decrescente (ver quantidade definida na Seção 27), lista dos próximos pendentes com destaque visual para atrasados.
- **Filtros e buscas:** não há filtros no painel; ele sempre reflete o mês corrente.
- **Botões e ações:** atalho para novo lançamento; links para as listas completas.
- **Mensagens esperadas:** estado vazio quando não há lançamentos no mês.
- **Estados importantes:** vazio (sem lançamentos no mês), carregando, sucesso.
- **Relação com DESIGN.md:** deve seguir os componentes de card, gráfico e badges de status definidos no documento de design.

### Tela: Lista de Lançamentos
- **Objetivo:** listar e gerenciar lançamentos financeiros.
- **Usuários que acessam:** usuário autenticado.
- **Principais ações:** criar novo lançamento; editar; marcar como concluído; excluir; visualizar detalhes.
- **Principais campos/informações exibidas:** tipo, valor, categoria, forma de pagamento, descrição, data prevista, data efetiva, status (com destaque de atrasado quando aplicável).
- **Filtros e buscas:** filtro por período, categoria e forma de pagamento.
- **Paginação:** 20 lançamentos por página, ordenados por data de criação decrescente.
- **Botões e ações:** "Novo lançamento" (primário); ações de editar/excluir/marcar concluído por linha.
- **Mensagens esperadas:** confirmação antes de excluir; sucesso ao salvar; erro de validação.
- **Estados importantes:** vazio (nenhum lançamento no filtro aplicado), erro, sucesso, sem permissão (ao tentar acessar lançamento de outro usuário — deve resultar em acesso negado).

### Tela: Formulário de Lançamento (criação/edição)
- **Objetivo:** criar ou editar um lançamento.
- **Usuários que acessam:** usuário autenticado.
- **Principais ações:** preencher/alterar tipo, valor, categoria, forma de pagamento, descrição, data prevista, status, data efetiva (quando concluído).
- **Principais campos:** tipo (receita/despesa), valor, categoria, forma de pagamento, descrição (até 300 caracteres), data prevista, status, data efetiva (condicional).
- **Botões e ações:** "Salvar" (primário); "Cancelar".
- **Mensagens esperadas:** erros de validação por campo (valor inválido, descrição muito longa, campos obrigatórios não preenchidos).
- **Estados importantes:** erro de validação, carregando, sucesso.

### Tela: Categorias
- **Objetivo:** listar e gerenciar categorias.
- **Usuários que acessam:** usuário autenticado.
- **Principais ações:** visualizar categorias padrão e próprias; criar nova categoria; excluir categoria própria.
- **Principais campos/informações exibidas:** nome, indicação de padrão do sistema ou criada pelo usuário.
- **Botões e ações:** "Nova categoria" (primário); ação de excluir (disponível apenas para categorias próprias).
- **Mensagens esperadas:** erro de nome duplicado; confirmação antes de excluir; aviso de que categorias padrão não podem ser excluídas.
- **Estados importantes:** vazio (nenhuma categoria própria criada ainda), sucesso, erro.

### Tela: Formas de Pagamento
- Idêntica em estrutura à tela de Categorias, aplicada à entidade Forma de Pagamento.

### Tela: Histórico de Alterações
- **Objetivo:** consultar mudanças realizadas em lançamentos, categorias e formas de pagamento.
- **Usuários que acessam:** usuário autenticado.
- **Principais ações:** visualizar lista de alterações; aplicar filtros; visualizar detalhes de uma alteração.
- **Principais campos/informações exibidas:** tipo de entidade, ação (criação/edição/exclusão), campo alterado, valor anterior, valor novo, data/hora.
- **Filtros e buscas:** filtro por período, categoria e forma de pagamento.
- **Paginação:** 20 registros por página, ordenados por data/hora da alteração decrescente.
- **Botões e ações:** nenhuma ação de escrita — tela somente leitura.
- **Mensagens esperadas:** estado vazio quando não há alterações no filtro aplicado.
- **Estados importantes:** vazio, carregando, sucesso.

## 13. Fluxos Funcionais

### Fluxo: Registrar uma receita ou despesa
- **Perfil que executa:** usuário autenticado.
- **Pré-condições:** usuário logado; ao menos uma categoria e uma forma de pagamento disponíveis.
- **Passo a passo:**
  1. Usuário acessa a área de lançamentos e escolhe "Novo lançamento".
  2. Informa tipo, valor, categoria, forma de pagamento, descrição, data prevista e status inicial.
  3. Confirma o registro.
  4. Sistema valida as informações (valor > 0, descrição ≤ 300 caracteres, campos obrigatórios preenchidos).
  5. Sistema grava o lançamento e gera registro de histórico do tipo "criação".
  6. Sistema recalcula o saldo do mês.
  7. Sistema exibe o lançamento na listagem e no painel.
- **Resultado esperado:** lançamento criado e refletido no saldo.
- **Erros possíveis:** valor inválido, descrição muito longa, campos obrigatórios ausentes.
- **Regras de permissão:** lançamento sempre vinculado ao usuário autenticado.
- **Logs/auditoria gerados:** registro de histórico do tipo "criação".

### Fluxo: Marcar um lançamento pendente como concluído
- **Perfil que executa:** usuário autenticado, dono do lançamento.
- **Pré-condições:** lançamento existente com status "pendente".
- **Passo a passo:**
  1. Usuário acessa o lançamento pendente.
  2. Escolhe "Marcar como concluído".
  3. Informa a data efetiva.
  4. Confirma a ação.
  5. Sistema valida a data efetiva.
  6. Sistema atualiza o status para "concluído" e recalcula saldo realizado e previsto.
  7. Sistema gera registro de histórico do tipo "edição" (campo alterado: status; e campo alterado: data efetiva).
- **Resultado esperado:** status atualizado e saldos recalculados.
- **Erros possíveis:** data efetiva ausente ou inválida.
- **Regras de permissão:** apenas o dono do lançamento pode alterar o status.
- **Logs/auditoria gerados:** histórico de edição.

### Fluxo: Editar um lançamento existente
- **Perfil que executa:** usuário autenticado, dono do lançamento.
- **Pré-condições:** lançamento existente e não excluído.
- **Passo a passo:**
  1. Usuário acessa o lançamento a alterar.
  2. Modifica os campos desejados.
  3. Confirma a alteração.
  4. Sistema valida as novas informações.
  5. Sistema atualiza o lançamento, recalcula o saldo e registra cada campo alterado no histórico.
  6. Sistema exibe o lançamento atualizado.
- **Resultado esperado:** lançamento atualizado e histórico gerado por campo alterado.
- **Erros possíveis:** mesmos da criação, aplicados aos novos valores.
- **Regras de permissão:** apenas o dono do lançamento pode editar.
- **Logs/auditoria gerados:** um registro de histórico do tipo "edição" para cada campo alterado (ex.: se valor e categoria mudarem na mesma ação, são gerados dois registros de histórico, um por campo, cada um com seu próprio valor anterior e valor novo). Essa é a única forma aceita de registro, coerente com a estrutura da tabela `historico_alteracoes` (campo `campo_alterado` singular) definida na Seção 11 e com a Seção 17.

### Fluxo: Excluir um lançamento
- **Perfil que executa:** usuário autenticado, dono do lançamento.
- **Pré-condições:** lançamento existente e não excluído.
- **Passo a passo:**
  1. Usuário acessa o lançamento a excluir.
  2. Escolhe "Excluir".
  3. Sistema solicita confirmação.
  4. Usuário confirma.
  5. Sistema marca o lançamento como excluído (soft delete).
  6. Sistema recalcula o saldo do mês.
  7. Sistema gera registro de histórico do tipo "exclusão".
  8. Sistema exibe a listagem atualizada, sem o item excluído.
- **Resultado esperado:** lançamento removido da listagem ativa; saldo atualizado; histórico preservado.
- **Erros possíveis:** tentativa de excluir lançamento já excluído ou de outro usuário (deve ser bloqueada).
- **Regras de permissão:** apenas o dono do lançamento pode excluir.
- **Logs/auditoria gerados:** histórico de exclusão.

### Fluxo: Consultar o painel financeiro
- **Perfil que executa:** usuário autenticado.
- **Pré-condições:** login realizado.
- **Passo a passo:**
  1. Usuário faz login.
  2. Sistema direciona ao painel financeiro (tela inicial).
  3. Sistema calcula e exibe saldo realizado, saldo previsto, total de receitas, total de despesas, gráfico de gastos por categoria, últimos lançamentos e próximos pendentes (destacando atrasados) do mês corrente.
  4. Usuário navega para outras áreas a partir do painel.
- **Resultado esperado:** visão consolidada do mês corrente.
- **Erros possíveis:** nenhum lançamento no mês (estado vazio, não é erro).
- **Regras de permissão:** apenas dados do próprio usuário.
- **Logs/auditoria gerados:** nenhum (consulta não gera histórico).

### Fluxo: Consultar o histórico de alterações
- **Perfil que executa:** usuário autenticado.
- **Pré-condições:** login realizado.
- **Passo a passo:**
  1. Usuário acessa a área de histórico.
  2. Sistema exibe a lista de alterações do usuário, incluindo alterações de itens já excluídos.
  3. Usuário pode aplicar filtros por período, categoria e forma de pagamento.
  4. Usuário visualiza os detalhes de uma alteração específica.
- **Resultado esperado:** consulta completa e correta do histórico.
- **Erros possíveis:** nenhum (tela somente leitura).
- **Regras de permissão:** apenas histórico do próprio usuário.
- **Logs/auditoria gerados:** nenhum.

### Fluxo: Login com bloqueio por tentativas
- **Perfil que executa:** visitante.
- **Pré-condições:** conta existente.
- **Passo a passo:**
  1. Usuário informa e-mail e senha incorretos.
  2. Sistema incrementa o contador de tentativas falhas da conta correspondente e registra evento no log de segurança.
  3. Ao atingir o limite de tentativas definido (ver Seção 15), o sistema bloqueia temporariamente novas tentativas de login para aquela conta, registrando o evento de bloqueio no log de segurança.
  4. Após o período de bloqueio expirar, o contador é reiniciado e novas tentativas são permitidas.
- **Resultado esperado:** proteção contra tentativas de força bruta.
- **Erros possíveis:** usuário legítimo temporariamente impedido de logar durante o bloqueio (mensagem clara deve informar o motivo, sem detalhar tempo restante exato, se assim decidido pela implementação).
- **Regras de permissão:** não aplicável (fluxo pré-autenticação).
- **Logs/auditoria gerados:** eventos de login inválido e de bloqueio por tentativas no log de segurança.

## 14. Validações e Regras de Negócio

### Lançamentos
- Valor obrigatório, numérico, sempre maior que zero (não permite zero ou negativo).
- Descrição opcional, texto livre, máximo de 300 caracteres.
- Tipo obrigatório: "receita" ou "despesa".
- Categoria obrigatória, deve pertencer ao usuário autenticado ou ser categoria padrão do sistema.
- Forma de pagamento obrigatória, deve pertencer ao usuário autenticado ou ser padrão do sistema.
- Data prevista obrigatória.
- Status obrigatório: "pendente" ou "concluído".
- Data efetiva obrigatória quando o status for "concluído"; deve permanecer vazia/nula enquanto o status for "pendente".
- Um lançamento com status "pendente" e data prevista anterior à data atual deve ser exibido com destaque visual de "atrasado" (regra de apresentação, não altera o status armazenado).
- Edição e exclusão livres, sem restrição de prazo.
- Toda edição ou exclusão recalcula imediatamente o saldo do mês afetado.

### Categorias e Formas de Pagamento
- Nome obrigatório.
- Não permitir duas categorias (ou duas formas de pagamento) com o mesmo nome para o mesmo usuário.
- Não permitir que o usuário crie uma categoria ou forma de pagamento própria com o mesmo nome (case-insensitive) de um item padrão do sistema já existente, evitando ambiguidade na tela de seleção.
- Categorias e formas de pagamento padrão do sistema não podem ser criadas, editadas ou excluídas por usuários.
- Exclusão de item próprio é sempre soft delete; lançamentos antigos que já utilizam o item mantêm a referência normalmente.

### Cálculo de Saldo
- O mês financeiro segue o calendário civil (do dia 1 ao último dia do mês).
- Fuso horário de referência: `America/Sao_Paulo`, fixado tanto no PHP (`date_default_timezone_set`) quanto na conexão com o MySQL, garantindo que a virada de dia/mês seja consistente entre aplicação e banco de dados.
- **Saldo realizado:** soma de receitas concluídas menos soma de despesas concluídas, considerando a data efetiva dentro do mês corrente.
- **Saldo previsto:** soma de receitas (concluídas + pendentes) menos soma de despesas (concluídas + pendentes), considerando a data prevista (para pendentes) ou data efetiva (para concluídos) dentro do mês corrente.
- O sistema opera exclusivamente em Real (R$).

### Cálculo dos Indicadores do Painel Financeiro
Para eliminar ambiguidade na implementação, os indicadores exibidos no painel (Seção 12) devem seguir exatamente as regras abaixo, todas restritas ao mês corrente (calendário civil) e ao usuário autenticado:
- **Total de receitas do mês:** soma de todos os lançamentos do tipo "receita" com status "concluído" (data efetiva dentro do mês corrente) mais os lançamentos do tipo "receita" com status "pendente" (data prevista dentro do mês corrente). O total é exibido de forma única, somando concluídos e pendentes — a mesma composição usada no saldo previsto.
- **Total de despesas do mês:** mesma lógica do total de receitas, aplicada aos lançamentos do tipo "despesa".
- **Gráfico de gastos por categoria:** considera exclusivamente lançamentos do tipo "despesa" (receitas não entram nesse gráfico), agrupados por categoria, somando despesas concluídas e pendentes cuja data efetiva (concluídas) ou data prevista (pendentes) esteja dentro do mês corrente — mesma composição usada no saldo previsto e no total de despesas.
- Essas regras usam a mesma base de composição do saldo previsto para manter os indicadores do painel coerentes entre si.
- **Biblioteca do gráfico:** o gráfico de gastos por categoria deve ser implementado com Chart.js, baixado e hospedado localmente em `assets/vendor/` (sem uso de CDN), conforme restrição técnica da Seção 3.

### Conta do usuário
- E-mail obrigatório, único no sistema, formato de e-mail válido.
- Senha obrigatória, com regras mínimas de segurança: tamanho mínimo definido como padrão provisório em `config/config.php` (ver Seção 27).
- Qualquer alteração de e-mail ou senha exige confirmação da senha atual.
- Exclusão da conta exige confirmação explícita do usuário e é sempre soft delete.

### Mensagens de erro esperadas
- Campos obrigatórios não preenchidos: mensagem específica por campo.
- Valor de lançamento inválido (zero, negativo ou não numérico): mensagem indicando que o valor deve ser maior que zero.
- Descrição acima de 300 caracteres: mensagem indicando o limite.
- E-mail já cadastrado: mensagem informando que o e-mail já está em uso.
- Credenciais inválidas no login: mensagem genérica, sem indicar se o e-mail existe ou se a senha está incorreta (para não revelar quais e-mails estão cadastrados).
- Tentativa de acessar registro de outro usuário: mensagem de acesso negado (ver Seção 16).

### Comportamentos em situações especiais
- Se o usuário tentar excluir uma categoria ou forma de pagamento padrão do sistema, a ação deve ser bloqueada com mensagem explicativa.
- Se o usuário tentar concluir um lançamento sem informar data efetiva, a ação deve ser bloqueada até que a data seja informada.

## 15. Autenticação e Sessão

- **Tipo de autenticação:** e-mail e senha, com sessão de servidor (sessão PHP nativa) após login bem-sucedido.
- **Fluxo de login:** usuário informa e-mail e senha; sistema valida credenciais contra o hash armazenado; em caso de sucesso, cria sessão autenticada e redireciona ao painel financeiro; em caso de falha, incrementa contador de tentativas e registra evento no log de segurança.
- **Fluxo de logout:** usuário aciona "Sair"; sistema encerra a sessão ativa e redireciona à tela de login.
- **Recuperação de acesso:** usuário solicita recuperação informando e-mail; sistema gera um token de redefinição com validade limitada (padrão provisório: 1 hora — ver Seção 27) e envia link por e-mail (SMTP configurado em `config/config.php`); usuário define nova senha através do link; token é marcado como utilizado após a redefinição e não pode ser reutilizado.
- **Envio de e-mail em ambiente de desenvolvimento:** no ambiente local (XAMPP), o envio do e-mail de recuperação de senha deve utilizar um servidor SMTP de teste local (ex.: Mailpit ou Mailtrap local), configurado em `config/config.php`. Em produção (Hostnet), o mesmo arquivo de configuração deve apontar para um SMTP real. A troca entre os dois ambientes deve ocorrer apenas pela alteração dos parâmetros de `config/config.php`, sem exigir mudança de código.
- **Bloqueio por tentativas:** após um número definido de tentativas de login inválidas consecutivas para a mesma conta (padrão provisório: 5 tentativas — ver Seção 27), o sistema bloqueia novas tentativas de login para aquela conta por um período definido (padrão provisório: 15 minutos), registrando o evento no log de segurança. Após o período, o contador é reiniciado.
- **Tempo de sessão:** a sessão deve expirar após um período de inatividade definido em `config/config.php` (padrão provisório: 30 minutos — ver Seção 27), exigindo novo login.
- **Proteção de rotas:** toda rota que não seja cadastro, login, recuperação/redefinição de senha deve validar a existência de sessão ativa antes de processar a requisição; caso não haja sessão válida, o usuário é redirecionado à tela de login.
- **Comportamento para usuário sem permissão:** ao tentar acessar um recurso de outro usuário (mesmo autenticado), o sistema deve negar o acesso e registrar o evento no log de segurança (ver Seção 16).

## 16. Controle de Acesso

Não há papéis nem hierarquia de perfis nesta versão — existe apenas um perfil ("Usuário"). O controle de acesso do sistema se resume a:

- **Autenticação obrigatória:** todas as telas, exceto cadastro, login, recuperação e redefinição de senha, exigem sessão ativa.
- **Isolamento de dados por usuário:** toda consulta, edição ou exclusão de lançamento, categoria, forma de pagamento ou registro de histórico deve validar no backend que o registro pertence ao usuário autenticado (por meio do campo `usuario_id`), independentemente do que for enviado pela interface.
- **Menus:** todos os usuários autenticados veem o mesmo conjunto de menus (Painel, Lançamentos, Categorias, Formas de Pagamento, Histórico, Conta).
- **Telas bloqueadas:** todas as telas internas são bloqueadas a usuários não autenticados.
- **Ações protegidas:** criação, edição e exclusão de lançamentos, categorias e formas de pagamento; edição de conta; exclusão de conta.
- **Validação no backend:** obrigatória em toda rota que manipule dados — a interface não deve ser a única barreira de proteção.
- **Mensagens para acesso negado:** mensagem genérica informando que o recurso não foi encontrado ou que o acesso não é permitido, sem revelar detalhes sobre a existência do registro de outro usuário.

## 17. Auditoria e Histórico

- **Registros auditados:** criação, edição e exclusão de lançamentos financeiros; exclusão de categorias e formas de pagamento próprias do usuário.
- **Ações registradas:** criação, edição (por campo alterado) e exclusão.
- **Campos mínimos usados em cada registro de histórico:** tipo de entidade, identificador da entidade, ação, campo alterado (quando aplicável), valor anterior, valor novo, data/hora da alteração.
- **Quem pode visualizar:** apenas o próprio usuário dono dos dados relacionados, na tela de Histórico de Alterações (Seção 12).
- **Como aparece nos CRUDs:** a tela geral de Histórico de Alterações (Seção 12), com filtros por período, categoria e forma de pagamento, é a forma obrigatória de consulta ao histórico. Um atalho adicional na tela de lançamento para visualizar o histórico específico daquele item é opcional e não obrigatório nesta versão; se implementado, não substitui a tela geral de histórico e não faz parte dos critérios de aceitação (Seção 26).
- **Regras de retenção:** o histórico não possui prazo de expiração ou remoção automática nesta versão; permanece disponível indefinidamente.

## 18. Soft Delete e Exclusões

- **Entidades que usam soft delete:** lançamentos financeiros, categorias (próprias do usuário), formas de pagamento (próprias do usuário), conta do usuário.
- **Quem pode excluir:** apenas o dono do registro.
- **Quem pode restaurar:** esta versão não prevê tela de restauração de registros excluídos pelo próprio usuário; a exclusão lógica existe para preservar integridade e histórico, não para oferecer "lixeira" navegável nesta primeira versão. Caso um evento de restauração seja necessário tecnicamente (ex.: correção administrativa direta no banco), ele deve ser registrado no log de segurança.
- **Quem pode excluir definitivamente:** não há exclusão física de dados nesta versão, por nenhum perfil.
- **Como registros excluídos aparecem:** registros excluídos não aparecem em nenhuma listagem ativa, filtro padrão ou cálculo de saldo; permanecem apenas como referência em registros de histórico e em lançamentos antigos que utilizavam a categoria/forma de pagamento excluída.
- **Filtros necessários:** toda consulta de listagem e todo cálculo de saldo devem filtrar explicitamente registros com marca de exclusão preenchida.
- **Cuidados contra exclusão indevida:** toda exclusão exige confirmação explícita do usuário na interface (modal de confirmação) antes de ser processada pelo backend.

## 19. Logs

### Log de erros

- **Quais erros serão registrados:** falhas técnicas inesperadas (erros de banco de dados, exceções não tratadas, falhas de configuração), sem incluir erros de validação normal de formulário (que são tratados como mensagens de interface, não como log de erro técnico).
- **Quais informações devem ser gravadas:** data/hora, tipo do erro, mensagem técnica, contexto (rota/ação onde ocorreu), usuário autenticado no momento (quando aplicável) e IP de origem.
- **Como o usuário verá mensagens seguras:** o usuário final nunca vê mensagem técnica ou stack trace; vê apenas uma mensagem genérica e amigável (ex.: "Ocorreu um erro ao processar sua solicitação. Tente novamente."), sem detalhes internos do sistema.
- **Quem poderá consultar os logs:** nesta versão, sem perfil de administrador, os logs de erro não são expostos em nenhuma tela do sistema; ficam disponíveis apenas para consulta técnica direta no banco de dados ou nos arquivos de log pelo responsável técnico do sistema.
- **Onde o log será armazenado:** preferencialmente na tabela `logs_erros` do banco de dados.
- **Como os logs serão protegidos:** acesso restrito a nível de banco de dados/servidor; nenhuma rota da aplicação expõe os logs de erro a usuários finais.

**Estratégia de contingência (log em arquivo):** quando o banco de dados estiver indisponível, a conexão falhar, o próprio erro impedir o registro normal em banco, ou ocorrer falha crítica antes da inicialização completa do sistema, o erro deve ser registrado em arquivo de texto dentro da pasta `logs/erros/`, localizada fora do alcance público de acesso via navegador e protegida contra acesso direto por URL (conforme Seção 5.3). O formato do arquivo deve conter, no mínimo, data/hora, tipo do erro e mensagem técnica, em uma linha por evento (ex.: arquivo diário `logs/erros/erro_AAAA-MM-DD.log`).

### Log de segurança

Eventos registrados:
- Login inválido (tentativa de login com credenciais incorretas).
- Acesso negado (tentativa de acessar recurso de outro usuário ou área protegida sem sessão válida).
- Bloqueio por tentativas de login.
- Alteração de dados sensíveis da conta (e-mail ou senha).
- Exclusão de registros importantes (lançamentos, categorias, formas de pagamento, conta do usuário).
- Restauração de registros importantes, se e quando ocorrer por meio técnico direto.
- Solicitação e conclusão de recuperação de senha.

Cada evento deve registrar: data/hora, tipo de evento, usuário relacionado (quando identificável), e-mail informado na tentativa (para tentativas de login sem autenticação), IP de origem e detalhes adicionais relevantes.

## 20. Configurações Globais

Não há configurações globais editáveis pela interface nesta primeira versão.

A estratégia de configuração técnica do projeto é a seguinte:

- Não deve ser utilizado arquivo `.env` para armazenar credenciais ou parâmetros técnicos sensíveis.
- Deve ser utilizado um arquivo de configuração em código PHP: `config/config.php`.
- Esse arquivo poderá conter, quando aplicável: dados de conexão com o banco de dados (host, nome do banco, usuário, senha), credenciais de SMTP para envio do e-mail de recuperação de senha, flags de ativação de logs (erro/segurança), tempo de expiração de sessão, tempo de expiração do token de recuperação de senha, e número máximo de tentativas de login antes do bloqueio temporário.
- O arquivo de configuração deve ficar dentro do `[Diretório do Projeto - Repositório]`, na pasta `config/`.
- O arquivo não deve ser acessível diretamente pelo navegador; deve ser carregado apenas internamente pelo código PHP, via `require`/`include`.
- A pasta `config/` deve ser protegida contra acesso direto por URL, conforme detalhado na Seção 5.3. Em ambiente Apache, pode-se usar `.htaccess` como camada adicional, mas a proteção não deve depender exclusivamente disso.
- **Fallback quando uma configuração estiver ausente:** o sistema deve utilizar valores padrão seguros previstos em código (ex.: expiração de sessão de 30 minutos, expiração de token de 1 hora, 5 tentativas de login) caso o parâmetro correspondente não esteja definido no arquivo de configuração, evitando falha total da aplicação por ausência de configuração. Esses valores são padrões provisórios, ainda não confirmados formalmente pelo usuário — ver Seção 27.

## 21. Uploads, Anexos e Arquivos

Não haverá upload, anexos ou arquivos nesta primeira versão do FinControle. Este recurso não faz parte do escopo do sistema e não deve ser implementado.

## 22. Relatórios, Consultas e Exportações

Não haverá relatórios formais nem exportações (CSV, PDF, Excel) nesta primeira versão. As únicas formas de consulta de dados são:

- Painel financeiro (Seção 12), com indicadores do mês corrente.
- Listagem de lançamentos com filtros por período, categoria e forma de pagamento.
- Histórico de alterações com filtros por período, categoria e forma de pagamento.

Todas as consultas acima respeitam obrigatoriamente o isolamento de dados por usuário autenticado. Não há exportação de arquivos em nenhuma dessas telas nesta versão.

## 23. APIs e Integrações Externas

Não haverá APIs (internas ou externas) nem integrações externas de qualquer natureza (bancárias, mensageria, calendário, pagamentos, etc.) nesta primeira versão do FinControle. Este recurso não faz parte do escopo do sistema e não deve ser implementado.

## 24. Segurança Funcional

- **Proteção de rotas:** todas as rotas que manipulam dados exigem sessão autenticada válida, verificada no Controller antes de qualquer operação.
- **Validação de permissões no backend:** toda operação de leitura, edição ou exclusão de lançamento, categoria, forma de pagamento ou histórico deve validar que o registro pertence ao usuário autenticado, mesmo que a interface já filtre corretamente — a validação de posse nunca deve depender apenas do front-end.
- **Proteção contra acesso indevido:** pastas internas (`config/`, `app/`, `database/migrations/`, `logs/`) protegidas contra acesso direto por URL (Seção 5.3); nenhuma rota pública deve expor caminhos de arquivos internos.
- **Cuidado com dados sensíveis:** senha do usuário sempre armazenada com hash seguro, nunca em texto puro; tokens de recuperação de senha armazenados com hash e com validade limitada e uso único.
- **Cuidado com mensagens de erro:** mensagens exibidas ao usuário nunca revelam detalhes técnicos internos (consultas SQL, caminhos de arquivo, stack traces); mensagens de login inválido não revelam se o e-mail existe no sistema.
- **Registro de eventos sensíveis:** login inválido, acesso negado, bloqueio por tentativas, alteração de dados sensíveis da conta e exclusão/restauração de registros importantes são sempre registrados no log de segurança (Seção 19).
- **Revisão de segurança recomendada:** antes da entrega, revisar manualmente que (a) nenhuma pasta interna é acessível via navegador, (b) nenhuma rota expõe dados de outro usuário mesmo manipulando parâmetros de URL, (c) senhas e tokens estão sempre armazenados com hash, e (d) o arquivo `config/config.php` não está acessível publicamente.

## 25. Organização Sugerida da Implementação

A organização abaixo considera que o projeto será criado inicialmente em ambiente local, no XAMPP, dentro de uma subpasta como `htdocs/fincontrole/` (podendo depois ser publicado na Hostnet, dentro de `www/fincontrole/` ou pasta equivalente). Esta seção define apenas a **ordem sugerida de execução**; o detalhamento de cada item (estrutura de pastas, proteção de acesso, estratégia de migrations) já está descrito integralmente nas Seções 5.2, 5.3, 5.4 e 11 — não é repetido aqui.

1. Preparação do `[Diretório do Projeto - Repositório]` (criação da pasta do projeto dentro do XAMPP).
2. Criação da estrutura inicial de pastas, conforme Seção 5.2.
3. Configuração inicial do projeto (arquivo de entrada `index.php`, roteamento básico).
4. Criação do arquivo de configuração em código (`config/config.php`), conforme Seção 5.4, sem uso de `.env`.
5. Proteção de pastas internas contra acesso direto pelo navegador, conforme Seção 5.3.
6. Estrutura arquitetural MVC (Models, Views, Controllers, Services), conforme Seção 5.1.
7. Criação do banco de dados MySQL.
8. Criação da estrutura de migrations e da tabela de controle `migrations`, conforme Seção 11 ("Estratégia de Migrations").
9. Criação das migrations das tabelas, campos, chaves e índices descritos na Seção 11, incluindo a inserção das categorias e formas de pagamento padrão do sistema.
10. Definição e implementação do mecanismo de controle de migrations executadas, conforme Seção 11.
11. Módulo de autenticação (cadastro, login, logout, recuperação e redefinição de senha, bloqueio por tentativas).
12. Módulo de conta do usuário (edição de e-mail/senha, exclusão de conta).
13. Recursos estruturais (soft delete, geração de histórico de alterações, logs de erro e de segurança).
14. Entidades principais (categorias, formas de pagamento, lançamentos).
15. CRUDs de categorias e formas de pagamento.
16. CRUD de lançamentos financeiros (criação, edição, atualização de status, exclusão).
17. Painel financeiro (cálculo de saldo realizado/previsto, totais, gráfico de gastos por categoria, últimos lançamentos, próximos pendentes com destaque de atrasados).
18. Tela e filtros do histórico de alterações.
19. Aplicação da identidade visual do `docs/DESIGN.md` em todas as telas.
20. Log de erros com estratégia de contingência em arquivo.
21. Revisão de segurança (rotas, permissões, proteção de pastas, hashes).
22. Revisão de qualidade (validações, mensagens de erro, estados vazios/carregando/sucesso).
23. Preparação da entrega (organização final do repositório, verificação de que nenhuma pasta interna está exposta).

## 26. Critérios de Aceitação Técnica e Funcional

- Funcionalidades principais implementadas: cadastro, login, recuperação de senha, edição de conta, exclusão de conta, CRUD de lançamentos, CRUD de categorias e formas de pagamento, painel financeiro, histórico de alterações.
- Arquitetura MVC respeitada, com separação clara entre Models, Views, Controllers e Services.
- Isolamento de dados por usuário validado no backend em todas as rotas.
- Validações funcionando conforme a Seção 14 (valor positivo, descrição até 300 caracteres, campos obrigatórios, data efetiva obrigatória ao concluir).
- Indicadores do painel financeiro (total de receitas, total de despesas, gráfico de gastos por categoria) calculados exatamente conforme as regras da Seção 14 ("Cálculo dos Indicadores do Painel Financeiro").
- Histórico de edição registrado com um registro por campo alterado, conforme padronizado na Seção 13 e na estrutura da Seção 11.
- Banco de dados coerente com o modelo proposto na Seção 11, incluindo soft delete e campos de auditoria.
- Índices criados para consultas críticas (filtros de lançamentos e histórico, cálculo de saldo, gráfico por categoria).
- Log de erros funcionando, incluindo o mecanismo de contingência em arquivo.
- Log de segurança funcionando para todos os eventos listados na Seção 19.
- Histórico de alterações funcionando para criação/edição/exclusão de lançamentos e exclusão de categorias/formas de pagamento.
- Soft delete funcionando para lançamentos, categorias, formas de pagamento e conta do usuário.
- Telas aderentes ao `docs/DESIGN.md`.
- Erros tratados de forma segura, sem exposição de detalhes técnicos ao usuário final.
- Ausência de funcionalidades não previstas neste FSD (sem uploads, exportações, APIs, integrações, RBAC, perfil de administrador ou lançamentos recorrentes).
- Revisão de segurança concluída (rotas, permissões, proteção de pastas internas, hashes de senha e token).
- Revisão de qualidade concluída (validações, mensagens, estados de tela).
- Estrutura do projeto organizada a partir do `[Diretório do Projeto - Repositório]`, sem dependência de nomes fixos como `public_html`, `public`, `htdocs` ou `www` na arquitetura do sistema.
- Arquivo de configuração em código criado e protegido, sem uso de `.env`.
- Credenciais sensíveis não expostas em arquivos acessíveis diretamente pelo navegador.
- Pastas internas (`config/`, `app/`, `database/`, `logs/`) protegidas contra acesso direto por URL.
- Migrations criadas para toda a estrutura do banco de dados descrita na Seção 11.
- Migrations contemplando tabelas, campos, índices e constraints necessários.
- Mecanismo definido e funcionando para evitar execução duplicada de migrations.
- Migrations não acessíveis diretamente pelo navegador.
- Execução de migrations feita por meio controlado e seguro (script de linha de comando ou rotina interna protegida), nunca por rota pública aberta.

## 27. Parâmetros Confirmados

- **Quantidade de itens exibidos no painel financeiro:** exibição dos **5 lançamentos mais recentes** (ordenados por data de criação decrescente) e dos **5 próximos lançamentos pendentes** (ordenados por data prevista) em cada lista do painel. Esse número poderá ser ajustado futuramente sem impacto estrutural no sistema.
- **Regras mínimas de segurança de senha, expiração de sessão, expiração de token de recuperação de senha e política de bloqueio por tentativas de login:** confirmados formalmente pelo usuário em 28/07/2026 como valores definitivos desta versão: senha com mínimo de **8 caracteres**; expiração de sessão por inatividade de **30 minutos**; expiração do token de recuperação de senha de **1 hora**; bloqueio de login após **5 tentativas inválidas consecutivas**, com duração de bloqueio de **15 minutos**. Todos esses valores ficam centralizados em `config/config.php` (Seção 20), podendo ser ajustados futuramente sem impacto estrutural no sistema.

Não há pendências bloqueantes para o início da codificação com base neste FSD.

## 28. Conclusão

Este FSD está pronto para orientar uma IA codificadora na implementação completa da primeira versão do FinControle, um sistema web de gestão financeira pessoal em PHP, MySQL e Bootstrap local, organizado em MVC, sem uso de `.env`, com migrations controladas, soft delete, histórico de alterações, logs de erro e de segurança, e isolamento estrito de dados por usuário autenticado.

Os parâmetros de segurança listados na Seção 27 (tamanho mínimo de senha, expiração de sessão, expiração de token de recuperação e política de bloqueio por tentativas) foram confirmados formalmente pelo usuário e são considerados definitivos para esta versão.

Os documentos que devem ser entregues à IA codificadora, junto com este FSD, são:

- `docs/FSD.md` — este documento.
- `docs/DESIGN.md` — referência visual de estilo, componentes e layout.

Nenhum outro documento é necessário para a implementação do sistema.
