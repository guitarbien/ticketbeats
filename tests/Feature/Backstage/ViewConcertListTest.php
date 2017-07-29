<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use ConcertFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

class ViewConcertListTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        Collection::macro('assertContains', function($value) {
            Assert::AssertTrue($this->contains($value), "Failed asserting that the collection contained the specified value.");
        });

        Collection::macro('assertNotContains', function($value) {
            Assert::assertFalse($this->contains($value), "Failed asserting that the collection did not contain the specified value.");
        });
    }

    public function test_一般使用者不能看到管理者的音樂列表頁()
    {
        $response = $this->get('/backstage/concerts');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_管理者只可以看到自己的音樂會的列表()
    {
        $this->disableExceptionHandling();
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $publishedConcertA = ConcertFactory::createPublished(['user_id' => $user->id]);
        $publishedConcertB = ConcertFactory::createPublished(['user_id' => $otherUser->id]);
        $publishedConcertC = ConcertFactory::createPublished(['user_id' => $user->id]);

        $unpublishedConcertA = factory(Concert::class)->states('unpublished')->create(['user_id' => $user->id]);
        $unpublishedConcertB = factory(Concert::class)->states('unpublished')->create(['user_id' => $otherUser->id]);
        $unpublishedConcertC = factory(Concert::class)->states('unpublished')->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertStatus(200);

        $response->data('publishedConcerts')->assertContains($publishedConcertA);
        $response->data('publishedConcerts')->assertNotContains($publishedConcertB);
        $response->data('publishedConcerts')->assertContains($publishedConcertC);
        $response->data('publishedConcerts')->assertNotContains($unpublishedConcertA);
        $response->data('publishedConcerts')->assertNotContains($unpublishedConcertB);
        $response->data('publishedConcerts')->assertNotContains($unpublishedConcertC);

        $response->data('unpublishedConcerts')->assertNotContains($publishedConcertA);
        $response->data('unpublishedConcerts')->assertNotContains($publishedConcertB);
        $response->data('unpublishedConcerts')->assertNotContains($publishedConcertC);
        $response->data('unpublishedConcerts')->assertContains($unpublishedConcertA);
        $response->data('unpublishedConcerts')->assertNotContains($unpublishedConcertB);
        $response->data('unpublishedConcerts')->assertContains($unpublishedConcertC);
    }
}
