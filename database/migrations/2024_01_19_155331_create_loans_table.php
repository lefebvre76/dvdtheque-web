<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Loan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('box_id')->constrained();
            $table->unsignedBigInteger('box_parent_id')->nullable();
            $table->enum('type', [Loan::TYPE_LOAN, Loan::TYPE_BORROW]);
            $table->string('contact');
            $table->json('contact_informations')->nullable();
            $table->datetime('reminder')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('box_parent_id')->references('id')->on('boxes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
