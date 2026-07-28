<?php
/**
 * Migration: cria a tabela `categorias` (FSD, Seção 11).
 *
 * usuario_id nulo indica categoria padrão do sistema (padrao = 1).
 */

declare(strict_types=1);

return [
    "CREATE TABLE IF NOT EXISTS categorias (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        usuario_id INT UNSIGNED NULL,
        nome VARCHAR(100) NOT NULL,
        padrao TINYINT(1) NOT NULL DEFAULT 0,
        criado_em DATETIME NOT NULL,
        atualizado_em DATETIME NOT NULL,
        excluido_em DATETIME NULL,
        PRIMARY KEY (id),
        KEY idx_categorias_usuario_nome (usuario_id, nome),
        CONSTRAINT fk_categorias_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];
