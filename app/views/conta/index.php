<?php
/**
 * @var array $erros
 * @var array $valores
 * @var string|null $emailAtual
 * @var string $csrfToken
 * @var array|null $flash
 */
$tituloPagina = 'Conta — FinControle';
$classeCartaoExtra = 'cartao-largo';
$paginaAtiva = 'conta';
require __DIR__ . '/../partials/app_topo.php';
?>
    <h1>Conta</h1>

    <?php if (!empty($erros['geral'])): ?>
        <div class="mensagem mensagem-erro"><?= htmlspecialchars($erros['geral']) ?></div>
    <?php endif; ?>

    <p class="secao-descricao">
        Logado(a) como <strong><?= htmlspecialchars($emailAtual ?? '') ?></strong>.
    </p>

    <form method="post" action="<?= htmlspecialchars(Sessao::url('/conta/editar')) ?>" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <div class="campo">
            <label for="novo_email">E-mail</label>
            <input type="email" id="novo_email" name="novo_email" value="<?= htmlspecialchars($valores['email'] ?? '') ?>" required>
            <?php if (!empty($erros['novo_email'])): ?>
                <div class="campo-erro"><?= htmlspecialchars($erros['novo_email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="nova_senha">Nova senha (opcional)</label>
            <input type="password" id="nova_senha" name="nova_senha">
            <?php if (!empty($erros['nova_senha'])): ?>
                <div class="campo-erro"><?= htmlspecialchars($erros['nova_senha']) ?></div>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="confirmacao_nova_senha">Confirmar nova senha</label>
            <input type="password" id="confirmacao_nova_senha" name="confirmacao_nova_senha">
            <?php if (!empty($erros['confirmacao_nova_senha'])): ?>
                <div class="campo-erro"><?= htmlspecialchars($erros['confirmacao_nova_senha']) ?></div>
            <?php endif; ?>
        </div>

        <div class="campo">
            <label for="senha_atual">Senha atual</label>
            <input type="password" id="senha_atual" name="senha_atual" required>
            <?php if (!empty($erros['senha_atual'])): ?>
                <div class="campo-erro"><?= htmlspecialchars($erros['senha_atual']) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="botao-primario">Salvar alterações</button>
    </form>

    <hr class="separador-secao">

    <p class="secao-titulo">Sair</p>
    <form method="post" action="<?= htmlspecialchars(Sessao::url('/logout')) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <button type="submit" class="botao-secundario">Sair da conta</button>
    </form>

    <hr class="separador-secao">

    <p class="secao-titulo">Excluir conta</p>
    <p class="secao-descricao">
        Esta ação encerra seu acesso ao sistema. Seus lançamentos, categorias e formas de pagamento
        são preservados para fins de integridade e histórico, mas você não poderá mais entrar com esta conta.
    </p>
    <form method="post" action="<?= htmlspecialchars(Sessao::url('/conta/excluir')) ?>"
          onsubmit="return confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita por você.');">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <button type="submit" class="botao-perigo">Excluir minha conta</button>
    </form>
<?php require __DIR__ . '/../partials/app_fim.php'; ?>
