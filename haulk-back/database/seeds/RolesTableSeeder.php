<?php

use App\Models\Users\User;
use App\Services\Permissions\Groups\BodyShop\PermissionGroupCompanies;
use App\Services\Permissions\Groups\BodyShop\PermissionGroupInventories;
use App\Services\Permissions\Groups\BodyShop\PermissionGroupInventoryCategories;
use App\Services\Permissions\Groups\BodyShop\PermissionGroupInventoryUnits;
use App\Services\Permissions\Groups\BodyShop\PermissionGroupOrders as BSPermissionGroupOrders;
use App\Services\Permissions\Groups\BodyShop\PermissionGroupReports;
use App\Services\Permissions\Groups\BodyShop\PermissionGroupSettings;
use App\Services\Permissions\Groups\BodyShop\PermissionGroupSuppliers;
use App\Services\Permissions\Groups\BodyShop\PermissionGroupTypesOfWork;
use App\Services\Permissions\Groups\BodyShop\PermissionGroupUsers as BSPermissionGroupUsers;
use App\Services\Permissions\Groups\BodyShop\PermissionGroupVehicleOwners;
use App\Services\Permissions\Groups\GPS\PermissionGroupDevices;
Use App\Services\Permissions\Groups\GPS\PermissionGroupGPSSettings;
Use App\Services\Permissions\Groups\GPS\PermissionGroupGPS;
use App\Services\Permissions\Groups\GPS\PermissionsGroupGpsMenu;
use App\Services\Permissions\Groups\PermissionGroupAlerts;
use App\Services\Permissions\Groups\PermissionGroupBilling;
use App\Services\Permissions\Groups\PermissionGroupCompanyReports;
use App\Services\Permissions\Groups\PermissionGroupCompanySettings;
use App\Services\Permissions\Groups\PermissionGroupContacts;
use App\Services\Permissions\Groups\PermissionGroupDictionaries;
use App\Services\Permissions\Groups\PermissionGroupDriverTripReports;
use App\Services\Permissions\Groups\PermissionGroupFuelCard;
use App\Services\Permissions\Groups\PermissionGroupFueling;
use App\Services\Permissions\Groups\PermissionGroupHistory;
use App\Services\Permissions\Groups\PermissionGroupLibrary;
use App\Services\Permissions\Groups\PermissionGroupLocations;
use App\Services\Permissions\Groups\PermissionGroupNews;
use App\Services\Permissions\Groups\PermissionGroupOrders;
use App\Services\Permissions\Groups\PermissionGroupPaymentMethods;
use App\Services\Permissions\Groups\PermissionGroupPayrolls;
use App\Services\Permissions\Groups\PermissionGroupProfile;
use App\Services\Permissions\Groups\PermissionGroupQuestionAnswer;
use App\Services\Permissions\Groups\PermissionGroupRoles;
use App\Services\Permissions\Groups\PermissionGroupSupportRequests;
use App\Services\Permissions\Groups\PermissionGroupTags;
use App\Services\Permissions\Groups\PermissionGroupTrailers;
use App\Services\Permissions\Groups\PermissionGroupTranslations;
use App\Services\Permissions\Groups\PermissionGroupTrucks;
use App\Services\Permissions\Groups\PermissionGroupUsers;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        try {
            // Reset cached roles and permissions
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            $rolesSectionPermissions = [
                User::SUPERADMIN_ROLE =>
                    resolve(PermissionGroupRoles::class)->mapsWithout([
                        strtolower(User::BSSUPERADMIN_ROLE),
                        strtolower(User::BSADMIN_ROLE),
                        strtolower(User::BSMECHANIC_ROLE),
                    ])
                    + resolve(PermissionGroupAlerts::class)->maps()
                    + resolve(PermissionGroupDictionaries::class)->maps()
                    + resolve(PermissionGroupBilling::class)->maps()
                    + resolve(PermissionGroupCompanySettings::class)->maps()
                    + resolve(PermissionGroupProfile::class)->maps()
                    + resolve(PermissionGroupOrders::class)->maps()
                    + resolve(PermissionGroupCompanyReports::class)->maps()
                    + resolve(PermissionGroupDriverTripReports::class)->maps()
                    + resolve(PermissionGroupContacts::class)->maps()
                    + resolve(PermissionGroupLibrary::class)->maps()
                    + resolve(PermissionGroupQuestionAnswer::class)->maps()
                    + resolve(PermissionGroupNews::class)->maps()
                    + resolve(PermissionGroupTranslations::class)->maps()
                    + resolve(PermissionGroupUsers::class)->maps()
                    + resolve(PermissionGroupPaymentMethods::class)->maps()
                    + resolve(PermissionGroupLocations::class)->maps()
                    + resolve(PermissionGroupHistory::class)->maps()
                    + resolve(PermissionGroupPayrolls::class)->maps()
                    + resolve(PermissionGroupSupportRequests::class)->maps()
                    + resolve(PermissionGroupVehicleOwners::class)->maps()
                    + resolve(PermissionGroupTags::class)->maps()
                    + resolve(PermissionGroupTrucks::class)->maps()
                    + resolve(PermissionGroupTrailers::class)->maps()
                    + resolve(PermissionGroupGPSSettings::class)->maps()
                    + resolve(PermissionGroupGPS::class)->maps()
                    + resolve(PermissionsGroupGpsMenu::class)->maps()
                    + resolve(PermissionGroupDevices::class)->maps()
                    + resolve(PermissionGroupFuelCard::class)->maps()
                    + resolve(PermissionGroupFueling::class)->maps(),

                User::ADMIN_ROLE =>
                    resolve(PermissionGroupRoles::class)->mapsWithout([
                        'superadmin',
                        strtolower(User::BSSUPERADMIN_ROLE),
                        strtolower(User::BSADMIN_ROLE),
                        strtolower(User::BSMECHANIC_ROLE),
                    ])
                    + resolve(PermissionGroupAlerts::class)->maps()
                    + resolve(PermissionGroupCompanySettings::class)->mapsWithout([
                        'create',
                        'update',
                        'delete',
                    ])
                    + resolve(PermissionGroupDictionaries::class)->maps()
                    + resolve(PermissionGroupProfile::class)->maps()
                    + resolve(PermissionGroupOrders::class)->maps()
                    + resolve(PermissionGroupCompanyReports::class)->maps()
                    + resolve(PermissionGroupDriverTripReports::class)->maps()
                    + resolve(PermissionGroupContacts::class)->maps()
                    + resolve(PermissionGroupLibrary::class)->maps()
                    + resolve(PermissionGroupQuestionAnswer::class)->maps()
                    + resolve(PermissionGroupNews::class)->maps()
                    + resolve(PermissionGroupTranslations::class)->maps()
                    + resolve(PermissionGroupUsers::class)->maps()
                    + resolve(PermissionGroupLocations::class)->maps()
                    + resolve(PermissionGroupHistory::class)->maps()
                    + resolve(PermissionGroupPayrolls::class)->maps()
                    + resolve(PermissionGroupSupportRequests::class)->maps()
                    + resolve(PermissionGroupVehicleOwners::class)->maps()
                    + resolve(PermissionGroupTags::class)->maps()
                    + resolve(PermissionGroupTrucks::class)->maps()
                    + resolve(PermissionGroupTrailers::class)->maps()
                    + resolve(PermissionGroupGPSSettings::class)->maps()
                    + resolve(PermissionGroupGPS::class)->maps()
                    + resolve(PermissionsGroupGpsMenu::class)->maps()
                    + resolve(PermissionGroupDevices::class)->maps()
                    + resolve(PermissionGroupFuelCard::class)->maps()
                    + resolve(PermissionGroupFueling::class)->maps(),

                User::DISPATCHER_ROLE =>
                    resolve(PermissionGroupRoles::class)->mapsWithout([
                        'superadmin',
                        'admin',
                        'dispatcher',
                        'accountant',
                        strtolower(User::BSSUPERADMIN_ROLE),
                        strtolower(User::BSADMIN_ROLE),
                        strtolower(User::BSMECHANIC_ROLE),
                        strtolower(User::OWNER_ROLE),
                    ])
                    + resolve(PermissionGroupAlerts::class)->maps()
                    + resolve(PermissionGroupProfile::class)->maps()
                    + resolve(PermissionGroupOrders::class)->mapsWithout([
                        'delete',
                        'order-review',
                        'inspection',
                        'deduct-from-driver',
                        'restore',
                        'delete-permanently',
                        'delete-comment',
                        'payment-stage-create',
                        'payment-stage-delete',
                        'export',
                    ])
                    + resolve(PermissionGroupContacts::class)->maps()
                    + resolve(PermissionGroupLibrary::class)->maps()
                    + resolve(PermissionGroupQuestionAnswer::class)->mapsWithout([
                        'create',
                        'update',
                        'delete',
                    ])
                    + resolve(PermissionGroupNews::class)->mapsWithout([
                        'create',
                        'update',
                        'delete',
                    ])
                    + resolve(PermissionGroupUsers::class)->mapsWithout([
                        'delete-comment'
                    ])
                    + resolve(PermissionGroupSupportRequests::class)->mapsWithout(
                        [
                            'close',
                        ]
                    )
                    + resolve(PermissionGroupTags::class)->mapsWithout([
                        'create',
                        'update',
                        'delete',
                        'read',
                    ])
                    + resolve(PermissionGroupTrucks::class)->mapsWithout([
                        'create',
                        'update',
                        'delete',
                        'delete-comment',
                    ])
                    + resolve(PermissionGroupTrailers::class)->mapsWithout([
                        'create',
                        'update',
                        'delete',
                        'delete-comment',
                    ])
                    + resolve(PermissionGroupGPS::class)->maps()
                    + resolve(PermissionsGroupGpsMenu::class)->mapsWithout([
                        'device-visible',
                        'device-active',
                    ])
                    + resolve(PermissionGroupDevices::class)->mapsWithout([
                        'create',
                        'update',
                        'attach_to_vehicle',
                        'request',
                    ]),

                User::DRIVER_ROLE =>
                    resolve(PermissionGroupProfile::class)->maps()
                    + resolve(PermissionGroupOrders::class)->mapsWithout([
                        'create',
                        'order-review',
                        'update',
                        'update-own',
                        'delete',
                        'delete-own',
                        'restore',
                        'restore-own',
                        'delete-permanently',
                        'deduct-from-driver',
                        'send-invoice',
                        'send-bol',
                        'take-offer',
                        'release-offer',
                        'change-status',
                        'delete-comment',
                        'send-signature-link',
                        'payment-stage-create',
                        'payment-stage-delete',
                        'export',
                    ])
                    + resolve(PermissionGroupLibrary::class)->maps()
                    + resolve(PermissionGroupQuestionAnswer::class)->mapsWithout([
                        'create',
                        'update',
                        'delete',
                    ])
                    + resolve(PermissionGroupNews::class)->mapsWithout([
                        'create',
                        'update',
                        'delete',
                    ])
                    + resolve(PermissionGroupSupportRequests::class)->mapsWithout(
                        [
                            'reply',
                            'close',
                        ]
                    ),

                User::ACCOUNTANT_ROLE =>
                    resolve(PermissionGroupRoles::class)->mapsWithout([
                        'superadmin',
                        strtolower(User::BSSUPERADMIN_ROLE),
                        strtolower(User::BSADMIN_ROLE),
                        strtolower(User::BSMECHANIC_ROLE),
                    ])
                    + resolve(PermissionGroupAlerts::class)->maps()
                    + resolve(PermissionGroupProfile::class)->maps()
                    + resolve(PermissionGroupDictionaries::class)->maps()
                    + resolve(PermissionGroupOrders::class)->mapsWithout([
                        'create',
                        'order-review',
                        'restore',
                        'restore-own',
                        'delete',
                        'delete-own',
                        'delete-permanently',
                        'inspection',
                        'add-attachment',
                        'take-offer',
                        'release-offer',
                        'delete-comment',
                        'send-signature-link',
                    ])
                    + resolve(PermissionGroupCompanyReports::class)->maps()
                    + resolve(PermissionGroupDriverTripReports::class)->mapsWithout([
                        'delete-comment',
                    ])
                    + resolve(PermissionGroupContacts::class)->mapsWithout([
                        'create',
                        'delete',
                    ])
                    + resolve(PermissionGroupUsers::class)->mapsWithout([
                        'delete-comment'
                    ])
                    + resolve(PermissionGroupPayrolls::class)->maps()
                    + resolve(PermissionGroupNews::class)->mapsWithout(
                        [
                            'create',
                            'update',
                            'delete',
                        ]
                    )
                    + resolve(PermissionGroupSupportRequests::class)->mapsWithout(
                        [
                            'close',
                        ]
                    )
                     + resolve(PermissionGroupVehicleOwners::class)->mapsWithout(
                         [
                             'create',
                             'update',
                             'delete',
                         ]
                    )
                    + resolve(PermissionGroupTags::class)->mapsWithout([
                        'create',
                        'update',
                        'delete',
                        'read',
                    ])
                    + resolve(PermissionGroupTrucks::class)->mapsWithout([
                        'create',
                        'update',
                        'delete',
                        'delete-comment',
                    ])
                    + resolve(PermissionGroupTrailers::class)->mapsWithout([
                        'create',
                        'update',
                        'delete',
                        'delete-comment',
                    ])
                    + resolve(PermissionGroupFuelCard::class)->maps()
                    + resolve(PermissionGroupFueling::class)->maps(),
                User::BSSUPERADMIN_ROLE => resolve(PermissionGroupProfile::class)->maps()
                    + resolve(PermissionGroupVehicleOwners::class)->maps()
                    + resolve(PermissionGroupSuppliers::class)->maps()
                    + resolve(PermissionGroupInventoryCategories::class)->maps()
                    + resolve(PermissionGroupCompanies::class)->maps()
                    + resolve(PermissionGroupInventories::class)->maps()
                    + resolve(PermissionGroupTags::class)->maps()
                    + resolve(PermissionGroupTypesOfWork::class)->maps()
                    + resolve(BSPermissionGroupUsers::class)->maps()
                    + resolve(PermissionGroupTrucks::class)->maps()
                    + resolve(PermissionGroupTrailers::class)->maps()
                    + resolve(PermissionGroupRoles::class)->mapsWithout([
                        'superadmin',
                        'admin',
                        'dispatcher',
                        'accountant',
                        'driver',
                        strtolower(User::BSSUPERADMIN_ROLE),
                        strtolower(User::OWNER_ROLE),
                        strtolower(User::OWNER_DRIVER_ROLE),
                    ])
                    + resolve(BSPermissionGroupOrders::class)->maps()
                    + resolve(PermissionGroupInventoryUnits::class)->maps()
                    + resolve(PermissionGroupSettings::class)->maps()
                    + resolve(PermissionGroupReports::class)->maps(),
                User::BSADMIN_ROLE => resolve(PermissionGroupProfile::class)->maps()
                    + resolve(PermissionGroupVehicleOwners::class)->maps()
                    + resolve(PermissionGroupSuppliers::class)->maps()
                    + resolve(PermissionGroupInventoryCategories::class)->maps()
                    + resolve(PermissionGroupCompanies::class)->maps()
                    + resolve(PermissionGroupInventories::class)->maps()
                    + resolve(PermissionGroupTags::class)->maps()
                    + resolve(PermissionGroupTypesOfWork::class)->maps()
                    + resolve(PermissionGroupTrucks::class)->maps()
                    + resolve(PermissionGroupTrailers::class)->maps()
                    + resolve(BSPermissionGroupUsers::class)->maps()
                    + resolve(PermissionGroupRoles::class)->mapsWithout([
                        'superadmin',
                        'admin',
                        'dispatcher',
                        'accountant',
                        'driver',
                        strtolower(User::BSSUPERADMIN_ROLE),
                        strtolower(User::BSADMIN_ROLE),
                        strtolower(User::OWNER_ROLE),
                        strtolower(User::OWNER_DRIVER_ROLE),
                    ])
                    + resolve(BSPermissionGroupOrders::class)->maps()
                    + resolve(PermissionGroupInventoryUnits::class)->maps()
                    + resolve(PermissionGroupSettings::class)->maps()
                    + resolve(PermissionGroupReports::class)->maps(),
                User::BSMECHANIC_ROLE => resolve(PermissionGroupProfile::class)->maps(),
                User::OWNER_ROLE => resolve(PermissionGroupProfile::class)->maps(),
                User::OWNER_DRIVER_ROLE =>
                    resolve(PermissionGroupProfile::class)->maps()
                    + resolve(PermissionGroupOrders::class)->mapsWithout([
                        'create',
                        'order-review',
                        'update',
                        'update-own',
                        'delete',
                        'delete-own',
                        'restore',
                        'restore-own',
                        'delete-permanently',
                        'deduct-from-driver',
                        'send-invoice',
                        'send-bol',
                        'take-offer',
                        'release-offer',
                        'change-status',
                        'delete-comment',
                        'send-signature-link',
                        'payment-stage-create',
                        'payment-stage-delete',
                        'export',
                    ])
                    + resolve(PermissionGroupLibrary::class)->maps()
                    + resolve(PermissionGroupQuestionAnswer::class)->mapsWithout([
                        'create',
                        'update',
                        'delete',
                    ])
                    + resolve(PermissionGroupNews::class)->mapsWithout([
                        'create',
                        'update',
                        'delete',
                    ])
                    + resolve(PermissionGroupSupportRequests::class)->mapsWithout(
                        [
                            'reply',
                            'close',
                        ]
                    ),
            ];

            foreach ($rolesSectionPermissions as $role => $sectionsPermissions) {
                /** @var Role $role */
                $role = Role::query()->updateOrCreate(['guard_name' => 'api', 'name' => $role]);

                foreach ($sectionsPermissions as $section => $permissions) {
                    Permission::query()->updateOrCreate(
                        ['guard_name' => 'api', 'name' => $section]
                    );

                    $role->givePermissionTo($section);

                    foreach ($permissions as $permission) {
                        Permission::query()->updateOrCreate(
                            ['guard_name' => 'api', 'name' => $section . ' ' . $permission]
                        );

                        $role->givePermissionTo($section . ' ' . $permission);
                    }
                }
            }

            $this->removeUnusedPermissions();

        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

    private function removeUnusedPermissions()
    {
        $classes = File::allFiles(app_path('Services/Permissions/Groups'));

        $names = [];

        foreach ($classes as $class)
        {
            $className = '\App\Services\Permissions\Groups\\' . preg_replace("/\..+$/", '', $class->getRelativePathname());

            $class = new ReflectionClass($className);

            if ($class->isAbstract()) {
                continue;
            }

            $obj = resolve($className);

            $names[] = $obj->getName();
        }

        Permission::query()->where('guard_name', 'api')
            ->whereRaw("name NOT SIMILAR TO '(" . implode('|', $names) . ")%'",)
            ->delete();
    }
}
