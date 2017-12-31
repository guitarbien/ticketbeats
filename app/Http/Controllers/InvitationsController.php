<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvitationsController extends Controller
{
    public function show()
    {
        return view('invitations.show');
    }
}
