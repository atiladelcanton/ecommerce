<div class="space-y-6">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $configured): ?>
        <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-sm text-warning-900">
            <p class="font-semibold">Configure a Inteligência Artificial primeiro.</p>
            <p class="mt-1">Cadastre sua API Key gratuita do Groq e ative o assistente.</p>
            <a href="<?php echo e($settingsUrl); ?>" class="mt-3 inline-flex font-semibold underline">Ir para configurações de IA</a>
        </div>
    <?php else: ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errorMessage): ?>
            <div class="rounded-xl border border-danger-200 bg-danger-50 p-4 text-sm text-danger-900" role="alert">
                <?php echo e($errorMessage); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($suggestion === null): ?>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-gray-950 dark:text-white">O que você quer fazer?</span>
                    <select wire:model="mode" class="fi-select-input block w-full rounded-lg border-gray-300 bg-white text-gray-950 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="generate">Criar textos para o cadastro</option>
                        <option value="improve">Melhorar os textos atuais</option>
                    </select>
                </label>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">
                    <p class="font-medium text-gray-950 dark:text-white">O que a IA pode alterar</p>
                    <p class="mt-1">Nome, descrição curta, descrição completa, SEO e sugestão de categoria.</p>
                    <p class="mt-2 font-medium text-gray-950 dark:text-white">O que ela nunca altera</p>
                    <p class="mt-1">Preço, custo, promoção, estoque, SKU, peso, dimensões e variações.</p>
                </div>
            </div>

            <label class="block">
                <span class="mb-2 block text-sm font-medium text-gray-950 dark:text-white">Informações adicionais sobre o produto</span>
                <textarea wire:model="brief" rows="6" maxlength="12000" class="block w-full rounded-lg border-gray-300 bg-white text-gray-950 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white" placeholder="Ex.: Nylon amarelo, 77 fios, vendido por metro, indicado para telas de serigrafia. Não invente outras características."></textarea>
                <span class="mt-2 block text-xs text-gray-500">Pode colar ficha técnica, observações, diferenciais reais ou apenas complementar o cadastro que já está preenchido.</span>
            </label>

            <div class="flex justify-end">
                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'button','wire:click' => 'generate','wire:loading.attr' => 'disabled','wire:target' => 'generate','icon' => 'heroicon-o-sparkles']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','wire:click' => 'generate','wire:loading.attr' => 'disabled','wire:target' => 'generate','icon' => 'heroicon-o-sparkles']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    <span wire:loading.remove wire:target="generate">Gerar sugestões</span>
                    <span wire:loading wire:target="generate">Gerando…</span>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
            </div>
        <?php else: ?>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-gray-950 dark:text-white">Revise antes de aplicar</p>
                    <p class="text-sm text-gray-500">Nada é salvo no produto até você aplicar as sugestões e depois salvar o cadastro.</p>
                </div>
                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'button','color' => 'gray','wire:click' => 'resetSuggestion','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','color' => 'gray','wire:click' => 'resetSuggestion','size' => 'sm']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Gerar novamente <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
            </div>

            <div class="space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                    'name' => 'Nome',
                    'short_description' => 'Descrição curta',
                    'seo_title' => 'Título SEO',
                    'seo_description' => 'Descrição SEO',
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($suggestion[$field] ?? null)): ?>
                        <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                            <label class="flex items-start gap-3">
                                <input type="checkbox" wire:model="applyFields.<?php echo e($field); ?>" class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-gray-950 dark:text-white"><?php echo e($label); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($initial[$field] ?? null)): ?>
                                        <span class="mt-2 block text-xs font-medium uppercase tracking-wide text-gray-400">Atual</span>
                                        <span class="mt-1 block text-sm text-gray-500"><?php echo e(strip_tags((string) $initial[$field])); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <span class="mt-3 block text-xs font-medium uppercase tracking-wide text-gray-400">Sugestão</span>
                                    <span class="mt-1 block text-sm text-gray-950 dark:text-white"><?php echo e(strip_tags((string) $suggestion[$field])); ?></span>
                                </span>
                            </label>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($suggestion['description'] ?? null)): ?>
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <label class="flex items-start gap-3">
                            <input type="checkbox" wire:model="applyFields.description" class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-gray-950 dark:text-white">Descrição completa</span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($initial['description'] ?? null)): ?>
                                    <span class="mt-2 block text-xs font-medium uppercase tracking-wide text-gray-400">Atual</span>
                                    <div class="prose prose-sm mt-1 max-w-none text-gray-500"><?php echo $currentDescription; ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <span class="mt-3 block text-xs font-medium uppercase tracking-wide text-gray-400">Sugestão</span>
                                <div class="prose prose-sm mt-1 max-w-none text-gray-950 dark:text-white"><?php echo $suggestedDescription; ?></div>
                            </span>
                        </label>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($suggestion['category_id'] ?? null)): ?>
                    <div class="rounded-xl border border-gray-200 p-4 dark:border-white/10">
                        <label class="flex items-start gap-3">
                            <input type="checkbox" wire:model="applyFields.category_id" class="mt-1 rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                            <span>
                                <span class="block text-sm font-semibold text-gray-950 dark:text-white">Categoria sugerida</span>
                                <span class="mt-1 block text-sm text-gray-600 dark:text-gray-300"><?php echo e($suggestion['category_name']); ?></span>
                            </span>
                        </label>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="flex flex-wrap justify-end gap-3">
                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'button','color' => 'gray','wire:click' => 'resetSuggestion']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','color' => 'gray','wire:click' => 'resetSuggestion']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>
Voltar <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'button','wire:click' => 'apply','wire:loading.attr' => 'disabled','wire:target' => 'apply','icon' => 'heroicon-o-check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','wire:click' => 'apply','wire:loading.attr' => 'disabled','wire:target' => 'apply','icon' => 'heroicon-o-check']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                    Aplicar selecionados ao formulário
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/admin/product-ai-assistant.blade.php ENDPATH**/ ?>