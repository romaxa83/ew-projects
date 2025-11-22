<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'order_payments',
            static function (Blueprint $table)
            {
                $table->unsignedBigInteger('refund_at')
                    ->nullable()
                    ->after('paid_at');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'order_payments',
            static function (Blueprint $table)
            {
                $table->dropColumn('refund_at');
            }
        );
    }
};
