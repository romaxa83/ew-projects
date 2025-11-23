<?php

namespace WezomCms\Core\Drivers;

use Google\Cloud\Translate\V2\TranslateClient;
use WezomCms\Core\Contracts\TranslatorDriverInterface;

class GoogleTranslator implements TranslatorDriverInterface
{
    /**
     * @var TranslateClient
     */
    private $translator;

    /**
     * Translator constructor.
     * @param $keyFilePath
     */
    public function __construct($keyFilePath)
    {
        $this->translator = new TranslateClient(compact('keyFilePath'));
    }

    /**
     * @param  string  $source
     * @param  string  $to
     * @param  string|null  $from
     * @return string|null
     *
     * @throws DetectSourceLocaleException
     */
    public function translate(string $source, string $to, ?string $from = null): ?string
    {
        if (null === $from) {
            // detect source locale
            $from = array_get($this->translator->detectLanguage($source), 'languageCode');
        }

        if (!$from) {
            throw new DetectSourceLocaleException();
        }

        // If source locale equal to target - return source text
        if ($from === $to) {
            return $source;
        }

        $result = $this->translator->translate($source, ['source' => $from, 'target' => $to]);

        return html_entity_decode(array_get($result, 'text'), ENT_QUOTES);
    }
}
