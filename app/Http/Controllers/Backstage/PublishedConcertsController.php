<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Class PublishedConcertsController
 * @package App\Http\Controllers\Backstage
 */
class PublishedConcertsController extends Controller
{
    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $concert = Auth::user()->concerts()->findOrFail(request('concert_id'));

        if ($concert->isPublished()) {
            abort(422);
        }

        $concert->publish();
        return redirect()->route('backstage.concerts.index');
    }
}
