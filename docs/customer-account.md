# Área do cliente

A loja possui um guard separado `customer`, independente dos usuários administrativos do Filament.

## Fluxo

- O cliente pode comprar como visitante.
- Ao criar uma conta na mesma sessão e com o mesmo e-mail do checkout, pedidos visitantes daquela sessão são vinculados à conta.
- Pedidos feitos enquanto autenticado recebem `customer_id` automaticamente.
- A área `/minha-conta` mostra histórico e acompanhamento dos pedidos do cliente autenticado.
- A tela de acompanhamento usa Livewire com atualização periódica e nunca consulta pedidos de outro cliente.

## Status de pedido

`pending`, `confirmed`, `processing`, `shipped`, `delivered`, `cancelled`.

A tela de acompanhamento exibe timeline, pagamento, endereço, itens e rastreio quando disponível.
