<?php

use App\Http\Controllers\Payment\AsaasWebhookController;
use App\Http\Controllers\Shipping\MelhorEnvioOAuthController;
use App\Http\Controllers\Shipping\MelhorEnvioWebhookController;
use App\Http\Controllers\Shipping\ShipmentLabelController;
use App\Http\Controllers\Shipping\ShipmentLabelFileController;
use App\Http\Controllers\Store\BlogController;
use App\Http\Controllers\Store\CustomerAccountController;
use App\Http\Controllers\Store\CustomerAddressController;
use App\Http\Controllers\Store\CustomerAuthController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\InstitutionalController;
use App\Http\Controllers\Store\OrderConfirmationController;
use App\Http\Controllers\Store\ProductController;
use App\Http\Controllers\Store\ShippingQuoteCheckoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::view('/produtos', 'store.products')->name('store.products');
Route::view('/carrinho', 'store.cart')->name('store.cart');
Route::view('/checkout', 'store.checkout')->name('store.checkout');
Route::get('/pedido/{number}/confirmacao', OrderConfirmationController::class)->name('store.order.confirmation');
Route::get('/produto/{slug}', ProductController::class)->name('store.product');
Route::get('/quem-somos', [InstitutionalController::class, 'about'])->name('store.about');
Route::get('/contato', [InstitutionalController::class, 'contact'])->name('store.contact');
Route::get('/blog', [BlogController::class, 'index'])->name('store.blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('store.blog.show');

Route::middleware('guest:customer')->group(function (): void {
    Route::get('/entrar', [CustomerAuthController::class, 'loginForm'])->name('customer.login');
    Route::post('/entrar', [CustomerAuthController::class, 'login'])->name('customer.login.store');
    Route::get('/criar-conta', [CustomerAuthController::class, 'registerForm'])->name('customer.register');
    Route::post('/criar-conta', [CustomerAuthController::class, 'register'])->name('customer.register.store');
});

Route::middleware('auth:customer')->prefix('minha-conta')->group(function (): void {
    Route::get('/', [CustomerAccountController::class, 'dashboard'])->name('customer.dashboard');
    Route::get('/pedidos', [CustomerAccountController::class, 'orders'])->name('customer.orders');
    Route::get('/pedidos/{number}', [CustomerAccountController::class, 'order'])->name('customer.order');
    Route::get('/dados', [CustomerAccountController::class, 'profile'])->name('customer.profile');
    Route::put('/dados', [CustomerAccountController::class, 'updateProfile'])->name('customer.profile.update');
    Route::put('/senha', [CustomerAccountController::class, 'updatePassword'])->name('customer.password.update');
    Route::get('/enderecos', [CustomerAddressController::class, 'index'])->name('customer.addresses');
    Route::get('/consulta-cep/{postalCode}', [CustomerAddressController::class, 'postalCode'])->where('postalCode', '[0-9\-]+')->name('customer.postal-code');
    Route::get('/enderecos/novo', [CustomerAddressController::class, 'create'])->name('customer.addresses.create');
    Route::post('/enderecos', [CustomerAddressController::class, 'store'])->name('customer.addresses.store');
    Route::get('/enderecos/{address}/editar', [CustomerAddressController::class, 'edit'])->name('customer.addresses.edit');
    Route::put('/enderecos/{address}', [CustomerAddressController::class, 'update'])->name('customer.addresses.update');
    Route::delete('/enderecos/{address}', [CustomerAddressController::class, 'destroy'])->name('customer.addresses.destroy');
    Route::post('/enderecos/{address}/principal', [CustomerAddressController::class, 'makeDefault'])->name('customer.addresses.default');
    Route::post('/sair', [CustomerAuthController::class, 'logout'])->name('customer.logout');
});

Route::middleware('auth:web')->prefix('admin/shipping/melhor-envio')->group(function (): void {
    Route::get('/connect', [MelhorEnvioOAuthController::class, 'connect'])->name('admin.shipping.melhor-envio.connect');
    Route::get('/callback', [MelhorEnvioOAuthController::class, 'callback'])->name('admin.shipping.melhor-envio.callback');
});

// Webhook público do Asaas: autenticação pelo cabeçalho asaas-access-token no controller.
// Não deve exigir login; o provedor envia notificações diretamente para esta URL.
Route::post('/webhooks/payments/asaas', AsaasWebhookController::class)
    ->name('webhooks.payments.asaas');

Route::post('/webhooks/shipping/melhor-envio', MelhorEnvioWebhookController::class)
    ->name('webhooks.shipping.melhor-envio');

Route::get('/admin/shipping/labels/{shipment}', ShipmentLabelController::class)->middleware('auth:web')->name('admin.shipping.labels.open');
Route::get('/admin/shipping/labels/{shipment}/file/{format}', ShipmentLabelFileController::class)->middleware('auth:web')->whereIn('format', ['pdf', 'zpl', 'jpeg'])->name('admin.shipping.labels.download');

Route::get('/pedido/{number}/entrega/{revision}', ShippingQuoteCheckoutController::class)
    ->whereNumber('revision')->middleware(['signed', 'throttle:60,1'])->name('store.order.shipping-quote');
