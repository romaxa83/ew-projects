<?php

use App\Enums\Commercial\Commissioning\ProtocolStatus;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Models\Commercial\Commissioning\Protocol;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(ProjectProtocol::TABLE,
            static function (Blueprint $table)
            {
                $table->bigIncrements('id');

                $table->unsignedBigInteger('project_id');
                $table->foreign('project_id')
                    ->on(CommercialProject::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedInteger('protocol_id');
                $table->foreign('protocol_id')
                    ->on(Protocol::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('status', 10)->default(ProtocolStatus::DRAFT);
                $table->timestamp('closed_at')->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(ProjectProtocol::TABLE);
    }
};




