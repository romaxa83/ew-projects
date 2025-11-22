<?php

use App\Models\Lists\BonusType;
use App\Models\Lists\ExpenseType;
use Illuminate\Database\Seeder;

class ListsSeeder extends Seeder
{
    public function run(): void
    {
        try {
            BonusType::query()->insertOrIgnore(
                collect(
                    BonusType::getDefaultTypesList()
                )
                    ->map(
                        function ($item) {
                            return [
                                'id' => $item['id'],
                                'title' => $item['title'],
                                'carrier_id' => 0,
                            ];
                        }
                    )
                    ->all()
            );
            DB::statement("SELECT setval('" . BonusType::TABLE_NAME . "_id_seq', COALESCE((SELECT MAX(id)+1 FROM " . BonusType::TABLE_NAME . "), 1), false);");

            ExpenseType::query()->insertOrIgnore(
                collect(
                    ExpenseType::getDefaultTypesList()
                )
                    ->map(
                        function ($item) {
                            return [
                                'id' => $item['id'],
                                'title' => $item['title'],
                                'carrier_id' => 0,
                            ];
                        }
                    )
                    ->all()
            );
            DB::statement("SELECT setval('" . ExpenseType::TABLE_NAME . "_id_seq', COALESCE((SELECT MAX(id)+1 FROM " . ExpenseType::TABLE_NAME . "), 1), false);");
        } catch (Exception $exception) {
            logger($exception);
        }
    }
}
