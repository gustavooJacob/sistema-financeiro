<?php
/**
 * Model da entidade Lançamento Financeiro (FSD, Seção 10/11/14): receitas e
 * despesas do usuário autenticado, com validações de valor/descrição/datas,
 * soft delete e isolamento estrito por usuario_id em toda operação de
 * escrita — a posse nunca depende apenas do Controller ou da interface.
 */

declare(strict_types=1);

class Lancamento
{
    private const CAMPOS_EDITAVEIS = [
        'tipo', 'valor', 'categoria_id', 'forma_pagamento_id', 'descricao', 'data_prevista', 'data_efetiva', 'status',
    ];

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function criar(int $usuarioId, array $dados): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO lancamentos
                (usuario_id, tipo, valor, categoria_id, forma_pagamento_id, descricao, data_prevista, data_efetiva, status, criado_em, atualizado_em)
             VALUES
                (:usuario_id, :tipo, :valor, :categoria_id, :forma_pagamento_id, :descricao, :data_prevista, :data_efetiva, :status, NOW(), NOW())'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'tipo' => $dados['tipo'],
            'valor' => $dados['valor'],
            'categoria_id' => $dados['categoria_id'],
            'forma_pagamento_id' => $dados['forma_pagamento_id'],
            'descricao' => $dados['descricao'] !== '' ? $dados['descricao'] : null,
            'data_prevista' => $dados['data_prevista'],
            'data_efetiva' => $dados['data_efetiva'] !== '' ? $dados['data_efetiva'] : null,
            'status' => $dados['status'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Busca um lançamento ativo, já validando que pertence ao usuário
     * informado (FSD, Seção 8/16: isolamento de dados validado no backend).
     */
    public function buscarPorId(int $id, int $usuarioId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM lancamentos WHERE id = :id AND usuario_id = :usuario_id AND excluido_em IS NULL LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'usuario_id' => $usuarioId]);
        $lancamento = $stmt->fetch();

        return $lancamento ?: null;
    }

    /**
     * Atualiza os campos editáveis. Retorna somente os campos cujo valor
     * realmente mudou (usado para gerar um registro de histórico por campo
     * alterado, conforme FSD Seção 13/17).
     */
    public function atualizar(int $id, int $usuarioId, array $dados, array $atual): array
    {
        $novosValores = [
            'tipo' => $dados['tipo'],
            'valor' => $dados['valor'],
            'categoria_id' => $dados['categoria_id'],
            'forma_pagamento_id' => $dados['forma_pagamento_id'],
            'descricao' => $dados['descricao'] !== '' ? $dados['descricao'] : null,
            'data_prevista' => $dados['data_prevista'],
            'data_efetiva' => $dados['data_efetiva'] !== '' ? $dados['data_efetiva'] : null,
            'status' => $dados['status'],
        ];

        $camposAlterados = [];
        foreach (self::CAMPOS_EDITAVEIS as $campo) {
            $valorAtual = $atual[$campo];
            $valorNovo = $novosValores[$campo];

            if ($campo === 'valor') {
                $mudou = bccomp((string) $valorAtual, (string) $valorNovo, 2) !== 0;
            } else {
                $mudou = (string) ($valorAtual ?? '') !== (string) ($valorNovo ?? '');
            }

            if ($mudou) {
                $camposAlterados[$campo] = ['anterior' => $valorAtual, 'novo' => $valorNovo];
            }
        }

        if (empty($camposAlterados)) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            'UPDATE lancamentos SET
                tipo = :tipo, valor = :valor, categoria_id = :categoria_id, forma_pagamento_id = :forma_pagamento_id,
                descricao = :descricao, data_prevista = :data_prevista, data_efetiva = :data_efetiva, status = :status,
                atualizado_em = NOW()
             WHERE id = :id AND usuario_id = :usuario_id AND excluido_em IS NULL'
        );
        $stmt->execute($novosValores + ['id' => $id, 'usuario_id' => $usuarioId]);

        return $camposAlterados;
    }

    /**
     * Marca um lançamento pendente como concluído, informando a data
     * efetiva (FSD, Seção 6/13: data efetiva obrigatória ao concluir).
     * Retorna os campos alterados (status e data_efetiva) para o histórico,
     * ou array vazio se o lançamento não existir, não pertencer ao usuário
     * ou já não estiver pendente.
     */
    public function marcarConcluido(int $id, int $usuarioId, string $dataEfetiva): array
    {
        $stmt = $this->pdo->prepare(
            "UPDATE lancamentos SET status = 'concluido', data_efetiva = :data_efetiva, atualizado_em = NOW()
             WHERE id = :id AND usuario_id = :usuario_id AND status = 'pendente' AND excluido_em IS NULL"
        );
        $stmt->execute(['id' => $id, 'usuario_id' => $usuarioId, 'data_efetiva' => $dataEfetiva]);

        if ($stmt->rowCount() === 0) {
            return [];
        }

        return [
            'status' => ['anterior' => 'pendente', 'novo' => 'concluido'],
            'data_efetiva' => ['anterior' => null, 'novo' => $dataEfetiva],
        ];
    }

    public function excluir(int $id, int $usuarioId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE lancamentos SET excluido_em = NOW(), atualizado_em = NOW()
             WHERE id = :id AND usuario_id = :usuario_id AND excluido_em IS NULL'
        );
        $stmt->execute(['id' => $id, 'usuario_id' => $usuarioId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Lista lançamentos ativos do usuário, com filtros opcionais de período
     * (por data prevista), categoria e forma de pagamento (FSD, Seção 12),
     * paginados em 20 registros por página, ordenados por criação decrescente.
     */
    public function listar(int $usuarioId, array $filtros, int $pagina, int $porPagina = 20): array
    {
        $condicoes = ['l.usuario_id = :usuario_id', 'l.excluido_em IS NULL'];
        $parametros = ['usuario_id' => $usuarioId];

        if (!empty($filtros['data_inicio'])) {
            $condicoes[] = 'l.data_prevista >= :data_inicio';
            $parametros['data_inicio'] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $condicoes[] = 'l.data_prevista <= :data_fim';
            $parametros['data_fim'] = $filtros['data_fim'];
        }

        if (!empty($filtros['categoria_id'])) {
            $condicoes[] = 'l.categoria_id = :categoria_id';
            $parametros['categoria_id'] = (int) $filtros['categoria_id'];
        }

        if (!empty($filtros['forma_pagamento_id'])) {
            $condicoes[] = 'l.forma_pagamento_id = :forma_pagamento_id';
            $parametros['forma_pagamento_id'] = (int) $filtros['forma_pagamento_id'];
        }

        $whereSql = implode(' AND ', $condicoes);

        $stmtTotal = $this->pdo->prepare("SELECT COUNT(*) FROM lancamentos l WHERE {$whereSql}");
        $stmtTotal->execute($parametros);
        $total = (int) $stmtTotal->fetchColumn();

        $pagina = max(1, $pagina);
        $offset = ($pagina - 1) * $porPagina;

        $stmt = $this->pdo->prepare(
            "SELECT l.*, c.nome AS categoria_nome, f.nome AS forma_pagamento_nome
             FROM lancamentos l
             INNER JOIN categorias c ON c.id = l.categoria_id
             INNER JOIN formas_pagamento f ON f.id = l.forma_pagamento_id
             WHERE {$whereSql}
             ORDER BY l.criado_em DESC
             LIMIT {$porPagina} OFFSET {$offset}"
        );
        $stmt->execute($parametros);

        return ['itens' => $stmt->fetchAll(), 'total' => $total];
    }
}
