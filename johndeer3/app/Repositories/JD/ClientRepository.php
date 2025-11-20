<?php

namespace App\Repositories\JD;

use App\Abstractions\AbstractRepository;
use App\Models\JD\Client;
use App\Models\JD\Region;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ClientRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Client::query();
    }

//    public function getById($id)
//    {
//        if(!$client = $this->query()->where('id', $id)->first()){
//            throw new \Exception('Not found client');
//        }
//        return $client;
//    }

    public function getAllActive(Request $request)
    {
        $perPage = $request['perPage'] ?? Client::DEFAULT_PER_PAGE;

        $query = Client::query()->with(['region'])->where('status', true);

        if(isset($request->search)){
            $query->where('company_name', 'like', '%' . $request->search . '%');
        }

        return $query->paginate($perPage);
    }

    public function getForHash(): Collection
    {
        return \DB::table(Client::TABLE)
            ->select([
                'customer_id',
                'company_name',
                'customer_first_name',
                'customer_last_name',
                'customer_second_name',
                'phone',
                Client::TABLE.'.status as c_status',
                'region_id',
                'name',
                Region::TABLE.'.status as r_status',
            ])
            ->join(
                Region::TABLE,
                Client::TABLE.'.region_id',
                '=',
                Region::TABLE.'.id'
            )
            ->get()
            ;
    }
//
//    public function deleteAll()
//    {
//        return $this->query()->delete();
//    }
}
