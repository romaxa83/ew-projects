<?php

use App\Models\Agreement\Agreement;
use App\Models\Order\Additions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Additions::TABLE_NAME,
            static function (Blueprint $table) {
                $table->unsignedBigInteger('agreement_id')
                    ->nullable();
                $table->foreign('agreement_id')
                    ->references('id')
                    ->on(Agreement::TABLE)
                    ->onDelete('cascade');
            }
        );
    }

    public function down(): void
    {
        Schema::table(Additions::TABLE_NAME,
            static function (Blueprint $table) {
                $table->dropForeign('order_additions_agreement_id_foreign');
                $table->dropColumn(['agreement_id']);
            }
        );
    }
};
