<?php

namespace Tests\Unit\Entities\Order\Parts;

use App\Entities\Order\Parts\EcommerceClientEntity;
use Tests\TestCase;

class EcommerceClientEntityTest extends TestCase
{
    protected array $data;

    public function setUp(): void
    {
        $this->data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
        ];

        parent::setUp();
    }

    /** @test */
    public function success_make()
    {
        $entity = EcommerceClientEntity::make($this->data);

        $this->assertEquals($entity->last_name, $this->data['last_name']);
        $this->assertEquals($entity->first_name, $this->data['first_name']);
        $this->assertEquals($entity->email->getValue(), $this->data['email']);
    }

    /** @test */
    public function success_to_json()
    {
        $entity = EcommerceClientEntity::make($this->data);

        $this->assertEquals($entity->toJson(), '{"first_name":"John","last_name":"Doe","email":"john@doe.com"}');

    }

    /** @test */
    public function success_to_array()
    {
        $entity = EcommerceClientEntity::make($this->data);

        $this->assertEquals($entity->toArray(), $this->data);

    }
}
