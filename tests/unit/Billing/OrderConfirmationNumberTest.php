<?php

use App\OrderConfirmationNumber;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class OrderConfirmationNumberTest extends TestCase
{
    // Must be unique
    // Can only contain uppercase letters and numbers
    // Cannot contain ambiguous characters (1, I, 0, O)
    // Must be 16 charaters long
    //
    // ABCDEFGHJKLMNPQRSTUVWXYZ
    // 23456789

    public function test_確認碼長度要為16()
    {
        $confirmationNumber = (new OrderConfirmationNumber)->generate();

        $this->assertEquals(16, strlen($confirmationNumber));
    }
}
