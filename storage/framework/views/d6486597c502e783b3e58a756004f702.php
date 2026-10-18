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

    <?php ($order = $this->record->loadMissing(['items', 'address', 'payments', 'statusHistories.user', 'shipments.events'])); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->shipping_pricing_status): ?>
        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['heading' => $order->shipping_pricing_status->label()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->shipping_pricing_status->label())]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

            <p class="text-sm text-gray-600">A solicitação reserva estoque por 24 horas. Depois desse prazo, a compra depende de uma nova verificação de disponibilidade.</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->latestShippingQuote && $order->latestShippingQuote->expires_at->isFuture() && ! $order->latestShippingQuote->superseded_at && $order->status === \App\Domain\Order\Enums\OrderStatus::RECEIVED): ?>
                <?php ($quoteLink = app(\App\Actions\Shipping\GetOrderShippingQuoteLinkAction::class)->execute($order->latestShippingQuote)); ?>
                <div class="mt-3 flex flex-wrap items-center gap-3" x-data="{ copied: false }" data-quote-link="<?php echo e($quoteLink); ?>">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['color' => 'gray','xOn:click' => 'navigator.clipboard.writeText($root.dataset.quoteLink).then(() => copied = true)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'gray','x-on:click' => 'navigator.clipboard.writeText($root.dataset.quoteLink).then(() => copied = true)']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
<span x-text="copied ? 'Link copiado' : 'Copiar link para o cliente'">Copiar link para o cliente</span> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
                    <a class="text-sm text-primary-600" href="<?php echo e(app(\App\Actions\Shipping\GetCustomerQuoteContactLinkAction::class)->execute($order->latestShippingQuote)); ?>" target="_blank" rel="noopener noreferrer">Compartilhar pelo WhatsApp</a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->shippingQuotes->isNotEmpty()): ?>
                <div class="mt-4 space-y-2 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $order->shippingQuotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <p>Revisão <?php echo e($quote->revision); ?> · <?php echo e($quote->carrier_name); ?> · <?php echo e(\App\Support\BrazilianMoney::display($quote->price_cents)); ?> · Válida até <?php echo e($quote->expires_at->format('d/m/Y H:i')); ?><?php echo e($quote->superseded_at ? ' · Substituída' : ''); ?><?php echo e($quote->accepted_at ? ' · Aceita' : ''); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div class="grid gap-6 xl:grid-cols-12">
        <section class="fi-section xl:col-span-4 p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Pedido</p>
                    <h2 class="mt-1 text-xl font-semibold text-zinc-900"><?php echo e($order->number); ?></h2>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold <?php echo e($order->status->color() === 'success' ? 'bg-green-50 text-green-700' : ($order->status->color() === 'warning' ? 'bg-amber-50 text-amber-700' : ($order->status->color() === 'danger' ? 'bg-red-50 text-red-700' : 'bg-zinc-100 text-zinc-700'))); ?>"><?php echo e($order->status->label()); ?></span>
            </div>
            <dl class="mt-6 grid gap-4 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">Criado em</dt><dd class="font-medium text-zinc-900"><?php echo e($order->created_at->format('d/m/Y H:i')); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">Pagamento</dt><dd class="font-medium text-zinc-900"><?php echo e($order->payment_status->label()); ?></dd></div>
                <div class="flex justify-between gap-4"><dt class="text-zinc-500">Itens</dt><dd class="font-medium text-zinc-900"><?php echo e($order->items->sum('quantity')); ?></dd></div>
                <div class="flex justify-between gap-4 border-t border-zinc-200 pt-4"><dt class="font-medium text-zinc-700">Total</dt><dd class="text-lg font-bold text-zinc-950"><?php echo e($order->formattedTotal()); ?></dd></div>
            </dl>
        </section>

        <section class="fi-section xl:col-span-4 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Cliente</p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-900"><?php echo e($order->customer_name); ?></h2>
            <dl class="mt-5 grid gap-3 text-sm">
                <div><dt class="text-zinc-500">E-mail</dt><dd class="mt-1 font-medium text-zinc-900"><?php echo e($order->customer_email); ?></dd></div>
                <div><dt class="text-zinc-500">Telefone</dt><dd class="mt-1 font-medium text-zinc-900"><?php echo e(\App\Support\BrazilianData::phone($order->customer_phone)); ?></dd></div>
                <div><dt class="text-zinc-500">CPF/CNPJ</dt><dd class="mt-1 font-medium text-zinc-900"><?php echo e(\App\Support\BrazilianData::document($order->customer_document)); ?></dd></div>
            </dl>
        </section>

        <section class="fi-section xl:col-span-4 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Entrega</p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-900"><?php echo e($order->shipping_name); ?></h2>
            <p class="mt-2 text-sm text-zinc-500"><?php echo e($order->shipping_description); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->shipping_provider): ?><p class="mt-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">Provider: <?php echo e(str_replace('_', ' ', ucfirst($order->shipping_provider))); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->address): ?>
                <address class="mt-5 text-sm leading-6 not-italic text-zinc-700">
                    <?php echo e($order->address->street); ?>, <?php echo e($order->address->number); ?><br>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->address->complement): ?><?php echo e($order->address->complement); ?><br><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php echo e($order->address->neighborhood); ?> — <?php echo e($order->address->city); ?>/<?php echo e($order->address->state); ?><br>
                    CEP <?php echo e(\App\Support\BrazilianData::postalCode($order->address->postal_code)); ?>

                </address>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->tracking_code): ?>
                <div class="mt-5 rounded-lg bg-zinc-50 p-4 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->carrier): ?><p><span class="text-zinc-500">Transportadora:</span> <strong><?php echo e($order->carrier); ?></strong></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <p class="mt-1"><span class="text-zinc-500">Rastreio:</span> <strong><?php echo e($order->tracking_code); ?></strong></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->tracking_url): ?><a href="<?php echo e($order->tracking_url); ?>" class="mt-2 inline-flex font-semibold text-red-600 hover:text-red-700" target="_blank" rel="noopener noreferrer">Abrir rastreio ↗</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        <section class="fi-section xl:col-span-8 p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Produtos</p>
                    <h2 class="mt-1 text-lg font-semibold text-zinc-900">Itens do pedido</h2>
                </div>
                <span class="text-sm text-zinc-500"><?php echo e($order->items->sum('quantity')); ?> item(ns)</span>
            </div>
            <div class="mt-5 divide-y divide-zinc-200">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="grid gap-3 py-4 sm:grid-cols-[minmax(0,1fr)_90px_130px] sm:items-center">
                        <div>
                            <p class="font-semibold text-zinc-900"><?php echo e($item->product_name); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->product_sku): ?><p class="mt-1 text-xs text-zinc-500">SKU <?php echo e($item->product_sku); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->variant_value): ?><p class="mt-1 text-xs text-zinc-500"><?php echo e($item->variant_name); ?>: <?php echo e($item->variant_value); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->shipping_measurements_snapshot): ?><p class="mt-1 text-xs text-zinc-500">Embalagem: <?php echo e($item->shipping_measurements_snapshot['weight'] ?? '—'); ?> kg · <?php echo e($item->shipping_measurements_snapshot['length'] ?? '—'); ?> × <?php echo e($item->shipping_measurements_snapshot['width'] ?? '—'); ?> × <?php echo e($item->shipping_measurements_snapshot['height'] ?? '—'); ?> cm</p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <p class="text-sm text-zinc-600"><?php echo e($item->quantity); ?> × <?php echo e(\App\Support\BrazilianMoney::display($item->unit_price)); ?></p>
                        <p class="text-right font-semibold text-zinc-900"><?php echo e(\App\Support\BrazilianMoney::display($item->total)); ?></p>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <dl class="ml-auto mt-5 max-w-sm border-t border-zinc-200 pt-4 text-sm">
                <div class="flex justify-between py-1.5"><dt class="text-zinc-500">Subtotal</dt><dd><?php echo e(\App\Support\BrazilianMoney::display($order->subtotal_cents)); ?></dd></div>
                <div class="flex justify-between py-1.5"><dt class="text-zinc-500">Frete</dt><dd><?php echo e($order->formattedShipping()); ?></dd></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->discount_cents > 0): ?><div class="flex justify-between py-1.5 text-green-700"><dt>Desconto</dt><dd>- <?php echo e(\App\Support\BrazilianMoney::display($order->discount_cents)); ?></dd></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="mt-2 flex justify-between border-t border-zinc-200 pt-3 text-base font-bold"><dt>Total</dt><dd><?php echo e($order->formattedTotal()); ?></dd></div>
            </dl>
        </section>

        <section class="fi-section xl:col-span-4 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Logística</p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-900">Envios e etiquetas</h2>
            <?php ($currentShipments = \App\Domain\Shipping\OrderShipping::currentShipments($order)); ?>
            <div class="mt-5 grid gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $currentShipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shipmentIndex => $shipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="rounded-lg border border-zinc-200 p-4 text-sm">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentShipments->count() > 1): ?>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">Volume <?php echo e($shipmentIndex + 1); ?> de <?php echo e($currentShipments->count()); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="flex items-center justify-between gap-3">
                            <strong><?php echo e($shipment->carrier_name ?: ucfirst(str_replace('_', ' ', $shipment->provider))); ?></strong>
                            <span class="rounded-full bg-zinc-100 px-2 py-1 text-[11px] font-semibold text-zinc-600"><?php echo e($shipment->status->label()); ?></span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shipment->service_name): ?><p class="mt-2 text-zinc-500"><?php echo e($shipment->service_name); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->shipping_document_type): ?>
                            <p class="mt-2"><span class="text-zinc-500">Documento:</span> <strong><?php echo e($order->shipping_document_type->label()); ?></strong></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->shipping_document_type === \App\Domain\Shipping\Enums\ShipmentDocumentType::INVOICE && $order->invoice_key): ?>
                                <p class="mt-1 break-all text-xs text-zinc-500">NF-e <?php echo e($order->invoice_key); ?></p>
                            <?php elseif($order->shipping_document_type === \App\Domain\Shipping\Enums\ShipmentDocumentType::CONTENT_DECLARATION && $order->dce_key): ?>
                                <p class="mt-1 break-all text-xs text-zinc-500">DC-e externa <?php echo e($order->dce_key); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shipment->tracking_code): ?><p class="mt-2"><span class="text-zinc-500">Rastreio:</span> <strong><?php echo e($shipment->tracking_code); ?></strong></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shipment->operation_phase): ?>
                            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-amber-900">
                                <p class="font-semibold">Não foi possível confirmar a última operação</p>
                                <p class="mt-1 text-xs leading-5">O envio foi pausado temporariamente para evitar uma cobrança duplicada. Use <strong>Verificar envio</strong> nas ações do pedido.</p>
                            </div>
                            <details class="mt-3 text-xs text-zinc-500">
                                <summary class="cursor-pointer font-medium text-zinc-600">Ver detalhes técnicos</summary>
                                <dl class="mt-2 grid gap-1 rounded-lg bg-zinc-50 p-3">
                                    <div><dt class="inline">Provedor:</dt> <dd class="inline"><?php echo e(str_replace('_', ' ', $shipment->provider)); ?></dd></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shipment->external_id): ?><div><dt class="inline">Identificador externo:</dt> <dd class="inline break-all"><?php echo e($shipment->external_id); ?></dd></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <div><dt class="inline">Etapa pendente:</dt> <dd class="inline"><?php echo e(str_replace('_', ' ', $shipment->operation_phase)); ?></dd></div>
                                </dl>
                            </details>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($shipment->generated_at && !$shipment->cancelled_at): ?>
                            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm">
                                <a class="inline-flex font-semibold text-red-600 hover:text-red-700" href="<?php echo e(route('admin.shipping.labels.open', ['shipment' => $shipment])); ?>" target="_blank" rel="noopener noreferrer">Abrir / imprimir etiqueta ↗</a>
                                <a class="inline-flex font-semibold text-zinc-600 hover:text-zinc-900" href="<?php echo e(route('admin.shipping.labels.download', ['shipment' => $shipment, 'format' => 'pdf'])); ?>">Baixar PDF</a>
                            </div>
                            <p class="mt-2 text-xs leading-5 text-zinc-400">No Sandbox, o Melhor Envio limita o download direto de arquivos a alguns serviços. Se o arquivo não estiver disponível, o sistema abrirá automaticamente a impressão oficial.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-sm text-zinc-500">Nenhuma etiqueta gerada para este pedido.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="fi-section xl:col-span-4 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Pagamentos</p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-900">Tentativas de pagamento</h2>
            <div class="mt-5 grid gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $order->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="rounded-lg border border-zinc-200 p-4 text-sm">
                        <div class="flex items-center justify-between gap-3"><strong><?php echo e(ucfirst($payment->gateway)); ?></strong><span class="text-xs font-semibold"><?php echo e($payment->status->label()); ?></span></div>
                        <p class="mt-2 text-zinc-500"><?php echo e(\App\Support\BrazilianMoney::display($payment->amount_cents)); ?></p>
                        <p class="mt-1 break-all text-xs text-zinc-400"><?php echo e($payment->external_id); ?></p>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-sm text-zinc-500">Nenhuma tentativa registrada.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="fi-section xl:col-span-12 p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Histórico</p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-900">Linha do tempo</h2>
            <ol class="mt-6 grid gap-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $order->statusHistories->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="relative border-l border-zinc-200 pb-6 pl-6 last:pb-0">
                        <span class="absolute -left-1.5 top-1.5 size-3 rounded-full bg-red-600 ring-4 ring-white"></span>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <strong class="text-sm text-zinc-900"><?php echo e($history->to_status->label()); ?></strong>
                            <time class="text-xs text-zinc-500"><?php echo e($history->created_at->format('d/m/Y H:i')); ?></time>
                        </div>
                        <p class="mt-1 text-sm text-zinc-600"><?php echo e($history->description); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($history->user): ?><p class="mt-1 text-xs text-zinc-400">Alterado por <?php echo e($history->user->name); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ol>
        </section>
    </div>
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
<?php /**PATH /var/www/html/resources/views/filament/resources/orders/pages/view-order.blade.php ENDPATH**/ ?>