<?php

namespace App\Console\Commands\ParserHelpers;

use App\Repositories\Usdot\UsdotRepository;
use App\Services\Parsers\PdfNormalizeService;
use App\Services\Parsers\PdfService;
use App\Services\Vehicles\VinDecodeService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\Fake\Repositories\Usdot\UsdotFakeRepository;
use Tests\Fake\Services\Vehicles\FakeVinDecodeService;
use Throwable;

class ParserMakeTestMethodCommand extends Command
{
    private const DIR = 'tests/Data/Files/Pdf/';
    private const TEST_DIR = 'tests/Unit/Parsers/';

    private const KEY_AS = [
        'pickup_contact' => 'PICKUP',
        'delivery_contact' => 'DELIVERY',
        'shipper_contact' => 'SHIPPER',
    ];

    protected $signature = 'parser:make-test-method';
    protected $description = 'Render test method';

    private string $key;

    private bool $insertIntoFile = false;

    public function handle(): int
    {
        $dir = base_path(self::DIR);
        $contents = scandir($dir);
        unset($contents[0], $contents[1]);
        $dirs = [];
        foreach ($contents as $item) {
            if (!is_dir($dir . $item)) {
                continue;
            }
            $dirs[] = $item;
        }
        $dispatcher = $this->choice(
            "Chose dispatcher type",
            $dirs,
            5
        );

        $fileNumbers = $this->ask("Write file number (or range)");

        $fileNumbers = explode('-', $fileNumbers);
        $min = (int)$fileNumbers[0];
        $max = !empty($fileNumbers[1]) ? (int)$fileNumbers[1] : $min;
        for ($i = $min; $i <= $max; $i++) {
            $fileNumber = $i;
            $file = $dir . $dispatcher . '/' . Str::snake($dispatcher) . '_' . $fileNumber . '.pdf';

            if (!file_exists($file)) {
                $this->error(sprintf("File [%s] not found", $file));
                continue;
            }
            $exception = false;
            try {
                $parsed = $this->parsed($file);
            } catch (Throwable $e) {
                $answer = $this->askWithCompletion(
                    sprintf(
                        "Parser threw exception [%s] with message [%s]. Generate test?",
                        get_class($e),
                        $e->getMessage()
                    ),
                    [
                        'yes',
                        'no'
                    ],
                    'yes'
                );
                if ($answer === 'no') {
                    throw $e;
                }
                $exception = true;
            }

            $string = "\t/**\n\t* @throws Throwable\n\t*/\n" .
                "\tpublic function test_" . $fileNumber . "(): void\n"
                . "\t{\n"
                . (!$exception ? '' : "\t\t" . '$this->expectException(\\' . get_class($e) . '::class);' . "\n")
                . "\t\t" . '$this->createMakes('
                . (!$exception ? implode(
                    ", ",
                    array_unique(
                        array_map(
                            fn(array $vehicle): string => '"' . Str::upper($vehicle['make']) . '"',
                            $parsed['vehicles']
                        )
                    )
                ) : '"NONE"')
                . ")\n"
                . "\t\t\t" . '->createStates('
                . '"' . (!empty($parsed['pickup_contact']['state']) ? $parsed['pickup_contact']['state'] : 'NONE') . '", '
                . '"' . (!empty($parsed['delivery_contact']['state']) ? $parsed['delivery_contact']['state'] : 'NONE') . '", '
                . '"' . (!empty($parsed['shipper_contact']['state']) ? $parsed['shipper_contact']['state'] : 'NONE') . '"'
                . ")\n"
                . "\t\t\t" . '->createTimeZones('
                . '"' . (!empty($parsed['pickup_contact']['zip']) ? $parsed['pickup_contact']['zip'] : 'NONE') . '", '
                . '"' . (!empty($parsed['delivery_contact']['zip']) ? $parsed['delivery_contact']['zip'] : 'NONE') . '", '
                . '"' . (!empty($parsed['shipper_contact']['zip']) ? $parsed['shipper_contact']['zip'] : 'NONE') . '"'
                . ")\n"
                . "\t\t\t->assertParsing(\n"
                . "\t\t\t\t" . $fileNumber . ",\n"
                . "\t\t\t\t" . (!$exception ? $this->renderValue($parsed, "\t\t\t\t") : '[]') . "\n"
                . "\t\t\t);\n"
                . "\t}";

            $testFile = self::TEST_DIR . $dispatcher . 'ParserTest.php';

            if (file_exists($testFile)) {
                $this->testFileExistsAction($testFile, $string, $fileNumber);
                continue;
            }

            $this->info($string);
        }
        return self::SUCCESS;
    }

