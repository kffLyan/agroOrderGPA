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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null')->onUpdate('cascade');
            $table->enum('order_source', ['WEB_PORTAL', 'WHATSAPP_ADMIN', 'ROUTINE_SCHEDULE'])->default('WEB_PORTAL');
            $table->date('target_delivery_date');
            $table->text('delivery_address');
            $table->decimal('estimated_total', 14, 2)->default(0.00);
            $table->decimal('grand_total', 14, 2)->nullable();
            $table->enum('status', [
                'DRAFT',
                'MENUNGGU_VERIFIKASI',
                'TERVERIFIKASI',
                'SIAP_KIRIM',
                'DALAM_PENGIRIMAN',
                'SELESAI',
                'SELESAI_CATATAN',
                'BATAL'
            ])->default('MENUNGGU_VERIFIKASI');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->string('surat_jalan_number', 50)->nullable()->unique();
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->string('vehicle_plate_number', 20)->nullable();
            $table->timestamp('departure_time')->nullable();
            $table->timestamp('arrival_time')->nullable();
            $table->string('pod_photo_url', 255)->nullable();
            $table->string('received_by_name', 100)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status'], 'idx_orders_user_status');
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
