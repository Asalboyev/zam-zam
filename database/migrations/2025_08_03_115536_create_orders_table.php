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
        // Migration for orders table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Mijoz
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('order_date')->default(now());

            // ✅ Kunlik tartib raqami (har kuni 0 dan boshlanadi)
            $table->unsignedInteger('daily_order_number');

            // Ovqatlar
            $table->foreignId('meal_1_id')->nullable()->constrained('meals');
            $table->integer('meal_1_quantity')->nullable();
            $table->foreignId('meal_2_id')->nullable()->constrained('meals');
            $table->integer('meal_2_quantity')->nullable();
            $table->foreignId('meal_3_id')->nullable()->constrained('meals');
            $table->integer('meal_3_quantity')->nullable();
            $table->foreignId('meal_4_id')->nullable()->constrained('meals');
            $table->integer('meal_4_quantity')->nullable();
            $table->integer('total_meals')->nullable();

            // Qo‘shimchalar
            $table->integer('cola_quantity')->default(0);

            // Yetkazib berish
            $table->decimal('delivery_fee', 12, 3)->default(20000);
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');

            // To‘lov
            $table->enum('payment_method', ['naqt', 'karta', 'transfer'])->default('naqt');
            $table->decimal('total_amount', 12, 0);

            // ✅ Mijozdan olingan haqiqiy summa
            $table->decimal('received_amount', 12, 0)->default(0);

            // Status
            $table->enum('status', ['new', 'preparing', 'delivered', 'cancelled'])->default('new');

            // ✅ (Ixtiyoriy) admin uchun izoh
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
