# Inventário de Insumos do Projeto

Levantamento da pasta `docs/` do projeto FinControle, realizado em 28/07/2026.

**Resultado da varredura:** a pasta `docs/` contém, no momento, apenas os dois documentos centrais de especificação. Não foram encontrados arquivos adicionais de apoio (logos, ícones, imagens de referência, PDFs, planilhas ou outros documentos complementares).

| Arquivo | O que é | Usado pelo sistema em execução? | Onde será usado | Observações |
|---|---|---|---|---|
| `FSD.md` | Documento de Especificação Funcional — consolida stack, ambientes, arquitetura, modelo de dados, regras de negócio e critérios de aceitação. | Não (documentação) | Referência para a IA codificadora durante toda a construção | Documento principal; algumas decisões estão marcadas como provisórias (Seção 27) e pendentes de confirmação do usuário. |
| `DESIGN.md` | Guia de referência visual (paleta de cores, tipografia, espaçamentos, componentes de UI, ícones), originalmente extraído de um dashboard de referência do tipo CRM/call center. | Não (documentação) | Referência de estilo para aplicação nas telas do sistema | Trechos herdados do domínio de call center (`call_card`, ícones de telefone/e-mail/monitor/maleta/localização) foram removidos após confirmação do usuário em 28/07/2026. Biblioteca de ícones definida como Lucide Icons e fonte Inter definida para hospedagem local (`assets/vendor/`); ambos os arquivos ainda precisam ser baixados pela IA codificadora na etapa de construção. |

**Observação importante:** nenhum arquivo aqui listado deve ser copiado para pastas públicas/assets nesta etapa. Essa cópia (quando aplicável) ocorre apenas na fase de construção, seguindo a estrutura definida no FSD (`assets/vendor/`, etc.).

Se, no futuro, novos arquivos forem adicionados a `docs/` (logos, ícones, imagens de referência), este inventário deverá ser atualizado antes da próxima etapa de construção.
