<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Loan;
use App\Models\Box;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PostLoanTest extends TestCase
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

    public function test_unauth_user_post_loan(): void
    {
        $box = Box::factory()->create();
        $response = $this->postJson(route('api.loan.store'), [
            'box_id' => $box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response->assertStatus(401);
    }

    public function test_auth_user_can_not_post_loan_if_he_does_set_required_values(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $response = $this
        ->actingAs($user)
        ->postJson(route('api.loan.store'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('box_id')
            ->assertJsonValidationErrors('type')
            ->assertJsonValidationErrors('contact');
    }

    public function test_auth_user_can_not_post_loan_if_he_does_not_have_box(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $response = $this
        ->actingAs($user)
        ->postJson(route('api.loan.store'), [
            'box_id' => $box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('box_id');
    }

    public function test_auth_user_can_post_loan_if_he_has_box(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $response = $this
        ->actingAs($user)
        ->postJson(route('api.loan.store'), [
            'box_id' => $box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response->assertStatus(201);
    }

    public function test_auth_user_cannot_post_loan_if_box_is_already_loaned(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        Loan::create([
            'user_id' => $user->id,
            'box_id' => $box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response = $this
        ->actingAs($user)
        ->postJson(route('api.loan.store'), [
            'box_id' => $box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo 2'
        ]);

        $response->assertStatus(403);
    }

    public function test_auth_user_can_post_loan_if_he_has_parent_box(): void
    {
        $parent_box = Box::factory()->create();
        $child_box = Box::factory()->create();
        $parent_box->boxes()->attach($child_box);
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $parent_box->id => ['wishlist' => false]
        ]);
        $response = $this
        ->actingAs($user)
        ->postJson(route('api.loan.store'), [
            'box_id' => $child_box->id,
            'box_parent_id' => $parent_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response->assertStatus(201);
    }

    public function test_auth_user_can_post_loan_with_parent_box(): void
    {
        $parent_box = Box::factory()->create();
        $child_box = Box::factory()->create();
        $parent_box->boxes()->attach($child_box);
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $parent_box->id => ['wishlist' => false]
        ]);
        $response = $this
        ->actingAs($user)
        ->postJson(route('api.loan.store'), [
            'box_id' => $parent_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response->assertStatus(201);
    }

    public function test_auth_user_cannot_post_loan_with_parent_box_already_loaned(): void
    {
        $parent_box = Box::factory()->create();
        $child_box = Box::factory()->create();
        $parent_box->boxes()->attach($child_box);
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $parent_box->id => ['wishlist' => false]
        ]);
        Loan::create([
            'user_id' => $user->id,
            'box_id' => $parent_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response = $this
        ->actingAs($user)
        ->postJson(route('api.loan.store'), [
            'box_id' => $parent_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo 2'
        ]);

        $response->assertStatus(403);
    }

    public function test_auth_user_cannot_post_loan_if_parent_box_already_loaned(): void
    {
        $parent_box = Box::factory()->create();
        $child_box = Box::factory()->create();
        $parent_box->boxes()->attach($child_box);
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $parent_box->id => ['wishlist' => false]
        ]);
        Loan::create([
            'user_id' => $user->id,
            'box_id' => $parent_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response = $this
        ->actingAs($user)
        ->postJson(route('api.loan.store'), [
            'box_id' => $child_box->id,
            'box_parent_id' => $parent_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo 2'
        ]);

        $response->assertStatus(403);
    }

    public function test_auth_user_cannot_post_loan_if_box_and_parent_box_are_not_linked(): void
    {
        $parent_box = Box::factory()->create();
        $child_box = Box::factory()->create();
        $parent_box->boxes()->attach($child_box);

        $parent_2_box = Box::factory()->create();
        $child_2_box = Box::factory()->create();
        $parent_2_box->boxes()->attach($child_2_box);

        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $parent_box->id => ['wishlist' => false],
            $parent_2_box->id => ['wishlist' => false],
        ]);

        $response = $this
        ->actingAs($user)
        ->postJson(route('api.loan.store'), [
            'box_id' => $child_2_box->id,
            'box_parent_id' => $parent_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo 2'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('box_id');
    }

    public function test_auth_user_can_borrow_box(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $response = $this
            ->actingAs($user)
            ->postJson(route('api.loan.store'), [
                'box_id' => $box->id,
                'type' => Loan::TYPE_BORROW,
                'contact' => 'John Doo'
            ]);

        $response->assertStatus(201);
    }

    public function test_auth_user_can_borrow_box_he_already_has(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false],
        ]);
        $response = $this
            ->actingAs($user)
            ->postJson(route('api.loan.store'), [
                'box_id' => $box->id,
                'type' => Loan::TYPE_BORROW,
                'contact' => 'John Doo'
            ]);

        $response->assertStatus(201);
    }

    public function test_auth_user_can_borrow_box_he_already_has_and_he_leaned(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false],
        ]);
        Loan::create([
            'user_id' => $user->id,
            'box_id' => $box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson(route('api.loan.store'), [
                'box_id' => $box->id,
                'type' => Loan::TYPE_BORROW,
                'contact' => 'John Doo'
            ]);

        $response->assertStatus(201);
    }
}