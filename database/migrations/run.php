<?php
/**
 * Executor de migrations do FinControle.
 *
 * Uso (linha de comando, nunca por rota pública — FSD Seção 11):
 *   php database/migrations/run.php
 *
 * Cria o banco de dados (se necessário), a tabela de controle `migrations`
 * e executa, em ordem, cada migration desta pasta ainda não registrada.
 * Migrations já executadas não são executadas novamente.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script só pode ser executado via linha de comando (PHP CLI).');
}

$configPath = __DIR__ . '/../../config/config.php';

if (!is_file($configPath)) {
    fwrite(STDERR, "Configuração não encontrada. Copie config/config.example.php para config/config.php.\n");
    exit(1);
}

$config = require $configPath;

date_default_timezone_set($config['timezone'] ?? 'America/Sao_Paulo');

$dbConfig = $config['database'];
$charset = $dbConfig['charset'] ?? 'utf8mb4';

// Offset fixo do horário de Brasília: desde 2019 o Brasil não adota mais
// horário de verão, então -03:00 é seguro e evita depender das tabelas de
// fuso horário do MySQL (mysql.time_zone_name), que nem sempre estão
// carregadas em instalações padrão do XAMPP.
const OFFSET_AMERICA_SAO_PAULO = '-03:00';

try {
    // 1) Conecta ao servidor MySQL sem selecionar banco, para poder criá-lo.
    $dsnServidor = sprintf('mysql:host=%s;charset=%s', $dbConfig['host'], $charset);
    $pdoServidor = new PDO($dsnServidor, $dbConfig['usuario'], $dbConfig['senha'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $nomeBanco = $dbConfig['nome'];
    $pdoServidor->exec(
        "CREATE DATABASE IF NOT EXISTS `{$nomeBanco}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    // 2) Reconecta já com o banco selecionado.
    $dsnBanco = sprintf('mysql:host=%s;dbname=%s;charset=%s', $dbConfig['host'], $nomeBanco, $charset);
    $pdo = new PDO($dsnBanco, $dbConfig['usuario'], $dbConfig['senha'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec("SET time_zone = '" . OFFSET_AMERICA_SAO_PAULO . "'");
    $pdo->exec('SET NAMES ' . $charset);

    // 3) Garante a existência da tabela de controle de migrations.
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS migrations (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            nome_arquivo VARCHAR(255) NOT NULL,
            executada_em DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uk_migrations_nome_arquivo (nome_arquivo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    // 4) Lista os arquivos de migration (exceto este executor), em ordem.
    $arquivos = glob(__DIR__ . '/*.php');
    $arquivos = array_filter($arquivos, static fn (string $arquivo): bool => basename($arquivo) !== 'run.php');
    sort($arquivos, SORT_STRING);

    $stmtVerifica = $pdo->prepare('SELECT COUNT(*) FROM migrations WHERE nome_arquivo = :nome_arquivo');
    $stmtRegistra = $pdo->prepare(
        'INSERT INTO migrations (nome_arquivo, executada_em) VALUES (:nome_arquivo, NOW())'
    );

    foreach ($arquivos as $arquivo) {
        $nomeArquivo = basename($arquivo);

        $stmtVerifica->execute(['nome_arquivo' => $nomeArquivo]);
        if ((int) $stmtVerifica->fetchColumn() > 0) {
            echo "[ignorada] {$nomeArquivo} (já executada)\n";
            continue;
        }

        $comandosSql = require $arquivo;

        if (!is_array($comandosSql)) {
            throw new RuntimeException("Migration {$nomeArquivo} deve retornar um array de comandos SQL.");
        }

        // Comandos DDL (CREATE TABLE) causam commit implícito no MySQL, então
        // não há transação real possível envolvendo-os; cada migration deve
        // ser idempotente por si só (CREATE TABLE IF NOT EXISTS) e, em caso
        // de falha no meio do arquivo, o erro interrompe a execução e não
        // registra a migration como concluída.
        try {
            foreach ($comandosSql as $sql) {
                $pdo->exec($sql);
            }
            $stmtRegistra->execute(['nome_arquivo' => $nomeArquivo]);
            echo "[executada] {$nomeArquivo}\n";
        } catch (Throwable $erro) {
            throw new RuntimeException("Falha ao executar {$nomeArquivo}: {$erro->getMessage()}", 0, $erro);
        }
    }

    echo "Migrations concluídas com sucesso.\n";
} catch (Throwable $erro) {
    fwrite(STDERR, 'Erro ao executar migrations: ' . $erro->getMessage() . "\n");
    exit(1);
}
