<?php
/**
 * Tela de Lista de Lançamentos (FSD, Seção 12): filtros por período,
 * categoria e forma de pagamento; paginação de 20 por página; destaque
 * visual de "atrasado" para pendentes com data prevista no passado.
 *
 * @var array $lancamentos
 * @var array $categorias
 * @var array $formasPagamento
 * @var int $pagina
 * @var int $totalPaginas
 * @var array $filtros
 * @var string $hoje
 * @var string $csrfToken
 * @var array|null $flash
 */
$classeCartaoExtra = 'cartao-tabela';
$paginaAtiva = 'lancamentos';
require __DIR__ . '/../partials/app_topo.php';
?>
    <h1>Lançamentos</h1>

    <form method="get" action="<?= htmlspecialchars(Sessao::url('/lancamentos')) ?>" class="formulario-filtros">
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
        <a href="<?= htmlspecialchars(Sessao::url('/lancamentos')) ?>" class="botao-ghost botao-auto">Limpar</a>
    </form>

    <div class="acoes-topo">
        <a href="<?= htmlspecialchars(Sessao::url('/lancamentos/novo')) ?>" class="botao-primario botao-auto">Novo lançamento</a>
    </div>

    <?php if (empty($lancamentos)): ?>
        <p class="secao-descricao">Nenhum lançamento encontrado para os filtros aplicados.</p>
    <?php else: ?>
        <table class="tabela-lancamentos">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Categoria</th>
                    <th>Forma de pagamento</th>
                    <th>Descrição</th>
                    <th>Data prevista</th>
                    <th>Data efetiva</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lancamentos as $lancamento): ?>
                    <?php $atrasado = $lancamento['status'] === 'pendente' && $lancamento['data_prevista'] < $hoje; ?>
                    <tr>
                        <td>
                            <span class="badge <?= $lancamento['tipo'] === 'receita' ? 'badge-sucesso' : 'badge-erro' ?>">
                                <?= $lancamento['tipo'] === 'receita' ? 'Receita' : 'Despesa' ?>
                            </span>
                        </td>
                        <td>R$ <?= htmlspecialchars(number_format((float) $lancamento['valor'], 2, ',', '.')) ?></td>
                        <td><?= htmlspecialchars($lancamento['categoria_nome']) ?></td>
                        <td><?= htmlspecialchars($lancamento['forma_pagamento_nome']) ?></td>
                        <td><?= htmlspecialchars($lancamento['descricao'] ?? '') ?></td>
                        <td><?= htmlspecialchars($lancamento['data_prevista']) ?></td>
                        <td><?= htmlspecialchars($lancamento['data_efetiva'] ?? '—') ?></td>
                        <td>
                            <?php if ($atrasado): ?>
                                <span class="badge badge-erro">Atrasado</span>
                            <?php elseif ($lancamento['status'] === 'concluido'): ?>
                                <span class="badge badge-sucesso">Concluído</span>
                            <?php else: ?>
                                <span class="badge badge-neutro">Pendente</span>
                            <?php endif; ?>
                        </td>
                        <td class="celula-acoes">
                            <a href="<?= htmlspecialchars(Sessao::url('/lancamentos/editar?id=' . $lancamento['id'])) ?>" class="botao-link">Editar</a>

                            <?php if ($lancamento['status'] === 'pendente'): ?>
                                <form method="post" action="<?= htmlspecialchars(Sessao::url('/lancamentos/concluir')) ?>" class="formulario-concluir">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $lancamento['id'] ?>">
                                    <input type="date" name="data_efetiva" max="<?= htmlspecialchars($hoje) ?>" required>
                                    <button type="submit" class="botao-link">Concluir</button>
                                </form>
                            <?php endif; ?>

                            <form method="post" action="<?= htmlspecialchars(Sessao::url('/lancamentos/excluir')) ?>"
                                  onsubmit="return confirm('Tem certeza que deseja excluir este lançamento?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="id" value="<?= (int) $lancamento['id'] ?>">
                                <button type="submit" class="botao-link botao-link-perigo">Excluir</button>
                            </form>
                        </td>
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
                    <a href="<?= htmlspecialchars(Sessao::url('/lancamentos') . '?' . http_build_query($query)) ?>"
                       class="link-paginacao <?= $p === $pagina ? 'link-paginacao-ativa' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

<?php require __DIR__ . '/../partials/app_fim.php'; ?>
