# Meios de Entrega

## Arquitetura e capacidades

O checkout usa `QuoteShippingAction`, que consulta `ShippingProviderRegistry::enabled()`. O Registry resolve somente adapters registrados e integrações ativas. Não existe fallback automático de frete fictício. `FakeShippingProvider` é utilizado explicitamente pelas fixtures de testes; não consta no registro da aplicação.

Contratos:

- `ShippingProvider`: identidade e cotações normalizadas.
- `ShippingCapabilitiesProvider`: capacidades gerais (retirada, etiquetas, rastreamento e necessidade de CEP).
- `ShipmentProvider`: criação do carrinho, compra e inspeção do envio.
- `ShipmentLabelProvider`: geração, impressão, consulta de cancelabilidade e cancelamento.
- `ShipmentTrackingProvider`: atualização de rastreamento.

`ShippingOption` contém integração, provider, IDs externos, nomes, centavos, estimativa, volumes, modo de fulfillment e metadata normalizada. O modo (`automatic`, `manual` ou `pickup`) é salvo no snapshot: a disponibilidade atual do adapter não transforma a natureza de um pedido antigo. `OrderShipping` concentra a interpretação dos registros legados.

`Order hasMany Shipments`. Cada slot de fulfillment possui uma ou mais tentativas; apenas a tentativa mais recente conta para conclusão. Uma tentativa cancelada continua pendente até ser substituída explicitamente.

## Configuração administrativa

**Configurações → Meios de Entrega**, em largura total, apresenta cards para Melhor Envio, SuperFrete, Correios e retirada. Os dois providers futuros não possuem toggle nem respostas simuladas. Todas as ações administrativas exigem o guard `web`; clientes usam outro guard.

Cada provider implementado tem ativação e Configurar no próprio card. O modal do Melhor Envio contém quatro abas: Conexão, Transportadoras e serviços, Unidades de postagem e Operação. Cada área possui salvamento independente; trocar de aba preserva rascunhos. A edição de transportadoras ocorre dentro do mesmo modal, sem diálogos aninhados. Credenciais não são validadas ao salvar operação, transportadoras ou sincronizar unidades. Retirada possui seu próprio modal. Salvar dados não compra frete. Desativar a integração interrompe novas cotações, mas mantém acesso operacional aos envios históricos quando a conta está conectada no ambiente correspondente.

## OAuth

Cadastrar o aplicativo com a opção de alterar transportadoras e serviços durante instalação **desmarcada**. O fluxo SABA → autorização → callback → tokens conecta exclusivamente a conta. Transportadoras, serviços, seguro e unidades são administrados no SABA.

O callback exibido no painel precisa ser idêntico ao cadastrado no aplicativo. `MELHOR_ENVIO_CALLBACK_URL` permite configurar o endereço público atrás de proxy. A mesma URI armazenada na sessão é enviada na troca do code. State é aleatório, descartável, vinculado à sessão e válido por dez minutos; ambiente e Client ID são conferidos novamente no callback.

Scopes: `cart-write`, `orders-read`, `shipping-calculate`, `shipping-cancel`, `shipping-checkout`, `shipping-companies`, `shipping-generate`, `shipping-print`, `shipping-tracking`, `ecommerce-shipping`.

Client Secret e tokens usam casts criptografados e são ocultos na serialização. O secret salvo nunca preenche novamente um campo Livewire. O refresh é protegido por trava compartilhada e ocorre no vencimento ou após 401, com uma única repetição autenticada. Trocar ambiente, Client ID ou secret invalida os tokens. Uma operação histórica não usa credenciais de outro ambiente. A migration de compatibilidade vincula registros legados à integração/ambiente vigente no upgrade, sem alterar nomes, preços ou JSON histórico. Pedidos anteriores com volumes completos continuam podendo gerar etiquetas a partir dos itens históricos. Se o ambiente já havia sido alterado antes do upgrade, conferir os vínculos antes de operar os envios antigos.

