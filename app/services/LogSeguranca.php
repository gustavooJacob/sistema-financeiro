<?php
/**
 * Registro de eventos de segurança (FSD, Seção 19 — Log de segurança).
 */

declare(strict_types=1);

class LogSeguranca
{
    public static function registrar(
        string $tipoEvento,
        ?int $usuarioId = null,
        ?string $emailTentativa = null,
        ?string $detalhes = null
    ): void {
        try {
            $pdo = Conexao::obterInstancia();
            $stmt = $pdo->prepare(
                'INSERT INTO logs_seguranca (data_hora, tipo_evento, usuario_id, email_tentativa, ip, detalhes)
                 VALUES (NOW(), :tipo_evento, :usuario_id, :email_tentativa, :ip, :detalhes)'
            );
            $stmt->execute([
                'tipo_evento' => $tipoEvento,
                'usuario_id' => $usuarioId,
                'email_tentativa' => $emailTentativa,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'detalhes' => $detalhes,
            ]);
        } catch (Throwable $erro) {
            // Falha ao gravar log de segurança não pode interromper o fluxo do usuário;
            // registra como erro técnico (com contingência em arquivo).
            LogErro::registrar('log_seguranca_falhou', $erro->getMessage(), $tipoEvento, $usuarioId);
        }
    }
}
