<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;

/**
 * Class RegisterController
 * @package App\Http\Controllers\Auth
 */
class RegisterController extends Controller
{
    public function register()
    {
        $user = User::create([
            'email'    => request('email'),
            'password' => request('password'),
        ]);

        return redirect()->route('backstage.concerts.index');
    }
}
