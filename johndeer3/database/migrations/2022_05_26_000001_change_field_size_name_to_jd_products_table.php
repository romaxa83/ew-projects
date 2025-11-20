<?php

use App\Models\JD\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFieldSizeNameToJdProductsTable extends Migration
{
    public function up(): void
    {
        Schema::table(Product::TABLE, function (Blueprint $table) {
            $table->float('size_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table(Product::TABLE, function (Blueprint $table) {
            $table->integer('size_name')->nullable()->change();
        });
    }
}
