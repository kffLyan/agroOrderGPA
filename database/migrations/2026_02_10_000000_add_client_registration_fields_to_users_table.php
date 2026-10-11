<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('client_type')->default('reguler')->after('id');
            $table->string('business_name')->nullable()->after('name');
            $table->string('phone')->nullable()->unique()->after('email');
            $table->boolean('email_is_placeholder')->default(false)->after('phone');
            $table->text('address')->nullable();
            $table->string('delivery_zone')->nullable();
            $table->string('delivery_window')->nullable();
            $table->string('vehicle_access')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('payment_method')->nullable();
            $table->json('preferred_commodities')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->timestamp('integrity_accepted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'client_type',
                'business_name',
                'phone',
                'email_is_placeholder',
                'address',
                'delivery_zone',
                'delivery_window',
                'vehicle_access',
                'delivery_notes',
                'payment_method',
                'preferred_commodities',
                'otp_verified_at',
                'integrity_accepted_at',
            ]);
        });
    }
};
