<?php

namespace App\Services\ImportLocations\Worker;

use App\Services\ImportLocations\ItemParts\City;
use App\Services\ImportLocations\ItemParts\State;
use Illuminate\Support\Collection;

class Import
{
    /**
     * @var Collection
     */
    public $states;
    /**
     * @var Collection
     */
    public $cities;
    /**
     * @var string
     */
    public $path;

    /**
     * Import constructor.
     */
    public function __construct()
    {
        $this->states = new Collection();
        $this->cities = new Collection();
        $this->path = database_path('csv/uszips.csv');
    }

    /**
     * @throws \Exception
     */
    public function parse()
    {
        # Этот скрипт расчитан только на работу один раз , когда таблицы штатов и городов пусты , если нужна будет фича по постепенному импорту данных нужно будет либо переписать этот скрипт либо написать новый
        $data = $this->getDataFromCsv(); // get data from csv
        unset($this->path);
        $data = $this->rewriteData($data, 'state_name', $this->uniqueData($data, 'state_name')); // push info about states in collection and rewrite string to integer for database
        $this->rewriteData($data, 'zip', $this->uniqueData($data, 'zip')); // push info about cites in collection and rewrite string to integer for database
        $this->uniqueCollections(); // unique states data
        new DatabaseWriter($this);
    }

    /**
     * @param array $dataArray
     * @param string $arrayKey
     * @param array $uniqueValues
     * @return array
     * @throws \Exception
     */
    private function rewriteData(array $dataArray, string $arrayKey, array $uniqueValues): array
    {
        $i = 0;
        foreach ($dataArray as $value) {
            $id = array_search($value["$arrayKey"], $uniqueValues) + 1;
            $this->pushToCollection($id, $value, $arrayKey);
            $value["$arrayKey"] = $id;
            $dataArray[$i] = $value;
            $i++;
        }
        return $dataArray;
    }

    /**
     * @param array $array
     * @param string $column
     * @return array
     */
    private function uniqueData(array $array, string $column): array
    {
        return array_filter(array_values(array_unique(array_column($array, $column))));
    }

    /**
     * @param int $id
     * @param array $values
     * @param string $entityField
     * @throws \Exception
     */
    private function pushToCollection(int $id, array $values, string $entityField)
    {
        switch ($entityField) {
            case 'state_name':
                $data = new State($id, $values);
                $this->states->push($data);
                break;
            case 'zip':
                $data = new City($id, $values);
                $this->cities->push($data);
                break;
            default:
                throw new \Exception('False');
        }
    }

    private function uniqueCollections(): void
    {
        foreach (get_object_vars($this) as $key => $value) {
            if (is_object($this->{$key})) {
                if ($key === 'states') {
                    $this->{$key} = $this->{$key}->unique('name', true);
                }
            }
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getDataFromCsv(): array
    {
        if (!file_exists($this->path) || !is_readable($this->path)) {
            throw new \Exception("File $this->path not exist");
        }
        $header = null;
        $data = [];
        try {
            if (($handle = fopen($this->path, 'r')) !== false) {
                while (($row = fgetcsv($handle, 100000, ',')) !== false) {
                    if (!$header) {
                        $header = array_map('trim', $row);
                    } else {
                        $data[] = array_combine(array_map('trim', $header), array_map('trim', $row));
                    }
                }
                fclose($handle);
            }
            return $data;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}
