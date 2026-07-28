<?php
/**
 * Migration: cria a tabela `logs_seguranca` (FSD, Seções 11 e 19).
 */

declare(strict_types=1);

return [
    "CREATE TABLE IF NOT EXISTS logs_seguranca (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        data_hora DATETIME NOT NULL,
        tipo_evento VARCHAR(100) NOT NULL,
        usuario_id INT UNSIGNED NULL,
        email_tentativa VARCHAR(190) NULL,
        ip VARCHAR(45) NULL,
        detalhes TEXT NULL,
        PRIMARY KEY (id),
        KEY idx_logs_seguranca_usuario_data (usuario_id, data_hora),
        KEY idx_logs_seguranca_tipo_data (tipo_evento, data_hora),
        CONSTRAINT fk_logs_seguranca_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];
