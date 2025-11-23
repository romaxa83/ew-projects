<?php

use App\Models\Employee;
use App\Models\Partners\Partner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Employee::TABLE, function (Blueprint $table) {
            $table->integer('partner_id')
                ->nullable()
                ->references('id')
                ->on(Partner::TABLE)
                ->onDelete('cascade')
            ;
        });
    }

    public function down(): void
    {
        Schema::table(Employee::TABLE, function (Blueprint $table) {
                $table->dropColumn('partner_id');
            });
    }
};
