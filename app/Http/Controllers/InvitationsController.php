<?php

namespace App\Http\Controllers;

use App\Invitation;

/**
 * Class InvitationsController
 * @package App\Http\Controllers
 */
class InvitationsController extends Controller
{
    /**
     * @param string $code
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(string $code)
    {
        $invitation = Invitation::findByCode($code);

        abort_if($invitation->hasBeenUsed(), 404);

        return view('invitations.show', [
            'invitation' => $invitation,
        ]);
    }
}
