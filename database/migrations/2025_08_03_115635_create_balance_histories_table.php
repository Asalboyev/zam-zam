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
        // Migration
        Schema::create('balance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->decimal('amount', 12, 0);
            $table->enum('type', ['payment', 'order', 'correction']);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_histories');
    }
};
