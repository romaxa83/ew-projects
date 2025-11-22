<?php

namespace Tests\Feature\Mutations\Order;

use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\Catalogs\Car\TransportType;
use App\Models\Catalogs\Region\City;
use App\Models\Catalogs\Region\Region;
use App\Models\Catalogs\Service\DriverAge;
use App\Models\Catalogs\Service\Duration;
use App\Models\Catalogs\Service\InsuranceFranchise;
use App\Models\Catalogs\Service\Privileges;
use App\Models\Catalogs\Service\Service;
use App\Types\Communication;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class InsuranceCreateTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_create_casco()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $user->refresh();
        $service = Service::where('alias', Service::INSURANCE_CASCO_ALIAS)->first();
        $franchise = InsuranceFranchise::where('id', 1)->first();
        $brand = Brand::where('id', 1)->first();
        $model = Model::where('id', 10)->first();
        $driverAge = DriverAge::where('id', 1)->first();

        $data = [
            'serviceId' => $service->id,
            'brandId' => $brand->id,
            'modelId' => $model->id,
            'insuranceCompany' => 'arks',
            'franchiseId' => $franchise->id,
            'countPayments' => Service::countPayments()[1],
            'driverAgeId' => $driverAge->id,
            'communication' => Communication::PHONE,
            'carCost' => 56888,
        ];
        $response = $this->postGraphQL(['query' => $this->getQueryStrCasco($data)])
            ->assertOk();

        $responseData = $response->json('data.orderInsuranceCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('uuid', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('paymentStatus', $responseData);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertArrayHasKey('name', $responseData['user']);
        $this->assertArrayHasKey('phone', $responseData['user']);
        $this->assertArrayHasKey('service', $responseData);
        $this->assertArrayHasKey('id', $responseData['service']);
        $this->assertArrayHasKey('name', $responseData['service']['current']);
        $this->assertArrayHasKey('admin', $responseData);
        $this->assertArrayHasKey('communication', $responseData);
        $this->assertArrayHasKey('closedAt', $responseData);
        $this->assertArrayHasKey('deletedAt', $responseData);
        $this->assertArrayHasKey('createdAt', $responseData);
        $this->assertArrayHasKey('updatedAt', $responseData);

        $this->assertArrayHasKey('franchise', $responseData['additions']);
        $this->assertArrayHasKey('name', $responseData['additions']['franchise']);
        $this->assertArrayHasKey('brand', $responseData['additions']);
        $this->assertArrayHasKey('model', $responseData['additions']);
        $this->assertArrayHasKey('driverAge', $responseData['additions']);
        $this->assertArrayHasKey('insuranceCompany', $responseData['additions']);
        $this->assertArrayHasKey('countPayments', $responseData['additions']);
        $this->assertArrayHasKey('carCost', $responseData['additions']);

        $this->assertNull($responseData['uuid']);
        $this->assertEquals($service->id, $responseData['service']['id']);
        $this->assertEquals($user->name, $responseData['user']['name']);
        $this->assertNull($responseData['admin']);
        $this->assertEquals(Communication::PHONE, $responseData['communication']);
        $this->assertNull($responseData['closedAt']);
        $this->assertNull($responseData['deletedAt']);
        $this->assertNotNull($responseData['createdAt']);
        $this->assertNotNull($responseData['updatedAt']);
        $this->assertEquals($this->order_status_draft, $responseData['status']);
        $this->assertEquals($this->order_payment_status_none, $responseData['paymentStatus']);

        $this->assertEquals($franchise->name, $responseData['additions']['franchise']['name']);
        $this->assertEquals($brand->name, $responseData['additions']['brand']['name']);
        $this->assertEquals($model->name, $responseData['additions']['model']['name']);
        $this->assertEquals($driverAge->current->name, $responseData['additions']['driverAge']['current']['name']);
        $this->assertEquals($data['insuranceCompany'], $responseData['additions']['insuranceCompany']);
        $this->assertEquals($data['countPayments'], $responseData['additions']['countPayments']);
        $this->assertEquals($data['carCost'], $responseData['additions']['carCost']);

        $this->assertNull($responseData['additions']['region']);
        $this->assertNull($responseData['additions']['city']);
        $this->assertNull($responseData['additions']['privileges']);
        $this->assertNull($responseData['additions']['transportType']);
        $this->assertNull($responseData['additions']['duration']);
    }

    /** @test */
    public function success_create_go()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $user->refresh();
        $service = Service::where('alias', Service::INSURANCE_GO_ALIAS)->first();
        $franchise = InsuranceFranchise::where('id', 1)->first();
        $region = Region::where('id', 1)->first();
        $city = City::where('id', 1)->first();

        $privileges = Privileges::where('id', 1)->first();
        $transportType = TransportType::where('id', 1)->first();
        $duration = Duration::where('id', 1)->first();

        $data = [
            'serviceId' => $service->id,
            'franchiseId' => $franchise->id,
            'communication' => Communication::PHONE,
            'regionId' => $region->id,
            'cityId' => $city->id,
            'privilegesId' => $privileges->id,
            'transportTypeId' => $transportType->id,
            'durationId' => $duration->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrGo($data)])
            ->assertOk();

        $responseData = $response->json('data.orderInsuranceCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('uuid', $responseData);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertArrayHasKey('name', $responseData['user']);
        $this->assertArrayHasKey('phone', $responseData['user']);
        $this->assertArrayHasKey('service', $responseData);
        $this->assertArrayHasKey('id', $responseData['service']);
        $this->assertArrayHasKey('name', $responseData['service']['current']);
        $this->assertArrayHasKey('admin', $responseData);
        $this->assertArrayHasKey('communication', $responseData);
        $this->assertArrayHasKey('franchise', $responseData['additions']);
        $this->assertArrayHasKey('name', $responseData['additions']['franchise']);
        $this->assertArrayHasKey('brand', $responseData['additions']);
        $this->assertArrayHasKey('model', $responseData['additions']);
        $this->assertArrayHasKey('driverAge', $responseData['additions']);
        $this->assertArrayHasKey('insuranceCompany', $responseData['additions']);
        $this->assertArrayHasKey('countPayments', $responseData['additions']);
        $this->assertArrayHasKey('carCost', $responseData['additions']);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('region', $responseData['additions']);
        $this->assertArrayHasKey('city', $responseData['additions']);
        $this->assertArrayHasKey('privileges', $responseData['additions']);
        $this->assertArrayHasKey('transportType', $responseData['additions']);
        $this->assertArrayHasKey('duration', $responseData['additions']);
        $this->assertArrayHasKey('useTaxi', $responseData['additions']);

        $this->assertEquals($service->id, $responseData['service']['id']);
        $this->assertEquals($user->name, $responseData['user']['name']);
        $this->assertEquals(Communication::PHONE, $responseData['communication']);
        $this->assertEquals($franchise->name, $responseData['additions']['franchise']['name']);

        $this->assertEquals($region->current->name, $responseData['additions']['region']['current']['name']);
        $this->assertEquals($city->current->name, $responseData['additions']['city']['current']['name']);
        $this->assertEquals($privileges->current->name, $responseData['additions']['privileges']['current']['name']);
        $this->assertEquals($transportType->current->name, $responseData['additions']['transportType']['current']['name']);
        $this->assertEquals($duration->current->name, $responseData['additions']['duration']['current']['name']);
        $this->assertEquals($this->order_status_draft, $responseData['status']);
        $this->assertNull($responseData['uuid']);
        $this->assertNull($responseData['admin']);
        $this->assertNull($responseData['additions']['brand']);
        $this->assertNull($responseData['additions']['model']);
        $this->assertNull($responseData['additions']['driverAge']);
        $this->assertNull($responseData['additions']['insuranceCompany']);
        $this->assertNull($responseData['additions']['countPayments']);
        $this->assertNull($responseData['additions']['carCost']);
    }


    private function getQueryStrCasco(array $data): string
    {
        return sprintf('
            mutation {
                orderInsuranceCreate(input:{
                    serviceId: "%s"
                    franchiseId: "%s"
                    communication: "%s"
                    brandId: "%s"
                    modelId: "%s"
                    driverAgeId: "%s"
                    insuranceCompany: "%s"
                    countPayments: %d
                    carCost: %d
                }) {
                    id
                    uuid
                    status
                    paymentStatus
                    user {
                        name
                        phone
                    }
                    service {
                        id
                        current {
                            name
                        }
                    }
                    admin {
                        name
                    }
                    communication
                    additions {
                        franchise {
                            name
                        }
                        brand {
                            name
                        }
                        model {
                            name
                        }
                        driverAge {
                            current {
                                name
                            }
                        }
                        insuranceCompany
                        countPayments
                        carCost
                        region {
                            current {
                                name
                            }
                        }
                        city {
                            current {
                                name
                            }
                        }
                        privileges {
                            current {
                                name
                            }
                        }
                        transportType {
                            current {
                                name
                            }
                        }
                        duration {
                            current {
                                name
                            }
                        }
                    }
                    closedAt
                    deletedAt
                    createdAt
                    updatedAt
                }
            }',
            $data['serviceId'],
            $data['franchiseId'],
            $data['communication'],
            $data['brandId'],
            $data['modelId'],
            $data['driverAgeId'],
            $data['insuranceCompany'],
            $data['countPayments'],
            $data['carCost'],
        );
    }

    private function getQueryStrGo(array $data): string
    {
        return sprintf('
            mutation {
                orderInsuranceCreate(input:{
                    serviceId: "%s"
                    franchiseId: "%s"
                    communication: "%s"
                    regionId: "%s"
                    cityId: "%s"
                    privilegesId: "%s"
                    transportTypeId: "%s"
                    durationId: %d
                    useTaxi: true
                }) {
                    id
                    uuid
                    status
                    user {
                        name
                        phone
                    }
                    service {
                        id
                        current {
                            name
                        }
                    }
                    admin {
                        name
                    }
                    communication
                    additions {
                        franchise {
                            name
                        }
                        brand {
                            name
                        }
                        model {
                            name
                        }
                        driverAge {
                            current {
                                name
                            }
                        }
                        insuranceCompany
                        countPayments
                        carCost
                        region {
                            current {
                                name
                            }
                        }
                        city {
                            current {
                                name
                            }
                        }
                        privileges {
                            current {
                                name
                            }
                        }
                        transportType {
                            current {
                                name
                            }
                        }
                        duration {
                            current {
                                name
                            }
                        }
                        useTaxi
                    }
                }
            }',
            $data['serviceId'],
            $data['franchiseId'],
            $data['communication'],
            $data['regionId'],
            $data['cityId'],
            $data['privilegesId'],
            $data['transportTypeId'],
            $data['durationId'],
        );
    }
}

