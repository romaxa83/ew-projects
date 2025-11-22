<?php

use App\Models\Catalog\Tickets\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Ticket::TABLE,
            static function (Blueprint $table) {
                $table->integer('case_id')->nullable();

            }
        );
    }

    public function down(): void
    {
        Schema::table(Ticket::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('case_id');
            }
        );
    }
};
