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
