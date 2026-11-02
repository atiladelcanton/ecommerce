<?php

declare(strict_types=1);

namespace App\Http\Controllers\Influencer;

use App\Domain\Influencer\Models\Influencer;
use App\Domain\Influencer\Repositories\InfluencerReportRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class InfluencerPortalController
{
    public function loginForm(): View|RedirectResponse
    {
        if (Auth::guard('influencer')->check()) {
            return redirect()->route('influencers.dashboard');
        }
        return view('influencers.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        $authenticated = Auth::guard('influencer')->attempt([
            'email' => mb_strtolower(trim($data['email'])), 'password' => $data['password'], 'is_active' => true,
        ]);
        if (! $authenticated) {
            return back()->withErrors(['email' => 'E-mail ou senha inválidos, ou acesso desativado.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        return redirect()->intended(route('influencers.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('influencer')->logout();
        $request->session()->regenerateToken();
        return redirect()->route('influencers.login');
    }

    private function current(): Influencer
    {
        $influencer = Auth::guard('influencer')->user();
        abort_unless($influencer instanceof Influencer && $influencer->is_active, 403);
        return $influencer;
    }

    public function dashboard(InfluencerReportRepository $reports): View
    {
        $influencer = $this->current();
        return view('influencers.dashboard', [
            'influencer' => $influencer,
            'stats' => $reports->overview($influencer),
            'coupons' => $reports->coupons($influencer),
            'recentCommissions' => $reports->recentCommissions($influencer),
        ]);
    }

    public function orders(InfluencerReportRepository $reports): View
    {
        $influencer = $this->current();
        return view('influencers.orders', ['influencer' => $influencer, 'orders' => $reports->orders($influencer)]);
    }

    public function commissions(InfluencerReportRepository $reports): View
    {
        $influencer = $this->current();
        return view('influencers.commissions', ['influencer' => $influencer, 'commissions' => $reports->commissions($influencer)]);
    }

    public function payments(InfluencerReportRepository $reports): View
    {
        $influencer = $this->current();
        return view('influencers.payments', [
            'influencer' => $influencer,
            'payments' => $reports->paidCommissions($influencer),
        ]);
    }

    public function profile(): View
    {
        return view('influencers.profile', ['influencer' => $this->current()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $influencer = $this->current();
        $input = $request->validate([
            'instagram' => ['nullable', 'string', 'max:255'],
            'tiktok' => ['nullable', 'string', 'max:255'],
            'pix_key' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'confirmed', 'string', 'min:10'],
        ]);
        // Never expose an existing Pix key. Blank means 'keep current'.
        foreach (['pix_key', 'password'] as $field) {
            if (! filled($input[$field] ?? null)) {
                unset($input[$field]);
            }
        }
        unset($input['password_confirmation']);
        $influencer->update($input);
        return back()->with('success', 'Perfil atualizado.');
    }
}
