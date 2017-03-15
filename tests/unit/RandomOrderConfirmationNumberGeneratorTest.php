<?php

use App\RandomOrderConfirmationNumberGenerator;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class RandomOrderConfirmationNumberGeneratorTest extends TestCase
{
    // Must be unique
    // Can only contain uppercase letters and numbers
    // Cannot contain ambiguous characters (1, I, 0, O)
    // Must be 24 charaters long
    //
    // ABCDEFGHJKLMNPQRSTUVWXYZ
    // 23456789

    public function test_確認碼長度要為24()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $confirmationNumber = $generator->generate();

        $this->assertEquals(24, strlen($confirmationNumber));
    }

    public function test_確認碼只能有大寫英文字和數字()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $confirmationNumber = $generator->generate();

        $this->assertRegexp('/^[A-Z0-9]+$/', $confirmationNumber);
    }

    public function test_確認碼不能有模糊字元()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $confirmationNumber = $generator->generate();

        $this->assertFalse(strpos($confirmationNumber, 'I'));
        $this->assertFalse(strpos($confirmationNumber, '1'));
        $this->assertFalse(strpos($confirmationNumber, '0'));
        $this->assertFalse(strpos($confirmationNumber, 'O'));
    }

    public function test_確認碼要是不重複唯一值()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $confirmationNumbers = collect(range(1, 100))->map(function() use($generator) {
            return $generator->generate();
        });

        $this->assertCount(100, $confirmationNumbers->unique());
    }
}