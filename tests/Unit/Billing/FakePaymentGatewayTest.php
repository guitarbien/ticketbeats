<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    protected function getPaymentGateway()
    {
        return new FakePaymentGateway;
    }

    public function test_可以以特定的帳號查到訂單總金額()
    {
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge(1000, $paymentGateway->getValidTestToken(), 'test_acct_0000');
        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        $paymentGateway->charge(4000, $paymentGateway->getValidTestToken(), 'test_acct_1234');

        static::assertEquals(6500, $paymentGateway->totalChargesFor('test_acct_1234'));
    }

    public function test_在第一次付款前執行hook()
    {
        $paymentGateway = new FakePaymentGateway;
        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use(&$timesCallbackRan) {
            $timesCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            static::assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        static::assertEquals(5000, $paymentGateway->totalCharges());
        static::assertEquals(1, $timesCallbackRan);
    }
}
