<?php

namespace App\Enums\Sorting;

use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Labels\Label;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Troubleshoots\Group;
use App\Models\Catalog\Troubleshoots\Troubleshoot;
use App\Models\Catalog\Videos\VideoLink;
use App\Models\Chat\ChatMenu;
use App\Models\Commercial\CommercialQuote;
use App\Models\Commercial\Commissioning\Protocol;
use App\Models\Commercial\Commissioning\Question;
use App\Models\Sliders\Slider;
use App\Models\Support\RequestSubjects\SupportRequestSubject;
use App\Models\Faq\Faq;
use App\Models\Menu\Menu;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use App\Models\Stores\Store;
use App\Models\Stores\StoreCategory;
use Core\Enums\BaseEnum;

/**
 * Class SortingModelsEnum
 * @package App\Enums\Sorting
 *
 * @method static static CATEGORY()
 * @method static static PRODUCT()
 * @method static static LABEL()
 * @method static static FEATURE_VALUE()
 * @method static static FEATURE()
 * @method static static TROUBLESHOOT_GROUP()
 * @method static static TROUBLESHOOT()
 * @method static static VIDEO_GROUP()
 * @method static static VIDEO_LINK()
 * @method static static FAQ()
 * @method static static MANUAL_GROUP()
 * @method static static ORDER_CATEGORY()
 * @method static static ORDER_DELIVERY_TYPE()
 * @method static static SUPPORT_REQUEST_SUBJECT()
 * @method static static MENU()
 * @method static static STORE_CATEGORY()
 * @method static static STORE()
 * @method static static COMMERCIAL_QUOTE()
 * @method static static SLIDER()
 * @method static static PROTOCOL()
 * @method static static QUESTION()
 */
class SortingModelsEnum extends BaseEnum
{
    public const CATEGORY = Category::class;
    public const PRODUCT = Product::class;
    public const LABEL = Label::class;

    public const CHAT_MENU = ChatMenu::class;

    public const FEATURE_VALUE = Value::class;
    public const FEATURE = Feature::class;

    public const TROUBLESHOOT_GROUP = Group::class;
    public const TROUBLESHOOT = Troubleshoot::class;

    public const VIDEO_GROUP = \App\Models\Catalog\Videos\Group::class;
    public const VIDEO_LINK = VideoLink::class;

    public const FAQ = Faq::class;

    public const MANUAL_GROUP = ManualGroup::class;

    public const ORDER_CATEGORY = OrderCategory::class;
    public const ORDER_DELIVERY_TYPE = OrderDeliveryType::class;

    public const SUPPORT_REQUEST_SUBJECT = SupportRequestSubject::class;

    public const MENU = Menu::class;

    public const STORE_CATEGORY = StoreCategory::class;
    public const STORE = Store::class;

    public const COMMERCIAL_QUOTE = CommercialQuote::class;

    public const SLIDER = Slider::class;

    public const PROTOCOL = Protocol::class;
    public const QUESTION = Question::class;
}
