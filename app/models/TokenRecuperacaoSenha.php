<?php
/**
 * Model da entidade Token de Recuperação de Senha (FSD, Seção 10 e 11).
 *
 * O valor do token nunca é armazenado em texto puro, apenas seu hash
 * (SHA-256). Cada token tem expiração limitada e uso único.
 */

declare(strict_types=1);

class TokenRecuperacaoSenha
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function criar(int $usuarioId, string $tokenTextoPuro, int $minutosValidade): void
    {
        $tokenHash = hash('sha256', $tokenTextoPuro);
        $expiraEm = (new DateTimeImmutable())->modify("+{$minutosValidade} minutes")->format('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare(
            'INSERT INTO tokens_recuperacao_senha (usuario_id, token_hash, expira_em, utilizado, criado_em)
             VALUES (:usuario_id, :token_hash, :expira_em, 0, NOW())'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'token_hash' => $tokenHash,
            'expira_em' => $expiraEm,
        ]);
    }

    public function buscarValidoPorTexto(string $tokenTextoPuro): ?array
    {
        $tokenHash = hash('sha256', $tokenTextoPuro);

        $stmt = $this->pdo->prepare(
            'SELECT * FROM tokens_recuperacao_senha
             WHERE token_hash = :token_hash AND utilizado = 0 AND expira_em > NOW()
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['token_hash' => $tokenHash]);
        $token = $stmt->fetch();

        return $token ?: null;
    }

    public function marcarUtilizado(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE tokens_recuperacao_senha SET utilizado = 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
