<?php

use App\Models\Calls\Queue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Queue::TABLE,
            function (Blueprint $table) {
                $table->tinyInteger('in_call')->default(0);
            });
    }

    public function down(): void
    {
        Schema::table(Queue::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('in_call');
            });
    }
};

