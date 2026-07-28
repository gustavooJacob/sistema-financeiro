<?php
/**
 * Migration: cria a tabela `logs_erros` (FSD, Seções 11 e 19).
 *
 * usuario_id não usa ON DELETE CASCADE: mesmo que o usuário seja excluído
 * (soft delete), o registro de log deve ser preservado para auditoria.
 */

declare(strict_types=1);

return [
    "CREATE TABLE IF NOT EXISTS logs_erros (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        data_hora DATETIME NOT NULL,
        tipo_erro VARCHAR(100) NULL,
        mensagem_tecnica TEXT NULL,
        contexto VARCHAR(255) NULL,
        usuario_id INT UNSIGNED NULL,
        ip VARCHAR(45) NULL,
        PRIMARY KEY (id),
        KEY idx_logs_erros_data_hora (data_hora),
        CONSTRAINT fk_logs_erros_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];
