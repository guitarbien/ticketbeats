<?php

namespace App\Http\Controllers;

use App\Concert;

/**
 * Class ConcertsController
 * @package App\Http\Controllers
 */
class ConcertsController extends Controller
{
    /**
     * @param string $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(string $id)
    {
        $concert = Concert::published()->findOrFail($id);
        return view('concerts.show', ['concert' => $concert]);
    }}
