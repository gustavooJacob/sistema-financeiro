<?php
/**
 * Conexão PDO única com o banco de dados do FinControle.
 *
 * Centraliza a leitura de config/config.php e a criação da conexão PDO,
 * garantindo o mesmo fuso horário (America/Sao_Paulo) usado pela aplicação
 * PHP também na sessão MySQL (FSD, Seção 14 — Cálculo de Saldo).
 */

declare(strict_types=1);

class Conexao
{
    /**
     * Offset fixo do horário de Brasília. Desde 2019 o Brasil não adota mais
     * horário de verão, então um offset fixo é seguro e evita depender das
     * tabelas de fuso horário do MySQL (nem sempre carregadas no XAMPP).
     */
    private const OFFSET_AMERICA_SAO_PAULO = '-03:00';

    private static ?PDO $instancia = null;

    private function __construct()
    {
    }

    public static function obterInstancia(): PDO
    {
        if (self::$instancia instanceof PDO) {
            return self::$instancia;
        }

        $configPath = __DIR__ . '/../../config/config.php';

        if (!is_file($configPath)) {
            throw new RuntimeException('Configuração da aplicação não encontrada em config/config.php.');
        }

        $config = require $configPath;
        $dbConfig = $config['database'];
        $charset = $dbConfig['charset'] ?? 'utf8mb4';

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $dbConfig['host'],
            $dbConfig['nome'],
            $charset
        );

        $pdo = new PDO($dsn, $dbConfig['usuario'], $dbConfig['senha'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $pdo->exec("SET time_zone = '" . self::OFFSET_AMERICA_SAO_PAULO . "'");

        self::$instancia = $pdo;

        return $pdo;
    }
}
