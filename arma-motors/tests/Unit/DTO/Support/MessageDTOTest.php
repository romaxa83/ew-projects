<?php

namespace Tests\Unit\DTO\Support;

use App\DTO\Support\MessageDTO;
use App\ValueObjects\Email;
use Tests\TestCase;

class MessageDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'categoryId' => 1,
            'userId' => 1,
            'email' => 'test@test.com',
            'text' => 'some text',
        ];

        $dto = MessageDTO::byArgs($data);

        $this->assertEquals($dto->getCategoryId(), $data['categoryId']);
        $this->assertEquals($dto->getUserId(), $data['userId']);
        $this->assertEquals($dto->getText(), $data['text']);
        $this->assertTrue($dto->getEmail() instanceof Email);
        $this->assertEquals($dto->getEmail(), $data['email']);
    }

    /** @test */
    public function check_fill_only_required()
    {
        $data = [
            'categoryId' => 1,
            'email' => 'test@test.com',
        ];

        $dto = MessageDTO::byArgs($data);

        $this->assertEquals($dto->getCategoryId(), $data['categoryId']);
        $this->assertEquals($dto->getEmail(), $data['email']);
        $this->assertNull($dto->getText());
        $this->assertNull($dto->getUserId());
    }

    /** @test */
    public function without_email()
    {
        $data = [
            'categoryId' => 1,
        ];

        $this->expectException(\InvalidArgumentException::class);
        MessageDTO::byArgs($data);
    }

    /** @test */
    public function without_category_id()
    {
        $data = [
            'email' => 'test@test.com',
        ];

        $this->expectException(\InvalidArgumentException::class);
        MessageDTO::byArgs($data);
    }
}


