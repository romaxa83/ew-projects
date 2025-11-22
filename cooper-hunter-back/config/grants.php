<?php

use App\Permissions\Companies\CompanyListPermission;
use Core\Services\Permissions\Filters\Dealer\DealerNotMainCompanyFilter;
use Core\Services\Permissions\Filters\MemberEmailVerifiedPermissionFilter;
use Core\Services\Permissions\Filters\TechnicianNotCertifiedPermissionFilter;
use Core\Services\Permissions\Filters\TechnicianUnverifiedPermissionFilter;

return [
    'permissions_disable' => env('PERMISSION_DISABLE', false),

    'matrix' => [
        App\Models\Admins\Admin::GUARD => [
            'groups' => [
                App\Permissions\Admins\AdminPermissionsGroup::class => [
                    App\Permissions\Admins\AdminListPermission::class,
                    App\Permissions\Admins\AdminCreatePermission::class,
                    App\Permissions\Admins\AdminUpdatePermission::class,
                    App\Permissions\Admins\AdminDeletePermission::class,
                ],

                App\Permissions\About\About\AboutCompanyPermissionsGroup::class => [
                    App\Permissions\About\About\AboutCompanyUpdatePermission::class,
                ],

                App\Permissions\Members\MemberPermissionsGroup::class => [
                    App\Permissions\Members\MemberVerifyEmailPermission::class,
                ],

                App\Permissions\About\ForMemberPages\ForMemberPagePermissionsGroup::class => [
                    App\Permissions\About\ForMemberPages\ForMemberPageUpdatePermission::class,
                ],

                config('chat.permissions.group') => config('chat.permissions.grants'),

                App\Permissions\Chat\Menu\ChatMenuPermissionsGroup::class => [
                    App\Permissions\Chat\Menu\ChatMenuCreatePermission::class,
                    App\Permissions\Chat\Menu\ChatMenuUpdatePermission::class,
                    App\Permissions\Chat\Menu\ChatMenuDeletePermission::class,
                    App\Permissions\Chat\Menu\ChatMenuListPermission::class,
                ],

                App\Permissions\Commercial\CommercialSettings\CommercialSettingsPermissionGroup::class => [
                    App\Permissions\Commercial\CommercialSettings\CommercialSettingsListPermission::class,
                    App\Permissions\Commercial\CommercialSettings\CommercialSettingsUpdatePermission::class,
                ],

                App\Permissions\Commercial\Credentials\CredentialsPermissionGroup::class => [
                    App\Permissions\Commercial\Credentials\CredentialsListPermission::class,
                    App\Permissions\Commercial\Credentials\CredentialsUpdatePermission::class,
                ],
                // Commercial Project
                App\Permissions\Commercial\CommercialProjects\CommercialProjectPermissionGroup::class => [
                    App\Permissions\Commercial\CommercialProjects\CommercialProjectListPermission::class,
                    App\Permissions\Commercial\CommercialProjects\CommercialProjectUpdatePermission::class,
                    App\Permissions\Commercial\CommercialProjects\CommercialStartCommissioningPermission::class,
                    App\Permissions\Commercial\CommercialProjects\CommercialProjectSetWarrantyPermission::class,
                ],
                // Commercial Quote
                App\Permissions\Commercial\CommercialQuotes\CommercialQuotePermissionGroup::class => [
                    App\Permissions\Commercial\CommercialQuotes\CommercialQuoteListPermission::class,
                    App\Permissions\Commercial\CommercialQuotes\CommercialQuoteUpdatePermission::class,
                ],
                // Commissioning Protocol
                App\Permissions\Commercial\Commissionings\Protocol\PermissionGroup::class => [
                    App\Permissions\Commercial\Commissionings\Protocol\CreatePermission::class,
                    App\Permissions\Commercial\Commissionings\Protocol\UpdatePermission::class,
                    App\Permissions\Commercial\Commissionings\Protocol\ListPermission::class,
                ],

                App\Permissions\Catalog\Categories\PermissionsGroup::class => [
                    App\Permissions\Catalog\Categories\ListPermission::class,
                    App\Permissions\Catalog\Categories\CreatePermission::class,
                    App\Permissions\Catalog\Categories\UpdatePermission::class,
                    App\Permissions\Catalog\Categories\DeletePermission::class,
                    App\Permissions\Catalog\Categories\CategoryImageUploadPermission::class,
                ],

                App\Permissions\Orders\Dealer\PermissionsGroup::class => [
                    App\Permissions\Orders\Dealer\ListPermission::class,
                ],

                App\Permissions\Catalog\Certificates\Certificate\PermissionsGroup::class => [
                    App\Permissions\Catalog\Certificates\Certificate\ListPermission::class,
                    App\Permissions\Catalog\Certificates\Certificate\CreatePermission::class,
                    App\Permissions\Catalog\Certificates\Certificate\UpdatePermission::class,
                    App\Permissions\Catalog\Certificates\Certificate\DeletePermission::class,
                ],

                App\Permissions\Catalog\Certificates\Type\PermissionsGroup::class => [
                    App\Permissions\Catalog\Certificates\Type\ListPermission::class,
                    App\Permissions\Catalog\Certificates\Type\CreatePermission::class,
                    App\Permissions\Catalog\Certificates\Type\UpdatePermission::class,
                    App\Permissions\Catalog\Certificates\Type\DeletePermission::class,
                ],

                App\Permissions\Catalog\Features\Features\PermissionsGroup::class => [
                    App\Permissions\Catalog\Features\Features\ListPermission::class,
                    App\Permissions\Catalog\Features\Features\CreatePermission::class,
                    App\Permissions\Catalog\Features\Features\UpdatePermission::class,
                    App\Permissions\Catalog\Features\Features\DeletePermission::class,
                ],

                // Labels
                App\Permissions\Catalog\Labels\PermissionsGroup::class => [
                    App\Permissions\Catalog\Labels\ListPermission::class,
                    App\Permissions\Catalog\Labels\CreatePermission::class,
                    App\Permissions\Catalog\Labels\UpdatePermission::class,
                    App\Permissions\Catalog\Labels\DeletePermission::class,
                ],

                App\Permissions\Catalog\Features\Values\PermissionsGroup::class => [
                    App\Permissions\Catalog\Features\Values\ListPermission::class,
                    App\Permissions\Catalog\Features\Values\CreatePermission::class,
                    App\Permissions\Catalog\Features\Values\UpdatePermission::class,
                    App\Permissions\Catalog\Features\Values\DeletePermission::class,
                ],

                App\Permissions\Catalog\Features\Specifications\PermissionsGroup::class => [
                    App\Permissions\Catalog\Features\Specifications\ListPermission::class,
                    App\Permissions\Catalog\Features\Specifications\CreatePermission::class,
                    App\Permissions\Catalog\Features\Specifications\UpdatePermission::class,
                    App\Permissions\Catalog\Features\Specifications\DeletePermission::class,
                ],

                App\Permissions\Catalog\Manuals\ManualPermissionGroup::class => [
                    App\Permissions\Catalog\Manuals\ManualListPermission::class,
                    App\Permissions\Catalog\Manuals\ManualCreatePermission::class,
                    App\Permissions\Catalog\Manuals\ManualUpdatePermission::class,
                    App\Permissions\Catalog\Manuals\ManualDeletePermission::class,
                    App\Permissions\Catalog\Manuals\ManualMediaUploadPermission::class,
                ],

                App\Permissions\Catalog\Products\PermissionsGroup::class => [
                    App\Permissions\Catalog\Products\ListPermission::class,
                    App\Permissions\Catalog\Products\CreatePermission::class,
                    App\Permissions\Catalog\Products\UpdatePermission::class,
                    App\Permissions\Catalog\Products\DeletePermission::class,
                    App\Permissions\Catalog\Products\ProductImageUploadPermission::class,
                ],

                App\Permissions\Catalog\Troubleshoots\Group\PermissionsGroup::class => [
                    App\Permissions\Catalog\Troubleshoots\Group\ListPermission::class,
                    App\Permissions\Catalog\Troubleshoots\Group\CreatePermission::class,
                    App\Permissions\Catalog\Troubleshoots\Group\UpdatePermission::class,
                    App\Permissions\Catalog\Troubleshoots\Group\DeletePermission::class,
                ],

                App\Permissions\Catalog\Troubleshoots\Troubleshoot\PermissionsGroup::class => [
                    App\Permissions\Catalog\Troubleshoots\Troubleshoot\ListPermission::class,
                    App\Permissions\Catalog\Troubleshoots\Troubleshoot\CreatePermission::class,
                    App\Permissions\Catalog\Troubleshoots\Troubleshoot\UpdatePermission::class,
                    App\Permissions\Catalog\Troubleshoots\Troubleshoot\DeletePermission::class,
                ],

                App\Permissions\Catalog\Videos\Group\PermissionsGroup::class => [
                    App\Permissions\Catalog\Videos\Group\ListPermission::class,
                    App\Permissions\Catalog\Videos\Group\CreatePermission::class,
                    App\Permissions\Catalog\Videos\Group\UpdatePermission::class,
                    App\Permissions\Catalog\Videos\Group\DeletePermission::class,
                ],

                App\Permissions\Catalog\Videos\Link\PermissionsGroup::class => [
                    App\Permissions\Catalog\Videos\Link\ListPermission::class,
                    App\Permissions\Catalog\Videos\Link\CreatePermission::class,
                    App\Permissions\Catalog\Videos\Link\UpdatePermission::class,
                    App\Permissions\Catalog\Videos\Link\DeletePermission::class,
                ],

                App\Permissions\Catalog\Pdf\PdfPermissionGroup::class => [
                    App\Permissions\Catalog\Pdf\DeletePermission::class,
                    App\Permissions\Catalog\Pdf\UploadPermission::class,
                ],

                App\Permissions\Content\OurCaseCategories\OurCaseCategoryPermissionsGroup::class => [
                    App\Permissions\Content\OurCaseCategories\OurCaseCategoryListPermission::class,
                    App\Permissions\Content\OurCaseCategories\OurCaseCategoryCreatePermission::class,
                    App\Permissions\Content\OurCaseCategories\OurCaseCategoryUpdatePermission::class,
                    App\Permissions\Content\OurCaseCategories\OurCaseCategoryDeletePermission::class,
                ],

                App\Permissions\Content\OurCases\OurCasePermissionsGroup::class => [
                    App\Permissions\Content\OurCases\OurCaseListPermission::class,
                    App\Permissions\Content\OurCases\OurCaseCreatePermission::class,
                    App\Permissions\Content\OurCases\OurCaseUpdatePermission::class,
                    App\Permissions\Content\OurCases\OurCaseDeletePermission::class,
                ],

                App\Permissions\Projects\ProjectPermissionGroup::class => [
                    App\Permissions\Projects\ProjectListPermission::class,
                ],

                App\Permissions\Utilities\Media\ManageMediaPermissionGroup::class => [
                    App\Permissions\Utilities\Media\ManageMediaPermission::class,
                ],

                App\Permissions\Utilities\AppVersion\AppVersionPermissionGroup::class => [
                    App\Permissions\Utilities\AppVersion\AppVersionPermission::class,
                ],

                App\Permissions\Faq\FaqPermissionsGroup::class => [
                    App\Permissions\Faq\FaqCreatePermission::class,
                    App\Permissions\Faq\FaqUpdatePermission::class,
                    App\Permissions\Faq\FaqDeletePermission::class,
                ],

                App\Permissions\Faq\Questions\QuestionPermissionsGroup::class => [
                    App\Permissions\Faq\Questions\QuestionAnswerPermission::class,
                    App\Permissions\Faq\Questions\QuestionDeletePermission::class,
                    App\Permissions\Faq\Questions\QuestionListPermission::class,
                ],

                App\Permissions\Localization\TranslatePermissionGroup::class => [
                    App\Permissions\Localization\TranslateListPermission::class,
                    App\Permissions\Localization\TranslateUpdatePermission::class,
                    App\Permissions\Localization\TranslateDeletePermission::class,
                ],

                App\Permissions\News\NewsPermissionGroup::class => [
                    App\Permissions\News\NewsListPermission::class,
                    App\Permissions\News\NewsCreatePermission::class,
                    App\Permissions\News\NewsUpdatePermission::class,
                    App\Permissions\News\NewsDeletePermission::class,
                ],

                App\Permissions\News\Videos\VideoPermissionGroup::class => [
                    App\Permissions\News\Videos\VideoListPermission::class,
                    App\Permissions\News\Videos\VideoCreatePermission::class,
                    App\Permissions\News\Videos\VideoUpdatePermission::class,
                    App\Permissions\News\Videos\VideoDeletePermission::class,
                ],

                App\Permissions\Menu\MenuPermissionsGroup::class => [
                    App\Permissions\Menu\MenuCreatePermission::class,
                    App\Permissions\Menu\MenuUpdatePermission::class,
                    App\Permissions\Menu\MenuDeletePermission::class,
                ],

                App\Permissions\Roles\RolePermissionsGroup::class => [
                    App\Permissions\Roles\RoleListPermission::class,
                    App\Permissions\Roles\RoleCreatePermission::class,
                    App\Permissions\Roles\RoleUpdatePermission::class,
                    App\Permissions\Roles\RoleDeletePermission::class,
                ],

                App\Permissions\Security\IpAccessPermissionsGroup::class => [
                    App\Permissions\Security\IpAccessListPermission::class,
                    App\Permissions\Security\IpAccessCreatePermission::class,
                    App\Permissions\Security\IpAccessUpdatePermission::class,
                    App\Permissions\Security\IpAccessDeletePermission::class,
                ],

                App\Permissions\Sliders\SliderPermissionGroup::class => [
                    App\Permissions\Sliders\SliderListPermission::class,
                    App\Permissions\Sliders\SliderCreatePermission::class,
                    App\Permissions\Sliders\SliderUpdatePermission::class,
                    App\Permissions\Sliders\SliderDeletePermission::class,
                ],

                App\Permissions\Stores\Distributors\DistributorPermissionGroup::class => [
                    App\Permissions\Stores\Distributors\DistributorListPermission::class,
                    App\Permissions\Stores\Distributors\DistributorCreatePermission::class,
                    App\Permissions\Stores\Distributors\DistributorUpdatePermission::class,
                    App\Permissions\Stores\Distributors\DistributorDeletePermission::class,
                ],

                App\Permissions\Stores\StoreCategories\StoreCategoryPermissionGroup::class => [
                    App\Permissions\Stores\StoreCategories\StoreCategoryListPermission::class,
                    App\Permissions\Stores\StoreCategories\StoreCategoryCreatePermission::class,
                    App\Permissions\Stores\StoreCategories\StoreCategoryUpdatePermission::class,
                    App\Permissions\Stores\StoreCategories\StoreCategoryDeletePermission::class,
                ],

                App\Permissions\Stores\Stores\StorePermissionGroup::class => [
                    App\Permissions\Stores\Stores\StoreListPermission::class,
                    App\Permissions\Stores\Stores\StoreCreatePermission::class,
                    App\Permissions\Stores\Stores\StoreUpdatePermission::class,
                    App\Permissions\Stores\Stores\StoreDeletePermission::class,
                ],

                App\Permissions\Supports\SupportPermissionGroup::class => [
                    App\Permissions\Supports\SupportUpdatePermission::class,
                ],

                // Technician ------------------------------------------------------
                App\Permissions\Technicians\TechnicianPermissionsGroup::class => [
                    App\Permissions\Technicians\TechnicianListPermission::class,
                    App\Permissions\Technicians\TechnicianCreatePermission::class,
                    App\Permissions\Technicians\TechnicianUpdatePermission::class,
                    App\Permissions\Technicians\TechnicianDeletePermission::class,
                    App\Permissions\Technicians\TechnicianToggleStatusPermission::class,
                    App\Permissions\Technicians\TechnicianArchiveListPermission::class,
                    App\Permissions\Technicians\TechnicianRestorePermission::class,
                    App\Permissions\Technicians\TechnicianSoftDeletePermission::class,
                ],
                // ------------------------------------------------------------------

                // User -------------------------------------------------------------
                App\Permissions\Users\UserPermissionsGroup::class => [
                    App\Permissions\Users\UserListPermission::class,
                    App\Permissions\Users\UserCreatePermission::class,
                    App\Permissions\Users\UserUpdatePermission::class,
                    App\Permissions\Users\UserDeletePermission::class,
                    App\Permissions\Users\UserArchiveListPermission::class,
                    App\Permissions\Users\UserRestorePermission::class,
                    App\Permissions\Users\UserSoftDeletePermission::class,
                ],
                // ------------------------------------------------------------------

                // Dealer -----------------------------------------------------------
                App\Permissions\Dealers\DealerPermissionsGroup::class => [
                    App\Permissions\Dealers\DealerListPermission::class,
                    App\Permissions\Dealers\DealerCreatePermission::class,
                    App\Permissions\Dealers\DealerUpdatePermission::class,
                    App\Permissions\Dealers\DealerDeletePermission::class,
                    App\Permissions\Dealers\DealerArchiveListPermission::class,
                    App\Permissions\Dealers\DealerRestorePermission::class,
                    App\Permissions\Dealers\DealerSoftDeletePermission::class,
                ],
                // ------------------------------------------------------------------

                // Company ------------------------------------------------------------------
                App\Permissions\Companies\CompanyPermissionsGroup::class => [
                    App\Permissions\Companies\CompanyUpdatePermission::class,
                    App\Permissions\Companies\CompanyListPermission::class,
                    App\Permissions\Companies\CompanySendCodePermission::class,
                    App\Permissions\Companies\CompanySendDataToOnecPermission::class,
                ],
                // ------------------------------------------------------------------
                App\Permissions\Orders\Categories\OrderCategoryPermissionsGroup::class => [
                    App\Permissions\Orders\Categories\OrderCategoryCreatePermission::class,
                    App\Permissions\Orders\Categories\OrderCategoryUpdatePermission::class,
                    App\Permissions\Orders\Categories\OrderCategoryDeletePermission::class,
                    App\Permissions\Orders\Categories\OrderCategoryListPermission::class,
                ],

                App\Permissions\Warranty\WarrantyInfo\WarrantyInfoPermissionsGroup::class => [
                    App\Permissions\Warranty\WarrantyInfo\WarrantyInfoCreatePermission::class,
                    App\Permissions\Warranty\WarrantyInfo\WarrantyInfoDeletePermission::class,
                    App\Permissions\Warranty\WarrantyInfo\WarrantyInfoUpdatePermission::class,
                ],

                App\Permissions\Warranty\WarrantyRegistration\WarrantyRegistrationPermissionsGroup::class => [
                    App\Permissions\Warranty\WarrantyRegistration\WarrantyRegistrationListPermission::class,
                ],

                App\Permissions\Orders\OrderPermissionsGroup::class => [
                    App\Permissions\Orders\OrderListPermission::class,
                    App\Permissions\Orders\OrderUpdatePermission::class,
                    App\Permissions\Orders\OrderDeletePermission::class,
                    App\Permissions\Orders\OrderCreatePermission::class,
                ],

                App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypePermissionsGroup::class => [
                    App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeListPermission::class,
                    App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeCreatePermission::class,
                    App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeUpdatePermission::class,
                    App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeDeletePermission::class,
                ],

                App\Permissions\SupportRequests\Subjects\SupportRequestSubjectPermissionsGroup::class => [
                    App\Permissions\SupportRequests\Subjects\SupportRequestSubjectCreatePermission::class,
                    App\Permissions\SupportRequests\Subjects\SupportRequestSubjectUpdatePermission::class,
                    App\Permissions\SupportRequests\Subjects\SupportRequestSubjectListPermission::class,
                    App\Permissions\SupportRequests\Subjects\SupportRequestSubjectDeletePermission::class,
                ],

                App\Permissions\SupportRequests\SupportRequestPermissionsGroup::class => [
                    App\Permissions\SupportRequests\SupportRequestAnswerPermission::class,
                    App\Permissions\SupportRequests\SupportRequestListPermission::class,
                    App\Permissions\SupportRequests\SupportRequestClosePermission::class,
                ],

                App\Permissions\Alerts\AlertPermissionsGroup::class => [
                    App\Permissions\Alerts\AlertListPermission::class,
                    App\Permissions\Alerts\AlertSetReadPermission::class,
                    App\Permissions\Alerts\AlertSendPermission::class,
                ],

                App\Permissions\About\Pages\PagePermissionsGroup::class => [
                    App\Permissions\About\Pages\PageCreatePermission::class,
                    App\Permissions\About\Pages\PageUpdatePermission::class,
                    App\Permissions\About\Pages\PageDeletePermission::class,
                ],

                App\Permissions\Catalog\Solutions\SolutionPermissionsGroup::class => [
                    App\Permissions\Catalog\Solutions\SolutionCreateUpdatePermission::class,
                    App\Permissions\Catalog\Solutions\SolutionReadPermission::class,
                    App\Permissions\Catalog\Solutions\SolutionDeletePermission::class,
                ],

                App\Permissions\GlobalSettings\GlobalSettingPermissionsGroup::class => [
                    App\Permissions\GlobalSettings\GlobalSettingCreatePermission::class,
                    App\Permissions\GlobalSettings\GlobalSettingUpdatePermission::class,
                    App\Permissions\GlobalSettings\GlobalSettingListPermission::class,
                ],
            ],
        ],

        App\Models\OneC\Moderator::GUARD => [
            'groups' => [
                App\Permissions\Catalog\Categories\PermissionsGroup::class => [
                    App\Permissions\Catalog\Categories\ListPermission::class,
                    App\Permissions\Catalog\Categories\CreatePermission::class,
                    App\Permissions\Catalog\Categories\UpdatePermission::class,
                    App\Permissions\Catalog\Categories\DeletePermission::class,
                    App\Permissions\Catalog\Categories\CategoryImageUploadPermission::class,
                ],

                App\Permissions\Catalog\Certificates\Certificate\PermissionsGroup::class => [
                    App\Permissions\Catalog\Certificates\Certificate\ListPermission::class,
                    App\Permissions\Catalog\Certificates\Certificate\CreatePermission::class,
                    App\Permissions\Catalog\Certificates\Certificate\UpdatePermission::class,
                    App\Permissions\Catalog\Certificates\Certificate\DeletePermission::class,
                ],

                App\Permissions\Catalog\Certificates\Type\PermissionsGroup::class => [
                    App\Permissions\Catalog\Certificates\Type\ListPermission::class,
                    App\Permissions\Catalog\Certificates\Type\CreatePermission::class,
                    App\Permissions\Catalog\Certificates\Type\UpdatePermission::class,
                    App\Permissions\Catalog\Certificates\Type\DeletePermission::class,
                ],

                App\Permissions\Catalog\Features\Features\PermissionsGroup::class => [
                    App\Permissions\Catalog\Features\Features\ListPermission::class,
                    App\Permissions\Catalog\Features\Features\CreatePermission::class,
                    App\Permissions\Catalog\Features\Features\UpdatePermission::class,
                    App\Permissions\Catalog\Features\Features\DeletePermission::class,
                ],

                App\Permissions\Catalog\Features\Values\PermissionsGroup::class => [
                    App\Permissions\Catalog\Features\Values\ListPermission::class,
                    App\Permissions\Catalog\Features\Values\CreatePermission::class,
                    App\Permissions\Catalog\Features\Values\UpdatePermission::class,
                    App\Permissions\Catalog\Features\Values\DeletePermission::class,
                ],

                App\Permissions\Catalog\Products\PermissionsGroup::class => [
                    App\Permissions\Catalog\Products\ListPermission::class,
                    App\Permissions\Catalog\Products\CreatePermission::class,
                    App\Permissions\Catalog\Products\UpdatePermission::class,
                    App\Permissions\Catalog\Products\DeletePermission::class,
                    App\Permissions\Catalog\Products\ProductImageUploadPermission::class,
                ],

                App\Permissions\Technicians\TechnicianPermissionsGroup::class => [
                    App\Permissions\Technicians\TechnicianListPermission::class,
                    App\Permissions\Technicians\TechnicianCreatePermission::class,
                    App\Permissions\Technicians\TechnicianUpdatePermission::class,
                    App\Permissions\Technicians\TechnicianDeletePermission::class,
                ],

                App\Permissions\Users\UserPermissionsGroup::class => [
                    App\Permissions\Users\UserListPermission::class,
                    App\Permissions\Users\UserCreatePermission::class,
                    App\Permissions\Users\UserUpdatePermission::class,
                    App\Permissions\Users\UserDeletePermission::class,
                ],

                App\Permissions\Orders\Categories\OrderCategoryPermissionsGroup::class => [
                    App\Permissions\Orders\Categories\OrderCategoryCreatePermission::class,
                    App\Permissions\Orders\Categories\OrderCategoryUpdatePermission::class,
                    App\Permissions\Orders\Categories\OrderCategoryDeletePermission::class,
                    App\Permissions\Orders\Categories\OrderCategoryListPermission::class,
                ],

                App\Permissions\Orders\OrderPermissionsGroup::class => [
                    App\Permissions\Orders\OrderListPermission::class,
                    App\Permissions\Orders\OrderUpdatePermission::class,
                    App\Permissions\Orders\OrderDeletePermission::class,
                ],

                App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypePermissionsGroup::class => [
                    App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeListPermission::class,
                ],

                App\Permissions\Warranty\WarrantyRegistration\WarrantyRegistrationPermissionsGroup::class => [
                    App\Permissions\Warranty\WarrantyRegistration\WarrantyRegistrationListPermission::class,
                    App\Permissions\Warranty\WarrantyRegistration\WarrantyRegistrationCreatePermission::class,
                    App\Permissions\Warranty\WarrantyRegistration\WarrantyRegistrationDeletePermission::class,
                    App\Permissions\Warranty\WarrantyRegistration\WarrantyRegistrationUpdatePermission::class,
                ],

                App\Permissions\GlobalSettings\GlobalSettingPermissionsGroup::class => [
                    App\Permissions\GlobalSettings\GlobalSettingCreatePermission::class,
                    App\Permissions\GlobalSettings\GlobalSettingUpdatePermission::class,
                    App\Permissions\GlobalSettings\GlobalSettingListPermission::class,
                ],
            ],
        ],

        App\Models\Users\User::GUARD => [
            'groups' => [
                App\Permissions\Roles\RolePermissionsGroup::class => [
                    App\Permissions\Roles\RoleListPermission::class,
                ],

                App\Permissions\Users\UserPermissionsGroup::class => [
                    App\Permissions\Users\UserUpdatePermission::class,
                ],

                App\Permissions\Catalog\Categories\PermissionsGroup::class => [
                    App\Permissions\Catalog\Categories\ListPermission::class,
                ],

                App\Permissions\Catalog\Products\PermissionsGroup::class => [
                    App\Permissions\Catalog\Products\ListPermission::class,
                ],

                App\Permissions\Projects\ProjectPermissionGroup::class => $projectCrudPermissions = [
                    App\Permissions\Projects\ProjectListPermission::class,
                    App\Permissions\Projects\ProjectCreatePermission::class,
                    App\Permissions\Projects\ProjectUpdatePermission::class,
                    App\Permissions\Projects\ProjectDeletePermission::class,
                ],

                App\Permissions\Alerts\AlertPermissionsGroup::class => [
                    App\Permissions\Alerts\AlertListPermission::class,
                    App\Permissions\Alerts\AlertSetReadPermission::class,
                ],

                App\Permissions\Fcm\FcmPermissionsGroup::class => [
                    App\Permissions\Fcm\FcmAddPermission::class,
                ],

                App\Permissions\GlobalSettings\GlobalSettingPermissionsGroup::class => [
                    App\Permissions\GlobalSettings\GlobalSettingListPermission::class,
                ],
            ],
        ],

        App\Models\Technicians\Technician::GUARD => [
            'groups' => [
                App\Permissions\Roles\RolePermissionsGroup::class => [
                    App\Permissions\Roles\RoleListPermission::class,
                ],

                App\Permissions\Technicians\TechnicianPermissionsGroup::class => [
                    App\Permissions\Technicians\TechnicianCreatePermission::class,
                    App\Permissions\Technicians\TechnicianUpdatePermission::class,

                    App\Permissions\Technicians\TechnicianVerifiedMarker::class,
                ],

                Core\Chat\Permissions\ChatPermissionGroup::class => [
                    Core\Chat\Permissions\ChatListPermission::class,
                    Core\Chat\Permissions\ChatMessagingPermission::class,
                ],

                App\Permissions\Chat\Menu\ChatMenuPermissionsGroup::class => [
                    App\Permissions\Chat\Menu\ChatMenuListPermission::class,
                ],

                App\Permissions\Catalog\Categories\PermissionsGroup::class => [
                    App\Permissions\Catalog\Categories\ListPermission::class,
                ],

                App\Permissions\Commercial\CommercialSettings\CommercialSettingsPermissionGroup::class => [
                    App\Permissions\Commercial\CommercialSettings\CommercialSettingsListPermission::class,
                ],

                App\Permissions\Commercial\Credentials\CredentialsPermissionGroup::class => [
                    App\Permissions\Commercial\Credentials\CredentialsListPermission::class,
                    App\Permissions\Commercial\Credentials\CredentialsUpdatePermission::class,
                ],
                // Commercial Project
                App\Permissions\Commercial\CommercialProjects\CommercialProjectPermissionGroup::class => [
                    App\Permissions\Commercial\CommercialProjects\CommercialProjectListPermission::class,
                    App\Permissions\Commercial\CommercialProjects\CommercialProjectCreatePermission::class,
                    App\Permissions\Commercial\CommercialProjects\CommercialProjectUpdatePermission::class,
                    App\Permissions\Commercial\CommercialProjects\CommercialProjectDeletePermission::class,
                ],
                // Commercial Quotes
                App\Permissions\Commercial\CommercialQuotes\CommercialQuotePermissionGroup::class => [
                    App\Permissions\Commercial\CommercialQuotes\CommercialQuoteCreatePermission::class,
                    App\Permissions\Commercial\CommercialQuotes\CommercialQuoteListPermission::class,
                ],
                // Commissioning
                App\Permissions\Commercial\Commissionings\Answer\PermissionGroup::class => [
                    App\Permissions\Commercial\Commissionings\Answer\CreatePermission::class,
                    App\Permissions\Commercial\Commissionings\Answer\UpdatePermission::class,
                    App\Permissions\Commercial\Commissionings\Answer\DeletePermission::class,
                    App\Permissions\Commercial\Commissionings\Answer\ListPermission::class,
                ],

                App\Permissions\Catalog\Products\PermissionsGroup::class => [
                    App\Permissions\Catalog\Products\ListPermission::class,
                ],

                App\Permissions\Catalog\Tickets\PermissionsGroup::class => [
                    App\Permissions\Catalog\Tickets\CreatePermission::class,
                    App\Permissions\Catalog\Tickets\UpdatePermission::class,
                ],

                App\Permissions\Projects\ProjectPermissionGroup::class => $projectCrudPermissions,

                App\Permissions\Orders\Categories\OrderCategoryPermissionsGroup::class => [
                    App\Permissions\Orders\Categories\OrderCategoryListPermission::class,
                ],

                App\Permissions\Orders\OrderPermissionsGroup::class => [
                    App\Permissions\Orders\OrderListPermission::class,
                    App\Permissions\Orders\OrderCreatePermission::class,
                    App\Permissions\Orders\OrderUpdatePermission::class,
                    App\Permissions\Orders\OrderPaidPermission::class,
                ],

                App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypePermissionsGroup::class => [
                    App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeListPermission::class,
                ],

                App\Permissions\SupportRequests\Subjects\SupportRequestSubjectPermissionsGroup::class => [
                    App\Permissions\SupportRequests\Subjects\SupportRequestSubjectListPermission::class,
                ],

                App\Permissions\SupportRequests\SupportRequestPermissionsGroup::class => [
                    App\Permissions\SupportRequests\SupportRequestCreatePermission::class,
                    App\Permissions\SupportRequests\SupportRequestAnswerPermission::class,
                    App\Permissions\SupportRequests\SupportRequestListPermission::class,
                ],

                App\Permissions\Alerts\AlertPermissionsGroup::class => [
                    App\Permissions\Alerts\AlertListPermission::class,
                    App\Permissions\Alerts\AlertSetReadPermission::class,
                ],

                App\Permissions\Fcm\FcmPermissionsGroup::class => [
                    App\Permissions\Fcm\FcmAddPermission::class,
                ],

                App\Permissions\GlobalSettings\GlobalSettingPermissionsGroup::class => [
                    App\Permissions\GlobalSettings\GlobalSettingListPermission::class,
                ],
            ],
        ],

        App\Models\Dealers\Dealer::GUARD => [
            'groups' => [
                App\Permissions\Roles\RolePermissionsGroup::class => [
                    App\Permissions\Roles\RoleListPermission::class,
                ],

                App\Permissions\Dealers\DealerPermissionsGroup::class => [
                    App\Permissions\Dealers\DealerUpdatePermission::class,
                ],

                App\Permissions\Alerts\AlertPermissionsGroup::class => [
                    App\Permissions\Alerts\AlertListPermission::class,
                    App\Permissions\Alerts\AlertSetReadPermission::class,
                ],

                App\Permissions\Fcm\FcmPermissionsGroup::class => [
                    App\Permissions\Fcm\FcmAddPermission::class,
                ],

                App\Permissions\Payments\PaymentPermissionGroup::class => [
                    App\Permissions\Payments\PaymentAddCardPermission::class,
                    App\Permissions\Payments\PaymentDeleteCardPermission::class,
                ],

                App\Permissions\Companies\CompanyPermissionsGroup::class => [
                    App\Permissions\Companies\CompanyUpdatePermission::class,
                    App\Permissions\Companies\CompanyListPermission::class,
                ],

                App\Permissions\Companies\ShippingAddress\CompanyShippingAddressPermissionsGroup::class => [
                    App\Permissions\Companies\ShippingAddress\CompanyShippingAddressListPermission::class,
                    App\Permissions\Companies\ShippingAddress\CompanyShippingAddressCreatePermission::class,
                    App\Permissions\Companies\ShippingAddress\CompanyShippingAddressUpdatePermission::class,
                    App\Permissions\Companies\ShippingAddress\CompanyShippingAddressDeletePermission::class,
                ],

                App\Permissions\Orders\Dealer\PermissionsGroup::class => [
                    App\Permissions\Orders\Dealer\CreatePermission::class,
                    App\Permissions\Orders\Dealer\UpdatePermission::class,
                    App\Permissions\Orders\Dealer\DeletePermission::class,
                    App\Permissions\Orders\Dealer\ListPermission::class,
                ],

                App\Permissions\Catalog\Categories\PermissionsGroup::class => [
                    App\Permissions\Catalog\Categories\ListPermission::class,
                ],
            ],
        ],
    ],

    'filters' => [
        MemberEmailVerifiedPermissionFilter::class => [
            //keep only these permissions

            App\Permissions\Roles\RoleListPermission::class,
            App\Permissions\Projects\ProjectListPermission::class,
            App\Permissions\Catalog\Categories\ListPermission::class,
            App\Permissions\Catalog\Products\ListPermission::class,
            App\Permissions\Users\UserUpdatePermission::class,
            App\Permissions\Technicians\TechnicianUpdatePermission::class,

            App\Permissions\Alerts\AlertListPermission::class,
            App\Permissions\Alerts\AlertSetReadPermission::class,

            App\Permissions\Fcm\FcmAddPermission::class,

            App\Permissions\SupportRequests\Subjects\SupportRequestSubjectListPermission::class,

            App\Permissions\Payments\PaymentAddCardPermission::class,
        ],

        DealerNotMainCompanyFilter::class => [
            //keep only these permissions

            App\Permissions\Companies\ShippingAddress\CompanyShippingAddressCreatePermission::class,
            App\Permissions\Companies\ShippingAddress\CompanyShippingAddressUpdatePermission::class,
            App\Permissions\Companies\ShippingAddress\CompanyShippingAddressDeletePermission::class,
        ],

        TechnicianUnverifiedPermissionFilter::class => [
            //remove these permissions from allowed

            App\Permissions\Projects\ProjectCreatePermission::class,
            App\Permissions\Projects\ProjectUpdatePermission::class,
            App\Permissions\Projects\ProjectDeletePermission::class,

            App\Permissions\Commercial\CommercialProjects\CommercialProjectListPermission::class,
            App\Permissions\Commercial\CommercialProjects\CommercialProjectCreatePermission::class,
            App\Permissions\Commercial\CommercialProjects\CommercialProjectUpdatePermission::class,
            App\Permissions\Commercial\CommercialProjects\CommercialProjectDeletePermission::class,

            App\Permissions\Commercial\CommercialSettings\CommercialSettingsListPermission::class,

            App\Permissions\Commercial\Credentials\CredentialsListPermission::class,
            App\Permissions\Commercial\Credentials\CredentialsUpdatePermission::class,

            App\Permissions\Technicians\TechnicianVerifiedMarker::class,

            Core\Chat\Permissions\ChatListPermission::class,
            Core\Chat\Permissions\ChatMessagingPermission::class,
            // Order --------------------------------------------------
            App\Permissions\Orders\OrderListPermission::class,
            App\Permissions\Orders\OrderCreatePermission::class,
            // --------------------------------------------------------
        ],

        TechnicianNotCertifiedPermissionFilter::class => [
            //remove these permissions from allowed

            App\Permissions\Catalog\Tickets\CreatePermission::class,
            App\Permissions\Catalog\Tickets\UpdatePermission::class,
        ],

        \Core\Services\Permissions\Filters\TechnicianNotCommercialCertifiedPermissionFilter::class => [
            //remove these permissions from allowed

            App\Permissions\Commercial\CommercialProjects\CommercialProjectListPermission::class,
            App\Permissions\Commercial\CommercialProjects\CommercialProjectCreatePermission::class,
            App\Permissions\Commercial\CommercialProjects\CommercialProjectUpdatePermission::class,
            App\Permissions\Commercial\CommercialProjects\CommercialProjectDeletePermission::class,

            App\Permissions\Commercial\CommercialQuotes\CommercialQuoteCreatePermission::class,
            App\Permissions\Commercial\CommercialQuotes\CommercialQuoteListPermission::class,

            App\Permissions\Commercial\Commissionings\Answer\CreatePermission::class,
            App\Permissions\Commercial\Commissionings\Answer\UpdatePermission::class,
            App\Permissions\Commercial\Commissionings\Answer\DeletePermission::class,
            App\Permissions\Commercial\Commissionings\Answer\ListPermission::class,

            App\Permissions\Commercial\Credentials\CredentialsListPermission::class,
            App\Permissions\Commercial\Credentials\CredentialsUpdatePermission::class,

            App\Permissions\Commercial\CommercialSettings\CommercialSettingsListPermission::class,
        ],
    ],

    'filter_enabled' => env('PERMISSION_FILTER_ENABLED', true),

    /*
     * Описывает зависимости между разрешениями
     * Например: Если пользователь может создавать других пользователей, у него должен быть доступ к списку возможных ролей
     */
    'relations' => [
        App\Models\Users\User::GUARD => [],
        App\Models\Technicians\Technician::GUARD => [],
//        App\Models\Dealers\Dealer::GUARD => [],
    ],
];
