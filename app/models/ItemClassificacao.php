<?php
/**
 * Model compartilhado para as entidades Categoria e Forma de Pagamento
 * (FSD, Seção 10/11): estruturas e regras idênticas, diferindo apenas pela
 * tabela de origem — por isso uma única classe parametrizada pela tabela,
 * em vez de duas classes duplicadas.
 */

declare(strict_types=1);

class ItemClassificacao
{
    private const TABELAS_PERMITIDAS = ['categorias', 'formas_pagamento'];

    private PDO $pdo;
    private string $tabela;

    public function __construct(PDO $pdo, string $tabela)
    {
        if (!in_array($tabela, self::TABELAS_PERMITIDAS, true)) {
            throw new InvalidArgumentException("Tabela não permitida: {$tabela}");
        }

        $this->pdo = $pdo;
        $this->tabela = $tabela;
    }

    /**
     * Itens padrão do sistema (usuario_id nulo) e itens próprios do usuário,
     * ativos (não excluídos), ordenados com os padrão primeiro.
     */
    public function listarAtivos(int $usuarioId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->tabela}
             WHERE excluido_em IS NULL AND (usuario_id IS NULL OR usuario_id = :usuario_id)
             ORDER BY padrao DESC, nome ASC"
        );
        $stmt->execute(['usuario_id' => $usuarioId]);

        return $stmt->fetchAll();
    }

    /**
     * Verifica duplicidade de nome (case-insensitive) contra os itens padrão
     * do sistema e os itens próprios do usuário (FSD, Seção 14).
     */
    public function nomeDuplicado(int $usuarioId, string $nome): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM {$this->tabela}
             WHERE excluido_em IS NULL AND (usuario_id IS NULL OR usuario_id = :usuario_id)
             AND LOWER(nome) = LOWER(:nome)"
        );
        $stmt->execute(['usuario_id' => $usuarioId, 'nome' => $nome]);

        return ((int) $stmt->fetchColumn()) > 0;
    }

    public function criar(int $usuarioId, string $nome): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->tabela} (usuario_id, nome, padrao, criado_em, atualizado_em)
             VALUES (:usuario_id, :nome, 0, NOW(), NOW())"
        );
        $stmt->execute(['usuario_id' => $usuarioId, 'nome' => $nome]);

        return (int) $this->pdo->lastInsertId();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->tabela} WHERE id = :id AND excluido_em IS NULL LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();

        return $item ?: null;
    }

    /**
     * Soft delete de item próprio. Retorna false se o item não existir, já
     * estiver excluído, for padrão do sistema ou pertencer a outro usuário —
     * a posse é sempre validada aqui, independentemente da interface.
     */
    public function excluirProprio(int $id, int $usuarioId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE {$this->tabela} SET excluido_em = NOW(), atualizado_em = NOW()
             WHERE id = :id AND usuario_id = :usuario_id AND padrao = 0 AND excluido_em IS NULL"
        );
        $stmt->execute(['id' => $id, 'usuario_id' => $usuarioId]);

        return $stmt->rowCount() > 0;
    }
}
