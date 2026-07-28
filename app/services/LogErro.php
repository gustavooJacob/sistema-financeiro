<?php
/**
 * Registro de erros técnicos (FSD, Seção 19 — Log de erros).
 *
 * Grava preferencialmente na tabela `logs_erros`. Se o banco estiver
 * indisponível (ou o próprio erro impedir o registro normal), usa
 * contingência em arquivo dentro de logs/erros/, conforme FSD Seção 19.
 */

declare(strict_types=1);

class LogErro
{
    public static function registrar(
        string $tipoErro,
        string $mensagemTecnica,
        ?string $contexto = null,
        ?int $usuarioId = null
    ): void {
        try {
            $pdo = Conexao::obterInstancia();
            $stmt = $pdo->prepare(
                'INSERT INTO logs_erros (data_hora, tipo_erro, mensagem_tecnica, contexto, usuario_id, ip)
                 VALUES (NOW(), :tipo_erro, :mensagem_tecnica, :contexto, :usuario_id, :ip)'
            );
            $stmt->execute([
                'tipo_erro' => $tipoErro,
                'mensagem_tecnica' => $mensagemTecnica,
                'contexto' => $contexto,
                'usuario_id' => $usuarioId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Throwable $erro) {
            self::registrarEmArquivo($tipoErro, $mensagemTecnica, $contexto, $erro);
        }
    }

    private static function registrarEmArquivo(
        string $tipoErro,
        string $mensagemTecnica,
        ?string $contexto,
        ?Throwable $erroOriginal = null
    ): void {
        $pasta = __DIR__ . '/../../logs/erros';

        if (!is_dir($pasta)) {
            @mkdir($pasta, 0755, true);
        }

        $arquivo = $pasta . '/erro_' . date('Y-m-d') . '.log';
        $linha = sprintf(
            '[%s] tipo=%s contexto=%s mensagem=%s%s' . PHP_EOL,
            date('Y-m-d H:i:s'),
            $tipoErro,
            $contexto ?? '-',
            $mensagemTecnica,
            $erroOriginal ? ' (falha_ao_gravar_no_banco=' . $erroOriginal->getMessage() . ')' : ''
        );

        @file_put_contents($arquivo, $linha, FILE_APPEND | LOCK_EX);
    }
}
