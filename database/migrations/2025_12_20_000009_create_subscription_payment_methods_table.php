<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('subscription-plans.table_names.payment_methods', 'plan_payment_methods');

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('type');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
            $table->index('is_default');
            });
        }
    }

    public function down(): void
    {
        $tableName = config('subscription-plans.table_names.payment_methods', 'plan_payment_methods');
        Schema::dropIfExists($tableName);
    }
};
