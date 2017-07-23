<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Http\Controllers\Controller;

class PublishedConcertsController extends Controller
{
    public function store()
    {
        $concert = Concert::find(request('concert_id'));
        $concert->publish();
        return redirect()->route('backstage.concerts.index');
    }
}
