<?php

namespace App\Dto\Companies;

use App\Dto\Utilities\Address\AddressDto;

use App\Enums\Companies\CompanyType;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CompanyDto
{
    public CompanyType $type;
    public string $businessName;
    public Email $email;
    public Phone $phone;
    public ?Phone $fax;
    public string $taxpayerID;
    public ?string $tax;
    public array $websites;
    public array $marketplaces;
    public array $tradeNames;
    public ?string $corporationID = null;

    public AddressDto $address;

    /** @var array<UploadedFile> */
    public array $media = [];

    /** @var array<ShippingAddressDto> */
    public array $shippingAddresses = [];

    public ContactDto $contactAccount;
    public ContactDto $contactOrder;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->type = CompanyType::fromValue(data_get($args, 'company_info.type'));
        $dto->businessName = data_get($args, 'company_info.business_name');
        $dto->email = new Email(data_get($args, 'company_info.email'));
        $dto->phone = new Phone(data_get($args, 'company_info.phone'));

        $dto->fax = empty(data_get($args, 'company_info.fax'))
            ? null
            : new Phone(data_get($args, 'company_info.fax'));

        $dto->taxpayerID = data_get($args, 'company_info.taxpayer_id');
        $dto->tax = data_get($args, 'company_info.tax');
        $dto->websites = data_get($args, 'company_info.websites', []);
        $dto->marketplaces = data_get($args, 'company_info.marketplaces', []);
        $dto->tradeNames = data_get($args, 'company_info.trade_names', []);
        $dto->corporationID = data_get($args, 'company_info.corporation_id');

        $dto->address = AddressDto::byArgs([
            'country_code' => data_get($args, 'company_info.country_code'),
            'state_id' => data_get($args, 'company_info.state_id'),
            'city' => data_get($args, 'company_info.city'),
            'address_line_1' => data_get($args, 'company_info.address_line_1'),
            'address_line_2' => data_get($args, 'company_info.address_line_2'),
            'zip' => data_get($args, 'company_info.zip'),
            'po_box' => data_get($args, 'company_info.po_box'),
        ]);

        $dto->media = data_get($args, 'media', []);

        foreach (data_get($args, 'shipping_address', []) as $item){
            $dto->shippingAddresses[] = ShippingAddressDto::byArgs($item);
        }

        if($contactAccount = data_get($args, 'contact_account')){
            $dto->contactAccount = ContactDto::byArgs($contactAccount);
        }
        if($contactOrder = data_get($args, 'contact_order')){
            $dto->contactOrder = ContactDto::byArgs($contactOrder);
        }

        return $dto;
    }
}
