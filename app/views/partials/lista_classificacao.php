<?php
/**
 * Template compartilhado das telas de Categorias e Formas de Pagamento
 * (FSD, Seção 12 — telas idênticas em estrutura).
 *
 * @var string $tituloPagina
 * @var string $rotuloSingular
 * @var string $rotuloPlural
 * @var string $rotaBase
 * @var array $itens
 * @var array $erros
 * @var array $valores
 * @var string $csrfToken
 * @var array|null $flash
 */
require __DIR__ . '/app_topo.php';

$itensProprios = array_filter($itens, static fn (array $item): bool => (int) $item['padrao'] === 0);
?>
    <h1><?= htmlspecialchars(ucfirst($rotuloPlural)) ?></h1>

    <form method="post" action="<?= htmlspecialchars(Sessao::url($rotaBase . '/criar')) ?>" class="formulario-inline" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="campo">
            <label for="nome">Nova <?= htmlspecialchars($rotuloSingular) ?></label>
            <input type="text" id="nome" name="nome" maxlength="100" value="<?= htmlspecialchars($valores['nome'] ?? '') ?>" required>
            <?php if (!empty($erros['nome'])): ?>
                <div class="campo-erro"><?= htmlspecialchars($erros['nome']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="botao-primario">Adicionar <?= htmlspecialchars($rotuloSingular) ?></button>
    </form>

    <hr class="separador-secao">

    <p class="secao-titulo">Itens disponíveis</p>

    <?php if (empty($itensProprios)): ?>
        <p class="secao-descricao">Você ainda não criou nenhuma <?= htmlspecialchars($rotuloSingular) ?> própria. As <?= htmlspecialchars($rotuloPlural) ?> padrão do sistema já estão disponíveis para uso.</p>
    <?php endif; ?>

    <ul class="lista-classificacao">
        <?php foreach ($itens as $item): ?>
            <li class="item-classificacao">
                <span class="item-nome"><?= htmlspecialchars($item['nome']) ?></span>
                <?php if ((int) $item['padrao'] === 1): ?>
                    <span class="badge badge-neutro">Padrão do sistema</span>
                <?php else: ?>
                    <span class="badge badge-info">Própria</span>
                    <form method="post" action="<?= htmlspecialchars(Sessao::url($rotaBase . '/excluir')) ?>"
                          onsubmit="return confirm('Tem certeza que deseja excluir esta <?= htmlspecialchars($rotuloSingular) ?>?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                        <button type="submit" class="botao-perigo-pequeno">Excluir</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

<?php require __DIR__ . '/app_fim.php'; ?>
