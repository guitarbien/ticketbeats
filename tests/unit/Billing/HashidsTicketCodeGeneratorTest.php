<?php

use App\HashidsTicketCodeGenerator;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class HashidsTicketCodeGeneratorTest extends TestCase
{
    public function test_票券代碼至少為六位數()
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');
        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertTrue(strlen($code) >= 6);
    }

    public function test_票券代碼只能有大寫字母()
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');
        $code = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertRegexp('/^[A-Z]+$/', $code);
    }

    public function test_同一個票券id產生的票券代碼是一樣的()
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');

        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertEquals($code1, $code2);
    }

    public function test_不同票券id產生的票券代碼是不同的()
    {
        $ticketCodeGenerator = new HashidsTicketCodeGenerator('testsalt1');

        $code1 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator->generateFor(new Ticket(['id' => 2]));

        $this->assertNotEquals($code1, $code2);
    }

    public function test_不同salt產生的代碼是不同的()
    {
        $ticketCodeGenerator1 = new HashidsTicketCodeGenerator('testsalt1');
        $ticketCodeGenerator2 = new HashidsTicketCodeGenerator('testsalt2');

        $code1 = $ticketCodeGenerator1->generateFor(new Ticket(['id' => 1]));
        $code2 = $ticketCodeGenerator2->generateFor(new Ticket(['id' => 1]));

        $this->assertNotEquals($code1, $code2);
    }
}
