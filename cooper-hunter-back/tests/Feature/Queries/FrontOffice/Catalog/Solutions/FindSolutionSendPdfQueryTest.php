<?php


namespace Tests\Feature\Queries\FrontOffice\Catalog\Solutions;


use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\GraphQL\Queries\FrontOffice\Catalog\Solutions\FindSolutionSendPdfQuery;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Solutions\Solution;
use App\Notifications\Catalog\Solutions\FindSolutionNotification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Database\Seeders\Catalog\Solutions\SolutionDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class FindSolutionSendPdfQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use TestStorage;

    public function test_send(): void
    {
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
                $file = UploadedFile::fake()
                    ->createWithContent(
                        'file.jpg',
                        file_get_contents(storage_path('testing/TEST-PHOTO.jpg'))
                    )
            )
            ->toMediaCollection(Product::MEDIA_COLLECTION_NAME);

        $email = $this->faker->safeEmail;

        Notification::fake();

        $this->postGraphQL(
            GraphQLQuery::query(FindSolutionSendPdfQuery::NAME)
                ->args(
                    [
                        'email' => $email,
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
            ->assertJson(
                [
                    'data' => [
                        FindSolutionSendPdfQuery::NAME => true
                    ]
                ]
            );
        Notification::assertSentTo(
            new AnonymousNotifiable(),
            FindSolutionNotification::class,
            function (FindSolutionNotification $notification, array $channels, AnonymousNotifiable $notifiable) use (
                $email
            )
            {
                if (!in_array('mail', $channels)) {
                    return false;
                }
                if ($notifiable->routes['mail'] !== $email) {
                    return false;
                }
                return true;
            }
        );
    }
}
