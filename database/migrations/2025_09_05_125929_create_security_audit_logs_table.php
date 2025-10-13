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
        Schema::create('security_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100)->index();
            $table->json('data');
            $table->string('ip_address', 45)->index();
            $table->string('user_agent', 500)->nullable();
            $table->string('admin_id', 128)->nullable()->index();
            $table->string('restaurant_uid', 128)->nullable()->index();
            $table->timestamp('created_at')->index();
            $table->timestamp('updated_at')->nullable();
            
            // Indexes for performance
            $table->index(['event_type', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['admin_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_audit_logs');
    }
};
