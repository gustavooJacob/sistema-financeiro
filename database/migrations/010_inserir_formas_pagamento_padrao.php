<?php
/**
 * Migration: insere as formas de pagamento padrão do sistema (FSD, Seção 11).
 *
 * usuario_id NULL e padrao = 1 identificam itens padrão, visíveis a todos
 * os usuários e não excluíveis pela interface (FSD, Seção 14).
 */

declare(strict_types=1);

$nomes = [
    'Dinheiro',
    'Cartão de Crédito',
    'Cartão de Débito',
    'Pix',
    'Transferência Bancária',
    'Boleto',
];

$comandos = [];
foreach ($nomes as $nome) {
    $nomeEscapado = str_replace("'", "''", $nome);
    $comandos[] = "INSERT INTO formas_pagamento (usuario_id, nome, padrao, criado_em, atualizado_em)
        VALUES (NULL, '{$nomeEscapado}', 1, NOW(), NOW())";
}

return $comandos;
