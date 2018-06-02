<?php

namespace App;

/**
 * Class HashidsTicketCodeGenerator
 * @package App
 */
class HashidsTicketCodeGenerator implements TicketCodeGenerator
{
    private $hashids;

    /**
     * HashidsTicketCodeGenerator constructor.
     * @param $salt
     */
    public function __construct($salt)
    {
        $this->hashids = new \Hashids\Hashids($salt, 6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    /**
     * @param $ticket
     * @return string
     */
    public function generateFor($ticket)
    {
        return $this->hashids->encode($ticket->id);
    }
}
