<?php

namespace App\GraphQL\Types\Dealers;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Companies\CompanyType;
use App\GraphQL\Types\Companies\ShippingAddressType;
use App\GraphQL\Types\Localization\LanguageType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Roles\PermissionType;
use App\Models\Dealers\Dealer;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;

class DealerProfileType extends BaseType
{
    public const NAME = 'DealerProfileType';
    public const MODEL = Dealer::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id()
            ],
            'email' => [
                'type' => NonNullType::string(),
            ],
            'name' => [
                'type' => Type::string(),
                'alias' => 'first_name'
            ],
            'is_main' => [
                'type' => Type::boolean(),
                'description' => 'гл. дилер в рамках корпорации (несколько компаний), устанавливается в админке'
            ],
            'is_main_company' => [
                'type' => Type::boolean(),
                'description' => 'гл. дилер в рамках компании, устанавливается автоматически, при регистрации первого дилера в компании'
            ],
            'is_verify_email' => [
                'type' => Type::boolean(),
                'is_relation' => false,
                'selectable' => false,
                'always' => 'email_verified_at',
                'resolve' => fn(Dealer $model) =>  $model->isEmailVerified()
            ],
            'company' => [
                'type' => CompanyType::type()
            ],
            'lang' => [
                'type' => Type::string(),
            ],
            'permissions' => [
                /** @see DealerProfileType::resolvePermissionsField() */
                'type' => Type::listOf(PermissionType::type()),
                'is_relation' => false,
            ],
            'shipping_addresses' => [
                'type' => ShippingAddressType::list(),
                'is_relation' => false,
                'resolve' => fn(Dealer $model) =>  $model->getShippingAddresses()
            ],
            'language' => [
                'type' => LanguageType::type(),
                'is_relation' => true,
            ],
        ];
    }

    protected function resolvePermissionsField(Dealer $root): Collection
    {
        return $root->getAllPermissions();
    }
}
