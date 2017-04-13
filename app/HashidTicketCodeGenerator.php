<?php

namespace App;

class HashidTicketCodeGenerator implements TicketCodeGenerator
{
    public function generateFor($ticket)
    {
        return 'AAAAAA';
    }
}
