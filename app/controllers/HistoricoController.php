<?php
/**
 * Controller da tela de Histórico de Alterações (FSD, Seção 6, Módulo 5):
 * consulta somente leitura, com filtros por período, categoria e forma de
 * pagamento, e paginação de 20 registros por página (FSD, Seção 12).
 */

declare(strict_types=1);

class HistoricoController
{
    private const POR_PAGINA = 20;

    private const ROTULOS_ENTIDADE = [
        'lancamento' => 'Lançamento',
        'categoria' => 'Categoria',
        'forma_pagamento' => 'Forma de pagamento',
    ];

    private const ROTULOS_ACAO = [
        'criacao' => 'Criação',
        'edicao' => 'Edição',
        'exclusao' => 'Exclusão',
    ];

    private const ROTULOS_CAMPO = [
        'tipo' => 'Tipo',
        'valor' => 'Valor',
        'categoria_id' => 'Categoria',
        'forma_pagamento_id' => 'Forma de pagamento',
        'descricao' => 'Descrição',
        'data_prevista' => 'Data prevista',
        'data_efetiva' => 'Data efetiva',
        'status' => 'Status',
    ];

    private PDO $pdo;
    private ItemClassificacao $categoriaModel;
    private ItemClassificacao $formaPagamentoModel;

    /** @var array<string, string|null> cache de nomes já resolvidos, para não repetir consultas na mesma página */
    private array $cacheNomes = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->categoriaModel = new ItemClassificacao($pdo, 'categorias');
        $this->formaPagamentoModel = new ItemClassificacao($pdo, 'formas_pagamento');
    }

    public function listar(): void
    {
        Sessao::exigirAutenticacao();

        $usuarioId = (int) Sessao::usuarioId();

        $filtros = [
            'data_inicio' => trim((string) ($_GET['data_inicio'] ?? '')),
            'data_fim' => trim((string) ($_GET['data_fim'] ?? '')),
            'categoria_id' => (int) ($_GET['categoria_id'] ?? 0),
            'forma_pagamento_id' => (int) ($_GET['forma_pagamento_id'] ?? 0),
        ];
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));

        $resultado = Historico::listar($this->pdo, $usuarioId, $filtros, $pagina, self::POR_PAGINA);
        $totalPaginas = (int) max(1, ceil($resultado['total'] / self::POR_PAGINA));

        $registros = array_map(fn (array $registro) => $this->prepararExibicao($registro), $resultado['itens']);

        $tituloPagina = 'Histórico de Alterações — FinControle';
        $csrfToken = Sessao::gerarTokenCsrf();
        $flash = Sessao::consumirFlash();
        $categorias = $this->categoriaModel->listarAtivos($usuarioId);
        $formasPagamento = $this->formaPagamentoModel->listarAtivos($usuarioId);

        require __DIR__ . '/../views/historico/index.php';
    }

    /**
     * Traduz os códigos internos (entidade/ação/campo) para rótulos legíveis
     * e resolve o nome de categoria/forma de pagamento quando o campo
     * alterado referenciar uma delas (mesmo que já tenham sido excluídas).
     */
    private function prepararExibicao(array $registro): array
    {
        $entidadeTipo = (string) $registro['entidade_tipo'];
        $acao = (string) $registro['acao'];
        $campo = $registro['campo_alterado'];

        $registro['entidade_rotulo'] = self::ROTULOS_ENTIDADE[$entidadeTipo] ?? $entidadeTipo;
        $registro['acao_rotulo'] = self::ROTULOS_ACAO[$acao] ?? $acao;
        $registro['campo_rotulo'] = $campo !== null ? (self::ROTULOS_CAMPO[$campo] ?? $campo) : null;

        if ($campo === 'categoria_id') {
            $registro['valor_anterior_exibicao'] = $this->resolverNome($this->categoriaModel, $registro['valor_anterior']);
            $registro['valor_novo_exibicao'] = $this->resolverNome($this->categoriaModel, $registro['valor_novo']);
        } elseif ($campo === 'forma_pagamento_id') {
            $registro['valor_anterior_exibicao'] = $this->resolverNome($this->formaPagamentoModel, $registro['valor_anterior']);
            $registro['valor_novo_exibicao'] = $this->resolverNome($this->formaPagamentoModel, $registro['valor_novo']);
        } else {
            $registro['valor_anterior_exibicao'] = $registro['valor_anterior'];
            $registro['valor_novo_exibicao'] = $registro['valor_novo'];
        }

        return $registro;
    }

    private function resolverNome(ItemClassificacao $model, ?string $id): ?string
    {
        if ($id === null || $id === '') {
            return null;
        }

        $chave = spl_object_id($model) . ':' . $id;
        if (array_key_exists($chave, $this->cacheNomes)) {
            return $this->cacheNomes[$chave];
        }

        $item = $model->buscarPorIdIncluindoExcluidos((int) $id);
        $nome = $item['nome'] ?? null;
        $this->cacheNomes[$chave] = $nome;

        return $nome;
    }
}
