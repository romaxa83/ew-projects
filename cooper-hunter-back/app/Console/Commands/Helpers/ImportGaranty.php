<?php

namespace App\Console\Commands\Helpers;

use App\Entities\Warranty\WarrantyProductInfo;
use App\Entities\Warranty\WarrantyUserInfo;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Warranty\WarrantyInfo\WarrantyAddress;
use App\Models\Warranty\WarrantyRegistration;
use App\Models\Warranty\WarrantyRegistrationUnitPivot;
use Illuminate\Console\Command;

class ImportGaranty extends Command
{
    protected $signature = 'helpers:import-war';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->setSerialNumber();
        $this->info('===========================================');
        $this->createWarranty();

        return self::SUCCESS;
    }

    public function setSerialNumber()
    {
        foreach (data_get($this->data(), 'serial_numbers') as $item)
        {
            $product = Product::query()->where('title', data_get($item, 'product'))->first();
            if(!$product){
                $this->warn('not found product - '.data_get($item, 'product'));
                return ;
            }

            foreach (data_get($item, 'serial_numbers') as $number){
                $serialNumber = ProductSerialNumber::query()
                    ->where('product_id', $product->id)
                    ->where('serial_number', $number)
                    ->first();

                if($serialNumber){
                    $this->warn('exist serialNumber: productId - '.$product->id.', number - ' . $number);
                } else {
                    $model = new ProductSerialNumber();
                    $model->product_id = $product->id;
                    $model->serial_number = $number;
                    $model->save();
                    $this->info('Create serial number - ' . $number);
                }
            }
        }
    }

    public function createWarranty()
    {
        $country = Country::query()->where('alias', data_get($this->data(), 'address.country'))->first();
        if(!$country){
            $this->warn('Country not found - '. data_get($this->data(), 'address.country'));
            return;
        }
        $state = State::query()->where('short_name', data_get($this->data(), 'address.state'))->first();
        if(!$state){
            $this->warn('State not found - '. data_get($this->data(), 'address.state'));
            return;
        }
        $warrantyExist = false;
        $id = data_get($this->data(), 'id');
        if($model = WarrantyRegistration::query()->where('id', $id)->first()){
            $this->warn('WarrantyRegistration exists - '.$id);
            $warrantyExist = true;
        } else {
            $model = new WarrantyRegistration();
            $this->info('Create warranty registration');
        }

        $model->id = $id;
        $model->type = data_get($this->data(), 'type');
        $model->user_info = WarrantyUserInfo::make([
            'first_name' => data_get($this->data(), 'user_info.first_name'),
            'last_name' => data_get($this->data(), 'user_info.last_name'),
            'email' => data_get($this->data(), 'user_info.email'),
            'company_name' => data_get($this->data(), 'user_info.company_name'),
            'company_address' => data_get($this->data(), 'user_info.company_address'),
        ], true);
        $model->product_info = WarrantyProductInfo::make([
            'purchase_date' => data_get($this->data(), 'product_info.purchase_date'),
            'installation_date' => data_get($this->data(), 'product_info.installation_date'),
            'installer_license_number' => data_get($this->data(), 'product_info.installer_license_number'),
            'purchase_place' => data_get($this->data(), 'product_info.purchase_place'),
        ]);
        $model->save();

        if($warrantyExist){
            $addr = $model->address;
            $this->warn('Exist warranty registration address');
        } else {
            $addr = new WarrantyAddress();
            $this->info('Create warranty registration address');
        }

        $addr->warranty_id = $model->id;
        $addr->country_id = $country->id;
        $addr->state_id = $state->id;
        $addr->city = data_get($this->data(), 'address.city');
        $addr->street = data_get($this->data(), 'address.street');
        $addr->zip = data_get($this->data(), 'address.zip');
        $addr->save();

        foreach (data_get($this->data(), 'serial_numbers') as $item)
        {
            $product = Product::query()->where('title', data_get($item, 'product'))->first();
            if(!$product){
                $this->warn('not found product - '.data_get($item, 'product'));
                return ;
            }

            foreach (data_get($item, 'serial_numbers') as $number){
                $serialNumber = WarrantyRegistrationUnitPivot::query()
                    ->where('warranty_registration_id', $model->id)
                    ->where('product_id', $product->id)
                    ->where('serial_number', $number)
                    ->first();

                if($serialNumber){
                    $this->warn('exist Pivot serialNumber: productId - '.$product->id.', number - ' . $number);
                } else {
                    $pivot = new WarrantyRegistrationUnitPivot();
                    $pivot->warranty_registration_id = $model->id;
                    $pivot->product_id = $product->id;
                    $pivot->serial_number = $number;
                    $pivot->save();
                    $this->info('Create Pivot serial number - ' . $number);
                }
            }
        }
    }

    private function data()
    {
        return [
            'id' => '5586',
            'type' => 'residential',
            'address' => [
                'country' => 'usa',
                'state' => 'FL',
                'city' => 'Sarasota',
                'street' => '4675 North Tamiami Trail, Sarasota, FL, USA',
                'zip' => '34234',
            ],
            'user_info' => [
                'email' => 'parth@shahahotels.com',
                'is_user' => true,
                'last_name' => 'shaha',
                'first_name' => 'Parth',
                'company_name' => '',
                'company_address' => '',
            ],
            'product_info' => [
                'purchase_date' => '2021-02-08',
                'purchase_place' => 'Comfort side',
                'installation_date' => '2021-05-24',
                'installer_license_number' => 'Cac1819002',
            ],
            'serial_numbers' => [
                [
                    'product' => 'CH09VCT230VI',
                    'serial_numbers' => [
                        '4F88660000417',
                        '4F88660000416',
                        '4F88660000396',
                        '4F88660000393',
                        '4F88660000330',
                        '4F88660000415',
                        '4F88660000397',
                        '4F88660000412',
                        '4F88660000391',
                        '4F88660000398',
                        '4F88660000414',
                        '4F88660000421',
                        '4F88660000399',
                        '4F88660000422',
                        '4F88660000418',
                        '4F88660000424',
                        '4F88660000362',
                        '4F88660000408',
                        '4F88660000400',
                        '4F88660000363',
                        '4F88660000383',
                        '4F88660000370',
                        '4F88660000372',
                        '4F88660000423',
                        '4F88660000377',
                        '4F88660000376',
                        '4F88660000388',
                        '4F88660000379',
                        '4F88660000365',
                        '4F88660000369',
                        '4F88660000382',
                        '4F88660000366',
                        '4F88660000442',
                        '4F88660000384',
                        '4F88660000361',
                        '4F88660000371',
                        '4F88660000375',
                        '4F88660000385',
                        '4F88660000368',
                        '4F88660000378',
                        '4F88660000429',
                        '4F88660000355',
                        '4F88660000403',
                        '4F88660000426',
                        '4F88660000325',
                        '4F88660000404',
                        '4F88660000419',
                        '4F88660000358',
                        '4F88660000331',
                        '4F88660000405',
                        '4F88660000359',
                        '4F88660000402',
                        '4F88660000389',
                        '4F88660000420',
                        '4F88650000190',
                        '4F88660000352',
                        '4F88650000206',
                        '4F88650000060',
                        '4F88660000326',
                        '4F88650000200',
                        '4F88650000075',
                        '4F88650000058',
                        '4F88650000074',
                        '4F88650000061',
                        '4F88650000179',
                        '4F88650000222',
                        '4F88650000237',
                        '4F88650000223',
                        '4F88650000215',
                        '4F88650000217',
                        '4F88650000205',
                        '4F88650000173',
                        '4F88650000216',
                        '4F88650000209',
                        '4F88650000213',
                        '4F88650000214',
                        '4F88650000210',
                        '4F88650000204',
                        '4F88650000207',
                        '4F88650000220'
                    ]
                ],
                [
                    'product' => 'CH09VCT230VO',
                    'serial_numbers' => [
                        '4F88750000058',
                        '4F88750000057',
                        '4F88750000049',
                        '4F88750000051',
                        '4F88750000026',
                        '4F88750000024',
                        '4F88750000052',
                        '4F88750000003',
                        '4F88750000042',
                        '4F88750000054',
                        '4F88750000041',
                        '4F88750000005',
                        '4F88750000035',
                        '4F88750000002',
                        '4F88750000001',
                        '4F88750000016',
                        '4F88750000015',
                        '4F88750000014',
                        '4F88750000078',
                        '4F88750000013',
                        '4F88750000004',
                        '4F88750000017',
                        '4F88760001142',
                        '4F88760001141',
                        '4F88760001181',
                        '4F88760001182',
                        '4F88760001170',
                        '4F88760001163',
                        '4F88760001174',
                        '4F88760001186',
                        '4F88760001175',
                        '4F88760001173',
                        '4F88760001183',
                        '4F88760001164',
                        '4F88760001201',
                        '4F88760001179',
                        '4F88760001200',
                        '4F88760001204',
                        '4F88760001206',
                        '4F88760001205',
                        '4F88760001191',
                        '4F88760001207',
                        '4F88760001199',
                        '4F88760001209',
                        '4F88760001202',
                        '4F88760001159',
                        '4F88760001168',
                        '4F88760001167',
                        '4F88760001157',
                        '4F88760001149',
                        '4F88760001160',
                        '4F88760001151',
                        '4F88760001131',
                        '4F88760001132',
                        '4F88760001125',
                        '4F88760001136',
                        '4F88760001124',
                        '4F88750000171',
                        '4F88750000174',
                        '4F88750000199',
                        '4F88750000201',
                        '4F88750000166',
                        '4F88750000204',
                        '4F88750000176',
                        '4F88750000196',
                        '4F88750000193',
                        '4F88750000163',
                        '4F88750000192',
                        '4F88750000175',
                        '4F88760001237',
                        '4F88760001176',
                        '4F88760001234',
                        '4F88760001177',
                        '4F88760001242',
                        '4F88760001185',
                        '4F88760001184',
                        '4F88760001187',
                    ]
                ],
                [
                    'product' => 'GWH09QC-A3DNA1D/O',
                    'serial_numbers' => [
                        '4F88350000076'
                    ],

                ]
            ]
        ];
    }
}
