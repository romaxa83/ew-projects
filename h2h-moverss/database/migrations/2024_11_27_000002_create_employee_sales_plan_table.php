<?php

use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create(Employee\SalesPlan::TABLE, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('employee_id')
                ->references('id')
                ->on(Employee::TABLE)
                ->onDelete('cascade');
            $table->integer("local")->nullable();
            $table->integer("intrestate")->nullable();
            $table->date("date_at");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Employee\SalesPlan::TABLE);
    }
};