Todas as chamadas incluem `User-Agent: Nome da aplicação (email técnico)`. Produção e sandbox usam URLs distintas. Credenciais reais não são necessárias para executar os testes.

## Transportadoras, serviços e agências

**Sincronizar transportadoras** consulta:

- `GET /api/v2/me/shipment/companies`
- `GET /api/v2/me/shipment/services`

O catálogo usa IDs externos, nunca comparação de nomes. Transportadoras são identificadas por integração + ambiente + external ID; serviços pertencem à transportadora. Novos registros começam desabilitados. Sincronizações preservam escolhas locais; uma resposta completa pode tornar itens ausentes indisponíveis. Falha em qualquer consulta não substitui o catálogo anterior.

Cada transportadora configura serviços exibidos no checkout, serviços com compra automática e sua unidade padrão. Serviços novos começam em operação manual: antes de habilitar compra automática, homologar a capacidade comercial do serviço. Serviços exclusivamente de cotação permanecem manuais; não se presume que todos os serviços de uma transportadora suportem compra comercial. A API nem sempre publica todas as capacidades em seu catálogo.

**Atualizar unidades** usa `GET /api/v2/me/shipment/agencies`, com `company`, `country`, `state` e `city`. Unidades ficam em `shipping_agencies` e a seleção padrão pertence à transportadora. O filtro company aceita ID textual ou inteiro; país/UF são normalizados para maiúsculas e cidade é aparada. A transportadora precisa estar sincronizada no ambiente atual. O array oficial `companies` vincula cada agência a todas as transportadoras conhecidas pelo pivot `shipping_agency_carrier`; uma mesma unidade pode atender várias empresas sem perder identidade ou vínculos anteriores. Seleção e preparação do envio validam esse vínculo e o ambiente. Uma agência de outra empresa/ambiente é rejeitada. Uma resposta filtrada não desativa agências fora daquele filtro. Erros de validação são exibidos junto aos campos; falhas HTTP recebem uma mensagem objetiva sem JSON ou credenciais. Listagens incompletas/paginadas exigem refinar os filtros, evitando tratar uma página como catálogo completo.

Metadata do serviço/empresa conserva regras publicadas pela API: `supports_purchase`, `agency_required`, `supports_agency`, `has_grouped_volumes`, `insurance_required` e `additional_services`, quando disponibilizadas. Não existem IDs de empresas/agências embutidos no código. Regras ausentes usam comportamento conservador: operação manual inicialmente, um envio por pacote, sem adicionais não comprovados e seguro mantido quando sua dispensa não é conhecida. Uma unidade escolhida pode ser mantida como preferência operacional, mas só é enviada ao Melhor Envio quando a regra sincronizada do serviço realmente exigir `agency`. Não há exigência global de agência para Melhor Envio.

## Cotação e snapshot

A agregação isola falhas individuais e ordena por centavos, prazo e nome. O adapter solicita explicitamente os IDs locais habilitados em `services` e filtra também a resposta. Seguro e adicionais são aplicados na cotação e preservados para a compra posterior.

Dinheiro permanece inteiro no domínio. `ApiMoney` converte decimais externos e gera números JSON exatos em reais sem float. Dimensões e peso mantêm as unidades exigidas pela API (cm/kg).

O cache inclui produtos, medidas, quantidades, preços, CEPs, providers e revisão das configurações. Alterações administrativas invalidam a revisão. Falhas parciais não ficam em cache. Peso ou dimensões inválidos impedem cotação automática do carrinho inteiro; nunca são inventadas medidas para completar dados ausentes.

Sem opção disponível, mostrar **Frete sob consulta**, contato da loja e bloquear finalização/pagamento, preservando o carrinho. Uma falha no Melhor Envio não oculta retirada. Antes de criar o pedido, a seleção é validada no backend, e somente valores e identidade retornados pelo servidor são persistidos. Nenhuma recotação ocorre ao criar etiquetas.

## Preparação e geração de etiquetas

