<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Mail\OrderConfirmationEmail;
use Illuminate\Support\Facades\Mail;

/**
 * Class ConcertOrdersController
 * @package App\Http\Controllers
 */
class ConcertOrdersController extends Controller
{
    /**
     * ConcertOrdersController constructor.
     * @param PaymentGateway $paymentGateway
     */
    public function __construct(private PaymentGateway $paymentGateway)
    {
    }

    /**
     * @param $concertId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);

        $this->validate(request(), [
            'email'           => ['required', 'email'],
            'ticket_quantity' => ['required', 'integer', 'min:1'],
            'payment_token'   => ['required'],
        ]);

        try {
            $reservation = $concert->reserveTickets(request('ticket_quantity'), request('email'));
            $order = $reservation->complete($this->paymentGateway, request('payment_token'), $concert->user->stripe_account_id);

            Mail::to($order->email)->send(new OrderConfirmationEmail($order));

            return response()->json($order, 201);
        } catch (PaymentFailedException) {
            $reservation->cancel();
            return response()->json([], 422);
        } catch (NotEnoughTicketsException) {
            return response()->json([], 422);
        }
    }
}
