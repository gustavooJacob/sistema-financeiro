<?php
/**
 * Controle de sessão do FinControle: início seguro, expiração por
 * inatividade, autenticação, proteção CSRF e mensagens flash.
 *
 * FSD, Seção 15: sessão PHP nativa, expiração por inatividade configurável
 * (padrão 30 minutos). FSD, Seção 5.4/20: parâmetros vêm de config/config.php.
 */

declare(strict_types=1);

class Sessao
{
    private static bool $iniciada = false;
    private static array $config = [];
    private static string $caminhoBase = '';

    public static function iniciar(array $config, string $caminhoBase = ''): void
    {
        if (self::$iniciada) {
            return;
        }

        self::$config = $config;
        self::$caminhoBase = rtrim($caminhoBase, '/');

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => self::requisicaoEhHttps(),
            ]);
            session_start();
        }

        self::$iniciada = true;

        self::validarExpiracaoPorInatividade();

        $_SESSION['ultima_atividade'] = time();
    }

    private static function requisicaoEhHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    }

    private static function validarExpiracaoPorInatividade(): void
    {
        $minutos = (int) (self::$config['seguranca']['sessao_expiracao_minutos'] ?? 30);
        $limiteSegundos = $minutos * 60;

        if (!empty($_SESSION['usuario_id']) && !empty($_SESSION['ultima_atividade'])) {
            $inativoPor = time() - (int) $_SESSION['ultima_atividade'];

            if ($inativoPor > $limiteSegundos) {
                self::encerrar();
                self::definirFlash('erro', 'Sua sessão expirou por inatividade. Faça login novamente.');
            }
        }
    }

    public static function autenticar(int $usuarioId, string $email): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $usuarioId;
        $_SESSION['usuario_email'] = $email;
        $_SESSION['ultima_atividade'] = time();
    }

    public static function encerrar(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parametros = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parametros['path'],
                $parametros['domain'],
                $parametros['secure'],
                $parametros['httponly']
            );
        }

        session_destroy();
    }

    public static function estaAutenticado(): bool
    {
        return !empty($_SESSION['usuario_id']);
    }

    public static function usuarioId(): ?int
    {
        return isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;
    }

    public static function usuarioEmail(): ?string
    {
        return $_SESSION['usuario_email'] ?? null;
    }

    public static function exigirAutenticacao(): void
    {
        if (!self::estaAutenticado()) {
            self::definirFlash('erro', 'Faça login para acessar esta área.');
            self::redirecionar('/login');
        }
    }

    public static function gerarTokenCsrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function validarTokenCsrf(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function definirFlash(string $tipo, string $mensagem): void
    {
        $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
    }

    public static function consumirFlash(): ?array
    {
        if (empty($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }

    /**
     * Monta uma URL absoluta para a aplicação, incluindo a subpasta em que
     * o projeto estiver instalado (ex.: "/sistema_financeiro"), sem
     * depender de nome fixo (FSD, Seção 4/25).
     */
    public static function url(string $caminho): string
    {
        return self::$caminhoBase . '/' . ltrim($caminho, '/');
    }

    public static function redirecionar(string $caminho): never
    {
        header('Location: ' . self::url($caminho));
        exit;
    }
}
