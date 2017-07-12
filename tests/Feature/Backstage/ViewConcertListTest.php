<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ViewConcertListTest extends TestCase
{
    use DatabaseMigrations;

    public function test_一般使用者不能看到管理者的音樂列表頁()
    {
        $response = $this->get('/backstage/concerts');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_管理者只可以看到自己的音樂會的列表()
    {
        $user = factory(User::class)->create();
        $concerts = factory(Concert::class, 3)->create(['user_id' => $user->id]);

        $otherUser = factory(User::class)->create();
        $otherConcerts = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertStatus(200);
        $this->assertTrue($response->original->getData()['concerts']->contains($concerts[0]));
        $this->assertTrue($response->original->getData()['concerts']->contains($concerts[1]));
        $this->assertTrue($response->original->getData()['concerts']->contains($concerts[2]));
        $this->assertFalse($response->original->getData()['concerts']->contains($otherConcerts));
    }
}
