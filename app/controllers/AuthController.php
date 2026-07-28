<?php
/**
 * Controller de Autenticação e Conta (Módulo 1 do FSD, Seção 6):
 * cadastro, login, logout e recuperação/redefinição de senha.
 */

declare(strict_types=1);

class AuthController
{
    private PDO $pdo;
    private array $config;
    private Usuario $usuarioModel;
    private TokenRecuperacaoSenha $tokenModel;

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
        $this->usuarioModel = new Usuario($pdo);
        $this->tokenModel = new TokenRecuperacaoSenha($pdo);
    }

    public function exibirCadastro(): void
    {
        if (Sessao::estaAutenticado()) {
            Sessao::redirecionar('/painel');
        }

        $this->renderizar('auth/cadastro', ['erros' => [], 'valores' => []]);
    }

    public function processarCadastro(): void
    {
        if (!Sessao::validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $this->falhaCsrf();
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');
        $confirmacaoSenha = (string) ($_POST['confirmacao_senha'] ?? '');
        $minimoCaracteres = (int) ($this->config['seguranca']['senha_minimo_caracteres'] ?? 8);

        $erros = [];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros['email'] = 'Informe um e-mail válido.';
        } elseif ($this->usuarioModel->emailExiste($email)) {
            $erros['email'] = 'Este e-mail já está cadastrado.';
        }

        if (mb_strlen($senha) < $minimoCaracteres) {
            $erros['senha'] = "A senha deve ter no mínimo {$minimoCaracteres} caracteres.";
        } elseif ($senha !== $confirmacaoSenha) {
            $erros['confirmacao_senha'] = 'A confirmação de senha não corresponde à senha informada.';
        }

        if (!empty($erros)) {
            $this->renderizar('auth/cadastro', ['erros' => $erros, 'valores' => ['email' => $email]]);
            return;
        }

        $this->usuarioModel->criar($email, $senha);

        Sessao::definirFlash('sucesso', 'Conta criada com sucesso. Faça login para continuar.');
        Sessao::redirecionar('/login');
    }

    public function exibirLogin(): void
    {
        if (Sessao::estaAutenticado()) {
            Sessao::redirecionar('/painel');
        }

        $this->renderizar('auth/login', ['erros' => [], 'valores' => []]);
    }

    public function processarLogin(): void
    {
        if (!Sessao::validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $this->falhaCsrf();
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');

        $mensagemGenerica = 'E-mail ou senha inválidos.';
        $maxTentativas = (int) ($this->config['seguranca']['login_max_tentativas'] ?? 5);
        $minutosBloqueio = (int) ($this->config['seguranca']['login_bloqueio_minutos'] ?? 15);

        $usuario = $email !== '' ? $this->usuarioModel->buscarPorEmail($email) : null;

        if ($usuario !== null && $this->usuarioModel->estaBloqueado($usuario)) {
            LogSeguranca::registrar('bloqueio_tentativas', (int) $usuario['id'], $email);
            $this->renderizar('auth/login', [
                'erros' => ['geral' => 'Conta temporariamente bloqueada por excesso de tentativas. Tente novamente mais tarde.'],
                'valores' => ['email' => $email],
            ]);
            return;
        }

        if ($usuario === null || !password_verify($senha, $usuario['senha_hash'])) {
            if ($usuario !== null) {
                $this->usuarioModel->registrarTentativaFalha((int) $usuario['id'], $maxTentativas, $minutosBloqueio);
            }

            LogSeguranca::registrar('login_invalido', $usuario['id'] ?? null, $email);

            $this->renderizar('auth/login', [
                'erros' => ['geral' => $mensagemGenerica],
                'valores' => ['email' => $email],
            ]);
            return;
        }

        $this->usuarioModel->resetarTentativasFalhas((int) $usuario['id']);
        Sessao::autenticar((int) $usuario['id'], $usuario['email']);
        Sessao::redirecionar('/painel');
    }

    public function logout(): void
    {
        if (!Sessao::validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $this->falhaCsrf();
        }

        Sessao::encerrar();
        Sessao::redirecionar('/login');
    }

    public function exibirRecuperarSenha(): void
    {
        $this->renderizar('auth/recuperar_senha', ['erros' => [], 'valores' => [], 'enviado' => false]);
    }

    public function processarRecuperarSenha(): void
    {
        if (!Sessao::validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $this->falhaCsrf();
        }

        $email = trim((string) ($_POST['email'] ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->renderizar('auth/recuperar_senha', [
                'erros' => ['email' => 'Informe um e-mail válido.'],
                'valores' => ['email' => $email],
                'enviado' => false,
            ]);
            return;
        }

        // Por segurança, a mensagem de confirmação é sempre a mesma,
        // independentemente de o e-mail existir ou não (FSD, Seção 12).
        $usuario = $this->usuarioModel->buscarPorEmail($email);

        if ($usuario !== null) {
            $tokenTextoPuro = bin2hex(random_bytes(32));
            $minutosValidade = (int) ($this->config['seguranca']['token_recuperacao_expiracao_minutos'] ?? 60);
            $this->tokenModel->criar((int) $usuario['id'], $tokenTextoPuro, $minutosValidade);

            $link = rtrim($this->config['url_base'] ?? '', '/') . '/redefinir-senha?token=' . $tokenTextoPuro;
            $corpo = "Olá,\n\nRecebemos uma solicitação de redefinição de senha para sua conta no FinControle.\n"
                . "Acesse o link abaixo para definir uma nova senha (válido por {$minutosValidade} minutos):\n\n{$link}\n\n"
                . "Se você não solicitou, ignore este e-mail.";

            $emailService = new EmailService($this->config['smtp']);
            $emailService->enviar($usuario['email'], $usuario['email'], 'Redefinição de senha — FinControle', $corpo);

            LogSeguranca::registrar('solicitacao_recuperacao_senha', (int) $usuario['id'], $email);
        } else {
            LogSeguranca::registrar('solicitacao_recuperacao_senha', null, $email, 'e-mail não cadastrado');
        }

        $this->renderizar('auth/recuperar_senha', ['erros' => [], 'valores' => [], 'enviado' => true]);
    }

    public function exibirRedefinirSenha(): void
    {
        $tokenTextoPuro = (string) ($_GET['token'] ?? '');
        $token = $tokenTextoPuro !== '' ? $this->tokenModel->buscarValidoPorTexto($tokenTextoPuro) : null;

        if ($token === null) {
            $this->renderizar('auth/redefinir_senha', ['tokenValido' => false, 'erros' => [], 'token' => '']);
            return;
        }

        $this->renderizar('auth/redefinir_senha', ['tokenValido' => true, 'erros' => [], 'token' => $tokenTextoPuro]);
    }

    public function processarRedefinirSenha(): void
    {
        if (!Sessao::validarTokenCsrf($_POST['csrf_token'] ?? null)) {
            $this->falhaCsrf();
        }

        $tokenTextoPuro = (string) ($_POST['token'] ?? '');
        $novaSenha = (string) ($_POST['senha'] ?? '');
        $confirmacaoSenha = (string) ($_POST['confirmacao_senha'] ?? '');
        $minimoCaracteres = (int) ($this->config['seguranca']['senha_minimo_caracteres'] ?? 8);

        $token = $this->tokenModel->buscarValidoPorTexto($tokenTextoPuro);

        if ($token === null) {
            $this->renderizar('auth/redefinir_senha', ['tokenValido' => false, 'erros' => [], 'token' => '']);
            return;
        }

        $erros = [];

        if (mb_strlen($novaSenha) < $minimoCaracteres) {
            $erros['senha'] = "A senha deve ter no mínimo {$minimoCaracteres} caracteres.";
        } elseif ($novaSenha !== $confirmacaoSenha) {
            $erros['confirmacao_senha'] = 'A confirmação de senha não corresponde à senha informada.';
        }

        if (!empty($erros)) {
            $this->renderizar('auth/redefinir_senha', [
                'tokenValido' => true,
                'erros' => $erros,
                'token' => $tokenTextoPuro,
            ]);
            return;
        }

        $this->usuarioModel->atualizarSenha((int) $token['usuario_id'], $novaSenha);
        $this->usuarioModel->resetarTentativasFalhas((int) $token['usuario_id']);
        $this->tokenModel->marcarUtilizado((int) $token['id']);

        LogSeguranca::registrar('redefinicao_senha_concluida', (int) $token['usuario_id']);

        Sessao::definirFlash('sucesso', 'Senha redefinida com sucesso. Faça login com a nova senha.');
        Sessao::redirecionar('/login');
    }

    private function falhaCsrf(): never
    {
        LogSeguranca::registrar('acesso_negado', Sessao::usuarioId(), null, 'token CSRF inválido');
        http_response_code(400);
        $this->renderizar('erros/erro_generico', [
            'mensagem' => 'Não foi possível processar sua solicitação. Recarregue a página e tente novamente.',
        ]);
        exit;
    }

    private function renderizar(string $view, array $dados = []): void
    {
        extract($dados, EXTR_SKIP);
        $csrfToken = Sessao::gerarTokenCsrf();
        $flash = Sessao::consumirFlash();
        require __DIR__ . '/../views/' . $view . '.php';
    }
}
