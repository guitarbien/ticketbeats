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

    public function test_同一個票券id產生的票券代碼是一樣的()
    {
        $ticketCodeGenerator = new HashidTicketCodeGenerator();

        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertEquals($code1, $code2);
    }
}