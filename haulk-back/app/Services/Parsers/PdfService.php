<?php

namespace App\Services\Parsers;

use App\Exceptions\Parser\PdfFileException;
use App\Services\Parsers\Drivers\AcvTransportation\AcvTransportation;
use App\Services\Parsers\Drivers\Autosled\Autosled;
use App\Services\Parsers\Drivers\BeaconShippingLogistics\BeaconShippingLogistics;
use App\Services\Parsers\Drivers\CentralDispatch\CentralDispatch;
use App\Services\Parsers\Drivers\DirectConnectLogistix\DirectConnectLogistix;
use App\Services\Parsers\Drivers\FisherShipping\FisherShipping;
use App\Services\Parsers\Drivers\ReadyLogistics\ReadyLogistics;
use App\Services\Parsers\Drivers\Rpm\Rpm;
use App\Services\Parsers\Drivers\ShipCars\ShipCars;
use App\Services\Parsers\Drivers\SuperDispatch\SuperDispatch;
use App\Services\Parsers\Drivers\TransportOrder\TransportOrder;
use App\Services\Parsers\Drivers\WholesaleExpress\WholesaleExpress;
use App\Services\Parsers\Drivers\WholesaleExpress2\WholesaleExpress2;
use App\Services\Parsers\Drivers\WindyTransLogistics\WindyTransLogistics;
use App\Services\Parsers\Objects\ParserPart;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\PdfToText\Pdf;

class PdfService
{
    private const PARSERS = [
        ReadyLogistics::class,
        ShipCars::class,
        SuperDispatch::class,
        CentralDispatch::class,
        Rpm::class,
        Autosled::class,
        WindyTransLogistics::class,
        WholesaleExpress::class,
        WholesaleExpress2::class,
        AcvTransportation::class,
        BeaconShippingLogistics::class,
        DirectConnectLogistix::class,
        FisherShipping::class,
        TransportOrder::class,
    ];

    public const PARSER_TYPE_SINGLE = 'single';
    public const PARSER_TYPE_MULTIPLE = 'multiple';
    public const PARSER_TYPE_CUSTOM = 'custom';

    private const ANSII = [
        '\x13' => 0,
        '\x14' => 1,
        '\x15' => 2,
        '\x16' => 3,
        '\x17' => 4,
        '\x18' => 5,
        '\x19' => 6,
        '\x1A' => 7,
        '\x1B' => 8,
        '\x1C' => 9
    ];

    private Pdf $PDF;

    private ?string $text;

    public function __construct(Pdf $PDF)
    {
        $this->PDF = $PDF;
    }

    /**
     * @param UploadedFile $file
     * @return Collection
     * @throws Exception
     */
    public function process(UploadedFile $file): Collection
    {
        $this->PDF->setPdf($file);
        $type = $this->typeIdentify();

        $this->PDF->setOptions($type->options());

        return $this->parse($type);
    }

    /**
     * @return ParserAbstract
     * @throws PdfFileException
     */
    public function typeIdentify(): ParserAbstract
    {
        foreach (self::PARSERS as $parser) {
            $parser = resolve($parser);
            /**@var ParserAbstract $parser*/
            if (!$this->checkType($parser)) {
                continue;
            }

            $parser->preCheck($this->text);
            $this->unsetBody();

            return $parser;
        }

        throw new PdfFileException('file_is_not_identify');
    }

    /**
     * @param ParserAbstract $parser
     * @return bool
     * @throws PdfFileException
     */
    public function checkType(ParserAbstract $parser): bool
    {
        try {
            $text = $this->getBody();
        } catch (Exception $exception) {
            Log::error($exception);
            $text = null;
        }

        if (empty($text)) {
            throw new PdfFileException();
        }

        return (bool)preg_match($parser->type(), $text);
    }

    public function getBody(): string
    {
        if (!isset($this->text)) {
            $this->text = $this->PDF->text();

            $this->replaceANSII();
        }

        return $this->text;
    }

    private function replaceANSII()
    {
        foreach (self::ANSII as $symbol => $value) {
            $this->text = (string)preg_replace(sprintf('#(%s)#', $symbol), $value, $this->text);
        }
    }

    private function unsetBody(): void
    {
        unset($this->text);
    }

    /**
     * @param ParserAbstract $parser
     * @return Collection
     * @throws PdfFileException
     */
    protected function parse(ParserAbstract $parser): Collection
    {
        $result = collect();

        foreach ($parser->parts() as $part) {
            $result->put($part->name, $this->getMatchByRegex($part));
        }
        return $result;
    }

    /**
     * @param ParserPart $parserPart
     * @return string|Collection|null|float
     * @throws PdfFileException
     * @throws Exception
     */
    public function getMatchByRegex(ParserPart $parserPart)
    {
        return $this->createByParserType($parserPart)
            ->parse($this->getBody());
    }

    /**
     * @param ParserPart $parserPart
     * @return ValueParserMultiple|ValueParserSingle|mixed
     * @throws PdfFileException
     */
    public function createByParserType(ParserPart $parserPart)
    {
        switch ($parserPart->type) {
            case self::PARSER_TYPE_SINGLE:
                return new ValueParserSingle($parserPart);
            case self::PARSER_TYPE_MULTIPLE:
                return new ValueParserMultiple($parserPart);
            case self::PARSER_TYPE_CUSTOM:
                return new $parserPart->parser($parserPart);
        }

        throw new PdfFileException('file_is_not_identify');
    }
}
