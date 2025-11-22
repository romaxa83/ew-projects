<?php

namespace App\Models\Catalogs\Calc;

use App\Models\BaseModel;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\DriveUnit;
use App\Models\Catalogs\Car\EngineVolume;
use App\Models\Catalogs\Car\Fuel;
use App\Models\Catalogs\Car\Model;
use App\Models\Catalogs\Car\Transmission;
use App\Services\Calc\CompositeCalcInterface;
use App\Services\Calc\CompositeCalcTrait;
use App\Services\Calc\CompositeItemCalcInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $brand_id
 * @property int $model_id
 * @property int $mileage_id
 * @property int $engine_volume_id
 * @property int $transmission_id
 * @property int $drive_unit_id
 * @property int $fuel_id
 *
 * @see CalcModel::scopeByBrand()
 * @method EloquentBuilder|static byBrand($id)
 *
 */
class CalcModel extends BaseModel implements CompositeCalcInterface
{
    use HasFactory;
    use CompositeCalcTrait;

    public const TABLE = 'calc_models';
    protected $table = self::TABLE;

    public function calcSpares(): array
    {
        $this->clearChildItem();
        foreach ($this->spares as $spare){
            $this->setChildItem($spare);
        }

        $result['totalSpares'] = 0;
        $result['totalSparesDiscount'] = 0;
        foreach ($this->compositeItems as $key => $item){
            /** @var $item CompositeItemCalcInterface */
            $result['spares'][$key] = [
                'price' => $item->calcPrice(),
                'priceDiscount' => $item->calcPriceDiscount(),
                'name' => $item->name(),
                'qty' => $item->qty(),
                'unit' => $item->unit(),
            ];

            $result['totalSpares'] += $result['spares'][$key]['price'];
            $result['totalSparesDiscount'] += $item->calcPriceDiscount() ?? $item->calcPrice();
        }

        $result['totalSpares'] = prettyPrice($result['totalSpares']);
        $result['totalSparesDiscount'] = prettyPrice($result['totalSparesDiscount']);

        return $result;
    }

    public function calcWorks(Brand $brand): array
    {
        $this->clearChildItem();
        foreach ($this->works as $work){
            $this->setChildItem($work);
        }

        $result['totalWorks'] = 0;
        $result['totalWorksDiscount'] = 0;

        if(null !== $brand->hourly_payment){
            foreach ($this->compositeItems as $key => $item){
                /** @var $item CompositeItemCalcInterface */

                $result['works'][$key] = [
                    'price' => $item->calcPrice($brand->hourly_payment ? $brand->hourly_payment->getValue() : null),
                    'priceDiscount' => $item->calcPriceDiscount($brand->discount_hourly_payment ? $brand->discount_hourly_payment->getValue() : null),
                    'name' => $item->name(),
                    'qty' => $item->qty(),
                    'unit' => $item->unit(),
                ];

                $result['totalWorks'] += $item->calcPrice($brand->hourly_payment ? $brand->hourly_payment->getValue() : null);
                $result['totalWorksDiscount'] += $item->calcPriceDiscount($brand->discount_hourly_payment ? $brand->discount_hourly_payment->getValue() : null)
                    ?? $item->calcPrice($brand->hourly_payment->getValue());
            }
        }

        $result['totalWorks'] = prettyPrice($result['totalWorks']);
        $result['totalWorksDiscount'] = prettyPrice($result['totalWorksDiscount']);

        return $result;
    }

    public function runCalc(): array
    {
        $spares = $this->calcSpares();
        $works = $this->calcWorks($this->brand);

        $result['allTotal'] = prettyPrice($spares['totalSpares'] + $works['totalWorks']);
        $result['allTotalDiscount'] = prettyPrice($spares['totalSparesDiscount'] + $works['totalWorksDiscount']);

        return array_merge($spares, $works, $result);
    }

    // relations

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(Model::class, 'model_id', 'id');
    }

    public function mileage(): BelongsTo
    {
        return $this->belongsTo(Mileage::class, 'mileage_id', 'id');
    }

    public function engineVolume(): BelongsTo
    {
        return $this->belongsTo(EngineVolume::class, 'engine_volume_id', 'id');
    }

    public function transmission(): BelongsTo
    {
        return $this->belongsTo(Transmission::class, 'transmission_id', 'id');
    }

    public function driveUnit(): BelongsTo
    {
        return $this->belongsTo(DriveUnit::class, 'drive_unit_id', 'id');
    }

    public function fuel(): BelongsTo
    {
        return $this->belongsTo(Fuel::class, 'fuel_id', 'id');
    }

    public function works(): BelongsToMany
    {
        return $this->belongsToMany(
            Work::class,
            'calc_model_work_pivot',
            'model_id', 'work_id'
        )->withPivot('minutes');
    }

    public function spares(): BelongsToMany
    {
        return $this->belongsToMany(
            Spares::class,
            'calc_model_spares_pivot',
            'model_id', 'spares_id'
        )->withPivot('qty');
    }

    // scopes

    public function scopeByBrand(EloquentBuilder|self $query, $brandId): EloquentBuilder
    {
        return $query->with('brand')
            ->whereHas('brand', function(EloquentBuilder $q) use ($brandId) {
                $q->where('id', $brandId);
            });
    }

    public function scopeByModel(EloquentBuilder $query, $modelId): EloquentBuilder
    {
        return $query->with('model')
            ->whereHas('model', function(EloquentBuilder $q) use ($modelId) {
                $q->where('id', $modelId);
            });
    }

    public function scopeBrandSort($query, string $val)
    {
        if($this->checkGraphqlSort($val)){
            return $query
                ->select(['car_brands.*', 'calc_models.*'])
                ->join('car_brands', 'car_brands.id' , '=', 'calc_models.brand_id')
                ->orderBy('car_brands.name', $val);
        }
    }

    public function scopeModelSort($query, string $val)
    {
        if($this->checkGraphqlSort($val)){
            return $query
                ->select(['car_models.*', 'calc_models.*'])
                ->join('car_models', 'car_models.id' , '=', 'calc_models.model_id')
                ->orderBy('car_models.name', $val);
        }
    }

    public function scopeMileageSort($query, string $val)
    {
        if($this->checkGraphqlSort($val)){
            return $query
                ->select(['mileages.*', 'calc_models.*'])
                ->join('mileages', 'mileages.id' , '=', 'calc_models.mileage_id')
                ->orderBy('mileages.value', $val);
        }
    }

    public function scopeEngineVolumeSort($query, string $val)
    {
        if($this->checkGraphqlSort($val)){
            return $query
                ->select(['car_engine_volumes.*', 'calc_models.*'])
                ->join('car_engine_volumes', 'car_engine_volumes.id' , '=', 'calc_models.engine_volume_id')
                ->orderBy('car_engine_volumes.volume', $val);
        }
    }
}
