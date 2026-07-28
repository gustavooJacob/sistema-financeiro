<?php
/**
 * Migration: cria a tabela `lancamentos` (FSD, Seção 11).
 *
 * Índices seguem exatamente as consultas críticas descritas no FSD:
 * painel/atrasados, saldo realizado por mês, gráfico por categoria,
 * filtro por forma de pagamento e filtragem de registros ativos.
 */

declare(strict_types=1);

return [
    "CREATE TABLE IF NOT EXISTS lancamentos (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        usuario_id INT UNSIGNED NOT NULL,
        tipo ENUM('receita','despesa') NOT NULL,
        valor DECIMAL(12,2) NOT NULL,
        categoria_id INT UNSIGNED NOT NULL,
        forma_pagamento_id INT UNSIGNED NOT NULL,
        descricao VARCHAR(300) NULL,
        data_prevista DATE NOT NULL,
        data_efetiva DATE NULL,
        status ENUM('pendente','concluido') NOT NULL,
        criado_em DATETIME NOT NULL,
        atualizado_em DATETIME NOT NULL,
        excluido_em DATETIME NULL,
        PRIMARY KEY (id),
        KEY idx_lancamentos_usuario_status_prevista (usuario_id, status, data_prevista),
        KEY idx_lancamentos_usuario_efetiva (usuario_id, data_efetiva),
        KEY idx_lancamentos_usuario_categoria (usuario_id, categoria_id),
        KEY idx_lancamentos_usuario_forma_pagamento (usuario_id, forma_pagamento_id),
        KEY idx_lancamentos_excluido_em (excluido_em),
        CONSTRAINT fk_lancamentos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
        CONSTRAINT fk_lancamentos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias (id) ON DELETE RESTRICT,
        CONSTRAINT fk_lancamentos_forma_pagamento FOREIGN KEY (forma_pagamento_id) REFERENCES formas_pagamento (id) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];
