<?php

namespace App\Http\Controllers;

use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        $concert = Concert::find($concertId);

        $ticketQuantity = request('ticket_quantity');
        $amount         = $ticketQuantity * $concert->ticket_price;
        $token          = request('payment_token');

        // 付款
        $this->paymentGateway->charge($amount, $token);

        // 寫入訂單
        $order = $concert->orders()->create(['email' => request('email')]);

        // 寫入票券
        foreach (range(1, $ticketQuantity) as $i)
        {
            $order->tickets()->create([]);
        }

        return response()->json([], 201);
    }
}
