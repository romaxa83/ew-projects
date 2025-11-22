<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'vehicles',
            static function (Blueprint $table) {
                $table->string('state_number')
                    ->after('id')
                    ->unique();
                $table->string('vin', 17)
                    ->unique('vin')
                    ->nullable()
                    ->change();
                $table->string('form')
                    ->after('vin');
                $table->foreignId('class_id')
                    ->after('form')
                    ->constrained('vehicle_classes')
                    ->references('id');
                $table->foreignId('type_id')
                    ->after('class_id')
                    ->constrained('vehicle_types')
                    ->references('id');
                $table->foreignId('make_id')
                    ->after('type_id')
                    ->constrained('vehicle_makes')
                    ->references('id');
                $table->foreignId('model_id')
                    ->after('make_id')
                    ->constrained('vehicle_models')
                    ->references('id');
                $table->foreignId('schema_id')
                    ->after('client_id')
                    ->constrained('schema_vehicles')
                    ->references('id');
                $table->unsignedInteger('odo')
                    ->after('schema_id')
                    ->nullable();
                $table->foreignId('trailer_id')
                    ->after('odo')
                    ->nullable()
                    ->constrained('vehicles')
                    ->references('id')
                    ->nullOnDelete();
                $table->boolean('active')
                    ->default(true)
                    ->after('trailer_id');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'vehicles',
            static function (Blueprint $table) {
                $table->dropUnique('vin');
                $table->string('vin')
                    ->unique()
                    ->change();
                $table->dropColumn('state_number');
                $table->dropColumn('form');
                $table->dropConstrainedForeignId('class_id');
                $table->dropConstrainedForeignId('type_id');
                $table->dropConstrainedForeignId('make_id');
                $table->dropConstrainedForeignId('model_id');
                $table->dropConstrainedForeignId('schema_id');
                $table->dropColumn('odo');
                $table->dropConstrainedForeignId('trailer_id');
                $table->dropColumn('active');
            }
        );
    }
};
