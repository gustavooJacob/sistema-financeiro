<?php
/**
 * Controller de Lançamentos Financeiros (FSD, Seção 6, Módulo 3): criação,
 * edição, atualização de status ("concluído") e exclusão (soft delete) de
 * receitas/despesas do usuário autenticado, com listagem paginada e
 * filtrada (FSD, Seção 12) e geração de histórico por campo alterado
 * (FSD, Seção 13/17).
 */

declare(strict_types=1);

class LancamentoController
{
    private const POR_PAGINA = 20;

    private PDO $pdo;
    private Lancamento $model;
    private ItemClassificacao $categoriaModel;
    private ItemClassificacao $formaPagamentoModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->model = new Lancamento($pdo);
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

        $resultado = $this->model->listar($usuarioId, $filtros, $pagina, self::POR_PAGINA);
        $totalPaginas = (int) max(1, ceil($resultado['total'] / self::POR_PAGINA));

        $tituloPagina = 'Lançamentos — FinControle';
        $csrfToken = Sessao::gerarTokenCsrf();
        $flash = Sessao::consumirFlash();
        $categorias = $this->categoriaModel->listarAtivos($usuarioId);
        $formasPagamento = $this->formaPagamentoModel->listarAtivos($usuarioId);
        $lancamentos = $resultado['itens'];
        $hoje = date('Y-m-d');

