<?php

namespace Tests\Feature\Saas\Admins;

class AdminListFilterTest extends BaseAdminManagerTest
{
    public function test_get_next_page_list(): void
    {
        $this->loginAsAdminManager();

        $this->createAdmins(60);

        $result = $this->requestToAdminListRoute(['page' => 2])
            ->assertOk();

        $admins = $result->json('data');

        self::assertCount(12, $admins);
    }

    public function test_get_limited_page_list(): void
    {
        $this->loginAsAdminManager();

        $this->createAdmins(60);

        $result = $this->requestToAdminListRoute(['per_page' => 15])
            ->assertOk();

        $admins = $result->json('data');

        self::assertCount(15, $admins);
    }

    public function test_get_admins_filter_by_query_chunk_of_email(): void
    {
        $this->loginAsAdminManager();

        $this->createAdmin(['email' => 'admin_email1@email.com']);
        $this->createAdmin(['email' => 'admin_email2@email.com']);
        $this->createAdmin(['email' => 'admin_email3@email.com']);

        $this->createAdmins(60);

        $result = $this->requestToAdminListRoute(['query' => 'admin_email'])
            ->assertOk();

        $admins = $result->json('data');

        self::assertCount(3, $admins);
    }

    public function test_get_admins_filter_by_query_chunk_of_name(): void
    {
        $this->loginAsAdminManager();

        $this->createAdmins(15, ['full_name' => 'AdminName']);
        $this->createAdmins(40);

        $result = $this->requestToAdminListRoute(['query' => 'adminname'])
            ->assertOk();

        $admins = $result->json('data');

        self::assertCount(15, $admins);
    }

    public function test_get_admins_filter_by_phone_chunk(): void
    {
        $this->loginAsAdminManager();
        $this->createAdmins(40);

        $this->createAdmin(['phone' => '+3809912345678']);
        $this->createAdmin(['phone' => '+3809911345678']);
        $this->createAdmin(['phone' => '+3809923456789']);

        $result = $this->requestToAdminListRoute(['query' => '0991'])
            ->assertOk();

        $admins = $result->json('data');

        self::assertCount(2, $admins);
    }

}
