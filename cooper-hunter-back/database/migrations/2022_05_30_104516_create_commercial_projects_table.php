<?php

use App\Enums\Commercial\CommercialProjectStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'commercial_projects',
            static function (Blueprint $table) {
                $table->id();

                $table->foreignId('parent_id')
                    ->nullable()
                    ->constrained('commercial_projects')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->numericMorphs('member', 'commercial_project_member');

                $table->enum('status', CommercialProjectStatusEnum::getValues())
                    ->default(CommercialProjectStatusEnum::CREATED);

                $table->string('code')
                    ->unique()
                    ->nullable();

                $table->string('name');

                $table->string('address_line_1');
                $table->string('address_line_2')->nullable();
                $table->string('city');
                $table->string('state');
                $table->string('zip');

                $table->string('address_hash')->index();

                $table->string('first_name');
                $table->string('last_name');
                $table->string('phone');
                $table->string('email');

                $table->string('company_name');
                $table->string('company_address');

                $table->text('description')->nullable();

                $table->timestamp('estimate_start_date');
                $table->timestamp('estimate_end_date');
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_projects');
    }
};
