<?php

namespace App\Imports\Order\Dealer;

use Exception;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Throwable;

class ProductImport implements ToCollection, WithChunkReading
{
    public Collection $data;

    public function __construct()
    {
        $this->data = collect();
    }

    public function collection(Collection $rows)
    {
        try {
            foreach ($rows as $k => $row){
                if($k != 0){
                    if(data_get($row, '3') != 0){
                        $this->data->push([
                            'id' => data_get($row, '0'),
                            'qty' => data_get($row, '3'),
                        ]);
                    }
                }
            }
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
