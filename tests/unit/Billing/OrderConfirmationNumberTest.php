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

    public function test_確認碼只能有大寫英文字和數字()
    {
        $confirmationNumber = (new OrderConfirmationNumber)->generate();

        $this->assertRegexp('/^[A-Z0-9]+$/', $confirmationNumber);
    }

    public function test_確認碼不能有模糊字元()
    {
        $confirmationNumber = (new OrderConfirmationNumber)->generate();

        $this->assertFalse(strpos($confirmationNumber, 'I'));
        $this->assertFalse(strpos($confirmationNumber, '1'));
        $this->assertFalse(strpos($confirmationNumber, '0'));
        $this->assertFalse(strpos($confirmationNumber, 'O'));
    }
}
