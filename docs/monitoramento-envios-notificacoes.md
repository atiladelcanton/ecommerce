# SABA — Monitoramento automático do Melhor Envio e Central de Notificações

## Objetivo

- Atualizar automaticamente estados de envio no backend sem que o operador precise clicar em "Verificar envio".
- Usar a central nativa de notificações persistentes do Filament 5 (ícone de sino/abertura lateral), com atualização automática do contador.
- Alertar após pagamento aprovado para emissão de NF-e **quando o pedido utiliza NF-e**; o SABA não emite NF-e automaticamente.
- Alertar quando **todas as etiquetas** estão geradas e disponíveis para impressão, além de mudanças para postado e entregue.

## Fluxo

1. O cron Laravel executa `saba:monitor-shipments` **a cada minuto** com trava de sobreposição.
2. A Action `DispatchShipmentMonitoringAction` seleciona envios elegíveis do Melhor Envio, com intervalo mínimo de 2 minutos por envio, e enfileira `CheckShipmentStatusJob` único por shipment.
3. O job faz **somente leitura** por `GET /api/v2/me/orders/{id}` através do provider atual. Se a operação antiga não possui ID externo, usa busca por tag de operação; jamais cria carrinho, compra frete ou regenera etiqueta.
4. `ReconcileShipmentAction` exige tag e serviço iguais aos já persistidos e reaproveita `ProcessShippingWebhookAction` para estados e histórico. Eventos antigos não fazem regredir o status.
5. `ProjectOrderShippingStatusAction` só move o pedido para **Pronto para envio** quando todas as etiquetas estiverem confirmadas, e para **Enviado** somente após confirmação da postagem.
6. `TransitionOrderStatusAction` grava alerta persistente, em uma única transação com o histórico, para as mudanças: Pago, Pronto para envio, Enviado, Entregue. Chave determinística por pedido/marco/administrador evita duplicados mesmo após reprocessamentos.

## Instalação

Atualize os arquivos e, depois de fazer backup, execute:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail artisan queue:restart
./vendor/bin/sail artisan saba:monitor-shipments
./vendor/bin/sail artisan saba:sync-order-alerts
./vendor/bin/sail artisan test --filter=AutomaticShipmentMonitoringTest
```

Em ambiente de desenvolvimento, mantenha **dois processos** ativos:

```bash
./vendor/bin/sail artisan schedule:work
./vendor/bin/sail artisan queue:work --queue=default --tries=3 --timeout=90
```

Em produção, configure `schedule:run` via cron a cada minuto e mantenha `queue:work` sob supervisor; o comando agendado sozinho **não realiza** as chamadas HTTP, pois elas são processadas na fila. **Não use `QUEUE_CONNECTION=sync` em produção** para este módulo.

## Central no Filament

A cada quinze minutos, `saba:sync-order-alerts` recompõe avisos ainda relevantes dos últimos 30 dias (sem duplicação), inclusive pedidos já pagos ou prontos antes deste update.

`AdminPanelProvider` habilita `->databaseNotifications()->databaseNotificationsPolling('20s')`.
A página **Admin → Pedido** atualiza os dados automaticamente a cada 20 segundos (`wire:poll`), sem chamadas à API do Melhor Envio disparadas pelo navegador.
A tabela `notifications` contém alertas para os usuários administrativos existentes, com links para `/admin/orders/{id}`. Ler e marcar notificações são recursos nativos do Filament. Não há dependência de plugin, Pusher ou WebSocket.

**Tempo esperado:** após a transportadora atualizar o estado, o job pode levar até ~2 minutos para verificar o mesmo envio, mais o tempo da fila; o navegador consulta as notificações em intervalos de 20 segundos. Não é uma garantia de entrega em tempo real.

## Segurança operacional

- O webhook do Melhor Envio continua sendo o canal primário. O monitor é fallback e também acompanha os envios após a etiqueta gerada.
- Não infere postagem apenas pela etiqueta liberada (`released`). A geração precisa de `generated_at` ou `generated` na API.
- Não compra etiqueta no cron. Operações financeiras incertas continuam exigindo resolução segura pela Action existente.
- Falhas externas registram somente tipo de exceção, origem relativa e IDs internos, sem credenciais/endereços/clientes.
- Não emite NF-e, não cria documento fiscal nem realiza postagem automaticamente.
- As notificações são enviadas a todos os usuários administrativos existentes no momento da transição; o SABA não tem perfis finos de permissões nesta etapa.

## Verificação

1. Criar pedido de teste com pagamento aprovado: o sino deve apresentar aviso para preparar NF-e (se a modalidade do pedido for NF-e).
2. Gerar etiqueta na integração; se o SABA ainda mantiver estado `purchased`, aguardar a próxima execução do monitor sem clicar em "Verificar envio".
3. Após confirmação remota da geração de **todos** os volumes, o pedido muda para Pronto para envio e aparece um alerta para impressão/postagem.
4. Repetir consulta ou webhook: não duplicar alerta nem histórico de estado.
5. Confirmar postagem no Melhor Envio: a consulta automática muda para Enviado, sem confundir com a liberação da etiqueta.

**Atenção:** executar um scanner de status não confirma que a transportadora já informou a geração ou a postagem; nos ambientes de sandbox essa atualização pode ser simulada com atrasos.
