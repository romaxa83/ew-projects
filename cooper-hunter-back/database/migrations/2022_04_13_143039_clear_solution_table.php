<?php

use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\Models\Catalog\Solutions\Solution;
use App\Models\Catalog\Solutions\SolutionDefaultLineSet;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $indoors = Solution::query()
            ->where('type', SolutionTypeEnum::INDOOR)
            ->with(['children'])
            ->whereDoesntHave('defaultLineSets')
            ->get();

        if ($indoors->isEmpty()) {
            return;
        }
        $defaultLineSets = [];

        foreach ($indoors as $indoor) {
            $defaultLineSets[] = [
                'indoor_id' => $indoor->id,
                'line_set_id' => $indoor->children[0]->id,
                'zone' => SolutionZoneEnum::SINGLE()
            ];
            $defaultLineSets[] = [
                'indoor_id' => $indoor->id,
                'line_set_id' => $indoor->children[0]->id,
                'zone' => SolutionZoneEnum::MULTI()
            ];
        }

        SolutionDefaultLineSet::insert($defaultLineSets);
    }

    public function down(): void
    {
    }
};
