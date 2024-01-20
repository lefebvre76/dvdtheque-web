<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Loan;
use App\Models\Box;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateLoanTest extends TestCase
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

    public function test_unauth_user_update_loan(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);

        $response = $this->putJson(route('api.loan.update', ['loan' => $loan->id]), [
            'box_id' => $box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response->assertStatus(401);
    }

    public function test_auth_user_can_not_update_loan_if_he_does_set_required_values(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);

        $response = $this
            ->actingAs($user)
            ->putJson(route('api.loan.update', ['loan' => $loan->id]), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('box_id')
            ->assertJsonValidationErrors('type')
            ->assertJsonValidationErrors('contact');
    }

    public function test_auth_user_can_not_update_loan_if_with_box_he_does_not_have(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);

        $other_box = Box::factory()->create();
        $response = $this
            ->actingAs($user)
            ->putJson(route('api.loan.update', ['loan' => $loan->id]), [
                'box_id' => $other_box->id,
                'type' => Loan::TYPE_LOAN,
                'contact' => 'John Doo'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('box_id');
    }

    public function test_auth_user_can_update_loan_with_same_box(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);

        $response = $this
            ->actingAs($user)
            ->putJson(route('api.loan.update', ['loan' => $loan->id]), [
                'box_id' => $box->id,
                'type' => Loan::TYPE_LOAN,
                'contact' => 'John Doo 2'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'contact' => 'John Doo 2'
            ]);
    }

    public function test_auth_user_cannot_update_loan_with_box_already_loaned(): void
    {
        $box = Box::factory()->create();
        $other_box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $user->boxes()->syncWithoutDetaching([
            $other_box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);
        $this->createLoan($user, $other_box);

        $response = $this
        ->actingAs($user)
        ->putJson(route('api.loan.update', ['loan' => $loan->id]), [
            'box_id' => $other_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response->assertStatus(403);
    }

    public function test_auth_user_can_update_loan_with_other_box_not_loaned(): void
    {
        $box = Box::factory()->create();
        $other_box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $user->boxes()->syncWithoutDetaching([
            $other_box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);

        $response = $this
        ->actingAs($user)
        ->putJson(route('api.loan.update', ['loan' => $loan->id]), [
            'box_id' => $other_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'box' => [
                    'id' => $other_box->id
                ]
            ]);
    }

    public function test_auth_user_can_update_loan_with_other_box_in_wishlist(): void
    {
        $box = Box::factory()->create();
        $other_box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $user->boxes()->syncWithoutDetaching([
            $other_box->id => ['wishlist' => true]
        ]);
        $loan = $this->createLoan($user, $box);

        $response = $this
        ->actingAs($user)
        ->putJson(route('api.loan.update', ['loan' => $loan->id]), [
            'box_id' => $other_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('box_id');
    }

    public function test_auth_user_can_update_loan_if_he_has_parent_box(): void
    {
        $user = User::factory()->create();
        $box = Box::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);

        $parent_box = Box::factory()->create();
        $child_box = Box::factory()->create();
        $parent_box->boxes()->attach($child_box);
        $user->boxes()->syncWithoutDetaching([
            $parent_box->id => ['wishlist' => false]
        ]);
        $response = $this
        ->actingAs($user)
        ->putJson(route('api.loan.update', ['loan' => $loan->id]), [
            'box_id' => $child_box->id,
            'box_parent_id' => $parent_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response->assertStatus(200);
    }

    public function test_auth_user_can_update_loan_with_parent_box(): void
    {
        $user = User::factory()->create();
        $box = Box::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);

        $parent_box = Box::factory()->create();
        $child_box = Box::factory()->create();
        $parent_box->boxes()->attach($child_box);
        $user->boxes()->syncWithoutDetaching([
            $parent_box->id => ['wishlist' => false]
        ]);
        $response = $this
        ->actingAs($user)
        ->putJson(route('api.loan.update', ['loan' => $loan->id]), [
            'box_id' => $parent_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo'
        ]);

        $response->assertStatus(200);
    }

    public function test_auth_user_cannot_update_loan_with_parent_box_already_loaned(): void
    {
        $user = User::factory()->create();
        $box = Box::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);

        $parent_box = Box::factory()->create();
        $child_box = Box::factory()->create();
        $parent_box->boxes()->attach($child_box);
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
        ->putJson(route('api.loan.update', ['loan' => $loan->id]), [
            'box_id' => $parent_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo 2'
        ]);

        $response->assertStatus(403);
    }

    public function test_auth_user_cannot_update_loan_if_parent_box_already_loaned(): void
    {
        $user = User::factory()->create();
        $box = Box::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);

        $parent_box = Box::factory()->create();
        $child_box = Box::factory()->create();
        $parent_box->boxes()->attach($child_box);
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
        ->putJson(route('api.loan.update', ['loan' => $loan->id]), [
            'box_id' => $child_box->id,
            'box_parent_id' => $parent_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo 2'
        ]);

        $response->assertStatus(403);
    }

    public function test_auth_user_cannot_update_loan_if_box_and_parent_box_are_not_linked(): void
    {
        $user = User::factory()->create();
        $box = Box::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);

        $parent_box = Box::factory()->create();
        $child_box = Box::factory()->create();
        $parent_box->boxes()->attach($child_box);

        $parent_2_box = Box::factory()->create();
        $child_2_box = Box::factory()->create();
        $parent_2_box->boxes()->attach($child_2_box);

        $user->boxes()->syncWithoutDetaching([
            $parent_box->id => ['wishlist' => false],
            $parent_2_box->id => ['wishlist' => false],
        ]);

        $response = $this
        ->actingAs($user)
        ->putJson(route('api.loan.update', ['loan' => $loan->id]), [
            'box_id' => $child_2_box->id,
            'box_parent_id' => $parent_box->id,
            'type' => Loan::TYPE_LOAN,
            'contact' => 'John Doo 2'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('box_id');
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