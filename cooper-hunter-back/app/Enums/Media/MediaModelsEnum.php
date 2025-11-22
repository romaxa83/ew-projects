<?php

namespace App\Enums\Media;

use App\Models\About\AboutCompany;
use App\Models\About\ForMemberPage;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Products\Product;
use App\Models\Content\OurCases\OurCase;
use App\Models\Content\OurCases\OurCaseCategory;
use App\Models\News\News;
use App\Models\News\PhotoAlbum;
use App\Models\Sliders\Slider;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoPackage;
use Core\Enums\BaseEnum;

class MediaModelsEnum extends BaseEnum
{
    public const CATEGORY = Category::MORPH_NAME;
    public const PRODUCT = Product::MORPH_NAME;
    public const WARRANTY_PACKAGE = WarrantyInfoPackage::MORPH_NAME;
    public const ABOUT_COMPANY = AboutCompany::MORPH_NAME;
    public const FOR_MEMBER_PAGE = ForMemberPage::MORPH_NAME;
    public const OUR_CASE_CATEGORY = OurCaseCategory::MORPH_NAME;
    public const OUR_CASE = OurCase::MORPH_NAME;
    public const NEWS = News::MORPH_NAME;
    public const SLIDER = Slider::MORPH_NAME;
    public const PHOTO_ALBUM = PhotoAlbum::MORPH_NAME;
}
