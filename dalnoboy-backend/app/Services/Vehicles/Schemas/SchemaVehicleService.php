<?php


namespace App\Services\Vehicles\Schemas;


use App\Dto\Vehicles\SchemaVehicleDto;
use App\Enums\Vehicles\VehicleFormEnum;
use App\Exceptions\Vehicles\Schemas\OriginalSchemaNotFound;
use App\Exceptions\Vehicles\Schemas\SchemaHasVehiclesException;
use App\Exceptions\Vehicles\Schemas\SimilarSchemaException;
use App\Exceptions\Vehicles\Schemas\SimilarSchemaNameException;
use App\Exceptions\Vehicles\Schemas\WheelNotFoundException;
use App\Models\Vehicles\Schemas\SchemaAxle;
use App\Models\Vehicles\Schemas\SchemaVehicle;
use App\Models\Vehicles\Schemas\SchemaWheel;
use Illuminate\Database\Eloquent\Collection;
use Intervention\Image\Facades\Image;
use Intervention\Image\Gd\Font;
use Intervention\Image\Image as BaseImage;

class SchemaVehicleService
{
    public function getDefaultSchema(string $type, int $axlesCount): SchemaVehicle
    {
        $type = VehicleFormEnum::fromValue($type);

        $schema = SchemaVehicle::default()
            ->vehicleForm($type)
            ->has('axles', $axlesCount)
            ->with(
                [
                    'axles',
                    'axles.wheels'
                ]
            )
            ->first();

        if ($schema) {
            return $schema;
        }

        $schema = SchemaVehicle::default()
            ->vehicleForm($type)
            ->withCount('axles')
            ->has('axles', '<', $axlesCount)
            ->with(
                [
                    'axles',
                    'axles.wheels'
                ]
            )
            ->orderByDesc('axles_count')
            ->first();

        return $this->addAxlesInScheme($schema, $axlesCount);
    }

    private function addAxlesInScheme(SchemaVehicle $schema, int $axlesCount): SchemaVehicle
    {
        if ($schema->vehicle_form->isNot(VehicleFormEnum::TRAILER)) {
            return $schema;
        }

        $newSchema = new SchemaVehicle();
        $newSchema->name = $schema->name . '_' . $axlesCount;
        $newSchema->is_default = true;
        $newSchema->vehicle_form = $schema->vehicle_form;
        $newSchema->save();

        //Axles without without axle with spare wheel
        $exampleAxle = $schema->axles->slice(0, -1);

        for ($i = 0, $max = $exampleAxle->count()-1; $i < $max; $i++) {
            //If it is last T axle and need to add more
            if ($i === $max-1 && $max !== $axlesCount-1) {
                $max++;
            }

            $wheelStep = 0;

            if (empty($exampleAxle[$i]) && !empty($lastAxle)) {
                $exampleAxle[$i] = $lastAxle;
                $exampleAxle[$i]->position++;
                $exampleAxle[$i]->need_add = true;
                $wheelStep = config('vehicles.schemas.add_axles.trailer.step');
            }

            /**@var SchemaAxle $lastAxle*/
            $lastAxle = $newSchema->axles()->create([
                'name' => $exampleAxle[$i]->name,
                'position' => $exampleAxle[$i]->position,
                'need_add' => $exampleAxle[$i]->need_add,
            ]);

            foreach ($exampleAxle[$i]->wheels as $wheel) {
                $lastAxle->wheels()->create([
                    'position' => $wheel->position,
                    'name' => !$wheelStep ? $wheel->name : $this->getNewWheelName($wheel),
                    'pos_x' => $wheel->pos_x + $wheelStep,
                    'pos_y' => $wheel->pos_y,
                    'rotate' => $wheel->rotate,
                    'use' => true
                ]);
            }
        }
        /**@var SchemaAxle $exampleLastAxle*/
        $exampleLastAxle = $schema->axles->last();

        /**@var SchemaAxle $spareAxle*/
        $spareAxle = $newSchema->axles()->create([
            'position' => $lastAxle->position++,
            'name' => $exampleLastAxle->name
        ]);

        foreach ($exampleLastAxle->wheels as $wheel) {
            $spareAxle->wheels()->create([
                'position' => $wheel->position,
                'name' => $wheel->name,
                'pos_x' => $lastAxle->wheels[0]->pos_x + config('vehicles.schemas.add_axles.trailer.spare'),
                'pos_y' => $wheel->pos_y,
                'rotate' => $wheel->rotate,
                'use' => true
            ]);
        }

        return $newSchema;
    }

