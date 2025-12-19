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
        $tableName              = config('subscription-plans.table_names.plan_subscription_usage', 'plan_subscription_usage');
        $planSubscriptionsTable = config('subscription-plans.table_names.plan_subscriptions', 'plan_subscriptions');
        $planFeaturesTable      = config('subscription-plans.table_names.plan_features', 'plan_features');

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($planSubscriptionsTable, $planFeaturesTable) {
            $table->id();
            $table->bigInteger('subscription_id')->unsigned()->nullable();
            $table->bigInteger('feature_id')->unsigned()->nullable();
            $table->bigInteger('used')->unsigned()->default(0);
            $table->dateTime('valid_until')->nullable();
            $table->string('timezone')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys & Indexes
            $table->unique(['subscription_id', 'feature_id']);
            $table->foreign('subscription_id')->references('id')->on($planSubscriptionsTable)
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('feature_id')->references('id')->on($planFeaturesTable)
                ->onDelete('cascade')->onUpdate('cascade');

            // Performance Indexes
            $table->index('valid_until');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('subscription-plans.table_names.plan_subscription_usage', 'plan_subscription_usage');
        Schema::dropIfExists($tableName);
    }
};
