<?php
/**
 * Migration: cria a tabela `formas_pagamento` (FSD, Seção 11).
 *
 * Estrutura idêntica à tabela `categorias`, conforme especificado no FSD.
 */

declare(strict_types=1);

return [
    "CREATE TABLE IF NOT EXISTS formas_pagamento (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        usuario_id INT UNSIGNED NULL,
        nome VARCHAR(100) NOT NULL,
        padrao TINYINT(1) NOT NULL DEFAULT 0,
        criado_em DATETIME NOT NULL,
        atualizado_em DATETIME NOT NULL,
        excluido_em DATETIME NULL,
        PRIMARY KEY (id),
        KEY idx_formas_pagamento_usuario_nome (usuario_id, nome),
        CONSTRAINT fk_formas_pagamento_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];
