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
        $tableName  = config('subscription-plans.table_names.plan_features', 'plan_features');
        $plansTable = config('subscription-plans.table_names.plans', 'plan_plans');

        Schema::create($tableName, function (Blueprint $table) use ($plansTable) {
            $table->id();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('slug')->nullable();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('value')->nullable();
            $table->smallInteger('resettable_period')->unsigned()->default(0);
            $table->string('resettable_interval')->default('month');
            $table->mediumInteger('sort_order')->unsigned()->default(0);
            $table->decimal('price', 10, 2)->default(0.00);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('plan_id')->references('id')->on($plansTable)
                ->onDelete('cascade')->onUpdate('cascade');

            // Performance Indexes
            $table->index(['plan_id', 'slug'], 'plan_slug_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('subscription-plans.table_names.plan_features', 'plan_features');
        Schema::dropIfExists($tableName);
    }
};
