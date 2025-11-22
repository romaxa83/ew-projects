<?php

declare(strict_types=1);

use App\GraphQL\Mutations\Common\Localization\SetLanguageMutation;
use App\GraphQL\Queries\BackOffice\Localization\TranslatesFilterableQuery;
use App\GraphQL\Queries\Common\Localization\LanguagesQuery;
use App\GraphQL\Queries\Common\Localization\TranslatesSimpleHashQuery;
use App\GraphQL\Queries\Common\Localization\TranslatesSimpleQuery;

// The schemas for query and/or mutation. It expects an array of schemas to provide
// both the 'query' fields and the 'mutation' fields.
//
// You can also provide a middleware that will only apply to the given schema
//
// Example:
//
//  'schema' => 'default',
//
//  'schemas' => [
//      'default' => [
//          'query' => [
//              'users' => 'App\GraphQL\Query\UsersQuery'
//          ],
//          'mutation' => [
//
//          ]
//      ],
//      'user' => [
//          'query' => [
//              'profile' => 'App\GraphQL\Query\ProfileQuery'
//          ],
//          'mutation' => [
//
//          ],
//          'middleware' => ['auth'],
//      ],
//      'user/me' => [
//          'query' => [
//              'profile' => 'App\GraphQL\Query\MyProfileQuery'
//          ],
//          'mutation' => [
//
//          ],
//          'middleware' => ['auth'],
//      ],
//  ]

