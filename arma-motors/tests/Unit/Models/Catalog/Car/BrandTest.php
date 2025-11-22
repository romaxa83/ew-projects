<?php

namespace Tests\Unit\Models\Catalog\Car;

use App\Exceptions\EmailVerifyException;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\Brand;
use App\Models\Notification\Fcm;
use App\Models\Permission\Role;
use App\Models\User\User;
use App\Models\Verify\EmailVerify;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\UserBuilder;

class BrandTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function get_const_color()
    {
        $colors = Brand::colors();

        $this->assertIsArray($colors);
        $this->assertCount(4, $colors);
    }

    /** @test */
    public function check_color()
    {
        $brand = new Brand();

        $this->assertFalse($brand->checkColor('fail'));
        $this->assertTrue($brand->checkColor(2));
    }

    /** @test */
    public function asset_color()
    {
        $brand = new Brand();

        $this->expectException(\InvalidArgumentException::class);

        $brand->assetColor('fail');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function asset_color_success()
    {
        $brand = new Brand();

        $brand->assetColor(2);
    }
}



