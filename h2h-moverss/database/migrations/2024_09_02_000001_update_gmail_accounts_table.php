<?php

use App\Models\Division;
use App\Models\Mailbox\Gmail\Account;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Account::TABLE, function (Blueprint $table) {
            $table->integer('division_id')
                ->references('id')
                ->on(Division::TABLE)
                ->onDelete('cascade')
                ->change()
            ;
        });
    }

    public function down(): void
    {}
};
