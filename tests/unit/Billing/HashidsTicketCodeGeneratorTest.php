<?php

use App\HashidTicketCodeGenerator;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class HashidsTicketCodeGeneratorTest extends TestCase
{
    public function test_票券代碼至少為六位數()
    {
        $ticketCodeGenerator = new HashidTicketCodeGenerator();
        $code = $ticketCodeGenerator->generate();
        $this->assertTrue(strlen($code) >= 6);
    }

    public function test_票券代碼只能有大寫字母()
    {
        $ticketCodeGenerator = new HashidTicketCodeGenerator();
        $code = $ticketCodeGenerator->generate();
        $this->assertRegexp('/^[A-Z]+$/', $code);
    }
}
