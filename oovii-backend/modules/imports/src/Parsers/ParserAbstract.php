<?php

namespace WezomCms\Imports\Parsers;

use Exception;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class ParserAbstract
{
    static array $availableFormats = ['xls', 'xlsx'];

    protected string $pathToCsvFile;

    protected string $pathToFile;

    protected $collection;

    protected null|array $errorMessage = [];

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

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function getErrorMessage(): array|null
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
                        if($row[0] == "" && $row[1] == ""){
                            break;
                        }
                        $header = $this->prettyHeader($header);
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

    protected function prettyHeader(array $header): array
    {
        foreach ($header as $k => $item){
            $header[$k] = str_replace("\n", "", mb_strtolower(trim($item)));
        }

        return $header;
    }
}

