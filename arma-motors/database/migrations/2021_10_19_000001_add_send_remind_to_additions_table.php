<?php

use App\Models\Order\Additions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSendRemindToAdditionsTable extends Migration
{
    public function up(): void
    {
        Schema::table('order_additions', function (Blueprint $table) {
            $table->boolean('is_send_remind')->default(false);
        });

        Additions::query()->update(['is_send_remind' => true]);
    }

    public function down(): void
    {
        Schema::table('order_additions', function (Blueprint $table) {
            $table->dropColumn('is_send_remind');
        });
    }
}
