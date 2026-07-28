<?php
/**
 * @var bool $tokenValido
 * @var array $erros
 * @var string $token
 * @var string $csrfToken
 * @var array|null $flash
 */
$tituloPagina = 'Redefinir senha — FinControle';
require __DIR__ . '/../partials/topo.php';
?>
    <h1>Redefinir senha</h1>

    <?php if (!$tokenValido): ?>
        <div class="mensagem mensagem-erro">
            Este link de redefinição de senha é inválido ou já expirou. Solicite um novo link.
        </div>
        <div class="rodape-link">
            <a href="<?= htmlspecialchars(Sessao::url('/recuperar-senha')) ?>">Solicitar novo link</a>
        </div>
    <?php else: ?>
        <form method="post" action="<?= htmlspecialchars(Sessao::url('/redefinir-senha')) ?>" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="campo">
                <label for="senha">Nova senha</label>
                <input type="password" id="senha" name="senha" required>
                <?php if (!empty($erros['senha'])): ?>
                    <div class="campo-erro"><?= htmlspecialchars($erros['senha']) ?></div>
                <?php endif; ?>
            </div>

            <div class="campo">
                <label for="confirmacao_senha">Confirmar nova senha</label>
                <input type="password" id="confirmacao_senha" name="confirmacao_senha" required>
                <?php if (!empty($erros['confirmacao_senha'])): ?>
                    <div class="campo-erro"><?= htmlspecialchars($erros['confirmacao_senha']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="botao-primario">Redefinir senha</button>
        </form>
    <?php endif; ?>
<?php require __DIR__ . '/../partials/fim.php'; ?>