    private function renderValue($value, string $tabs)
    {
        if (is_null($value)) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_string($value)) {
            return '"' . $value . '"';
        }
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        if (is_array($value)) {
            if (empty($value)) {
                return "[]";
            }
            $result = "[\n";
            foreach ($value as $key => $item) {
                if (in_array($key, ['pickup_contact', 'delivery_contact', 'shipper_contact', 'payment'], true)) {
                    $this->key = $key;
                }
                if ($key === 'state_id') {
                    $item = '$this->states[self::' . self::KEY_AS[$this->key] . '_KEY]';
                } elseif ($key === 'timezone') {
                    $item = '$this->timezones[self::' . self::KEY_AS[$this->key] . '_KEY]';
                } elseif (($key === 'dispatch_instructions' || $key === 'terms') && $item !== null) {
                    $item = $this->splitLongText($item, $tabs);
                } elseif ($key === 'city' && $item === null && isset($value['zip'])) {
                    $item = '$this->cities[self::' . self::KEY_AS[$this->key] . '_KEY]';
                    unset($this->tempData);
                } else {
                    $item = $this->renderValue($item, $tabs . "\t");
                }
                $result .= $tabs . "\t" . (is_string($key) ? '"' . $key . '" => ' : '') . $item . ",\n";
            }
            $result .= $tabs . "]";
            return $result;
        }
        return 'null';
    }

    private function splitLongText(string $instructions, string $tabs): string
    {
        $firstLine = 73;
        $nextLine = 94;

        $instructions = '"' . Str::replace('"', '\"', $instructions) . '"';
        $instructions = Str::replace("\n", "\\n", $instructions);
        $length = Str::length($instructions);
        if ($length < 73 + 0.1 * 73) {
            return $instructions;
        }
        $words = explode(" ", $instructions);
        $line = 0;
        $lines = [];
        foreach ($words as $word) {
            $lines[$line] = !empty($lines[$line]) ? $lines[$line] . " " . $word : " " . $word;
            $maxLength = $line !== 0 ? $nextLine : $firstLine;
            if (Str::length($lines[$line]) < $maxLength) {
                continue;
            }
            $line++;
        }
        return implode("\"\n" . $tabs . "\t\t. \"", $lines);
    }

    /**
     * @param $file
     * @return array
     * @throws Throwable
     */
    private function parsed($file): array
    {
        app()->singleton(VinDecodeService::class, FakeVinDecodeService::class);
        app()->singleton(UsdotRepository::class, UsdotFakeRepository::class);
        $service = resolve(PdfService::class);
        $normalizeService = resolve(PdfNormalizeService::class);
        return $normalizeService->normalizeAfterParsing(
            $service->process(
                UploadedFile::fake()
                    ->createWithContent(
                        uniqid() . '.pdf',
                        file_get_contents($file)
                    )
            )
        );
    }

    private function testFileExistsAction(string $testFile, string $rendered, int $number): void
    {
        $answer = $this->insertIntoFile === false ? $this->askWithCompletion(
            sprintf("Do you want to add new test in test file [%s]", $testFile),
            [
                'all',
                'yes',
                'no'
            ],
            'yes'
        ) : 'yes';
        if ($answer === 'no') {
            $this->line($rendered);
            return;
        }
        if ($answer === 'all') {
            $this->insertIntoFile = true;
        }
        $content = trim(file_get_contents($testFile));
        if (preg_match("/public function test_" . $number . "\(\)/", $content)) {
            $this->error(sprintf("File [%s] already has function [test_%s]", $testFile, $number));
            return;
        }
        $rendered = preg_replace("/\\\$([0-9 ]+)/", "\\\\$$1", $rendered);
        $content = preg_replace("/}$/", "\n" . $rendered . "\n}\n", $content);
        file_put_contents($testFile, $content);
        $this->info(sprintf("New test method [test_%s] was added in test file [%s]", $number, $testFile));
    }
}