        require __DIR__ . '/../views/lancamentos/index.php';
    }

    public function exibirCriar(): void
    {
        Sessao::exigirAutenticacao();

        $this->renderizarFormulario([
            'modo' => 'criar',
            'erros' => [],
            'valores' => ['tipo' => 'despesa', 'status' => 'pendente', 'valor' => '', 'categoria_id' => '', 'forma_pagamento_id' => '', 'descricao' => '', 'data_prevista' => '', 'data_efetiva' => ''],
        ]);
    }

    public function processarCriar(): void
    {
        Sessao::exigirAutenticacao();

        if (!Sessao::validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $this->falhaCsrf();
        }

        $usuarioId = (int) Sessao::usuarioId();
        $dados = $this->lerDadosFormulario();
        $erros = $this->validar($usuarioId, $dados);

        if (!empty($erros)) {
            $this->renderizarFormulario(['modo' => 'criar', 'erros' => $erros, 'valores' => $dados]);
            return;
        }

        if ($dados['status'] === 'pendente') {
            $dados['data_efetiva'] = '';
        }

        $id = $this->model->criar($usuarioId, $dados);
        Historico::registrar($this->pdo, $usuarioId, 'lancamento', $id, 'criacao', null, null, $this->descrever($dados));

        Sessao::definirFlash('sucesso', 'Lançamento criado com sucesso.');
        Sessao::redirecionar('/lancamentos');
    }

    public function exibirEditar(): void
    {
        Sessao::exigirAutenticacao();

        $usuarioId = (int) Sessao::usuarioId();
        $id = (int) ($_GET['id'] ?? 0);
        $lancamento = $this->buscarComPermissao($id, $usuarioId, 'editar');

        $this->renderizarFormulario(['modo' => 'editar', 'erros' => [], 'valores' => $lancamento]);
    }

    public function processarEditar(): void
    {
        Sessao::exigirAutenticacao();

        if (!Sessao::validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $this->falhaCsrf();
        }

        $usuarioId = (int) Sessao::usuarioId();
        $id = (int) ($_POST['id'] ?? 0);
        $lancamentoAtual = $this->buscarComPermissao($id, $usuarioId, 'editar');

        $dados = $this->lerDadosFormulario();
        $erros = $this->validar($usuarioId, $dados);

        if (!empty($erros)) {
            $valores = $dados;
            $valores['id'] = $id;
            $this->renderizarFormulario(['modo' => 'editar', 'erros' => $erros, 'valores' => $valores]);
            return;
        }

        if ($dados['status'] === 'pendente') {
            $dados['data_efetiva'] = '';
        }

        $camposAlterados = $this->model->atualizar($id, $usuarioId, $dados, $lancamentoAtual);

        foreach ($camposAlterados as $campo => $valores) {
            Historico::registrar(
                $this->pdo,
                $usuarioId,
                'lancamento',
                $id,
                'edicao',
                $campo,
                $valores['anterior'] === null ? null : (string) $valores['anterior'],
                $valores['novo'] === null ? null : (string) $valores['novo']
            );
        }

        Sessao::definirFlash('sucesso', 'Lançamento atualizado com sucesso.');
        Sessao::redirecionar('/lancamentos');
    }

    public function processarConcluir(): void
    {
        Sessao::exigirAutenticacao();

        if (!Sessao::validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $this->falhaCsrf();
        }

        $usuarioId = (int) Sessao::usuarioId();
        $id = (int) ($_POST['id'] ?? 0);
        $dataEfetiva = trim((string) ($_POST['data_efetiva'] ?? ''));

        $this->buscarComPermissao($id, $usuarioId, 'concluir');

        if ($dataEfetiva === '' || !$this->dataValida($dataEfetiva)) {
            Sessao::definirFlash('erro', 'Informe uma data efetiva válida para concluir o lançamento.');
            Sessao::redirecionar('/lancamentos');
        }

        $camposAlterados = $this->model->marcarConcluido($id, $usuarioId, $dataEfetiva);

        foreach ($camposAlterados as $campo => $valores) {
            Historico::registrar(
                $this->pdo,
                $usuarioId,
                'lancamento',
                $id,
                'edicao',
                $campo,
                $valores['anterior'] === null ? null : (string) $valores['anterior'],
                $valores['novo'] === null ? null : (string) $valores['novo']
            );
        }

        Sessao::definirFlash('sucesso', 'Lançamento marcado como concluído.');
        Sessao::redirecionar('/lancamentos');
    }

    public function processarExcluir(): void
    {
        Sessao::exigirAutenticacao();

        if (!Sessao::validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $this->falhaCsrf();
        }

        $usuarioId = (int) Sessao::usuarioId();
        $id = (int) ($_POST['id'] ?? 0);
        $lancamento = $this->buscarComPermissao($id, $usuarioId, 'excluir');

        $excluido = $this->model->excluir($id, $usuarioId);

        if ($excluido) {
            Historico::registrar($this->pdo, $usuarioId, 'lancamento', $id, 'exclusao', null, $this->descrever($lancamento), null);
        }

        Sessao::definirFlash('sucesso', 'Lançamento excluído com sucesso.');
        Sessao::redirecionar('/lancamentos');
    }

    /**
     * Busca o lançamento validando posse; caso não exista ou pertença a
     * outro usuário, registra acesso negado e redireciona com mensagem
     * genérica (FSD, Seção 16), nunca revelando detalhes do registro alheio.
     */
    private function buscarComPermissao(int $id, int $usuarioId, string $acao): array
    {
        $lancamento = $this->model->buscarPorId($id, $usuarioId);

        if ($lancamento === null) {
            LogSeguranca::registrar('acesso_negado', $usuarioId, null, "tentativa de {$acao} lancamento id={$id}");
            Sessao::definirFlash('erro', 'Não foi possível localizar o lançamento informado.');
            Sessao::redirecionar('/lancamentos');
        }

        return $lancamento;
    }

    private function lerDadosFormulario(): array
    {
        return [
            'tipo' => (string) ($_POST['tipo'] ?? ''),
            'valor' => trim((string) ($_POST['valor'] ?? '')),
            'categoria_id' => (int) ($_POST['categoria_id'] ?? 0),
            'forma_pagamento_id' => (int) ($_POST['forma_pagamento_id'] ?? 0),
            'descricao' => trim((string) ($_POST['descricao'] ?? '')),
            'data_prevista' => trim((string) ($_POST['data_prevista'] ?? '')),
            'status' => (string) ($_POST['status'] ?? ''),
            'data_efetiva' => trim((string) ($_POST['data_efetiva'] ?? '')),
        ];
    }

    /**
     * Validações da Seção 14 do FSD: valor > 0, descrição ≤ 300 caracteres,
     * campos obrigatórios, data efetiva obrigatória quando concluído e
     * categoria/forma de pagamento acessíveis ao usuário autenticado.
     */
    private function validar(int $usuarioId, array $dados): array
    {
        $erros = [];

        if (!in_array($dados['tipo'], ['receita', 'despesa'], true)) {
            $erros['tipo'] = 'Selecione o tipo do lançamento.';
        }

        if ($dados['valor'] === '' || !is_numeric($dados['valor']) || (float) $dados['valor'] <= 0) {
            $erros['valor'] = 'Informe um valor maior que zero.';
        }

        if ($dados['descricao'] !== '' && mb_strlen($dados['descricao']) > 300) {
            $erros['descricao'] = 'A descrição deve ter no máximo 300 caracteres.';
        }

        $categoria = $dados['categoria_id'] > 0 ? $this->categoriaModel->buscarPorId($dados['categoria_id']) : null;
        if (!$this->itemAcessivel($categoria, $usuarioId)) {
            $erros['categoria_id'] = 'Selecione uma categoria válida.';
        }

        $formaPagamento = $dados['forma_pagamento_id'] > 0 ? $this->formaPagamentoModel->buscarPorId($dados['forma_pagamento_id']) : null;
        if (!$this->itemAcessivel($formaPagamento, $usuarioId)) {
            $erros['forma_pagamento_id'] = 'Selecione uma forma de pagamento válida.';
        }

        if ($dados['data_prevista'] === '' || !$this->dataValida($dados['data_prevista'])) {
            $erros['data_prevista'] = 'Informe uma data prevista válida.';
        }

        if (!in_array($dados['status'], ['pendente', 'concluido'], true)) {
            $erros['status'] = 'Selecione o status do lançamento.';
        } elseif ($dados['status'] === 'concluido' && ($dados['data_efetiva'] === '' || !$this->dataValida($dados['data_efetiva']))) {
            $erros['data_efetiva'] = 'Informe a data efetiva para um lançamento concluído.';
        }

        return $erros;
    }

    private function itemAcessivel(?array $item, int $usuarioId): bool
    {
        return $item !== null && ($item['usuario_id'] === null || (int) $item['usuario_id'] === $usuarioId);
    }

    private function dataValida(string $data): bool
    {
        $partes = DateTime::createFromFormat('Y-m-d', $data);

        return $partes !== false && $partes->format('Y-m-d') === $data;
    }

    private function descrever(array $lancamento): string
    {
        $tipo = $lancamento['tipo'] ?? '';
        $valor = $lancamento['valor'] ?? '0';
        $dataPrevista = $lancamento['data_prevista'] ?? '';

        return ucfirst((string) $tipo) . " de R$ {$valor} previsto para {$dataPrevista}";
    }

    private function falhaCsrf(): never
    {
        LogSeguranca::registrar('acesso_negado', Sessao::usuarioId(), null, 'token CSRF inválido');
        http_response_code(400);
        $mensagem = 'Não foi possível processar sua solicitação. Recarregue a página e tente novamente.';
        require __DIR__ . '/../views/erros/erro_generico.php';
        exit;
    }

    private function renderizarFormulario(array $dados): void
    {
        extract($dados, EXTR_SKIP);
        $tituloPagina = ($modo === 'criar' ? 'Novo Lançamento' : 'Editar Lançamento') . ' — FinControle';
        $csrfToken = Sessao::gerarTokenCsrf();
        $flash = Sessao::consumirFlash();
        $usuarioId = (int) Sessao::usuarioId();
        $categorias = $this->categoriaModel->listarAtivos($usuarioId);
        $formasPagamento = $this->formaPagamentoModel->listarAtivos($usuarioId);
        require __DIR__ . '/../views/lancamentos/formulario.php';
    }
}