Em pedido em preparação com pagamento aprovado, **Gerar etiqueta** abre **Preparar envio**. O operador escolhe individualmente entre **Nota Fiscal (NF-e)** e **Declaração de conteúdo**. NF-e exige chave comercial de 44 dígitos. Na declaração, `options.invoice` não é enviado; os produtos, quantidades e valores vêm automaticamente do snapshot imutável do pedido. Uma chave DC-e só aparece dentro da opção avançada **Já possui uma DC-e emitida externamente?** e permanece opcional. A unidade de postagem aparece somente quando o serviço suportar/exigir.

A escolha é persistida em `orders.shipping_document_type` antes da primeira operação financeira e não pode ser trocada depois que já existe frete remoto. O remetente vem de `store_settings`: razão/nome, CNPJ, IE, CNAE quando aplicável, telefone, email e endereço. No envio comercial a IE é exigida. Na declaração de conteúdo, o adapter não envia uma IE ativa: `from.state_register` fica vazio ou `ISENTO`, conforme a configuração. A interface alerta que declaração só deve ser usada quando o envio puder legalmente ocorrer sem NF-e; a aplicação não tenta substituir regras fiscais da transportadora/SEFAZ.

Fluxo persistido por shipment:

`pending → cart_created → purchased → label_generated → posted → delivered`

Endpoints:

1. `POST /api/v2/me/cart`
2. `POST /api/v2/me/shipment/checkout`
3. `POST /api/v2/me/shipment/generate`

Todos os slots são criados antes da primeira compra. Volumes e produtos vêm do snapshot, sem consultar preço atual do catálogo. Serviços que não agrupam volumes geram uma etiqueta por pacote. A soma do preço pago nos slots continua igual ao frete contratado; custo do provider permanece separado.

Cada tentativa possui UUID de operação, constraint única e fase persistida antes da chamada. Travas compartilhadas e transações curtas com `lockForUpdate` impedem concorrência. Chamadas externas ficam fora de transações repetíveis; operações de compra não têm retry genérico.

Depois da compra, falha de geração conserva `purchased`; repetir a ação gera a mesma etiqueta. Resultado incerto de criação/compra conserva a fase em andamento e bloqueia nova compra. Na interface, o usuário vê **Verificar envio**: o sistema usa `GET /api/v2/me/orders` para localizar a operação pela tag exclusiva `saba-operation:<UUID>` quando ainda não existe ID local, confirma o candidato com `GET /api/v2/me/orders/{id}` e valida também o serviço. Se o ID já for conhecido, consulta diretamente os detalhes. Um envio de outra operação é recusado. Uma criação de carrinho confirmada pode retomar o fluxo; uma compra ainda `pending` permanece bloqueada até o Melhor Envio confirmar `released` ou estado posterior. Não existe botão para apagar a incerteza e comprar novamente às cegas. O termo técnico de conciliação permanece apenas no backend.

Quando todas as etiquetas estão geradas, pedido fica **Pronto para envio**, sem antecipar postagem. Serviços manuais permitem marcar o envio sem comprar etiqueta pela API.

## Impressão

PDF é o padrão; PDF/ZPL/JPEG usam `GET /api/v2/me/imprimir/{formato}/{id}`. O SABA autentica a requisição no servidor e entrega o arquivo por rota administrativa com cache privado. Binários não são gravados no banco. O endpoint `POST /api/v2/me/shipment/print` continua disponível para links de impressão quando necessário.

O sandbox possui limitações de formatos/serviços que precisam ser verificadas na homologação; erro de impressão não altera compra ou pedido.

## Webhook, rastreamento e status

Rota pública: `POST /webhooks/shipping/melhor-envio`, excluída de CSRF. Assinatura `X-ME-Signature` é HMAC-SHA256/base64 do corpo bruto com o Client Secret; comparação usa `hash_equals`.

