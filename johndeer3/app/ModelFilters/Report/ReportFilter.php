<?php

namespace App\ModelFilters\Report;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class ReportFilter extends ModelFilter
{
    public function ps($value): self
    {
        $user = \Auth::user();
        if($user && ($user->isSM() || $user->isAdmin())){
            if(is_array($value)){
                return $this->whereIn('user_id', $value);
            }
            return $this->where('user_id', $value);
        }

        return $this;
    }

    public function dealer($value): self
    {
        $user = \Auth::user();
        if($user && ($user->isTM() || $user->isTMD() || $user->isAdmin())){
            return $this->whereHas('user',function(Builder $q) use($value) {
                if(is_array($value)){
                    return $q->whereIn('dealer_id', $value);
                }
                return $q->where('dealer_id', '=', $value);
            });
        }

        return $this;
    }

    public function tm($value): self
    {
        $user = \Auth::user();
        if($user && $user->isAdmin()){
            return $this->whereHas('user', function(Builder $q) use($value) {
                $q->whereHas('dealer', function(Builder $q) use($value) {
                    $q->whereHas('tm', function(Builder $q) use($value) {
                        if(is_array($value)){
                            return $q->whereIn('id', $value);
                        }
                        return $q->where('id', $value);
                    });
                });
            });
        }
        return $this;
    }

    public function modelDescription($value): self
    {
        return $this->whereHas('reportMachines',function(Builder $q) use ($value) {
            $q->whereHas('modelDescription', function(Builder $q) use ($value){
                if(is_array($value)){
                    return $q->whereIn('id', $value);
                }
                return $q->where('id', '=', $this->prettyValue($value));
            });
        });
    }

    public function equipmentGroup($value): self
    {
        return $this->whereHas('reportMachines',function(Builder $q) use($value) {
            $q->whereHas('equipmentGroup', function(Builder $q) use ($value){
                if(is_array($value)){
                    return $q->whereIn('id', $value);
                }
                return $q->where('id', '=', $this->prettyValue($value));
            });
        });
    }

    public function machineSerialNumber($value): self
    {
        return $this->whereHas('reportMachines',function(Builder $q) use($value) {
            $q->where('machine_serial_number', 'like', $this->prettyValue($value) .'%');
        });
    }

    public function country($value): self
    {
        return $this->whereHas('location', function (Builder $q) use ($value) {
            if(is_array($value)){
                return $q->whereIn('country', $value);
            }
            return $q->where('country', $this->prettyValue($value));
        });
    }

    public function region($value): self
    {
        return $this->whereHas('location', function (Builder $q) use ($value) {
            if(is_array($value)){
                return $q->whereIn('region', $value);
            }
            return $q->where('region', $this->prettyValue($value));
        });
    }

    public function district($value): self
    {
        return $this->whereHas('location', function (Builder $q) use ($value) {
            if(is_array($value)){
                return $q->whereIn('district', $value);
            }
            return $q->where('district', $this->prettyValue($value));
        });
    }

    public function clientModelDescription($value): self
    {
        return $this->where(function(Builder $q) use($value) {
            $q->orWhereHas('clients', function (Builder $q) use ($value) {
                $q->where('model_description_id', $this->prettyValue($value));
            })->orWhereHas('reportClients', function (Builder $q) use($value) {
                $q->where('model_description_id', $this->prettyValue($value));
            });
        });
    }

    public function featureValue($value): self
    {
        return $this->whereHas('features.value', function (Builder $q) use ($value){
            if(is_array($value)){
                return $q->whereIn('value_id', $value);
            }
            return $q->where('value_id','=', $this->prettyValue($value));
        });
    }

    public function year($value): self
    {
        return $this->whereYear('created_at', $this->prettyValue($value));
    }

    public function status($value): self
    {
        if(is_array($value)){
            return $this->whereIn('status', $value);
        }
        return $this->where('status', $this->prettyValue($value));
    }

    public function created($value):self
    {
        return $this->whereBetween('created_at', parse_date_query($value));
    }

    private function prettyValue($value)
    {
        if($value === 'null'){
            return null;
        }
        return $value;
    }
}
