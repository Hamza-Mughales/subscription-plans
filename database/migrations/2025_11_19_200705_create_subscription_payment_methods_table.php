<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('subscription-plans.table_names.payment_methods', 'plan_payment_methods');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->json('name'); // Translatable
            $table->string('slug', 190)->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tableName = config('subscription-plans.table_names.payment_methods', 'plan_payment_methods');
        Schema::dropIfExists($tableName);
    }
};
