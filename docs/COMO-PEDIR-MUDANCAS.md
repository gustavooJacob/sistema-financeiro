# COMO-PEDIR-MUDANCAS.md — Guia para Pedir Alterações no FinControle

Este documento ajuda **qualquer pessoa**, mesmo sem conhecimento técnico, a pedir mudanças futuras no FinControle para uma IA (Claude Code ou similar) de forma segura e eficaz.

## Como pedir mudanças de forma simples

1. **Sempre abra um chat novo** para cada tarefa nova (evita que a IA misture contexto de tarefas diferentes).
2. **Peça para a IA ler os documentos do projeto antes de começar.** É a parte mais importante — sem isso, a IA pode "inventar" uma solução que não segue as regras do sistema. Inclua sempre esta linha no início do seu pedido:

   > Antes de começar, leia `docs/MANUTENCAO.md`, `docs/FSD.md`, `docs/DESIGN.md`, `docs/STATUS.md` e `docs/ERROS.md`.

3. **Descreva o que você quer, não como fazer.** Você não precisa saber PHP nem MySQL — descreva o resultado esperado (ex.: "quero um campo de observações no lançamento") e deixe a IA decidir a implementação.
4. **Peça uma coisa por vez.** Mudanças pequenas e isoladas são mais fáceis de revisar e têm menos risco de quebrar algo que já funciona.
5. **Sempre peça para testar antes de considerar pronto.** A IA deve rodar o sistema localmente (XAMPP) e mostrar o que testou.
6. **No final, peça para atualizar `docs/STATUS.md`** (o que foi feito) **e `docs/ERROS.md`** (se algum erro apareceu e foi corrigido).

## Checklist antes de aceitar uma alteração

Antes de considerar uma mudança concluída, confirme que a IA:

- [ ] Leu os documentos vivos (`MANUTENCAO.md`, `FSD.md`, `DESIGN.md`, `STATUS.md`, `ERROS.md`) antes de alterar código.
- [ ] Explicou o plano antes de mexer nos arquivos (ou pelo menos explicou o que mudou, ao final).
- [ ] Testou a alteração de verdade (não só disse "deve funcionar") — pediu para rodar o sistema no navegador ou mostrou o resultado do teste.
- [ ] Não alterou áreas do sistema que você não pediu para mexer.
- [ ] Não removeu nenhuma proteção de segurança (login, confirmação de senha, proteção contra acesso a dados de outro usuário, etc.).
- [ ] Atualizou `docs/STATUS.md` com o que foi feito.
- [ ] Registrou em `docs/ERROS.md` qualquer erro encontrado durante o processo (se houve).
- [ ] Informou como você pode testar a mudança por conta própria.
- [ ] Não pediu para você colar senhas, chaves de banco ou qualquer credencial diretamente na conversa (essas informações só devem existir em `config/config.php`, no seu computador).

Se qualquer item acima não foi cumprido, peça para a IA corrigir antes de considerar a tarefa concluída.

## Modelos de prompts prontos

Copie, adapte o que estiver entre colchetes `[ ]` e envie.

### 1. Adicionar um campo em um cadastro

> Leia `docs/MANUTENCAO.md`, `docs/FSD.md`, `docs/DESIGN.md`, `docs/STATUS.md` e `docs/ERROS.md` antes de começar.
>
> Quero adicionar o campo **[nome do campo, ex.: "observações"]** ao cadastro de **[entidade, ex.: "lançamento financeiro"]**. Ele deve ser **[obrigatório/opcional]**, do tipo **[texto/número/data/etc.]**, e aparecer **[onde: no formulário, na listagem, em ambos]**.
>
> Depois de implementar, teste criando e editando um registro com esse campo preenchido e vazio (se opcional), confirme que nada quebrou nas outras telas, e atualize `docs/STATUS.md`.

### 2. Criar uma nova tela

> Leia `docs/MANUTENCAO.md`, `docs/FSD.md`, `docs/DESIGN.md`, `docs/STATUS.md` e `docs/ERROS.md` antes de começar.
>
> Quero criar uma nova tela para **[objetivo da tela, ex.: "listar apenas os lançamentos em atraso"]**. Ela deve ficar acessível em **[de onde, ex.: "um novo item na sidebar" ou "um link dentro da tela de Lançamentos"]** e seguir o mesmo padrão visual das demais telas internas (`docs/DESIGN.md`).
>
> Importante: essa tela só deve mostrar dados do usuário autenticado, como todas as outras telas do sistema.
>
> Depois de implementar, teste acessando a tela logado e deslogado, e com dois usuários diferentes para confirmar que cada um só vê seus próprios dados. Atualize `docs/STATUS.md` ao final.

