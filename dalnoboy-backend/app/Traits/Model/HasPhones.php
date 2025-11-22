<?php


namespace App\Traits\Model;


use App\Models\Phones\Phone;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasPhones
{
    protected static function boot()
    {
        parent::boot();

        self::deleting(
            function (self $model)
            {
                $model
                    ->phones()
                    ->delete();
            }
        );
    }

    public function phones(): MorphMany
    {
        return $this->morphMany(Phone::class, 'owner')
            ->orderByDesc('is_default')
            ->orderByDesc('phone');
    }

    public function phone(): MorphOne
    {
        return $this->morphOne(Phone::class, 'owner')
            ->where('is_default', true);
    }
}
