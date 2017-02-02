<?php

use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class FakePaymentGatewayTest extends TestCase
{
    protected function getPaymentGateway()
    {
        return new FakePaymentGateway;
    }

    public function test_可以透過callback取得付款的物件()
    {
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(2, $newCharges);
        $this->assertEquals([4000, 5000], $newCharges->all());
    }

    public function test_以合法token付款成功()
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals(2500, $newCharges->sum());
    }

    /**
     * @expectedException App\Billing\PaymentFailedException
     */
    public function test_以不合法token付款失敗()
    {
        $paymentGateway = new FakePaymentGateway;
        $paymentGateway->charge(2500, 'invalid-payment-token');

        // try {
        //     $paymentGateway = new FakePaymentGateway;
        //     $paymentGateway->charge(2500, 'invalid-payment-token');
        // } catch (PaymentFailedException $e) {
        //     return;
        // }

        // $this->fail();
    }


    public function test_在第一次付款前執行hook()
    {
        $paymentGateway = new FakePaymentGateway;
        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use(&$timesCallbackRan) {
            $timesCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertEquals(5000, $paymentGateway->totalCharges());
        $this->assertEquals(1, $timesCallbackRan);
    }
}
