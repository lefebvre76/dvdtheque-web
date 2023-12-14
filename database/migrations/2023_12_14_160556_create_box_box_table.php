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
        Schema::create('box_box', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pack_id');
            $table->foreignId('box_id')->constrained();
            $table->timestamps();

            $table->foreign('pack_id')->references('id')->on('boxes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('box_box');
    }
};
