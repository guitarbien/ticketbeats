<?php

namespace App\Http\Controllers;

use App\Invitation;
use Illuminate\Http\Request;

class InvitationsController extends Controller
{
    public function show(string $code)
    {
        $invitation = Invitation::findByCode($code);

        if ($invitation->hasBeenUsed()) {
            abort(404);
        }

        return view('invitations.show', [
            'invitation' => $invitation,
        ]);
    }
}
