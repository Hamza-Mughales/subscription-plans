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
        Schema::create('plan_subscription_usage', function (Blueprint $table) {
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
            $table->foreign('subscription_id')->references('id')->on('plan_subscriptions')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('feature_id')->references('id')->on('plan_features')
                ->onDelete('cascade')->onUpdate('cascade');

            // Performance Indexes
            $table->index('valid_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_subscription_usage');
    }
};
