<?php

namespace App\Imports\Spares\Imports;

use App\Exceptions\ErrorsCode;
use App\Helpers\ConvertNumber;
use App\Models\Catalogs\Calc\Spares;
use App\Services\Telegram\TelegramDev;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class MitsubishiImport implements ToCollection, WithChunkReading
{
    private int $count = 0;

    public function collection(Collection $rows)
    {
        $data = [];
        $type = Spares::TYPE_MITSUBISHI;

        try {
            foreach ($rows as $key => $row) {
                if($this->count != 0){
                    $data[$key]['article'] = (string)$row[0];
                    $data[$key]['name'] = $row[1];
                    $data[$key]['price'] = ConvertNumber::fromFloatToNumber($row[2], 100);
                    $data[$key]['discount_price'] = null != $row[3] ? ConvertNumber::fromFloatToNumber($row[3], 100) : null;
                    $data[$key]['type'] = $type;
                    $data[$key]['sort'] = $this->count;
                    $data[$key]['qty'] = Spares::DEFAULT_QTY;
                }
                $this->count++;
            }
            TelegramDev::info("При импорте обработано - {$this->count} - позиций", $type);

            \DB::table('spares')->upsert(
                $data,
                ['type', 'article'],
                ['price', 'discount_price','name', 'qty']
            );
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage(), ErrorsCode::IMPORT_PROBLEM);
        }
    }

    public function chunkSize(): int
    {
        return 2000;
    }
}
