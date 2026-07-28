<?php
/**
 * Helper para renderizar ícones Lucide (SVGs hospedados localmente em
 * assets/vendor/icons/, sem CDN — FSD Seção 3, DESIGN.md Seção 10) embutidos
 * diretamente no HTML, permitindo colorir via CSS (stroke="currentColor").
 */
class Icone
{
    private const PASTA = __DIR__ . '/../../assets/vendor/icons';

    public static function render(string $nome, int $tamanho = 20, string $classeExtra = ''): string
    {
        $nomeSeguro = basename($nome);

        if (!preg_match('/^[a-z0-9-]+$/', $nomeSeguro)) {
            return '';
        }

        $caminho = self::PASTA . '/' . $nomeSeguro . '.svg';

        if (!is_file($caminho)) {
            return '';
        }

        static $cache = [];
        if (!isset($cache[$caminho])) {
            $cache[$caminho] = file_get_contents($caminho);
        }
        $svg = $cache[$caminho];

        $classe = trim('icone ' . $classeExtra);
        $svg = preg_replace('/width="\d+"/', 'width="' . $tamanho . '"', $svg, 1);
        $svg = preg_replace('/height="\d+"/', 'height="' . $tamanho . '"', $svg, 1);
        $svg = preg_replace('/<svg\s/', '<svg class="' . htmlspecialchars($classe, ENT_QUOTES) . '" ', $svg, 1);

        return $svg;
    }
}
