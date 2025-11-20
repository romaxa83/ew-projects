<?php

namespace WezomCms\Core\ExtendPackage;

/**
 * Trait Translatable
 * @package WezomCms\Core\ExtendPackage
 * @property array $translatedAttributes Names of the fields being translated in the "Translation" model.
 */
trait Translatable
{
    use \Astrotomic\Translatable\Translatable;
}