    private function getNewWheelName(SchemaWheel $exampleWheel): string
    {
        $wheelLater = preg_replace("/[0-9]+/", '', $exampleWheel->name);
        $wheelNumber = (int)preg_replace("/[^0-9]+/", '', $exampleWheel->name) + 2;

        return $wheelNumber . $wheelLater;
    }

    public function show(array $args): Collection
    {
        return SchemaVehicle::notDefault()
            ->filter($args)
            ->orderBy('name')
            ->get();
    }

    public function renderSchema(SchemaVehicle $schema): ?string
    {
        $schema = $schema->refresh();
        if (empty($schema->vehicle_form)) {
            return null;
        }
        $schemaImg = Image::make(config('vehicles.schemas.' . $schema->vehicle_form->value));
        $wheelOnImg = Image::make(config('vehicles.schemas.wheel.on'))
            ->backup();
        $wheelOffImg = Image::make(config('vehicles.schemas.wheel.off'))
            ->backup();

        foreach ($schema->axles as $axle) {
            if ($axle->need_add) {
                $schemaImg = $this->addAxleOnImgSchema($schemaImg, $schema->vehicle_form);
            }

            foreach ($axle->wheels as $wheel) {
                $wheelImg = $wheel->use ? $wheelOnImg : $wheelOffImg;

                $schemaImg->insert(
                    $wheelImg
                        ->reset()
                        ->text(
                            $wheel->name,
                            $wheelImg->width() / 2,
                            $wheelImg->height() / 2,
                            function (Font $font) use ($wheel)
                            {
                                $font->file(config('vehicles.schemas.font.file'));
                                $font->size(config('vehicles.schemas.font.size'));
                                $font->color($wheel->use ? config('vehicles.schemas.font.color.on') : config('vehicles.schemas.font.color.off'));
                                $font->valign('center');
                                $font->align('center');
                            }
                        )
                        ->rotate($wheel->rotate),
                    'top-right',
                    $wheel->pos_x,
                    $wheel->pos_y
                );
            }
        }

        $schemaImg->encode('png');

        return 'data:' . $schemaImg->mime . ';base64,' . base64_encode($schemaImg->encoded);
    }

    private function addAxleOnImgSchema(BaseImage $image, VehicleFormEnum $vehicleForm): BaseImage
    {
        $image->backup();

        //Cut spare from primary img in other img
        $spare = clone $image->resizeCanvas(-($image->width() - config('vehicles.schemas.add_axles.' . $vehicleForm->value . '.resize.spare')), 0, 'left', true);
        //Cut first half of vehicle in other img
        $trailer = clone $image->reset()->resizeCanvas(-config('vehicles.schemas.add_axles.' . $vehicleForm->value . '.resize.spare'), 0, 'right', true);
        $trailer->backup();
        //Cut last axle as example
        $lastAxle = clone $trailer->resizeCanvas(-($trailer->width()-config('vehicles.schemas.add_axles.' . $vehicleForm->value . '.resize.last_axle')), 0, 'left', true);
        $trailer->reset();

        //Create new img with three parts
        $image = Image::canvas($spare->width() + $lastAxle->width() + $trailer->width(), $image->height());
        $image
            ->insert($spare)
            ->insert($lastAxle, 'top-left', $spare->width())
            ->insert($trailer, 'top-left', $spare->width() + $lastAxle->width());

        unset($spare, $trailer, $lastAxle);

        return $image;
    }

