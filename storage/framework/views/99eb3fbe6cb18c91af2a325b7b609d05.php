<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div wire:poll.20s class="space-y-5">
        <section class="fi-section p-4 lg:p-5">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                <div class="xl:col-span-2">
                    <label class="mb-1 block text-xs font-semibold text-zinc-500">Buscar</label>
                    <input wire:model.live.debounce.350ms="search" type="search" placeholder="Pedido, cliente, e-mail ou documento" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm outline-none transition focus:border-red-400 focus:ring-2 focus:ring-red-100">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-zinc-500">Pagamento</label>
                    <select wire:model.live="paymentStatus" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm">
                        <option value="">Todos</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $paymentStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($value); ?>"><?php echo e($label); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-zinc-500">Entrega</label>
                    <select wire:model.live="shippingMethod" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm">
                        <option value="">Todas</option>
                        <option value="standard">Entrega padrão</option>
                        <option value="pickup">Retirada</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-zinc-500">De</label>
                    <input wire:model.live="fromDate" type="date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-zinc-500">Até</label>
                    <input wire:model.live="toDate" type="date" class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm">
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between gap-3 text-xs text-zinc-500">
                <button wire:click="resetFilters" type="button" class="font-semibold text-red-600 hover:text-red-700">Limpar filtros</button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cancelledCount): ?><span><?php echo e($cancelledCount); ?> pedido(s) cancelado(s) fora do fluxo principal</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <div class="saba-kanban-grid overflow-x-auto pb-3">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php ($status = $column['status']); ?>
                <section class="saba-kanban-column">
                    <header class="saba-kanban-column-header">
                        <div>
                            <p class="saba-kanban-column-title"><?php echo e($status->label()); ?></p>
                            <p class="saba-kanban-column-subtitle"><?php echo e($column['orders']->count()); ?> pedido(s)</p>
                        </div>
                        <span class="saba-kanban-dot saba-kanban-dot--<?php echo e($status->value); ?>"></span>
                    </header>

                    <div class="saba-kanban-stack">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $column['orders']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="saba-kanban-card" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'kanban-order-'.e($order->id).''; ?>wire:key="kanban-order-<?php echo e($order->id); ?>">
                                <a href="<?php echo e(\App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order])); ?>" class="block">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-bold text-zinc-950"><?php echo e($order->number); ?></p>
                                            <p class="mt-1 line-clamp-1 text-sm text-zinc-600"><?php echo e($order->customer_name); ?></p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-zinc-100 px-2 py-1 text-[11px] font-semibold text-zinc-600"><?php echo e($order->items->sum('quantity')); ?> item(ns)</span>
                                    </div>
                                    <div class="mt-4 flex items-end justify-between gap-3">
                                        <div>
                                            <p class="text-xs text-zinc-400"><?php echo e($order->created_at->diffForHumans()); ?></p>
                                            <p class="mt-1 text-xs text-zinc-500"><?php echo e($order->shipping_option_id === 'pickup' ? 'Retirada' : $order->shipping_name); ?></p>
                                        </div>
                                        <strong class="text-sm text-zinc-950"><?php echo e(\App\Support\BrazilianMoney::display($order->total_cents)); ?></strong>
                                    </div>
                                </a>

                                <div class="mt-4 flex flex-wrap gap-2 border-t border-zinc-100 pt-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === \App\Domain\Order\Enums\OrderStatus::PENDING): ?>
                                        <span class="text-xs font-medium text-zinc-400">Aguardando pagamento</span>
                                    <?php elseif($status === \App\Domain\Order\Enums\OrderStatus::PAID): ?>
                                        <button wire:click="moveOrder(<?php echo e($order->id); ?>, 'processing')" type="button" class="saba-kanban-action">Iniciar preparação</button>
                                    <?php elseif($status === \App\Domain\Order\Enums\OrderStatus::PROCESSING): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\App\Domain\Shipping\OrderShipping::mode($order) === 'pickup'): ?>
                                            <button wire:click="moveOrder(<?php echo e($order->id); ?>, 'ready_for_pickup')" type="button" class="saba-kanban-action">Pronto para retirada</button>
                                        <?php elseif(\App\Domain\Shipping\OrderShipping::mode($order) === 'automatic'): ?>
                                            <a href="<?php echo e(\App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order])); ?>" class="saba-kanban-action">Gerar etiqueta</a>
                                        <?php else: ?>
                                            <button wire:click="openShippingModal(<?php echo e($order->id); ?>)" type="button" class="saba-kanban-action">Marcar enviado</button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php elseif($status === \App\Domain\Order\Enums\OrderStatus::READY_FOR_SHIPPING): ?>
                                        <p class="w-full text-xs text-zinc-500"><?php echo e($order->shipping_quote_snapshot['carrier_name'] ?? $order->carrier); ?> · <?php echo e($order->shipping_name); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $order->shipments->whereNotNull('generated_at')->whereNull('cancelled_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <a class="saba-kanban-action" href="<?php echo e(route('admin.shipping.labels.download', ['shipment' => $shipment, 'format' => $shipment->label_format ?? 'pdf'])); ?>" target="_blank">Etiqueta gerada #<?php echo e($shipment->id); ?> · Imprimir</a>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shipment->tracking_code): ?><span class="text-xs text-zinc-500"><?php echo e($shipment->tracking_code); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <a href="<?php echo e(\App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order])); ?>" class="saba-kanban-action">Imprimir etiqueta</a>
                                        <span class="text-xs font-medium text-zinc-400">Aguardando postagem</span>
                                    <?php elseif(in_array($status, [\App\Domain\Order\Enums\OrderStatus::SHIPPED, \App\Domain\Order\Enums\OrderStatus::READY_FOR_PICKUP], true)): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\App\Domain\Shipping\OrderShipping::mode($order) !== 'automatic'): ?>
                                        <button wire:click="moveOrder(<?php echo e($order->id); ?>, 'delivered')" type="button" class="saba-kanban-action">Marcar entregue</button>
                                        <?php else: ?>
                                            <span class="text-xs text-zinc-500">Entrega confirmada pela transportadora</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-xs font-medium text-green-700">Fluxo concluído</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($status, [\App\Domain\Order\Enums\OrderStatus::PENDING, \App\Domain\Order\Enums\OrderStatus::PAID, \App\Domain\Order\Enums\OrderStatus::PROCESSING], true)): ?>
                                        <button wire:click="cancel(<?php echo e($order->id); ?>)" wire:confirm="Cancelar este pedido?" type="button" class="text-xs font-semibold text-zinc-400 transition hover:text-red-600">Cancelar</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="rounded-xl border border-dashed border-zinc-200 bg-white/60 px-4 py-8 text-center text-xs text-zinc-400">Nenhum pedido nesta etapa.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </section>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showShippingModal): ?>
        <div class="fixed inset-0 z-50 grid place-items-center bg-zinc-950/40 p-4" role="dialog" aria-modal="true" aria-label="Informar rastreio">
            <div class="w-full max-w-lg rounded-xl border border-zinc-200 bg-white p-6 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Envio</p>
                        <h2 class="mt-1 text-xl font-semibold text-zinc-950">Informar rastreio</h2>
                        <p class="mt-1 text-sm text-zinc-500">O cliente verá essas informações imediatamente na área dele.</p>
                    </div>
                    <button wire:click="closeShippingModal" type="button" class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700" aria-label="Fechar">✕</button>
                </div>
                <div class="mt-6 grid gap-4">
                    <label class="grid gap-1 text-sm font-medium text-zinc-700">Transportadora
                        <input wire:model="carrier" type="text" class="rounded-lg border border-zinc-200 px-3 py-2" placeholder="Ex.: Correios">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['carrier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </label>
                    <label class="grid gap-1 text-sm font-medium text-zinc-700">Código de rastreio
                        <input wire:model="trackingCode" type="text" class="rounded-lg border border-zinc-200 px-3 py-2" placeholder="Código fornecido pela transportadora">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['trackingCode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </label>
                    <label class="grid gap-1 text-sm font-medium text-zinc-700">Link de rastreio <span class="text-xs font-normal text-zinc-400">opcional</span>
                        <input wire:model="trackingUrl" type="url" class="rounded-lg border border-zinc-200 px-3 py-2" placeholder="https://...">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['trackingUrl'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </label>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="closeShippingModal" type="button" class="rounded-lg border border-zinc-200 px-4 py-2 text-sm font-semibold text-zinc-600 hover:bg-zinc-50">Cancelar</button>
                    <button wire:click="confirmShipping" wire:loading.attr="disabled" type="button" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60">Confirmar envio</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH /home/atila/projects/saba-ecommerce-starter/resources/views/filament/pages/orders-kanban.blade.php ENDPATH**/ ?>