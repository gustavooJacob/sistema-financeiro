<?php
/**
 * Placeholder do Painel Financeiro — apenas para validar a rota protegida
 * criada na Fase 3 (Autenticação). O painel real (saldo, gráfico, listas)
 * é implementado na Fase 7 (ver docs/PLANO.md).
 *
 * @var string|null $emailUsuario
 * @var array|null $flash
 * @var string $csrfToken
 */
$tituloPagina = 'Painel — FinControle';
require __DIR__ . '/../partials/topo.php';
?>
    <h1>Bem-vindo(a)</h1>
    <p style="color: var(--cor-texto-secundario); margin-bottom: 24px;">
        Login realizado com sucesso como <strong><?= htmlspecialchars($emailUsuario ?? '') ?></strong>.<br>
        O painel financeiro completo será implementado na Fase 7.
    </p>
    <form method="post" action="<?= htmlspecialchars(Sessao::url('/logout')) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <button type="submit" class="botao-primario">Sair</button>
    </form>

    <div class="rodape-link">
        <a href="<?= htmlspecialchars(Sessao::url('/conta')) ?>">Minha conta</a>
    </div>
<?php require __DIR__ . '/../partials/fim.php'; ?>
