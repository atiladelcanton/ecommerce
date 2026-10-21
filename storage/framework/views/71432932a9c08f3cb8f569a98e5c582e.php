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

    <?php ($customer = $this->record); ?>

    <div class="grid gap-6 xl:grid-cols-12">
        <section class="fi-section p-6 xl:col-span-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Cliente</p>
                    <h2 class="mt-1 text-xl font-semibold text-zinc-950"><?php echo e($customer->name); ?></h2>
                    <p class="mt-1 text-sm text-zinc-500">Cliente desde <?php echo e($customer->created_at->format('d/m/Y')); ?></p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->birth_date): ?>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-right">
                        <span class="block text-xs font-medium text-zinc-500">Data de nascimento</span>
                        <strong class="mt-1 block text-sm text-zinc-900"><?php echo e($customer->birth_date->format('d/m/Y')); ?></strong>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                <div><dt class="text-xs font-medium uppercase tracking-wide text-zinc-500">E-mail</dt><dd class="mt-1 text-sm font-medium text-zinc-900"><?php echo e($customer->email); ?></dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-zinc-500">Telefone</dt><dd class="mt-1 text-sm font-medium text-zinc-900"><?php echo e(\App\Support\BrazilianData::phone($customer->phone)); ?></dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-zinc-500">CPF/CNPJ</dt><dd class="mt-1 text-sm font-medium text-zinc-900"><?php echo e(\App\Support\BrazilianData::document($customer->document)); ?></dd></div>
                <div><dt class="text-xs font-medium uppercase tracking-wide text-zinc-500">Nascimento</dt><dd class="mt-1 text-sm font-medium text-zinc-900"><?php echo e($customer->birth_date?->format('d/m/Y') ?? 'Não informado'); ?></dd></div>
            </dl>
        </section>

        <section class="fi-section p-6 xl:col-span-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Resumo de compras</p>
            <div class="mt-5 grid grid-cols-2 gap-4">
                <div><span class="block text-xs text-zinc-500">Pedidos</span><strong class="mt-1 block text-2xl text-zinc-950"><?php echo e(number_format($ordersCount, 0, ',', '.')); ?></strong></div>
                <div><span class="block text-xs text-zinc-500">Pedidos pagos</span><strong class="mt-1 block text-2xl text-zinc-950"><?php echo e(number_format($paidOrdersCount, 0, ',', '.')); ?></strong></div>
                <div class="col-span-2 border-t border-zinc-200 pt-4"><span class="block text-xs text-zinc-500">Total em compras pagas</span><strong class="mt-1 block text-xl text-zinc-950"><?php echo e(\App\Support\BrazilianMoney::display($paidTotalCents)); ?></strong></div>
                <div class="col-span-2"><span class="block text-xs text-zinc-500">Último pedido</span><strong class="mt-1 block text-sm text-zinc-900"><?php echo e($lastOrderAt ? \Illuminate\Support\Carbon::parse($lastOrderAt)->format('d/m/Y H:i') : 'Sem pedidos'); ?></strong></div>
            </div>
        </section>

        <section class="fi-section p-6 xl:col-span-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Endereços</p>
                <h2 class="mt-1 text-lg font-semibold text-zinc-950">Endereços cadastrados</h2>
            </div>
            <div class="mt-5 grid gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $customer->addresses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $address): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="rounded-xl border border-zinc-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <strong class="text-sm text-zinc-900"><?php echo e($address->label ?: 'Endereço'); ?></strong>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($address->is_default): ?><span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700">Principal</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-zinc-600">
                            <?php echo e($address->street); ?>, <?php echo e($address->number); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($address->complement): ?> — <?php echo e($address->complement); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><br>
                            <?php echo e($address->neighborhood); ?> · <?php echo e($address->city); ?>/<?php echo e($address->state); ?><br>
                            CEP <?php echo e(\App\Support\BrazilianData::postalCode($address->postal_code)); ?>

                        </p>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-sm text-zinc-500">Nenhum endereço cadastrado.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="fi-section p-6 xl:col-span-7">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Histórico</p>
                    <h2 class="mt-1 text-lg font-semibold text-zinc-950">Compras do cliente</h2>
                </div>
                <a href="<?php echo e(\App\Filament\Resources\Orders\OrderResource::getUrl()); ?>?tableSearch=<?php echo e(urlencode($customer->email)); ?>" class="text-sm font-semibold text-red-600 hover:text-red-700">Ver todos em Pedidos</a>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500">
                        <tr><th class="pb-3 pr-4">Pedido</th><th class="pb-3 pr-4">Data</th><th class="pb-3 pr-4">Itens</th><th class="pb-3 pr-4">Status</th><th class="pb-3 pr-4 text-right">Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td class="py-3 pr-4"><a class="font-semibold text-red-600 hover:text-red-700" href="<?php echo e(\App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order])); ?>"><?php echo e($order->number); ?></a></td>
                                <td class="py-3 pr-4 text-zinc-600"><?php echo e($order->created_at->format('d/m/Y H:i')); ?></td>
                                <td class="py-3 pr-4 text-zinc-600"><?php echo e($order->items_count); ?></td>
                                <td class="py-3 pr-4"><span class="font-medium text-zinc-800"><?php echo e($order->status->label()); ?></span></td>
                                <td class="py-3 pr-4 text-right font-semibold text-zinc-900"><?php echo e($order->formattedTotal()); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="5" class="py-8 text-center text-zinc-500">Este cliente ainda não possui pedidos.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
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
<?php /**PATH /var/www/html/resources/views/filament/resources/customers/pages/view-customer.blade.php ENDPATH**/ ?>