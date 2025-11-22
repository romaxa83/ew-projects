<?php

use App\Models\Admins\Admin;
use App\Models\Commercial\CommercialQuote;
use App\Models\Commercial\QuoteHistory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(QuoteHistory::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('admin_id');
                $table->foreign('admin_id')
                    ->references('id')
                    ->on(Admin::TABLE)
                    ->onDelete('cascade');
                $table->unsignedInteger('quote_id');
                $table->foreign('quote_id')
                    ->references('id')
                    ->on(CommercialQuote::TABLE)
                    ->onDelete('cascade');

                $table->integer('position');
                $table->string('estimate');
                $table->jsonb('data');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(QuoteHistory::TABLE);
    }
};


