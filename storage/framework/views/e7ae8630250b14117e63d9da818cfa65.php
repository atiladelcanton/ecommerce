<?php if (isset($component)) { $__componentOriginalb525200bfa976483b4eaa0b7685c6e24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-widgets::components.widget','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-widgets::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <section class="saba-dashboard-card saba-outcomes-card">
        <header class="saba-dashboard-card__header">
            <div>
                <p class="saba-dashboard-card__eyebrow">Desfechos</p>
                <h2 class="saba-dashboard-card__title">Resumo de <?php echo e($monthLabel); ?></h2>
                <p class="saba-dashboard-card__description">Somente pedidos já entregues ou cancelados entram na taxa.</p>
            </div>
        </header>

        <div class="saba-outcomes-grid">
            <div class="saba-outcomes-metric saba-outcomes-metric--success">
                <span class="saba-outcomes-metric__label">Entregues</span>
                <strong class="saba-outcomes-metric__value"><?php echo e(number_format($delivered, 0, ',', '.')); ?></strong>
            </div>

            <div class="saba-outcomes-metric saba-outcomes-metric--danger">
                <span class="saba-outcomes-metric__label">Cancelados</span>
                <strong class="saba-outcomes-metric__value"><?php echo e(number_format($cancelled, 0, ',', '.')); ?></strong>
            </div>

            <div class="saba-outcomes-metric">
                <span class="saba-outcomes-metric__label">Taxa de cancelamento</span>
                <strong class="saba-outcomes-metric__value">
                    <?php echo e($cancellationRate === null ? '—' : number_format($cancellationRate, 1, ',', '.').'%'); ?>

                </strong>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cancellationRateDelta !== null): ?>
                    <span class="saba-outcomes-metric__hint <?php echo e($cancellationRateDelta > 0 ? 'saba-outcomes-metric__hint--bad' : ($cancellationRateDelta < 0 ? 'saba-outcomes-metric__hint--good' : '')); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cancellationRateDelta > 0): ?>+<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php echo e(number_format($cancellationRateDelta, 1, ',', '.')); ?> p.p. vs. mês anterior
                    </span>
                <?php else: ?>
                    <span class="saba-outcomes-metric__hint">Sem base comparável no mês anterior</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="saba-outcomes-metric">
                <span class="saba-outcomes-metric__label">Valor dos cancelados</span>
                <strong class="saba-outcomes-metric__value saba-outcomes-metric__value--money"><?php echo e($cancelledValue); ?></strong>
            </div>
        </div>
    </section>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $attributes = $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $component = $__componentOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/filament/widgets/order-outcomes-summary.blade.php ENDPATH**/ ?>