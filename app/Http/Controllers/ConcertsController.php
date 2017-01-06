<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConcertsController extends Controller
{
    public function show()
    {
        return view('concerts.show', ['concert' => $concert]);
    }
}
