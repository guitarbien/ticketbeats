<?php

namespace Tests\Feature\Backstage;

use App\User;
use Carbon\Carbon;
use ConcertFactory;
use OrderFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ViewPublishedConcertOrdersTest extends TestCase
{
    use DatabaseMigrations;

    public function test_管理者可以看到自己已發佈的音樂會的訂單()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.published-concert-orders.index');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    public function test_管理者可以看到最多十筆自己已發佈的音樂會的訂單()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $oldOrder = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('11 days ago')]);
        $recentOrder10 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('10 days ago')]);
        $recentOrder9 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('9 days ago')]);
        $recentOrder8 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('8 days ago')]);
        $recentOrder7 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('7 days ago')]);
        $recentOrder6 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('6 days ago')]);
        $recentOrder5 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('5 days ago')]);
        $recentOrder4 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('4 days ago')]);
        $recentOrder3 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('3 days ago')]);
        $recentOrder2 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('2 days ago')]);
        $recentOrder1 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('1 days ago')]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->data('orders')->assertNotContains($oldOrder);
        $response->data('orders')->assertEquals([
            $recentOrder1,
            $recentOrder2,
            $recentOrder3,
            $recentOrder4,
            $recentOrder5,
            $recentOrder6,
            $recentOrder7,
            $recentOrder8,
            $recentOrder9,
            $recentOrder10,
        ]);
    }

    public function test_管理者不能看尚未發佈的音樂會的訂單()
    {
        $user = factory(User::class)->create();

        $concert = ConcertFactory::createUnpublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");
        $response->assertStatus(404);
    }

    public function test_管理者不能看別人已發佈的音樂會的訂單()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $concert = ConcertFactory::createPublished(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");
        $response->assertStatus(404);
    }

    public function test_一般使用者不能看到任何已發佈的音樂會的訂單()
    {
        $concert = ConcertFactory::createPublished();

        $response = $this->get("/backstage/published-concerts/{$concert->id}/orders");
        $response->assertRedirect('/login');
    }
}