<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Actions\Customer\ChangeCustomerPasswordAction;
use App\Actions\Customer\UpdateCustomerProfileAction;
use App\Domain\Order\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CustomerAccountController
{
    public function dashboard(Request $request): View
    {
        $customer = $request->user('customer');
        $orders = $customer->orders()->withCount('items')->limit(5)->get();
        return view('store.customer.dashboard', ['customer' => $customer, 'orders' => $orders, 'ordersCount' => $customer->orders()->count()]);
    }

    public function orders(Request $request): View
    {
        $customer = $request->user('customer');
        $orders = $customer->orders()->withCount('items')->paginate(10);
        return view('store.customer.orders', compact('customer', 'orders'));
    }

    public function order(Request $request, string $number): View
    {
        Order::query()->where('customer_id', $request->user('customer')->id)->where('number', $number)->firstOrFail();
        return view('store.customer.order', ['number' => $number]);
    }

    public function profile(Request $request): View
    {
        return view('store.customer.profile', ['customer' => $request->user('customer')]);
    }

    public function updateProfile(Request $request, UpdateCustomerProfileAction $action): RedirectResponse
    {
        $action->execute($request->user('customer'), $request->all());
        return back()->with('toast', ['type' => 'success', 'title' => 'Dados atualizados', 'message' => 'Seus dados foram salvos.']);
    }

    public function updatePassword(Request $request, ChangeCustomerPasswordAction $action): RedirectResponse
    {
        $action->execute($request->user('customer'), $request->all());
        return back()->with('toast', ['type' => 'success', 'title' => 'Senha alterada', 'message' => 'Sua nova senha já está ativa.']);
    }
}
