<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Http\Controllers\Controller;
use App\Jobs\SendAttendeeMessage;
use Illuminate\Support\Facades\Auth;

/**
 * Class ConcertMessagesController
 * @package App\Http\Controllers\Backstage
 */
class ConcertMessagesController extends Controller
{
    /**
     * @param string $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(string $id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        return view('backstage.concert-messages.new', ['concert' => $concert]);
    }

    /**
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(string $id)
    {
        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->findOrFail($id);

        $this->validate(request(), [
            'subject' => ['required'],
            'message' => ['required'],
        ]);

        $message = $concert->attendeeMessages()->create(request(['subject', 'message']));

        SendAttendeeMessage::dispatch($message);

        return redirect()->route('backstage.concert-messages.new', $concert)
            ->with('flash', 'Your message has been sent.');
    }
}
