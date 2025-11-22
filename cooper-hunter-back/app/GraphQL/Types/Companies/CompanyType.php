<?php

namespace App\GraphQL\Types\Companies;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Dealers\DealerType;
use App\GraphQL\Types\Enums\Companies\CompanyStatusEnumType;
use App\GraphQL\Types\Enums\Companies\CompanyTypeEnumType;
use App\GraphQL\Types\Locations\CountryType;
use App\GraphQL\Types\Locations\StateType;
use App\GraphQL\Types\Media\MediaType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Payments\MemberPaymentCardType;
use App\Models\Companies\Company;
use GraphQL\Type\Definition\Type;

class CompanyType extends BaseType
{
    public const NAME = 'companyType';
    public const MODEL = Company::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'status' => [
                    'type' => CompanyStatusEnumType::nonNullType(),
                ],
                'type' => [
                    'type' => CompanyTypeEnumType::nonNullType(),
                ],
                'business_name' => [
                    'type' => NonNullType::string(),
                ],
                'terms' => [
                    'type' => Type::string(),
                    'is_relation' => false,
                    'resolve' => fn(Company $model):?string => $model->term_names
                ],
                'email' => [
                    'type' => NonNullType::string(),
                ],
                'phone' => [
                    'type' => Type::string(),
                ],
                'fax' => [
                    'type' => Type::string(),
                ],
                'corporation' => [
                    'type' => CorporationType::type(),
                    'is_relation' => true,
                ],
                'country' => [
                    'type' => CountryType::type(),
                    'is_relation' => true,
                ],
                'state' => [
                    'type' => StateType::type(),
                    'is_relation' => true,
                ],
                'city' => [
                    'type' => NonNullType::string(),
                ],
                'address_line_1' => [
                    'type' => NonNullType::string(),
                ],
                'address_line_2' => [
                    'type' => Type::string(),
                ],
                'zip' => [
                    'type' => NonNullType::string(),
                ],
                'po_box' => [
                    'type' => Type::string(),
                ],
                'taxpayer_id' => [
                    'type' => NonNullType::string(),
                ],
                'tax' => [
                    'type' => Type::string(),
                ],
                'websites' => [
                    'type' => Type::listOf(Type::string()),
                ],
                'marketplaces' => [
                    'type' => Type::listOf(Type::string()),
                ],
                'trade_names' => [
                    'type' => Type::listOf(Type::string()),
                ],
                'shipping_addresses' => [
                    'type' => ShippingAddressType::list(),
                    'alias' => 'shippingAddresses',
                    'is_relation' => true,
                ],
                'shipping_addresses_active' => [
                    'type' => ShippingAddressType::list(),
                    'alias' => 'shippingAddressesActive',
                    'is_relation' => true,
                ],
                'contact_account' => [
                    'type' => ContactType::type(),
                    'is_relation' => true,
                    'alias' => 'contactAccount',
                ],
                'contact_order' => [
                    'type' => ContactType::type(),
                    'is_relation' => true,
                    'alias' => 'contactOrder',
                ],
                'media' => [
                    'type' => MediaType::list(),
                    'always' => 'id',
                    'alias' => 'media',
                ],
                'dealers' => [
                    'type' => DealerType::list(),
                ],
                'manager' => [
                    'type' => ManagerType::type(),
                ],
                'commercial_manager' => [
                    'type' => CommercialManagerType::type(),
                    'is_relation' => true,
                    'alias' => 'commercialManager',
                ],
                'payment_cards' => [
                    'type' => MemberPaymentCardType::list(),
                    'alias' => 'cards'
                ],
            ]
        );
    }
}
