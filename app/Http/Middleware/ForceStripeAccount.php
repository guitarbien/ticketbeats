<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Class ForceStripeAccount
 * @package App\Http\Middleware
 */
class ForceStripeAccount
{
    /**
     * Handle an incoming request.
     * @param $request
     * @param $next
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle($request, $next)
    {
        if (Auth::user()->stripe_account_id === null) {
            return redirect()->route('backstage.stripe-connect.connect');
        }

        return $next();
    }
}
