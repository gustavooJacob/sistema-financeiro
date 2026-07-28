<?php
/**
 * Formulário de criação/edição de Lançamento (FSD, Seção 12).
 *
 * @var string $modo 'criar' ou 'editar'
 * @var array $erros
 * @var array $valores
 * @var array $categorias
 * @var array $formasPagamento
 * @var string $csrfToken
 * @var array|null $flash
 */
$classeCartaoExtra = 'cartao-largo';
require __DIR__ . '/../partials/topo.php';

$acao = $modo === 'criar' ? Sessao::url('/lancamentos/criar') : Sessao::url('/lancamentos/editar');
$statusAtual = $valores['status'] ?? 'pendente';
?>
    <h1><?= $modo === 'criar' ? 'Novo Lançamento' : 'Editar Lançamento' ?></h1>

    <form method="post" action="<?= htmlspecialchars($acao) ?>" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <?php if ($modo === 'editar'): ?>
            <input type="hidden" name="id" value="<?= (int) $valores['id'] ?>">
        <?php endif; ?>

        <div class="campo">
            <label for="tipo">Tipo</label>
            <select id="tipo" name="tipo" required>
                <option value="receita" <?= ($valores['tipo'] ?? '') === 'receita' ? 'selected' : '' ?>>Receita</option>
                <option value="despesa" <?= ($valores['tipo'] ?? '') === 'despesa' ? 'selected' : '' ?>>Despesa</option>
            </select>
            <?php if (!empty($erros['tipo'])): ?><div class="campo-erro"><?= htmlspecialchars($erros['tipo']) ?></div><?php endif; ?>
        </div>

        <div class="campo">
            <label for="valor">Valor (R$)</label>
            <input type="number" id="valor" name="valor" step="0.01" min="0.01" value="<?= htmlspecialchars((string) ($valores['valor'] ?? '')) ?>" required>
            <?php if (!empty($erros['valor'])): ?><div class="campo-erro"><?= htmlspecialchars($erros['valor']) ?></div><?php endif; ?>
        </div>

        <div class="campo">
            <label for="categoria_id">Categoria</label>
            <select id="categoria_id" name="categoria_id" required>
                <option value="">Selecione</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= (int) $categoria['id'] ?>" <?= (int) ($valores['categoria_id'] ?? 0) === (int) $categoria['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($categoria['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($erros['categoria_id'])): ?><div class="campo-erro"><?= htmlspecialchars($erros['categoria_id']) ?></div><?php endif; ?>
        </div>

        <div class="campo">
            <label for="forma_pagamento_id">Forma de pagamento</label>
            <select id="forma_pagamento_id" name="forma_pagamento_id" required>
                <option value="">Selecione</option>
                <?php foreach ($formasPagamento as $forma): ?>
                    <option value="<?= (int) $forma['id'] ?>" <?= (int) ($valores['forma_pagamento_id'] ?? 0) === (int) $forma['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($forma['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($erros['forma_pagamento_id'])): ?><div class="campo-erro"><?= htmlspecialchars($erros['forma_pagamento_id']) ?></div><?php endif; ?>
        </div>

        <div class="campo">
            <label for="descricao">Descrição (opcional)</label>
            <input type="text" id="descricao" name="descricao" maxlength="300" value="<?= htmlspecialchars((string) ($valores['descricao'] ?? '')) ?>">
            <?php if (!empty($erros['descricao'])): ?><div class="campo-erro"><?= htmlspecialchars($erros['descricao']) ?></div><?php endif; ?>
        </div>

        <div class="campo">
            <label for="data_prevista">Data prevista</label>
            <input type="date" id="data_prevista" name="data_prevista" value="<?= htmlspecialchars((string) ($valores['data_prevista'] ?? '')) ?>" required>
            <?php if (!empty($erros['data_prevista'])): ?><div class="campo-erro"><?= htmlspecialchars($erros['data_prevista']) ?></div><?php endif; ?>
        </div>

        <div class="campo">
            <label for="status">Status</label>
            <select id="status" name="status" required onchange="document.getElementById('campo-data-efetiva').style.display = this.value === 'concluido' ? 'block' : 'none';">
                <option value="pendente" <?= $statusAtual === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                <option value="concluido" <?= $statusAtual === 'concluido' ? 'selected' : '' ?>>Concluído</option>
            </select>
            <?php if (!empty($erros['status'])): ?><div class="campo-erro"><?= htmlspecialchars($erros['status']) ?></div><?php endif; ?>
        </div>

        <div class="campo" id="campo-data-efetiva" style="display: <?= $statusAtual === 'concluido' ? 'block' : 'none' ?>;">
            <label for="data_efetiva">Data efetiva</label>
            <input type="date" id="data_efetiva" name="data_efetiva" value="<?= htmlspecialchars((string) ($valores['data_efetiva'] ?? '')) ?>">
            <?php if (!empty($erros['data_efetiva'])): ?><div class="campo-erro"><?= htmlspecialchars($erros['data_efetiva']) ?></div><?php endif; ?>
        </div>

        <div class="acoes-formulario">
            <button type="submit" class="botao-primario botao-auto">Salvar</button>
            <a href="<?= htmlspecialchars(Sessao::url('/lancamentos')) ?>" class="botao-secundario botao-auto">Cancelar</a>
        </div>
    </form>
<?php require __DIR__ . '/../partials/fim.php'; ?>
