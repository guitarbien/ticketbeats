<?php

namespace App;

/**
 * Class Reservation
 * @package App
 */
class Reservation
{
    private $tickets;
    private $email;

    /**
     * Reservation constructor.
     * @param $tickets
     * @param $email
     */
    public function __construct($tickets, $email)
    {
        $this->tickets = $tickets;
        $this->email   = $email;
    }

    /**
     * @return mixed
     */
    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    public function tickets()
    {
        return $this->tickets;
    }

    public function email()
    {
        return $this->email;
    }

    /**
     * @param $paymentGateway
     * @param $paymentToken
     * @param string $destinationAccountId
     * @return Order
     */
    public function complete($paymentGateway, $paymentToken, string $destinationAccountId)
    {
        $charge = $paymentGateway->charge($this->totalCost(), $paymentToken, $destinationAccountId);

        return Order::forTickets($this->tickets(), $this->email(), $charge);
    }

    public function cancel()
    {
        foreach ($this->tickets as $ticket)
        {
            $ticket->release();
        }
    }
}
