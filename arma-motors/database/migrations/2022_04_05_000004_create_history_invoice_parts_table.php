<?php

use App\Models\History\Invoice;
use App\Models\History\InvoicePart;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(InvoicePart::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('row_id');
                $table->foreign('row_id')
                    ->references('id')
                    ->on(Invoice::TABLE)
                    ->onDelete('cascade');

                $table->string('name')->nullable();
                $table->string('ref')->nullable();
                $table->string('unit')->nullable();
                $table->float('discounted_price')->nullable();
                $table->float('price')->nullable();
                $table->float('quantity')->nullable();
                $table->float('rate')->nullable();
                $table->float('sum')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(InvoicePart::TABLE);
    }
};

