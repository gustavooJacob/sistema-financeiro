<?php
/**
 * Migration: insere as categorias padrão do sistema (FSD, Seção 11).
 *
 * usuario_id NULL e padrao = 1 identificam itens padrão, visíveis a todos
 * os usuários e não excluíveis pela interface (FSD, Seção 14).
 */

declare(strict_types=1);

$nomes = [
    'Salário',
    'Freelance',
    'Investimentos',
    'Alimentação',
    'Moradia',
    'Transporte',
    'Saúde',
    'Educação',
    'Lazer',
    'Compras',
    'Contas e Serviços',
    'Outros',
];

$comandos = [];
foreach ($nomes as $nome) {
    $nomeEscapado = str_replace("'", "''", $nome);
    $comandos[] = "INSERT INTO categorias (usuario_id, nome, padrao, criado_em, atualizado_em)
        VALUES (NULL, '{$nomeEscapado}', 1, NOW(), NOW())";
}

return $comandos;
