<?php
/**
 * Página de erro genérica exibida ao usuário final. Nunca deve conter
 * mensagem técnica, stack trace ou detalhe de SQL (FSD, Seção 19/24).
 *
 * @var string $mensagem
 */
$tituloPagina = 'Erro — FinControle';
require __DIR__ . '/../partials/topo.php';
?>
    <h1>Ops, algo deu errado</h1>
    <div class="mensagem mensagem-erro">
        <?= htmlspecialchars($mensagem ?? 'Ocorreu um erro ao processar sua solicitação. Tente novamente.') ?>
    </div>
    <div class="rodape-link">
        <a href="/login">Voltar ao início</a>
    </div>
<?php require __DIR__ . '/../partials/fim.php'; ?>