    public function create(SchemaVehicleDto $dto): SchemaVehicle
    {
        $schema = new SchemaVehicle();

        $originalSchema = $this->checkSchemaData($dto, $schema);

        $schema->name = $dto->getName();
        $schema->vehicle_form = $originalSchema->vehicle_form;
        $schema->save();

        foreach ($originalSchema->axles as $originalAxle) {
            /**@var SchemaAxle $axle */
            $axle = $schema
                ->axles()
                ->create(
                    [
                        'position' => $originalAxle->position,
                        'name' => $originalAxle->name,
                    ]
                );

            foreach ($originalAxle->wheels as $wheel) {
                $axle
                    ->wheels()
                    ->create(
                        [
                            'position' => $wheel->position,
                            'name' => $wheel->name,
                            'pos_x' => $wheel->pos_x,
                            'pos_y' => $wheel->pos_y,
                            'rotate' => $wheel->rotate,
                            'use' => $dto->getWheels()
                                ->contains($wheel->id)
                        ]
                    );
            }
        }
        return $schema->refresh();
    }

    private function checkSchemaData(SchemaVehicleDto $dto, SchemaVehicle $schemaVehicle): SchemaVehicle
    {
        if ($schemaVehicle->id && $schemaVehicle->id !== $dto->getOriginalSchemaId()) {
            throw new OriginalSchemaNotFound();
        }

        /**@var SchemaVehicle $originalSchema */
        $originalSchema = SchemaVehicle::query()
            ->{$schemaVehicle->id ? 'notDefault' : 'default'}()
            ->with(
                [
                    'axles',
                    'axles.wheels'
                ]
            )
            ->find($dto->getOriginalSchemaId());

        if (!$originalSchema) {
            throw new OriginalSchemaNotFound();
        }

        $wheels = $originalSchema->wheels()
            ->whereIn(
                SchemaWheel::TABLE . '.id',
                $dto->getWheels()
                    ->toArray()
            )
            ->orderBy('id')
            ->get();

        if ($wheels->count() !== $dto->getWheels()
                ->count()) {
            throw new WheelNotFoundException();
        }

        if (
        SchemaVehicle::notDefault()
            ->where('id', '<>', $schemaVehicle->id)
            ->where('name', $dto->getName())
            ->exists()
        ) {
            throw new SimilarSchemaNameException();
        }

        $schemas = SchemaVehicle::notDefault()
            ->select(
                [
                    SchemaVehicle::TABLE . '.id',
                    SchemaVehicle::TABLE . '.name',
                ]
            )
            ->with(
                'wheels:' . SchemaWheel::TABLE . '.id,' . SchemaWheel::TABLE . '.name,' . SchemaWheel::TABLE . '.use'
            )
            ->where(SchemaVehicle::TABLE . '.id', '<>', $schemaVehicle->id)
            ->get();

        if ($schemas->isEmpty()) {
            return $originalSchema;
        }

        $neededWheels = $wheels->pluck('name')
            ->implode('');
        foreach ($schemas as $schema) {
            if ($neededWheels !== $schema->wheels->filter(fn(SchemaWheel $wheel) => $wheel->use)
                    ->pluck('name')
                    ->implode('')) {
                continue;
            }
            throw new SimilarSchemaException();
        }

        return $originalSchema;
    }

    public function update(SchemaVehicleDto $dto, SchemaVehicle $schema): SchemaVehicle
    {
        $originalSchema = $this->checkSchemaData($dto, $schema);

        $schema->name = $dto->getName();
        $schema->save();

        /**@var SchemaWheel[] $wheels */
        $wheels = $originalSchema->wheels()
            ->get();

        foreach ($wheels as $wheel) {
            $wheel->use = $dto->getWheels()
                ->contains($wheel->id);
            if ($wheel->isDirty()) {
                $wheel->save();
            }
        }
        return $schema->refresh();
    }

    public function delete(SchemaVehicle $schema): bool
    {
        if ($schema->vehicles()->exists()) {
            throw new SchemaHasVehiclesException();
        }
        return $schema->delete();
    }
}
