<?php

namespace App;

/**
 * Class RandomOrderConfirmationNumberGenerator
 * @package App
 */
class RandomOrderConfirmationNumberGenerator implements OrderConfirmationNumberGenerator, InvitationCodeGenerator
{
    /**
     * @return bool|string
     */
    public function generate()
    {
        $pool = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 24)), 0, 24);
    }
}
