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
        $tableName  = config('subscription-plans.table_names.plan_modules', 'plan_modules');
        $plansTable = config('subscription-plans.table_names.plans', 'plan_plans');

        Schema::create($tableName, function (Blueprint $table) use ($plansTable) {
            $table->id();
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('module');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->foreign('plan_id')->references('id')->on($plansTable)
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('subscription-plans.table_names.plan_modules', 'plan_modules');
        Schema::dropIfExists($tableName);
    }
};
