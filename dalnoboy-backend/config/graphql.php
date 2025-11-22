<?php

declare(strict_types=1);

use App\Exceptions\ErrorRendering;
use App\GraphQL\Types\Wrappers\PaginationType;
use Core\Http\Controllers\GraphQLController;
use Rebing\GraphQL\GraphQL;

return [

    // The prefix for routes
    'prefix' => 'graphql',

    'admin_prefix' => 'graphql/BackOffice',

    // The routes to make GraphQL request. Either a string that will apply
    // to both query and mutation or an array containing the key 'query' and/or
    // 'mutation' with the according Route
    //
    // Example:
    //
    // Same route for both query and mutation
    //
    // 'routes' => 'path/to/query/{graphql_schema?}',
    //
    // or define each route
    //
    // 'routes' => [
    //     'query' => 'query/{graphql_schema?}',
    //     'mutation' => 'mutation/{graphql_schema?}',
    // ]
    //
    'routes' => '{graphql_schema?}',

    // The controller to use in GraphQL request. Either a string that will apply
    // to both query and mutation or an array containing the key 'query' and/or
    // 'mutation' with the according Controller and method
    //
    // Example:
    //
    // 'controllers' => [
    //     'query' => '\Rebing\GraphQL\GraphQLController@query',
    //     'mutation' => '\Rebing\GraphQL\GraphQLController@mutation'
    // ]
    //
    'controllers' => GraphQLController::class.'@query',

    'middleware' => [
        'throttle:api',
        App\Http\Middleware\SetAcceptJson::class,
        App\GraphQL\Middlewares\Localization\SystemLangSetterMiddleware::class,
    ],

    // Additional route group attributes
    //
    // Example:
    //
    // 'route_group_attributes' => ['guard' => 'api']
    //
    'route_group_attributes' => [],

    // The name of the default schema used when no argument is provided
    // to GraphQL::schema() or when the route is used without the graphql_schema
    // parameter.
    'default_schema' => 'default',

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
    //
    'schemas' => [
        'default' => [
            'query' => [
                App\GraphQL\Queries\Common\Localization\LanguagesQuery::class,
                App\GraphQL\Queries\FrontOffice\Localization\TranslatesListQuery::class,

                App\GraphQL\Queries\FrontOffice\Users\UserQuery::class,

                App\GraphQL\Queries\FrontOffice\Locations\RegionsQuery::class,

                App\GraphQL\Queries\FrontOffice\Clients\ClientsQuery::class,

                App\GraphQL\Queries\FrontOffice\Drivers\DriversQuery::class,

                App\GraphQL\Queries\FrontOffice\Vehicles\Schemas\SchemasVehicleQuery::class,
                App\GraphQL\Queries\FrontOffice\Vehicles\VehiclesQuery::class,

                App\GraphQL\Queries\FrontOffice\Dictionaries\VehicleClassesQuery::class,
                App\GraphQL\Queries\FrontOffice\Dictionaries\VehicleTypesQuery::class,
                App\GraphQL\Queries\FrontOffice\Dictionaries\VehicleMakesQuery::class,
                App\GraphQL\Queries\FrontOffice\Dictionaries\VehicleModelsQuery::class,

                App\GraphQL\Queries\FrontOffice\Dictionaries\ProblemsQuery::class,
                App\GraphQL\Queries\FrontOffice\Dictionaries\RegulationsQuery::class,
                App\GraphQL\Queries\FrontOffice\Dictionaries\RecommendationsQuery::class,

                App\GraphQL\Queries\FrontOffice\Dictionaries\TireMakesQuery::class,
                App\GraphQL\Queries\FrontOffice\Dictionaries\TireModelsQuery::class,
                App\GraphQL\Queries\FrontOffice\Dictionaries\TireTypesQuery::class,
                App\GraphQL\Queries\FrontOffice\Dictionaries\TireWidthQuery::class,
                App\GraphQL\Queries\FrontOffice\Dictionaries\TireHeightsQuery::class,
                App\GraphQL\Queries\FrontOffice\Dictionaries\TireDiametersQuery::class,
                App\GraphQL\Queries\FrontOffice\Dictionaries\TireSizesQuery::class,
                App\GraphQL\Queries\FrontOffice\Dictionaries\TireSpecificationsQuery::class,
                App\GraphQL\Queries\FrontOffice\Dictionaries\TireRelationshipTypesQuery::class,
                App\GraphQL\Queries\FrontOffice\Tires\TiresQuery::class,

                App\GraphQL\Queries\FrontOffice\Dictionaries\InspectionReasonsQuery::class,

                App\GraphQL\Queries\FrontOffice\Settings\SettingsQuery::class,

                App\GraphQL\Queries\FrontOffice\Dictionaries\DictionariesQuery::class,

                App\GraphQL\Queries\FrontOffice\Branches\BranchesQuery::class,
                App\GraphQL\Queries\FrontOffice\Branches\BranchesListQuery::class,

                App\GraphQL\Queries\FrontOffice\Dictionaries\TireChangesReasonsQuery::class,

                App\GraphQL\Queries\FrontOffice\Inspections\InspectionsQuery::class,
            ],
            'mutation' => [
                App\GraphQL\Mutations\FrontOffice\Localization\SetLanguageMutation::class,

                App\GraphQL\Mutations\FrontOffice\Users\UserLoginMutation::class,
                App\GraphQL\Mutations\FrontOffice\Users\UserTokenRefreshMutation::class,
                App\GraphQL\Mutations\FrontOffice\Users\UserLogoutMutation::class,

                App\GraphQL\Mutations\FrontOffice\Vehicles\VehicleCreateMutation::class,
                App\GraphQL\Mutations\FrontOffice\Vehicles\VehicleUpdateMutation::class,

                App\GraphQL\Mutations\FrontOffice\Users\UserSettingsUpdateMutation::class,
                App\GraphQL\Mutations\FrontOffice\Users\UserAvatarUploadMutation::class,
                App\GraphQL\Mutations\FrontOffice\Users\UserAvatarDeleteMutation::class,

                App\GraphQL\Mutations\FrontOffice\Drivers\DriverCreateMutation::class,
                App\GraphQL\Mutations\FrontOffice\Drivers\DriverUpdateMutation::class,

                App\GraphQL\Mutations\FrontOffice\Inspections\InspectionCreateMutation::class,
                App\GraphQL\Mutations\FrontOffice\Inspections\InspectionUpdateMutation::class,
                App\GraphQL\Mutations\FrontOffice\Inspections\InspectionLinkedMutation::class,
                App\GraphQL\Mutations\FrontOffice\Inspections\InspectionUnLinkedMutation::class,
                App\GraphQL\Mutations\FrontOffice\Inspections\InspectionTirePhotoUploadMutation::class,
                App\GraphQL\Mutations\FrontOffice\Inspections\TestTireUploadMutation::class,

                App\GraphQL\Mutations\FrontOffice\Clients\ClientCreateMutation::class,
                App\GraphQL\Mutations\FrontOffice\Clients\ClientUpdateMutation::class,

                App\GraphQL\Mutations\FrontOffice\Dictionaries\TireMakes\TireMakeCreateMutation::class,
                App\GraphQL\Mutations\FrontOffice\Dictionaries\TireModels\TireModelCreateMutation::class,
                App\GraphQL\Mutations\FrontOffice\Dictionaries\TireSizes\TireSizeCreateMutation::class,
                App\GraphQL\Mutations\FrontOffice\Dictionaries\TireSpecifications\TireSpecificationCreateMutation::class,
                App\GraphQL\Mutations\FrontOffice\Dictionaries\VehicleMakes\VehicleMakeCreateMutation::class,
                App\GraphQL\Mutations\FrontOffice\Dictionaries\VehicleModels\VehicleModelCreateMutation::class,

                App\GraphQL\Mutations\FrontOffice\Tires\TireCreateMutation::class,
                App\GraphQL\Mutations\FrontOffice\Tires\TireUpdateMutation::class,
            ],
            'subscription' => [],
            'middleware' => [],
            'method' => ['post'],
        ],
        'BackOffice' => [
            'query' => [
                App\GraphQL\Queries\Common\Localization\LanguagesQuery::class,
                App\GraphQL\Queries\BackOffice\Localization\TranslatesQuery::class,
                App\GraphQL\Queries\BackOffice\Localization\TranslatesListQuery::class,

                App\GraphQL\Queries\BackOffice\Admins\AdminsQuery::class,
                App\GraphQL\Queries\BackOffice\Admins\AdminQuery::class,

                App\GraphQL\Queries\BackOffice\Permissions\AdminRolesQuery::class,
                App\GraphQL\Queries\BackOffice\Permissions\UserRolesQuery::class,
                App\GraphQL\Queries\BackOffice\Permissions\AvailableAdminGrantsQuery::class,
                App\GraphQL\Queries\BackOffice\Permissions\AvailableUserGrantsQuery::class,

                App\GraphQL\Queries\BackOffice\Branches\BranchesQuery::class,
                App\GraphQL\Queries\BackOffice\Branches\BranchesListQuery::class,
                App\GraphQL\Queries\BackOffice\Branches\BranchesExportQuery::class,
                App\GraphQL\Queries\BackOffice\Branches\BranchesImportExampleQuery::class,

                App\GraphQL\Queries\BackOffice\Locations\RegionsQuery::class,

                App\GraphQL\Queries\BackOffice\Users\UsersQuery::class,
                App\GraphQL\Queries\BackOffice\Users\UsersExportQuery::class,
                App\GraphQL\Queries\BackOffice\Users\UsersImportExampleQuery::class,

                App\GraphQL\Queries\BackOffice\Managers\ManagersQuery::class,

                App\GraphQL\Queries\BackOffice\Clients\ClientsQuery::class,

                App\GraphQL\Queries\BackOffice\Drivers\DriversQuery::class,

                App\GraphQL\Queries\BackOffice\Vehicles\Schemas\SchemaVehicleDefaultQuery::class,
                App\GraphQL\Queries\BackOffice\Vehicles\Schemas\SchemasVehicleQuery::class,
                App\GraphQL\Queries\BackOffice\Vehicles\VehiclesQuery::class,

                App\GraphQL\Queries\BackOffice\Dictionaries\VehicleClassesQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\VehicleTypesQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\VehicleMakesQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\VehicleModelsQuery::class,

                App\GraphQL\Queries\BackOffice\Dictionaries\ProblemsQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\RegulationsQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\RecommendationsQuery::class,

                App\GraphQL\Queries\BackOffice\Dictionaries\TireMakesQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\TireModelsQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\TireTypesQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\TireWidthQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\TireHeightsQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\TireDiametersQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\TireSizesQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\TireSpecificationsQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\TireRelationshipTypesQuery::class,
                App\GraphQL\Queries\BackOffice\Dictionaries\DictionariesQuery::class,

                App\GraphQL\Queries\BackOffice\Tires\TiresQuery::class,

                App\GraphQL\Queries\BackOffice\Dictionaries\InspectionReasonsQuery::class,

                App\GraphQL\Queries\BackOffice\Settings\SettingsQuery::class,

                App\GraphQL\Queries\BackOffice\Inspections\InspectionsQuery::class,
            ],
            'mutation' => [
                App\GraphQL\Mutations\BackOffice\Localization\SetLanguageMutation::class,

                App\GraphQL\Mutations\BackOffice\Admins\AdminLoginMutation::class,
                App\GraphQL\Mutations\BackOffice\Admins\AdminTokenRefreshMutation::class,
                App\GraphQL\Mutations\BackOffice\Admins\AdminLogoutMutation::class,
                App\GraphQL\Mutations\BackOffice\Admins\AdminCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Admins\AdminUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Admins\AdminDeleteMutation::class,

                App\GraphQL\Mutations\BackOffice\Permission\AdminRoleCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Permission\AdminRoleUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Permission\AdminRoleDeleteMutation::class,

                App\GraphQL\Mutations\BackOffice\Localization\TranslateCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Localization\TranslateUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Localization\TranslateDeleteMutation::class,

                App\GraphQL\Mutations\BackOffice\Users\UserCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Users\UserUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Users\UserRegeneratePasswordMutation::class,
                App\GraphQL\Mutations\BackOffice\Users\UserDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Users\UsersImportMutation::class,

                App\GraphQL\Mutations\BackOffice\Branches\BranchCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Branches\BranchUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Branches\BranchDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Branches\BranchToggleActiveMutation::class,
                App\GraphQL\Mutations\BackOffice\Branches\BranchesImportMutation::class,

                App\GraphQL\Mutations\BackOffice\Managers\ManagerCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Managers\ManagerUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Managers\ManagerDeleteMutation::class,

                App\GraphQL\Mutations\BackOffice\Clients\ClientCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Clients\ClientUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Clients\ClientBanMutation::class,
                App\GraphQL\Mutations\BackOffice\Clients\ClientDeleteMutation::class,

                App\GraphQL\Mutations\BackOffice\Drivers\DriverCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Drivers\DriverUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Drivers\DriverDeleteMutation::class,

                App\GraphQL\Mutations\BackOffice\Vehicles\Schemas\SchemaVehicleCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Vehicles\Schemas\SchemaVehicleUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Vehicles\Schemas\SchemaVehicleDeleteMutation::class,

                App\GraphQL\Mutations\BackOffice\Vehicles\VehicleCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Vehicles\VehicleUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Vehicles\VehicleDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Vehicles\VehicleToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleClasses\VehicleClassCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleClasses\VehicleClassUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleClasses\VehicleClassDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleClasses\VehicleClassToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleTypes\VehicleTypeCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleTypes\VehicleTypeUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleTypes\VehicleTypeDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleTypes\VehicleTypeToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleMakes\VehicleMakeCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleMakes\VehicleMakeUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleMakes\VehicleMakeDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleMakes\VehicleMakeToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleModels\VehicleModelCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleModels\VehicleModelUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleModels\VehicleModelDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\VehicleModels\VehicleModelToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\Problems\ProblemCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\Problems\ProblemUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\Problems\ProblemDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\Problems\ProblemToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\Regulations\RegulationCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\Regulations\RegulationUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\Regulations\RegulationDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\Regulations\RegulationToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\Recommendations\RecommendationCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\Recommendations\RecommendationUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\Recommendations\RecommendationDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\Recommendations\RecommendationToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\TireMakes\TireMakeCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireMakes\TireMakeUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireMakes\TireMakeDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireMakes\TireMakeToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\TireModels\TireModelCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireModels\TireModelUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireModels\TireModelDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireModels\TireModelToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\TireTypes\TireTypeCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireTypes\TireTypeUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireTypes\TireTypeDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireTypes\TireTypeToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\TireHeights\TireHeightCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireHeights\TireHeightUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireHeights\TireHeightDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireHeights\TireHeightToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\TireDiameters\TireDiameterCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireDiameters\TireDiameterUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireDiameters\TireDiameterDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireDiameters\TireDiameterToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\TireWidths\TireWidthCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireWidths\TireWidthUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireWidths\TireWidthDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireWidths\TireWidthToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\TireSizes\TireSizeCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireSizes\TireSizeUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireSizes\TireSizeDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireSizes\TireSizeToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\TireSpecifications\TireSpecificationCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireSpecifications\TireSpecificationUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireSpecifications\TireSpecificationDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireSpecifications\TireSpecificationToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\TireRelationshipTypes\TireRelationshipTypeCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireRelationshipTypes\TireRelationshipTypeUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireRelationshipTypes\TireRelationshipTypeDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\TireRelationshipTypes\TireRelationshipTypeToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Tires\TireCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Tires\TireUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Tires\TireDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Tires\TireToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Dictionaries\InspectionReasons\InspectionReasonCreateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\InspectionReasons\InspectionReasonUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\InspectionReasons\InspectionReasonDeleteMutation::class,
                App\GraphQL\Mutations\BackOffice\Dictionaries\InspectionReasons\InspectionReasonToggleActiveMutation::class,

                App\GraphQL\Mutations\BackOffice\Settings\SettingsUpdateMutation::class,

                App\GraphQL\Mutations\BackOffice\Admins\AdminRecoverPasswordMutation::class,
                App\GraphQL\Mutations\BackOffice\Admins\AdminSetPasswordMutation::class,

                App\GraphQL\Mutations\BackOffice\Inspections\InspectionUpdateMutation::class,
                App\GraphQL\Mutations\BackOffice\Inspections\InspectionLinkedMutation::class,
                App\GraphQL\Mutations\BackOffice\Inspections\InspectionUnLinkedMutation::class,
                App\GraphQL\Mutations\BackOffice\Inspections\InspectionTireUpdateMutation::class,

            ],
            'subscription' => [],
            'middleware' => [
            ],
            'method' => ['post'],
        ],
    ],

    // The types available in the application. You can then access it from the
    // facade like this: GraphQL::type('user')
    //
    // Example:
    //
    // 'types' => [
    //     'user' => 'App\GraphQL\Type\UserType'
    // ]
    //
    'types' => [
        /**ENUM TYPE*/
        App\GraphQL\Types\Enums\Localization\LanguageEnumType::class,
        App\GraphQL\Types\Enums\Messages\MessageKindEnumType::class,
        App\GraphQL\Types\Enums\Messages\AlertTargetEnumType::class,
        App\GraphQL\Types\Enums\Clients\BanReasonEnumType::class,
        App\GraphQL\Types\Enums\Vehicles\VehicleFormEnumType::class,
        App\GraphQL\Types\Enums\Users\AuthorizationExpirationPeriodEnumType::class,
        App\GraphQL\Types\Enums\Dictionaries\DictionaryEnumType::class,
        App\GraphQL\Types\Enums\Inspections\InspectionModerationFieldEnumType::class,
        App\GraphQL\Types\Enums\Inspections\InspectionModerationEntityEnumType::class,
        App\GraphQL\Types\Enums\Inspections\TirePhotoTypeEnumType::class,

        /**INPUT TYPES*/
        App\GraphQL\InputTypes\PhoneInputType::class,
        App\GraphQL\InputTypes\Localization\TranslateInputType::class,
        App\GraphQL\InputTypes\Admins\AdminInputType::class,
        App\GraphQL\InputTypes\Branches\BranchInputType::class,
        App\GraphQL\InputTypes\Users\UserInputType::class,
        App\GraphQL\InputTypes\Managers\ManagerInputType::class,
        App\GraphQL\InputTypes\Clients\ClientInputType::class,
        App\GraphQL\InputTypes\Clients\ClientBanInputType::class,
        App\GraphQL\InputTypes\Drivers\DriverInputType::class,
        App\GraphQL\InputTypes\Vehicles\Schemas\SchemaVehicleInputType::class,
        App\GraphQL\InputTypes\Vehicles\VehicleInputType::class,
        App\GraphQL\InputTypes\Dictionaries\VehicleClassInputType::class,
        App\GraphQL\InputTypes\Dictionaries\VehicleClassTranslateInputType::class,
        App\GraphQL\InputTypes\Dictionaries\VehicleTypeInputType::class,
        App\GraphQL\InputTypes\Dictionaries\VehicleTypeTranslateInputType::class,
        App\GraphQL\InputTypes\Dictionaries\VehicleMakeInputType::class,
        App\GraphQL\InputTypes\Dictionaries\VehicleModelInputType::class,

        App\GraphQL\InputTypes\Dictionaries\TireMakeInputType::class,
        App\GraphQL\InputTypes\Dictionaries\TireModelInputType::class,
        App\GraphQL\InputTypes\Dictionaries\TireTypeInputType::class,
        App\GraphQL\InputTypes\Dictionaries\TireTypeTranslateInputType::class,
        App\GraphQL\InputTypes\Dictionaries\TireHeightInputType::class,
        App\GraphQL\InputTypes\Dictionaries\TireDiameterInputType::class,
        App\GraphQL\InputTypes\Dictionaries\TireWidthInputType::class,
        App\GraphQL\InputTypes\Dictionaries\TireSizeInputType::class,
        App\GraphQL\InputTypes\Dictionaries\TireSpecificationInputType::class,
        App\GraphQL\InputTypes\Dictionaries\TireRelationshipTypeInputType::class,
        App\GraphQL\InputTypes\Dictionaries\TireRelationshipTypeTranslateInputType::class,
        App\GraphQL\InputTypes\Tires\TireInputType::class,

        App\GraphQL\InputTypes\Dictionaries\ProblemInputType::class,
        App\GraphQL\InputTypes\Dictionaries\ProblemTranslateInputType::class,
        App\GraphQL\InputTypes\Dictionaries\RegulationInputType::class,
        App\GraphQL\InputTypes\Dictionaries\RegulationTranslateInputType::class,
        App\GraphQL\InputTypes\Dictionaries\RecommendationInputType::class,
        App\GraphQL\InputTypes\Dictionaries\RecommendationTranslateInputType::class,

        App\GraphQL\InputTypes\Dictionaries\InspectionReasonInputType::class,
        App\GraphQL\InputTypes\Dictionaries\InspectionReasonTranslateInputType::class,

        App\GraphQL\InputTypes\Settings\SettingsInputType::class,

        App\GraphQL\InputTypes\Users\UserSettingsInputType::class,

        App\GraphQL\InputTypes\Inspection\InspectionPhotosInputType::class,
        App\GraphQL\InputTypes\Inspection\InspectionRecommendationInputType::class,
        App\GraphQL\InputTypes\Inspection\InspectionTireInputType::class,
        App\GraphQL\InputTypes\Inspection\InspectionTirePhotosInputType::class,
        App\GraphQL\InputTypes\Inspection\InspectionTirePhotosInputAsFileType::class,
        App\GraphQL\InputTypes\Inspection\InspectionInputType::class,
        App\GraphQL\InputTypes\Inspection\InspectionLinkedInputType::class,
        App\GraphQL\InputTypes\Inspection\InspectionUnLinkedInputType::class,

        /**OUTPUT TYPES*/
        App\GraphQL\Types\UploadType::class,
        App\GraphQL\Types\DownloadType::class,
        App\GraphQL\Types\PhoneType::class,

        App\GraphQL\Types\Media\MediaConversionType::class,
        App\GraphQL\Types\Media\MediaType::class,

        App\GraphQL\Types\Localization\LanguageType::class,
        App\GraphQL\Types\Localization\TranslateType::class,

        App\GraphQL\Types\Roles\RoleType::class,
        App\GraphQL\Types\Roles\RoleTranslateType::class,
        App\GraphQL\Types\Roles\RoleTranslateInputType::class,
        App\GraphQL\Types\Roles\GrantType::class,
        App\GraphQL\Types\Roles\GrantGroupType::class,

        App\GraphQL\Types\Users\UserLoginType::class,
        App\GraphQL\Types\Users\UserType::class,

        App\GraphQL\Types\Admins\AdminLoginType::class,
        App\GraphQL\Types\Admins\AdminType::class,

        App\GraphQL\Types\Managers\ManagerType::class,

        App\GraphQL\Types\Clients\ClientType::class,
        App\GraphQL\Types\Clients\ClientBanType::class,

        App\GraphQL\Types\Vehicles\Schemas\SchemaWheelType::class,
        App\GraphQL\Types\Vehicles\Schemas\SchemaAxleType::class,
        App\GraphQL\Types\Vehicles\Schemas\SchemaVehicleType::class,

        App\GraphQL\Types\Vehicles\VehicleType::class,

        App\GraphQL\Types\Drivers\DriverType::class,

        App\GraphQL\Types\Locations\RegionTranslateType::class,
        App\GraphQL\Types\Locations\RegionType::class,

        App\GraphQL\Types\Branches\BranchType::class,

        App\GraphQL\Types\Unions\Authenticatable::class,

        App\GraphQL\Types\Security\IpAccessType::class,

        App\GraphQL\Types\Dictionaries\VehicleClassTranslateType::class,
        App\GraphQL\Types\Dictionaries\VehicleClassType::class,

        App\GraphQL\Types\Dictionaries\VehicleTypeTranslateType::class,
        App\GraphQL\Types\Dictionaries\VehicleTypeType::class,

        App\GraphQL\Types\Dictionaries\VehicleMakeType::class,

        App\GraphQL\Types\Dictionaries\VehicleModelType::class,

        App\GraphQL\Types\Dictionaries\ProblemTranslateType::class,
        App\GraphQL\Types\Dictionaries\ProblemType::class,

        App\GraphQL\Types\Dictionaries\RegulationTranslateType::class,
        App\GraphQL\Types\Dictionaries\RegulationType::class,

        App\GraphQL\Types\Dictionaries\RecommendationTranslateType::class,
        App\GraphQL\Types\Dictionaries\RecommendationType::class,

        App\GraphQL\Types\Dictionaries\TireMakeType::class,

        App\GraphQL\Types\Dictionaries\TireModelType::class,

        App\GraphQL\Types\Dictionaries\TireTypeTranslateType::class,
        App\GraphQL\Types\Dictionaries\TireTypeType::class,

        App\GraphQL\Types\Dictionaries\TireWidthType::class,
        App\GraphQL\Types\Dictionaries\TireHeightType::class,
        App\GraphQL\Types\Dictionaries\TireDiameterType::class,
        App\GraphQL\Types\Dictionaries\TireSizeType::class,
        App\GraphQL\Types\Dictionaries\TireSpecificationType::class,

        App\GraphQL\Types\Dictionaries\TireRelationshipTypeTranslateType::class,
        App\GraphQL\Types\Dictionaries\TireRelationshipTypeType::class,

        App\GraphQL\Types\Tires\TireType::class,

        App\GraphQL\Types\Dictionaries\InspectionReasonTranslateType::class,
        App\GraphQL\Types\Dictionaries\InspectionReasonType::class,

        App\GraphQL\Types\Settings\SettingsType::class,

        App\GraphQL\Types\Dictionaries\DictionaryType::class,

        App\GraphQL\Types\Dictionaries\TireChangesReasonType::class,
        App\GraphQL\Types\Dictionaries\TireChangesReasonTranslateType::class,

        App\GraphQL\Types\Inspections\InspectionPhotosType::class,
        App\GraphQL\Types\Inspections\InspectionRecommendationType::class,
        App\GraphQL\Types\Inspections\InspectionTireType::class,
        App\GraphQL\Types\Inspections\InspectionTirePhotosType::class,
        App\GraphQL\Types\Inspections\InspectionModerationFieldType::class,
        App\GraphQL\Types\Inspections\InspectionType::class,

    ],

    // The types will be loaded on demand. Default is to load all types on each request
    // Can increase performance on schemes with many types
    // Presupposes the config type key to match the type class name property
    'lazyload_types' => false,

    'schema_cache' => env('GRAPHQL_SCHEMA_CACHE', false),

    // This callable will be passed the Error object for each errors GraphQL catch.
    // The method should return an array representing the error.
    // Typically:
    // [
    //     'message' => '',
    //     'locations' => []
    // ]
    'error_formatter' => [ErrorRendering::class, 'formatError'],

    /*
     * Custom Error Handling
     *
     * Expected handler signature is: function (array $errors, callable $formatter): array
     *
     * The default handler will pass exceptions to laravel Error Handling mechanism
     */
    'errors_handler' => [GraphQL::class, 'handleErrors'],

    // You can set the key, which will be used to retrieve the dynamic variables
    'params_key' => 'variables',

    /*
     * Options to limit the query complexity and depth. See the doc
     * @ https://webonyx.github.io/graphql-php/security
     * for details. Disabled by default.
     */
    'security' => [
        'query_max_complexity' => null,
        'query_max_depth' => null,
        'disable_introspection' => false,
    ],

    /*
     * You can define your own pagination type.
     * Reference \Rebing\GraphQL\Support\PaginationType::class
     */
    //'pagination_type' => Rebing\GraphQL\Support\PaginationType::class,
    'pagination_type' => PaginationType::class,

    /*
     * Config for GraphiQL (see (https://github.com/graphql/graphiql).
     */
    'graphiql' => [
        'prefix' => '/graphiql',
        'controller' => GraphQLController::class.'@graphiql',
        'middleware' => [],
        'view' => 'graphql::graphiql',
        'display' => env('ENABLE_GRAPHIQL', true),
    ],

    /*
     * Overrides the default field resolver
     * See http://webonyx.github.io/graphql-php/data-fetching/#default-field-resolver
     *
     * Example:
     *
     * ```php
     * 'defaultFieldResolver' => function ($root, $args, $context, $info) {
     * },
     * ```
     * or
     * ```php
     * 'defaultFieldResolver' => [SomeKlass::class, 'someMethod'],
     * ```
     */
    'defaultFieldResolver' => null,

    /*
     * Any headers that will be added to the response returned by the default controller
     */
    'headers' => [],

    /*
     * Any JSON encoding options when returning a response from the default controller
     * See http://php.net/manual/function.json-encode.php for the full list of options
     */
    'json_encoding_options' => 0,
];
