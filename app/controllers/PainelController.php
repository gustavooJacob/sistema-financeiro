<?php
/**
 * Controller do Painel Financeiro.
 *
 * Nesta fase (Fase 3 — Autenticação), existe apenas como rota protegida
 * de destino após o login, para validar a exigência de sessão ativa.
 * Os indicadores financeiros reais serão implementados na Fase 7
 * (ver docs/PLANO.md), conforme FSD Seção 12 (Módulo 4).
 */

declare(strict_types=1);

class PainelController
{
    public function index(): void
    {
        Sessao::exigirAutenticacao();

        $flash = Sessao::consumirFlash();
        $emailUsuario = Sessao::usuarioEmail();
        $csrfToken = Sessao::gerarTokenCsrf();

        require __DIR__ . '/../views/painel/index.php';
    }
}
