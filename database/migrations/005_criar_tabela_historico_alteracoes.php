<?php
/**
 * Migration: cria a tabela `historico_alteracoes` (FSD, Seção 11).
 *
 * entidade_id é referência lógica (sem FK obrigatória), pois a entidade
 * original (lançamento, categoria ou forma de pagamento) pode já ter sido
 * excluída quando o histórico for consultado.
 */

declare(strict_types=1);

return [
    "CREATE TABLE IF NOT EXISTS historico_alteracoes (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        usuario_id INT UNSIGNED NOT NULL,
        entidade_tipo ENUM('lancamento','categoria','forma_pagamento') NOT NULL,
        entidade_id INT UNSIGNED NOT NULL,
        acao ENUM('criacao','edicao','exclusao') NOT NULL,
        campo_alterado VARCHAR(100) NULL,
        valor_anterior TEXT NULL,
        valor_novo TEXT NULL,
        data_alteracao DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_historico_usuario_data (usuario_id, data_alteracao),
        KEY idx_historico_usuario_entidade (usuario_id, entidade_tipo, entidade_id),
        CONSTRAINT fk_historico_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];
