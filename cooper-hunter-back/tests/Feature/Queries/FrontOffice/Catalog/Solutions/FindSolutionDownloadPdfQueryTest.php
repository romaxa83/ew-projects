<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Solutions;

use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\Events\Statistics\FindSolutionStatisticEvent;
use App\GraphQL\Queries\FrontOffice\Catalog\Solutions\FindSolutionDownloadPdfQuery;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Solutions\Solution;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Database\Seeders\Catalog\Solutions\SolutionDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class FindSolutionDownloadPdfQueryTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_get_link(): void
    {
        Event::fake();

        Config::set('app.env', 'local');

        $this->seed(SolutionDemoSeeder::class);

        $outdoor = Solution::query()
            ->where('type', SolutionTypeEnum::OUTDOOR)
            ->where('zone', SolutionZoneEnum::MULTI)
            ->where('btu', 48000)
            ->first();
        $outdoor->product->clearMediaCollection(Product::MEDIA_COLLECTION_NAME);

        $outdoor
            ->product
            ->addMedia(
                UploadedFile::fake()
                    ->createWithContent(
                        'file.jpg',
                        file_get_contents(storage_path('testing/TEST-PHOTO.jpg'))
                    )
            )
            ->toMediaCollection(Product::MEDIA_COLLECTION_NAME);

        $link = $this->postGraphQL(
            GraphQLQuery::query(FindSolutionDownloadPdfQuery::NAME)
                ->args(
                    [
                        'pdf' => [
                            'outdoor_id' => $outdoor->id,
                            'indoors' => [
                                [
                                    'indoor_id' => $outdoor->children[1]->id,
                                    'line_set_id' => $outdoor->children[1]->children[0]->id,
                                ],
                                [
                                    'indoor_id' => $outdoor->children[0]->id,
                                    'line_set_id' => $outdoor->children[0]->children[0]->id,
                                ],
                                [
                                    'indoor_id' => $outdoor->children[1]->id,
                                    'line_set_id' => $outdoor->children[1]->children[0]->id,
                                ],
                            ]
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        FindSolutionDownloadPdfQuery::NAME
                    ]
                ]
            )
            ->json('data.' . FindSolutionDownloadPdfQuery::NAME);

        $parsed = parse_url($link);
        $this->assertArrayHasKey('host', $parsed);
        $this->assertArrayHasKey('path', $parsed);

        $this
            ->get($link)
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename="find_solution.pdf"');

        Event::assertDispatched(FindSolutionStatisticEvent::class);
    }
}
