<?php
/**
 * Layout compartilhado das telas internas (autenticadas): sidebar de
 * navegação por ícones (FSD Seção 16; DESIGN.md Seção 8) + área de
 * conteúdo. Views que usam este parcial devem, em seguida, requerer
 * app_fim.php ao final.
 *
 * @var string $tituloPagina
 * @var string $paginaAtiva 'painel'|'lancamentos'|'categorias'|'formas_pagamento'|'historico'|'conta'
 * @var string|null $classeCartaoExtra
 * @var array|null $flash
 * @var string $csrfToken
 */

$itensMenu = [
    'painel' => ['rota' => '/painel', 'icone' => 'layout-dashboard', 'rotulo' => 'Painel'],
    'lancamentos' => ['rota' => '/lancamentos', 'icone' => 'receipt', 'rotulo' => 'Lançamentos'],
    'categorias' => ['rota' => '/categorias', 'icone' => 'tag', 'rotulo' => 'Categorias'],
    'formas_pagamento' => ['rota' => '/formas-pagamento', 'icone' => 'credit-card', 'rotulo' => 'Formas de pagamento'],
    'historico' => ['rota' => '/historico', 'icone' => 'history', 'rotulo' => 'Histórico'],
    'conta' => ['rota' => '/conta', 'icone' => 'user', 'rotulo' => 'Minha conta'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'FinControle') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(Sessao::url('/assets/vendor/bootstrap/css/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(Sessao::url('/assets/css/app.css')) ?>">
</head>
<body class="tela-app">
<div class="app-shell">
    <nav class="sidebar">
        <ul class="sidebar-menu">
            <?php foreach ($itensMenu as $chave => $item): ?>
                <li>
                    <a href="<?= htmlspecialchars(Sessao::url($item['rota'])) ?>"
                       class="sidebar-item <?= $paginaAtiva === $chave ? 'sidebar-item-ativo' : '' ?>"
                       title="<?= htmlspecialchars($item['rotulo']) ?>">
                        <?= Icone::render($item['icone'], 20) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <form method="post" action="<?= htmlspecialchars(Sessao::url('/logout')) ?>" class="sidebar-rodape">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <button type="submit" class="sidebar-item" title="Sair">
                <?= Icone::render('log-out', 20) ?>
            </button>
        </form>
    </nav>

    <main class="conteudo-principal">
        <?php if (!empty($flash)): ?>
            <div class="mensagem mensagem-<?= htmlspecialchars($flash['tipo']) ?>">
                <?= htmlspecialchars($flash['mensagem']) ?>
            </div>
        <?php endif; ?>

        <div class="cartao <?= htmlspecialchars($classeCartaoExtra ?? '') ?>">
