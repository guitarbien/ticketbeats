<?php

use App\Billing\FakePaymentGateway;
// use App\Billing\PaymentFailedException;
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
