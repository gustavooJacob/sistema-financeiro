<?php
/**
 * Model da entidade Usuário (FSD, Seção 10 e 11).
 *
 * Responsável por toda a comunicação com a tabela `usuarios`: criação,
 * busca, hash/verificação de senha e controle de bloqueio por tentativas
 * de login inválidas (FSD, Seção 15).
 */

declare(strict_types=1);

class Usuario
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function emailExiste(string $email): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM usuarios WHERE email = :email AND excluido_em IS NULL'
        );
        $stmt->execute(['email' => $email]);

        return ((int) $stmt->fetchColumn()) > 0;
    }

    public function criar(string $email, string $senha): int
    {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (email, senha_hash, criado_em, atualizado_em)
             VALUES (:email, :senha_hash, NOW(), NOW())'
        );
        $stmt->execute(['email' => $email, 'senha_hash' => $senhaHash]);

        return (int) $this->pdo->lastInsertId();
    }

    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM usuarios WHERE email = :email AND excluido_em IS NULL LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM usuarios WHERE id = :id AND excluido_em IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function estaBloqueado(array $usuario): bool
    {
        if (empty($usuario['bloqueado_ate'])) {
            return false;
        }

        return new DateTimeImmutable($usuario['bloqueado_ate']) > new DateTimeImmutable();
    }

    public function registrarTentativaFalha(int $id, int $maxTentativas, int $minutosBloqueio): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET tentativas_login_falhas = tentativas_login_falhas + 1, atualizado_em = NOW()
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        $stmt = $this->pdo->prepare('SELECT tentativas_login_falhas FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $tentativas = (int) $stmt->fetchColumn();

        if ($tentativas >= $maxTentativas) {
            $bloqueadoAte = (new DateTimeImmutable())
                ->modify("+{$minutosBloqueio} minutes")
                ->format('Y-m-d H:i:s');

            $stmt = $this->pdo->prepare(
                'UPDATE usuarios SET bloqueado_ate = :bloqueado_ate, atualizado_em = NOW() WHERE id = :id'
            );
            $stmt->execute(['bloqueado_ate' => $bloqueadoAte, 'id' => $id]);
        }
    }

    public function resetarTentativasFalhas(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET tentativas_login_falhas = 0, bloqueado_ate = NULL, atualizado_em = NOW()
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }

    public function atualizarSenha(int $id, string $novaSenha): void
    {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET senha_hash = :senha_hash, atualizado_em = NOW() WHERE id = :id'
        );
        $stmt->execute(['senha_hash' => $senhaHash, 'id' => $id]);
    }

    public function atualizarEmail(int $id, string $novoEmail): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET email = :email, atualizado_em = NOW() WHERE id = :id'
        );
        $stmt->execute(['email' => $novoEmail, 'id' => $id]);
    }

    /**
     * Exclusão da própria conta (soft delete — FSD, Seção 6/Módulo 1).
     * Os dados financeiros do usuário permanecem no banco, preservando
     * integridade e histórico; apenas o acesso é impedido a partir daqui.
     */
    public function excluir(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET excluido_em = NOW(), atualizado_em = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }
}
