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

    private function oldAttributes($overrides = [])
    {
        return array_merge([
            'title'                  => 'old title',
            'subtitle'               => 'old subtitle',
            'additional_information' => 'old additional_information',
            'date'                   => Carbon::parse('2017-01-01 5:00pm'),
            'venue'                  => 'old venue',
            'venue_address'          => 'old venue_address',
            'city'                   => 'old city',
            'state'                  => 'old state',
            'zip'                    => '00000',
            'ticket_price'           => 2000,
            'ticket_quantity'        => 5,
        ], $overrides);
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title'                  => 'new title',
            'subtitle'               => 'new subtitle',
            'additional_information' => 'new additional_information',
            'date'                   => '2018-12-12',
            'time'                   => '8:00pm',
            'venue'                  => 'new venue',
            'venue_address'          => 'new venue_address',
            'city'                   => 'new city',
            'state'                  => 'new state',
            'zip'                    => '99999',
            'ticket_price'           => '72.50',
            'ticket_quantity'        => '10',
        ], $overrides);
    }

    public function test_管理者可以看到自己還沒發佈的音樂會修改頁()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);

        static::assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);
        static::assertTrue($response->data('concert')->is($concert));
    }

    public function test_管理者不能看到自己已經發佈的音樂會修改頁()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);

        static::assertTrue($concert->isPublished());

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

    public function test_管理者可以編輯自己尚未發布的音樂會()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id'                => $user->id,
            'title'                  => 'old title',
            'subtitle'               => 'old subtitle',
            'additional_information' => 'old additional_information',
            'date'                   => Carbon::parse('2017-01-01 5:00pm'),
            'venue'                  => 'old venue',
            'venue_address'          => 'old venue_address',
            'city'                   => 'old city',
            'state'                  => 'old state',
            'zip'                    => '00000',
            'ticket_price'           => 2000,
            'ticket_quantity'        => 5,
        ]);

        static::assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title'                  => 'new title',
            'subtitle'               => 'new subtitle',
            'additional_information' => 'new additional_information',
            'date'                   => '2018-12-12',
            'time'                   => '8:00pm',
            'venue'                  => 'new venue',
            'venue_address'          => 'new venue_address',
            'city'                   => 'new city',
            'state'                  => 'new state',
            'zip'                    => '99999',
            'ticket_price'           => '72.50',
            'ticket_quantity'        => '10',
        ]);

        $response->assertRedirect("/backstage/concerts");
        tap($concert->fresh(), function($concert) {
            static::assertEquals('new title', $concert->title);
            static::assertEquals('new subtitle', $concert->subtitle);
            static::assertEquals('new additional_information', $concert->additional_information);
            static::assertEquals(Carbon::parse('2018-12-12 8:00pm'), $concert->date);
            static::assertEquals('new venue', $concert->venue);
            static::assertEquals('new venue_address', $concert->venue_address);
            static::assertEquals('new city', $concert->city);
            static::assertEquals('new state', $concert->state);
            static::assertEquals('99999', $concert->zip);
            static::assertEquals(7250, $concert->ticket_price);
            static::assertEquals(10, $concert->ticket_quantity);
        });
    }

    public function test_管理者不能編輯別人尚未發布的音樂會()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $otherUser->id,
        ]));

        static::assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(404);

        $oldConcert = $this->oldAttributes(['user_id' => $otherUser->id]);
        $freshConcert = $concert->fresh()->getAttributes();

        foreach ($oldConcert as $key => $value) {
            self::assertEquals($value, $freshConcert[$key]);
        }
    }

    public function test_管理者不能編輯已經發布的音樂會()
    {
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->states('published')->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        static::assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertStatus(403);

        $oldConcert = $this->oldAttributes(['user_id' => $user->id]);
        $freshConcert = $concert->fresh()->getAttributes();

        foreach ($oldConcert as $key => $value) {
            self::assertEquals($value, $freshConcert[$key]);
        }
    }

    public function test_一般使用者不能編輯音樂會()
    {
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        static::assertFalse($concert->isPublished());

        $response = $this->patch("/backstage/concerts/{$concert->id}", $this->validParams());

        $response->assertRedirect('/login');

        $oldConcert = $this->oldAttributes(['user_id' => $user->id]);
        $freshConcert = $concert->fresh()->getAttributes();

        foreach ($oldConcert as $key => $value) {
            self::assertEquals($value, $freshConcert[$key]);
        }
    }

    public function test_title欄位為必填()
    {
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'title'   => 'old title',
        ]);

        static::assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'title' => ''
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('title');
        tap($concert->fresh(), function($concert) {
            static::assertEquals('old title', $concert->title);
        });
    }

    public function test_subtitle欄位為非必填()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'subtitle' => 'Old subtitle',
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'subtitle' => '',
        ]));
        $response->assertRedirect("/backstage/concerts");
        tap($concert->fresh(), function ($concert) {
            static::assertNull($concert->subtitle);
        });
    }

    public function test_additional_information欄位為非必填()
    {$this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'additional_information' => 'Old additional information',
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'additional_information' => '',
        ]));
        $response->assertRedirect("/backstage/concerts");
        tap($concert->fresh(), function ($concert) {
            static::assertNull($concert->additional_information);
        });
    }

    public function test_date欄位為必填()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2018-01-01 8:00pm'),
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'date' => '',
        ]));
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('date');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals(Carbon::parse('2018-01-01 8:00pm'), $concert->date);
        });
    }

    public function test_date欄位格式必須為日期()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2018-01-01 8:00pm'),
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'date' => 'not a date',
        ]));
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('date');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals(Carbon::parse('2018-01-01 8:00pm'), $concert->date);
        });
    }

    public function test_時間為必填()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2018-01-01 8:00pm'),
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'time' => '',
        ]));
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('time');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals(Carbon::parse('2018-01-01 8:00pm'), $concert->date);
        });
    }

    public function test_time欄位格式必須為時間()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2018-01-01 8:00pm'),
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'time' => 'not-a-time',
        ]));
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('time');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals(Carbon::parse('2018-01-01 8:00pm'), $concert->date);
        });
    }

    public function test_venue為必填()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'venue' => 'Old venue',
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'venue' => '',
        ]));
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('venue');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals('Old venue', $concert->venue);
        });
    }

    public function test_venue_address為必填()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'venue_address' => 'Old address',
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'venue_address' => '',
        ]));
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('venue_address');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals('Old address', $concert->venue_address);
        });
    }

    public function test_city為必填()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'city' => 'Old city',
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'city' => '',
        ]));
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('city');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals('Old city', $concert->city);
        });
    }

    public function test_state為必填()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'state' => 'Old state',
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'state' => '',
        ]));
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('state');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals('Old state', $concert->state);
        });
    }

    public function test_zip為必填()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'zip' => 'Old zip',
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'zip' => '',
        ]));
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('zip');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals('Old zip', $concert->zip);
        });
    }

    public function test_ticket_price為必填()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_price' => 5250,
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'ticket_price' => '',
        ]));
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals(5250, $concert->ticket_price);
        });
    }

    public function test_ticket_price必須為數字()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_price' => 5250,
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'ticket_price' => 'not a price',
        ]));
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals(5250, $concert->ticket_price);
        });
    }

    public function test_ticket_price至少要為5()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_price' => 5250,
        ]);
        static::assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'ticket_price' => '4.99',
        ]));
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals(5250, $concert->ticket_price);
        });
    }

    public function test_ticket_quantity為必填()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5,
        ]);
        static::assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'ticket_quantity' => '',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals(5, $concert->ticket_quantity);
        });
    }

    public function test_ticket_quantity必須為數字()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5,
        ]);
        static::assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'ticket_quantity' => '7.8',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals(5, $concert->ticket_quantity);
        });
    }

    public function test_ticket_quantity至少要為1()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5,
        ]);
        static::assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->patch("/backstage/concerts/{$concert->id}", $this->validParams([
            'ticket_quantity' => '0',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');
        tap($concert->fresh(), function ($concert) {
            static::assertEquals(5, $concert->ticket_quantity);
        });
    }
}
