<?php
/**
 * Arquivo de configuração de EXEMPLO do FinControle.
 *
 * Copie este arquivo para "config.php" (na mesma pasta) e preencha os
 * valores reais do seu ambiente. O arquivo "config.php" nunca deve ser
 * versionado no Git (ver .gitignore).
 *
 * Este projeto não utiliza .env — todas as configurações sensíveis ficam
 * neste arquivo PHP, carregado apenas internamente via require/include.
 */

return [
    // Fuso horário de referência da aplicação e do banco de dados.
    'timezone' => 'America/Sao_Paulo',

    // Conexão com o banco de dados MySQL.
    'database' => [
        'host' => 'localhost',
        'nome' => 'fincontrole',
        'usuario' => 'root',
        'senha' => '',
        'charset' => 'utf8mb4',
    ],

    // Configuração de SMTP para envio do e-mail de recuperação de senha.
    // Em desenvolvimento (XAMPP), aponte para um SMTP de teste local
    // (ex.: Mailpit ou Mailtrap local). Em produção (Hostnet), aponte
    // para um SMTP real. A troca ocorre apenas alterando estes valores.
    'smtp' => [
        'host' => 'localhost',
        'porta' => 1025,
        'usuario' => '',
        'senha' => '',
        'tls' => false,
        'remetente_email' => 'nao-responda@fincontrole.local',
        'remetente_nome' => 'FinControle',
    ],

    // URL base da aplicação, usada para montar o link de redefinição de
    // senha enviado por e-mail (ex.: link "http://localhost/fincontrole").
    // Sem barra final.
    'url_base' => 'http://localhost/sistema_financeiro',

    // Flags de ativação de logs.
    'logs' => [
        'erros_ativo' => true,
        'seguranca_ativo' => true,
    ],

    // Parâmetros de segurança confirmados no FSD (Seção 27).
    'seguranca' => [
        'senha_minimo_caracteres' => 8,
        'sessao_expiracao_minutos' => 30,
        'token_recuperacao_expiracao_minutos' => 60,
        'login_max_tentativas' => 5,
        'login_bloqueio_minutos' => 15,
    ],
];
