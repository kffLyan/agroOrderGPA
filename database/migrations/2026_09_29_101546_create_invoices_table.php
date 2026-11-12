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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->date('period_start')->nullable();
            $table->date('period_end');
            $table->decimal('subtotal_amount', 14, 2);
            $table->decimal('grand_total', 14, 2);
            $table->date('due_date');
            $table->enum('status', ['UNPAID', 'PARTIAL', 'PAID', 'OVERDUE'])->default('UNPAID');
            $table->string('pdf_file_path', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
