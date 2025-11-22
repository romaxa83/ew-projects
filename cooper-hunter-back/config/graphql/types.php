<?php

declare(strict_types=1);

// The types available in the application. You can then access it from the
// facade like this: GraphQL::type('user')
//
// Example:
//
// 'types' => [
//     'user' => 'App\GraphQL\Type\UserType'
// ]

return [
    App\GraphQL\Types\UploadType::class,

    App\GraphQL\InputTypes\SimpleTranslationInput::class,
    App\GraphQL\InputTypes\SimpleTranslationWithDescriptionInput::class,

    App\GraphQL\Types\Enums\Chat\ConversationTabEnumType::class,

    // Commercial Input and Types
    App\GraphQL\InputTypes\Commercial\CommercialProjectInput::class,
    App\GraphQL\InputTypes\Commercial\CommercialProjectAdditionInput::class,
    App\GraphQL\InputTypes\Commercial\CommercialProjectAdminInput::class,
    App\GraphQL\InputTypes\Commercial\CommercialQuoteInput::class,
    App\GraphQL\InputTypes\Commercial\CommercialQuoteAdminInput::class,
    App\GraphQL\InputTypes\Commercial\CommercialQuoteItemInput::class,
    App\GraphQL\InputTypes\Commercial\CommercialCredentialsInput::class,
    App\GraphQL\Types\Enums\Commercial\CommercialCredentialsStatusEnumType::class,
    App\GraphQL\Types\Enums\Commercial\CommercialProjectStatusEnumType::class,
    App\GraphQL\Types\Enums\Commercial\CommercialQuoteStatusEnumType::class,
    App\GraphQL\Types\Commercial\RDPAccountType::class,
    App\GraphQL\Types\Commercial\CredentialsRequestType::class,
    App\GraphQL\Types\Commercial\CommercialProjectType::class,
    App\GraphQL\Types\Commercial\CommercialQuoteType::class,
    App\GraphQL\Types\Commercial\CommercialQuoteItemType::class,
    App\GraphQL\Types\Commercial\CommercialQuoteHistoryType::class,
    App\GraphQL\Types\Commercial\CommercialSettingsType::class,
    App\GraphQL\Types\Commercial\CredentialRequestCounterType::class,
    App\GraphQL\Types\Commercial\TaxType::class,
    App\GraphQL\Types\Commercial\CommercialQuoteCounterType::class,
    App\GraphQL\Types\Commercial\CommercialProjectUnitType::class,
    App\GraphQL\Types\Commercial\CommercialProjectAdditionType::class,

    // Commissioning -----------------------------------------------------------
    App\GraphQL\Types\Enums\Commercial\Commissioning\ProtocolTypeEnumType::class,
    App\GraphQL\Types\Enums\Commercial\Commissioning\ProtocolStatusEnumType::class,
    App\GraphQL\Types\Enums\Commercial\Commissioning\AnswerTypeEnumType::class,
    App\GraphQL\Types\Enums\Commercial\Commissioning\AnswerPhotoTypeEnumType::class,
    App\GraphQL\Types\Enums\Commercial\Commissioning\AnswerStatusEnumType::class,
    App\GraphQL\Types\Enums\Commercial\Commissioning\QuestionStatusEnumType::class,

    App\GraphQL\Types\Commercial\Commissioning\ProtocolTranslationInputType::class,
    App\GraphQL\Types\Commercial\Commissioning\ProtocolTranslationType::class,
    App\GraphQL\Types\Commercial\Commissioning\ProtocolType::class,
    App\GraphQL\Types\Commercial\Commissioning\QuestionTranslationInputType::class,
    App\GraphQL\Types\Commercial\Commissioning\QuestionTranslationType::class,
    App\GraphQL\Types\Commercial\Commissioning\QuestionType::class,
    App\GraphQL\Types\Commercial\Commissioning\OptionAnswerTranslationInputType::class,
    App\GraphQL\Types\Commercial\Commissioning\OptionAnswerTranslationType::class,
    App\GraphQL\Types\Commercial\Commissioning\OptionAnswerType::class,
    App\GraphQL\Types\Commercial\Commissioning\ProjectProtocolType::class,
    App\GraphQL\Types\Commercial\Commissioning\ProjectProtocolQuestionType::class,
    App\GraphQL\Types\Commercial\Commissioning\AnswerType::class,

    App\GraphQL\InputTypes\Commercial\Commissioning\ProtocolInput::class,
    App\GraphQL\InputTypes\Commercial\Commissioning\QuestionInput::class,
    App\GraphQL\InputTypes\Commercial\Commissioning\QuestionUpdateInput::class,
    App\GraphQL\InputTypes\Commercial\Commissioning\OptionAnswerInput::class,
    App\GraphQL\InputTypes\Commercial\Commissioning\OptionsAnswerUpdateInput::class,
    App\GraphQL\InputTypes\Commercial\Commissioning\AnswerInput::class,
    // --------------------------------------------------------------------------

    // Payments -----------------------------------------------------------------
    App\GraphQL\Types\Enums\Payments\PaymentCard\MorphSupportedTypeEnumType::class,

    App\GraphQL\InputTypes\Payments\PaymentCardAddInput::class,

    App\GraphQL\Types\Payments\MemberPaymentCardType::class,
    // --------------------------------------------------------------------------

    // Member -------------------------------------------------------------------
    App\GraphQL\Types\Enums\Members\MemberTypeEnum::class,
    // --------------------------------------------------------------------------

    // About Input and Types
    App\GraphQL\Types\Enums\About\ForMemberPageEnumType::class,
    App\GraphQL\InputTypes\About\About\AboutCompanyInput::class,
    App\GraphQL\InputTypes\About\About\AboutCompanyTranslationInput::class,
    App\GraphQL\InputTypes\About\ForMemberPages\ForMemberPageInput::class,
    App\GraphQL\InputTypes\About\ForMemberPages\ForMemberPageTranslationInput::class,

    App\GraphQL\Types\Projects\ProjectType::class,
    App\GraphQL\Types\Projects\ProjectSystemType::class,
    App\GraphQL\Types\Projects\ProjectSystemUnitType::class,

    App\GraphQL\Types\Enums\Projects\Systems\WarrantyStatusEnumType::class,
    App\GraphQL\Types\Enums\Warranties\WarrantyTypeEnumType::class,

    App\GraphQL\InputTypes\Projects\ProjectCreateInput::class,
    App\GraphQL\InputTypes\Projects\ProjectUpdateInput::class,

    App\GraphQL\InputTypes\Projects\Systems\CheckProjectSystemUnitInput::class,
    App\GraphQL\InputTypes\Projects\Systems\ProjectSystemCreateInput::class,
    App\GraphQL\InputTypes\Projects\Systems\ProjectSystemUpdateInput::class,
    App\GraphQL\InputTypes\Projects\Systems\ProjectSystemUnitInput::class,

    App\GraphQL\Types\Sliders\SliderType::class,
    App\GraphQL\Types\Sliders\SliderTranslationType::class,

    App\GraphQL\InputTypes\Sliders\SliderInput::class,
    App\GraphQL\InputTypes\Sliders\SliderTranslationInput::class,

    App\GraphQL\Types\Stores\StoreCategoryType::class,
    App\GraphQL\Types\Stores\StoreCategoryTranslationType::class,

    App\GraphQL\Types\Stores\StoreType::class,
    App\GraphQL\Types\Stores\StoreTranslationType::class,
    App\GraphQL\Types\Stores\DistributorType::class,
    App\GraphQL\Types\Stores\DistributorTranslationType::class,
    App\GraphQL\Types\Stores\CoordinateType::class,

    App\GraphQL\InputTypes\Stores\Distributors\CoordinateInput::class,
    App\GraphQL\InputTypes\Stores\Distributors\CoordinateInRadiusFilterInput::class,
    App\GraphQL\InputTypes\Stores\Distributors\DistributorInput::class,
    App\GraphQL\InputTypes\Stores\Distributors\DistributorTranslationInput::class,

    App\GraphQL\InputTypes\Stores\StoreCategories\StoreCategoryInputType::class,
    App\GraphQL\InputTypes\Stores\StoreCategories\StoreCategoryTranslationInput::class,

    App\GraphQL\InputTypes\Stores\Stores\StoreInputType::class,
    App\GraphQL\InputTypes\Stores\Stores\StoreTranslationInput::class,

    App\GraphQL\InputTypes\Warranty\AddressInfoInput::class,
    App\GraphQL\InputTypes\Warranty\UserInfoInput::class,
    App\GraphQL\InputTypes\Warranty\TechnicianInfoInput::class,
    App\GraphQL\InputTypes\Warranty\ProductInfoInput::class,

    App\GraphQL\Types\Enums\Messages\MessageKindEnumType::class,

    App\GraphQL\Types\Enums\Permissions\AdminPermissionEnum::class,
    App\GraphQL\Types\Enums\Permissions\UserPermissionEnum::class,
    App\GraphQL\Types\Localization\LanguageType::class,
    App\GraphQL\Types\Localization\TranslateType::class,

    App\GraphQL\Types\Roles\RoleType::class,
    App\GraphQL\Types\Roles\RoleTranslateType::class,
    App\GraphQL\Types\Roles\RoleTranslateInputType::class,
    App\GraphQL\Types\Roles\PermissionType::class,
    App\GraphQL\Types\Roles\GrantType::class,
    App\GraphQL\Types\Roles\GrantGroupType::class,

    App\GraphQL\Types\Enums\Users\UserMorphTypeEnum::class,
    App\GraphQL\Types\Enums\Users\MemberMorphTypeEnum::class,

    App\GraphQL\Types\Users\UserType::class,
    App\GraphQL\Types\Users\UserProfileType::class,
    App\GraphQL\Types\Users\UserMorphType::class,

    App\GraphQL\Types\Members\MemberLoginType::class,
    App\GraphQL\Types\Members\MemberSubscriptionType::class,
    App\GraphQL\Types\Members\MemberType::class,

    // Technicians -----------------------------------------------
    App\GraphQL\Types\Technicians\TechnicianProfileType::class,
    App\GraphQL\Types\Technicians\TechnicianType::class,
    // -----------------------------------------------------------

    // Dealers ---------------------------------------------------
    App\GraphQL\InputTypes\Dealers\DealerInput::class,

    App\GraphQL\Types\Dealers\DealerType::class,
    App\GraphQL\Types\Dealers\DealerProfileType::class,
    // -----------------------------------------------------------

    // Dealer Order ----------------------------------------------
    App\GraphQL\InputTypes\Orders\Dealer\OrderInput::class,

    App\GraphQL\InputTypes\Orders\Dealer\PaymentDescInput::class,
    App\GraphQL\InputTypes\Orders\Dealer\PaymentDescTranslationInput::class,
    App\GraphQL\Types\Orders\Dealer\PaymentDesc\PaymentDescType::class,
    App\GraphQL\Types\Orders\Dealer\PaymentDesc\PaymentDescTranslationType::class,

    App\GraphQL\Types\Enums\Orders\Dealer\OrderStatusTypeEnum::class,
    App\GraphQL\Types\Enums\Orders\Dealer\OrderTypeTypeEnum::class,
    App\GraphQL\Types\Enums\Orders\Dealer\DeliveryTypeTypeEnum::class,
    App\GraphQL\Types\Enums\Orders\Dealer\PaymentTypeTypeEnum::class,

    App\GraphQL\Types\Orders\Dealer\OrderType::class,
    App\GraphQL\Types\Orders\Dealer\ItemType::class,
    App\GraphQL\Types\Orders\Dealer\ProductType::class,
    App\GraphQL\Types\Orders\Dealer\OrderFileFromOnecType::class,
    App\GraphQL\Types\Orders\Dealer\SerialNumberType::class,
    App\GraphQL\Types\Orders\Dealer\PackingSlipType::class,
    App\GraphQL\Types\Orders\Dealer\DimensionsType::class,
    // report
    App\GraphQL\Types\Orders\Dealer\Report\ReportType::class,
    App\GraphQL\Types\Orders\Dealer\Report\ReportLocationType::class,
    App\GraphQL\Types\Orders\Dealer\Report\ReportLocationItemType::class,
    // packing slip
    App\GraphQL\InputTypes\Orders\Dealer\PackingSlipInput::class,
    App\GraphQL\Types\Orders\Dealer\PackingSlipItemType::class,
    App\GraphQL\Types\Orders\Dealer\PackingSlipSerialNumberType::class,
    // -----------------------------------------------------------

    // Companies -------------------------------------------------
    App\GraphQL\Types\Companies\ManagerType::class,
    App\GraphQL\Types\Companies\CommercialManagerType::class,
    App\GraphQL\Types\Companies\CompanyType::class,
    App\GraphQL\Types\Companies\CompanyForListType::class,
    App\GraphQL\Types\Companies\ContactType::class,
    App\GraphQL\Types\Companies\CorporationType::class,
    App\GraphQL\Types\Companies\ShippingAddressType::class,
    App\GraphQL\Types\Companies\ShippingAddressForListType::class,

    App\GraphQL\InputTypes\Companies\CompanyInput::class,
    App\GraphQL\InputTypes\Companies\ContactInput::class,
    App\GraphQL\InputTypes\Companies\ShippingAddressInput::class,

    App\GraphQL\Types\Enums\Companies\CompanyTypeEnumType::class,
    App\GraphQL\Types\Enums\Companies\CompanyStatusEnumType::class,
    // -----------------------------------------------------------

    App\GraphQL\Types\Members\MemberProfileUnionType::class,

    App\GraphQL\Types\Admins\AdminType::class,
    App\GraphQL\Types\Admins\AdminLoginType::class,
    App\GraphQL\Types\Admins\AdminProfileType::class,

    App\GraphQL\Types\Users\AdminUserLoginType::class,

    App\GraphQL\Types\Unions\Authenticatable::class,

    App\GraphQL\Types\Messages\ResponseMessageType::class,

    App\GraphQL\Types\Security\IpAccessType::class,
    // Locations ----------------------------------------------------------------
    App\GraphQL\Types\Locations\StateType::class,
    App\GraphQL\Types\Locations\StateTranslationsType::class,
    App\GraphQL\Types\Locations\CountryType::class,
    App\GraphQL\Types\Locations\CountryTranslationsType::class,
    // --------------------------------------------------------------------------

    // Catalog Brand ------------------------------------------------------------
    App\GraphQL\Types\Catalog\Brands\BrandType::class,
    // --------------------------------------------------------------------------

    // Catalog Label ------------------------------------------------------------
    App\GraphQL\Types\Catalog\Labels\LabelType::class,
    App\GraphQL\Types\Catalog\Labels\LabelTranslationType::class,

    App\GraphQL\InputTypes\Catalog\Labels\LabelTranslationInputType::class,
    App\GraphQL\InputTypes\Catalog\Labels\LabelInput::class,

    App\GraphQL\Types\Enums\Catalog\Labels\ColorTypeEnumType::class,
    // --------------------------------------------------------------------------

    // Catalog Product ----------------------------------------------------------
    App\GraphQL\Types\Enums\Catalog\Products\ProductOwnerTypeEnumType::class,
    App\GraphQL\Types\Enums\Catalog\Products\ProductUnitTypeEnumType::class,
    App\GraphQL\Types\Enums\Catalog\Products\ProductUnitSubTypeEnumType::class,
    // --------------------------------------------------------------------------

    App\GraphQL\Types\Enums\Catalog\CategoryTypeEnumType::class,
    App\GraphQL\Types\Catalog\Categories\CategoryType::class,
    App\GraphQL\Types\Catalog\Categories\SimpleCategoryType::class,
    App\GraphQL\Types\Catalog\Categories\CategoryRootType::class,
    App\GraphQL\Types\Catalog\Categories\CategoryTranslateInputType::class,
    App\GraphQL\Types\Catalog\Categories\CategoryTranslateType::class,
    App\GraphQL\Types\Catalog\Categories\CategoryForSelectType::class,
    App\GraphQL\Types\Catalog\Categories\CategoryForSitemapType::class,
    App\GraphQL\Types\Catalog\Categories\CategoryBreadcrumbType::class,

    App\GraphQL\Types\Catalog\Certificates\CertificateType::class,
    App\GraphQL\Types\Catalog\Certificates\CertificateTypeType::class,

    App\GraphQL\Types\Catalog\Products\ProductKeywordType::class,
    App\GraphQL\Types\Catalog\Products\ProductType::class,
    App\GraphQL\Types\Catalog\Products\SimpleProductType::class,
    App\GraphQL\Types\Catalog\Products\ProductTranslateInputType::class,
    App\GraphQL\Types\Catalog\Products\TranslateType::class,
    App\GraphQL\Types\Catalog\Products\FeatureValueType::class,
    App\GraphQL\Types\Catalog\Products\UnitTypeType::class,

    App\GraphQL\Types\Catalog\Search\CatalogSearchType::class,
    App\GraphQL\Types\Catalog\Search\SearchUnionType::class,

    App\GraphQL\Types\Catalog\Tickets\TicketStatusEnumType::class,
    App\GraphQL\Types\Catalog\Tickets\TicketTranslationType::class,
    App\GraphQL\Types\Catalog\Tickets\TicketOrderCategoryType::class,
    App\GraphQL\Types\Catalog\Tickets\TicketType::class,

    App\GraphQL\Types\Catalog\Features\Specifications\SpecificationType::class,
    App\GraphQL\Types\Catalog\Features\Specifications\SpecificationTranslationType::class,

    App\GraphQL\Types\Catalog\Features\Values\MetricType::class,
    App\GraphQL\Types\Catalog\Features\Values\ValueType::class,

    App\GraphQL\Types\Catalog\Features\Features\FeatureType::class,
    App\GraphQL\Types\Catalog\Features\Features\TranslateInputType::class,
    App\GraphQL\Types\Catalog\Features\Features\TranslateType::class,
    App\GraphQL\Types\Catalog\Features\Features\FeaturesForProductInputType::class,

    App\GraphQL\Types\Catalog\Filters\FilterType::class,
    App\GraphQL\Types\Catalog\Filters\FilterValueType::class,

    App\GraphQL\Types\Catalog\Videos\Groups\VideoGroupType::class,
    App\GraphQL\Types\Catalog\Videos\Groups\TranslateInputType::class,
    App\GraphQL\Types\Catalog\Videos\Groups\TranslateType::class,

    App\GraphQL\Types\Catalog\Videos\Links\VideoLinkTypeEnumType::class,
    App\GraphQL\Types\Catalog\Videos\Links\VideoLinkType::class,
    App\GraphQL\Types\Catalog\Videos\Links\TranslateInputType::class,
    App\GraphQL\Types\Catalog\Videos\Links\TranslateType::class,

    App\GraphQL\Types\Catalog\Troubleshoots\Groups\TroubleshootGroupType::class,
    App\GraphQL\Types\Catalog\Troubleshoots\Groups\TranslateInputType::class,
    App\GraphQL\Types\Catalog\Troubleshoots\Groups\TranslateType::class,

    App\GraphQL\Types\Catalog\Troubleshoots\Troubleshoots\TroubleshootType::class,

    App\GraphQL\Types\Catalog\Manuals\ManualType::class,
    App\GraphQL\Types\Catalog\Manuals\ManualGroupType::class,
    App\GraphQL\Types\Catalog\Manuals\ManualGroupTranslateType::class,

    App\GraphQL\Types\Catalog\Manuals\Categories\ManualCategoryType::class,
    App\GraphQL\Types\Catalog\Manuals\Categories\ManualSubcategoryType::class,
    App\GraphQL\Types\Catalog\Manuals\Categories\ManualCategoryProductGroupType::class,
    App\GraphQL\Types\Catalog\Manuals\Categories\ManualCategoryProductListType::class,

    App\GraphQL\Types\Catalog\Pdf\PdfType::class,

    App\GraphQL\Types\Catalog\Favourites\FavouriteProductType::class,

    App\GraphQL\Types\Enums\Dashboard\Widgets\DashboardWidgetSectionEnumType::class,
    App\GraphQL\Types\Enums\Dashboard\Widgets\DashboardWidgetTypeEnumType::class,

    App\GraphQL\Types\Dashboard\Widgets\DashboardWidgetType::class,

    App\GraphQL\InputTypes\Catalog\Features\Specifications\SpecificationCreateInput::class,
    App\GraphQL\InputTypes\Catalog\Features\Specifications\SpecificationTranslationInput::class,
    App\GraphQL\InputTypes\Catalog\Features\Specifications\SpecificationUpdateInput::class,

    App\GraphQL\InputTypes\Catalog\Products\ProductKeywordInput::class,
    App\GraphQL\InputTypes\Catalog\Products\CertificateInputType::class,
    App\GraphQL\InputTypes\Catalog\Products\ProductInput::class,

    App\GraphQL\InputTypes\Catalog\Solutions\FindSolutionIndoorInputType::class,
    App\GraphQL\InputTypes\Catalog\Solutions\FindSolutionDefaultSchemaInputType::class,
    App\GraphQL\InputTypes\Catalog\Solutions\FindSolutionLineSetInputType::class,
    App\GraphQL\InputTypes\Catalog\Solutions\FindSolutionInputType::class,
    App\GraphQL\InputTypes\Catalog\Solutions\FindSolutionIndoorPdfInputType::class,
    App\GraphQL\InputTypes\Catalog\Solutions\FindSolutionPdfInputType::class,
    App\GraphQL\InputTypes\Catalog\Solutions\SolutionSeries\SolutionSeriesInputType::class,
    App\GraphQL\InputTypes\Catalog\Solutions\SolutionSeries\SolutionSeriesTranslationInputType::class,
    App\GraphQL\Types\Catalog\Solutions\SolutionDefaultSchemaType::class,
    App\GraphQL\Types\Catalog\Solutions\SolutionType::class,

    App\GraphQL\Types\Enums\Catalog\Solutions\SolutionClimateZoneEnumType::class,
    App\GraphQL\Types\Enums\Catalog\Solutions\SolutionIndoorEnumType::class,
    App\GraphQL\Types\Enums\Catalog\Solutions\SolutionTypeEnumType::class,
    App\GraphQL\Types\Enums\Catalog\Solutions\SolutionZoneEnumType::class,

    App\GraphQL\Types\Catalog\Solutions\SolutionSeriesTranslateType::class,
    App\GraphQL\Types\Catalog\Solutions\SolutionSeriesType::class,
    App\GraphQL\Types\Catalog\Solutions\SolutionIndoorSettingType::class,
    App\GraphQL\Types\Catalog\Solutions\SolutionLineSetType::class,
    App\GraphQL\Types\Catalog\Solutions\FindSolutionLineSetType::class,
    App\GraphQL\Types\Catalog\Solutions\FindSolutionIndoorType::class,
    App\GraphQL\Types\Catalog\Solutions\FindSolutionType::class,

    App\GraphQL\InputTypes\Catalog\Tickets\TicketInput::class,
    App\GraphQL\InputTypes\Catalog\Tickets\TicketTranslationInput::class,

    App\GraphQL\Types\Content\OurCase\OurCaseType::class,
    App\GraphQL\Types\Content\OurCase\OurCaseTranslationType::class,
    App\GraphQL\Types\Content\OurCase\OurCaseCategoryType::class,
    App\GraphQL\Types\Content\OurCase\OurCaseCategoryTranslationType::class,
    App\GraphQL\InputTypes\Content\OurCaseCategories\OurCaseCategoryCreateInput::class,
    App\GraphQL\InputTypes\Content\OurCaseCategories\OurCaseCategoryUpdateInput::class,
    App\GraphQL\InputTypes\Content\OurCaseCategories\OurCaseCategoryTranslationInput::class,

    App\GraphQL\InputTypes\Content\OurCases\OurCaseCreateInput::class,
    App\GraphQL\InputTypes\Content\OurCases\OurCaseUpdateInput::class,
    App\GraphQL\InputTypes\Content\OurCases\OurCaseTranslationInput::class,

    App\GraphQL\Types\About\AboutCompanyType::class,
    App\GraphQL\Types\About\AboutCompanyTranslationType::class,
    App\GraphQL\Types\About\ForMemberPageType::class,
    App\GraphQL\Types\About\ForMemberPageTranslationType::class,

    App\GraphQL\Types\Faq\QuestionType::class,
    App\GraphQL\Types\Faq\QuestionCounterType::class,

    App\GraphQL\Types\Faq\FaqType::class,
    App\GraphQL\Types\Faq\FaqTranslationType::class,

    App\GraphQL\Types\News\NewsType::class,
    App\GraphQL\Types\News\NewsTranslationType::class,

    App\GraphQL\Types\News\VideoType::class,
    App\GraphQL\Types\News\VideoTranslationType::class,

    App\GraphQL\Types\News\TagType::class,
    App\GraphQL\Types\News\TagTranslationType::class,

    App\GraphQL\Types\Enums\Menu\MenuPositionTypeEnum::class,
    App\GraphQL\Types\Enums\Menu\MenuBlockTypeEnum::class,

    App\GraphQL\Types\Menu\MenuTranslationType::class,
    App\GraphQL\Types\Menu\MenuType::class,

    App\GraphQL\InputTypes\News\NewsCreateInput::class,
    App\GraphQL\InputTypes\News\NewsUpdateInput::class,
    App\GraphQL\InputTypes\News\NewsTranslationInput::class,

    App\GraphQL\InputTypes\News\Videos\VideoCreateInput::class,
    App\GraphQL\InputTypes\News\Videos\VideoUpdateInput::class,
    App\GraphQL\InputTypes\News\Videos\VideoTranslationInput::class,

    App\GraphQL\Types\Supports\SupportType::class,
    App\GraphQL\Types\Supports\SupportTranslationType::class,

    App\GraphQL\InputTypes\Supports\SupportInput::class,
    App\GraphQL\InputTypes\Supports\SupportTranslationInput::class,

    App\GraphQL\InputTypes\Catalog\Manuals\ManualGroupCreateInput::class,
    App\GraphQL\InputTypes\Catalog\Manuals\ManualGroupUpdateInput::class,
    App\GraphQL\InputTypes\Catalog\Manuals\ManualCreateInput::class,
    App\GraphQL\InputTypes\Catalog\Manuals\ManualUpdateInput::class,

    App\GraphQL\InputTypes\Auth\Password\ChangePasswordInput::class,
    App\GraphQL\InputTypes\Auth\Sms\ConfirmSmsTokenInput::class,

    App\GraphQL\InputTypes\Faq\Questions\AskAQuestionInput::class,
    App\GraphQL\InputTypes\Faq\Questions\AnswerQuestionInput::class,

    App\GraphQL\InputTypes\Faq\FaqCreateInput::class,
    App\GraphQL\InputTypes\Faq\FaqUpdateInput::class,
    App\GraphQL\InputTypes\Faq\FaqTranslationInput::class,

    App\GraphQL\InputTypes\Favourites\FavouriteInput::class,

    App\GraphQL\InputTypes\Menu\MenuInput::class,
    App\GraphQL\InputTypes\Menu\MenuTranslationInput::class,

    App\GraphQL\InputTypes\Warranty\WarrantyInfo\WarrantyInfoInput::class,
    App\GraphQL\InputTypes\Warranty\WarrantyInfo\WarrantyInfoTranslationInput::class,
    App\GraphQL\InputTypes\Warranty\WarrantyInfo\WarrantyInfoPackageInput::class,
    App\GraphQL\InputTypes\Warranty\WarrantyInfo\WarrantyInfoPackageTranslationInput::class,

    App\GraphQL\Types\Auth\Sms\SmsCodeTokenType::class,
    App\GraphQL\Types\Auth\Sms\SmsAccessTokenType::class,

    App\GraphQL\Types\Media\MediaType::class,
    App\GraphQL\Types\Media\MediaConversionType::class,
    App\GraphQL\Types\Media\MediaModelsTypeEnum::class,

    App\GraphQL\Types\Enums\Avatars\AvatarModelsTypeEnum::class,

    App\GraphQL\Types\Warranty\WarrantyInfoType\WarrantyInfoType::class,
    App\GraphQL\Types\Warranty\WarrantyInfoType\WarrantyInfoTranslationType::class,
    App\GraphQL\Types\Warranty\WarrantyInfoType\WarrantyInfoPackageType::class,
    App\GraphQL\Types\Warranty\WarrantyInfoType\WarrantyInfoPackageTranslationType::class,

    App\GraphQL\Types\Warranty\WarrantyRegistrations\WarrantyUserInfoType::class,
    App\GraphQL\Types\Warranty\WarrantyRegistrations\WarrantyRegistrationType::class,

    App\GraphQL\Types\Warranty\WarrantyVerificationStatusType::class,

    App\GraphQL\Types\Enums\Sorting\SortingModelsTypeEnum::class,
    App\GraphQL\Types\Enums\Favourites\FavouriteModelsEnumType::class,
    App\GraphQL\Types\Enums\Favourites\FavouriteSubscriptionActionEnumType::class,
    App\GraphQL\Types\Favourites\FavouriteSubscriptionType::class,

    App\GraphQL\Types\Enums\Faq\Questions\QuestionStatusEnumType::class,

    App\GraphQL\Types\Orders\Categories\OrderCategoryType::class,
    App\GraphQL\Types\Orders\Categories\OrderCategoryTranslateType::class,

    App\GraphQL\Types\Enums\Orders\OrderStatusTypeEnum::class,
    App\GraphQL\Types\Enums\Orders\OrderFilterTabTypeEnum::class,
    App\GraphQL\Types\Enums\Orders\OrderCostStatusTypeEnum::class,
    App\GraphQL\Types\Enums\Orders\OrderFilterCostStatusTypeEnum::class,
    App\GraphQL\Types\Enums\Orders\OrderFilterTrkNumberExistsTypeEnum::class,

    App\GraphQL\InputTypes\Orders\OrderPartsInput::class,
    App\GraphQL\InputTypes\Orders\OrderShippingInput::class,
    App\GraphQL\InputTypes\Orders\OrderInput::class,

    App\GraphQL\Types\Orders\Deliveries\OrderDeliveryTypeType::class,
    App\GraphQL\Types\Orders\Deliveries\OrderDeliveryTypeTranslateType::class,

    App\GraphQL\Types\Enums\Orders\OrderSubscriptionActionTypeEnum::class,
    App\GraphQL\Types\Orders\OrderSubscriptionType::class,

    App\GraphQL\Types\Orders\OrderPaymentType::class,
    App\GraphQL\Types\Orders\OrderShippingTrkNumberType::class,
    App\GraphQL\Types\Orders\OrderShippingType::class,
    App\GraphQL\Types\Orders\OrderPartType::class,
    App\GraphQL\Types\Orders\OrderType::class,
    App\GraphQL\Types\Orders\OrderStatusHistoryType::class,
    App\GraphQL\Types\Orders\OrderTotalType::class,
    App\GraphQL\Types\Orders\OrderCounterType::class,

    App\GraphQL\InputTypes\Orders\BackOffice\OrderPaymentBackOfficeInput::class,
    App\GraphQL\InputTypes\Orders\BackOffice\OrderPartsBackOfficeInput::class,
    App\GraphQL\InputTypes\Orders\BackOffice\OrderShippingBackOfficeInput::class,
    App\GraphQL\InputTypes\Orders\BackOffice\OrderBackOfficeInput::class,

    App\GraphQL\Types\Enums\Payments\PaymentReturnPlatformTypeEnum::class,
    App\GraphQL\Types\Orders\OrderPaymentCheckoutUrlType::class,

    App\GraphQL\Types\SupportRequests\Subjects\SupportRequestSubjectTranslateType::class,
    App\GraphQL\Types\SupportRequests\Subjects\SupportRequestSubjectType::class,

    App\GraphQL\InputTypes\SupportRequests\SupportRequestMessageInput::class,
    App\GraphQL\InputTypes\SupportRequests\SupportRequestInput::class,

    App\GraphQL\Types\Enums\SupportRequests\SupportRequestSubscriptionActionTypeEnum::class,
    App\GraphQL\Types\SupportRequests\SupportRequestSubscriptionType::class,

    App\GraphQL\Types\SupportRequests\SupportRequestMessageType::class,
    App\GraphQL\Types\SupportRequests\SupportRequestType::class,
    App\GraphQL\Types\SupportRequests\SupportRequestCounterType::class,

    App\GraphQL\Types\Enums\Alerts\AlertObjectTypeEnum::class,
    App\GraphQL\Types\Enums\Alerts\AlertMemberTypeEnum::class,
    App\GraphQL\Types\Enums\Alerts\AlertAdminTypeEnum::class,
    App\GraphQL\Types\Alerts\AlertObjectType::class,
    App\GraphQL\Types\Alerts\AlertAdminType::class,
    App\GraphQL\Types\Alerts\AlertMemberType::class,
    App\GraphQL\Types\Alerts\AlertCounterType::class,
    App\GraphQL\Types\Alerts\AlertSubscriptionType::class,
    App\GraphQL\InputTypes\Alert\AlertRecipientInputType::class,
    App\GraphQL\InputTypes\Alert\AlertSendInputType::class,

    // Utilities ----------------------------------------------------------------
    App\GraphQL\Types\Enums\LanguageTypeEnum::class,
    App\GraphQL\InputTypes\Utilities\Upload\MultiLangFileType::class,
    App\GraphQL\InputTypes\Utilities\Upload\UploadMultiLangType::class,

    App\GraphQL\InputTypes\Utilities\Address\AddressInput::class,
    App\GraphQL\InputTypes\Utilities\Morph\MorphInput::class,
    // --------------------------------------------------------------------------

    App\GraphQL\InputTypes\About\Pages\PageTranslationInput::class,
    App\GraphQL\InputTypes\About\Pages\PageInput::class,

    App\GraphQL\Types\About\Pages\PageTranslationType::class,
    App\GraphQL\Types\About\Pages\PageType::class,

    App\GraphQL\Types\GlobalSettings\GlobalSettingType::class,
    App\GraphQL\InputTypes\GlobalSettings\GlobalSettingUpdateInput::class,

    App\GraphQL\Types\Chat\ChatInTabCounterType::class,

    App\GraphQL\Types\Enums\Chat\ChatMenuActionEnumType::class,
    App\GraphQL\Types\Enums\Chat\ChatMenuActionRedirectEnumType::class,

    App\GraphQL\InputTypes\Chat\ChatMenuTranslationInputType::class,
    App\GraphQL\InputTypes\Chat\ChatMenuInputType::class,

    App\GraphQL\Types\Chat\ChatMenuTranslationType::class,
    App\GraphQL\Types\Chat\ChatMenuType::class,

    App\GraphQL\Types\Enums\Utils\Versioning\VersionStatusEnumType::class,

    App\GraphQL\Types\Utilities\AppVersionType::class,

    ...config('chat.graphql.types'),
];
