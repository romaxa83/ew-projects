<?php

namespace App\Services\Catalog\Solutions;

use App\Collections\Statistics\Solutions\IndoorsCollection;
use App\Contracts\Utilities\HasGeneratePdf;
use App\Dto\Catalog\Solutions\FindSolutionChangeIndoorDto;
use App\Dto\Catalog\Solutions\FindSolutionDto;
use App\Dto\Catalog\Solutions\SolutionDefaultSchemaDto;
use App\Dto\Catalog\Solutions\SolutionDto;
use App\Dto\Utilities\Pdf\PdfDataDto;
use App\Enums\Solutions\SolutionClimateZoneEnum;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\Events\Statistics\FindSolutionStatisticEvent;
use App\Exceptions\Catalog\CantChangeDeleteSolutionSettingException;
use App\Exceptions\Catalog\SolutionBtuNotFoundException;
use App\Exceptions\Catalog\SolutionIncorrectZoneCountException;
use App\Exceptions\Catalog\SolutionIndoorCanNotChangeException;
use App\Exceptions\Catalog\SolutionMultiIndoorCanNotSearchException;
use App\Exceptions\Catalog\SolutionOutdoorNotFoundException;
use App\Exceptions\Catalog\SolutionSeriesNotFoundException;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Solutions\Solution;
use App\Models\Catalog\Solutions\SolutionClimateZone;
use App\Models\Catalog\Solutions\SolutionSchema;
use App\Models\Media\Media;
use App\Models\Statistics\FindSolutionStatistic;
use App\Notifications\Catalog\Solutions\FindSolutionNotification;
use App\Traits\Utilities\HasPdfService;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Barryvdh\DomPDF\PDF;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SolutionService implements HasGeneratePdf
{
    use HasPdfService;

    public const MAX_ZONES = 5;

    /**
     * @throws JsonException
     */
    public function storeStatistic(Collection $solution): void
    {
        $stat = new FindSolutionStatistic();

        $title = $solution['outdoor']['unit'];

        preg_match('/(?<voltage>\d{3})\w{2}$/', $title, $matches);

        $voltage = $matches['voltage'] ?? '';

        $stat->outdoor = $title;
        $stat->outdoor_btu = filter_var($solution['outdoor']['btu'], FILTER_SANITIZE_NUMBER_INT);
        $stat->outdoor_voltage = $voltage;
        $stat->climate_zone = $solution['outdoor']['climate_zone'];
        $stat->series = $solution['outdoor']['series'];
        $stat->indoors = IndoorsCollection::resolve($solution['indoors']);

        $stat->save();
    }

    /**
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     * @throws InvalidArgumentException
     * @throws IOException
     */
    public function getStatistics(array $args = []): StreamedResponse
    {
        $statistics = FindSolutionStatistic::query()
            ->filter($args)
            ->get();

        $indoorHeader = [
            'title' => 'indoor#',
            'type' => 'type#',
            'btu' => 'indoor#_btu',
            'lineset' => 'lineset#',
        ];

        $getIndoorHeader = static fn(int $key, string $header): string => preg_replace(
            '/#/',
            $key,
            $header,
        );

        $rows = [];

        foreach ($statistics as $statistic) {
            $row = [
                'quantity' => '1',
                'date' => $statistic->created_at->format('m/d/Y'),
                'outdoor' => $statistic->outdoor,
                'outdoor_btu' => $statistic->outdoor_btu,
                'outdoor_voltage' => $statistic->outdoor_voltage,
                'climate_zone' => $statistic->climate_zone,
                'series' => $statistic->series,
            ];

            foreach (range(1, self::MAX_ZONES) as $index) {
                if ($indoorUnit = $statistic->indoors->get($index)) {
                    $row[$getIndoorHeader($index, $indoorHeader['title'])] = $indoorUnit->getTitle();
                    $row[$getIndoorHeader($index, $indoorHeader['type'])] = $indoorUnit->getType();
                    $row[$getIndoorHeader($index, $indoorHeader['btu'])] = $indoorUnit->getBtu();
                    $row[$getIndoorHeader($index, $indoorHeader['lineset'])] = $indoorUnit->getLineSet();
                } else {
                    $row[$getIndoorHeader($index, $indoorHeader['title'])] = '';
                    $row[$getIndoorHeader($index, $indoorHeader['type'])] = '';
                    $row[$getIndoorHeader($index, $indoorHeader['btu'])] = '';
                    $row[$getIndoorHeader($index, $indoorHeader['lineset'])] = '';
                }
            }

            $rows[] = $row;
        }

        return fastexcel($rows)->download('solution-statistics-' . now()->format('m-d-Y') . '.xlsx');
    }

    public function download(array $solution): string
    {
        return $this->setPdfDataInCache(
            data: $this->getPdfData($solution),
            name: trans('solution.pdf_name')
        )
            ->getPdfUrl();
    }

    private function getPdfData(array $args): Collection
    {
        $outdoor = Solution::find($args['outdoor_id']);

        $countZones = count($args['indoors']);

        if ($countZones > self::MAX_ZONES) {
            throw new SolutionIncorrectZoneCountException();
        }

        if ($outdoor->zone->isMulti() && $countZones === 1) {
            throw new SolutionIncorrectZoneCountException();
        }

        if ($outdoor->zone->isSingle() && $countZones > 1) {
            throw new SolutionIncorrectZoneCountException();
        }

        $result = collect(
            [
                'category' => $outdoor->zone->description,
                'outdoor' => collect(
                    [
                        'unit' => $outdoor->product->title,
                        'climate_zone' => $outdoor->climateZones->map(
                            fn(SolutionClimateZone $climateZone) => $climateZone->climate_zone->description
                        )
                            ->implode(', '),
                        'series' => $outdoor->series->translation->title,
                        'btu' => number_format($outdoor->btu) . ' BTU',
                        'zones' => trans_choice('solution.zones', $countZones, ['count' => $countZones]),
                        'image' => $this->getImgBase64($outdoor),
                    ]
                ),
                'indoors' => collect()
            ]
        );

        $indoors = Solution::whereIn('id', array_column($args['indoors'], 'indoor_id'))
            ->get();

        if ($outdoor->btu + $outdoor->btu * $outdoor->max_btu_percent / 100 < $indoors->sum('btu')) {
            throw new SolutionBtuNotFoundException();
        }

        $lineSets = Solution::whereIn('id', array_column($args['indoors'], 'line_set_id'))
            ->get();

        foreach ($args['indoors'] as $indoor) {
            /**@var Solution $solutionIndoor */
            $solutionIndoor = $indoors->sole('id', $indoor['indoor_id']);

            $result->get('indoors')
                ->push(
                    collect(
                        [
                            'unit' => $solutionIndoor->product->title,
                            'btu' => number_format($solutionIndoor->btu) . ' BTU',
                            'series' => $solutionIndoor->series->translation->title,
                            'type' => $solutionIndoor->indoor_type->description,
                            'line_set' => $lineSets->sole('id', $indoor['line_set_id'])->product->title,
                            'image' => $this->getImgBase64($solutionIndoor),
                        ]
                    )
                );
        }

        event(new FindSolutionStatisticEvent($result));
        logger_info("getPdfData");
        return $result;
    }

    /**
     * @param FindSolutionDto $dto
     * @return Collection
     * @throws SolutionOutdoorNotFoundException
     * @throws SolutionMultiIndoorCanNotSearchException
     */
    public function find(FindSolutionDto $dto): Collection
    {
        $outdoor = $this->getOutdoor($dto);

        if (!$outdoor) {
            throw new SolutionOutdoorNotFoundException();
        }

        if ($dto->isSingleZone()) {
            return $this->createResultCollection(
                $outdoor,
                $outdoor->children()
                    ->with(
                        [
                            'children',
                            'defaultLineSets'
                        ]
                    )
                    ->limit(1)
                    ->get()
            );
        }

        return $this->createResultCollection(
            $outdoor,
            $this->getIndoors($outdoor, $dto->getCountZones())
        );
    }

    public function getOutdoor(FindSolutionDto $dto): ?Solution
    {
        return Solution::query()
            ->where('btu', $dto->getBtu())
            ->where('series_id', $dto->getSeriesId())
            ->where('zone', $dto->getZone())
            ->where('voltage', $dto->getVoltage())
            ->where('type', SolutionTypeEnum::OUTDOOR)
            ->where(
                fn(Builder $builder) => $dto->getClimateZones()
                    ->each(
                        fn(string $zone) => $builder->whereHas(
                            'climateZones',
                            fn(Builder $builderClimateZone) => $builderClimateZone->where('climate_zone', $zone)
                        )
                    )
            )
            ->first();
    }

    /**
     * @param Solution $outdoor
     * @param Collection|Solution[] $indoors
     * @param bool $isCorrectBtu
     * @return Collection
     */
    private function createResultCollection(
        Solution $outdoor,
        Collection|array $indoors,
        bool $isCorrectBtu = true
    ): Collection {
        return collect(
            [
                'id' => $outdoor->id,
                'climate_zones' => $outdoor->climateZones->pluck('climate_zone'),
                'series' => $outdoor->series,
                'zone' => $outdoor->zone,
                'btu' => $outdoor->btu,
                'voltage' => $outdoor->voltage,
                'product' => $outdoor->product,
                'is_correct_btu' => $isCorrectBtu,
                'indoors' => $indoors->map(
                    function (Solution $indoor) use ($outdoor) {
                        $defaultLineSet = [];

                        foreach ($indoor->defaultLineSets as $item) {
                            $defaultLineSet[$item->line_set_id][] = $item->zone;
                        }

                        return [
                            'id' => $indoor->id,
                            'series' => $indoor->series,
                            'btu' => $indoor->btu,
                            'type' => $indoor->indoor_type,
                            'product' => $indoor->product,
                            'line_sets' => $indoor->children()
                                ->get()
                                ->map(
                                    fn(Solution $lineSet) => [
                                        'id' => $lineSet->id,
                                        'short_name' => $lineSet->short_name,
                                        'product' => $lineSet->product,
                                        'default' => array_key_exists($lineSet->id, $defaultLineSet) && in_array(
                                                $outdoor->zone,
                                                $defaultLineSet[$lineSet->id]
                                            )
                                    ]
                                )
                        ];
                    }
                )
            ]
        );
    }

    /**
     * @param Solution $outdoor
     * @param int $countZones
     * @return Collection
     * @throws SolutionMultiIndoorCanNotSearchException
     */
    public function getIndoors(Solution $outdoor, int $countZones): Collection
    {
        $indoors = $outdoor
            ->schemas()
            ->where('count_zones', $countZones)
            ->orderBy('zone')
            ->with(
                [
                    'indoor',
                    'indoor.children',
                    'indoor.defaultLineSets'
                ]
            )
            ->get();

        if ($indoors->isEmpty()) {
            throw new SolutionMultiIndoorCanNotSearchException();
        }

        return $indoors->map(
            fn(SolutionSchema $schema) => $schema->indoor
        );
    }

    private function getImgBase64(Solution $solution): ?string
    {
        /**@var Media $media */
        $media = $solution->product
            ->getFirstMedia(Product::MEDIA_COLLECTION_NAME);

        if ($media === null || empty($media->mime_type)) {
            return null;
        }

        if ($media->hasGeneratedConversion(Product::PDF_CARD_CONVERSION)) {
            $file = file_get_contents($media->getPath(Product::PDF_CARD_CONVERSION));
        } else {
            $file = Storage::disk($media->disk)
                ->get($media->id . DIRECTORY_SEPARATOR . $media->file_name);
        }

        return 'data:' . $media->mime_type . ';base64,' . base64_encode($file);
    }

    public function changeIndoor(FindSolutionChangeIndoorDto $dto): Collection
    {
        $outdoor = Solution::find($dto->getOutdoorId());

        $indoorSettings = $dto->getIndoorsSetting();

        $indoors = collect();

        for ($i = 0, $max = $dto->getCountZones(); $i < $max; $i++) {
            if (empty($indoorSettings[$i])) {
                throw new SolutionIncorrectZoneCountException();
            }
            $indoor = $outdoor->children()
                ->where('btu', $indoorSettings[$i]->getBtu())
                ->where('series_id', $indoorSettings[$i]->getSeriesId())
                ->where('indoor_type', $indoorSettings[$i]->getType())
                ->first();

            if (!$indoor) {
                throw new SolutionIndoorCanNotChangeException($i + 1);
            }
            $indoors->push($indoor);
        }

        $indoorsBtu = $indoors->pluck('btu')
            ->sum();

        return $this->createResultCollection(
            $outdoor,
            $indoors,
            $indoorsBtu <= $outdoor->btu + $outdoor->btu * $outdoor->max_btu_percent / 100
        );
    }

    public function getSeriesOutdoorList(array $args): Collection
    {
        $series = Solution::query()
            ->select('series_id')
            ->with('series')
            ->where('zone', $args['zone'])
            ->where(
                fn(Builder $builder) => array_map(
                    fn(string $zone) => $builder->whereHas(
                        'climateZones',
                        fn(Builder $builderClimateZone) => $builderClimateZone->where('climate_zone', $zone)
                    ),
                    $args['climate_zones']
                )
            )
            ->groupBy('series_id')
            ->get();

        if ($series->isEmpty()) {
            throw new SolutionSeriesNotFoundException();
        }

        return $series->map(
            fn(Solution $solution) => $solution->series
        );
    }

    public function getBtuOutdoorList(array $args): Collection
    {
        $btu = Solution::query()
            ->select('btu')
            ->where('zone', $args['zone'])
            ->where('series_id', $args['series_id'])
            ->groupBy('btu')
            ->orderBy('btu')
            ->get();

        if ($btu->isEmpty()) {
            throw new SolutionBtuNotFoundException();
        }

        return $btu->pluck('btu');
    }

    public function getVoltageOutdoorList(array $args): Collection
    {
        $btu = Solution::query()
            ->select('voltage')
            ->where('zone', $args['zone'])
            ->where('series_id', $args['series_id'])
            ->where('btu', $args['btu'])
            ->groupBy('voltage')
            ->orderBy('voltage')
            ->get();

        return $btu->pluck('voltage');
    }

    public function getIndoorSettingByOutdoor(Solution $outdoor): Collection
    {
        $intermediateResult = [];

        $indoors = $outdoor->children()
            ->with('series')
            ->get();

        /**@var Solution $indoor */
        foreach ($indoors as $indoor) {
            if (!empty($intermediateResult[$indoor->series_id])) {
                $intermediateResult[$indoor->series_id]['btu'][$indoor->btu][] = $indoor->indoor_type;
            } else {
                $intermediateResult[$indoor->series_id] = [
                    'series' => $indoor->series,
                    'btu' => [
                        $indoor->btu => [
                            $indoor->indoor_type
                        ]
                    ]
                ];
            }
        }

        $result = [];

        foreach ($intermediateResult as $seriesId => $seriesData) {
            foreach ($seriesData['btu'] as $btu => $types) {
                $result[] = [
                    'series' => $seriesData['series'],
                    'btu' => (int)$btu,
                    'types' => $types
                ];
            }
        }

        return collect($result);
    }

    public function createUpdate(SolutionDto $dto): Product
    {
        $solution = Solution::query()
            ->updateOrCreate(
                [
                    'product_id' => $dto->getProductId(),
                ],
                [
                    'type' => $dto->getType(),
                    'short_name' => $dto->getShortName(),
                    'indoor_type' => $dto->getIndoorType(),
                    'btu' => $dto->getBtu(),
                    'max_btu_percent' => $dto->getMaxBtuPercent(),
                    'voltage' => $dto->getVoltage(),
                    'zone' => $dto->getZone(),
                    'series_id' => $dto->getSeriesId()
                ]
            );

        if ($solution->wasChanged('type')) {
            $this->checkParentType($solution);
        }

        if ($dto->getType()
            ->is(SolutionTypeEnum::LINE_SET)) {
            return $solution->refresh()->product;
        }

        if ($dto->getType()
            ->is(SolutionTypeEnum::OUTDOOR)) {
            $solution->children()
                ->sync($dto->getIndoorIds());

            $solution->climateZones()
                ->delete();

            $solution->climateZones()
                ->createMany(
                    array_map(
                        fn(SolutionClimateZoneEnum $climateZone) => [
                            'climate_zone' => $climateZone->value
                        ],
                        $dto->getClimateZones()
                    )
                );

            if ($dto->getZone()
                ->isNot(SolutionZoneEnum::MULTI)) {
                return $solution->refresh()->product;
            }

            $solution
                ->schemas()
                ->delete();

            $solution->schemas()
                ->createMany(
                    $dto
                        ->getDefaultSchemas()
                        ->map(
                            fn(SolutionDefaultSchemaDto $schemaDto) => [
                                'indoor_id' => $schemaDto->getIndoorId(),
                                'zone' => $schemaDto->getZone(),
                                'count_zones' => $schemaDto->getCountZones()
                            ]
                        )
                        ->values()
                        ->toArray()
                );

            return $solution->refresh()->product;
        }

        $lineSetIds = [];

        $solution->defaultLineSets()
            ->delete();

        foreach ($dto->getLineSets() as $lineSetDto) {
            $lineSetIds[] = $lineSetDto->getLineSetId();

            if (!$lineSetDto->getDefaultForZones()) {
                continue;
            }
            $solution->defaultLineSets()
                ->createMany(
                    array_map(
                        fn(SolutionZoneEnum $enum) => [
                            'line_set_id' => $lineSetDto->getLineSetId(),
                            'zone' => $enum
                        ],
                        $lineSetDto->getDefaultForZones()
                    )
                );
        }

        $solution->children()
            ->sync($lineSetIds);

        return $solution->refresh()->product;
    }

    private function checkParentType(Solution $solution, bool $change = true): void
    {
        $solution
            ->children()
            ->detach();

        $parents = $solution->parents;

        if (!$parents) {
            return;
        }

        $parents->each(
            function (Solution $parent) use ($solution, $change) {
                $parent
                    ->children()
                    ->detach($solution->id);

                if (!$parent->children()
                    ->exists()) {
                    //All changes will rollback by transaction in mutation
                    throw new CantChangeDeleteSolutionSettingException($parent->product->title);
                }
            }
        );
    }

    public function delete(Solution $solution): ?bool
    {
        $this->checkParentType($solution);

        return $solution->delete();
    }

    public function getAllBtu(): Collection
    {
        return collect(
            config('catalog.solutions.btu.lists.all')
        );
    }

    public function getBtuList(array $args): Collection
    {
        if ($args['type'] === SolutionTypeEnum::OUTDOOR) {
            $BTUs = config(
                'catalog.solutions.btu.lists.' .
                SolutionTypeEnum::OUTDOOR . '.' .
                $args['zone']
            );
        } else {
            $BTUs = array_values(
                array_unique(
                    array_merge(
                        config(
                            'catalog.solutions.btu.lists.' .
                            SolutionTypeEnum::INDOOR . '.' .
                            SolutionZoneEnum::SINGLE . '.' .
                            $args['indoor_type']
                        ),
                        config(
                            'catalog.solutions.btu.lists.' .
                            SolutionTypeEnum::INDOOR . '.' .
                            SolutionZoneEnum::MULTI . '.' .
                            $args['indoor_type']
                        ),
                    )
                )
            );
        }

        sort($BTUs);

        return collect($BTUs);
    }

    public function getList(array $args): Collection
    {
        return Solution::filter($args)
            ->get();
    }

    public function send(array $solution, string $email): bool
    {
        Notification::route('mail', $email)
            ->notify(
                new FindSolutionNotification(
                    $this->getPdfOutput(
                        $this->getPdfData($solution),
                        trans('solution.pdf_name')
                    )
                )
            );
        return true;
    }

    public function generatePdf(PdfDataDto $pdfDataDto): PDF
    {
        return PdfFacade::setPaper('A4')
            ->setOptions(['isRemoteEnabled' => true])
            ->loadView(
                view: 'pdf.find-solution',
                data: [
                    'language' => $pdfDataDto->getLanguage(),
                    'name' => $pdfDataDto->getName(),
                    'pdf_data' => $pdfDataDto->getPdfData()
                ],
                encoding: 'UTF-8'
            );
    }
}
