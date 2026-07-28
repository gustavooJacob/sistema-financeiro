# Bibliotecas de terceiros (hospedagem local)

Conforme restrição técnica do `docs/FSD.md` (Seção 3), nenhuma dependência de front-end é carregada via CDN. Todas as bibliotecas abaixo foram baixadas e são mantidas localmente dentro desta pasta (versionadas no Git).

- `assets/vendor/bootstrap/css/bootstrap.min.css` e `assets/vendor/bootstrap/js/bootstrap.bundle.min.js` — Bootstrap 5.3.3 (apenas os arquivos minificados finais; carregado antes de `assets/css/app.css`, que sobrepõe a paleta/tipografia/espaçamentos do `docs/DESIGN.md`).
- `assets/vendor/fonts/InterVariable.woff2` — fonte Inter (variável, cobre os pesos 400/500/600 usados no DESIGN.md) em um único arquivo `.woff2`, referenciada via `@font-face` em `assets/css/app.css`.
- `assets/vendor/icons/*.svg` — ícones individuais da biblioteca Lucide Icons usados nas telas do sistema (sidebar de navegação e demais componentes), renderizados inline pelo helper `app/services/Icone.php` (permite colorir via CSS com `stroke="currentColor"`).
- `assets/vendor/chartjs/chart.umd.min.js` — Chart.js (Fase 7), usado no gráfico de gastos por categoria do painel.

Nenhum desses arquivos é carregado de um CDN em tempo de execução; todos ficam versionados neste repositório para funcionar igualmente no XAMPP e na hospedagem final (Hostnet).