Eventos são registrados em `shipment_events` com chave única, ocorrência e processamento. Reenvios não duplicam histórico. Eventos desconhecidos são registrados sem inventar estado; eventos atrasados não desfazem postagem/entrega.

- `order.generated`: etiqueta gerada.
- `order.posted` / `order.received`: postagem, mesmo sem código de rastreio.
- `order.delivered`: entrega do shipment.
- `order.cancelled`: cancela somente o shipment.
- `order.paused`, `order.suspended`, `order.undelivered`: ocorrência operacional.

O pedido só fica **Enviado** com todos os slots postados, e **Entregue** com todos entregues. O cliente vê os envios individuais, transportadora, serviço, rastreio opcional e últimos eventos. O Kanban conserva as sete colunas principais e mantém cancelados fora do fluxo.

**Atualizar rastreamento** usa `POST /api/v2/me/shipment/tracking` como consulta de fallback; webhook continua fonte principal. Ações comuns não marcam manualmente um frete automático como enviado/entregue. A contingência explícita exige confirmação e justificativa auditável.

Nomes públicos do enum: `RECEIVED` e `READY_TO_SHIP`. Valores persistidos `pending` e `ready_for_shipping` e aliases PHP antigos são preservados.

## Cancelamento e reemissão

Consultar `POST /api/v2/me/shipment/cancellable` antes de `POST /api/v2/me/shipment/cancel`. Recusa gera mensagem administrativa. Cancelamento logístico não cancela a venda.

**Preparar novo envio** cria nova tentativa para o mesmo slot cancelado, com UUID novo, histórico e confirmação. Repetir essa preparação não cria mais tentativas. O pedido volta à preparação quando necessário. Uma compra posterior é uma operação explícita para substituir o frete cancelado.

## Retirada na loja

`LocalPickupProvider` é um provider real de cotação, sem etiqueta ou tracking. Configura nome, endereço, instruções e dias de preparação. Cotação zero; seus dados são snapshotted no pedido.

Fluxo: `PAID → PROCESSING → READY_FOR_PICKUP → DELIVERED`. Não cria shipment de transportadora nem exige rastreio.

## Como implementar SuperFrete

1. Criar adapter em `Infrastructure/Shipping`, com client e DTOs próprios; registrar a classe em `config/shipping.php`.
2. Implementar `ShippingProvider` e `ShippingCapabilitiesProvider`; implementar `ShipmentProvider`, `ShipmentLabelProvider` e/ou `ShipmentTrackingProvider` somente para operações suportadas.
3. Normalizar cotações em `ShippingOption`, usando centavos, identidade externa e volumes necessários à compra.
4. Configurar catálogo local/credenciais com Actions e criptografia, sem criar condicionais no checkout ou Kanban.
5. Preservar o protocolo de fases persistidas, verificação segura de operações incertas e idempotência. Implementar autenticação/deduplicação dos webhooks conforme documentação própria.
6. Adicionar testes com HTTP fake e proibição de chamadas externas; habilitar o card apenas depois do adapter real estar implementado.

O mesmo procedimento vale para Correios. Nenhuma das duas APIs está implementada nesta entrega.

## Validação e aplicação

Aplicar somente migrations novas com `./vendor/bin/sail artisan migrate`; não usar `migrate:fresh` no banco da loja. Executar testes, Pint e build antes da aplicação. Testes de MySQL/concorrência devem apontar para banco isolado; `RefreshDatabase`/`DatabaseMigrations` apagam dados nesse banco de testes.

Homologação com credenciais reais: autorização com a opção de instalação desmarcada, catálogo acessível, regras específicas de cada serviço/agência, compra comercial, assinaturas de webhook e formatos de impressão. Restrições impostas pelo servidor do Melhor Envio não podem ser removidas pelo SABA.

## Frete assistido e conclusão na loja

Quando não há entrega automática para um CEP válido, o cliente pode solicitar cotação (também se houver somente retirada). O servidor valida novamente a elegibilidade, cria o pedido recebido sem pagamento e reserva o estoque controlado por 24 horas. `shipping_cents` e `total_cents` permanecem nulos até a publicação: a interface mostra **A definir**. Não existe frete fictício de zero reais nem criação de etiqueta nessa etapa.

