<?php
/**
 * Migration: cria a tabela `tokens_recuperacao_senha` (FSD, Seção 11).
 *
 * O valor do token nunca é armazenado em texto puro, apenas seu hash.
 */

declare(strict_types=1);

return [
    "CREATE TABLE IF NOT EXISTS tokens_recuperacao_senha (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        usuario_id INT UNSIGNED NOT NULL,
        token_hash VARCHAR(255) NOT NULL,
        expira_em DATETIME NOT NULL,
        utilizado TINYINT(1) NOT NULL DEFAULT 0,
        criado_em DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_tokens_usuario_utilizado_expira (usuario_id, utilizado, expira_em),
        CONSTRAINT fk_tokens_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];
