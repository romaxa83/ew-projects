<?php

namespace WezomCms\Orders\Http\Middleware;

use Cart;
use Closure;
use Flash;
use Illuminate\Http\Request;

class RedirectToHomeIfCartIsEmpty
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Cart::isEmpty()) {
            Flash::warning(__('cms-orders::site.cart.Cart is empty'), 10);

            return redirect()->route('home');
        }

        return $next($request);
    }
}
