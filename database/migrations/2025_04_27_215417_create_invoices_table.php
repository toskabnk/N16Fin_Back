<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('odoo_invoice_id')->unique();
            $table->string('reference');
            $table->date('invoice_date');
            $table->number('month');
            $table->decimal('amount_total', 15, 2);
            $table->string('state');
            $table->boolean('manual')->default(false);
            $table->json('centers')->nullable();
            $table->foreignId('business_line_id');
            $table->foreignId('supplier_id');
            $table->foreignId('share_type_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odoo_invoices');
    }
};
