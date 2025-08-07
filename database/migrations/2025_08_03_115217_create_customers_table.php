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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('telegram')->nullable();
            $table->enum('status', ['Active', 'Blok'])->default('Active');
                $table->enum('type', ['oylik', 'odiy'])->default('odiy');

            // Address information
            $table->string('address')->nullable();
            $table->string('district')->nullable();
            $table->string('location_coordinates')->nullable();

            // Balance information
            $table->decimal('balance', 12, 0)->default(0);
            $table->date('balance_due_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
