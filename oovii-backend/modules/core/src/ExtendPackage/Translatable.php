<?php

namespace WezomCms\Core\ExtendPackage;

/**
 * Trait Translatable
 * @package WezomCms\Core\ExtendPackage
 * @property array $translatedAttributes Names of the fields being translated in the "Translation" model.
 */
trait Translatable
{
    use \Astrotomic\Translatable\Translatable {
        saveTranslations as private traitSaveTranslations;
    }

    protected function saveTranslations(): bool
    {
        if ($this->relationLoaded('translation')) {
            $translation = $this->translation;

            if ($translation !== null && $this->isTranslationDirty($translation)) {
                if (!empty($connectionName = $this->getConnectionName())) {
                    $translation->setConnection($connectionName);
                }

                $translation->setAttribute($this->getTranslationRelationKey(), $this->getKey());
                $translation->save();
            }
        }

        return $this->traitSaveTranslations();
    }

    /**
     * Override method to get rid of redundant functionality
     */
    protected function getLocaleKey(): string
    {
        return config('translatable.locale_key', 'locale');
    }
}
