<?php

namespace App\Services\Catalog\Manuals;

use App\Dto\Catalog\Manuals\ManualGroupDto;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Models\Catalog\Manuals\ManualGroupTranslation;

class ManualGroupService
{
    public function create(ManualGroupDto $dto): ManualGroup
    {
        $manualGroup = new ManualGroup();
        $manualGroup->show_commercial_certified = $dto->getShowCommercialCertified();
        $manualGroup->save();

        $this->saveTranslations($manualGroup, $dto);

        return $manualGroup;
    }

    protected function saveTranslations(ManualGroup $manualGroup, ManualGroupDto $dto): void
    {
        foreach ($dto->getTranslations() as $translation) {
            $t = new ManualGroupTranslation();
            $t->row_id = $manualGroup->id;
            $t->language = $translation->getLanguage();
            $t->title = $translation->getTitle();
            $t->save();
        }
    }

    public function update(ManualGroup $manualGroup, ManualGroupDto $dto): ManualGroup
    {
        $manualGroup->show_commercial_certified = $dto->getShowCommercialCertified();
        $manualGroup->save();

        $this->updateTranslations($manualGroup, $dto);

        return $manualGroup;
    }

    protected function updateTranslations(ManualGroup $manualGroup, ManualGroupDto $dto): void
    {
        foreach ($dto->getTranslations() ?? [] as $translation) {
            $t = $manualGroup->translations->where('language', $translation->getLanguage())->first();
            $t->title = $translation->getTitle();
            $t->save();
        }
    }

    public function delete(ManualGroup $manualGroup): bool
    {
        return $manualGroup->delete();
    }
}
