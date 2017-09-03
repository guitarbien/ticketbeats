<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConcertMessagesController extends Controller
{
    public function create($id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        return view('backstage.concert-messages.new', ['concert' => $concert]);
    }

    public function store($id)
    {
        /** @var Concert $concert */
        $concert = Auth::user()->concerts()->findOrFail($id);

        $this->validate(request(), [
            'subject' => ['required'],
            'message' => ['required'],
        ]);

        $message = $concert->attendeeMessages()->create(request(['subject', 'message']));

        SendAttendeeMessage::dispatch($message);

        return redirect()->route('backstage.concert-messages.new', $concert->id)
            ->with('flash', 'Your message has been sent.');
    }
}
