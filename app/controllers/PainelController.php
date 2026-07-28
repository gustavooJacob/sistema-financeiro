<?php
/**
 * Controller do Painel Financeiro (FSD, Seção 12, Módulo 4): tela inicial
 * após login, com saldo realizado/previsto, totais do mês, gráfico de
 * gastos por categoria e listas de lançamentos recentes/pendentes.
 */

declare(strict_types=1);

class PainelController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        Sessao::exigirAutenticacao();

        $flash = Sessao::consumirFlash();
        $emailUsuario = Sessao::usuarioEmail();
        $csrfToken = Sessao::gerarTokenCsrf();

        $servico = new PainelFinanceiro($this->pdo);
        $resumo = $servico->obterResumoMensal((int) Sessao::usuarioId());

        require __DIR__ . '/../views/painel/index.php';
    }
}
