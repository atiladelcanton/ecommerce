# Instalação — Monitoramento automático e notificações do SABA

Esta atualização adiciona leitura periódica de status no Melhor Envio, atualização automática na tela de detalhes do pedido e alertas persistentes na central do Filament.

## Aplicação segura

1. Faça backup do código e do banco. Utilize o ZIP **somente arquivos alterados** para atualizar a instalação existente; não substitua `.env`, imagens, logs ou banco.
2. Extraia na raiz do Laravel, preservando os diretórios.
3. Execute:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan optimize:clear
./vendor/bin/sail artisan queue:restart
./vendor/bin/sail artisan saba:monitor-shipments
./vendor/bin/sail artisan saba:sync-order-alerts
./vendor/bin/sail artisan test --filter=AutomaticShipmentMonitoringTest
./vendor/bin/sail artisan test
./vendor/bin/sail pint --test
```

A migration é incremental: cria a tabela `notifications` se não houver e adiciona `shipments.monitor_checked_at`. Nunca rode `migrate:fresh` na loja.

## Serviços necessários

Em desenvolvimento, em dois terminais separados:

```bash
./vendor/bin/sail artisan schedule:work
./vendor/bin/sail artisan queue:work --queue=default --tries=3 --timeout=90
```

`QUEUE_CONNECTION=database` é uma opção válida se a tabela de jobs já estiver migrada. Em produção, configure cron `schedule:run` por minuto e `queue:work` sob um gerenciador de processos.

## Comportamento esperado

- O monitor agenda verificações a cada minuto; cada envio é consultado no máximo a cada 2 minutos, observada a disponibilidade do worker e da API.
- O job **apenas consulta** os envios no Melhor Envio; ele **não** compra frete, gera etiqueta, imprime, posta, emite NF-e ou altera estoque.
- Apenas após a API confirmar que **todas as etiquetas** foram geradas, o pedido passa para **Pronto para envio**.
- O sino nativo do Filament exibe notificações persistentes e atualiza o contador aproximadamente a cada 20 segundos.
- A página administrativa de detalhes do pedido consulta o próprio SABA a cada 20 segundos, sem necessidade de recarregar o navegador.
- Quando o pagamento é aprovado, há aviso sobre a emissão de NF-e nos pedidos de envio automático com essa modalidade fiscal. O sistema não emite notas sozinho.

**Acompanhe:** `storage/logs/laravel.log` por `Automatic shipment status updated.` ou `Automatic shipment status check failed.`. Em caso de falha o envio mantém o estado anterior para a próxima checagem.

Confira as regras e os testes adicionais em `docs/monitoramento-envios-notificacoes.md`.
