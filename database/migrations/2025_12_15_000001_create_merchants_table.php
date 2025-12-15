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
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_name')->unique();
            $table->string('api_key')->unique();
            
            // Encrypted M-Pesa credentials
            $table->text('mpesa_shortcode');
            $table->text('mpesa_passkey');
            $table->text('mpesa_initiator_name');
            $table->text('mpesa_initiator_password');
            $table->text('mpesa_consumer_key');
            $table->text('mpesa_consumer_secret');
            
            // Status and metadata
            $table->boolean('is_active')->default(true);
            $table->string('environment')->default('sandbox'); // sandbox or production
            $table->timestamp('last_used_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('api_key');
            $table->index('merchant_name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
