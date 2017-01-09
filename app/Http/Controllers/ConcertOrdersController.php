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

        // 付款
        $this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));

        // 寫入訂單
        $order = $concert->orders()->create(['email' => request('email')]);

        // 寫入票券
        foreach (range(1, request('ticket_quantity')) as $i)
        {
            $order->tickets()->create([]);
        }

        return response()->json([], 201);
    }
}
