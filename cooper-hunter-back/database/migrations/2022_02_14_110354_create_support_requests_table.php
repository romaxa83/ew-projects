<?php

use App\Models\Support\RequestSubjects\SupportRequestSubject;
use App\Models\Technicians\Technician;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'support_requests',
            static function (Blueprint $table)
            {
                $table->id();
                $table->unsignedBigInteger('subject_id')
                    ->nullable();
                $table->unsignedBigInteger('technician_id');
                $table->boolean('is_closed')
                    ->default(false);
                $table->timestamps();

                $table->foreign('subject_id')
                    ->on(SupportRequestSubject::TABLE)
                    ->references('id')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
                $table->foreign('technician_id')
                    ->on(Technician::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('support_requests');
    }
};
