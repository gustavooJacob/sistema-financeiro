<?php
/**
 * Migration: cria a tabela `usuarios` (FSD, Seção 11).
 *
 * Retorna a lista de comandos SQL executados pelo runner (database/migrations/run.php).
 */

declare(strict_types=1);

return [
    "CREATE TABLE IF NOT EXISTS usuarios (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        email VARCHAR(190) NOT NULL,
        senha_hash VARCHAR(255) NOT NULL,
        tentativas_login_falhas TINYINT UNSIGNED NOT NULL DEFAULT 0,
        bloqueado_ate DATETIME NULL,
        criado_em DATETIME NOT NULL,
        atualizado_em DATETIME NOT NULL,
        excluido_em DATETIME NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uk_usuarios_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];
