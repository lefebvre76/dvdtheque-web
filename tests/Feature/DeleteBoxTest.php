<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Loan;
use App\Models\Box;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DeleteBoxTest extends TestCase
{
    use DatabaseMigrations {
        runDatabaseMigrations as baseRunDatabaseMigrations;
    }

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function runDatabaseMigrations()
    {
        $this->baseRunDatabaseMigrations();
        $this->artisan('db:seed');
    }

    public function test_unauth_user_delete_box(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);

        $response = $this->deleteJson(route('api.box.me.remove', ['box' => $box->id]));
        $response->assertStatus(401);
    }

    public function test_auth_user_can_delete_his_box(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('api.box.me.remove', ['box' => $box->id]));
        $response->assertStatus(204);
    }

    public function test_auth_user_cannot_delete_box_with_loan(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $this->createLoan($user, $box);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('api.box.me.remove', ['box' => $box->id]));
        $response->assertStatus(403);
    }

    public function test_auth_user_cannot_delete_box_with_child_loaned(): void
    {
        $child_box = Box::factory()->create();
        $parent_box = Box::factory()->create();
        $parent_box->boxes()->attach($child_box);
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $parent_box->id => ['wishlist' => false]
        ]);
        Loan::create([
            'user_id' => $user->id,
            'box_id' => $child_box->id,
            'box_parent_id' => $parent_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('api.box.me.remove', ['box' => $parent_box->id]));
        $response->assertStatus(403);
    }

    private function createLoan($user, $box) {
        return Loan::create([
            'user_id' => $user->id,
            'box_id' => $box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);
    }
}