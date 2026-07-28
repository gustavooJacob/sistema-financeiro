<?php
/**
 * Tela de Histórico de Alterações (FSD, Seção 12, Módulo 5): consulta
 * somente leitura, com filtros por período, categoria e forma de pagamento;
 * paginação de 20 registros por página. Nenhuma ação de escrita nesta tela.
 *
 * @var array $registros
 * @var array $categorias
 * @var array $formasPagamento
 * @var int $pagina
 * @var int $totalPaginas
 * @var array $filtros
 * @var array|null $flash
 */
$classeCartaoExtra = 'cartao-tabela';
$paginaAtiva = 'historico';
require __DIR__ . '/../partials/app_topo.php';
?>
    <h1>Histórico de Alterações</h1>

    <form method="get" action="<?= htmlspecialchars(Sessao::url('/historico')) ?>" class="formulario-filtros">
        <div class="campo">
            <label for="data_inicio">De</label>
            <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio']) ?>">
        </div>
        <div class="campo">
            <label for="data_fim">Até</label>
            <input type="date" id="data_fim" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim']) ?>">
        </div>
        <div class="campo">
            <label for="categoria_id">Categoria</label>
            <select id="categoria_id" name="categoria_id">
                <option value="">Todas</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= (int) $categoria['id'] ?>" <?= (int) $filtros['categoria_id'] === (int) $categoria['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($categoria['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label for="forma_pagamento_id">Forma de pagamento</label>
            <select id="forma_pagamento_id" name="forma_pagamento_id">
                <option value="">Todas</option>
                <?php foreach ($formasPagamento as $forma): ?>
                    <option value="<?= (int) $forma['id'] ?>" <?= (int) $filtros['forma_pagamento_id'] === (int) $forma['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($forma['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="botao-secundario botao-auto">Filtrar</button>
        <a href="<?= htmlspecialchars(Sessao::url('/historico')) ?>" class="botao-ghost botao-auto">Limpar</a>
    </form>

    <?php if (empty($registros)): ?>
        <p class="secao-descricao">Nenhuma alteração encontrada para os filtros aplicados.</p>
    <?php else: ?>
        <table class="tabela-lancamentos">
            <thead>
                <tr>
                    <th>Data/hora</th>
                    <th>Entidade</th>
                    <th>Ação</th>
                    <th>Campo alterado</th>
                    <th>Valor anterior</th>
                    <th>Valor novo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $registro): ?>
                    <tr>
                        <td><?= htmlspecialchars($registro['data_alteracao']) ?></td>
                        <td><?= htmlspecialchars($registro['entidade_rotulo']) ?></td>
                        <td>
                            <span class="badge <?= $registro['acao'] === 'exclusao' ? 'badge-erro' : ($registro['acao'] === 'criacao' ? 'badge-sucesso' : 'badge-neutro') ?>">
                                <?= htmlspecialchars($registro['acao_rotulo']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($registro['campo_rotulo'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($registro['valor_anterior_exibicao'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($registro['valor_novo_exibicao'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPaginas > 1): ?>
            <div class="paginacao">
                <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                    <?php
                    $query = array_filter([
                        'pagina' => $p,
                        'data_inicio' => $filtros['data_inicio'],
                        'data_fim' => $filtros['data_fim'],
                        'categoria_id' => $filtros['categoria_id'] ?: null,
                        'forma_pagamento_id' => $filtros['forma_pagamento_id'] ?: null,
                    ], static fn ($valor) => $valor !== null && $valor !== '');
                    ?>
                    <a href="<?= htmlspecialchars(Sessao::url('/historico') . '?' . http_build_query($query)) ?>"
                       class="link-paginacao <?= $p === $pagina ? 'link-paginacao-ativa' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

<?php require __DIR__ . '/../partials/app_fim.php'; ?>
