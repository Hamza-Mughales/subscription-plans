<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName              = config('subscription-plans.table_names.invoices', 'plan_invoices');
        $subscriberKey          = config('subscription-plans.foreign_keys.subscriber_id', 'subscriber_id');
        $planSubscriptionsTable = config('subscription-plans.table_names.plan_subscriptions', 'plan_subscriptions');

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($subscriberKey, $planSubscriptionsTable) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('subscription_id')->constrained($planSubscriptionsTable)->onDelete('cascade');
            $table->unsignedBigInteger($subscriberKey);
            $table->decimal('amount', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->string('status')->default('new');
            $table->date('due_date')->nullable();
            $table->date('exp_date')->nullable();
            $table->boolean('paid')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index($subscriberKey);
            $table->index('subscription_id');
            $table->index('status');
            $table->index('paid');
            $table->index('due_date');
            $table->index('invoice_number');
            });
        }
    }

    public function down(): void
    {
        $tableName = config('subscription-plans.table_names.invoices', 'plan_invoices');
        Schema::dropIfExists($tableName);
    }
};
