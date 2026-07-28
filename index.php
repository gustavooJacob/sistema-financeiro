<?php
/**
 * Ponto de entrada único da aplicação FinControle.
 *
 * Toda requisição do navegador passa por este arquivo, que carrega a
 * configuração da aplicação e direciona a rota solicitada ao Controller
 * apropriado (organização inspirada em MVC — ver docs/FSD.md, Seção 5.1).
 *
 * Nesta etapa (preparação de terreno), apenas o esqueleto de carregamento
 * é criado. O roteamento para os Controllers reais será implementado nas
 * próximas fases (ver docs/PLANO.md).
 */

declare(strict_types=1);

$configPath = __DIR__ . '/config/config.php';

if (!is_file($configPath)) {
    http_response_code(500);
    echo 'Configuração da aplicação não encontrada. Copie config/config.example.php para config/config.php e preencha os valores do seu ambiente.';
    exit;
}

$config = require $configPath;

date_default_timezone_set($config['timezone'] ?? 'America/Sao_Paulo');

// TODO (próxima fase): inicializar sessão, roteamento e Controllers.
echo 'FinControle — estrutura inicial preparada. Roteamento será implementado nas próximas fases.';
