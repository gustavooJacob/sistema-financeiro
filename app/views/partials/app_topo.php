<?php
/**
 * Layout compartilhado das telas internas (autenticadas): sidebar de
 * navegação por ícones, retrátil via botão "hambúrguer" (expande exibindo
 * o rótulo de cada item; estado lembrado entre páginas via localStorage,
 * já que a navegação recarrega a página inteira) + área de conteúdo
 * centralizada. Views que usam este parcial devem, em seguida, requerer
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
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars(Sessao::url('/assets/favicon.svg')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(Sessao::url('/assets/vendor/bootstrap/css/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(Sessao::url('/assets/css/app.css')) ?>">
    <script>
        // Aplica o estado de sidebar expandida/recolhida antes da renderização
        // do <body>, evitando "piscar" a sidebar no estado errado a cada
        // navegação (cada rota é um recarregamento completo de página).
        if (localStorage.getItem('sidebarExpandida') === '1') {
            document.documentElement.classList.add('sidebar-expandida');
        }
    </script>
</head>
<body class="tela-app">
<div class="app-shell">
    <nav class="sidebar">
        <button type="button" class="sidebar-item sidebar-hamburguer" title="Expandir/recolher menu" aria-label="Expandir ou recolher o menu">
            <?= Icone::render('menu', 20) ?>
        </button>

        <ul class="sidebar-menu">
            <?php foreach ($itensMenu as $chave => $item): ?>
                <li>
                    <a href="<?= htmlspecialchars(Sessao::url($item['rota'])) ?>"
                       class="sidebar-item <?= $paginaAtiva === $chave ? 'sidebar-item-ativo' : '' ?>"
                       title="<?= htmlspecialchars($item['rotulo']) ?>">
                        <?= Icone::render($item['icone'], 20) ?>
                        <span class="sidebar-item-rotulo"><?= htmlspecialchars($item['rotulo']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <form method="post" action="<?= htmlspecialchars(Sessao::url('/logout')) ?>" class="sidebar-rodape">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
            <button type="submit" class="sidebar-item" title="Sair">
                <?= Icone::render('log-out', 20) ?>
                <span class="sidebar-item-rotulo">Sair</span>
            </button>
        </form>
    </nav>

    <script>
        document.querySelector('.sidebar-hamburguer').addEventListener('click', function () {
            var expandida = document.documentElement.classList.toggle('sidebar-expandida');
            localStorage.setItem('sidebarExpandida', expandida ? '1' : '0');
        });
    </script>

    <main class="conteudo-principal">
        <?php if (!empty($flash)): ?>
            <div class="mensagem mensagem-<?= htmlspecialchars($flash['tipo']) ?>">
                <?= htmlspecialchars($flash['mensagem']) ?>
            </div>
        <?php endif; ?>

        <div class="cartao <?= htmlspecialchars($classeCartaoExtra ?? '') ?>">
