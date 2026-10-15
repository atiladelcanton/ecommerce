<?php if (isset($component)) { $__componentOriginalbe28baaac5279f8581803b8157bf8af7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbe28baaac5279f8581803b8157bf8af7 = $attributes; } ?>
<?php $component = App\View\Components\Store\Layout::resolve(['title' => 'Entrar na minha conta'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('store.layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Store\Layout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="store-container store-account-auth">
        <section class="store-account-auth-card">
            <p class="store-eyebrow">Área do cliente</p>
            <h1 class="store-title mt-2">Acesse sua conta</h1>
            <p class="mt-3 text-sm leading-6 text-store-muted">Veja seus pedidos e acompanhe cada etapa da entrega.</p>
            <form method="POST" action="<?php echo e(route('customer.login.store')); ?>" class="mt-7 grid gap-4">
                <?php echo csrf_field(); ?>
                <div><label class="store-label" for="email">E-mail</label><input class="store-input" id="email" name="email" type="email" value="<?php echo e(old('email', $email)); ?>" autocomplete="email" required><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-store-red"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
                <div><label class="store-label" for="password">Senha</label><input class="store-input" id="password" name="password" type="password" autocomplete="current-password" required></div>
                <label class="flex items-center gap-2 text-sm text-store-muted"><input type="checkbox" name="remember" value="1"> Lembrar de mim</label>
                <button class="store-button w-full" type="submit">Entrar</button>
            </form>
            <p class="mt-6 text-center text-sm text-store-muted">Ainda não tem conta? <a class="store-text-link" href="<?php echo e(route('customer.register', ['email' => old('email', $email)])); ?>">Criar conta</a></p>
        </section>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbe28baaac5279f8581803b8157bf8af7)): ?>
<?php $attributes = $__attributesOriginalbe28baaac5279f8581803b8157bf8af7; ?>
<?php unset($__attributesOriginalbe28baaac5279f8581803b8157bf8af7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbe28baaac5279f8581803b8157bf8af7)): ?>
<?php $component = $__componentOriginalbe28baaac5279f8581803b8157bf8af7; ?>
<?php unset($__componentOriginalbe28baaac5279f8581803b8157bf8af7); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/store/customer/login.blade.php ENDPATH**/ ?>