<?php
/**
 * @var string $tituloPagina
 * @var string|null $classeCartaoExtra
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'FinControle') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars(Sessao::url('/assets/favicon.svg')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(Sessao::url('/assets/css/app.css')) ?>">
</head>
<body class="tela-auth">
<div class="cartao-autenticacao <?= htmlspecialchars($classeCartaoExtra ?? '') ?>">
    <?php if (!empty($flash)): ?>
        <div class="mensagem mensagem-<?= htmlspecialchars($flash['tipo']) ?>">
            <?= htmlspecialchars($flash['mensagem']) ?>
        </div>
    <?php endif; ?>
