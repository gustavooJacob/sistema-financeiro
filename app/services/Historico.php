<?php
/**
 * Geração de registros no histórico de alterações (FSD, Seção 17): criação,
 * edição (por campo) e exclusão de lançamentos; exclusão de categorias e
 * formas de pagamento próprias. Nunca editável ou excluível pelo usuário.
 */

declare(strict_types=1);

class Historico
{
    public static function registrar(
        PDO $pdo,
        int $usuarioId,
        string $entidadeTipo,
        int $entidadeId,
        string $acao,
        ?string $campoAlterado,
        ?string $valorAnterior,
        ?string $valorNovo
    ): void {
        $stmt = $pdo->prepare(
            'INSERT INTO historico_alteracoes
                (usuario_id, entidade_tipo, entidade_id, acao, campo_alterado, valor_anterior, valor_novo, data_alteracao)
             VALUES (:usuario_id, :entidade_tipo, :entidade_id, :acao, :campo_alterado, :valor_anterior, :valor_novo, NOW())'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'entidade_tipo' => $entidadeTipo,
            'entidade_id' => $entidadeId,
            'acao' => $acao,
            'campo_alterado' => $campoAlterado,
            'valor_anterior' => $valorAnterior,
            'valor_novo' => $valorNovo,
        ]);
    }

    /**
     * Lista o histórico do usuário (FSD, Seção 12/17), incluindo alterações
     * de lançamentos, categorias e formas de pagamento já excluídos. Filtros
     * de categoria/forma de pagamento consideram tanto o próprio registro
     * de histórico daquela categoria/forma (ex.: sua exclusão) quanto os
     * lançamentos atualmente vinculados a ela — o histórico não guarda a
     * categoria/forma vigente em cada momento passado, apenas o estado
     * atual do lançamento referenciado, decisão adotada por não haver
     * definição mais específica no FSD para este cruzamento de filtros.
     */
    public static function listar(PDO $pdo, int $usuarioId, array $filtros, int $pagina, int $porPagina = 20): array
    {
        $condicoes = ['he.usuario_id = :usuario_id'];
        $parametros = ['usuario_id' => $usuarioId];

        if (!empty($filtros['data_inicio'])) {
            $condicoes[] = 'he.data_alteracao >= :data_inicio';
            $parametros['data_inicio'] = $filtros['data_inicio'] . ' 00:00:00';
        }

        if (!empty($filtros['data_fim'])) {
            $condicoes[] = 'he.data_alteracao <= :data_fim';
            $parametros['data_fim'] = $filtros['data_fim'] . ' 23:59:59';
        }

        if (!empty($filtros['categoria_id'])) {
            $condicoes[] = "((he.entidade_tipo = 'categoria' AND he.entidade_id = :categoria_id_entidade)
                OR (he.entidade_tipo = 'lancamento' AND l.categoria_id = :categoria_id_lancamento))";
            $parametros['categoria_id_entidade'] = (int) $filtros['categoria_id'];
            $parametros['categoria_id_lancamento'] = (int) $filtros['categoria_id'];
        }

        if (!empty($filtros['forma_pagamento_id'])) {
            $condicoes[] = "((he.entidade_tipo = 'forma_pagamento' AND he.entidade_id = :forma_pagamento_id_entidade)
                OR (he.entidade_tipo = 'lancamento' AND l.forma_pagamento_id = :forma_pagamento_id_lancamento))";
            $parametros['forma_pagamento_id_entidade'] = (int) $filtros['forma_pagamento_id'];
            $parametros['forma_pagamento_id_lancamento'] = (int) $filtros['forma_pagamento_id'];
        }

        $whereSql = implode(' AND ', $condicoes);
        $fromSql = "historico_alteracoes he LEFT JOIN lancamentos l ON l.id = he.entidade_id AND he.entidade_tipo = 'lancamento'";

        $stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM {$fromSql} WHERE {$whereSql}");
        $stmtTotal->execute($parametros);
        $total = (int) $stmtTotal->fetchColumn();

        $pagina = max(1, $pagina);
        $offset = ($pagina - 1) * $porPagina;

        $stmt = $pdo->prepare(
            "SELECT he.* FROM {$fromSql}
             WHERE {$whereSql}
             ORDER BY he.data_alteracao DESC
             LIMIT {$porPagina} OFFSET {$offset}"
        );
        $stmt->execute($parametros);

        return ['itens' => $stmt->fetchAll(), 'total' => $total];
    }
}
