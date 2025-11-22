<?php

namespace Tests\Unit\Services\Utilities;

use App\Enums\Utils\Versioning\VersionStatusEnum;
use App\Models\Utils\Version;
use App\Services\Utilities\AppVersionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AppVersionServiceTest extends TestCase
{
    use DatabaseTransactions;

    private AppVersionService $service;

    /**
     * @dataProvider VersionCheckDataProvider
     */
    public function test_versions(
        string $mobileVersion,
        array $currentVersions,
        VersionStatusEnum $expectedStatus
    ): void {
        $version = Version::factory()->create($currentVersions);

        $this->assertSame(
            $expectedStatus->value,
            $this->service->compare($mobileVersion, $version)->value,
        );
    }

    public function VersionCheckDataProvider(): array
    {
        return [
            'set_1' => [
                'mobileVersion' => '1.0.0',
                'currentVersions' => [
                    'recommended_version' => '1.0.2',
                    'required_version' => '1.0.1',
                ],
                'expectedStatus' => VersionStatusEnum::UPDATE_REQUIRED(),
            ],
            'set_2' => [
                'mobileVersion' => '1.0.1',
                'currentVersions' => [
                    'recommended_version' => '1.0.2',
                    'required_version' => '1.0.1',
                ],
                'expectedStatus' => VersionStatusEnum::UPDATE_RECOMMENDED(),
            ],
            'set_3' => [
                'mobileVersion' => '1.0.2',
                'currentVersions' => [
                    'recommended_version' => '1.0.2',
                    'required_version' => '1.0.1',
                ],
                'expectedStatus' => VersionStatusEnum::OK(),
            ],
            'set_4' => [
                'mobileVersion' => '1.0.22',
                'currentVersions' => [
                    'recommended_version' => '1.0.2',
                    'required_version' => '1.0.1',
                ],
                'expectedStatus' => VersionStatusEnum::OK(),
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(AppVersionService::class);
    }
}