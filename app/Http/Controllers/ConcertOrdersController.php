<?php

namespace App\Http\Controllers;

use App\Billing\FakePaymentGateway;
use App\Concert;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller
{
    public function store($concertId)
    {
        $concert = Concert::find($concertId);

        $amount = request('ticket_quantity') * $concert->ticket_price;
        $token  = request('payment_token');

        $paymentGateway = new FakePaymentGateway;
        $paymentGateway->charge($amount, $token);

        // dd($paymentGateway->totalCharges());

        return response()->json([], 201);
    }
}
