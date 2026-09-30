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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict')->onUpdate('cascade');
            $table->decimal('ordered_qty', 10, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('actual_net_weight', 10, 2)->nullable();
            $table->decimal('subtotal_final', 14, 2)->nullable();
            $table->foreignId('weighed_by')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('weighed_at')->nullable();
            $table->decimal('returned_weight', 10, 2)->nullable();
            $table->string('return_reason', 255)->nullable();
            $table->timestamps();

            $table->index(['order_id', 'product_id'], 'idx_order_items_composite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
