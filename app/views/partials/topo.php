<?php
/**
 * @var string $tituloPagina
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'FinControle') ?></title>
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
<div class="cartao-autenticacao">
    <?php if (!empty($flash)): ?>
        <div class="mensagem mensagem-<?= htmlspecialchars($flash['tipo']) ?>">
            <?= htmlspecialchars($flash['mensagem']) ?>
        </div>
    <?php endif; ?>
