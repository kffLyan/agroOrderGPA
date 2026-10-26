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
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null')->onUpdate('cascade');
            $table->string('payment_reference', 100)->unique();
            $table->string('payment_method', 50)->default('TRANSFER_BANK');
            $table->decimal('amount', 14, 2);
            $table->string('proof_url', 255)->nullable();
            $table->enum('status', ['MENUNGGU_VERIFIKASI', 'LUNAS', 'DITOLAK'])->default('MENUNGGU_VERIFIKASI');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->timestamp('paid_at')->nullable();
            $table->string('verification_note', 255)->nullable();
            $table->timestamps();
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
