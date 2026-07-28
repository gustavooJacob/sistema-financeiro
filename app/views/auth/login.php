<?php
/**
 * @var array $erros
 * @var array $valores
 * @var string $csrfToken
 * @var array|null $flash
 */
$tituloPagina = 'Entrar — FinControle';
require __DIR__ . '/../partials/topo.php';
?>
    <h1>Entrar</h1>

    <?php if (!empty($erros['geral'])): ?>
        <div class="mensagem mensagem-erro"><?= htmlspecialchars($erros['geral']) ?></div>
    <?php endif; ?>

    <form method="post" action="/login" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="campo">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($valores['email'] ?? '') ?>" required>
        </div>

        <div class="campo">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required>
        </div>

        <button type="submit" class="botao-primario">Entrar</button>
    </form>

    <div class="rodape-link">
        <a href="/recuperar-senha">Esqueci minha senha</a><br>
        Ainda não tem conta? <a href="/cadastro">Criar conta</a>
    </div>
<?php require __DIR__ . '/../partials/fim.php'; ?>