### 3. Corrigir um erro

> Leia `docs/MANUTENCAO.md`, `docs/FSD.md`, `docs/STATUS.md` e `docs/ERROS.md` antes de começar.
>
> Encontrei o seguinte problema: **[descreva o que aconteceu, com o máximo de detalhe possível: o que você fez, o que esperava que acontecesse, e o que aconteceu de fato — se possível, cole a mensagem de erro exata]**.
>
> Investigue a causa raiz (não aplique um "remendo" que apenas esconda o sintoma), corrija, teste o cenário que falhava e também os cenários próximos que possam ter sido afetados. Registre o erro e a solução em `docs/ERROS.md` e atualize `docs/STATUS.md`.

### 4. Alterar uma regra de negócio

> Leia `docs/MANUTENCAO.md`, `docs/FSD.md` (principalmente as Seções 13 e 14) e `docs/STATUS.md` antes de começar.
>
> A regra atual é: **[descreva a regra como ela funciona hoje, se souber]**. Quero que passe a funcionar assim: **[descreva a nova regra]**.
>
> Confirme comigo se essa mudança conflita com alguma regra já documentada no FSD antes de implementar. Depois de implementar, teste o cenário principal e pelo menos um cenário de erro/borda. Atualize `docs/FSD.md` (se a regra documentada mudou), `docs/STATUS.md` e, se necessário, `docs/ERROS.md`.

### 5. Ajustar visual conforme o DESIGN.md

> Leia `docs/MANUTENCAO.md` e `docs/DESIGN.md` antes de começar.
>
> Na tela **[nome da tela]**, o elemento **[o que está errado, ex.: "o botão de excluir"]** está **[descreva o problema visual]**. Ajuste seguindo exatamente os tokens de cor, espaçamento e tipografia definidos em `docs/DESIGN.md`, reaproveitando as classes já existentes em `assets/css/app.css` sempre que possível (evite criar CSS novo duplicado).
>
> Depois de ajustar, mostre (ou descreva) como ficou e confirme que não quebrou nenhuma outra tela que usa o mesmo componente. Atualize `docs/STATUS.md`.

### 6. Criar um relatório ou filtro

> Leia `docs/MANUTENCAO.md` e `docs/FSD.md` (Seção 7 — Fora de Escopo, e Seção 22 — Relatórios) antes de começar.
>
> Quero adicionar um filtro por **[critério, ex.: "faixa de valor"]** na tela de **[Lançamentos / Histórico]**.
>
> Importante: o FSD desta versão não prevê exportação de arquivos (CSV, PDF, Excel) nem relatórios avançados — se o que estou pedindo for isso, me avise antes de implementar, pois é uma mudança de escopo que precisa da minha confirmação explícita.
>
> Depois de implementar, teste o filtro com e sem resultados (estado vazio) e confirme o isolamento por usuário. Atualize `docs/STATUS.md`.

### 7. Revisar segurança depois de uma mudança

> Leia `docs/MANUTENCAO.md` (seção "Cuidados de segurança") e `docs/FSD.md` (Seções 14–20 e 24) antes de começar.
>
> Acabei de pedir a alteração **[descreva a alteração ou cole o resumo do que foi feito]**. Faça uma revisão de segurança focada nessa mudança: confirme que toda consulta nova usa prepared statements, que toda saída dinâmica está escapada, que formulários novos têm proteção CSRF, que qualquer leitura/edição/exclusão valida `usuario_id` no backend, e que nenhuma pasta interna ficou exposta. Relate o que foi conferido e o resultado.

### 8. Preparar uma alteração para commit

> Depois de concluir e testar a alteração, faça o seguinte:
> 1. Confirme que `docs/STATUS.md` está atualizado com o que foi feito.
> 2. Confirme que `docs/ERROS.md` foi atualizado, se algum erro apareceu.
> 3. Rode `git status` e me mostre o que será commitado, confirmando que nenhum arquivo de segredo (como `config/config.php`) está na lista.
> 4. Crie um commit com uma mensagem clara descrevendo a mudança.
> 5. Não faça `git push` sem eu confirmar antes.
