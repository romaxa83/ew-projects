<?php

namespace App\Services\Import\Parser;

use Exception;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class ParserAbstract
{

    /**
     * @var array
     */
    static $availableFormats = ['xls', 'xlsx'];

    /**
     * @var string
     */
    protected $pathToCsvFile;
    /**
     * @var string
     */
    protected $pathToFile;

    protected $collection;
    /**
     * @var array
     */
    protected $errorMessage = [];

    public function __construct(string $pathToFile)
    {
        $this->collection = new Collection();
        $this->pathToFile = $pathToFile;
        $this->errorMessage = null;
    }


    /**
     * @throws Exception
     */
    abstract  public function parse();

    /**
     * @throws Exception
     */
    public function start(): void
    {
        $this->convertXlsToCsv();
        $this->parse();
        @unlink($this->pathToFile);
        @unlink($this->pathToFile);
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return array|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @throws Exception
     */
    protected function convertXlsToCsv(): void
    {
        try {
            $filePathParts = explode('/', $this->pathToFile);
            $fileName = end($filePathParts);
            $file = new UploadedFile($this->pathToFile, $fileName);
            $extension = $file->getClientOriginalExtension();
            if ($extension === 'xlsx') {
                $reader = new Xlsx();
            } elseif ($extension === 'xls') {
                $reader = new Xls();
            } else {
                throw new Exception('Wrong File');
            }
            $spreadsheet = $reader->load($this->pathToFile);
            $loadedSheetNames = $spreadsheet->getSheetNames();
            $writer = new Csv($spreadsheet);
            $filesPath = [];
            foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {
                $writer->setSheetIndex($sheetIndex);
                $fileName = storage_path('app/public/') . str_replace(' ', '_', $loadedSheetName) . '.csv';
                $writer->save($fileName);
                $filesPath[] = $fileName;
            }

            $this->setPathToCsvFile($filesPath[0]);
        } catch (Exception $exception) {
            throw $exception;
        }
    }


    /**
     * @param $pathToFile
     */
    protected function setPathToCsvFile($pathToFile): void
    {
        $this->pathToCsvFile = $pathToFile;
    }

    /**
     * @param $path
     * @return array
     * @throws Exception
     */
    protected function getDataFromCsv($path): array
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new Exception("File $path not exist");
        }
        $header = null;
        $data = [];
        try {
            if (($handle = fopen($path, 'r')) !== false) {
                while (($row = fgetcsv($handle, 100000, ',')) !== false) {
                    if (!$header) {
                        $header = $row;
                    } else {
                        $data[] = array_combine($header, $row);
                    }
                }
                fclose($handle);
            }
            return $data;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
