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
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->integer('plan_id')->unsigned()->nullable();
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
            $table->foreign('plan_id')->references('id')->on('plans')
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
        Schema::dropIfExists('plan_features');
    }
};
