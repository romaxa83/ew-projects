<?php

use App\Models\History\CarItem;
use App\Models\History\Invoice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Invoice::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('row_id');
                $table->foreign('row_id')
                    ->references('id')
                    ->on(CarItem::TABLE)
                    ->onDelete('cascade');

                $table->string('aa_uuid')->nullable();
                $table->string('address')->nullable();
                $table->float('amount_including_vat')->nullable();
                $table->float('amount_vat')->nullable();
                $table->float('amount_without_vat')->nullable();
                $table->string('author')->nullable();
                $table->string('contact_information')->nullable();
                $table->timestamp('date')->nullable();
                $table->float('discount')->nullable();
                $table->string('etc')->nullable();
                $table->string('number')->nullable();
                $table->string('organization')->nullable();
                $table->string('phone')->nullable();
                $table->string('shopper')->nullable();
                $table->string('tax_code')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Invoice::TABLE);
    }
};
