<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName     = config('subscription-plans.table_names.invoice_items', 'invoice_items');
        $invoicesTable = config('subscription-plans.table_names.invoices', 'invoices');

        Schema::create($tableName, function (Blueprint $table) use ($invoicesTable) {
            $table->id();
            $table->foreignId('invoice_id')->constrained($invoicesTable)->onDelete('cascade');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tableName = config('subscription-plans.table_names.invoice_items', 'invoice_items');
        Schema::dropIfExists($tableName);
    }
};
