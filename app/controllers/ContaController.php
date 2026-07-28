<?php
/**
 * Controller de Conta/Perfil (Módulo 1 do FSD, Seção 6): edição de
 * e-mail/senha mediante confirmação da senha atual e exclusão (soft
 * delete) da própria conta.
 */

declare(strict_types=1);

class ContaController
{
    private PDO $pdo;
    private array $config;
    private Usuario $usuarioModel;

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
        $this->usuarioModel = new Usuario($pdo);
    }

    public function exibir(): void
    {
        Sessao::exigirAutenticacao();

        $this->renderizar(['erros' => [], 'valores' => ['email' => Sessao::usuarioEmail()]]);
    }

    public function processarEditar(): void
    {
        Sessao::exigirAutenticacao();

        if (!Sessao::validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $this->falhaCsrf();
        }

        $usuarioId = (int) Sessao::usuarioId();
        $usuario = $this->usuarioModel->buscarPorId($usuarioId);

        if ($usuario === null) {
            Sessao::encerrar();
            Sessao::redirecionar('/login');
        }

        $senhaAtual = (string) ($_POST['senha_atual'] ?? '');
        $novoEmail = trim((string) ($_POST['novo_email'] ?? ''));
        $novaSenha = (string) ($_POST['nova_senha'] ?? '');
        $confirmacaoNovaSenha = (string) ($_POST['confirmacao_nova_senha'] ?? '');
        $minimoCaracteres = (int) ($this->config['seguranca']['senha_minimo_caracteres'] ?? 8);

        $erros = [];

        if ($senhaAtual === '' || !password_verify($senhaAtual, $usuario['senha_hash'])) {
            $erros['senha_atual'] = 'Senha atual incorreta.';
        }

        if ($novoEmail === '' || !filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
            $erros['novo_email'] = 'Informe um e-mail válido.';
        } elseif ($novoEmail !== $usuario['email'] && $this->usuarioModel->emailExiste($novoEmail)) {
            $erros['novo_email'] = 'Este e-mail já está em uso.';
        }

        $alterarSenha = $novaSenha !== '' || $confirmacaoNovaSenha !== '';
        if ($alterarSenha) {
            if (mb_strlen($novaSenha) < $minimoCaracteres) {
                $erros['nova_senha'] = "A senha deve ter no mínimo {$minimoCaracteres} caracteres.";
            } elseif ($novaSenha !== $confirmacaoNovaSenha) {
                $erros['confirmacao_nova_senha'] = 'A confirmação de senha não corresponde à senha informada.';
            }
        }

        if (!empty($erros)) {
            $this->renderizar(['erros' => $erros, 'valores' => ['email' => $novoEmail]]);
            return;
        }

        if ($novoEmail !== $usuario['email']) {
            $this->usuarioModel->atualizarEmail($usuarioId, $novoEmail);
            Sessao::atualizarEmail($novoEmail);
            LogSeguranca::registrar('email_alterado', $usuarioId, $novoEmail);
        }

        if ($alterarSenha) {
            $this->usuarioModel->atualizarSenha($usuarioId, $novaSenha);
            LogSeguranca::registrar('senha_alterada', $usuarioId, $novoEmail);
        }

        Sessao::definirFlash('sucesso', 'Alterações salvas com sucesso.');
        Sessao::redirecionar('/conta');
    }

    public function processarExcluir(): void
    {
        Sessao::exigirAutenticacao();

        if (!Sessao::validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $this->falhaCsrf();
        }

        $usuarioId = (int) Sessao::usuarioId();

        $this->usuarioModel->excluir($usuarioId);
        LogSeguranca::registrar('conta_excluida', $usuarioId, Sessao::usuarioEmail());

        Sessao::encerrar();
        Sessao::definirFlash('sucesso', 'Sua conta foi excluída. Esperamos vê-lo(a) novamente.');
        Sessao::redirecionar('/login');
    }

    private function falhaCsrf(): never
    {
        LogSeguranca::registrar('acesso_negado', Sessao::usuarioId(), null, 'token CSRF inválido');
        http_response_code(400);
        $mensagem = 'Não foi possível processar sua solicitação. Recarregue a página e tente novamente.';
        require __DIR__ . '/../views/erros/erro_generico.php';
        exit;
    }

    private function renderizar(array $dados = []): void
    {
        extract($dados, EXTR_SKIP);
        $emailAtual = Sessao::usuarioEmail();
        $csrfToken = Sessao::gerarTokenCsrf();
        $flash = Sessao::consumirFlash();
        require __DIR__ . '/../views/conta/index.php';
    }
}
