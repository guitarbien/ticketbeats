<?php

namespace Tests\Feature\Backstage;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * Class ViewConcertListTest
 * @package Tests\Feature\Backstage
 */
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
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $publishedConcertA = \Database\Factories\ConcertFactory::createPublished(['user_id' => $user->id]);
        $publishedConcertB = \Database\Factories\ConcertFactory::createPublished(['user_id' => $otherUser->id]);
        $publishedConcertC = \Database\Factories\ConcertFactory::createPublished(['user_id' => $user->id]);

        $unpublishedConcertA = \Database\Factories\ConcertFactory::createunPublished(['user_id' => $user->id]);
        $unpublishedConcertB = \Database\Factories\ConcertFactory::createunPublished(['user_id' => $otherUser->id]);
        $unpublishedConcertC = \Database\Factories\ConcertFactory::createunPublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertStatus(200);

        $response->data('publishedConcerts')->assertEquals([
            $publishedConcertA,
            $publishedConcertC,
        ]);

        $response->data('unpublishedConcerts')->assertEquals([
            $unpublishedConcertA,
            $unpublishedConcertC,
        ]);
    }
}
