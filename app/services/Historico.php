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
}
