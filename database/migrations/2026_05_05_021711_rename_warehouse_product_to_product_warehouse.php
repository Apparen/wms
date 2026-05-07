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
        Schema::rename('warehouse_product', 'product_warehouse');
    }

    public function down(): void
    {
        Schema::rename('product_warehouse', 'warehouse_product');
    }
};
