<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('boxes', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('bar_code')->unique();
            $table->string('title', 255);
            $table->string('original_title', 255)->nullable();
            $table->integer('year')->nullable();
            $table->text('synopsis')->nullable();
            $table->string('edition')->nullable();
            $table->string('editor')->nullable();
            $table->string('dvdfr_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boxes');
    }
};
