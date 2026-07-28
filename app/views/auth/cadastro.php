<?php
/**
 * @var array $erros
 * @var array $valores
 * @var string $csrfToken
 * @var array|null $flash
 */
$tituloPagina = 'Criar conta — FinControle';
require __DIR__ . '/../partials/topo.php';
?>
    <h1>Criar conta</h1>

    <?php if (!empty($erros['geral'])): ?>
        <div class="mensagem mensagem-erro"><?= htmlspecialchars($erros['geral']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars(Sessao::url('/cadastro')) ?>" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="campo">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($valores['email'] ?? '') ?>" required>
            <?php if (!empty($erros['email'])): ?>
                <div class="campo-erro"><?= htmlspecialchars($erros['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required>
            <?php if (!empty($erros['senha'])): ?>
                <div class="campo-erro"><?= htmlspecialchars($erros['senha']) ?></div>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="confirmacao_senha">Confirmar senha</label>
            <input type="password" id="confirmacao_senha" name="confirmacao_senha" required>
            <?php if (!empty($erros['confirmacao_senha'])): ?>
                <div class="campo-erro"><?= htmlspecialchars($erros['confirmacao_senha']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="botao-primario">Criar conta</button>
    </form>

    <div class="rodape-link">
        Já tem uma conta? <a href="<?= htmlspecialchars(Sessao::url('/login')) ?>">Entrar</a>
    </div>
<?php require __DIR__ . '/../partials/fim.php'; ?>
