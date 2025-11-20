<?php

use App\Models\Reports\Item;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Item::TABLE,
            function (Blueprint $table) {
                $table->string('callid')
                    ->after('report_id')->nullable();
            });
    }

    public function down(): void
    {
        Schema::table(Item::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('callid');
            });
    }
};



