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
        Schema::create('daily_meals', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('daily_meal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_meal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_id')->constrained()->cascadeOnDelete();
            $table->integer('count')->default(1); // count ustuni qo‘shildi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_meals');
    }
};
