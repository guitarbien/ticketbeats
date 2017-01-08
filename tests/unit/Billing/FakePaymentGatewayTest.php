<?php

use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class FakePaymentGatewayTest extends TestCase
{
    public function test_以合法token付款成功()
    {
        // 以 gateway 取得 token
        $paymentGateway = new FakePaymentGateway;

        // 付款
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        // 驗證金額
        $this->assertEquals(2500, $paymentGateway->totalCharges());

    }
}
