<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Class PublishedConcertOrdersController
 * @package App\Http\Controllers\Backstage
 */
class PublishedConcertOrdersController extends Controller
{
    /**
     * @param $concertId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($concertId)
    {
        $concert = Auth::user()->concerts()->published()->findOrFail($concertId);

        return view('backstage.published-concert-orders.index', [
            'concert' => $concert,
            'orders' => $concert->orders()->latest()->take(10)->get(),
        ]);
    }
}
