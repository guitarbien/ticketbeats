<?php

namespace Tests\Unit\Mail;

use App\Mail\OrderConfirmationEmail;
use App\Order;
use Tests\TestCase;

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

        // In Laravel 5.5
        // $rendered = $email->render();

        $this->assertContains(url('/orders/ORDERCONFIRMATION1234'), $rendered);
    }

    public function test_email要有主旨()
    {
        $order = factory(Order::class)->make();
        $email = new OrderConfirmationEmail($order);
        $this->assertEquals('Your TicketBeats Order', $email->build()->subject);
    }

    private function render($mailable)
    {
        $mailable->build();

        return view($mailable->view, $mailable->buildViewData())->render();
    }
}
