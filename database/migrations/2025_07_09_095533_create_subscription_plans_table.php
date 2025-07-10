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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->default(0.00);
            $table->integer('duration_days')->default(30);
            $table->integer('device_limit')->default(2);
            $table->integer('user_limit')->default(2);
            $table->enum('subscription_type', ['Weekly', 'Monthly', 'Annual'])->default('Monthly');
            $table->enum('subscription_tier', ['Free', 'Basic', 'Pro', 'Enterprise'])->default('Free');
            $table->boolean('is_trial')->default(false); //Have a trial
            $table->integer('trial_period_days')->default(0)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('max_dashboards')->default(1);
            $table->boolean('data_retention')->default(true);
            $table->boolean('api_access')->default(true);
            $table->boolean('white_labeling')->default(true);
            $table->integer('custom_widgets')->default(2);
            $table->boolean('email_alerts')->default(true);
            $table->boolean('sms_integration')->default(true);
            $table->boolean('role_management')->default(true);
            $table->boolean('rule_engine')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
