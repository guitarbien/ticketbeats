<?php

use App\Mail\OrderConfirmationEmail;
use App\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class OrderConfirmationEmailTest extends TestCase
{
    public function test_email內含有連到確認頁的連結()
    {
        $order = factory(Order::class)->make([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);

        $email = new OrderConfirmationEmail($order);

        // get the html rendering content
        $rendered = $this->render($email);

        $this->assertContains(url('/orders/ORDERCONFIRMATION1234'), $rendered);
    }

    private function render($mailable)
    {
        $mailable->build();

        return view($mailable->view, $mailable->buildViewData())->render();
    }
}
