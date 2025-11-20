<?php

namespace App\Services\Import\Parser;

use App\Services\Import\Template\IosLinkTemplate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class IosLinkParser extends ParserAbstract
{
    public function __construct(string $pathToFile)
    {
        parent::__construct($pathToFile);
    }

    /**
     * @throws \Exception
     */
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
    public static function validate(UploadedFile $file)
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
        if ($diff = array_diff(IosLinkTemplate::$requiredColumns, $columns)) {
            return ['You do not have required columns in your client list: ' . implode(', ', $diff)];
        }
        // All ok
        return [];
    }

    /**
     * @inheritDoc
     */
    public function parse()
    {
        $data = $this->getDataFromCsv($this->pathToCsvFile);
        foreach ($data as $key => $value) {
            if (Arr::get($value, 'Code') == '') {
                continue;
            }
            $parsedIosLink = new IosLinkTemplate();
            $parsedIosLink->setData($value + ['row_id' => $key + 2])->parse();
            if ($parsedIosLink->isValid()) {
                $this->collection->push($parsedIosLink);
            } else {
                $this->errorMessage[] = $parsedIosLink->message;
            }
        }
    }
}
