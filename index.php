<?php
/**
 * Ponto de entrada único da aplicação FinControle.
 *
 * Toda requisição do navegador passa por este arquivo (via .htaccess), que
 * carrega a configuração, inicia a sessão e direciona a rota solicitada ao
 * Controller apropriado (organização inspirada em MVC — FSD, Seção 5.1).
 */

declare(strict_types=1);

$configPath = __DIR__ . '/config/config.php';

if (!is_file($configPath)) {
    http_response_code(500);
    echo 'Configuração da aplicação não encontrada. Copie config/config.example.php para config/config.php e preencha os valores do seu ambiente.';
    exit;
}

$config = require $configPath;

date_default_timezone_set($config['timezone'] ?? 'America/Sao_Paulo');

// Carregamento manual das classes (sem autoloader/Composer — FSD Seção 3/CLAUDE.md).
require __DIR__ . '/app/models/Conexao.php';
require __DIR__ . '/app/services/LogErro.php';
require __DIR__ . '/app/services/LogSeguranca.php';
require __DIR__ . '/app/services/Sessao.php';
require __DIR__ . '/app/services/EmailService.php';
require __DIR__ . '/app/models/Usuario.php';
require __DIR__ . '/app/models/TokenRecuperacaoSenha.php';
require __DIR__ . '/app/controllers/AuthController.php';
require __DIR__ . '/app/controllers/PainelController.php';

// Caminho base da aplicação (ex.: "/sistema_financeiro" no XAMPP, ou a
// subpasta equivalente na hospedagem final), detectado a partir do próprio
// index.php — nunca fixo no código (FSD, Seção 4/25).
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

Sessao::iniciar($config, $scriptDir);

set_exception_handler(static function (Throwable $erro) {
    LogErro::registrar(get_class($erro), $erro->getMessage(), $_SERVER['REQUEST_URI'] ?? null, Sessao::usuarioId());
    http_response_code(500);
    $mensagem = 'Ocorreu um erro ao processar sua solicitação. Tente novamente.';
    require __DIR__ . '/app/views/erros/erro_generico.php';
});

// Extrai a rota (caminho relativo à pasta do projeto, sem query string).
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

$rota = $uri;
if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
    $rota = substr($uri, strlen($scriptDir));
}
$rota = '/' . trim($rota, '/');
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = Conexao::obterInstancia();
} catch (Throwable $erro) {
    LogErro::registrar('conexao_banco_falhou', $erro->getMessage(), $rota);
    http_response_code(500);
    $mensagem = 'Ocorreu um erro ao processar sua solicitação. Tente novamente.';
    require __DIR__ . '/app/views/erros/erro_generico.php';
    exit;
}

$authController = new AuthController($pdo, $config);
$painelController = new PainelController();

$rotas = [
    'GET /' => static fn () => Sessao::estaAutenticado()
        ? Sessao::redirecionar('/painel')
        : Sessao::redirecionar('/login'),
    'GET /cadastro' => [$authController, 'exibirCadastro'],
    'POST /cadastro' => [$authController, 'processarCadastro'],
    'GET /login' => [$authController, 'exibirLogin'],
    'POST /login' => [$authController, 'processarLogin'],
    'POST /logout' => [$authController, 'logout'],
    'GET /recuperar-senha' => [$authController, 'exibirRecuperarSenha'],
    'POST /recuperar-senha' => [$authController, 'processarRecuperarSenha'],
    'GET /redefinir-senha' => [$authController, 'exibirRedefinirSenha'],
    'POST /redefinir-senha' => [$authController, 'processarRedefinirSenha'],
    'GET /painel' => [$painelController, 'index'],
];

$chave = $metodo . ' ' . $rota;

if (isset($rotas[$chave])) {
    $rotas[$chave]();
    exit;
}

http_response_code(404);
$mensagem = 'Página não encontrada.';
require __DIR__ . '/app/views/erros/erro_generico.php';
