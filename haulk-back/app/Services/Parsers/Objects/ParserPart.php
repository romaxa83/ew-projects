<?php

namespace App\Services\Parsers\Objects;

use App\Services\Parsers\PdfService;

class ParserPart
{
    public string $name;
    /**@var string|array|null $pattern */
    public $pattern = null;
    public ?array $replacementBefore = null;
    public ?array $replacementAfter = null;
    public ?array $replacementIntend = null;
    public ?string $parser = null;
    public string $type;

    public static function init(array $setting): self
    {
        $part = new self();
        $part->name = $setting['name'];
        $part->pattern = data_get($setting, 'pattern');
        $part->replacementBefore = data_get($setting, 'replacement_before');
        $part->replacementAfter = data_get($setting, 'replacement_after');
        $part->replacementIntend = data_get($setting, 'replacement_intend');
        $part->parser = data_get($setting, 'parser');
        $part->type = data_get($setting, 'type');
        return $part;
    }

    public static function make(string $name): self
    {
        $parser = new self();
        $parser->name = $name;
        return $parser;
    }

    public function setPattern(string $pattern): self
    {
        $this->name = $pattern;
        return $this;
    }

    public function setReplacementBefore(array $replacementBefore): self
    {
        $this->replacementBefore = $replacementBefore;
        return $this;
    }

    public function setReplacementAfter(array $replacementAfter): self
    {
        $this->replacementAfter = $replacementAfter;
        return $this;
    }

    /**
     * @param ParserPartIntend[] $replacementIntend
     * @return self
     */
    public function setReplacementIntend(array $replacementIntend): self
    {
        $this->replacementIntend = $replacementIntend;
        return $this;
    }

    public function single(string $pattern): self
    {
        $this->type = PdfService::PARSER_TYPE_SINGLE;
        $this->pattern = $pattern;
        return $this;
    }

    public function multi(array $patterns): self
    {
        $this->type = PdfService::PARSER_TYPE_MULTIPLE;
        $this->pattern = $patterns;
        return $this;
    }

    public function custom(string $parser): self
    {
        $this->type = PdfService::PARSER_TYPE_CUSTOM;
        $this->parser = $parser;
        return $this;
    }

    public function isNeedReplacementBefore(): bool
    {
        return is_array($this->replacementBefore);
    }

    public function isNeedReplacementAfter(): bool
    {
        return is_array($this->replacementAfter);
    }

    public function isNeedReplacementIntend(): bool
    {
        return is_array($this->replacementIntend);
    }
}
