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
        Schema::create('harvest_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict')->onUpdate('cascade');
            $table->enum('source_type', ['PETANI_BINAAN', 'BUFFER_LUAR'])->default('PETANI_BINAAN');
            $table->string('supplier_name', 100);
            $table->date('batch_date');
            $table->decimal('initial_quantity', 10, 2);
            $table->decimal('available_quantity', 10, 2);
            $table->foreignId('inputted_by')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();

            $table->index(['product_id', 'batch_date', 'available_quantity'], 'idx_batches_product_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harvest_batches');
    }
};
