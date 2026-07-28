<?php
/**
 * Controller compartilhado das telas de Categorias e Formas de Pagamento
 * (FSD, Seção 6, Módulo 2). As duas entidades têm regras e estrutura de
 * tela idênticas (FSD, Seção 12: "Idêntica em estrutura à tela de
 * Categorias"), por isso uma única classe parametrizada por instância,
 * evitando duplicar o mesmo controller duas vezes.
 */

declare(strict_types=1);

class ClassificacaoController
{
    private PDO $pdo;
    private ItemClassificacao $model;
    private string $entidadeTipo;
    private string $rotaBase;
    private string $pastaView;
    private string $tituloPagina;
    private string $rotuloSingular;
    private string $rotuloPlural;

    public function __construct(
        PDO $pdo,
        string $tabela,
        string $entidadeTipo,
        string $rotaBase,
        string $pastaView,
        string $tituloPagina,
        string $rotuloSingular,
        string $rotuloPlural
    ) {
        $this->pdo = $pdo;
        $this->model = new ItemClassificacao($pdo, $tabela);
        $this->entidadeTipo = $entidadeTipo;
        $this->rotaBase = $rotaBase;
        $this->pastaView = $pastaView;
        $this->tituloPagina = $tituloPagina;
        $this->rotuloSingular = $rotuloSingular;
        $this->rotuloPlural = $rotuloPlural;
    }

    public function listar(): void
    {
        Sessao::exigirAutenticacao();

        $usuarioId = (int) Sessao::usuarioId();
        $itens = $this->model->listarAtivos($usuarioId);

        $this->renderizar(['erros' => [], 'itens' => $itens, 'valores' => ['nome' => '']]);
    }

    public function processarCriar(): void
    {
        Sessao::exigirAutenticacao();

        if (!Sessao::validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $this->falhaCsrf();
        }

        $usuarioId = (int) Sessao::usuarioId();
        $nome = trim((string) ($_POST['nome'] ?? ''));

        $erros = [];

        if ($nome === '') {
            $erros['nome'] = "Informe o nome da {$this->rotuloSingular}.";
        } elseif (mb_strlen($nome) > 100) {
            $erros['nome'] = 'O nome deve ter no máximo 100 caracteres.';
        } elseif ($this->model->nomeDuplicado($usuarioId, $nome)) {
            $erros['nome'] = "Já existe uma {$this->rotuloSingular} com este nome.";
        }

        if (!empty($erros)) {
            $itens = $this->model->listarAtivos($usuarioId);
            $this->renderizar(['erros' => $erros, 'itens' => $itens, 'valores' => ['nome' => $nome]]);
            return;
        }

        $this->model->criar($usuarioId, $nome);

        Sessao::definirFlash('sucesso', ucfirst($this->rotuloSingular) . ' criada com sucesso.');
        Sessao::redirecionar($this->rotaBase);
    }

    public function processarExcluir(): void
    {
        Sessao::exigirAutenticacao();

        if (!Sessao::validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $this->falhaCsrf();
        }

        $usuarioId = (int) Sessao::usuarioId();
        $id = (int) ($_POST['id'] ?? 0);
        $item = $this->model->buscarPorId($id);

        if ($item !== null && (int) $item['padrao'] === 1) {
            Sessao::definirFlash('erro', 'Itens padrão do sistema não podem ser excluídos.');
            Sessao::redirecionar($this->rotaBase);
        }

        if ($item === null || (int) $item['usuario_id'] !== $usuarioId) {
            LogSeguranca::registrar('acesso_negado', $usuarioId, null, "tentativa de excluir {$this->entidadeTipo} id={$id}");
            Sessao::definirFlash('erro', 'Não foi possível localizar o item informado.');
            Sessao::redirecionar($this->rotaBase);
        }

        $this->pdo->beginTransaction();
        try {
            $excluido = $this->model->excluirProprio($id, $usuarioId);

            if ($excluido) {
                Historico::registrar($this->pdo, $usuarioId, $this->entidadeTipo, $id, 'exclusao', null, $item['nome'], null);
            }

            $this->pdo->commit();
        } catch (Throwable $erro) {
            $this->pdo->rollBack();
            throw $erro;
        }

        Sessao::definirFlash('sucesso', ucfirst($this->rotuloSingular) . ' excluída com sucesso.');
        Sessao::redirecionar($this->rotaBase);
    }

    private function falhaCsrf(): never
    {
        LogSeguranca::registrar('acesso_negado', Sessao::usuarioId(), null, 'token CSRF inválido');
        http_response_code(400);
        $mensagem = 'Não foi possível processar sua solicitação. Recarregue a página e tente novamente.';
        require __DIR__ . '/../views/erros/erro_generico.php';
        exit;
    }

    private function renderizar(array $dados): void
    {
        extract($dados, EXTR_SKIP);
        $tituloPagina = $this->tituloPagina;
        $rotuloSingular = $this->rotuloSingular;
        $rotuloPlural = $this->rotuloPlural;
        $rotaBase = $this->rotaBase;
        $csrfToken = Sessao::gerarTokenCsrf();
        $flash = Sessao::consumirFlash();
        require __DIR__ . "/../views/{$this->pastaView}/index.php";
    }
}
