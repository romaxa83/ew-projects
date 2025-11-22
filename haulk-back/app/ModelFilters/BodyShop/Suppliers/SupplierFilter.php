<?php

namespace App\ModelFilters\BodyShop\Suppliers;

use App\Models\BodyShop\Suppliers\Supplier;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class SupplierFilter
 *
 * @mixin Supplier
 *
 * @package App\ModelFilters\BodyShop\VehicleOwners
 */
class SupplierFilter extends ModelFilter
{
    public function q(string $name)
    {
        $word = escapeLike(mb_convert_case($name, MB_CASE_LOWER));
        $searchString = '%' . $word . '%';
        $this->where(
            function (Builder $query) use ($searchString, $name) {
                return $query
                    ->whereRaw('lower(name) like ?', [$searchString])
                    ->orWhereHas(
                        'contacts',
                        function (Builder $builder) use ($searchString, $name) {
                            $builder
                                ->leftJoin(DB::raw('json_to_recordset(bs_supplier_contacts.phones) as phones_arr(number text)'), DB::raw('1'), '=', DB::raw('1'))
                                ->leftJoin(DB::raw('json_to_recordset(bs_supplier_contacts.emails) as emails_arr(value text)'),  DB::raw('1'), '=', DB::raw('1'))
                                ->whereRaw('lower(email) like ?', [$searchString])
                                ->orWhereRaw('lower(phone) like ?', [$searchString])
                                ->orWhereRaw('lower(name) like ?', [$searchString])
                                ->orWhereRaw('phones_arr.number like ?', [$searchString])
                                ->orWhereRaw('emails_arr.value like ?', [$searchString]);
                        }
                    );
            }
        );
    }

    public function searchid(int $id): void
    {
        $this->where('id', $id);
    }
}
