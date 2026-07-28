<?php
/**
 * @var array $erros
 * @var array $valores
 * @var bool $enviado
 * @var string $csrfToken
 * @var array|null $flash
 */
$tituloPagina = 'Recuperar senha — FinControle';
require __DIR__ . '/../partials/topo.php';
?>
    <h1>Recuperar senha</h1>

    <?php if ($enviado): ?>
        <div class="mensagem mensagem-sucesso">
            Se este e-mail estiver cadastrado, você receberá um link para redefinir sua senha em instantes.
        </div>
        <div class="rodape-link">
            <a href="/login">Voltar ao login</a>
        </div>
    <?php else: ?>
        <form method="post" action="/recuperar-senha" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="campo">
                <label for="email">E-mail cadastrado</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($valores['email'] ?? '') ?>" required>
                <?php if (!empty($erros['email'])): ?>
                    <div class="campo-erro"><?= htmlspecialchars($erros['email']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="botao-primario">Enviar link de recuperação</button>
        </form>

        <div class="rodape-link">
            <a href="/login">Voltar ao login</a>
        </div>
    <?php endif; ?>
<?php require __DIR__ . '/../partials/fim.php'; ?>
