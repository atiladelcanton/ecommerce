<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary['cart']['items'] === []): ?>
        <div class="store-cart-empty">
            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-shopping-cart'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'size-12 mx-auto','aria-hidden' => 'true']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
            <p>Seu carrinho está vazio.</p>
            <a href="<?php echo e(route('store.products')); ?>" class="store-button">Ver produtos</a>
        </div>
    <?php else: ?>
        <form wire:submit="submit" class="grid items-start gap-8 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="grid min-w-0 gap-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                    <div class="rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-store-red-dark" role="alert"><?php echo e($errors->first()); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <section class="rounded-xl border border-store-border bg-white p-5 sm:p-7" aria-labelledby="checkout-customer-heading">
                    <h2 id="checkout-customer-heading" class="mb-6 text-lg font-semibold">Dados do cliente</h2>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard('customer')->check()): ?>
                        <p class="mb-5 rounded-lg bg-store-soft p-3 text-sm text-store-muted">Usando os dados da sua conta. Você pode alterá-los em <a class="store-text-link" href="<?php echo e(route('customer.profile')); ?>">Meus dados</a>.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="grid gap-x-5 gap-y-3 sm:grid-cols-2">
                        <?php if (isset($component)) { $__componentOriginal2c1e1efc1c5785977ba546180ef40205 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c1e1efc1c5785977ba546180ef40205 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.checkout-field','data' => ['name' => 'name','label' => 'Nome','autocomplete' => 'name','class' => 'sm:col-span-2','readonly' => auth('customer')->check()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.checkout-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'name','label' => 'Nome','autocomplete' => 'name','class' => 'sm:col-span-2','readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth('customer')->check())]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $attributes = $__attributesOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $component = $__componentOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__componentOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal2c1e1efc1c5785977ba546180ef40205 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c1e1efc1c5785977ba546180ef40205 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.checkout-field','data' => ['name' => 'email','label' => 'E-mail','type' => 'email','autocomplete' => 'email','inputmode' => 'email','readonly' => auth('customer')->check()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.checkout-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'email','label' => 'E-mail','type' => 'email','autocomplete' => 'email','inputmode' => 'email','readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth('customer')->check())]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $attributes = $__attributesOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $component = $__componentOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__componentOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal2c1e1efc1c5785977ba546180ef40205 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c1e1efc1c5785977ba546180ef40205 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.checkout-field','data' => ['name' => 'phone','label' => 'Telefone','type' => 'tel','autocomplete' => 'tel','inputmode' => 'tel','maxlength' => 16,'mask' => 'phone','readonly' => auth('customer')->check()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.checkout-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'phone','label' => 'Telefone','type' => 'tel','autocomplete' => 'tel','inputmode' => 'tel','maxlength' => 16,'mask' => 'phone','readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth('customer')->check())]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $attributes = $__attributesOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $component = $__componentOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__componentOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal2c1e1efc1c5785977ba546180ef40205 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c1e1efc1c5785977ba546180ef40205 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.checkout-field','data' => ['name' => 'document','label' => 'CPF/CNPJ','inputmode' => 'numeric','maxlength' => 18,'mask' => 'document','class' => 'sm:col-span-2','readonly' => auth('customer')->check()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.checkout-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'document','label' => 'CPF/CNPJ','inputmode' => 'numeric','maxlength' => 18,'mask' => 'document','class' => 'sm:col-span-2','readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(auth('customer')->check())]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $attributes = $__attributesOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $component = $__componentOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__componentOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
                    </div>
                </section>

                <section class="rounded-xl border border-store-border bg-white p-5 sm:p-7" aria-labelledby="checkout-address-heading">
                    <h2 id="checkout-address-heading" class="mb-2 text-lg font-semibold">Endereço de entrega</h2>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard('customer')->check()): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($savedAddresses !== []): ?>
                            <p class="mb-5 text-sm text-store-muted">Escolha um endereço salvo ou use um novo endereço.</p>
                            <div class="mb-6 grid gap-3 md:grid-cols-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $savedAddresses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $address): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <label class="cursor-pointer rounded-xl border p-4 transition has-checked:border-store-red has-checked:bg-red-50/30">
                                        <div class="flex items-start gap-3">
                                            <input class="mt-1 accent-store-red" type="radio" wire:model.live="addressChoice" value="saved:<?php echo e($address['id']); ?>">
                                            <span class="min-w-0 text-sm">
                                                <span class="flex flex-wrap items-center gap-2 font-semibold"><?php echo e($address['label']); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($address['is_default']): ?><span class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-store-red">Principal</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></span>
                                                <span class="mt-1 block text-store-muted"><?php echo e($address['street']); ?>, <?php echo e($address['number']); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($address['complement']): ?> - <?php echo e($address['complement']); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></span>
                                                <span class="block text-store-muted"><?php echo e($address['neighborhood']); ?> · <?php echo e($address['city']); ?>/<?php echo e($address['state']); ?></span>
                                            </span>
                                        </div>
                                    </label>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <label class="cursor-pointer rounded-xl border border-dashed border-store-border p-4 transition has-checked:border-store-red has-checked:bg-red-50/30">
                                    <span class="flex items-center gap-3 text-sm font-semibold"><input class="accent-store-red" type="radio" wire:model.live="addressChoice" value="new"> + Usar novo endereço</span>
                                </label>
                            </div>
                        <?php else: ?>
                            <p class="mb-5 text-sm text-store-muted">Você ainda não possui endereços salvos. Este primeiro endereço poderá ser salvo como principal.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!auth('customer')->check() || $addressChoice === 'new'): ?>
                        <div class="grid gap-x-5 gap-y-3 sm:grid-cols-2">
                            <?php if (isset($component)) { $__componentOriginal2c1e1efc1c5785977ba546180ef40205 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c1e1efc1c5785977ba546180ef40205 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.checkout-field','data' => ['name' => 'postal_code','label' => 'CEP','autocomplete' => 'postal-code','inputmode' => 'numeric','maxlength' => 9,'mask' => 'postalCode','blur' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.checkout-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'postal_code','label' => 'CEP','autocomplete' => 'postal-code','inputmode' => 'numeric','maxlength' => 9,'mask' => 'postalCode','blur' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                <p wire:loading wire:target="form.postal_code" class="text-xs text-store-muted" role="status">Buscando endereço...</p>
                                <p wire:loading.remove wire:target="form.postal_code" class="text-xs leading-5 text-store-muted" role="status" aria-live="polite"><?php echo e($postalCodeMessage); ?></p>
                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $attributes = $__attributesOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $component = $__componentOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__componentOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginal2c1e1efc1c5785977ba546180ef40205 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c1e1efc1c5785977ba546180ef40205 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.checkout-field','data' => ['name' => 'street','label' => 'Rua','autocomplete' => 'address-line1','class' => 'sm:col-span-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.checkout-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'street','label' => 'Rua','autocomplete' => 'address-line1','class' => 'sm:col-span-2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $attributes = $__attributesOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $component = $__componentOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__componentOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginal2c1e1efc1c5785977ba546180ef40205 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c1e1efc1c5785977ba546180ef40205 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.checkout-field','data' => ['name' => 'number','label' => 'Número','maxlength' => 30]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.checkout-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'number','label' => 'Número','maxlength' => 30]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $attributes = $__attributesOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $component = $__componentOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__componentOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginal2c1e1efc1c5785977ba546180ef40205 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c1e1efc1c5785977ba546180ef40205 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.checkout-field','data' => ['name' => 'complement','label' => 'Complemento','autocomplete' => 'address-line2','required' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.checkout-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'complement','label' => 'Complemento','autocomplete' => 'address-line2','required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $attributes = $__attributesOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $component = $__componentOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__componentOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginal2c1e1efc1c5785977ba546180ef40205 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c1e1efc1c5785977ba546180ef40205 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.checkout-field','data' => ['name' => 'neighborhood','label' => 'Bairro']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.checkout-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'neighborhood','label' => 'Bairro']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $attributes = $__attributesOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $component = $__componentOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__componentOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginal2c1e1efc1c5785977ba546180ef40205 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2c1e1efc1c5785977ba546180ef40205 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.store.checkout-field','data' => ['name' => 'city','label' => 'Cidade','autocomplete' => 'address-level2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.checkout-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'city','label' => 'Cidade','autocomplete' => 'address-level2']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $attributes = $__attributesOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__attributesOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2c1e1efc1c5785977ba546180ef40205)): ?>
<?php $component = $__componentOriginal2c1e1efc1c5785977ba546180ef40205; ?>
<?php unset($__componentOriginal2c1e1efc1c5785977ba546180ef40205); ?>
<?php endif; ?>
                            <div>
                                <label for="checkout-state" class="store-label">Estado</label>
                                <select id="checkout-state" name="state" wire:model="form.state" autocomplete="address-level1" class="store-input" required aria-describedby="checkout-state-error" <?php $__errorArgs = ['state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> aria-invalid="true" <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>>
                                    <option value="">Selecione</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($state); ?>"><?php echo e($state); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <p id="checkout-state-error" class="mt-2 text-sm text-store-red"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><?php echo e($message); ?><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
                            </div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard('customer')->check()): ?>
                            <div class="mt-5 grid gap-2 text-sm">
                                <label class="flex items-center gap-2"><input type="checkbox" wire:model.live="saveNewAddress" class="accent-store-red"> Salvar este endereço na minha conta</label>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($saveNewAddress): ?>
                                    <label class="flex items-center gap-2"><input type="checkbox" wire:model="makeDefaultAddress" class="accent-store-red"> Tornar endereço principal</label>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php else: ?>
                        <?php ($selected = collect($savedAddresses)->firstWhere('id', (int) str_replace('saved:', '', $addressChoice))); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selected): ?>
                            <div class="rounded-xl bg-store-soft p-4 text-sm">
                                <p class="font-semibold"><?php echo e($selected['label']); ?></p>
                                <p class="mt-1 text-store-muted"><?php echo e($selected['street']); ?>, <?php echo e($selected['number']); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selected['complement']): ?> - <?php echo e($selected['complement']); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
                                <p class="text-store-muted"><?php echo e($selected['neighborhood']); ?> · <?php echo e($selected['city']); ?>/<?php echo e($selected['state']); ?> · <?php echo e(\App\Support\BrazilianData::postalCode($selected['postal_code'])); ?></p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <fieldset class="mt-6 border-t border-store-border pt-6">
                        <legend class="pt-6 text-sm font-semibold">Forma de entrega</legend>
                        <div class="mt-3 grid gap-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $summary['shippingOptions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-store-border p-4 has-checked:border-store-red">
                                    <input type="radio" wire:model.live="form.shipping_option" name="shipping_option" value="<?php echo e($option->id); ?>" required class="mt-1 accent-store-red" aria-describedby="shipping-option-error">
                                    <span class="min-w-0 flex-1"><span class="block text-sm font-semibold"><?php echo e($option->name); ?></span><span class="mt-1 block text-xs leading-5 text-store-muted"><?php echo e($option->carrierName); ?></span><span class="mt-1 block text-xs leading-5 text-store-muted"><?php echo e($option->description); ?></span></span>
                                    <span class="shrink-0 text-sm font-semibold"><?php echo e($option->fulfillmentMode === 'pickup' ? 'Grátis' : \App\Support\BrazilianMoney::display($option->price)); ?></span>
                                </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary['shippingOptions'] === [] && !$summary['shippingError']): ?>
                                <div class="rounded-lg border border-dashed border-store-border bg-store-soft p-4 text-sm text-store-muted">
                                    Informe o CEP de entrega para consultar as opções e prazos disponíveis.
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <p id="shipping-option-error" class="mt-2 text-sm text-store-red" role="status"><?php echo e($summary['shippingError']); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['shipping_option'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><?php echo e($message); ?><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary['canRequestShippingQuote']): ?>
                            <div class="mt-4 rounded-lg border border-store-border bg-store-soft p-4">
                                <p class="text-sm">Solicite a cotação da entrega. Seus itens serão reservados por 24 horas e você receberá pelo atendimento um link para revisar o frete e concluir a compra.</p>
                                <button type="button" wire:click="requestShippingQuote" wire:loading.attr="disabled" class="store-button mt-3">Solicitar cotação de entrega</button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary['shippingError'] && $summary['shippingContact']): ?>
                            <a class="store-text-link mt-2 inline-flex" href="https://wa.me/<?php echo e(preg_replace('/\D/', '', $summary['shippingContact'])); ?>?text=<?php echo e(rawurlencode('Olá! Preciso consultar o frete dos produtos do meu carrinho.')); ?>" target="_blank" rel="noopener noreferrer">Consultar entrega com a loja</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </fieldset>
                </section>

                <section class="rounded-xl border border-store-border bg-white p-5 sm:p-7" aria-labelledby="checkout-payment-heading">
                    <h2 id="checkout-payment-heading" class="mb-3 text-lg font-semibold">Pagamento</h2>
                    <p class="text-sm leading-6 text-store-muted"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(app(\App\Infrastructure\Payments\PaymentGatewayRegistry::class)->active()): ?> Você será encaminhado ao Asaas para concluir sua compra. O pedido será considerado pago somente após confirmação da cobrança. Seus itens ficarão reservados até o pagamento ser confirmado, reprovado ou a cobrança expirar. <?php else: ?> Ambiente de demonstração: nenhuma cobrança real será realizada. <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></p>
                </section>
                <a href="<?php echo e(route('store.cart')); ?>" class="store-text-link">← Voltar ao carrinho</a>
            </div>

            <aside class="min-w-0 rounded-xl border border-store-border bg-store-soft p-5 sm:p-6 xl:sticky xl:top-28" aria-labelledby="checkout-summary-heading">
                <h2 id="checkout-summary-heading" class="mb-6 text-lg font-semibold">Seu pedido</h2>
                <ul class="grid gap-5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $summary['cart']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'checkout-item-'.e($key).''; ?>wire:key="checkout-item-<?php echo e($key); ?>" class="flex justify-between gap-4 text-sm">
                            <div class="min-w-0"><p class="font-semibold wrap-anywhere"><?php echo e($line['product']->name); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($line['variant']): ?><p class="mt-1 text-xs text-store-muted"><?php echo e($line['variant']->name); ?>: <?php echo e($line['variant']->value); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <p class="mt-1 text-xs text-store-muted"><?php echo e($line['item']->quantity); ?> × <?php echo e(\App\Support\BrazilianMoney::display($line['unitPrice'])); ?></p>
                            </div>
                            <span class="shrink-0"><?php echo e(\App\Support\BrazilianMoney::display($line['subtotal'])); ?></span>
                        </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ul>
                <dl class="mt-6 border-t border-store-border pt-4 text-sm">
                    <div class="flex justify-between gap-4 py-2"><dt>Subtotal</dt><dd><?php echo e(\App\Support\BrazilianMoney::display($summary['cart']['subtotal'])); ?></dd></div>
                    <div class="flex justify-between gap-4 py-2"><dt>Frete</dt><dd><?php echo e($summary['selectedShipping'] ? \App\Support\BrazilianMoney::display($summary['selectedShipping']->price) : 'Selecione a entrega'); ?></dd></div>
                    <div class="mt-4 flex justify-between gap-4 border-t border-store-border pt-5 text-lg font-semibold"><dt>Total</dt><dd aria-live="polite"><?php echo e($summary['selectedShipping'] && $summary['totals'] ? \App\Support\BrazilianMoney::display($summary['totals']->total) : 'A definir'); ?></dd></div>
                </dl>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary['totalError']): ?><p class="mt-4 text-sm text-store-red" role="alert"><?php echo e($summary['totalError']); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary['canRequestShippingQuote'] && ! $summary['selectedShipping']): ?>
                    <button type="button" wire:click="requestShippingQuote" wire:loading.attr="disabled" class="store-button mt-6 w-full">Solicitar cotação de entrega</button>
                    <p class="mt-3 text-sm text-store-muted">Você revisará o frete e o total antes do pagamento. Nenhuma cobrança será realizada nesta solicitação.</p>
                <?php else: ?>
                <button type="submit" wire:loading.attr="disabled" wire:target="submit" <?php if($summary['shippingError'] || $summary['totalError'] || !$summary['selectedShipping']): echo 'disabled'; endif; ?> class="store-button mt-6 w-full disabled:cursor-wait disabled:opacity-50">
                    <span wire:loading.remove wire:target="submit"><?php echo e(app(\App\Infrastructure\Payments\PaymentGatewayRegistry::class)->active() ? 'Continuar para o pagamento' : 'Finalizar pedido de teste'); ?></span>
                    <span wire:loading wire:target="submit">Finalizando pedido…</span>
                </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="mt-3 text-center text-xs leading-5 text-store-muted"><?php echo e(auth('customer')->check() ? 'Este pedido será vinculado à sua conta.' : 'Você pode finalizar sem criar uma conta e criar seu acesso depois.'); ?></p>
            </aside>
        </form>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/store/checkout-page.blade.php ENDPATH**/ ?>