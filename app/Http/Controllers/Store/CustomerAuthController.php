<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Actions\Customer\LinkGuestOrdersToCustomerAction;
use App\Actions\Customer\RegisterCustomerAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class CustomerAuthController
{
    public function loginForm(Request $request): View
    {
        return view('store.customer.login', ['email' => (string) $request->query('email', '')]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('customer')->attempt(['email' => mb_strtolower($credentials['email']), 'password' => $credentials['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'E-mail ou senha inválidos.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('customer.dashboard'));
    }

    public function registerForm(Request $request): View
    {
        return view('store.customer.register', ['email' => (string) $request->query('email', '')]);
    }

    public function register(Request $request, RegisterCustomerAction $register, LinkGuestOrdersToCustomerAction $linkOrders): RedirectResponse
    {
        $customer = $register->execute($request->all());
        // Claim capabilities live in the current browser session and are checked against
        // both normalized e-mail and CPF/CNPJ before any order is linked.
        $linked = $linkOrders->execute($customer);
        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        $message = $linked > 0
            ? ($linked === 1 ? 'Sua conta foi criada e 1 pedido anterior foi vinculado.' : "Sua conta foi criada e {$linked} pedidos anteriores foram vinculados.")
            : 'Sua conta foi criada com sucesso.';

        return redirect()->route('customer.dashboard')->with('toast', [
            'type' => 'success', 'title' => 'Conta criada', 'message' => $message,
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        // Keep the administrative web guard/session intact when both areas are open in the same browser.
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
