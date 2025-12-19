<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName     = config('subscription-plans.table_names.invoice_transactions', 'plan_invoice_transactions');
        $invoicesTable = config('subscription-plans.table_names.invoices', 'plan_invoices');

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($invoicesTable) {
            $table->id();
            $table->foreignId('invoice_id')->constrained($invoicesTable)->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('status')->nullable()->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('payment_method');
            $table->index('transaction_id');
            });
        }
    }

    public function down(): void
    {
        $tableName = config('subscription-plans.table_names.invoice_transactions', 'plan_invoice_transactions');
        Schema::dropIfExists($tableName);
    }
};
