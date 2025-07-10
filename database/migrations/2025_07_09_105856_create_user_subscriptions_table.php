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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->onDelete('restrict');
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['active', 'pending', 'cancelled', 'expired', 'trial'])->default('active');
            $table->integer('effective_device_limit')->nullable();
            $table->integer('effective_user_limit')->nullable();
            $table->integer('effective_max_dashboards')->nullable();
            $table->integer('effective_custom_widgets')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'subscription_plan_id']);
            $table->unique(['user_id', 'subscription_plan_id'], 'user_plan_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
