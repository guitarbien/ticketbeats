<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;

trait PaymentGatewayContractTests
{
    abstract protected function getPaymentGateway();

    public function test_以合法token付款成功()
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        });

        static::assertCount(1, $newCharges);
        static::assertEquals(2500, $newCharges->map->amount()->sum());
    }

    public function test_成功付款之後可以得到詳細資訊()
    {
        $paymentGateway = $this->getPaymentGateway();
        $charge = $paymentGateway->charge(2500, $paymentGateway->getValidTestToken($paymentGateway::TEST_CARD_NUMBER), 'test_acct_1234');

        static::assertEquals(substr($paymentGateway::TEST_CARD_NUMBER, -4), $charge->cardLastFour());
        static::assertEquals(2500, $charge->amount());
        static::assertEquals('test_acct_1234', $charge->destination());
    }

    public function test_可以透過callback取得付款的物件()
    {
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken(), 'test_acct_1234');

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken(), 'test_acct_1234');
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken(), 'test_acct_1234');
        });

        static::assertCount(2, $newCharges);
        static::assertEquals([5000, 4000], $newCharges->map->amount()->all());
    }

    public function test_以不合法token付款失敗()
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            try {
                    $paymentGateway->charge(2500, 'invalid-payment-token', 'test_acct_1234');

            } catch (PaymentFailedException $e) {
                return;
            }

            $this->fail("Charging with an invalid payment token did not throw a PaymentFailedException.");
        });

        static::assertCount(0, $newCharges);
    }
}
