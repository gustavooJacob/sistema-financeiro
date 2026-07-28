# Bibliotecas de terceiros (hospedagem local)

Conforme restrição técnica do `docs/FSD.md` (Seção 3), nenhuma dependência de front-end pode ser carregada via CDN. Todos os arquivos abaixo devem ser baixados e mantidos localmente dentro desta pasta antes do início da Fase de construção de telas:

- `assets/vendor/bootstrap/` — Bootstrap (CSS e JS).
- `assets/vendor/fonts/` — arquivos `.woff2` da fonte Inter (ver `docs/DESIGN.md`, Seção 3).
- `assets/vendor/icons/` — SVGs individuais da biblioteca Lucide Icons (ver `docs/DESIGN.md`, Seção 10).
- `assets/vendor/chartjs/` — Chart.js, usado no gráfico de gastos por categoria do painel (ver `docs/FSD.md`, Seção 14).

Nenhum desses arquivos foi baixado ainda nesta etapa de preparação do terreno.
