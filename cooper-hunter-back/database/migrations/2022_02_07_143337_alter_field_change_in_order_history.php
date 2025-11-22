<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'order_status_histories',
            static function (Blueprint $table)
            {
                $table->renameColumn('member_id', 'changer_id');
                $table->renameColumn('member_type', 'changer_type');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'order_status_histories',
            static function (Blueprint $table)
            {
                $table->renameColumn('changer_id', 'member_id');
                $table->renameColumn('changer_type', 'member_type');
            }
        );
    }
};