No pedido administrativo, **Informar cotação de entrega** registra transportadora, serviço, preço em centavos, prazo, instruções e validade (padrão sete dias). Cada publicação cria uma revisão em `order_shipping_quotes` e substitui a anterior. O funcionário copia o link ou abre a mensagem de WhatsApp; nenhum envio é automático. O telefone nacional do cliente recebe o DDI 55 nesse link.

O link assinado tem a validade da cotação e permite revisar o snapshot e confirmar o pagamento de demonstração. Cada requisição Livewire revalida assinatura, revisão, vencimento e proprietário. Pedidos de clientes cadastrados exigem login do titular; convidados usam o link protegido como capacidade de acesso. GET não aceita a cotação nem cobra. Revisar o frete não renova a reserva. Depois das 24 horas, a confirmação só continua se ainda houver estoque. Publicações e pagamentos travam o pedido para evitar que uma revisão seja alterada durante a confirmação.

Situação do frete: `awaiting_quote`, `quoted`, `confirmed`, separada de `OrderStatus`. O pedido só avança para pago após definição e aceitação do frete. O provider `manual` declara suas capacidades e nunca produz cotações públicas; esses envios seguem o fluxo manual existente. O gateway continua sendo de demonstração: nenhum gateway financeiro real foi integrado.

## Formatos oficiais e Livewire 4

O endpoint de cálculo pode retornar um objeto de serviço único, lista ou envelope `data`. O adapter normaliza esses formatos antes de validar IDs e montar `ShippingOption`. Uma falha isolada de serviço preserva as opções válidas e impede cache de resultado parcial. Rejeições de tamanho/peso e falhas técnicas recebem mensagens próprias sem resposta crua da API. O CEP usa `wire:model.live.blur`, que efetivamente envia a mudança ao servidor na versão instalada do Livewire 4.

## Validação de etiqueta e erros do Melhor Envio

A compra de etiqueta começa em `POST /api/v2/me/cart`. Respostas HTTP 422 são validações de dados, portanto não são tratadas como operação incerta e podem ser corrigidas e reenviadas sem criar uma nova compra. O client extrai apenas mensagens de erro seguras da resposta do provedor, mostra uma mensagem útil no admin e registra `provider`, endpoint, status, request ID e mensagem do provedor. Payloads, tokens e secrets não são registrados.

Para envio comercial, a chave de NF-e é validada localmente quanto a 44 dígitos, modelo 55 e dígito verificador. Isso não substitui a validação do Melhor Envio/SEFAZ: inclusive no Sandbox uma chave apenas inventada, ainda que estruturalmente válida, pode ser recusada. Para testar o fluxo sem possuir NF-e, use **Declaração de conteúdo** somente quando essa modalidade for aplicável ao envio. Nesse fluxo, `products` é sempre enviado corretamente e a DC-e externa é opcional; se ela não existir, `options.dce` também não é enviado.

A unidade de postagem escolhida pode ser mantida no SABA como referência operacional. No payload do Melhor Envio, `agency` só é enviado quando o serviço sincronizado indicar `agency_required`; serviços com unidade apenas opcional não recebem esse parâmetro. Isso evita rejeições do `/cart` em cenários OAuth/Sandbox em que a API não espera agência para o serviço selecionado.


## Monitoramento automático e central de notificações — 2026-10-21

O estado do frete também é consultado pelo job agendado `saba:monitor-shipments` (consulta read-only ao Melhor Envio, sem repetir compras). A fila executa os jobs individuais e o Filament apresenta alertas persistentes de pagamento aprovado/ação fiscal, etiquetas prontas, postagem e entrega. O monitor respeita ambientes e verifica a tag da operação antes de atualizar estados. Detalhes em `docs/monitoramento-envios-notificacoes.md`.
