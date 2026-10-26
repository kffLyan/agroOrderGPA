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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 30)->unique();
            $table->string('name', 100);
            $table->string('unit', 20)->default('kg');
            $table->string('grade', 20)->default('Grade A');
            $table->decimal('base_price', 12, 2);
            $table->decimal('minimum_order', 10, 2)->default(1.00);
            $table->string('image_url', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
