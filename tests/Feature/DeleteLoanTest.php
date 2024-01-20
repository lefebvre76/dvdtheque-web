<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Loan;
use App\Models\Box;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DeleteLoanTest extends TestCase
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

    public function test_unauth_user_delete_loan(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);

        $response = $this->deleteJson(route('api.loan.delete', ['loan' => $loan->id]));

        $response->assertStatus(401);
    }

    public function test_auth_user_can_delete_his_loan(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('api.loan.delete', ['loan' => $loan->id]));

            $response->assertStatus(204);
    }

    public function test_auth_user_cannot_delete_loan_to_other_user(): void
    {
        $box = Box::factory()->create();
        $user = User::factory()->create();
        $user->boxes()->syncWithoutDetaching([
            $box->id => ['wishlist' => false]
        ]);
        $loan = $this->createLoan($user, $box);

        $response = $this
            ->actingAs(User::factory()->create())
            ->deleteJson(route('api.loan.delete', ['loan' => $loan->id]));

            $response->assertStatus(404);
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