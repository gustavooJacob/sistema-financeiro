<?php
/**
 * Serviço de cálculo dos indicadores do Painel Financeiro (FSD, Seção 14,
 * "Cálculo dos Indicadores do Painel Financeiro"): saldo realizado, saldo
 * previsto, totais de receitas/despesas e gráfico de gastos por categoria,
 * todos restritos ao mês corrente (calendário civil) e ao usuário autenticado.
 * As duas listas do painel (últimos lançamentos e próximos pendentes)
 * também refletem apenas o mês corrente, conforme FSD Seção 12
 * ("ele sempre reflete o mês corrente") e Seção 13 (fluxo do painel).
 */

declare(strict_types=1);

class PainelFinanceiro
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function obterResumoMensal(int $usuarioId): array
    {
        $mesInicio = date('Y-m-01');
        $mesFim = date('Y-m-t');
        $hoje = date('Y-m-d');

        $concluidos = $this->buscarPorCampoData($usuarioId, 'concluido', 'data_efetiva', $mesInicio, $mesFim);
        $pendentes = $this->buscarPorCampoData($usuarioId, 'pendente', 'data_prevista', $mesInicio, $mesFim);

        $saldoRealizado = $this->somarSaldo($concluidos);
        $saldoPrevisto = $this->somarSaldo(array_merge($concluidos, $pendentes));
        $totalReceitas = $this->somarPorTipo(array_merge($concluidos, $pendentes), 'receita');
        $totalDespesas = $this->somarPorTipo(array_merge($concluidos, $pendentes), 'despesa');
        $graficoCategorias = $this->agruparDespesasPorCategoria(array_merge($concluidos, $pendentes));

        $ultimosLancamentos = $this->buscarUltimosLancamentos($usuarioId, $mesInicio, $mesFim);
        $proximosPendentes = $this->buscarProximosPendentes($usuarioId, $mesInicio, $mesFim);

        $vazio = empty($concluidos) && empty($pendentes) && empty($ultimosLancamentos);

        return [
            'mes_referencia' => $this->formatarMesReferencia(),
            'saldo_realizado' => $saldoRealizado,
            'saldo_previsto' => $saldoPrevisto,
            'total_receitas' => $totalReceitas,
            'total_despesas' => $totalDespesas,
            'grafico_categorias' => $graficoCategorias,
            'ultimos_lancamentos' => $ultimosLancamentos,
            'proximos_pendentes' => $proximosPendentes,
            'hoje' => $hoje,
            'vazio' => $vazio,
        ];
    }

    private function buscarPorCampoData(
        int $usuarioId,
        string $status,
        string $campoData,
        string $mesInicio,
        string $mesFim
    ): array {
        $stmt = $this->pdo->prepare(
            "SELECT tipo, valor, categoria_id
             FROM lancamentos
             WHERE usuario_id = :usuario_id AND excluido_em IS NULL AND status = :status
               AND {$campoData} BETWEEN :mes_inicio AND :mes_fim"
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'status' => $status,
            'mes_inicio' => $mesInicio,
            'mes_fim' => $mesFim,
        ]);

        return $stmt->fetchAll();
    }

    private function somarSaldo(array $lancamentos): string
    {
        $saldo = '0.00';
        foreach ($lancamentos as $lancamento) {
            $saldo = $lancamento['tipo'] === 'receita'
                ? bcadd($saldo, (string) $lancamento['valor'], 2)
                : bcsub($saldo, (string) $lancamento['valor'], 2);
        }

        return $saldo;
    }

    private function somarPorTipo(array $lancamentos, string $tipo): string
    {
        $total = '0.00';
        foreach ($lancamentos as $lancamento) {
            if ($lancamento['tipo'] === $tipo) {
                $total = bcadd($total, (string) $lancamento['valor'], 2);
            }
        }

        return $total;
    }

    private function agruparDespesasPorCategoria(array $lancamentos): array
    {
        $categoriasIds = [];
        $totaisPorCategoria = [];

        foreach ($lancamentos as $lancamento) {
            if ($lancamento['tipo'] !== 'despesa') {
                continue;
            }

            $categoriaId = (int) $lancamento['categoria_id'];
            $categoriasIds[$categoriaId] = true;
            $totaisPorCategoria[$categoriaId] = bcadd(
                $totaisPorCategoria[$categoriaId] ?? '0.00',
                (string) $lancamento['valor'],
                2
            );
        }

        if (empty($categoriasIds)) {
            return [];
        }

        $nomesPorId = $this->buscarNomesCategorias(array_keys($categoriasIds));

        $grafico = [];
        foreach ($totaisPorCategoria as $categoriaId => $total) {
            $grafico[] = [
                'categoria' => $nomesPorId[$categoriaId] ?? 'Categoria removida',
                'total' => $total,
            ];
        }

        usort($grafico, static fn (array $a, array $b) => bccomp($b['total'], $a['total'], 2));

        return $grafico;
    }

    private function buscarNomesCategorias(array $ids): array
    {
        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare("SELECT id, nome FROM categorias WHERE id IN ({$marcadores})");
        $stmt->execute(array_values($ids));

        $nomes = [];
        foreach ($stmt->fetchAll() as $linha) {
            $nomes[(int) $linha['id']] = $linha['nome'];
        }

        return $nomes;
    }

    private function buscarUltimosLancamentos(int $usuarioId, string $mesInicio, string $mesFim): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT l.*, c.nome AS categoria_nome, f.nome AS forma_pagamento_nome
             FROM lancamentos l
             INNER JOIN categorias c ON c.id = l.categoria_id
             INNER JOIN formas_pagamento f ON f.id = l.forma_pagamento_id
             WHERE l.usuario_id = :usuario_id AND l.excluido_em IS NULL
               AND l.criado_em BETWEEN :mes_inicio AND :mes_fim
             ORDER BY l.criado_em DESC
             LIMIT 5'
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'mes_inicio' => $mesInicio . ' 00:00:00',
            'mes_fim' => $mesFim . ' 23:59:59',
        ]);

        return $stmt->fetchAll();
    }

    private function buscarProximosPendentes(int $usuarioId, string $mesInicio, string $mesFim): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT l.*, c.nome AS categoria_nome, f.nome AS forma_pagamento_nome
             FROM lancamentos l
             INNER JOIN categorias c ON c.id = l.categoria_id
             INNER JOIN formas_pagamento f ON f.id = l.forma_pagamento_id
             WHERE l.usuario_id = :usuario_id AND l.excluido_em IS NULL AND l.status = 'pendente'
               AND l.data_prevista BETWEEN :mes_inicio AND :mes_fim
             ORDER BY l.data_prevista ASC
             LIMIT 5"
        );
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'mes_inicio' => $mesInicio,
            'mes_fim' => $mesFim,
        ]);

        return $stmt->fetchAll();
    }

    private function formatarMesReferencia(): string
    {
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
            7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        return $meses[(int) date('n')] . '/' . date('Y');
    }
}
