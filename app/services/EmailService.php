<?php
/**
 * Envio de e-mails via SMTP (cliente próprio, sem biblioteca externa —
 * o projeto não usa gerenciador de dependências de back-end, ver
 * CLAUDE.md/FSD Seção 3).
 *
 * Usado apenas pelo fluxo de recuperação de senha (FSD, Seção 15).
 * Em desenvolvimento, aponta para um SMTP de teste local (ex.: Mailpit);
 * em produção, para o SMTP real da hospedagem — troca feita somente via
 * config/config.php.
 */

declare(strict_types=1);

class EmailService
{
    private array $smtpConfig;

    public function __construct(array $smtpConfig)
    {
        $this->smtpConfig = $smtpConfig;
    }

    public function enviar(string $paraEmail, string $paraNome, string $assunto, string $corpoTexto): bool
    {
        $conexao = null;

        try {
            $host = $this->smtpConfig['host'];
            $porta = (int) $this->smtpConfig['porta'];

            $conexao = @fsockopen($host, $porta, $codigoErro, $mensagemErro, 10);
            if ($conexao === false) {
                throw new RuntimeException("Falha ao conectar ao SMTP {$host}:{$porta} — {$mensagemErro}");
            }

            $this->lerResposta($conexao, 220);
            $this->enviarComando($conexao, 'EHLO ' . gethostname(), 250);

            if (!empty($this->smtpConfig['tls'])) {
                $this->enviarComando($conexao, 'STARTTLS', 220);
                if (!stream_socket_enable_crypto($conexao, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('Falha ao iniciar TLS com o servidor SMTP.');
                }
                $this->enviarComando($conexao, 'EHLO ' . gethostname(), 250);
            }

            if (!empty($this->smtpConfig['usuario'])) {
                $this->enviarComando($conexao, 'AUTH LOGIN', 334);
                $this->enviarComando($conexao, base64_encode($this->smtpConfig['usuario']), 334);
                $this->enviarComando($conexao, base64_encode($this->smtpConfig['senha']), 235);
            }

            $remetente = $this->smtpConfig['remetente_email'];
            $this->enviarComando($conexao, "MAIL FROM:<{$remetente}>", 250);
            $this->enviarComando($conexao, "RCPT TO:<{$paraEmail}>", [250, 251]);
            $this->enviarComando($conexao, 'DATA', 354);

            $cabecalhos = implode("\r\n", [
                'From: ' . $this->smtpConfig['remetente_nome'] . ' <' . $remetente . '>',
                'To: ' . $paraNome . ' <' . $paraEmail . '>',
                'Subject: ' . $this->codificarAssunto($assunto),
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
            ]);

            $corpoEscapado = preg_replace('/^\./m', '..', $corpoTexto);
            $mensagem = $cabecalhos . "\r\n\r\n" . $corpoEscapado . "\r\n.";

            $this->enviarComando($conexao, $mensagem, 250);
            $this->enviarComando($conexao, 'QUIT', 221);

            return true;
        } catch (Throwable $erro) {
            LogErro::registrar('envio_email_falhou', $erro->getMessage(), 'EmailService::enviar');
            return false;
        } finally {
            if (is_resource($conexao)) {
                fclose($conexao);
            }
        }
    }

    private function enviarComando($conexao, string $comando, $codigoEsperado)
    {
        fwrite($conexao, $comando . "\r\n");
        return $this->lerResposta($conexao, $codigoEsperado);
    }

    private function lerResposta($conexao, $codigoEsperado): string
    {
        $resposta = '';
        while ($linha = fgets($conexao, 515)) {
            $resposta .= $linha;
            if (isset($linha[3]) && $linha[3] === ' ') {
                break;
            }
        }

        $codigo = (int) substr($resposta, 0, 3);
        $codigosValidos = is_array($codigoEsperado) ? $codigoEsperado : [$codigoEsperado];

        if (!in_array($codigo, $codigosValidos, true)) {
            throw new RuntimeException("Resposta SMTP inesperada: {$resposta}");
        }

        return $resposta;
    }

    private function codificarAssunto(string $assunto): string
    {
        return '=?UTF-8?B?' . base64_encode($assunto) . '?=';
    }
}
