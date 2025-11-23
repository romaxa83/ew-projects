<?php

namespace WezomCms\Imports\Parsers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use WezomCms\Imports\Templates\ProductTemplate;

class ProductParser extends ParserAbstract
{
    public function __construct(string $pathToFile)
    {
        parent::__construct($pathToFile);
    }

    public function start(): void
    {
        $this->convertXlsToCsv();
        $this->parse();
        @unlink($this->pathToFile);
        @unlink($this->pathToCsvFile);
    }

    /**
     * @param UploadedFile $file
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws Exception
     */
    public static function validate(UploadedFile $file): array
    {
        $extension = $extension ?? $file->getClientOriginalExtension();
        // Check extensions
        if (in_array($extension, static::$availableFormats) === false) {
            return ['Wrong import file mime!'];
        }
        $inputFileType = ucfirst($extension);
        $reader = IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->path());

        // Check categories spreadsheet first line
        $line = $spreadsheet->getSheet(0)->getRowIterator()->current();
        $columns = [];
        foreach ($line->getCellIterator() as $cell) {
            $columns[$cell->getColumn()] = $cell->getValue();
        }
        if ($diff = array_diff(ProductTemplate::$requiredColumns, $columns)) {
            return ['You do not have required columns in your client list: ' . implode(', ', $diff)];
        }
        // All ok
        return [];
    }

    public function parse()
    {
        $data = $this->getDataFromCsv($this->pathToCsvFile);

        foreach ($data as $key => $value) {
//            if (Arr::get($value, 'Code') == '') {
//                continue;
//            }

//            if($value['parent code'] != "NULL"){
//                dd($value);
//            }

            $parsedProduct = new ProductTemplate();
            $parsedProduct->setData($value + ['row_id' => $key + 2])->parse();

            if (true) {
//            if ($parsedProduct->isValid()) {
                $this->collection->push($parsedProduct);
            } else {
                $this->errorMessage[] = $parsedProduct->message;
            }
        }
    }
}

