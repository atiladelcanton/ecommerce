<div wire:poll.15s class="grid gap-6" aria-live="polite">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->shipping_pricing_status && $order->status !== \App\Domain\Order\Enums\OrderStatus::CANCELLED): ?>
        <section class="mb-5 rounded-xl border border-store-border bg-white p-5">
            <h2 class="font-semibold"><?php echo e($order->shipping_pricing_status->label()); ?></h2>
            <p class="mt-2 text-sm text-store-muted">A reserva inicial de estoque dura 24 horas. Depois disso, a disponibilidade será conferida novamente ao concluir a compra.</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->latestShippingQuote && $order->latestShippingQuote->expires_at->isFuture() && $order->status === \App\Domain\Order\Enums\OrderStatus::RECEIVED): ?>
                <a class="store-button mt-4" href="<?php echo e(app(\App\Actions\Shipping\GetOrderShippingQuoteLinkAction::class)->execute($order->latestShippingQuote)); ?>">Revisar frete e concluir pedido</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <section class="store-account-card">
        <div class="store-order-tracking-header">
            <div class="min-w-0">
                <p class="store-eyebrow">Pedido <?php echo e($order->number); ?></p>
                <h1 class="store-title mt-2">Acompanhe seu pedido</h1>
                <p class="store-order-live-note mt-2">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 11a8 8 0 1 0-2.34 5.66" />
                        <path d="M20 5v6h-6" />
                    </svg>
                    Atualização automática a cada 15 segundos
                </p>
            </div>
            <div class="store-order-current-status">
                <span class="store-order-current-status-icon store-order-current-status-icon--<?php echo e($order->status->value); ?>" aria-hidden="true">
                    <?php if (isset($component)) { $__componentOriginal6449da563c37d54691e551bd375e2b81 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6449da563c37d54691e551bd375e2b81 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.order-status-icon','data' => ['status' => $order->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.order-status-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->status)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6449da563c37d54691e551bd375e2b81)): ?>
<?php $attributes = $__attributesOriginal6449da563c37d54691e551bd375e2b81; ?>
<?php unset($__attributesOriginal6449da563c37d54691e551bd375e2b81); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6449da563c37d54691e551bd375e2b81)): ?>
<?php $component = $__componentOriginal6449da563c37d54691e551bd375e2b81; ?>
<?php unset($__componentOriginal6449da563c37d54691e551bd375e2b81); ?>
<?php endif; ?>
                </span>
                <div class="min-w-0">
                    <span class="store-order-status store-order-status--<?php echo e($order->status->value); ?>"><?php echo e($order->status->label()); ?></span>
                    <p class="mt-1 text-xs text-store-muted">Atualizado em <?php echo e($order->updated_at->format('d/m/Y H:i')); ?></p>
                </div>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === \App\Domain\Order\Enums\OrderStatus::CANCELLED): ?>
            <div class="mt-6 rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-store-red-dark">Este pedido foi cancelado. Se precisar de ajuda, entre em contato com a loja.</div>
        <?php else: ?>
            <ol class="store-order-progress mt-8" style="--order-step-count: <?php echo e(count($steps)); ?>" aria-label="Progresso do pedido">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li
                        class="<?php echo e($step['complete'] ? 'is-complete' : ''); ?> <?php echo e($step['current'] ? 'is-current' : ''); ?> <?php echo e($step['status'] === \App\Domain\Order\Enums\OrderStatus::DELIVERED ? 'is-delivered' : ''); ?>"
                        <?php if($step['current']): ?> aria-current="step" <?php endif; ?>
                    >
                        <span class="store-order-progress-dot" aria-hidden="true">
                            <?php if (isset($component)) { $__componentOriginal6449da563c37d54691e551bd375e2b81 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6449da563c37d54691e551bd375e2b81 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.order-status-icon','data' => ['status' => $step['status']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.order-status-icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($step['status'])]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6449da563c37d54691e551bd375e2b81)): ?>
<?php $attributes = $__attributesOriginal6449da563c37d54691e551bd375e2b81; ?>
<?php unset($__attributesOriginal6449da563c37d54691e551bd375e2b81); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6449da563c37d54691e551bd375e2b81)): ?>
<?php $component = $__componentOriginal6449da563c37d54691e551bd375e2b81; ?>
<?php unset($__componentOriginal6449da563c37d54691e551bd375e2b81); ?>
<?php endif; ?>
                        </span>
                        <span class="store-order-progress-copy">
                            <span class="store-order-progress-label"><?php echo e($step['label']); ?></span>
                            <span class="store-order-progress-description"><?php echo e($step['description']); ?></span>
                        </span>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ol>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </section>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        <section class="store-account-card min-w-0">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-semibold">Itens do pedido</h2>
                <span class="text-sm text-store-muted"><?php echo e($order->items->sum('quantity')); ?> item(ns)</span>
            </div>
            <ul class="mt-4 divide-y divide-store-border">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="flex justify-between gap-4 py-4 text-sm">
                        <div class="min-w-0">
                            <p class="font-semibold wrap-anywhere"><?php echo e($item->product_name); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->variant_value): ?><p class="mt-1 text-xs text-store-muted"><?php echo e($item->variant_name); ?>: <?php echo e($item->variant_value); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <p class="mt-1 text-xs text-store-muted"><?php echo e($item->quantity); ?> × <?php echo e(\App\Support\BrazilianMoney::display($item->unit_price)); ?></p>
                        </div>
                        <strong class="shrink-0"><?php echo e(\App\Support\BrazilianMoney::display($item->total)); ?></strong>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
            <dl class="mt-4 border-t border-store-border pt-4 text-sm">
                <div class="flex justify-between py-2"><dt>Subtotal</dt><dd><?php echo e(\App\Support\BrazilianMoney::display($order->subtotal_cents)); ?></dd></div>
                <div class="flex justify-between py-2"><dt>Frete</dt><dd><?php echo e($order->formattedShipping()); ?></dd></div>
                <div class="mt-2 flex justify-between border-t border-store-border pt-4 text-lg font-semibold"><dt>Total</dt><dd><?php echo e($order->formattedTotal()); ?></dd></div>
            </dl>
        </section>

        <aside class="grid gap-6">
            <section class="store-account-card">
                <h2 class="font-semibold">Entrega</h2>
                <p class="mt-3 text-sm font-medium"><?php echo e($order->shipping_name); ?></p>
                <p class="mt-1 text-xs leading-5 text-store-muted"><?php echo e($order->shipping_description); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === \App\Domain\Order\Enums\OrderStatus::READY_FOR_PICKUP): ?>
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">Seu pedido está pronto para retirada. Entre em contato com a SABA se precisar combinar o horário.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === \App\Domain\Order\Enums\OrderStatus::READY_FOR_SHIPPING): ?>
                    <div class="mt-4 rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">Seu pedido está embalado, com etiqueta gerada e aguardando a postagem na transportadora.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php ($shipmentAlert = $order->shipments->sortByDesc('id')->first(fn($shipment) => in_array($shipment->status->value, ['undelivered', 'paused', 'suspended'], true))); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shipmentAlert): ?>
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">A transportadora informou uma ocorrência no envio (<?php echo e($shipmentAlert->status->label()); ?>). A loja está acompanhando a situação.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\App\Domain\Shipping\OrderShipping::mode($order) === 'pickup'): ?>
                    <p class="mt-3 text-sm text-store-muted"><?php echo e($order->shipping_quote_snapshot['metadata']['address'] ?? ''); ?></p>
                    <p class="mt-2 text-sm text-store-muted"><?php echo e($order->shipping_quote_snapshot['metadata']['instructions'] ?? ''); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $order->shipments->sortByDesc('attempt')->unique('fulfillment_key'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="mt-4 rounded-lg border border-store-border p-4 text-sm">
                        <p class="font-semibold"><?php echo e($shipment->carrier_name); ?> · <?php echo e($shipment->service_name); ?></p>
                        <p class="mt-1"><?php echo e($shipment->status->label()); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shipment->tracking_code): ?><p class="mt-2">Rastreio: <?php echo e($shipment->tracking_code); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shipment->tracking_url): ?><a class="store-text-link mt-2 inline-flex" href="<?php echo e($shipment->tracking_url); ?>" target="_blank" rel="noopener noreferrer">Rastrear entrega ↗</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <ul class="mt-3 grid gap-2 text-xs text-store-muted">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $shipment->events->sortByDesc('occurred_at')->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($event->status && \App\Domain\Shipping\Enums\ShipmentStatus::tryFrom($event->status)): ?>
                                    <li><?php echo e($event->occurred_at?->format('d/m/Y H:i')); ?> · <?php echo e(\App\Domain\Shipping\Enums\ShipmentStatus::from($event->status)->label()); ?></li>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </ul>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->tracking_code): ?>
                    <div class="mt-4 rounded-lg bg-store-soft p-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->carrier): ?><p class="mb-2 text-xs text-store-muted">Transportadora: <strong class="text-store-ink"><?php echo e($order->carrier); ?></strong></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <p class="text-xs text-store-muted">Código de rastreio</p>
                        <p class="mt-1 font-semibold"><?php echo e($order->tracking_code); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->tracking_url): ?><a class="store-text-link mt-2 inline-flex" href="<?php echo e($order->tracking_url); ?>" target="_blank" rel="noopener noreferrer">Rastrear entrega ↗</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->address): ?>
                    <address class="mt-4 text-sm leading-6 not-italic text-store-muted">
                        <?php echo e($order->address->street); ?>, <?php echo e($order->address->number); ?><br>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->address->complement): ?><?php echo e($order->address->complement); ?><br><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php echo e($order->address->neighborhood); ?><br>
                        <?php echo e($order->address->city); ?> / <?php echo e($order->address->state); ?> — CEP <?php echo e(\App\Support\BrazilianData::postalCode($order->address->postal_code)); ?>

                    </address>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>

            <section class="store-account-card">
                <h2 class="font-semibold">Pagamento</h2>
                <p class="mt-3"><span class="store-payment-status store-payment-status--<?php echo e($order->payment_status->value); ?>"><?php echo e($order->payment_status->label()); ?></span></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->payments->last()?->is_demo): ?>
                    <p class="mt-3 text-xs leading-5 text-store-muted">Pagamento simulado no ambiente de demonstração.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>

            <section class="store-account-card">
                <h2 class="font-semibold">Histórico</h2>
                <ol class="mt-4 grid gap-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $order->statusHistories->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li class="relative border-l border-store-border pl-4 text-sm">
                            <span class="absolute -left-1.5 top-1 size-3 rounded-full bg-store-red" aria-hidden="true"></span>
                            <p class="font-medium"><?php echo e($history->to_status instanceof \App\Domain\Order\Enums\OrderStatus ? $history->to_status->label() : ucfirst((string) $history->to_status)); ?></p>
                            <p class="mt-1 text-xs leading-5 text-store-muted"><?php echo e($history->description); ?></p>
                            <time class="mt-1 block text-xs text-store-muted"><?php echo e($history->created_at->format('d/m/Y H:i')); ?></time>
                        </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ol>
            </section>
        </aside>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/store/customer-order-tracking.blade.php ENDPATH**/ ?>