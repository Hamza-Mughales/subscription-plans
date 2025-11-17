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
        Schema::create('plan_subscription_features', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('subscription_id')->unsigned()->nullable();
            $table->bigInteger('feature_id')->unsigned()->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->enum('source', ['default', 'purchased'])->default('default');
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys & Indexes
            $table->unique(['subscription_id', 'feature_id'], 'subscription_feature_unique');
            $table->foreign('subscription_id')->references('id')->on('plan_subscriptions')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('feature_id')->references('id')->on('plan_features')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_subscription_features');
    }
};
