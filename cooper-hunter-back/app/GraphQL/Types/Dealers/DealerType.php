<?php

namespace App\GraphQL\Types\Dealers;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Companies\CompanyType;
use App\GraphQL\Types\Companies\ShippingAddressType;
use App\GraphQL\Types\Roles\RoleType;
use App\Models\Dealers\Dealer;
use Core\GraphQL\Fields\PermissionField;
use GraphQL\Type\Definition\Type;

class DealerType extends BaseType
{
    public const NAME = 'dealer';
    public const MODEL = Dealer::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'email' => [
                    'type' => Type::string(),
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
                'company' => [
                    'type' => CompanyType::type()
                ],
                'lang' => [
                    'type' => Type::string(),
                ],
                'is_verify_email' => [
                    'type' => Type::boolean(),
                    'is_relation' => false,
                    'selectable' => false,
                    'always' => 'email_verified_at',
                    'resolve' => fn(Dealer $model) =>  $model->isEmailVerified()
                ],
                'shipping_addresses' => [
                    'type' => ShippingAddressType::list(),
                    'alias' => 'shippingAddresses'
                ],
                'permission' => PermissionField::class,
                'roles' => [
                    'type' => Type::listOf(RoleType::type()),
                ]
            ]
        );
    }
}

