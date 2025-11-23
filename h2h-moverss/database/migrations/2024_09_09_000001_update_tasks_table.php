<?php

use App\Models\Division;
use App\Models\Tasks\Status;
use App\Models\Tasks\Task;
use App\Models\Tasks\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Task::TABLE, function (Blueprint $table) {
            $table->integer('type_id')
                ->references('id')
                ->on(Type::TABLE)
                ->onDelete('cascade')
                ->change()
            ;
            $table->integer('status_id')
                ->references('id')
                ->on(Status::TABLE)
                ->onDelete('cascade')
                ->change()
            ;
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
