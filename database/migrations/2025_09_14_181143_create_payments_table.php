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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->decimal('amount', 10, 2);

            $table->string('method', 32);
            $table->string('status', 32);

            $table->string('transaction_id')->nullable()->unique();

            $table->json('payload')->nullable();

            //Tash kjo po bjen ne kundershtim me logjiken qe munen me nda pagesen disa njerz (pe boj koment tani e shohim qysh ja bojm)
            /*
            $table->boolean('succeeded_flag')->nullable()
                ->storedAs("CASE WHEN status = 'succeeded' THEN 1 ELSE NULL END");
            $table->unique(['order_id', 'succeeded_flag'], 'unique_successful_payment_per_order');
            */

            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