return [
    'default' => [
        'query' => [
            App\GraphQL\Queries\FrontOffice\About\AboutCompanyQuery::class,
            App\GraphQL\Queries\FrontOffice\About\ForMemberPageQuery::class,
            App\GraphQL\Queries\FrontOffice\About\SpecificationsQuery::class,

            LanguagesQuery::class,
            TranslatesSimpleQuery::class,
            TranslatesSimpleHashQuery::class,
            App\GraphQL\Queries\FrontOffice\Permissions\UserRolesQuery::class,
            App\GraphQL\Queries\FrontOffice\Users\UserProfileQuery::class,

            App\GraphQL\Queries\FrontOffice\Members\MemberProfileUnionQuery::class,

            // Locations ------------------------------------------------------------------
            App\GraphQL\Queries\FrontOffice\Locations\States::class,
            App\GraphQL\Queries\FrontOffice\Locations\Countries::class,
            // ----------------------------------------------------------------------------

            // Technician -----------------------------------------------------------------
            App\GraphQL\Queries\FrontOffice\Technicians\TechnicianProfileQuery::class,
            App\GraphQL\Queries\FrontOffice\Permissions\TechnicianRolesQuery::class,
            // ----------------------------------------------------------------------------

            // Dealer ---------------------------------------------------------------------
            App\GraphQL\Queries\FrontOffice\Dealers\DealerProfileQuery::class,
            // ----------------------------------------------------------------------------

            // Dealer Orders --------------------------------------------------------------
            App\GraphQL\Queries\FrontOffice\Orders\Dealer\OrdersQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\Dealer\OrderQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\Dealer\ProductsQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\Dealer\ProductsExcelQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\Dealer\ChangeProductPriceQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\Dealer\EstimateQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\Dealer\OrderSerialNumberExcelQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\Dealer\ReportQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\Dealer\ReportExcelQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\Dealer\PaymentDescQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\Dealer\CheckPoQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\Dealer\PackingSlipPdfQuery::class,
            // ----------------------------------------------------------------------------

            // Companies ------------------------------------------------------------------
            App\GraphQL\Queries\FrontOffice\Companies\CompanyListQuery::class,
            App\GraphQL\Queries\FrontOffice\Companies\CompaniesQuery::class,
            App\GraphQL\Queries\FrontOffice\Companies\ShippingAddressListQuery::class,
            // ----------------------------------------------------------------------------

            App\GraphQL\Queries\FrontOffice\Tickets\TicketsQuery::class,

            App\GraphQL\Queries\FrontOffice\Chat\Conversations\ConversationQuery::class,
            App\GraphQL\Queries\FrontOffice\Chat\Participants\ParticipantQuery::class,
            App\GraphQL\Queries\FrontOffice\Chat\Messages\MessageQuery::class,
            App\GraphQL\Queries\FrontOffice\Chat\Menu\ChatMenusQuery::class,

            App\GraphQL\Queries\FrontOffice\Catalog\Categories\CategoriesQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Categories\CategoryQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Categories\CategoriesAsTreeQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Categories\CategoriesForSelectQuery::class,

            App\GraphQL\Queries\FrontOffice\Catalog\Products\ProductsQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Products\ProductQuery::class,

            App\GraphQL\Queries\FrontOffice\Catalog\Filters\FilterQuery::class,

            App\GraphQL\Queries\FrontOffice\Catalog\Manuals\ManualCategoriesQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Manuals\ManualCategoryQuery::class,

            App\GraphQL\Queries\FrontOffice\Catalog\Search\CatalogSearchQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Search\ProductSearchQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Search\SearchUnionQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Search\UnitSearchQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Search\UnitsSearchQuery::class,

            App\GraphQL\Queries\FrontOffice\Catalog\Tickets\TicketQuery::class,

            App\GraphQL\Queries\FrontOffice\Catalog\Solutions\FindSolutionQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Solutions\FindSolutionChangeIndoorQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Solutions\SolutionSeriesListQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Solutions\SolutionBtuListQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Solutions\SolutionVoltageListQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Solutions\SolutionIndoorSettingQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Solutions\FindSolutionDownloadPdfQuery::class,
            App\GraphQL\Queries\FrontOffice\Catalog\Solutions\FindSolutionSendPdfQuery::class,

            // Commercial Project ---------------------------------------------------------
            App\GraphQL\Queries\FrontOffice\Commercial\CommercialProjectsForCredentialsQuery::class,
            App\GraphQL\Queries\FrontOffice\Commercial\CommercialProjectsForQuotesQuery::class,
            App\GraphQL\Queries\FrontOffice\Commercial\CommercialProjectsQuery::class,
            App\GraphQL\Queries\FrontOffice\Commercial\CommercialSettingsQuery::class,
            App\GraphQL\Queries\FrontOffice\Commercial\RDPAccountQuery::class,
            App\GraphQL\Queries\FrontOffice\Commercial\CheckRDPCredentialsQuery::class,
            App\GraphQL\Queries\FrontOffice\Commercial\CommercialProjectUnitsQuery::class,
            App\GraphQL\Queries\FrontOffice\Commercial\CommercialProjectUnitsExcelQuery::class,
            App\GraphQL\Queries\FrontOffice\Commercial\CommercialProjectProtocolQuery::class,
            // ----------------------------------------------------------------------------

            // Commercial Quote -----------------------------------------------------------
            App\GraphQL\Queries\FrontOffice\Commercial\CommercialQuotesQuery::class,
            // ----------------------------------------------------------------------------

            App\GraphQL\Queries\FrontOffice\Content\OurCases\OurCasesQuery::class,

            App\GraphQL\Queries\FrontOffice\Counters\ProjectCounterQuery::class,
            App\GraphQL\Queries\FrontOffice\Counters\FavouriteCounterQuery::class,

            App\GraphQL\Queries\FrontOffice\Projects\UserProjectsQuery::class,
            App\GraphQL\Queries\FrontOffice\Projects\TechnicianProjectsQuery::class,

            App\GraphQL\Queries\FrontOffice\Projects\Systems\UserProjectSystemQuery::class,
            App\GraphQL\Queries\FrontOffice\Projects\Systems\TechnicianProjectSystemQuery::class,
            App\GraphQL\Queries\FrontOffice\Projects\Systems\Units\UserProjectSystemUnitsQuery::class,
            App\GraphQL\Queries\FrontOffice\Projects\Systems\Units\TechnicianProjectSystemUnitsQuery::class,

            App\GraphQL\Queries\FrontOffice\Faq\FaqQuery::class,

            App\GraphQL\Queries\FrontOffice\Sliders\SliderQuery::class,

            App\GraphQL\Queries\FrontOffice\Stores\OnlineStoresQuery::class,
            App\GraphQL\Queries\FrontOffice\Stores\DistributorQuery::class,

            App\GraphQL\Queries\FrontOffice\News\NewsQuery::class,
            App\GraphQL\Queries\FrontOffice\News\PhotoAlbumQuery::class,
            App\GraphQL\Queries\FrontOffice\News\TagsQuery::class,
            App\GraphQL\Queries\FrontOffice\News\VideosQuery::class,

            App\GraphQL\Queries\FrontOffice\Supports\SupportQuery::class,

            App\GraphQL\Queries\FrontOffice\Menu\MenuQuery::class,

            App\GraphQL\Queries\FrontOffice\Favourites\FavouriteProductsQuery::class,

            App\GraphQL\Queries\FrontOffice\Orders\Categories\OrderCategoriesQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\DeliveryTypes\OrderDeliveryTypesQuery::class,

            App\GraphQL\Queries\FrontOffice\Orders\OrderQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\OrderAvailableProjectQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\OrderProjectsQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\OrderTotalQuery::class,
            App\GraphQL\Queries\FrontOffice\Orders\OrderPaymentQuery::class,

            App\GraphQL\Queries\FrontOffice\Warranty\WarrantyInfo\WarrantyInfoQuery::class,

            App\GraphQL\Queries\FrontOffice\Warranty\VerifyWarrantyStatusQuery::class,

            App\GraphQL\Queries\FrontOffice\HomePage\HomeCategoriesQuery::class,

            App\GraphQL\Queries\FrontOffice\Content\OurCases\OurCaseCategoriesQuery::class,

            App\GraphQL\Queries\FrontOffice\SupportRequests\SupportRequestSubjectsQuery::class,

            App\GraphQL\Queries\FrontOffice\SupportRequests\SupportRequestsQuery::class,
            App\GraphQL\Queries\FrontOffice\SupportRequests\SupportRequestCounterQuery::class,

            App\GraphQL\Queries\FrontOffice\Sitemaps\SitemapCategoriesQuery::class,

            App\GraphQL\Queries\FrontOffice\Alerts\AlertQuery::class,
            App\GraphQL\Queries\FrontOffice\Alerts\AlertCounterQuery::class,

            App\GraphQL\Queries\FrontOffice\About\PageQuery::class,

            App\GraphQL\Queries\FrontOffice\Utilities\ZipCodeByIpAddressQuery::class,

            App\GraphQL\Queries\FrontOffice\GlobalSettings\GlobalSettingQuery::class,
        ],
        'mutation' => [
            App\GraphQL\Mutations\Common\Testing\TranslatedErrorMutation::class,
            App\GraphQL\Mutations\Common\Testing\InternalErrorMutation::class,

            SetLanguageMutation::class,

            App\GraphQL\Mutations\FrontOffice\Auth\MemberRequestSmsTokenMutation::class,
            App\GraphQL\Mutations\FrontOffice\Auth\MemberConfirmSmsTokenMutation::class,
            // Commercial Project
            App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects\CommercialProjectCreateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects\CommercialProjectUpdateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects\CommercialProjectDeleteMutation::class,

            App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects\CommercialProjectAdditionCreateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects\CommercialProjectAdditionUpdateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects\CommercialProjectAdditionDeleteMutation::class,
            // Commercial Quote
            App\GraphQL\Mutations\FrontOffice\Commercial\CommercialQuotes\CommercialQuoteCreateMutation::class,
            // Commercial Request
            App\GraphQL\Mutations\FrontOffice\Commercial\Credentials\CommercialCredentialsRequestMutation::class,

            // Commissioning --------------------------------------------------------
            App\GraphQL\Mutations\FrontOffice\Commercial\Commissioning\AnswerImageUploadMutation::class,
            App\GraphQL\Mutations\FrontOffice\Commercial\Commissioning\AnswerImageDeleteMutation::class,

            App\GraphQL\Mutations\FrontOffice\Commercial\Commissioning\AnswersCreateOrUpdateMutation::class,
            // ----------------------------------------------------------------------

            App\GraphQL\Mutations\FrontOffice\Users\UserTokenRefreshMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserRegisterMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserResendEmailVerificationMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserPhoneVerificationMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserChangePasswordMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserUpdateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserLogoutMutation::class,
            App\GraphQL\Mutations\FrontOffice\Users\UserDeleteProfileMutation::class,

            App\GraphQL\Mutations\FrontOffice\Projects\MemberProjectCreateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Projects\MemberProjectUpdateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Projects\MemberProjectSystemUpdateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Projects\MemberProjectDeleteMutation::class,
            App\GraphQL\Mutations\FrontOffice\Projects\MemberProjectSystemDeleteMutation::class,
            App\GraphQL\Mutations\FrontOffice\Projects\MemberProjectSystemUnitDeleteMutation::class,

            App\GraphQL\Mutations\FrontOffice\Projects\CheckUnitsSchemaMutation::class,

            App\GraphQL\Mutations\FrontOffice\Members\MemberCheckPasswordMutation::class,
            App\GraphQL\Mutations\FrontOffice\Members\MemberLoginMutation::class,
            App\GraphQL\Mutations\FrontOffice\Members\MemberForgotPasswordMutation::class,
            App\GraphQL\Mutations\FrontOffice\Members\MemberResetPasswordMutation::class,
            App\GraphQL\Mutations\FrontOffice\Members\MemberEmailConfirmationMutation::class,

            App\GraphQL\Mutations\FrontOffice\Tickets\TicketCreateMutation::class,

            App\GraphQL\Mutations\FrontOffice\Chat\SendMessageMutation::class,
            App\GraphQL\Mutations\FrontOffice\Chat\ReadMessagesMutation::class,
            App\GraphQL\Mutations\FrontOffice\Chat\ReadAllMessagesMutation::class,
            App\GraphQL\Mutations\FrontOffice\Chat\ProhibitMessagingMutation::class,

            App\GraphQL\Mutations\FrontOffice\Fcm\FcmTokenAddMutation::class,

            // Technicians ------------------------------------------------------------------
            App\GraphQL\Mutations\FrontOffice\Technicians\Avatars\AvatarUploadMutation::class,
            App\GraphQL\Mutations\FrontOffice\Technicians\Avatars\AvatarDeleteMutation::class,

            App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianRegisterMutation::class,
            App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianChangePasswordMutation::class,
            App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianTokenRefreshMutation::class,
            App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianResendEmailVerificationMutation::class,
            App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianPhoneVerificationMutation::class,
            App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianUpdateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianLogoutMutation::class,
            App\GraphQL\Mutations\FrontOffice\Technicians\TechnicianDeleteProfileMutation::class,
            // -------------------------------------------------------------------------------

            // Dealers -----------------------------------------------------------------------
            App\GraphQL\Mutations\FrontOffice\Dealers\DealerRegisterMutation::class,
            App\GraphQL\Mutations\FrontOffice\Dealers\DealerChangePasswordMutation::class,
            App\GraphQL\Mutations\FrontOffice\Dealers\DealerLogoutMutation::class,
            App\GraphQL\Mutations\FrontOffice\Dealers\DealerTokenRefreshMutation::class,
            // -------------------------------------------------------------------------------

            // Dealer Orders -----------------------------------------------------------------
            App\GraphQL\Mutations\FrontOffice\Orders\Dealer\CreateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\Dealer\UpdateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\Dealer\DeleteMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\Dealer\CheckoutMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\Dealer\CopyMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\Dealer\UploadProductsExcelMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\Dealer\MediaUploadMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\Dealer\MediaDeleteMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\Dealer\ChangeProductPriceMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\Dealer\UpdatePackingSlipMutation::class,

            App\GraphQL\Mutations\FrontOffice\Orders\Dealer\Item\AddItemMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\Dealer\Item\DeleteItemMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\Dealer\Item\UpdateItemMutation::class,
            // -------------------------------------------------------------------------------

            // Companies ---------------------------------------------------------------------
            App\GraphQL\Mutations\FrontOffice\Companies\CreateApplicationMutation::class,
            App\GraphQL\Mutations\FrontOffice\Companies\MediaUploadMutation::class,
            App\GraphQL\Mutations\FrontOffice\Companies\MediaDeleteMutation::class,

            App\GraphQL\Mutations\FrontOffice\Companies\ShippingAddress\AddShippingAddressMutation::class,
            App\GraphQL\Mutations\FrontOffice\Companies\ShippingAddress\UpdateShippingAddressMutation::class,
            App\GraphQL\Mutations\FrontOffice\Companies\ShippingAddress\DeleteShippingAddressMutation::class,
            // -------------------------------------------------------------------------------

            // Payments ----------------------------------------------------------------------
            App\GraphQL\Mutations\FrontOffice\Payments\MemberAddCardMutation::class,
            App\GraphQL\Mutations\FrontOffice\Payments\MemberDeleteCardMutation::class,
            App\GraphQL\Mutations\FrontOffice\Payments\MemberToggleDefaultCardMutation::class,
            // -------------------------------------------------------------------------------

            App\GraphQL\Mutations\FrontOffice\Favourites\AddToFavouriteMutation::class,
            App\GraphQL\Mutations\FrontOffice\Favourites\RemoveAllFromFavouriteMutation::class,
            App\GraphQL\Mutations\FrontOffice\Favourites\RemoveFromFavouriteMutation::class,

            App\GraphQL\Mutations\FrontOffice\Faq\Questions\AskAQuestionMutation::class,

            App\GraphQL\Mutations\FrontOffice\Warranty\SystemWarrantyRegistrationMutation::class,
            App\GraphQL\Mutations\FrontOffice\Warranty\SystemCanRegisterWarrantyMutation::class,
            App\GraphQL\Mutations\FrontOffice\Warranty\ProductWarrantyRegistrationMutation::class,

            App\GraphQL\Mutations\FrontOffice\Orders\OrderCreateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\OrderCreateByTicketMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\OrderConnectProjectMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\OrderDisconnectProjectMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\OrderCanceledMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\OrderShippingUpdateMutation::class,
            App\GraphQL\Mutations\FrontOffice\Orders\OrderPaymentCheckoutCreateMutation::class,

            App\GraphQL\Mutations\FrontOffice\SupportRequests\SupportRequestCreateMutation::class,
            App\GraphQL\Mutations\FrontOffice\SupportRequests\SupportRequestAnswerMutation::class,
            App\GraphQL\Mutations\FrontOffice\SupportRequests\SupportRequestSetIsReadMutation::class,

            App\GraphQL\Mutations\FrontOffice\Alerts\AlertSetReadMutation::class,

            App\GraphQL\Mutations\FrontOffice\Utilities\AppVersionStatusMutation::class,
        ],
        'subscription' => [
            App\GraphQL\Subscriptions\FrontOffice\Alerts\AlertSubscription::class,
            App\GraphQL\Subscriptions\FrontOffice\Chat\ConversationUpdatedSubscription::class,
            App\GraphQL\Subscriptions\FrontOffice\Orders\OrderSubscription::class,
            App\GraphQL\Subscriptions\FrontOffice\SupportRequests\SupportRequestSubscription::class,
            App\GraphQL\Subscriptions\FrontOffice\Members\MemberSubscription::class,
            App\GraphQL\Subscriptions\FrontOffice\Favourites\FavouriteSubscription::class,
        ],
        'middleware' => [],
        'method' => ['post'],
    ],
    'BackOffice' => [
        'query' => [
            App\GraphQL\Queries\BackOffice\About\AboutCompanyQuery::class,
            App\GraphQL\Queries\BackOffice\About\ForMemberPageQuery::class,
            App\GraphQL\Queries\BackOffice\About\SpecificationsQuery::class,

            App\GraphQL\Queries\BackOffice\Dashboard\DashboardWidgetsQuery::class,

            App\GraphQL\Queries\BackOffice\Commercial\CommercialProjectsQuery::class,

            LanguagesQuery::class,
            TranslatesSimpleQuery::class,
            TranslatesSimpleHashQuery::class,
            TranslatesFilterableQuery::class,
            App\GraphQL\Queries\BackOffice\Permissions\AdminRolesQuery::class,
            App\GraphQL\Queries\BackOffice\Permissions\UserRolesQueryForAdmin::class,
            App\GraphQL\Queries\BackOffice\Permissions\TechnicianRolesQueryForAdmin::class,
            App\GraphQL\Queries\BackOffice\Users\UsersQueryForAdminPanel::class,
            App\GraphQL\Queries\BackOffice\Users\UsersArchiveQueryForAdminPanel::class,
            App\GraphQL\Queries\BackOffice\Admins\AdminsQuery::class,
            App\GraphQL\Queries\BackOffice\Admins\AdminProfileQuery::class,

            App\GraphQL\Queries\BackOffice\Chat\ChatTabCountersQuery::class,
            App\GraphQL\Queries\BackOffice\Chat\Conversations\ConversationQuery::class,
            App\GraphQL\Queries\BackOffice\Chat\Participants\ParticipantQuery::class,
            App\GraphQL\Queries\BackOffice\Chat\Messages\MessageQuery::class,

            App\GraphQL\Queries\BackOffice\Chat\Menu\ChatMenusQuery::class,
            // Commercial -----------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Commercial\CommercialSettingsQuery::class,
            App\GraphQL\Queries\BackOffice\Commercial\CredentialsRequestsQuery::class,
            App\GraphQL\Queries\BackOffice\Commercial\RDPAccountsQuery::class,

            App\GraphQL\Queries\BackOffice\Commercial\CredentialsRequestCounterQuery::class,

            App\GraphQL\Queries\BackOffice\Commercial\TaxQuery::class,
            App\GraphQL\Queries\BackOffice\Commercial\CommercialProjectUnitsQuery::class,
            // ----------------------------------------------------------------------------

            // Commercial Quote -----------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Commercial\CommercialQuotesQuery::class,
            App\GraphQL\Queries\BackOffice\Commercial\CommercialQuoteHistoriesQuery::class,
            App\GraphQL\Queries\BackOffice\Commercial\CommercialQuoteHistoryPdfQuery::class,
            App\GraphQL\Queries\BackOffice\Commercial\CommercialQuotePdfQuery::class,
            App\GraphQL\Queries\BackOffice\Commercial\CommercialQuoteCounterQuery::class,
            // ----------------------------------------------------------------------------

            // Commissioning --------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Commercial\Commissioning\ProtocolQuery::class,
            App\GraphQL\Queries\BackOffice\Commercial\Commissioning\QuestionQuery::class,
            App\GraphQL\Queries\BackOffice\Commercial\Commissioning\OptionAnswerQuery::class,
            // ----------------------------------------------------------------------------

            // Locations ------------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Locations\States::class,
            App\GraphQL\Queries\BackOffice\Locations\Countries::class,
            // ----------------------------------------------------------------------------

            App\GraphQL\Queries\BackOffice\Projects\ProjectsQuery::class,

            App\GraphQL\Queries\BackOffice\Security\IpAccessQuery::class,

            App\GraphQL\Queries\BackOffice\Sliders\SliderQuery::class,

            App\GraphQL\Queries\BackOffice\Statistics\SolutionStatisticsDownloadQuery::class,

            App\GraphQL\Queries\BackOffice\Stores\StoreCategoryQuery::class,
            App\GraphQL\Queries\BackOffice\Stores\StoreQuery::class,
            App\GraphQL\Queries\BackOffice\Stores\DistributorQuery::class,

            // Technicians ----------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Technicians\TechniciansQuery::class,
            App\GraphQL\Queries\BackOffice\Technicians\TechniciansArchiveQuery::class,
            // ----------------------------------------------------------------------------

            // Dealers --------------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Dealers\DealersQuery::class,
            // ----------------------------------------------------------------------------

            // Dealer Orders --------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Orders\Dealer\OrdersQuery::class,
            App\GraphQL\Queries\BackOffice\Orders\Dealer\PaymentDescQuery::class,
            // ----------------------------------------------------------------------------

            // Companies ------------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Companies\CompaniesQuery::class,
            App\GraphQL\Queries\BackOffice\Companies\CorporationListQuery::class,
            App\GraphQL\Queries\BackOffice\Companies\CompanyListQuery::class,
            App\GraphQL\Queries\BackOffice\Companies\ShippingAddressListQuery::class,
            // ----------------------------------------------------------------------------

            App\GraphQL\Queries\BackOffice\Permissions\AvailableAdminGrantsQuery::class,
            App\GraphQL\Queries\BackOffice\Permissions\AvailableUserGrantsQuery::class,
            App\GraphQL\Queries\BackOffice\Permissions\AvailableTechnicianGrantsQuery::class,

            // Catalog Labels --------------------------------------------------------------------
            App\GraphQL\Queries\BackOffice\Catalog\Labels\LabelsQuery::class,
            // -----------------------------------------------------------------------------------

            App\GraphQL\Queries\BackOffice\Catalog\Solutions\SolutionSeriesListQuery::class,
            App\GraphQL\Queries\BackOffice\Catalog\Solutions\SolutionBtuListQuery::class,
            App\GraphQL\Queries\BackOffice\Catalog\Solutions\SolutionListQuery::class,

            App\GraphQL\Queries\BackOffice\Catalog\Categories\CategoriesQuery::class,
            App\GraphQL\Queries\BackOffice\Catalog\Categories\CategoryQuery::class,
            App\GraphQL\Queries\BackOffice\Catalog\Categories\CategoriesAsTreeQuery::class,
            App\GraphQL\Queries\BackOffice\Catalog\Categories\CategoriesForSelectQuery::class,

            App\GraphQL\Queries\BackOffice\Catalog\Products\ProductKeywordsQuery::class,
            App\GraphQL\Queries\BackOffice\Catalog\Products\ProductsQuery::class,
            App\GraphQL\Queries\BackOffice\Catalog\Products\ProductQuery::class,

            App\GraphQL\Queries\BackOffice\Catalog\UnitTypes\UnitTypesQuery::class,

            App\GraphQL\Queries\BackOffice\Catalog\Features\Values\ValuesQuery::class,
            App\GraphQL\Queries\BackOffice\Catalog\Features\Values\MetricsQuery::class,
            App\GraphQL\Queries\BackOffice\Catalog\Features\Features\FeaturesQuery::class,

            App\GraphQL\Queries\BackOffice\Catalog\Videos\Groups\VideoGroupsQuery::class,
            App\GraphQL\Queries\BackOffice\Catalog\Videos\Links\VideoLinksQuery::class,

            App\GraphQL\Queries\BackOffice\Catalog\Troubleshoots\Groups\GroupsQuery::class,
            App\GraphQL\Queries\BackOffice\Catalog\Troubleshoots\Troubleshoot\TroubleshootQuery::class,

            App\GraphQL\Queries\BackOffice\Catalog\Manuals\ManualGroupsQuery::class,
            App\GraphQL\Queries\BackOffice\Catalog\Manuals\ManualsQuery::class,

            // PDF
            App\GraphQL\Queries\BackOffice\Catalog\Pdf\PdfsQuery::class,

            App\GraphQL\Queries\BackOffice\Content\OurCases\OurCasesQuery::class,

            App\GraphQL\Queries\BackOffice\Faq\FaqQuery::class,
            App\GraphQL\Queries\BackOffice\Faq\QuestionQuery::class,
            App\GraphQL\Queries\BackOffice\Faq\QuestionCounterQuery::class,

            App\GraphQL\Queries\BackOffice\News\NewsQuery::class,
            App\GraphQL\Queries\BackOffice\News\TagsQuery::class,
            App\GraphQL\Queries\BackOffice\News\PhotoAlbumQuery::class,
            App\GraphQL\Queries\BackOffice\News\VideosQuery::class,

            App\GraphQL\Queries\BackOffice\Supports\SupportQuery::class,

            App\GraphQL\Queries\BackOffice\Menu\MenuQuery::class,

            App\GraphQL\Queries\BackOffice\Catalog\Certificates\CertificateTypesQuery::class,
            App\GraphQL\Queries\BackOffice\Catalog\Certificates\CertificatesQuery::class,

            App\GraphQL\Queries\BackOffice\Orders\Categories\OrderCategoriesQuery::class,
            App\GraphQL\Queries\BackOffice\Orders\DeliveryTypes\OrderDeliveryTypesQuery::class,
            App\GraphQL\Queries\BackOffice\Orders\OrderQuery::class,
            App\GraphQL\Queries\BackOffice\Orders\OrderAvailableProjectQuery::class,
            App\GraphQL\Queries\BackOffice\Orders\OrderStatusHistoryQuery::class,
            App\GraphQL\Queries\BackOffice\Orders\OrderCounterQuery::class,

            App\GraphQL\Queries\BackOffice\Content\OurCases\OurCaseCategoriesQuery::class,

            App\GraphQL\Queries\BackOffice\Utilities\AppVersionsQuery::class,

            App\GraphQL\Queries\BackOffice\Warranty\WarrantyInfo\WarrantyInfoQuery::class,
            App\GraphQL\Queries\BackOffice\Warranty\WarrantyRegistrations\WarrantyRegistrationQuery::class,

            App\GraphQL\Queries\BackOffice\SupportRequests\SupportRequestSubjectsQuery::class,
            App\GraphQL\Queries\BackOffice\SupportRequests\SupportRequestsQuery::class,
            App\GraphQL\Queries\BackOffice\SupportRequests\SupportRequestCounterQuery::class,

            App\GraphQL\Queries\BackOffice\Alerts\AlertQuery::class,
            App\GraphQL\Queries\BackOffice\Alerts\AlertCounterQuery::class,
            App\GraphQL\Queries\BackOffice\Alerts\MembersForAlertQuery::class,

            App\GraphQL\Queries\BackOffice\About\PageQuery::class,

            App\GraphQL\Queries\BackOffice\GlobalSettings\GlobalSettingQuery::class,
        ],
        'mutation' => [
            App\GraphQL\Mutations\Common\Testing\TranslatedErrorMutation::class,
            App\GraphQL\Mutations\Common\Testing\InternalErrorMutation::class,

            App\GraphQL\Mutations\BackOffice\About\AboutCompanyMutation::class,
            App\GraphQL\Mutations\BackOffice\About\ForMemberPageMutation::class,
            App\GraphQL\Mutations\BackOffice\About\VideoManager\VideoDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\About\VideoManager\VideoUploadMutation::class,
            App\GraphQL\Mutations\BackOffice\About\VideoManager\VideoPreviewUploadMutation::class,
            App\GraphQL\Mutations\BackOffice\About\VideoManager\VideoPreviewDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\About\VideoManager\AdditionalVideoDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\About\VideoManager\AdditionalVideoUploadMutation::class,

            App\GraphQL\Mutations\BackOffice\About\VideoManager\AdditionalVideoPreviewUploadMutation::class,
            App\GraphQL\Mutations\BackOffice\About\VideoManager\AdditionalVideoPreviewDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Chat\JoinConversationMutation::class,
            App\GraphQL\Mutations\BackOffice\Chat\SendMessageMutation::class,
            App\GraphQL\Mutations\BackOffice\Chat\ReadMessagesMutation::class,
            App\GraphQL\Mutations\BackOffice\Chat\ReadAllMessagesMutation::class,
            App\GraphQL\Mutations\BackOffice\Chat\ChatConversationStartMutation::class,
            App\GraphQL\Mutations\BackOffice\Chat\ChatConversationClosedMutation::class,

            App\GraphQL\Mutations\BackOffice\Chat\Menu\ChatMenuCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Chat\Menu\ChatMenuUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Chat\Menu\ChatMenuToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\Chat\Menu\ChatMenuDeleteMutation::class,

            // Commercial Project ---------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Commercial\CommercialProjects\CommercialProjectUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\CommercialProjects\CommercialProjectSetWarrantyMutation::class,
            // ----------------------------------------------------------------------------

            // Commercial Quote -----------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Commercial\CommercialQuotes\CommercialQuoteUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\CommercialQuotes\CommercialQuoteSendEmailMutation::class,
            // ----------------------------------------------------------------------------

            // Members --------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Member\VerifyEmailMutation::class,
            // ----------------------------------------------------------------------------

            App\GraphQL\Mutations\BackOffice\Commercial\CommercialSettings\CommercialSettingsMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\CommercialSettings\CommercialSettingsPdfUploadMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\CommercialSettings\CommercialSettingsRDPUploadMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\CommercialSettings\CommercialSettingsPdfDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\CommercialSettings\CommercialSettingsRDPDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Commercial\Credentials\RDPAccountUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\Credentials\RDPAccountDeactivateMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\Credentials\RDPAccountDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\Credentials\CredentialsRequestApproveMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\Credentials\CredentialsRequestDenyMutation::class,

            // Commissioning --------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Protocol\CreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Protocol\UpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Protocol\DeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Question\CreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Question\UpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Question\DeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\OptionAnswer\CreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\OptionAnswer\UpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\OptionAnswer\DeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\SetStatusProjectQuestionMutation::class,

            App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\StartCommissioningMutation::class,
            // ----------------------------------------------------------------------------

            // Media ----------------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Utilities\Media\MediaUploadMutation::class,
            App\GraphQL\Mutations\BackOffice\Utilities\Media\MediaDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Utilities\Media\MediaSortMutation::class,
            // ----------------------------------------------------------------------------

            App\GraphQL\Mutations\BackOffice\Utilities\Sorting\SortModelsMutation::class,

            App\GraphQL\Mutations\BackOffice\Utilities\Upload\UploadMultiLangMutation::class,
            App\GraphQL\Mutations\BackOffice\Utilities\CreateOrUpdateAppVersionsMutation::class,

            SetLanguageMutation::class,

            App\GraphQL\Mutations\BackOffice\Admins\Avatars\AvatarUploadMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\Avatars\AvatarDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Admins\AdminLoginMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminTokenRefreshMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminLogoutMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminChangePasswordMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminUpdateProfileMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminDeleteProfileMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminLoginAsUserMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminForgotPasswordMutation::class,
            App\GraphQL\Mutations\BackOffice\Admins\AdminResetPasswordMutation::class,

            App\GraphQL\Mutations\BackOffice\Localization\CreateOrUpdateTranslateMutation::class,
            App\GraphQL\Mutations\BackOffice\Localization\UpsertTranslationsMutation::class,
            App\GraphQL\Mutations\BackOffice\Localization\DeleteTranslateMutation::class,

            App\GraphQL\Mutations\BackOffice\Permission\UserRoleCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\UserRoleUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\UserRoleDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\AdminRoleCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\AdminRoleUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Permission\AdminRoleDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Security\IpAccessCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Security\IpAccessUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Security\IpAccessDeleteMutation::class,

            // Dealers ---------------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Dealers\CreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Dealers\ToggleMainMutation::class,
            // -----------------------------------------------------------------------------------

            // Dealers order ---------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Orders\Dealer\UpdatePaymentDescMutation::class,
            // -----------------------------------------------------------------------------------

            // Companies -------------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Companies\UpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Companies\SendCodeMutation::class,
            App\GraphQL\Mutations\BackOffice\Companies\SendCompanyDataToOnecMutation::class,
            App\GraphQL\Mutations\BackOffice\Companies\ShippingAddress\ToggleActiveMutation::class,
            // -----------------------------------------------------------------------------------

            // Technicians -----------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Technicians\TechnicianToggleCommercialCertificationMutation::class,
            App\GraphQL\Mutations\BackOffice\Technicians\TechnicianToggleCertifiedMutation::class,
            App\GraphQL\Mutations\BackOffice\Technicians\TechnicianToggleVerifiedMutation::class,
            App\GraphQL\Mutations\BackOffice\Technicians\TechnicianSoftDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Technicians\TechnicianDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Technicians\TechnicianRestoreMutation::class,
            // -----------------------------------------------------------------------------------

            // Users -----------------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Users\UserDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Users\UserSoftDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Users\UserRestoreMutation::class,
            // -----------------------------------------------------------------------------------

            // Catalog Labels --------------------------------------------------------------------
            App\GraphQL\Mutations\BackOffice\Catalog\Labels\CreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Labels\UpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Labels\DeleteMutation::class,
            // -----------------------------------------------------------------------------------

            App\GraphQL\Mutations\BackOffice\Catalog\Solutions\Series\SolutionSeriesCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Solutions\Series\SolutionSeriesUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Solutions\Series\SolutionSeriesDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Solutions\SolutionCreateUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Solutions\SolutionDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Categories\CategoryCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Categories\CategoryUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Categories\CategoryToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Categories\CategoryDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Categories\PosterImage\PosterImageDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Categories\PosterImage\PosterImageUploadMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Products\Keywords\ProductKeywordsModifyMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Products\Keywords\ProductKeywordCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Products\Keywords\ProductKeywordUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Products\Keywords\ProductKeywordDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Products\ProductCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Products\ProductUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Products\ProductToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Products\ProductDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Features\Values\FeatureValueCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Features\Values\FeatureValueUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Features\Values\FeatureValueToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Features\Values\FeatureValueDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Features\Features\FeatureCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Features\Features\FeatureUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Features\Features\FeatureToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Features\Features\FeatureDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Features\Specifications\SpecificationCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Features\Specifications\SpecificationUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Features\Specifications\SpecificationDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Features\Specifications\SpecificationToggleActiveMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Videos\Groups\VideoGroupCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Videos\Groups\VideoGroupUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Videos\Groups\VideoGroupToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Videos\Groups\VideoGroupDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Videos\Links\VideoLinkCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Videos\Links\VideoLinkUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Videos\Links\VideoLinkToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Videos\Links\VideoLinkDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Groups\TroubleshootGroupCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Groups\TroubleshootGroupUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Groups\TroubleshootGroupToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Groups\TroubleshootGroupDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot\TroubleshootCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot\TroubleshootUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot\TroubleshootToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot\TroubleshootDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Manuals\Groups\ManualGroupCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Manuals\Groups\ManualGroupUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Manuals\Groups\ManualGroupDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Manuals\ManualCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Manuals\ManualUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Manuals\ManualDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Type\CertificateTypeCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Type\CertificateTypeUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Type\CertificateTypeDeleteMutation::class,
            // Certificate
            App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Certificate\CertificateCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Certificate\CertificateDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Certificates\Certificate\CertificateUpdateMutation::class,

            // PDF Upload
            App\GraphQL\Mutations\BackOffice\Catalog\Pdf\PdfUploadMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Pdf\PdfReUploadMutation::class,
            App\GraphQL\Mutations\BackOffice\Catalog\Pdf\PdfDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Content\OurCaseCategories\OurCaseCategoryCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Content\OurCaseCategories\OurCaseCategoryUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Content\OurCaseCategories\OurCaseCategoryToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\Content\OurCaseCategories\OurCaseCategoryDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Content\OurCases\OurCaseCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Content\OurCases\OurCaseUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Content\OurCases\OurCaseToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\Content\OurCases\OurCaseDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Faq\Questions\AnswerQuestionMutation::class,
            App\GraphQL\Mutations\BackOffice\Faq\Questions\QuestionDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Faq\FaqCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Faq\FaqUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Faq\FaqDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Faq\FaqToggleActiveMutation::class,

            App\GraphQL\Mutations\BackOffice\Sliders\SliderCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Sliders\SliderUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Sliders\SliderDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Sliders\SliderToggleActiveMutation::class,

            App\GraphQL\Mutations\BackOffice\Stores\StoreCategories\StoreCategoryCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Stores\StoreCategories\StoreCategoryUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Stores\StoreCategories\StoreCategoryDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Stores\StoreCategories\StoreCategoryToggleActiveMutation::class,

            App\GraphQL\Mutations\BackOffice\Stores\Stores\StoreCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Stores\Stores\StoreUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Stores\Stores\StoreDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Stores\Stores\StoreToggleActiveMutation::class,

            App\GraphQL\Mutations\BackOffice\Stores\Distributors\DistributorCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Stores\Distributors\DistributorUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Stores\Distributors\DistributorDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Stores\Distributors\DistributorToggleActiveMutation::class,

            App\GraphQL\Mutations\BackOffice\News\NewsCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\News\NewsUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\News\NewsToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\News\NewsDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\News\PhotoAlbum\PhotoAlbumDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\News\PhotoAlbum\PhotoAlbumUploadMutation::class,

            App\GraphQL\Mutations\BackOffice\News\Videos\VideoCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\News\Videos\VideoUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\News\Videos\VideoToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\News\Videos\VideoDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\Supports\SupportUpdateMutation::class,

            App\GraphQL\Mutations\BackOffice\Menu\MenuCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Menu\MenuUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Menu\MenuDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Menu\MenuToggleActiveMutation::class,

            App\GraphQL\Mutations\BackOffice\Warranty\WarrantyInfo\WarrantyInfoMutation::class,

            App\GraphQL\Mutations\BackOffice\Orders\Categories\OrderCategoryCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\Categories\OrderCategoryUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\Categories\OrderCategoryDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\Categories\OrderCategoryToggleActiveMutation::class,

            App\GraphQL\Mutations\BackOffice\Orders\Deliveries\OrderDeliveryTypeCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\Deliveries\OrderDeliveryTypeUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\Deliveries\OrderDeliveryTypeDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\Deliveries\OrderDeliveryTypeToggleActiveMutation::class,

            App\GraphQL\Mutations\BackOffice\Orders\OrderCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\OrderUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\OrderConnectProjectMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\OrderDisconnectProjectMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\OrderDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\OrderStatusChangeMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\OrderShippingUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\OrderPaymentUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\OrderCommentUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\OrderTechnicianChangeMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\OrderSerialNumberChangeMutation::class,
            App\GraphQL\Mutations\BackOffice\Orders\OrderPartsUpdateMutation::class,

            App\GraphQL\Mutations\BackOffice\SupportRequests\Subjects\SupportRequestSubjectCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\SupportRequests\Subjects\SupportRequestSubjectUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\SupportRequests\Subjects\SupportRequestSubjectDeleteMutation::class,
            App\GraphQL\Mutations\BackOffice\SupportRequests\Subjects\SupportRequestSubjectToggleActiveMutation::class,

            App\GraphQL\Mutations\BackOffice\SupportRequests\SupportRequestAnswerMutation::class,
            App\GraphQL\Mutations\BackOffice\SupportRequests\SupportRequestCloseMutation::class,
            App\GraphQL\Mutations\BackOffice\SupportRequests\SupportRequestSetIsReadMutation::class,

            App\GraphQL\Mutations\BackOffice\Alerts\AlertSetReadMutation::class,
            App\GraphQL\Mutations\BackOffice\Alerts\AlertSendMutation::class,

            App\GraphQL\Mutations\BackOffice\About\Pages\PageCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\About\Pages\PageUpdateMutation::class,
            App\GraphQL\Mutations\BackOffice\About\Pages\PageToggleActiveMutation::class,
            App\GraphQL\Mutations\BackOffice\About\Pages\PageDeleteMutation::class,

            App\GraphQL\Mutations\BackOffice\GlobalSettings\GlobalSettingCreateMutation::class,
            App\GraphQL\Mutations\BackOffice\GlobalSettings\GlobalSettingUpdateMutation::class,
        ],
        'subscription' => [
            App\GraphQL\Subscriptions\BackOffice\Alerts\AlertSubscription::class,
            App\GraphQL\Subscriptions\BackOffice\Chat\ConversationUpdatedSubscription::class,
            App\GraphQL\Subscriptions\BackOffice\Orders\OrderSubscription::class,
            App\GraphQL\Subscriptions\BackOffice\SupportRequests\SupportRequestSubscription::class,
        ],
        'middleware' => [
            App\GraphQL\Middlewares\Security\IpAccessMiddleware::class,
        ],
        'method' => ['post'],
    ],
];
