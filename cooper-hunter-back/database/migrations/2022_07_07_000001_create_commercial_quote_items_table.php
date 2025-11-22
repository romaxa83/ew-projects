<?php

use App\Models\Catalog\Products\Product;
use App\Models\Commercial\CommercialQuote;
use App\Models\Commercial\QuoteItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(QuoteItem::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedInteger('commercial_quote_id');
                $table->foreign('commercial_quote_id')
                    ->references('id')
                    ->on(CommercialQuote::TABLE)
                    ->onDelete('cascade');

                $table->unsignedBigInteger('product_id')->nullable();
                $table->foreign('product_id')
                    ->references('id')
                    ->on(Product::TABLE)
                    ->onDelete('cascade');

                $table->string('name')->nullable();
                $table->unsignedInteger('price')->default(0);
                $table->unsignedInteger('qty')->default(0);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(QuoteItem::TABLE);
    }
};

