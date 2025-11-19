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
        $tableName  = config('subscription-plans.table_names.plan_subscriptions', 'plan_subscriptions');
        $plansTable = config('subscription-plans.table_names.plans', 'plan_plans');

        Schema::create($tableName, function (Blueprint $table) use ($plansTable) {
            $table->id();
            $table->morphs('subscriber');
            $table->bigInteger('plan_id')->unsigned()->nullable();
            $table->string('slug')->nullable();
            $table->json('name')->nullable();
            $table->json('description')->nullable();
            $table->string('subscription_type')->nullable();
            $table->dateTime('trial_ends_at')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('cancels_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->string('timezone')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('plan_id')->references('id')->on($plansTable)
                ->onDelete('cascade')->onUpdate('cascade');

            // Performance Indexes
            $table->index(['subscriber_type', 'subscriber_id'], 'subscriber_index');
            $table->index(['is_active', 'ends_at'], 'active_ends_index');
            $table->index(['plan_id', 'is_active'], 'plan_active_index');
            $table->index('trial_ends_at');
            $table->index('canceled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('subscription-plans.table_names.plan_subscriptions', 'plan_subscriptions');
        Schema::dropIfExists($tableName);
    }
};
