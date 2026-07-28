<?php
/**
 * Painel Financeiro (Dashboard) — tela inicial após login (FSD, Seção 12,
 * Módulo 4). Indicadores calculados por PainelFinanceiro::obterResumoMensal(),
 * seguindo exatamente a Seção 14 ("Cálculo dos Indicadores do Painel
 * Financeiro"). Gráfico de gastos por categoria renderizado com Chart.js
 * hospedado localmente (sem CDN — FSD Seção 3).
 *
 * @var string|null $emailUsuario
 * @var array|null $flash
 * @var string $csrfToken
 * @var array $resumo
 */
$classeCartaoExtra = 'cartao-painel';
$paginaAtiva = 'painel';
require __DIR__ . '/../partials/app_topo.php';

$formatarMoeda = static fn (string $valor): string => 'R$ ' . number_format((float) $valor, 2, ',', '.');
?>
    <div class="painel-topo">
        <div>
            <h1>Painel Financeiro</h1>
            <p class="secao-descricao">
                Olá, <strong><?= htmlspecialchars($emailUsuario ?? '') ?></strong> — resumo de <?= htmlspecialchars($resumo['mes_referencia']) ?>.
            </p>
        </div>
        <a href="<?= htmlspecialchars(Sessao::url('/lancamentos/novo')) ?>" class="botao-primario botao-auto">Novo lançamento</a>
    </div>

    <?php if ($resumo['vazio']): ?>
        <div class="painel-vazio">
            <p class="secao-descricao">Nenhum lançamento registrado em <?= htmlspecialchars($resumo['mes_referencia']) ?> ainda.</p>
            <a href="<?= htmlspecialchars(Sessao::url('/lancamentos/novo')) ?>" class="botao-primario botao-auto">Criar o primeiro lançamento</a>
        </div>
    <?php else: ?>
        <div class="grade-indicadores">
            <div class="cartao-indicador">
                <span class="indicador-rotulo">Saldo realizado</span>
                <span class="indicador-valor <?= bccomp($resumo['saldo_realizado'], '0', 2) < 0 ? 'indicador-negativo' : 'indicador-positivo' ?>">
                    <?= htmlspecialchars($formatarMoeda($resumo['saldo_realizado'])) ?>
                </span>
            </div>
            <div class="cartao-indicador">
                <span class="indicador-rotulo">Saldo previsto</span>
                <span class="indicador-valor <?= bccomp($resumo['saldo_previsto'], '0', 2) < 0 ? 'indicador-negativo' : 'indicador-positivo' ?>">
                    <?= htmlspecialchars($formatarMoeda($resumo['saldo_previsto'])) ?>
                </span>
            </div>
            <div class="cartao-indicador">
                <span class="indicador-rotulo">Receitas do mês</span>
                <span class="indicador-valor indicador-positivo"><?= htmlspecialchars($formatarMoeda($resumo['total_receitas'])) ?></span>
            </div>
            <div class="cartao-indicador">
                <span class="indicador-rotulo">Despesas do mês</span>
                <span class="indicador-valor indicador-negativo"><?= htmlspecialchars($formatarMoeda($resumo['total_despesas'])) ?></span>
            </div>
        </div>

        <div class="grade-painel">
            <div class="cartao-secao">
                <h2 class="secao-titulo">Gastos por categoria</h2>
                <?php if (empty($resumo['grafico_categorias'])): ?>
                    <p class="secao-descricao">Nenhuma despesa registrada neste mês.</p>
                <?php else: ?>
                    <div class="grafico-container">
                        <canvas id="grafico-categorias"></canvas>
                    </div>
                <?php endif; ?>
            </div>

            <div class="cartao-secao">
                <h2 class="secao-titulo">Últimos lançamentos</h2>
                <?php if (empty($resumo['ultimos_lancamentos'])): ?>
                    <p class="secao-descricao">Nenhum lançamento criado neste mês.</p>
                <?php else: ?>
                    <ul class="lista-painel">
                        <?php foreach ($resumo['ultimos_lancamentos'] as $lancamento): ?>
                            <li class="item-painel">
                                <div class="item-painel-info">
                                    <span class="badge <?= $lancamento['tipo'] === 'receita' ? 'badge-sucesso' : 'badge-erro' ?>">
                                        <?= $lancamento['tipo'] === 'receita' ? 'Receita' : 'Despesa' ?>
                                    </span>
                                    <span class="item-painel-categoria"><?= htmlspecialchars($lancamento['categoria_nome']) ?></span>
                                </div>
                                <span class="item-painel-valor"><?= htmlspecialchars($formatarMoeda((string) $lancamento['valor'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <a href="<?= htmlspecialchars(Sessao::url('/lancamentos')) ?>" class="botao-link">Ver todos os lançamentos</a>
            </div>

            <div class="cartao-secao">
                <h2 class="secao-titulo">Próximos pendentes</h2>
                <?php if (empty($resumo['proximos_pendentes'])): ?>
                    <p class="secao-descricao">Nenhum lançamento pendente neste mês.</p>
                <?php else: ?>
                    <ul class="lista-painel">
                        <?php foreach ($resumo['proximos_pendentes'] as $lancamento): ?>
                            <?php $atrasado = $lancamento['data_prevista'] < $resumo['hoje']; ?>
                            <li class="item-painel">
                                <div class="item-painel-info">
                                    <span class="badge <?= $atrasado ? 'badge-erro' : 'badge-neutro' ?>">
                                        <?= $atrasado ? 'Atrasado' : 'Pendente' ?>
                                    </span>
                                    <span class="item-painel-categoria"><?= htmlspecialchars($lancamento['categoria_nome']) ?></span>
                                    <span class="item-painel-data"><?= htmlspecialchars($lancamento['data_prevista']) ?></span>
                                </div>
                                <span class="item-painel-valor"><?= htmlspecialchars($formatarMoeda((string) $lancamento['valor'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <a href="<?= htmlspecialchars(Sessao::url('/lancamentos')) ?>" class="botao-link">Ver todos os lançamentos</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($resumo['grafico_categorias'])): ?>
        <script src="<?= htmlspecialchars(Sessao::url('/assets/vendor/chartjs/chart.umd.min.js')) ?>"></script>
        <script>
            const dadosGrafico = <?= json_encode(array_map(
                static fn (array $item) => ['categoria' => $item['categoria'], 'total' => (float) $item['total']],
                $resumo['grafico_categorias']
            ), JSON_UNESCAPED_UNICODE) ?>;

            new Chart(document.getElementById('grafico-categorias'), {
                type: 'doughnut',
                data: {
                    labels: dadosGrafico.map((item) => item.categoria),
                    datasets: [{
                        data: dadosGrafico.map((item) => item.total),
                        backgroundColor: [
                            '#5B5FEF', '#EB5757', '#2ECC71', '#F2994A', '#9B51E0',
                            '#56CCF2', '#F2C94C', '#BB6BD9', '#219653', '#EB5757',
                        ],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { size: 11 } } },
                    },
                },
            });
        </script>
    <?php endif; ?>
<?php require __DIR__ . '/../partials/app_fim.php'; ?>
