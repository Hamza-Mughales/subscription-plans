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
        $tableName              = config('subscription-plans.table_names.plan_subscription_features', 'plan_subscription_features');
        $planSubscriptionsTable = config('subscription-plans.table_names.plan_subscriptions', 'plan_subscriptions');
        $planFeaturesTable      = config('subscription-plans.table_names.plan_features', 'plan_features');

        Schema::create($tableName, function (Blueprint $table) use ($planSubscriptionsTable, $planFeaturesTable) {
            $table->id();
            $table->bigInteger('subscription_id')->unsigned()->nullable();
            $table->bigInteger('feature_id')->unsigned()->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->enum('source', ['default', 'purchased'])->default('default');
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys & Indexes
            $table->unique(['subscription_id', 'feature_id'], 'subscription_feature_unique');
            $table->foreign('subscription_id')->references('id')->on($planSubscriptionsTable)
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('feature_id')->references('id')->on($planFeaturesTable)
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('subscription-plans.table_names.plan_subscription_features', 'plan_subscription_features');
        Schema::dropIfExists($tableName);
    }
};
