<?php
namespace Tests\Feature\Backstage;
use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class EditConcertTest extends TestCase
{
    use DatabaseMigrations;

    public function test_管理者可以看到自己還沒發佈的音樂會修改頁()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    public function test_管理者不能看到自己已經發佈的音樂會修改頁()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");
        $response->assertStatus(403);
    }

    public function test_管理者不能看到別人的音樂會修改頁()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");
        $response->assertStatus(404);
    }

    public function test_管理者不能看到不存在的音樂會修改頁()
    {
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->get("/backstage/concerts/999/edit");
        $response->assertStatus(404);
    }

    public function test_一般使用者要看任何音樂會修改頁都必須導到登入頁()
    {
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);
        $response = $this->get("/backstage/concerts/{$concert->id}/edit");
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_一般使用者要看任何音樂會修改頁都必須導到登入頁即使音樂會不存在()
    {
        $response = $this->get("/backstage/concerts/999/edit");
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
